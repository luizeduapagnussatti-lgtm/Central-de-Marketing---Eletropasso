<?php
/**
 * Template Modelo 02 — Estilo Livre Eletropasso (3 topo + 2 destaque)
 *
 * Layout otimizado para formato 1:1 (1080x1080).
 * Variaveis esperadas:
 * @var array  $encarte_data
 * @var array  $itens
 * @var string $formato
 * @var string $base_path
 */

$formato = $formato ?? '1x1';
$titulo = htmlspecialchars($encarte_data['titulo_campanha'] ?? 'Promocao Eletropasso', ENT_QUOTES, 'UTF-8');
$validade_inicio = marketing_format_date_br($encarte_data['validade_inicio'] ?? null);
$validade_fim = marketing_format_date_br($encarte_data['validade_fim'] ?? null);
$rodape = htmlspecialchars(
    $encarte_data['texto_legal_rodape'] ?? 'Ofertas validas enquanto durarem os estoques.',
    ENT_QUOTES,
    'UTF-8'
);
$logo_relativo = marketing_logo_relativo('preta');
$logo_path = marketing_path($logo_relativo);
$logo_exists = is_file($logo_path);

$formatos = marketing_formatos();
if (!isset($formatos[$formato ?? ''])) {
    $formato = '1x1';
}
$dims = $formatos[$formato];

$itens_top = array_slice($itens, 0, 3);
$itens_bottom = array_slice($itens, 3, 2);

$modelo_config = $modelo_config ?? (new ModeloLayoutService())->configVisualPadrao('modelo_02');
$cores = $modelo_config['cores'] ?? [];
$textos = $modelo_config['textos'] ?? [];
$badge_oferta = htmlspecialchars((string) ($textos['badge_oferta'] ?? 'Oferta'), ENT_QUOTES, 'UTF-8');
$clube_badge = htmlspecialchars((string) ($textos['clube_badge'] ?? 'Clube Eletropasso'), ENT_QUOTES, 'UTF-8');
$mostrar_clube = (bool) ($textos['mostrar_clube'] ?? false);
$utils_css = marketing_encarte_stylesheet_url($base_path, 'assets/encarte/encarte-utilities.css');
$fonts_css = marketing_encarte_stylesheet_url($base_path, 'assets/encarte/fonts.css');

function ep_preco_partes(float $valor): array
{
    $formatado = number_format($valor, 2, ',', '.');
    [$reais, $centavos] = explode(',', $formatado);

    return [
        'reais'    => $reais,
        'centavos' => ',' . $centavos,
    ];
}

function ep_render_item_livre(
    array $item,
    string $base_path,
    string $badge_oferta,
    string $variante = 'top',
    bool $reverse = false
): void {
    $foto = marketing_encarte_foto_src($base_path, $item);
    $nome = htmlspecialchars($item['nome_comercial'] ?? '', ENT_QUOTES, 'UTF-8');
    $sku = htmlspecialchars($item['sku'] ?? '', ENT_QUOTES, 'UTF-8');
    $unidade = htmlspecialchars($item['unidade'] ?? 'und', ENT_QUOTES, 'UTF-8');
    $precoNormalVal = (float) ($item['preco_normal'] ?? 0);
    $precoPromoVal = (float) ($item['preco_promocional'] ?? 0);
    $precoNormal = $precoNormalVal > 0 ? marketing_format_money($precoNormalVal) : '';
    $partes = ep_preco_partes($precoPromoVal);
    $reverseClass = $reverse ? ' item-free--reverse' : '';
    $detailsAlign = $variante === 'bottom' && !$reverse ? ' style="text-align: right;"' : '';
    $tagAlign = $variante === 'bottom' && !$reverse ? ' style="left: auto; right: -10px;"' : '';

    $detalhes = function () use (
        $sku,
        $nome,
        $precoNormal,
        $precoNormalVal,
        $precoPromoVal,
        $partes,
        $unidade,
        $detailsAlign,
        $tagAlign,
        $badge_oferta
    ): void {
        ?>
        <div class="item-details"<?= $detailsAlign ?>>
            <?php if ($sku !== ''): ?><div class="item-sku">Cod: <?= $sku ?></div><?php endif; ?>
            <div class="item-name"><?= $nome ?></div>
            <div class="price-box">
                <?php if ($precoNormal !== '' && $precoNormalVal > $precoPromoVal): ?>
                    <div class="price-normal"><?= $precoNormal ?></div>
                <?php endif; ?>
                <div class="price-promo">
                    <div class="oferta-tag"<?= $tagAlign ?>><?= $badge_oferta ?></div>
                    <span class="promo-currency">R$</span>
                    <span class="promo-value"><?= $partes['reais'] ?></span>
                    <span class="promo-cents"><?= $partes['centavos'] ?></span>
                    <span class="promo-unit"><?= $unidade ?></span>
                </div>
            </div>
        </div>
        <?php
    };

    $imagem = function () use ($foto, $nome, $variante): void {
        ?>
        <div class="item-image">
            <?php if ($foto !== ''): ?><img class="foto-flutuante<?= $variante === 'bottom' ? ' foto-flutuante--forte' : '' ?>" src="<?= $foto ?>" alt="<?= $nome ?>"><?php endif; ?>
        </div>
        <?php
    };
    ?>
    <div class="item-free item-free--<?= $variante ?><?= $reverseClass ?>">
        <?php if ($variante === 'bottom'): ?>
            <?php $detalhes(); ?>
            <?php $imagem(); ?>
        <?php else: ?>
            <?php $imagem(); ?>
            <?php $detalhes(); ?>
        <?php endif; ?>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $titulo ?></title>
<link rel="stylesheet" href="<?= htmlspecialchars($fonts_css, ENT_QUOTES, 'UTF-8') ?>">
<link rel="stylesheet" href="<?= htmlspecialchars($utils_css, ENT_QUOTES, 'UTF-8') ?>">
<style>
:root {
  --ep-primary: <?= htmlspecialchars((string) ($cores['primary'] ?? '#b91c1c'), ENT_QUOTES, 'UTF-8') ?>;
  --ep-dark: <?= htmlspecialchars((string) ($cores['dark'] ?? '#7f1d1d'), ENT_QUOTES, 'UTF-8') ?>;
  --ep-accent: #fecaca;
  --bg-top: <?= htmlspecialchars((string) ($cores['fundo'] ?? '#ffffff'), ENT_QUOTES, 'UTF-8') ?>;
  --bg-bottom: <?= htmlspecialchars((string) ($cores['fundo_escuro'] ?? '#1f2937'), ENT_QUOTES, 'UTF-8') ?>;
  --text-dark: #111827;
  --text-light: #ffffff;
  --text-muted: #6b7280;
  --promo-bg: <?= htmlspecialchars((string) ($cores['preco_bg'] ?? '#b91c1c'), ENT_QUOTES, 'UTF-8') ?>;
  --promo-dark: <?= htmlspecialchars((string) ($cores['dark'] ?? '#7f1d1d'), ENT_QUOTES, 'UTF-8') ?>;
  --badge-bg: <?= htmlspecialchars((string) ($cores['badge_bg'] ?? '#ffffff'), ENT_QUOTES, 'UTF-8') ?>;
  --badge-txt: <?= htmlspecialchars((string) ($cores['badge_texto'] ?? '#b91c1c'), ENT_QUOTES, 'UTF-8') ?>;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: Arial, Helvetica, sans-serif;
  background: #fff;
  -webkit-font-smoothing: antialiased;
}

.encarte-container {
  width: <?= (int) $dims['width'] ?>px;
  height: <?= (int) $dims['height'] ?>px;
  position: relative;
  overflow: hidden;
  background: linear-gradient(to bottom, var(--bg-top) 50%, var(--bg-bottom) 50%);
}

.bg-curve {
  position: absolute;
  top: 48%;
  left: -10%;
  width: 120%;
  height: 62%;
  background-color: var(--bg-bottom);
  border-top-left-radius: 50% 18%;
  border-top-right-radius: 50% 18%;
  z-index: 1;
}

.encarte-header {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  z-index: 10;
  display: flex;
  align-items: center;
  gap: 28px;
  padding: 20px 40px 16px;
  background: #ffffff;
  border-bottom: 4px solid var(--ep-primary);
  overflow: visible;
}

.header-brand {
  display: flex;
  align-items: center;
  gap: 28px;
  min-width: 0;
  flex: 1;
  overflow: visible;
}

.logo-box {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  overflow: visible;
}

.logo-box img,
.header-logo {
  height: auto;
  max-height: 58px;
  width: auto;
  max-width: 220px;
  object-fit: contain;
  object-position: left center;
  display: block;
  background: transparent;
}

.logo-box .logo-text {
  font-weight: 900;
  font-size: 28px;
  color: var(--ep-primary);
}

.logo-box .logo-text span { color: var(--text-dark); }

.tag-linha {
  font-weight: 800;
  font-size: 21px;
  color: var(--text-dark);
  text-transform: uppercase;
  line-height: 1.2;
  letter-spacing: -0.3px;
  flex: 1;
  min-width: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.clube-badge {
  flex-shrink: 0;
  background: var(--ep-primary);
  color: #fff;
  font-size: 11px;
  font-weight: 800;
  padding: 6px 10px;
  border-radius: 6px;
  text-transform: uppercase;
  letter-spacing: 0.02em;
  align-self: center;
}

.main-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 5;
  padding: 128px 36px 52px;
  display: flex;
  flex-direction: column;
}

.top-section {
  display: flex;
  justify-content: space-between;
  height: 45%;
  padding-top: 10px;
  gap: 12px;
}

.item-free {
  position: relative;
  width: 31%;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: left;
}

.item-free--bottom {
  width: 45%;
  flex-direction: row;
  gap: 15px;
  align-items: flex-end;
}

.item-free--bottom.item-free--reverse {
  flex-direction: row-reverse;
}

.item-image {
  width: 100%;
  height: 220px;
  display: flex;
  justify-content: center;
  align-items: flex-end;
  margin-bottom: 15px;
}

.item-image img {
  max-width: 90%;
  max-height: 90%;
  object-fit: contain;
}

.bottom-section .item-image {
  width: 55%;
  height: 300px;
  margin-bottom: 0;
}

.item-details { width: 100%; padding: 0 10px; }

.item-sku {
  font-size: 12px;
  color: var(--text-muted);
  margin-bottom: 3px;
  font-weight: 700;
}

.item-name {
  font-size: 17px;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 12px;
  color: var(--text-dark);
  min-height: 40px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.price-box { display: inline-block; }

.price-normal {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-muted);
  text-decoration: line-through;
  margin-bottom: 4px;
}

.price-promo {
  background: linear-gradient(135deg, var(--promo-dark) 0%, var(--promo-bg) 100%);
  color: #fff;
  padding: 6px 15px;
  border-radius: 6px;
  display: inline-flex;
  align-items: baseline;
  gap: 2px;
  box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
  position: relative;
}

.oferta-tag {
  position: absolute;
  top: -15px;
  left: -10px;
  background: var(--badge-bg);
  color: var(--badge-txt);
  font-size: 10px;
  font-weight: 900;
  padding: 4px 8px;
  border-radius: 4px;
  transform: rotate(-8deg);
  border: 2px solid var(--ep-accent);
  box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
  text-transform: uppercase;
}

.promo-currency { font-size: 16px; font-weight: 700; }
.promo-value { font-size: 38px; font-weight: 900; letter-spacing: -2px; line-height: 1; }
.promo-cents { font-size: 18px; font-weight: 800; position: relative; top: -12px; }
.promo-unit { font-size: 11px; align-self: flex-end; margin-left: 3px; margin-bottom: 4px; }

.bottom-section {
  display: flex;
  justify-content: space-around;
  align-items: flex-end;
  height: 55%;
  padding-bottom: 48px;
  gap: 16px;
}

.bottom-section .item-name { color: var(--text-light); }
.bottom-section .item-sku { color: #d1d5db; }
.bottom-section .price-normal { color: #9ca3af; }

.bottom-section .item-image {
  width: 55%;
  height: 300px;
  margin-bottom: 0;
}

.bottom-section .item-image img {
  filter: <?= marketing_encarte_foto_filter_css('forte') ?>;
}

.bottom-section .item-details { width: 45%; }

.encarte-footer {
  position: absolute;
  bottom: 15px;
  left: 0;
  width: 100%;
  text-align: center;
  font-size: 13px;
  font-weight: 700;
  color: #9ca3af;
  z-index: 10;
  padding: 0 40px;
  line-height: 1.35;
}
</style>
</head>
<body>
<div class="encarte-container formato-<?= htmlspecialchars($formato, ENT_QUOTES, 'UTF-8') ?>">

  <div class="bg-curve"></div>

  <header class="encarte-header">
    <div class="header-brand">
      <div class="logo-box">
        <?php if ($logo_exists): ?>
          <img class="header-logo" src="<?= marketing_encarte_img_src($base_path, $logo_relativo) ?>" alt="Eletropasso">
        <?php else: ?>
          <span class="logo-text">ELETRO<span>PASSO</span></span>
        <?php endif; ?>
      </div>
      <h1 class="tag-linha"><?= $titulo ?></h1>
      <?php if ($mostrar_clube): ?>
        <span class="clube-badge"><?= $clube_badge ?></span>
      <?php endif; ?>
    </div>
  </header>

  <div class="main-container">
    <div class="top-section">
      <?php foreach ($itens_top as $item): ?>
        <?php ep_render_item_livre($item, $base_path, $badge_oferta, 'top'); ?>
      <?php endforeach; ?>
    </div>

    <div class="bottom-section">
      <?php foreach ($itens_bottom as $index => $item): ?>
        <?php ep_render_item_livre($item, $base_path, $badge_oferta, 'bottom', $index === 1); ?>
      <?php endforeach; ?>
    </div>
  </div>

  <footer class="encarte-footer">
    <?= $rodape ?>
    <?php if ($validade_inicio || $validade_fim): ?>
      <?php if ($validade_inicio): ?> Validas de <?= $validade_inicio ?><?php endif; ?>
      <?php if ($validade_fim): ?> ate <?= $validade_fim ?><?php endif; ?>.
    <?php endif; ?>
  </footer>

</div>
</body>
</html>
