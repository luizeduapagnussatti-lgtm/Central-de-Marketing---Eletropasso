<?php
/**
 * Template Modelo 01 — Grade de Produtos Eletropasso (Estilo Sem Bordas)
 *
 * Variaveis esperadas:
 * @var array  $encarte_data
 * @var array  $itens
 * @var string $formato
 * @var string $base_path
 */

$formato = $formato ?? '9x16';
$titulo = htmlspecialchars($encarte_data['titulo_campanha'] ?? 'Promocao Eletropasso', ENT_QUOTES, 'UTF-8');
$validade_inicio = marketing_format_date_br($encarte_data['validade_inicio'] ?? null);
$validade_fim = marketing_format_date_br($encarte_data['validade_fim'] ?? null);
$rodape = htmlspecialchars(
    $encarte_data['texto_legal_rodape'] ?? 'Ofertas validas enquanto durarem os estoques.',
    ENT_QUOTES,
    'UTF-8'
);
$logo_relativo = marketing_logo_relativo('branca');
$logo_path = marketing_path($logo_relativo);
$logo_exists = is_file($logo_path);

$formatos = marketing_formatos();
$dims = $formatos[$formato] ?? $formatos['9x16'];
$cols = match ($formato) {
    'a4'     => 3,
    '16x9'   => 4,
    '1x1'    => 2,
    default  => 2,
};

$modelo_config = $modelo_config ?? (new ModeloLayoutService())->configVisualPadrao('modelo_01');
$cores = $modelo_config['cores'] ?? [];
$textos = $modelo_config['textos'] ?? [];
$badge_oferta = htmlspecialchars((string) ($textos['badge_oferta'] ?? 'Oferta Especial!'), ENT_QUOTES, 'UTF-8');
$utils_css = marketing_encarte_stylesheet_url($base_path, 'assets/encarte/encarte-utilities.css');
$fonts_css = marketing_encarte_stylesheet_url($base_path, 'assets/encarte/fonts.css');
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
  --ep-primary:   <?= htmlspecialchars((string) ($cores['primary'] ?? '#b91c1c'), ENT_QUOTES, 'UTF-8') ?>;
  --ep-dark:      <?= htmlspecialchars((string) ($cores['dark'] ?? '#7f1d1d'), ENT_QUOTES, 'UTF-8') ?>;
  --ep-bg:        <?= htmlspecialchars((string) ($cores['fundo'] ?? '#ffffff'), ENT_QUOTES, 'UTF-8') ?>;
  --ep-price-bg:  <?= htmlspecialchars((string) ($cores['preco_bg'] ?? '#16a34a'), ENT_QUOTES, 'UTF-8') ?>;
  --ep-price-txt: <?= htmlspecialchars((string) ($cores['preco_texto'] ?? '#ffffff'), ENT_QUOTES, 'UTF-8') ?>;
  --ep-gray:      #4b5563;
  --ep-light:     #f3f4f6;
  --ep-clube-bg:  <?= htmlspecialchars((string) ($cores['badge_bg'] ?? '#059669'), ENT_QUOTES, 'UTF-8') ?>;
  --ep-oferta-bg: <?= htmlspecialchars((string) ($cores['badge_bg'] ?? '#059669'), ENT_QUOTES, 'UTF-8') ?>;
  --ep-oferta-txt: <?= htmlspecialchars((string) ($cores['badge_texto'] ?? '#ffffff'), ENT_QUOTES, 'UTF-8') ?>;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
  background: var(--ep-bg);
  color: #111827;
  -webkit-font-smoothing: antialiased;
}

.encarte-container {
  width: <?= (int) $dims['width'] ?>px;
  height: <?= (int) $dims['height'] ?>px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background: var(--ep-bg);
  position: relative;
}

/* Fundo com textura sutil de material eletrico (opcional) */
.encarte-container::before {
  content: '⚡ 🔌 💡 ⚡ 🔌 💡';
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  font-size: 120px;
  color: rgba(0,0,0,0.02);
  line-height: 2;
  text-align: center;
  z-index: 0;
  pointer-events: none;
  overflow: hidden;
  white-space: pre-wrap;
  word-wrap: break-word;
  opacity: 0.5;
}

.encarte-header {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  gap: 24px;
  padding: <?= $formato === 'a4' ? '48px 60px 64px' : '32px 40px 52px' ?>;
  background:
    linear-gradient(to bottom, rgba(255, 255, 255, 0) 35%, rgba(255, 255, 255, 0.55) 72%, #ffffff 100%),
    linear-gradient(135deg, var(--ep-primary) 0%, var(--ep-dark) 100%);
  color: #ffffff;
  flex-shrink: 0;
  border-bottom: none;
  box-shadow: none;
}

.encarte-header img.logo {
  height: <?= $formato === 'a4' ? '72px' : '56px' ?>;
  width: auto;
  object-fit: contain;
  filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.25));
}

.encarte-header-text { flex: 1; }

.encarte-header-text h1 {
  font-size: <?= $formato === 'a4' ? '56px' : ($formato === '16x9' ? '40px' : '36px') ?>;
  font-weight: 900;
  color: #ffffff;
  line-height: 1.1;
  text-transform: uppercase;
  letter-spacing: -1px;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.encarte-header-text .vigencia {
  margin-top: 8px;
  font-size: <?= $formato === 'a4' ? '24px' : '18px' ?>;
  color: #fecaca;
  font-weight: 600;
}

.encarte-grid {
  position: relative;
  z-index: 1;
  flex: 1;
  display: grid;
  grid-template-columns: repeat(<?= $cols ?>, 1fr);
  gap: <?= $formato === 'a4' ? '40px' : '24px' ?>;
  padding: <?= $formato === 'a4' ? '24px 60px 50px' : '8px 40px 30px' ?>;
  align-content: start;
  overflow: hidden;
  background: var(--ep-bg);
}

.produto-card {
  position: relative;
  background: transparent;
  border: none;
  padding: <?= $formato === 'a4' ? '10px' : '8px' ?>;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  min-height: 0;
}

.produto-foto {
  width: 100%;
  height: <?= $formato === 'a4' ? '240px' : ($formato === '16x9' ? '140px' : '160px') ?>;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
  position: relative;
}

.produto-foto img {
  max-width: 100%;
  max-height: 100%;
}

/* Badge de icone flutuante (ex: Wi-Fi, LED) */
.produto-icon {
  position: absolute;
  top: 0;
  right: 0;
  background: #fff;
  border-radius: 50%;
  width: <?= $formato === 'a4' ? '48px' : '36px' ?>;
  height: <?= $formato === 'a4' ? '48px' : '36px' ?>;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: <?= $formato === 'a4' ? '24px' : '18px' ?>;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  color: var(--ep-primary);
}

.produto-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  text-align: left;
  width: 100%;
  padding: 0 8px;
}

.produto-sku {
  font-size: <?= $formato === 'a4' ? '14px' : '11px' ?>;
  color: var(--ep-gray);
  margin-bottom: 4px;
  font-weight: 600;
}

.produto-nome {
  font-size: <?= $formato === 'a4' ? '22px' : '15px' ?>;
  font-weight: 700;
  color: #111827;
  line-height: 1.2;
  margin-bottom: 6px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  width: 100%;
}

.produto-desc {
  font-size: <?= $formato === 'a4' ? '16px' : '12px' ?>;
  color: var(--ep-gray);
  margin-bottom: 12px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  width: 100%;
}

.produto-precos {
  margin-top: auto;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  position: relative;
}

.preco-normal {
  font-size: <?= $formato === 'a4' ? '22px' : '16px' ?>;
  color: #374151;
  font-weight: 700;
  text-decoration: none;
  margin-bottom: 2px;
  margin-right: 12px;
  z-index: 1;
  position: relative;
  display: inline-block;
  padding: 0 2px;
}

.preco-normal::after {
  content: '';
  position: absolute;
  left: 0;
  right: 0;
  top: 52%;
  height: <?= $formato === 'a4' ? '3px' : '2px' ?>;
  background: #6b7280;
  transform: translateY(-50%);
  border-radius: 1px;
}

.preco-promo-wrap {
  display: inline-flex;
  align-items: baseline;
  gap: 4px;
  background: var(--ep-price-bg);
  color: var(--ep-price-txt);
  padding: <?= $formato === 'a4' ? '12px 20px' : '8px 16px' ?>;
  border-radius: 12px;
  box-shadow: 0 6px 12px rgba(22, 163, 74, 0.3);
  position: relative;
}

/* Badge promocional sobreposto ao preco */
.oferta-badge {
  position: absolute;
  top: -18px;
  left: -16px;
  background: var(--ep-oferta-bg);
  color: var(--ep-oferta-txt);
  font-size: <?= $formato === 'a4' ? '13px' : '9px' ?>;
  font-weight: 800;
  padding: <?= $formato === 'a4' ? '6px 10px' : '4px 8px' ?>;
  border-radius: 8px;
  transform: rotate(-6deg);
  box-shadow: 0 4px 8px rgba(5, 150, 105, 0.35);
  border: 2px solid #fff;
  text-transform: uppercase;
  letter-spacing: 0.02em;
  line-height: 1.15;
  text-align: center;
  white-space: nowrap;
  max-width: <?= $formato === 'a4' ? '160px' : '110px' ?>;
}

.preco-moeda {
  font-size: <?= $formato === 'a4' ? '24px' : '16px' ?>;
  font-weight: 700;
}

.preco-promo {
  font-size: <?= $formato === 'a4' ? '48px' : '32px' ?>;
  font-weight: 900;
  line-height: 0.9;
  letter-spacing: -1px;
}

.preco-unidade {
  font-size: <?= $formato === 'a4' ? '18px' : '12px' ?>;
  font-weight: 700;
  opacity: 0.9;
  margin-left: 2px;
}

.encarte-footer {
  position: relative;
  z-index: 1;
  flex-shrink: 0;
  background: var(--ep-dark);
  color: #fff;
  text-align: center;
  padding: <?= $formato === 'a4' ? '24px 60px' : '16px 32px' ?>;
  font-size: <?= $formato === 'a4' ? '18px' : '12px' ?>;
  font-weight: 500;
  line-height: 1.4;
}
</style>
</head>
<body>
<div class="encarte-container formato-<?= htmlspecialchars($formato, ENT_QUOTES, 'UTF-8') ?>">

  <header class="encarte-header">
    <?php if ($logo_exists): ?>
      <img class="logo" src="<?= marketing_encarte_img_src($base_path, $logo_relativo) ?>" alt="Eletropasso">
    <?php endif; ?>
    <div class="encarte-header-text">
      <h1><?= $titulo ?></h1>
      <?php if ($validade_inicio || $validade_fim): ?>
        <div class="vigencia">
          Ofertas validas
          <?php if ($validade_inicio): ?>de <?= $validade_inicio ?><?php endif; ?>
          <?php if ($validade_fim): ?> ate <?= $validade_fim ?><?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </header>

  <main class="encarte-grid">
    <?php foreach ($itens as $item):
        $foto = marketing_encarte_foto_src($base_path, $item);
        $nome = htmlspecialchars($item['nome_comercial'] ?? '', ENT_QUOTES, 'UTF-8');
        $desc = htmlspecialchars($item['descricao_complementar'] ?? '', ENT_QUOTES, 'UTF-8');
        $sku = htmlspecialchars($item['sku'] ?? '', ENT_QUOTES, 'UTF-8');
        
        $precoNormalVal = (float) ($item['preco_normal'] ?? 0);
        $precoPromoVal = (float) ($item['preco_promocional'] ?? 0);
        
        $precoNormal = $precoNormalVal > 0 ? marketing_format_money($precoNormalVal) : '';
        
        // Separar moeda e valor para estilizar
        $precoFormatado = number_format($precoPromoVal, 2, ',', '.');
        $unidade = htmlspecialchars($item['unidade'] ?? 'und', ENT_QUOTES, 'UTF-8');
        
        // Icone configuravel pelo modelo
        $icone = marketing_modelo_icone($nome, $modelo_config);
    ?>
    <article class="produto-card">
      <div class="produto-foto">
        <?php if ($foto !== ''): ?>
          <img class="foto-flutuante" src="<?= $foto ?>" alt="<?= $nome ?>">
        <?php endif; ?>
        <?php if ($icone !== ''): ?>
          <div class="produto-icon"><?= $icone ?></div>
        <?php endif; ?>
      </div>
      
      <div class="produto-info">
        <?php if ($sku !== ''): ?>
          <div class="produto-sku">Cód: <?= $sku ?></div>
        <?php endif; ?>
        <div class="produto-nome"><?= $nome ?></div>
        <?php if ($desc !== ''): ?>
          <div class="produto-desc"><?= $desc ?></div>
        <?php endif; ?>
      </div>

      <div class="produto-precos">
        <?php if ($precoNormal !== '' && $precoNormalVal > $precoPromoVal): ?>
          <div class="preco-normal">De: <?= $precoNormal ?></div>
        <?php endif; ?>
        <div class="preco-promo-wrap">
          <div class="oferta-badge"><?= $badge_oferta ?></div>
          <span class="preco-moeda">R$</span>
          <span class="preco-promo"><?= $precoFormatado ?></span>
          <span class="preco-unidade"><?= $unidade ?></span>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </main>

  <footer class="encarte-footer"><?= $rodape ?></footer>

</div>
</body>
</html>