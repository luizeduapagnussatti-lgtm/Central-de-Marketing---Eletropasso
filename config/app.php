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

/** URL de asset de marca com cache-bust por filemtime. */
function marketing_brand_asset_url(string $relative): string
{
    $relative = ltrim(str_replace('\\', '/', $relative), '/');
    $full = marketing_path($relative);
    if (!is_file($full)) {
        return $relative;
    }

    return $relative . '?v=' . filemtime($full);
}

/** Caminho relativo da logo horizontal da Central de Marketing (navbar). */
function marketing_logo_header_relativo(): string
{
    $preferida = 'assets/brand/logo_central_marketing.svg';
    if (is_file(marketing_path($preferida))) {
        return $preferida;
    }

    return marketing_logo_relativo('preta');
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

    return 'file:///' . implode('/', array_map('rawurlencode', explode('/', str_replace('\\', '/', realpath($full)))));
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
 * Detecta placeholders legados salvos como texto estatico no Fabric.
 */
function ep_match_static_placeholder_text(string $text): ?string
{
    $normalized = strtoupper(trim($text));

    return match ($normalized) {
        '[NOME_PRODUTO]', '[NOME]' => 'nome_produto',
        '[PRECO_NORMAL]', '[PRECO_DE]' => 'preco_normal',
        '[PRECO_PROMO]', '[PRECO_POR]' => 'preco_promo',
        '[UNIDADE]' => 'unidade',
        '[TITULO_CAMPANHA]' => 'titulo_campanha',
        '[VALIDADE]' => 'validade_periodo',
        '[RODAPE]' => 'texto_legal_rodape',
        default => null,
    };
}

function ep_is_campanha_text_type(string $textType): bool
{
    return in_array($textType, ['titulo_campanha', 'validade_periodo', 'texto_legal_rodape'], true);
}

function ep_campanha_text_type_from_name(string $name): ?string
{
    return match ($name) {
        'titulo_linha1'    => 'titulo_campanha',
        'validade_periodo' => 'validade_periodo',
        'texto_legal'      => 'texto_legal_rodape',
        default            => null,
    };
}

function ep_format_validade_periodo(?string $inicio, ?string $fim): string
{
    $di = marketing_format_date_br($inicio);
    $df = marketing_format_date_br($fim);

    if ($di !== '' && $df !== '') {
        return 'Valido de ' . $di . ' ate ' . $df;
    }
    if ($df !== '') {
        return 'Valido ate ' . $df;
    }
    if ($di !== '') {
        return 'Valido a partir de ' . $di;
    }

    return '';
}

function ep_get_campanha_text_value(string $textType, array $encarte_data, array $render_opts = []): string
{
    $encarte = $render_opts['encarte_data'] ?? $encarte_data;
    if (!is_array($encarte)) {
        $encarte = [];
    }

    return match ($textType) {
        'titulo_campanha' => (static function () use ($encarte, $render_opts): string {
            $titulo = trim((string) ($encarte['titulo_campanha'] ?? ''));
            if ($titulo === '') {
                $modeloConfig = is_array($render_opts['modelo_config'] ?? null) ? $render_opts['modelo_config'] : [];
                $titulo = trim((string) ($modeloConfig['textos']['titulo_linha1'] ?? ''));
            }

            return htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
        })(),
        'validade_periodo' => htmlspecialchars(
            ep_format_validade_periodo(
                isset($encarte['validade_inicio']) ? (string) $encarte['validade_inicio'] : null,
                isset($encarte['validade_fim']) ? (string) $encarte['validade_fim'] : null
            ),
            ENT_QUOTES,
            'UTF-8'
        ),
        'texto_legal_rodape' => htmlspecialchars(
            trim((string) ($encarte['texto_legal_rodape'] ?? $render_opts['texto_legal_rodape'] ?? '')),
            ENT_QUOTES,
            'UTF-8'
        ),
        default => '',
    };
}

/** Resolve conteudo de texto Fabric (campanha, produto ou estatico). */
function ep_resolve_fabric_text_content(array $obj, array $itens, array $render_opts): string
{
    $name = (string) ($obj['name'] ?? '');

    if (!empty($obj['isCampanhaText'])) {
        $textType = (string) ($obj['textType'] ?? ep_campanha_text_type_from_name($name) ?? '');
        if ($textType !== '' && ep_is_campanha_text_type($textType)) {
            return ep_get_campanha_text_value($textType, [], $render_opts);
        }
    }

    if (!empty($obj['isDynamicText'])) {
        $textType = (string) ($obj['textType'] ?? '');
        if (ep_is_campanha_text_type($textType)) {
            return ep_get_campanha_text_value($textType, [], $render_opts);
        }

        $linkedZone = (int) ($obj['linkedZone'] ?? 1);

        return ep_get_dynamic_text_value($textType, $linkedZone, $itens, $render_opts);
    }

    $rawText = (string) ($obj['text'] ?? '');
    $textTypeStatic = ep_match_static_placeholder_text($rawText);
    if ($textTypeStatic !== null) {
        if (ep_is_campanha_text_type($textTypeStatic)) {
            return ep_get_campanha_text_value($textTypeStatic, [], $render_opts);
        }

        $linkedZone = (int) ($obj['linkedZone'] ?? 1);

        return ep_get_dynamic_text_value($textTypeStatic, $linkedZone, $itens, $render_opts);
    }

    $campanhaByName = ep_campanha_text_type_from_name($name);
    if ($campanhaByName !== null) {
        $resolved = ep_get_campanha_text_value($campanhaByName, [], $render_opts);
        if ($resolved !== '') {
            return $resolved;
        }
    }

    return htmlspecialchars($rawText, ENT_QUOTES, 'UTF-8');
}

/**
 * Converte objeto Fabric.js (fabric_state) em HTML/CSS absoluto para Puppeteer.
 */

/**
 * Coleta slots de produto no canvas (cards e zonas avulsas) para mapear item 1..N pela ordem visual.
 *
 * @return array<int, int> mapa zoneId/productIndex -> indice 0-based em $itens
 */
function ep_build_encarte_product_zone_map(array $fabric_objects): array
{
    $slots = [];

    foreach ($fabric_objects as $obj) {
        if (!is_array($obj)) {
            continue;
        }

        if (!empty($obj['isProductCard']) && ($obj['type'] ?? '') === 'group') {
            $key = (int) ($obj['productIndex'] ?? $obj['zoneId'] ?? 0);
            if ($key > 0) {
                $bbox = ep_fabric_bbox($obj);
                $slots[$key] = ['top' => $bbox['top'], 'left' => $bbox['left']];
            }
            continue;
        }

        if (!empty($obj['isProductZone']) && empty($obj['isProductCard'])) {
            $key = (int) ($obj['zoneId'] ?? 0);
            if ($key > 0 && !isset($slots[$key])) {
                $bbox = ep_fabric_bbox($obj);
                $slots[$key] = ['top' => $bbox['top'], 'left' => $bbox['left']];
            }
        }
    }

    if ($slots === []) {
        return [];
    }

    uasort(
        $slots,
        static fn (array $a, array $b): int => $a['top'] <=> $b['top'] ?: $a['left'] <=> $b['left']
    );

    $map = [];
    $itemIndex = 0;
    foreach (array_keys($slots) as $zoneKey) {
        $map[(int) $zoneKey] = $itemIndex++;
    }

    return $map;
}

function ep_resolve_encarte_item_index(int $zoneOrProductIndex, array $render_opts = []): int
{
    $map = $render_opts['product_zone_map'] ?? [];
    if (is_array($map) && $map !== [] && isset($map[$zoneOrProductIndex])) {
        return (int) $map[$zoneOrProductIndex];
    }

    return $zoneOrProductIndex - 1;
}

/** @return array<string, mixed>|null */
function ep_get_encarte_item(int $zoneOrProductIndex, array $itens, array $render_opts = []): ?array
{
    $idx = ep_resolve_encarte_item_index($zoneOrProductIndex, $render_opts);
    if ($idx < 0 || !isset($itens[$idx])) {
        return null;
    }

    return $itens[$idx];
}

function ep_get_dynamic_text_value(string $textType, int $linkedZone, array $itens, array $render_opts = []): string
{
    $item = ep_get_encarte_item($linkedZone, $itens, $render_opts);

    if ($item === null) {
        return '';
    }

    return match ($textType) {
        'nome_produto' => htmlspecialchars((string) ($item['nome_comercial'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'preco_normal' => 'R$ ' . number_format((float) ($item['preco_normal'] ?? 0), 2, ',', '.'),
        'preco_promo'  => 'R$ ' . number_format((float) ($item['preco_promocional'] ?? 0), 2, ',', '.'),
        'unidade'      => htmlspecialchars((string) ($item['unidade'] ?? 'und'), ENT_QUOTES, 'UTF-8'),
        default        => '',
    };
}

/**
 * Calcula bounding box top-left a partir das coordenadas Fabric (considera originX/originY).
 *
 * @return array{left: float, top: float, width: float, height: float}
 */
function ep_fabric_bbox(array $obj): array
{
    $left = (float) ($obj['left'] ?? 0);
    $top = (float) ($obj['top'] ?? 0);
    $scaleX = (float) ($obj['scaleX'] ?? 1);
    $scaleY = (float) ($obj['scaleY'] ?? 1);
    $width = max(1, (float) ($obj['width'] ?? 0) * abs($scaleX));
    $height = max(1, (float) ($obj['height'] ?? 0) * abs($scaleY));
    $originX = (string) ($obj['originX'] ?? 'left');
    $originY = (string) ($obj['originY'] ?? 'top');

    if ($originX === 'center') {
        $left -= $width / 2;
    } elseif ($originX === 'right') {
        $left -= $width;
    }

    if ($originY === 'center') {
        $top -= $height / 2;
    } elseif ($originY === 'bottom') {
        $top -= $height;
    }

    return [
        'left'   => $left,
        'top'    => $top,
        'width'  => $width,
        'height' => $height,
    ];
}

/** CSS text-shadow a partir do objeto shadow do Fabric. */
function ep_fabric_text_shadow_css(array $obj): string
{
    if (empty($obj['shadow']) || !is_array($obj['shadow'])) {
        return '';
    }

    $sx = (float) ($obj['shadow']['offsetX'] ?? 0);
    $sy = (float) ($obj['shadow']['offsetY'] ?? 0);
    $blur = (float) ($obj['shadow']['blur'] ?? 0);
    $color = (string) ($obj['shadow']['color'] ?? 'rgba(0,0,0,0.45)');

    return sprintf('text-shadow:%spx %spx %spx %s;', $sx, $sy, $blur, $color);
}

/**
 * Posicao absoluta de um filho dentro de um grupo Fabric.
 *
 * @return array{left: float, top: float, width: float, height: float}
 */
function ep_fabric_group_child_bbox(array $group, array $child): array
{
    $groupBbox = ep_fabric_bbox($group);
    $centerX = $groupBbox['left'] + ($groupBbox['width'] / 2);
    $centerY = $groupBbox['top'] + ($groupBbox['height'] / 2);
    $gScaleX = abs((float) ($group['scaleX'] ?? 1));
    $gScaleY = abs((float) ($group['scaleY'] ?? 1));
    $cScaleX = abs((float) ($child['scaleX'] ?? 1));
    $cScaleY = abs((float) ($child['scaleY'] ?? 1));

    $relLeft = (float) ($child['left'] ?? 0);
    $relTop = (float) ($child['top'] ?? 0);
    $width = max(1, (float) ($child['width'] ?? 0) * $cScaleX * $gScaleX);
    $height = max(1, (float) ($child['height'] ?? 0) * $cScaleY * $gScaleY);

    $absLeft = $centerX + ($relLeft * $gScaleX);
    $absTop = $centerY + ($relTop * $gScaleY);

    $originX = (string) ($child['originX'] ?? 'left');
    $originY = (string) ($child['originY'] ?? 'top');
    if ($originX === 'center') {
        $absLeft -= $width / 2;
    } elseif ($originX === 'right') {
        $absLeft -= $width;
    }
    if ($originY === 'center') {
        $absTop -= $height / 2;
    } elseif ($originY === 'bottom') {
        $absTop -= $height;
    }

    return [
        'left'   => $absLeft,
        'top'    => $absTop,
        'width'  => $width,
        'height' => $height,
    ];
}

/** Renderiza card de produto completo (grupo Fabric com foto + textos). */
function ep_render_product_card(array $group, string $base_path, array $itens, array $render_opts = []): string
{
    $productIndex = (int) ($group['productIndex'] ?? $group['zoneId'] ?? 1);
    $item = ep_get_encarte_item($productIndex, $itens, $render_opts);
    $html = '';

    $hasFotoPart = false;
    foreach ($group['objects'] ?? [] as $candidate) {
        if (!is_array($candidate)) {
            continue;
        }
        $candidatePart = (string) ($candidate['cardPart'] ?? '');
        if ($candidatePart === 'foto') {
            $hasFotoPart = true;
            break;
        }
        if ($candidatePart === '' && !empty($candidate['isProductZone']) && empty($candidate['isProductCard'])) {
            $hasFotoPart = true;
            break;
        }
    }

    $fotoFallbackUsed = false;

    foreach ($group['objects'] ?? [] as $child) {
        if (!is_array($child)) {
            continue;
        }

        $part = (string) ($child['cardPart'] ?? '');
        if ($part === 'label' || $part === 'frame') {
            continue;
        }

        if ($part === '' && !empty($child['isProductZone']) && empty($child['isProductCard'])) {
            $part = 'foto';
        }

        if ($part === '' && !$hasFotoPart && !$fotoFallbackUsed && ($child['type'] ?? '') === 'rect') {
            $part = 'foto';
            $fotoFallbackUsed = true;
        }

        if ($part === '' && !empty($child['isDynamicText'])) {
            $part = (string) ($child['textType'] ?? '');
        }

        if ($part === '') {
            continue;
        }

        $childBbox = ep_fabric_group_child_bbox($group, $child);
        $baseStyle = sprintf(
            'position:absolute;left:%spx;top:%spx;',
            $childBbox['left'],
            $childBbox['top']
        );

        if ($part === 'foto') {
            if ($item === null) {
                continue;
            }

            $foto = marketing_encarte_foto_src($base_path, $item);
            if ($foto === '') {
                error_log("encarte render: produto {$productIndex} sem foto ou caminho invalido");
                continue;
            }

            $alt = htmlspecialchars((string) ($item['nome_comercial'] ?? 'Produto'), ENT_QUOTES, 'UTF-8');
            $fotoEsc = htmlspecialchars($foto, ENT_QUOTES, 'UTF-8');
            $html .= sprintf(
                '<img class="foto-flutuante foto-flutuante--forte fabric-zona-produto" data-zone-id="%d" src="%s" alt="%s" style="%swidth:%spx;height:%spx;object-fit:contain;">',
                $productIndex,
                $fotoEsc,
                $alt,
                $baseStyle,
                $childBbox['width'],
                $childBbox['height']
            );
            continue;
        }

        if (!empty($child['isDynamicText'])) {
            $textType = (string) ($child['textType'] ?? $part);
            $text = ep_get_dynamic_text_value($textType, $productIndex, $itens, $render_opts);
            $fill = htmlspecialchars((string) ($child['fill'] ?? '#ffffff'), ENT_QUOTES, 'UTF-8');
            $gScaleY = abs((float) ($group['scaleY'] ?? 1));
            $cScaleY = abs((float) ($child['scaleY'] ?? 1));
            $fontSize = max(8, (float) ($child['fontSize'] ?? 16) * $cScaleY * $gScaleY);
            $fontWeight = (int) ($child['fontWeight'] ?? 400);
            $fontFamily = htmlspecialchars((string) ($child['fontFamily'] ?? 'Segoe UI, Arial, sans-serif'), ENT_QUOTES, 'UTF-8');
            $textAlign = htmlspecialchars((string) ($child['textAlign'] ?? 'left'), ENT_QUOTES, 'UTF-8');
            $cScaleX = abs((float) ($child['scaleX'] ?? 1));
            $gScaleX = abs((float) ($group['scaleX'] ?? 1));
            $width = (float) ($child['width'] ?? 0);
            $widthCss = $width > 0 ? 'width:' . ($width * $cScaleX * $gScaleX) . 'px;' : '';
            $linethroughCss = !empty($child['linethrough']) ? 'text-decoration:line-through;' : '';
            $shadowCss = ep_fabric_text_shadow_css($child);

            $class = 'fabric-text';
            if ($textType === 'preco_promo') {
                $class .= ' font-display preco-3d';
            }

            $html .= sprintf(
                '<div class="%s" data-card-part="%s" data-product-index="%d" style="%s%sfont-size:%spx;font-weight:%d;font-family:%s;color:%s;text-align:%s;white-space:pre-wrap;line-height:1.1;%s%s">%s</div>',
                $class,
                htmlspecialchars($part, ENT_QUOTES, 'UTF-8'),
                $productIndex,
                $baseStyle,
                $widthCss,
                $fontSize,
                $fontWeight,
                $fontFamily,
                $fill,
                $textAlign,
                $linethroughCss,
                $shadowCss,
                nl2br($text)
            );
        }
    }

    return $html;
}

function ep_render_fabric_object(array $obj, string $base_path, array $itens = [], array $render_opts = []): string
{
    $type = (string) ($obj['type'] ?? '');
    $name = (string) ($obj['name'] ?? '');
    $visible = ($obj['visible'] ?? true) !== false;

    if (!$visible || $name === 'fundo-editor') {
        return '';
    }

    if (!empty($obj['isProductCard']) && $type === 'group') {
        return ep_render_product_card($obj, $base_path, $itens, $render_opts);
    }

    $bbox = ep_fabric_bbox($obj);
    $left = $bbox['left'];
    $top = $bbox['top'];
    $angle = (float) ($obj['angle'] ?? 0);
    $opacity = (float) ($obj['opacity'] ?? 1);

    $transform = $angle !== 0.0 ? ' transform:rotate(' . $angle . 'deg); transform-origin:top left;' : '';
    $baseStyle = sprintf(
        'position:absolute;left:%spx;top:%spx;opacity:%s;%s',
        $left,
        $top,
        $opacity,
        $transform
    );

    if (!empty($obj['isProductZone']) && empty($obj['isProductCard'])) {
        $zoneId = (int) ($obj['zoneId'] ?? 0);
        $width = $bbox['width'];
        $height = $bbox['height'];

        $item = ep_get_encarte_item($zoneId, $itens, $render_opts);

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

        $srcRaw = strtok($srcRaw, '?') ?: $srcRaw;

        $srcRel = $srcRaw;
        if (str_contains($srcRaw, 'assets/')) {
            $srcRel = substr($srcRaw, (int) strpos($srcRaw, 'assets/'));
        } else {
            $srcRel = preg_replace('#^(https?://[^/]+)?/?#', '', $srcRaw) ?? $srcRaw;
        }
        $srcRel = ltrim(str_replace('\\', '/', $srcRel), '/');
        $src = marketing_encarte_img_src($base_path, $srcRel);

        if ($src === '' && $name === 'palco') {
            $modeloConfig = is_array($render_opts['modelo_config'] ?? null) ? $render_opts['modelo_config'] : [];
            $formato = (string) ($render_opts['formato'] ?? '9x16');
            $fallbackRel = marketing_encarte_fundo_caminho($modeloConfig, $formato);
            if ($fallbackRel !== '') {
                $src = marketing_encarte_img_src($base_path, $fallbackRel);
            }
        }

        if ($src === '') {
            if ($name === 'palco') {
                error_log('[Central Marketing] Palco sem src resolvido: ' . $srcRel);
            }

            return '';
        }

        $width = $bbox['width'];
        $height = $bbox['height'];
        $alt = htmlspecialchars($name !== '' ? $name : 'Elemento', ENT_QUOTES, 'UTF-8');
        $srcEsc = htmlspecialchars($src, ENT_QUOTES, 'UTF-8');

        $extraClass = $name === 'palco' ? ' encarte-palco-img' : '';
        $objectFit = $name === 'palco' ? 'object-fit:contain;' : '';
        $filter = ' style="' . $baseStyle . 'width:' . $width . 'px;height:' . $height . 'px;' . $objectFit . '"';

        $imgClass = ($name === 'elemento-produto' || str_starts_with($name, 'elemento'))
            ? ' class="foto-flutuante foto-flutuante--forte' . $extraClass . '"'
            : ' class="fabric-img' . $extraClass . '"';

        return '<img' . $imgClass . ' src="' . $srcEsc . '" alt="' . $alt . '"' . $filter . '>';
    }

    if ($type === 'rect') {
        $width = $bbox['width'];
        $height = $bbox['height'];
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
        $text = ep_resolve_fabric_text_content($obj, $itens, $render_opts);

        $fill = htmlspecialchars((string) ($obj['fill'] ?? '#ffffff'), ENT_QUOTES, 'UTF-8');
        $scaleY = (float) ($obj['scaleY'] ?? 1);
        $fontSize = max(8, (float) ($obj['fontSize'] ?? 16) * abs($scaleY));
        $fontWeight = (int) ($obj['fontWeight'] ?? 400);
        $fontFamily = htmlspecialchars((string) ($obj['fontFamily'] ?? 'Segoe UI, Arial, sans-serif'), ENT_QUOTES, 'UTF-8');
        $textAlign = htmlspecialchars((string) ($obj['textAlign'] ?? 'left'), ENT_QUOTES, 'UTF-8');
        $scaleX = (float) ($obj['scaleX'] ?? 1);
        $width = (float) ($obj['width'] ?? 0);
        $widthCss = $width > 0 ? 'width:' . ($width * abs($scaleX)) . 'px;' : '';
        $linethroughCss = !empty($obj['linethrough']) ? 'text-decoration:line-through;' : '';
        $shadowCss = ep_fabric_text_shadow_css($obj);

        $class = 'fabric-text';
        if (in_array($name, ['titulo_linha1', 'titulo_linha2'], true)) {
            $class .= ' font-display preco-3d';
        }
        if (!empty($obj['isDynamicText']) && ($obj['textType'] ?? '') === 'preco_promo') {
            $class .= ' font-display preco-3d';
        }
        if (!empty($obj['isCampanhaText']) && ($obj['textType'] ?? '') === 'titulo_campanha') {
            $class .= ' font-display preco-3d';
        }

        $textTypeObj = (string) ($obj['textType'] ?? '');
        $isValidade = $name === 'validade_periodo'
            || $textTypeObj === 'validade_periodo'
            || ep_campanha_text_type_from_name($name) === 'validade_periodo';

        // Validade deve permanecer em uma unica linha (placeholder curto nao pode limitar a largura).
        $whiteSpace = $isValidade ? 'nowrap' : 'pre-wrap';
        if ($isValidade) {
            $widthCss = '';
        }
        $textHtml = $isValidade ? $text : nl2br($text);

        return sprintf(
            '<div class="%s" data-name="%s" style="%s%sfont-size:%spx;font-weight:%d;font-family:%s;color:%s;text-align:%s;white-space:%s;line-height:1.1;%s%s">%s</div>',
            $class,
            htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            $baseStyle,
            $widthCss,
            $fontSize,
            $fontWeight,
            $fontFamily,
            $fill,
            $textAlign,
            $whiteSpace,
            $linethroughCss,
            $shadowCss,
            $textHtml
        );
    }

    return '';
}
