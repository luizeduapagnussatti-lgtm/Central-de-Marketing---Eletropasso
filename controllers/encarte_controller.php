<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

class EncarteController
{
    private EncarteService $encarteService;
    private EncarteRenderService $renderService;
    private HubPrecificacaoService $hubService;
    private MarketingAssistant $assistant;

    public function __construct()
    {
        $this->encarteService = new EncarteService();
        $this->renderService = new EncarteRenderService($this->encarteService);
        $this->hubService = new HubPrecificacaoService();
        $this->assistant = new MarketingAssistant();
    }

    public function handle(string $action): void
    {
        match ($action) {
            'listar'        => $this->listar(),
            'detalhe'       => $this->detalhe(),
            'salvar'        => $this->salvar(),
            'gerar'         => $this->gerar(),
            'status'        => $this->status(),
            'nova_versao'   => $this->novaVersao(),
            'upload_foto'       => $this->uploadFoto(),
            'processar_fundo'   => $this->processarFundo(),
            'reprocessar_fundo' => $this->processarFundo(),
            'buscar_sku'    => $this->buscarSku(),
            'titulos_ia'    => $this->titulosIa(),
            'normalizar'    => $this->normalizarNome(),
            'excluir'       => $this->excluir(),
            default         => marketing_json_response(false, null, 'Acao invalida.', 404),
        };
    }

    private function listar(): void
    {
        marketing_json_response(true, [
            'grupos' => $this->encarteService->listarAgrupadosPorMes(),
        ]);
    }

    private function detalhe(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $encarte = $this->encarteService->buscarPorId($id);

        if ($encarte === null) {
            marketing_json_response(false, null, 'Encarte nao encontrado.', 404);
        }

        marketing_json_response(true, ['encarte' => $encarte]);
    }

    private function salvar(): void
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            $payload = $_POST;
        }

        if (empty($payload['titulo_campanha']) || empty($payload['formato'])) {
            marketing_json_response(false, null, 'Titulo e formato sao obrigatorios.', 422);
        }

        try {
            $id = $this->encarteService->salvar($payload);
            marketing_json_response(true, ['id' => $id]);
        } catch (Throwable $e) {
            LoggerService::error('Erro ao salvar encarte', ['erro' => $e->getMessage()]);
            marketing_json_response(false, null, $e->getMessage(), 500);
        }
    }

    private function gerar(): void
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        $id = (int) ($payload['id'] ?? $_POST['id'] ?? 0);

        if ($id <= 0) {
            marketing_json_response(false, null, 'ID invalido.', 422);
        }

        @set_time_limit(120);

        try {
            EncarteRenderService::limparTemp();
            $resultado = $this->renderService->gerar($id);
            marketing_json_response(true, $resultado);
        } catch (Throwable $e) {
            LoggerService::error('Erro ao gerar encarte PNG', ['id' => $id, 'erro' => $e->getMessage()]);
            marketing_json_response(false, null, $e->getMessage(), 500);
        }
    }

    private function status(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $encarte = $this->encarteService->buscarPorId($id);

        if ($encarte === null) {
            marketing_json_response(false, null, 'Encarte nao encontrado.', 404);
        }

        marketing_json_response(true, [
            'status'               => $encarte['status'],
            'caminho_imagem_final' => $encarte['caminho_imagem_final'],
            'erro_geracao'         => $encarte['erro_geracao'],
            'tempo_geracao_ms'     => $encarte['tempo_geracao_ms'],
        ]);
    }

    private function novaVersao(): void
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        $id = (int) ($payload['id'] ?? 0);

        try {
            $novoId = $this->encarteService->criarNovaVersao($id);
            marketing_json_response(true, ['id' => $novoId]);
        } catch (Throwable $e) {
            marketing_json_response(false, null, $e->getMessage(), 500);
        }
    }

    private function uploadFoto(): void
    {
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            marketing_json_response(false, null, 'Upload invalido.', 422);
        }

        $maxMb = (int) (marketing_config('max_upload_size_mb', '10') ?? 10);
        $maxBytes = $maxMb * 1024 * 1024;

        if ($_FILES['foto']['size'] > $maxBytes) {
            marketing_json_response(false, null, "Arquivo excede {$maxMb}MB.", 422);
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $mime = mime_content_type($_FILES['foto']['tmp_name']);
        if (!in_array($mime, $allowed, true)) {
            marketing_json_response(false, null, 'Formato nao permitido. Use JPG, PNG ou WebP.', 422);
        }

        $ext = match ($mime) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        $dir = marketing_path('assets/produtos/originais');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'foto_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destino = $dir . '/' . $filename;

        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
            marketing_json_response(false, null, 'Falha ao salvar arquivo.', 500);
        }

        $caminhoRelativo = 'assets/produtos/originais/' . $filename;

        // Upload rapido: rembg roda em request separado (processar_fundo).
        marketing_json_response(true, [
            'caminho_foto_original'       => $caminhoRelativo,
            'caminho_foto_limpa'          => $caminhoRelativo,
            'processamento_imagem_status' => 'processando',
            'processamento_imagem_erro'   => null,
        ]);
    }

    private function processarFundo(): void
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            $payload = $_POST;
        }

        $caminhoOriginal = trim((string) ($payload['caminho_foto_original'] ?? ''));
        if ($caminhoOriginal === '' || !str_starts_with($caminhoOriginal, 'assets/produtos/originais/')) {
            marketing_json_response(false, null, 'Caminho da foto original invalido.', 422);
        }

        $absOriginal = marketing_path($caminhoOriginal);
        if (!is_file($absOriginal)) {
            marketing_json_response(false, null, 'Foto original nao encontrada.', 404);
        }

        $ext = strtolower(pathinfo($caminhoOriginal, PATHINFO_EXTENSION) ?: 'jpg');
        $nomeBase = pathinfo($caminhoOriginal, PATHINFO_FILENAME);
        $caminhoLimpa = 'assets/produtos/limpas/' . $nomeBase . '.png';

        $rembg = $this->assistant->removerFundoImagem($absOriginal, marketing_path($caminhoLimpa));
        if ($rembg['ok']) {
            marketing_json_response(true, [
                'caminho_foto_original'       => $caminhoOriginal,
                'caminho_foto_limpa'          => $caminhoLimpa,
                'processamento_imagem_status' => 'ok',
                'processamento_imagem_erro'   => null,
            ]);
        }

        $fallbackRel = 'assets/produtos/limpas/' . $nomeBase . '.' . $ext;
        $fallbackAbs = marketing_path($fallbackRel);
        if (!is_dir(dirname($fallbackAbs))) {
            mkdir(dirname($fallbackAbs), 0755, true);
        }
        @copy($absOriginal, $fallbackAbs);

        marketing_json_response(true, [
            'caminho_foto_original'       => $caminhoOriginal,
            'caminho_foto_limpa'          => is_file($fallbackAbs) ? $fallbackRel : $caminhoOriginal,
            'processamento_imagem_status' => 'erro',
            'processamento_imagem_erro'   => $rembg['erro'] ?? 'Falha ao remover fundo.',
        ]);
    }

    private function buscarSku(): void
    {
        $sku = trim((string) ($_GET['sku'] ?? ''));
        $resultado = $this->hubService->buscarPorSku($sku);

        if (!$resultado['success']) {
            marketing_json_response(false, null, $resultado['error'] ?? 'Erro.', 404);
        }

        $nomeComercial = $this->assistant->normalizarNome($resultado['data']['nome_erp']);

        marketing_json_response(true, [
            'sku'               => $resultado['data']['sku'],
            'nome_erp'          => $resultado['data']['nome_erp'],
            'nome_comercial'    => $nomeComercial,
            'preco_venda_atual' => $resultado['data']['preco_venda_atual'],
            'unidade'           => $resultado['data']['unidade'] ?? 'und',
        ]);
    }

    private function titulosIa(): void
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        $nomes = $payload['nomes'] ?? [];

        if (!is_array($nomes) || $nomes === []) {
            marketing_json_response(false, null, 'Lista de nomes vazia.', 422);
        }

        $opcoes = $this->assistant->gerarTitulosCampanha($nomes);
        marketing_json_response(true, ['opcoes' => $opcoes]);
    }

    private function normalizarNome(): void
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        $nomeErp = trim((string) ($payload['nome_erp'] ?? ''));

        if ($nomeErp === '') {
            marketing_json_response(false, null, 'Nome ERP vazio.', 422);
        }

        marketing_json_response(true, [
            'nome_comercial' => $this->assistant->normalizarNome($nomeErp),
        ]);
    }

    private function excluir(): void
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        $id = (int) ($payload['id'] ?? $_POST['id'] ?? 0);

        if ($id <= 0) {
            marketing_json_response(false, null, 'ID invalido.', 422);
        }

        if (!$this->encarteService->excluir($id)) {
            marketing_json_response(false, null, 'Encarte nao encontrado.', 404);
        }

        marketing_json_response(true, ['id' => $id]);
    }
}
