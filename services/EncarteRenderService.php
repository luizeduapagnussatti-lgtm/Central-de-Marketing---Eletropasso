<?php
declare(strict_types=1);

/**
 * Monta HTML temporario e dispara Puppeteer para captura PNG.
 */
class EncarteRenderService
{
    private EncarteService $encarteService;

    public function __construct(?EncarteService $encarteService = null)
    {
        $this->encarteService = $encarteService ?? new EncarteService();
    }

    public function gerar(int $encarteId): array
    {
        $inicio = microtime(true);
        $encarte = $this->encarteService->buscarPorId($encarteId);

        if ($encarte === null) {
            throw new RuntimeException('Encarte nao encontrado.');
        }

        if (empty($encarte['itens'])) {
            throw new RuntimeException('Encarte sem itens para renderizar.');
        }

        $this->encarteService->atualizarStatus($encarteId, 'gerando');

        try {
            $htmlPath = $this->montarHtmlTemporario($encarte);
            $outputDir = marketing_path('encartes/gerados');
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $outputFile = $outputDir . '/encarte_' . $encarteId . '_v' . $encarte['versao'] . '.png';
            $formato = $encarte['formato'];

            $this->executarPuppeteer($htmlPath, $formato, $outputFile);

            if (!is_file($outputFile)) {
                throw new RuntimeException('Arquivo PNG nao foi gerado pelo Puppeteer.');
            }

            @unlink($htmlPath);

            $tempoMs = (int) round((microtime(true) - $inicio) * 1000);
            $caminhoRelativo = 'encartes/gerados/' . basename($outputFile);

            $this->encarteService->atualizarStatus($encarteId, 'concluido', null, $tempoMs, $caminhoRelativo);
            LoggerService::info('Encarte gerado', ['id' => $encarteId, 'tempo_ms' => $tempoMs]);

            return [
                'caminho'   => $caminhoRelativo,
                'tempo_ms'  => $tempoMs,
                'formato'   => $formato,
            ];
        } catch (Throwable $e) {
            $tempoMs = (int) round((microtime(true) - $inicio) * 1000);
            $this->encarteService->atualizarStatus($encarteId, 'erro', $e->getMessage(), $tempoMs);
            LoggerService::error('Falha ao gerar encarte', ['id' => $encarteId, 'erro' => $e->getMessage()]);
            throw $e;
        }
    }

    public function montarHtmlTemporario(array $encarte): string
    {
        $tempDir = marketing_path('temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $htmlPath = $tempDir . '/temp_encarte_' . $encarte['id'] . '_' . time() . '.html';

        $templatePath = $this->encarteService->resolverArquivoTemplate(
            (string) ($encarte['modelo_layout'] ?? 'modelo_01')
        );

        $modeloService = new ModeloLayoutService();
        $modeloDb = $modeloService->buscarPorCodigo((string) ($encarte['modelo_layout'] ?? 'modelo_01'));
        $modelo_config = $modeloDb !== null
            ? $modeloService->configVisualMerged($modeloDb)
            : $modeloService->configVisualPadrao((string) ($encarte['modelo_layout'] ?? 'modelo_01'));

        ob_start();
        $encarte_data = $encarte;
        $itens = $encarte['itens'];
        $formato = $encarte['formato'];
        $base_path = marketing_root_path();
        include $templatePath;
        $html = ob_get_clean();

        file_put_contents($htmlPath, $html);

        return $htmlPath;
    }

    private function executarPuppeteer(string $htmlPath, string $formato, string $outputPath): void
    {
        $nodeBin = marketing_env('NODE_BIN', 'node');
        $timeout = (int) marketing_env('PUPPETEER_TIMEOUT_MS', '30000');
        $script = marketing_path('bin/render_encarte.js');

        $nodeArg = escapeshellarg($nodeBin);
        $scriptArg = escapeshellarg($script);
        $htmlArg = escapeshellarg($htmlPath);
        $formatoArg = escapeshellarg($formato);
        $outputArg = escapeshellarg($outputPath);
        $timeoutArg = escapeshellarg((string) $timeout);

        $cmd = "{$nodeArg} {$scriptArg} {$htmlArg} {$formatoArg} {$outputArg} {$timeoutArg} 2>&1";

        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new RuntimeException('Puppeteer falhou: ' . implode("\n", $output));
        }
    }

    /** Limpa arquivos HTML temporarios antigos (> 1 hora). */
    public static function limparTemp(): int
    {
        $tempDir = marketing_path('temp');
        if (!is_dir($tempDir)) {
            return 0;
        }

        $removidos = 0;
        $limite = time() - 3600;

        foreach (glob($tempDir . '/temp_encarte_*.html') ?: [] as $file) {
            if (filemtime($file) < $limite) {
                @unlink($file);
                $removidos++;
            }
        }

        return $removidos;
    }

    /** Gera PNG de preview para catalogo de modelos. */
    public function gerarPreviewModelo(string $codigoModelo, string $formato, string $outputPath): void
    {
        $modeloService = new ModeloLayoutService();
        $modeloDb = $modeloService->buscarPorCodigo($codigoModelo);
        $arquivoTemplate = (string) ($modeloDb['arquivo_template'] ?? '');
        $isFabric = $arquivoTemplate === 'modelo_fabric.php';

        $fotoExemplo = $this->buscarFotoExemplo();
        $itensBase = $this->itensPreviewExemplo($codigoModelo, $fotoExemplo, $isFabric);

        $formatoPreview = match ($codigoModelo) {
            'modelo_02' => '1x1',
            'modelo_03' => '9x16',
            default     => $formato,
        };

        if ($isFabric && $modeloDb !== null) {
            $formatosSuportados = $modeloDb['formatos_suportados'] ?? [];
            if (is_array($formatosSuportados) && $formatosSuportados !== []) {
                $formatoPreview = (string) ($formatosSuportados[0] ?? $formato);
            }
        }

        $encarte = [
            'id'                   => 0,
            'titulo_campanha'      => match ($codigoModelo) {
                'modelo_02' => 'Especial Material Eletrico',
                'modelo_03' => 'PROMOCAO|FECHA MES',
                default     => $isFabric ? 'Preview do Modelo' : 'Super Ofertas de Material Eletrico',
            },
            'modelo_layout'        => $codigoModelo,
            'formato'              => $formatoPreview,
            'validade_inicio'      => date('Y-m-d'),
            'validade_fim'         => date('Y-m-d', strtotime('+7 days')),
            'texto_legal_rodape'   => 'Ofertas validas enquanto durarem os estoques. Imagens meramente ilustrativas.',
            'itens'                => $itensBase,
        ];

        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $htmlPath = $this->montarHtmlTemporario($encarte);

        try {
            $this->executarPuppeteer($htmlPath, $formatoPreview, $outputPath);
        } finally {
            @unlink($htmlPath);
        }

        if (!is_file($outputPath)) {
            throw new RuntimeException('Preview PNG nao foi gerado.');
        }
    }

    private function itensPreviewExemplo(string $codigoModelo, string $fotoExemplo, bool $isFabric = false): array
    {
        if ($isFabric) {
            return $this->itensPreviewFabric($fotoExemplo);
        }

        if ($codigoModelo === 'modelo_02') {
            return [
                [
                    'nome_comercial' => 'Parafusadeira e Furadeira Makita 12V',
                    'sku' => '43411',
                    'preco_normal' => 389.90,
                    'preco_promocional' => 349.90,
                    'unidade' => 'und',
                    'caminho_foto_limpa' => $fotoExemplo,
                ],
                [
                    'nome_comercial' => 'Refletor LED Avant Slim 100W IP65',
                    'sku' => '39862',
                    'preco_normal' => 119.90,
                    'preco_promocional' => 89.90,
                    'unidade' => 'und',
                    'caminho_foto_limpa' => $fotoExemplo,
                ],
                [
                    'nome_comercial' => 'Quadro Distribuicao Tigre 12/16 Disj.',
                    'sku' => '33750',
                    'preco_normal' => 85.90,
                    'preco_promocional' => 69.99,
                    'unidade' => 'und',
                    'caminho_foto_limpa' => $fotoExemplo,
                ],
                [
                    'nome_comercial' => 'Cabo Flexivel Sil 2,5mm² 100m',
                    'sku' => '38004',
                    'preco_normal' => 195.90,
                    'preco_promocional' => 169.90,
                    'unidade' => 'und',
                    'caminho_foto_limpa' => $fotoExemplo,
                ],
                [
                    'nome_comercial' => 'Cabo Flexivel Sil 4,0mm² 100m',
                    'sku' => '38016',
                    'preco_normal' => 310.90,
                    'preco_promocional' => 279.90,
                    'unidade' => 'und',
                    'caminho_foto_limpa' => $fotoExemplo,
                ],
            ];
        }

        if ($codigoModelo === 'modelo_03') {
            return [
                [
                    'nome_comercial' => 'Painel de Embutir 18W',
                    'descricao_complementar' => 'Luz branca 6500K | 1260 lumens | Alta durabilidade',
                    'preco_promocional' => 14.90,
                    'unidade' => 'und',
                    'caminho_foto_limpa' => $fotoExemplo,
                ],
                [
                    'nome_comercial' => 'Botina Eletricista',
                    'descricao_complementar' => 'Couro resistente | Biqueira de aco | Solado antiderrapante',
                    'preco_promocional' => 79.90,
                    'unidade' => 'und',
                    'caminho_foto_limpa' => $fotoExemplo,
                ],
                [
                    'nome_comercial' => 'Padrao Completo 5MT',
                    'descricao_complementar' => 'Padrao completo | Seguranca | Instalacao confiavel',
                    'preco_promocional' => 1500.00,
                    'unidade' => 'und',
                    'caminho_foto_limpa' => $fotoExemplo,
                ],
                [
                    'nome_comercial' => 'Alicate Eletricista',
                    'descricao_complementar' => 'Aco forjado | Isolamento 1000V | Corte preciso',
                    'preco_promocional' => 64.90,
                    'unidade' => 'und',
                    'caminho_foto_limpa' => $fotoExemplo,
                ],
                [
                    'nome_comercial' => 'Quadro Sobrepor',
                    'descricao_complementar' => '4 disjuntores | Sobrepor | Facil instalacao',
                    'preco_promocional' => 38.90,
                    'unidade' => 'und',
                    'caminho_foto_limpa' => $fotoExemplo,
                ],
                [
                    'nome_comercial' => 'Caixa de Luz 4x2',
                    'descricao_complementar' => 'Padrao 4x2 | PVC resistente | Seguranca na obra',
                    'preco_promocional' => 1.49,
                    'unidade' => 'und',
                    'caminho_foto_limpa' => $fotoExemplo,
                ],
            ];
        }

        return [
            [
                'nome_comercial' => 'Lampada LED Smart 9W Wi-Fi',
                'descricao_complementar' => 'Controle pelo celular',
                'sku' => '10001',
                'preco_normal' => 39.90,
                'preco_promocional' => 29.90,
                'unidade' => 'und',
                'caminho_foto_limpa' => $fotoExemplo,
            ],
            [
                'nome_comercial' => 'Tomada 2P+T 10A Branca',
                'descricao_complementar' => 'Linha residencial',
                'sku' => '10002',
                'preco_normal' => 12.90,
                'preco_promocional' => 9.90,
                'unidade' => 'und',
                'caminho_foto_limpa' => $fotoExemplo,
            ],
            [
                'nome_comercial' => 'Cabo Flexivel 2,5mm Azul',
                'descricao_complementar' => 'Rolo 100 metros',
                'sku' => '10003',
                'preco_normal' => 289.00,
                'preco_promocional' => 249.90,
                'unidade' => 'rl',
                'caminho_foto_limpa' => $fotoExemplo,
            ],
            [
                'nome_comercial' => 'Disjuntor Monopolar 20A',
                'descricao_complementar' => 'Curva C',
                'sku' => '10004',
                'preco_normal' => 24.50,
                'preco_promocional' => 19.90,
                'unidade' => 'und',
                'caminho_foto_limpa' => $fotoExemplo,
            ],
        ];
    }

    /** Itens genericos para preview de modelos Fabric (zonas dinamicas). */
    private function itensPreviewFabric(string $fotoExemplo): array
    {
        $nomes = [
            'Parafusadeira Makita 12V',
            'Refletor LED 100W IP65',
            'Disjuntor Monopolar 20A',
            'Cabo Flexivel 2,5mm 100m',
            'Tomada 2P+T 10A Branca',
            'Lampada LED Smart 9W',
            'Alicate Eletricista 1000V',
            'Quadro Distribuicao 12 Disj.',
            'Padrao Completo 5MT',
            'Caixa de Luz 4x2',
            'Botina Eletricista',
            'Painel LED 18W Embutir',
        ];

        $itens = [];
        foreach ($nomes as $i => $nome) {
            $precoNormal = 49.90 + ($i * 17.5);
            $precoPromo = round($precoNormal * 0.85, 2);
            $itens[] = [
                'nome_comercial'     => $nome,
                'sku'                => (string) (10001 + $i),
                'preco_normal'       => $precoNormal,
                'preco_promocional'  => $precoPromo,
                'unidade'            => 'und',
                'caminho_foto_limpa' => $fotoExemplo,
            ];
        }

        return $itens;
    }

    private function buscarFotoExemplo(): string
    {
        $dir = marketing_path('assets/produtos/limpas');
        if (!is_dir($dir)) {
            return '';
        }

        $arquivos = array_merge(
            glob($dir . '/*.png') ?: [],
            glob($dir . '/*.jpg') ?: [],
            glob($dir . '/*.webp') ?: []
        );

        if ($arquivos === []) {
            return '';
        }

        $relativo = str_replace(
            marketing_root_path() . DIRECTORY_SEPARATOR,
            '',
            $arquivos[0]
        );

        return str_replace('\\', '/', $relativo);
    }
}
