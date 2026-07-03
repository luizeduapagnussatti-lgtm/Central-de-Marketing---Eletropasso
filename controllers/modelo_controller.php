<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

class ModeloController
{
    private ModeloLayoutService $modeloService;
    private EncarteRenderService $renderService;

    public function __construct()
    {
        $this->modeloService = new ModeloLayoutService();
        $this->renderService = new EncarteRenderService();
    }

    public function handle(string $action): void
    {
        match ($action) {
            'listar'          => $this->listar(),
            'detalhe'         => $this->detalhe(),
            'criar'           => $this->criar(),
            'salvar'          => $this->salvar(),
            'alternar_ativo'  => $this->alternarAtivo(),
            'excluir'         => $this->excluir(),
            'gerar_preview'   => $this->gerarPreview(),
            'upload_fundo'    => $this->uploadFundo(),
            'upload_elemento' => $this->uploadElemento(),
            default           => marketing_json_response(false, null, 'Acao invalida.', 404),
        };
    }

    private function listar(): void
    {
        marketing_json_response(true, [
            'modelos' => $this->modeloService->listarTodos(),
        ]);
    }

    private function detalhe(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $modelo = $this->modeloService->buscarPorId($id);

        if ($modelo === null) {
            marketing_json_response(false, null, 'Modelo nao encontrado.', 404);
        }

        $modelo['config_visual'] = $this->modeloService->configVisualMerged($modelo);

        marketing_json_response(true, ['modelo' => $modelo]);
    }

    private function criar(): void
    {
        $nome = trim((string) ($_POST['nome_exibicao'] ?? ''));
        $formato = trim((string) ($_POST['formato'] ?? ''));

        if ($nome === '') {
            marketing_json_response(false, null, 'Nome do modelo e obrigatorio.', 422);
        }

        if ($formato === '') {
            marketing_json_response(false, null, 'Formato e obrigatorio.', 422);
        }

        if (empty($_FILES['fundo']) || !is_uploaded_file($_FILES['fundo']['tmp_name'])) {
            marketing_json_response(false, null, 'Arquivo de fundo nao enviado.', 422);
        }

        $maxMb = (int) (marketing_config('max_upload_size_mb', '10') ?? 10);
        $maxBytes = $maxMb * 1024 * 1024;

        if ((int) $_FILES['fundo']['size'] > $maxBytes) {
            marketing_json_response(false, null, "Arquivo excede {$maxMb}MB.", 422);
        }

        $mime = mime_content_type($_FILES['fundo']['tmp_name']);
        if ($mime === false) {
            $mime = (string) ($_FILES['fundo']['type'] ?? '');
        }

        $tmp = (string) $_FILES['fundo']['tmp_name'];

        try {
            $modelo = $this->modeloService->criarComFundo($nome, $formato, $tmp, $mime);
            $id = (int) $modelo['id'];

            marketing_json_response(true, [
                'id'       => $id,
                'codigo'   => $modelo['codigo'],
                'redirect' => 'editar-modelo.php?id=' . $id,
            ]);
        } catch (InvalidArgumentException $e) {
            marketing_json_response(false, null, $e->getMessage(), 422);
        } catch (Throwable $e) {
            LoggerService::error('Erro ao criar modelo', ['erro' => $e->getMessage()]);
            marketing_json_response(false, null, $e->getMessage(), 500);
        }
    }

    private function salvar(): void
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            marketing_json_response(false, null, 'Payload invalido.', 422);
        }

        try {
            $modelo = $this->modeloService->salvar($payload);
            $modelo['config_visual'] = $this->modeloService->configVisualMerged($modelo);
            marketing_json_response(true, ['modelo' => $modelo]);
        } catch (InvalidArgumentException $e) {
            marketing_json_response(false, null, $e->getMessage(), 422);
        } catch (Throwable $e) {
            LoggerService::error('Erro ao salvar modelo', ['erro' => $e->getMessage()]);
            marketing_json_response(false, null, $e->getMessage(), 500);
        }
    }

    private function alternarAtivo(): void
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            $payload = $_POST;
        }

        $id = (int) ($payload['id'] ?? 0);
        if ($id <= 0) {
            marketing_json_response(false, null, 'ID invalido.', 422);
        }

        try {
            $modelo = $this->modeloService->alternarAtivo($id);
            marketing_json_response(true, ['modelo' => $modelo]);
        } catch (Throwable $e) {
            marketing_json_response(false, null, $e->getMessage(), 500);
        }
    }

    private function excluir(): void
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            $payload = $_POST;
        }

        $id = (int) ($payload['id'] ?? 0);
        if ($id <= 0) {
            marketing_json_response(false, null, 'ID invalido.', 422);
        }

        try {
            $this->modeloService->excluir($id);
            marketing_json_response(true, ['excluido' => true]);
        } catch (Throwable $e) {
            marketing_json_response(false, null, $e->getMessage(), 422);
        }
    }

    private function gerarPreview(): void
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            $payload = $_POST;
        }

        $id = (int) ($payload['id'] ?? 0);
        $modelo = $this->modeloService->buscarPorId($id);

        if ($modelo === null) {
            marketing_json_response(false, null, 'Modelo nao encontrado.', 404);
        }

        $codigo = (string) $modelo['codigo'];
        $formatos = $modelo['formatos_suportados'];
        $formato = match ($codigo) {
            'modelo_02' => '1x1',
            'modelo_03' => '9x16',
            default     => (string) ($formatos[0] ?? '9x16'),
        };

        $outputPath = marketing_path('assets/modelos/' . $codigo . '.png');

        try {
            $this->renderService->gerarPreviewModelo($codigo, $formato, $outputPath);
            $relativo = 'assets/modelos/' . $codigo . '.png';
            $versao = is_file($outputPath) ? (string) filemtime($outputPath) : '';

            marketing_json_response(true, [
                'preview' => $versao !== '' ? $relativo . '?v=' . $versao : $relativo,
            ]);
        } catch (Throwable $e) {
            LoggerService::error('Erro ao gerar preview modelo', ['erro' => $e->getMessage()]);
            marketing_json_response(false, null, $e->getMessage(), 500);
        }
    }

    private function uploadElemento(): void
    {
        $modeloId = (int) ($_POST['modelo_id'] ?? 0);

        if ($modeloId <= 0) {
            marketing_json_response(false, null, 'ID do modelo e obrigatorio.', 422);
        }

        if (empty($_FILES['elemento']) || !is_uploaded_file($_FILES['elemento']['tmp_name'])) {
            marketing_json_response(false, null, 'Arquivo de elemento nao enviado.', 422);
        }

        $maxMb = (int) (marketing_config('max_upload_size_mb', '10') ?? 10);
        $maxBytes = $maxMb * 1024 * 1024;

        if ((int) $_FILES['elemento']['size'] > $maxBytes) {
            marketing_json_response(false, null, "Arquivo excede {$maxMb}MB.", 422);
        }

        $mime = mime_content_type($_FILES['elemento']['tmp_name']);
        if ($mime === false) {
            $mime = (string) ($_FILES['elemento']['type'] ?? '');
        }

        $tmp = (string) $_FILES['elemento']['tmp_name'];

        try {
            $resultado = $this->modeloService->salvarElemento($modeloId, $tmp, $mime);
            marketing_json_response(true, $resultado);
        } catch (InvalidArgumentException $e) {
            marketing_json_response(false, null, $e->getMessage(), 422);
        } catch (Throwable $e) {
            LoggerService::error('Erro ao salvar elemento', ['erro' => $e->getMessage()]);
            marketing_json_response(false, null, $e->getMessage(), 500);
        }
    }

    private function uploadFundo(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $formato = trim((string) ($_POST['formato'] ?? ''));

        if ($id <= 0 || $formato === '') {
            marketing_json_response(false, null, 'ID e formato sao obrigatorios.', 422);
        }

        if (empty($_FILES['fundo']) || !is_uploaded_file($_FILES['fundo']['tmp_name'])) {
            marketing_json_response(false, null, 'Arquivo de fundo nao enviado.', 422);
        }

        $mime = (string) ($_FILES['fundo']['type'] ?? '');
        $tmp = (string) $_FILES['fundo']['tmp_name'];

        try {
            $modelo = $this->modeloService->salvarFundo($id, $formato, $tmp, $mime);
            marketing_json_response(true, ['modelo' => $modelo]);
        } catch (InvalidArgumentException $e) {
            marketing_json_response(false, null, $e->getMessage(), 422);
        } catch (Throwable $e) {
            LoggerService::error('Erro ao salvar fundo', ['erro' => $e->getMessage()]);
            marketing_json_response(false, null, $e->getMessage(), 500);
        }
    }
}
