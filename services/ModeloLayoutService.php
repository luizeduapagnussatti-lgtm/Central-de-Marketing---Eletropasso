<?php
declare(strict_types=1);

/**
 * CRUD e configuracao visual dos modelos de encarte.
 */
class ModeloLayoutService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? marketing_pdo();
    }

    public function listarTodos(): array
    {
        $st = $this->pdo->query('SELECT * FROM modelos_layout ORDER BY id ASC');

        return array_map([$this, 'normalizarLinha'], $st->fetchAll());
    }

    public function listarAtivos(): array
    {
        $st = $this->pdo->query('SELECT * FROM modelos_layout WHERE ativo = 1 ORDER BY id ASC');

        return array_map([$this, 'normalizarLinha'], $st->fetchAll());
    }

    public function buscarPorId(int $id): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM modelos_layout WHERE id = ? LIMIT 1');
        $st->execute([$id]);
        $modelo = $st->fetch();

        return $modelo === false ? null : $this->normalizarLinha($modelo);
    }

    public function buscarPorCodigo(string $codigo): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM modelos_layout WHERE codigo = ? LIMIT 1');
        $st->execute([$codigo]);
        $modelo = $st->fetch();

        return $modelo === false ? null : $this->normalizarLinha($modelo);
    }

    public function buscarPorCodigoAtivo(string $codigo): ?array
    {
        $st = $this->pdo->prepare(
            'SELECT * FROM modelos_layout WHERE codigo = ? AND ativo = 1 LIMIT 1'
        );
        $st->execute([$codigo]);
        $modelo = $st->fetch();

        return $modelo === false ? null : $this->normalizarLinha($modelo);
    }

    public function salvar(array $payload): array
    {
        $id = (int) ($payload['id'] ?? 0);
        if ($id <= 0) {
            throw new InvalidArgumentException('ID do modelo e obrigatorio.');
        }

        $modelo = $this->buscarPorId($id);
        if ($modelo === null) {
            throw new RuntimeException('Modelo nao encontrado.');
        }

        $nome = trim((string) ($payload['nome_exibicao'] ?? ''));
        if ($nome === '') {
            throw new InvalidArgumentException('Nome de exibicao e obrigatorio.');
        }

        $descricao = trim((string) ($payload['descricao'] ?? ''));
        $maxItens = (int) ($payload['max_itens_default'] ?? $modelo['max_itens_default']);
        if ($maxItens < 1 || $maxItens > 24) {
            throw new InvalidArgumentException('Maximo de itens deve estar entre 1 e 24.');
        }

        $formatos = $payload['formatos_suportados'] ?? [];
        if (!is_array($formatos) || $formatos === []) {
            throw new InvalidArgumentException('Selecione ao menos um formato.');
        }

        $formatosValidos = array_keys(marketing_formatos());
        $formatosFiltrados = array_values(array_intersect($formatosValidos, $formatos));
        if ($formatosFiltrados === []) {
            throw new InvalidArgumentException('Formatos selecionados invalidos.');
        }

        $configPayload = is_array($payload['config_visual'] ?? null)
            ? $payload['config_visual']
            : [];
        $configVisual = $this->mesclarConfigVisual(
            (string) $modelo['codigo'],
            $configPayload,
            is_array($modelo['config_visual'] ?? null) ? $modelo['config_visual'] : []
        );

        $slotsProduto = $this->contarSlotsProduto($configVisual);
        if ($slotsProduto > 0) {
            $maxItens = $slotsProduto;
        }

        $thumbnail = trim((string) ($payload['thumbnail'] ?? ''));
        if ($thumbnail !== '' && str_starts_with($thumbnail, 'data:image/')) {
            $this->salvarThumbnailPreview((string) $modelo['codigo'], $thumbnail);
        }

        $st = $this->pdo->prepare(
            'UPDATE modelos_layout
             SET nome_exibicao = ?, descricao = ?, formatos_suportados = ?, max_itens_default = ?, config_visual = ?
             WHERE id = ?'
        );
        $st->execute([
            $nome,
            $descricao !== '' ? $descricao : null,
            json_encode($formatosFiltrados, JSON_UNESCAPED_UNICODE),
            $maxItens,
            json_encode($configVisual, JSON_UNESCAPED_UNICODE),
            $id,
        ]);

        return $this->buscarPorId($id) ?? $modelo;
    }

    public function alternarAtivo(int $id): array
    {
        $modelo = $this->buscarPorId($id);
        if ($modelo === null) {
            throw new RuntimeException('Modelo nao encontrado.');
        }

        $novoAtivo = ((int) ($modelo['ativo'] ?? 0)) === 1 ? 0 : 1;
        $st = $this->pdo->prepare('UPDATE modelos_layout SET ativo = ? WHERE id = ?');
        $st->execute([$novoAtivo, $id]);

        return $this->buscarPorId($id) ?? $modelo;
    }

    public function excluir(int $id): void
    {
        $modelo = $this->buscarPorId($id);
        if ($modelo === null) {
            throw new RuntimeException('Modelo nao encontrado.');
        }

        $st = $this->pdo->prepare(
            'SELECT COUNT(*) FROM encartes WHERE modelo_layout = ?'
        );
        $st->execute([$modelo['codigo']]);
        $total = (int) $st->fetchColumn();

        if ($total > 0) {
            throw new RuntimeException(
                'Modelo em uso por ' . $total . ' encarte(s). Nao pode excluir.'
            );
        }

        $del = $this->pdo->prepare('DELETE FROM modelos_layout WHERE id = ?');
        $del->execute([$id]);
    }

    public function gerarCodigoUnico(): string
    {
        $st = $this->pdo->query('SELECT codigo FROM modelos_layout ORDER BY id ASC');
        $maxNum = 0;

        foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $codigo) {
            if (preg_match('/^modelo_(\d+)$/', (string) $codigo, $matches) === 1) {
                $maxNum = max($maxNum, (int) $matches[1]);
            }
        }

        return 'modelo_' . str_pad((string) ($maxNum + 1), 2, '0', STR_PAD_LEFT);
    }

    public function criar(array $payload): array
    {
        $nome = trim((string) ($payload['nome_exibicao'] ?? ''));
        if ($nome === '') {
            throw new InvalidArgumentException('Nome de exibicao e obrigatorio.');
        }

        $formatos = $payload['formatos_suportados'] ?? [];
        if (!is_array($formatos) || $formatos === []) {
            throw new InvalidArgumentException('Selecione ao menos um formato.');
        }

        $formatosValidos = array_keys(marketing_formatos());
        $formatosFiltrados = array_values(array_intersect($formatosValidos, $formatos));
        if ($formatosFiltrados === []) {
            throw new InvalidArgumentException('Formatos selecionados invalidos.');
        }

        $maxItens = (int) ($payload['max_itens_default'] ?? 12);
        if ($maxItens < 1 || $maxItens > 24) {
            throw new InvalidArgumentException('Maximo de itens deve estar entre 1 e 24.');
        }

        $codigo = $this->gerarCodigoUnico();
        $configVisual = $this->configVisualPadrao($codigo);
        $configVisual['fabric_state'] = [
            'version' => '5.3.0',
            'objects' => [],
        ];

        $descricao = trim((string) ($payload['descricao'] ?? ''));
        $arquivoTemplate = trim((string) ($payload['arquivo_template'] ?? 'modelo_fabric.php'));
        if ($arquivoTemplate === '') {
            $arquivoTemplate = 'modelo_fabric.php';
        }

        $st = $this->pdo->prepare(
            'INSERT INTO modelos_layout
             (codigo, versao, nome_exibicao, descricao, arquivo_template, formatos_suportados, config_visual, max_itens_default, ativo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)'
        );
        $st->execute([
            $codigo,
            '1.0',
            $nome,
            $descricao !== '' ? $descricao : null,
            $arquivoTemplate,
            json_encode($formatosFiltrados, JSON_UNESCAPED_UNICODE),
            json_encode($configVisual, JSON_UNESCAPED_UNICODE),
            $maxItens,
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $modelo = $this->buscarPorId($id);
        if ($modelo === null) {
            throw new RuntimeException('Falha ao criar modelo.');
        }

        return $modelo;
    }

    public function criarComFundo(string $nome, string $formato, string $tmpPath, string $mime): array
    {
        $modelo = $this->criar([
            'nome_exibicao'       => $nome,
            'formatos_suportados' => [$formato],
            'arquivo_template'    => 'modelo_fabric.php',
        ]);

        $id = (int) $modelo['id'];
        $this->salvarFundo($id, $formato, $tmpPath, $mime);

        return $this->inicializarFabricStateComPalco($id, $formato);
    }

    public function inicializarFabricStateComPalco(int $id, string $formato): array
    {
        $modelo = $this->buscarPorId($id);
        if ($modelo === null) {
            throw new RuntimeException('Modelo nao encontrado.');
        }

        $config = $this->configVisualMerged($modelo);
        $dims = marketing_formatos()[$formato] ?? marketing_formatos()['9x16'];
        $fundoPath = (string) ($config['fundos'][$formato] ?? '');
        if ($fundoPath === '') {
            throw new RuntimeException('Fundo nao encontrado para o formato.');
        }

        $corFundo = (string) ($config['cores']['fundo'] ?? '#ffffff');
        $width = (int) $dims['width'];
        $height = (int) $dims['height'];

        $absFundo = marketing_path($fundoPath);
        $imgW = $width;
        $imgH = $height;
        if (is_file($absFundo)) {
            $info = @getimagesize($absFundo);
            if ($info !== false) {
                $imgW = (int) $info[0];
                $imgH = (int) $info[1];
            }
        }

        $scale = min($width / max(1, $imgW), $height / max(1, $imgH));
        $displayW = $imgW * $scale;
        $displayH = $imgH * $scale;
        $leftPalco = ($width - $displayW) / 2;
        $topPalco = ($height - $displayH) / 2;

        $fabricState = [
            'version'    => '5.3.0',
            'background' => $corFundo,
            'objects'    => [
                [
                    'type'       => 'rect',
                    'name'       => 'fundo-editor',
                    'left'       => 0,
                    'top'        => 0,
                    'width'      => $width,
                    'height'     => $height,
                    'fill'       => $corFundo,
                    'selectable' => false,
                    'evented'    => false,
                    'visible'    => true,
                    'opacity'    => 1,
                    'scaleX'     => 1,
                    'scaleY'     => 1,
                    'angle'      => 0,
                    'rx'         => 0,
                    'ry'         => 0,
                ],
                [
                    'type'        => 'image',
                    'name'        => 'palco',
                    'left'        => $leftPalco,
                    'top'         => $topPalco,
                    'width'       => $imgW,
                    'height'      => $imgH,
                    'scaleX'      => $scale,
                    'scaleY'      => $scale,
                    'angle'       => 0,
                    'opacity'     => 1,
                    'visible'     => true,
                    'selectable'  => true,
                    'evented'     => true,
                    'originX'     => 'left',
                    'originY'     => 'top',
                    'src'         => $fundoPath,
                    'crossOrigin' => 'anonymous',
                ],
            ],
        ];

        $config['fabric_state'] = $fabricState;

        $st = $this->pdo->prepare('UPDATE modelos_layout SET config_visual = ? WHERE id = ?');
        $st->execute([json_encode($config, JSON_UNESCAPED_UNICODE), $id]);

        $atualizado = $this->buscarPorId($id) ?? $modelo;
        $atualizado['config_visual'] = $this->configVisualMerged($atualizado);

        return $atualizado;
    }

    public function configVisualPadrao(string $codigo): array
    {
        $base = [
            'cores' => [
                'primary'      => '#b91c1c',
                'dark'         => '#7f1d1d',
                'fundo'        => '#ffffff',
                'fundo_escuro' => '#1f2937',
                'preco_bg'     => '#b91c1c',
                'preco_texto'  => '#ffffff',
                'badge_bg'     => '#ffffff',
                'badge_texto'  => '#b91c1c',
            ],
            'textos' => [
                'badge_oferta'  => 'Oferta',
                'clube_badge'   => 'Clube Eletropasso',
                'mostrar_clube' => false,
            ],
            'icones' => [
                'auto' => true,
                'tipo' => 'emoji',
                'mapa' => [
                    'led'    => '💡',
                    'wifi'   => '📶',
                    'tomada' => '🔌',
                    'cabo'   => '⚡',
                ],
            ],
            'fundos' => [],
        ];

        if ($codigo === 'modelo_01') {
            $base['cores']['preco_bg'] = '#16a34a';
            $base['cores']['badge_bg'] = '#059669';
            $base['cores']['badge_texto'] = '#ffffff';
            $base['textos']['badge_oferta'] = 'Oferta Especial!';
            $base['textos']['texto_legal'] = 'Ofertas validas enquanto durarem os estoques.';
            $base['textos']['mostrar_clube'] = false;
            $base['icones']['auto'] = true;
        }

        if ($codigo === 'modelo_02') {
            $base['textos']['mostrar_clube'] = true;
            $base['icones']['auto'] = false;
            $base['icones']['mapa'] = [];
        }

        if ($codigo === 'modelo_03') {
            $base['cores'] = [
                'primary'      => '#dc2626',
                'dark'         => '#991b1b',
                'fundo'        => '#030b17',
                'fundo_escuro' => '#071326',
                'preco_bg'     => '#dc2626',
                'preco_texto'  => '#ffffff',
                'badge_bg'     => '#1e3a8a',
                'badge_texto'  => '#ffffff',
            ];
            $base['textos'] = array_merge($base['textos'], [
                'titulo_linha1'    => 'PROMOCAO',
                'titulo_linha2'    => 'FECHA MES',
                'faixa_oferta'     => 'OFERTAS IMPERDIVEIS!',
                'subtitulo'        => 'QUALIDADE, SEGURANCA E OS MELHORES PRECOS VOCE ENCONTRA AQUI!',
                'footer_endereco'  => 'Av. Brasil, Centro',
                'footer_cidade'    => 'Passo Fundo - RS',
                'footer_whatsapp'  => '(54) 9 9999-9999',
            ]);
            $base['icones']['auto'] = true;
            $base['fundos'] = [
                '9x16'   => 'assets/modelos/fundos/modelo_03_9x16.png',
                'status' => 'assets/modelos/fundos/modelo_03_9x16.png',
            ];
        }

        return $base;
    }

    public function configVisualMerged(array $modelo): array
    {
        $codigo = (string) ($modelo['codigo'] ?? 'modelo_01');
        $salvo = is_array($modelo['config_visual'] ?? null) ? $modelo['config_visual'] : [];

        return $this->mesclarConfigVisual($codigo, $salvo, []);
    }

    /** Conta slots de produto definidos no fabric_state (cards, zonas ou textos vinculados). */
    public function contarSlotsProduto(array $configVisual): int
    {
        $objects = $configVisual['fabric_state']['objects'] ?? [];
        if (!is_array($objects) || $objects === []) {
            return 0;
        }

        $indices = [];

        foreach ($objects as $obj) {
            if (!is_array($obj)) {
                continue;
            }

            if (!empty($obj['isProductCard'])) {
                $indices[] = (int) ($obj['productIndex'] ?? $obj['zoneId'] ?? 0);
            } elseif (!empty($obj['isProductZone'])) {
                $indices[] = (int) ($obj['zoneId'] ?? 0);
            } elseif (!empty($obj['isDynamicText'])) {
                $indices[] = (int) ($obj['linkedZone'] ?? 0);
            }
        }

        $indices = array_values(array_filter(array_unique($indices), static fn (int $n): bool => $n > 0));

        if ($indices === []) {
            return 0;
        }

        return max($indices);
    }

    public function resolverArquivoTemplate(string $codigoModelo): string
    {
        $modelo = $this->buscarPorCodigo($codigoModelo);
        $arquivo = $modelo['arquivo_template'] ?? $codigoModelo . '.php';
        $caminho = marketing_path('views/templates/' . ltrim($arquivo, '/'));

        if (!is_file($caminho)) {
            throw new RuntimeException('Template do modelo nao encontrado: ' . $arquivo);
        }

        return $caminho;
    }

    public function salvarFundo(int $id, string $formato, string $tmpPath, string $mime): array
    {
        $modelo = $this->buscarPorId($id);
        if ($modelo === null) {
            throw new RuntimeException('Modelo nao encontrado.');
        }

        $formatosValidos = array_keys(marketing_formatos());
        if (!in_array($formato, $formatosValidos, true)) {
            throw new InvalidArgumentException('Formato invalido.');
        }

        $dims = marketing_formatos()[$formato];
        $info = @getimagesize($tmpPath);
        if ($info === false) {
            throw new InvalidArgumentException('Arquivo de imagem invalido.');
        }

        [$width, $height] = $info;
        $targetW = (int) $dims['width'];
        $targetH = (int) $dims['height'];
        $this->validarProporcaoFundo($width, $height, $targetW, $targetH);

        $ext = match ($mime) {
            'image/png'  => 'png',
            'image/jpeg' => 'jpg',
            default      => throw new InvalidArgumentException('Use PNG ou JPEG.'),
        };

        $dir = marketing_path('assets/modelos/fundos');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $codigo = (string) $modelo['codigo'];
        $destino = $dir . '/' . $codigo . '_' . $formato . '.' . $ext;
        $tmpSalvo = $dir . '/.tmp_' . uniqid('', true) . '.' . $ext;
        if (!move_uploaded_file($tmpPath, $tmpSalvo) && !rename($tmpPath, $tmpSalvo)) {
            throw new RuntimeException('Falha ao salvar arquivo de fundo.');
        }

        try {
            $this->normalizarImagemFundo($tmpSalvo, $destino, $mime, $targetW, $targetH);
        } finally {
            if (is_file($tmpSalvo)) {
                @unlink($tmpSalvo);
            }
        }

        $relativo = 'assets/modelos/fundos/' . $codigo . '_' . $formato . '.' . $ext;
        $config = $this->configVisualMerged($modelo);
        $config['fundos'][$formato] = $relativo;

        $st = $this->pdo->prepare('UPDATE modelos_layout SET config_visual = ? WHERE id = ?');
        $st->execute([json_encode($config, JSON_UNESCAPED_UNICODE), $id]);

        $atualizado = $this->buscarPorId($id) ?? $modelo;
        $atualizado['config_visual'] = $this->configVisualMerged($atualizado);

        return $atualizado;
    }

    private function mesclarConfigVisual(string $codigo, array $novo, array $atual): array
    {
        $base = $this->configVisualPadrao($codigo);
        $merged = array_replace_recursive($base, $atual, $novo);

        foreach (['cores', 'textos', 'icones', 'fundos'] as $secao) {
            if (!isset($merged[$secao]) || !is_array($merged[$secao])) {
                $merged[$secao] = $base[$secao] ?? [];
            }
        }

        if (!is_array($merged['icones']['mapa'] ?? null)) {
            $merged['icones']['mapa'] = $base['icones']['mapa'];
        }

        $tipoIcone = (string) ($merged['icones']['tipo'] ?? 'emoji');
        $merged['icones']['tipo'] = in_array($tipoIcone, ['emoji', 'fontawesome'], true) ? $tipoIcone : 'emoji';

        foreach ($merged['cores'] as $chave => $valor) {
            $merged['cores'][$chave] = $this->validarCorHex((string) $valor, (string) $base['cores'][$chave]);
        }

        $merged['textos']['badge_oferta'] = trim((string) ($merged['textos']['badge_oferta'] ?? ''));
        if ($merged['textos']['badge_oferta'] === '') {
            $merged['textos']['badge_oferta'] = $base['textos']['badge_oferta'];
        }

        $merged['textos']['clube_badge'] = trim((string) ($merged['textos']['clube_badge'] ?? ''));
        if ($merged['textos']['clube_badge'] === '') {
            $merged['textos']['clube_badge'] = $base['textos']['clube_badge'];
        }

        $merged['textos']['mostrar_clube'] = (bool) ($merged['textos']['mostrar_clube'] ?? false);
        $merged['icones']['auto'] = (bool) ($merged['icones']['auto'] ?? true);

        if (isset($novo['fabric_state']) && is_array($novo['fabric_state'])) {
            $merged['fabric_state'] = $novo['fabric_state'];
        } elseif (isset($atual['fabric_state']) && is_array($atual['fabric_state'])) {
            $merged['fabric_state'] = $atual['fabric_state'];
        }

        return $merged;
    }

    public function salvarElemento(int $id, string $tmpPath, string $mime): array
    {
        $modelo = $this->buscarPorId($id);
        if ($modelo === null) {
            throw new RuntimeException('Modelo nao encontrado.');
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowed, true)) {
            throw new InvalidArgumentException('Formato nao permitido. Use JPG, PNG ou WebP.');
        }

        $ext = match ($mime) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        $dir = marketing_path('assets/modelos/elementos');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = (int) $modelo['id'] . '_' . time() . '.' . $ext;
        $destino = $dir . '/' . $filename;

        if (!move_uploaded_file($tmpPath, $destino)) {
            if (!rename($tmpPath, $destino)) {
                throw new RuntimeException('Falha ao salvar elemento.');
            }
        }

        $relativoOriginal = 'assets/modelos/elementos/' . $filename;
        $nomeBase = pathinfo($filename, PATHINFO_FILENAME);
        $relativoLimpa = 'assets/modelos/elementos/' . $nomeBase . '.png';
        $status = 'pendente';

        $assistant = new MarketingAssistant();
        $rembg = $assistant->removerFundoImagem($destino, marketing_path($relativoLimpa));
        if ($rembg['ok']) {
            $status = 'ok';
            $url = $relativoLimpa;
        } else {
            $url = $relativoOriginal;
            $status = 'erro';
        }

        return [
            'url'    => $url,
            'status' => $status,
        ];
    }

    private function salvarThumbnailPreview(string $codigo, string $dataUrl): void
    {
        if (!preg_match('#^data:image/(png|jpeg|webp);base64,(.+)$#', $dataUrl, $matches)) {
            return;
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false || $binary === '') {
            return;
        }

        $dir = marketing_path('assets/modelos');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($dir . '/' . $codigo . '.png', $binary);
    }

    private function validarCorHex(string $valor, string $fallback): string
    {
        $valor = trim($valor);
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $valor) === 1) {
            return strtolower($valor);
        }

        return $fallback;
    }

    private function validarProporcaoFundo(int $width, int $height, int $targetW, int $targetH): void
    {
        if ($width < 1 || $height < 1) {
            throw new InvalidArgumentException('Arquivo de imagem invalido.');
        }

        $expected = $targetW / $targetH;
        $actual = $width / $height;
        if (abs($expected - $actual) > 0.02) {
            throw new InvalidArgumentException(
                'Proporcao incorreta. Esperado ' . $targetW . 'x' . $targetH
                . ' (Stories 9:16, quadrado 1:1, etc.), recebido ' . $width . 'x' . $height . '.'
            );
        }
    }

    private function normalizarImagemFundo(
        string $srcPath,
        string $destPath,
        string $mime,
        int $targetW,
        int $targetH
    ): void {
        $info = @getimagesize($srcPath);
        if ($info === false) {
            throw new InvalidArgumentException('Arquivo de imagem invalido.');
        }

        [$srcW, $srcH] = $info;
        if ($srcW === $targetW && $srcH === $targetH) {
            if ($srcPath !== $destPath && !copy($srcPath, $destPath)) {
                throw new RuntimeException('Falha ao salvar arquivo de fundo.');
            }

            return;
        }

        if (!extension_loaded('gd')) {
            if ($srcPath !== $destPath && !copy($srcPath, $destPath)) {
                throw new RuntimeException('Falha ao salvar arquivo de fundo.');
            }

            return;
        }

        $createFn = match ($mime) {
            'image/png'  => 'imagecreatefrompng',
            'image/jpeg' => 'imagecreatefromjpeg',
            default      => throw new InvalidArgumentException('Use PNG ou JPEG.'),
        };

        $srcImg = @$createFn($srcPath);
        if ($srcImg === false) {
            throw new InvalidArgumentException('Nao foi possivel ler a imagem.');
        }

        $dstImg = imagecreatetruecolor($targetW, $targetH);
        if ($dstImg === false) {
            imagedestroy($srcImg);
            throw new RuntimeException('Falha ao preparar imagem de fundo.');
        }

        if ($mime === 'image/png') {
            imagealphablending($dstImg, false);
            imagesavealpha($dstImg, true);
            $transparent = imagecolorallocatealpha($dstImg, 0, 0, 0, 127);
            imagefill($dstImg, 0, 0, $transparent);
        }

        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $targetW, $targetH, $srcW, $srcH);

        $saved = match ($mime) {
            'image/png'  => imagepng($dstImg, $destPath),
            'image/jpeg' => imagejpeg($dstImg, $destPath, 92),
            default      => false,
        };

        imagedestroy($srcImg);
        imagedestroy($dstImg);

        if (!$saved) {
            throw new RuntimeException('Falha ao salvar imagem redimensionada.');
        }
    }

    private function normalizarLinha(array $modelo): array
    {
        $formatos = json_decode((string) ($modelo['formatos_suportados'] ?? '[]'), true);
        $modelo['formatos_suportados'] = is_array($formatos) ? $formatos : [];

        $config = json_decode((string) ($modelo['config_visual'] ?? ''), true);
        $modelo['config_visual'] = is_array($config) ? $config : null;

        return $modelo;
    }
}
