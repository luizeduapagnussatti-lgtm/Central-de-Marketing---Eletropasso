<?php
declare(strict_types=1);

/**
 * Carrega variaveis do arquivo .env para $_ENV e putenv.
 */
function marketing_load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\"'");
        $_ENV[$key] = $value;
        putenv("{$key}={$value}");
    }
}

function marketing_env(string $key, ?string $default = null): ?string
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }

    return (string) $value;
}

function marketing_root_path(): string
{
    return dirname(__DIR__);
}

function marketing_path(string $relative = ''): string
{
    $root = marketing_root_path();
    if ($relative === '') {
        return $root;
    }

    return $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
}

function marketing_url(string $path = ''): string
{
    $base = rtrim((string) marketing_env('APP_BASE_URL', '/'), '/');
    $path = ltrim($path, '/');

    return $path === '' ? $base . '/' : $base . '/' . $path;
}

/** Formatos de encarte suportados com dimensoes. */
function marketing_formatos(): array
{
    return [
        '9x16'   => ['label' => 'Stories (9:16)', 'width' => 1080, 'height' => 1920, 'scale' => 2],
        'status' => ['label' => 'Status WhatsApp (9:16)', 'width' => 1080, 'height' => 1920, 'scale' => 2],
        '1x1'    => ['label' => 'Feed Quadrado (1:1)', 'width' => 1080, 'height' => 1080, 'scale' => 2],
        '16x9'   => ['label' => 'Paisagem (16:9)', 'width' => 1920, 'height' => 1080, 'scale' => 2],
        'a4'     => ['label' => 'Impressao A4', 'width' => 2480, 'height' => 3508, 'scale' => 1],
    ];
}

function marketing_json_response(bool $success, mixed $data = null, ?string $error = null, int $httpCode = 200): never
{
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'data'    => $data,
        'error'   => $error,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function marketing_format_money(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function marketing_format_date_br(?string $date): string
{
    if ($date === null || $date === '') {
        return '';
    }
    $ts = strtotime($date);

    return $ts ? date('d/m/Y', $ts) : '';
}

/** Caminho relativo da logo por variante (preta=fundo claro, branca=fundo escuro). */
function marketing_logo_relativo(string $variante = 'preta'): string
{
    $map = [
        'preta'  => 'assets/brand/logo_eletropasso_preta.png',
        'branca' => 'assets/brand/logo_eletropasso_branca.png',
        'padrao' => 'assets/brand/logo_eletropasso.png',
    ];

    $preferida = $map[$variante] ?? $map['padrao'];
    if (is_file(marketing_path($preferida))) {
        return $preferida;
    }

    foreach ($map as $fallback) {
        if (is_file(marketing_path($fallback))) {
            return $fallback;
        }
    }

    return $preferida;
}

/**
 * Caminho relativo da foto do produto (prioriza PNG sem fundo / rembg).
 */
function marketing_encarte_foto_caminho(array $item): string
{
    $limpa = trim((string) ($item['caminho_foto_limpa'] ?? ''));
    if ($limpa !== '') {
        return $limpa;
    }

    return trim((string) ($item['caminho_foto_original'] ?? ''));
}

/**
 * URL para injetar em <img src> no render Puppeteer (file://).
 * Nao usa canvas nem background-image — so o PNG transparente na tag img.
 */
function marketing_encarte_foto_src(string $base_path, array $item): string
{
    return marketing_encarte_img_src($base_path, marketing_encarte_foto_caminho($item));
}

/** Resolve caminho relativo ou absoluto para URL file:// usada nos templates. */
function marketing_encarte_img_src(string $base_path, string $caminho): string
{
    if ($caminho === '') {
        return '';
    }

    $full = str_starts_with($caminho, $base_path)
        ? $caminho
        : $base_path . '/' . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $caminho), DIRECTORY_SEPARATOR);

    if (!is_file($full)) {
        return '';
    }

    return 'file:///' . str_replace('\\', '/', realpath($full));
}

/** Caminho relativo do palco estatico por formato (config_visual.fundos). */
function marketing_encarte_fundo_caminho(array $modelo_config, string $formato): string
{
    $fundos = $modelo_config['fundos'] ?? [];
    if (!is_array($fundos)) {
        return '';
    }

    if (!empty($fundos[$formato])) {
        return trim((string) $fundos[$formato]);
    }

    foreach ($fundos as $caminho) {
        $caminho = trim((string) $caminho);
        if ($caminho !== '') {
            return $caminho;
        }
    }

    return '';
}

/** URL file:// do palco estatico para background-image do encarte. */
function marketing_encarte_fundo_src(string $base_path, array $modelo_config, string $formato): string
{
    return marketing_encarte_img_src($base_path, marketing_encarte_fundo_caminho($modelo_config, $formato));
}

/** URL file:// de stylesheet local para templates Puppeteer. */
function marketing_encarte_stylesheet_url(string $base_path, string $relativo): string
{
    return marketing_encarte_img_src($base_path, $relativo);
}

/**
 * filter CSS para foto flutuante: drop-shadow ignora transparencia do PNG
 * e projeta sombra apenas nos pixels visiveis do produto.
 * Preferir classes .foto-flutuante do encarte-utilities.css quando possivel.
 */
function marketing_encarte_foto_filter_css(string $intensidade = 'media'): string
{
    return match ($intensidade) {
        'forte' => 'drop-shadow(-15px 20px 15px rgba(0, 0, 0, 0.9)) brightness(1.1)',
        'media' => 'drop-shadow(0 18px 22px rgba(0, 0, 0, 0.45)) brightness(1.05)',
        'suave' => 'drop-shadow(0 12px 16px rgba(0, 0, 0, 0.25))',
        default => 'drop-shadow(0 18px 22px rgba(0, 0, 0, 0.45)) brightness(1.05)',
    };
}

/** Config visual merged do modelo (JSON do banco + defaults). */
function marketing_modelo_config(array $modelo): array
{
    return (new ModeloLayoutService())->configVisualMerged($modelo);
}

/** Descricao do modelo: banco com fallback por codigo. */
function marketing_modelo_descricao(array|string $modeloOuCodigo): string
{
    if (is_array($modeloOuCodigo)) {
        $desc = trim((string) ($modeloOuCodigo['descricao'] ?? ''));
        if ($desc !== '') {
            return $desc;
        }
        $codigo = (string) ($modeloOuCodigo['codigo'] ?? '');
    } else {
        $codigo = $modeloOuCodigo;
    }

    return match ($codigo) {
        'modelo_02' => 'Feed quadrado 1:1 com 3 produtos no topo e 2 destaques grandes na base escura, sem bordas.',
        'modelo_03' => 'Encarte vertical premium com fundo escuro, titulo 3D, grid de 6 produtos com features e rodape institucional.',
        default     => 'Layout com produtos flutuantes, precos em destaque e identidade Eletropasso.',
    };
}

/** Icone automatico baseado no nome do produto e config do modelo. */
function marketing_modelo_icone(string $nome, array $modelo_config): string
{
    $icones = $modelo_config['icones'] ?? [];
    if (!($icones['auto'] ?? true)) {
        return '';
    }

    $mapa = is_array($icones['mapa'] ?? null) ? $icones['mapa'] : [];
    $nomeLower = strtolower($nome);

    if (str_contains($nomeLower, 'smart') || str_contains($nomeLower, 'wi-fi') || str_contains($nomeLower, 'wifi')) {
        return (string) ($mapa['wifi'] ?? '📶');
    }
    if (str_contains($nomeLower, 'led') || str_contains($nomeLower, 'lampada')) {
        return (string) ($mapa['led'] ?? '💡');
    }
    if (str_contains($nomeLower, 'tomada') || str_contains($nomeLower, 'interruptor')) {
        return (string) ($mapa['tomada'] ?? '🔌');
    }
    if (str_contains($nomeLower, 'fio') || str_contains($nomeLower, 'cabo') || str_contains($nomeLower, 'disjuntor')) {
        return (string) ($mapa['cabo'] ?? '⚡');
    }

    return '';
}

function marketing_modelo_preview_src(string $codigo): string
{
    $relativo = 'assets/modelos/' . $codigo . '.png';
    if (!is_file(marketing_path($relativo))) {
        return '';
    }

    $full = marketing_path($relativo);
    $versao = is_file($full) ? (string) filemtime($full) : '';

    return $versao !== '' ? $relativo . '?v=' . $versao : $relativo;
}

function marketing_modelo_formatos_label(array $modelo): string
{
    $formatos = $modelo['formatos_suportados'] ?? [];
    if (!is_array($formatos)) {
        $formatos = json_decode((string) ($modelo['formatos_suportados'] ?? '[]'), true);
    }
    if (!is_array($formatos) || $formatos === []) {
        return '';
    }

    $labels = marketing_formatos();
    $nomes = [];
    foreach ($formatos as $codigoFmt) {
        $nomes[] = $labels[$codigoFmt]['label'] ?? $codigoFmt;
    }

    return implode(' · ', array_slice($nomes, 0, 3)) . (count($nomes) > 3 ? ' +' . (count($nomes) - 3) : '');
}

/**
 * Converte objeto Fabric.js (fabric_state) em HTML/CSS absoluto para Puppeteer.
 */
function ep_get_dynamic_text_value(string $textType, int $linkedZone, array $itens): string
{
    $idx = $linkedZone - 1;
    $item = $itens[$idx] ?? null;

    if ($item === null) {
        return match ($textType) {
            'nome_produto' => '[NOME_PRODUTO]',
            'preco_normal' => '[PRECO_NORMAL]',
            'preco_promo'  => '[PRECO_PROMO]',
            'unidade'      => '[UNIDADE]',
            default        => '',
        };
    }

    return match ($textType) {
        'nome_produto' => htmlspecialchars((string) ($item['nome_comercial'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'preco_normal' => 'R$ ' . number_format((float) ($item['preco_normal'] ?? 0), 2, ',', '.'),
        'preco_promo'  => 'R$ ' . number_format((float) ($item['preco_promocional'] ?? 0), 2, ',', '.'),
        'unidade'      => htmlspecialchars((string) ($item['unidade'] ?? 'und'), ENT_QUOTES, 'UTF-8'),
        default        => '',
    };
}

function ep_render_fabric_object(array $obj, string $base_path, array $itens = []): string
{
    $type = (string) ($obj['type'] ?? '');
    $name = (string) ($obj['name'] ?? '');
    $left = (float) ($obj['left'] ?? 0);
    $top = (float) ($obj['top'] ?? 0);
    $angle = (float) ($obj['angle'] ?? 0);
    $opacity = (float) ($obj['opacity'] ?? 1);
    $scaleX = (float) ($obj['scaleX'] ?? 1);
    $scaleY = (float) ($obj['scaleY'] ?? 1);
    $visible = ($obj['visible'] ?? true) !== false;

    if (!$visible || $name === 'fundo-editor') {
        return '';
    }

    $transform = $angle !== 0.0 ? ' transform:rotate(' . $angle . 'deg); transform-origin:top left;' : '';
    $baseStyle = sprintf(
        'position:absolute;left:%spx;top:%spx;opacity:%s;%s',
        $left,
        $top,
        $opacity,
        $transform
    );

    if (!empty($obj['isProductZone'])) {
        $zoneId = (int) ($obj['zoneId'] ?? 0);
        $width = max(1, (float) ($obj['width'] ?? 0) * abs($scaleX));
        $height = max(1, (float) ($obj['height'] ?? 0) * abs($scaleY));

        $idx = $zoneId - 1;
        $item = ($idx >= 0 && isset($itens[$idx])) ? $itens[$idx] : null;

        if ($item === null) {
            return sprintf(
                '<div class="fabric-zona-produto" data-zone-id="%d" style="%swidth:%spx;height:%spx;opacity:0;pointer-events:none;"></div>',
                $zoneId,
                $baseStyle,
                $width,
                $height
            );
        }

        $foto = marketing_encarte_foto_src($base_path, $item);
        if ($foto === '') {
            return '';
        }

        $alt = htmlspecialchars((string) ($item['nome_comercial'] ?? 'Produto'), ENT_QUOTES, 'UTF-8');
        $fotoEsc = htmlspecialchars($foto, ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<img class="foto-flutuante foto-flutuante--forte fabric-zona-produto" data-zone-id="%d" src="%s" alt="%s" style="%swidth:%spx;height:%spx;object-fit:contain;">',
            $zoneId,
            $fotoEsc,
            $alt,
            $baseStyle,
            $width,
            $height
        );
    }

    if ($type === 'image') {
        $srcRaw = (string) ($obj['src'] ?? '');
        if ($srcRaw === '') {
            return '';
        }

        $srcRel = $srcRaw;
        if (str_contains($srcRaw, 'assets/')) {
            $srcRel = substr($srcRaw, (int) strpos($srcRaw, 'assets/'));
        } else {
            $srcRel = preg_replace('#^(https?://[^/]+)?/?#', '', $srcRaw) ?? $srcRaw;
        }
        $srcRel = ltrim(str_replace('\\', '/', $srcRel), '/');
        $src = marketing_encarte_img_src($base_path, $srcRel);
        if ($src === '') {
            return '';
        }

        $width = max(1, (float) ($obj['width'] ?? 0) * abs($scaleX));
        $height = max(1, (float) ($obj['height'] ?? 0) * abs($scaleY));
        $alt = htmlspecialchars($name !== '' ? $name : 'Elemento', ENT_QUOTES, 'UTF-8');
        $srcEsc = htmlspecialchars($src, ENT_QUOTES, 'UTF-8');

        $extraClass = $name === 'palco' ? ' encarte-palco-img' : '';
        $filter = $name === 'elemento-produto' || str_starts_with($name, 'elemento')
            ? ' style="' . $baseStyle . 'width:' . $width . 'px;height:' . $height . 'px;"'
            : ' style="' . $baseStyle . 'width:' . $width . 'px;height:' . $height . 'px;"';

        $imgClass = ($name === 'elemento-produto' || str_starts_with($name, 'elemento'))
            ? ' class="foto-flutuante foto-flutuante--forte' . $extraClass . '"'
            : ' class="fabric-img' . $extraClass . '"';

        return '<img' . $imgClass . ' src="' . $srcEsc . '" alt="' . $alt . '"' . $filter . '>';
    }

    if ($type === 'rect') {
        $width = max(1, (float) ($obj['width'] ?? 0) * abs($scaleX));
        $height = max(1, (float) ($obj['height'] ?? 0) * abs($scaleY));
        $fill = htmlspecialchars((string) ($obj['fill'] ?? 'transparent'), ENT_QUOTES, 'UTF-8');
        $rx = (float) ($obj['rx'] ?? 0);
        $radius = $rx > 0 ? 'border-radius:' . $rx . 'px;' : '';

        return sprintf(
            '<div class="fabric-rect" data-name="%s" style="%swidth:%spx;height:%spx;background-color:%s;%s"></div>',
            htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            $baseStyle,
            $width,
            $height,
            $fill,
            $radius
        );
    }

    if ($type === 'i-text' || $type === 'text' || $type === 'textbox') {
        if (!empty($obj['isDynamicText'])) {
            $textType = (string) ($obj['textType'] ?? '');
            $linkedZone = (int) ($obj['linkedZone'] ?? 1);
            $text = ep_get_dynamic_text_value($textType, $linkedZone, $itens);
        } else {
            $text = htmlspecialchars((string) ($obj['text'] ?? ''), ENT_QUOTES, 'UTF-8');
        }

        $fill = htmlspecialchars((string) ($obj['fill'] ?? '#ffffff'), ENT_QUOTES, 'UTF-8');
        $fontSize = max(8, (float) ($obj['fontSize'] ?? 16) * abs($scaleY));
        $fontWeight = (int) ($obj['fontWeight'] ?? 400);
        $fontFamily = htmlspecialchars((string) ($obj['fontFamily'] ?? 'Segoe UI, Arial, sans-serif'), ENT_QUOTES, 'UTF-8');
        $textAlign = htmlspecialchars((string) ($obj['textAlign'] ?? 'left'), ENT_QUOTES, 'UTF-8');
        $width = (float) ($obj['width'] ?? 0);
        $widthCss = $width > 0 ? 'width:' . ($width * abs($scaleX)) . 'px;' : '';
        $linethroughCss = !empty($obj['linethrough']) ? 'text-decoration:line-through;' : '';

        $class = 'fabric-text';
        if (in_array($name, ['titulo_linha1', 'titulo_linha2'], true)) {
            $class .= ' font-display preco-3d';
        }
        if (!empty($obj['isDynamicText']) && ($obj['textType'] ?? '') === 'preco_promo') {
            $class .= ' font-display preco-3d';
        }

        return sprintf(
            '<div class="%s" data-name="%s" style="%s%sfont-size:%spx;font-weight:%d;font-family:%s;color:%s;text-align:%s;white-space:pre-wrap;line-height:1.1;%s">%s</div>',
            $class,
            htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            $baseStyle,
            $widthCss,
            $fontSize,
            $fontWeight,
            $fontFamily,
            $fill,
            $textAlign,
            $linethroughCss,
            nl2br($text)
        );
    }

    return '';
}
