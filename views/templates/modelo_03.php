<?php
/**
 * Template Modelo 03 — Premium Escuro (palco estatico + camada dinamica)
 *
 * @var array  $encarte_data
 * @var array  $itens
 * @var string $formato
 * @var string $base_path
 * @var array  $modelo_config
 */

$formato = $formato ?? '9x16';
$formatos = marketing_formatos();
$dims = $formatos[$formato] ?? $formatos['9x16'];

$modelo_config = $modelo_config ?? (new ModeloLayoutService())->configVisualPadrao('modelo_03');
$cores = $modelo_config['cores'] ?? [];
$textos = $modelo_config['textos'] ?? [];
$iconesConfig = $modelo_config['icones'] ?? [];

$fundo_src = marketing_encarte_fundo_src($base_path, $modelo_config, $formato);
$tem_palco = $fundo_src !== '';

$utils_css = marketing_encarte_stylesheet_url($base_path, 'assets/encarte/encarte-utilities.css');
$fonts_css = marketing_encarte_stylesheet_url($base_path, 'assets/encarte/fonts.css');
$fa_css = marketing_encarte_stylesheet_url($base_path, 'assets/vendor/fontawesome/fontawesome-subset.css');
$tailwind_css = marketing_encarte_stylesheet_url($base_path, 'assets/encarte/tailwind.encarte.css');
$usar_fa = ($iconesConfig['tipo'] ?? 'emoji') === 'fontawesome';

$tituloRaw = trim((string) ($encarte_data['titulo_campanha'] ?? ''));
if (str_contains($tituloRaw, '|')) {
    [$tituloLinha1, $tituloLinha2] = array_map('trim', explode('|', $tituloRaw, 2));
} else {
    $tituloLinha1 = trim((string) ($textos['titulo_linha1'] ?? 'PROMOCAO'));
    $tituloLinha2 = trim((string) ($textos['titulo_linha2'] ?? 'FECHA MES'));
}

$footerEndereco = htmlspecialchars((string) ($textos['footer_endereco'] ?? ''), ENT_QUOTES, 'UTF-8');
$footerCidade = htmlspecialchars((string) ($textos['footer_cidade'] ?? ''), ENT_QUOTES, 'UTF-8');
$footerWhatsapp = htmlspecialchars((string) ($textos['footer_whatsapp'] ?? ''), ENT_QUOTES, 'UTF-8');
$rodapeLegal = htmlspecialchars((string) ($encarte_data['texto_legal_rodape'] ?? ''), ENT_QUOTES, 'UTF-8');

$itensGrid = array_slice($itens, 0, 6);

$fabric_state = $modelo_config['fabric_state'] ?? null;
$fabric_objects = is_array($fabric_state['objects'] ?? null) ? $fabric_state['objects'] : [];
$tem_fabric_overlay = $fabric_objects !== [];
$tem_fabric_palco = false;
foreach ($fabric_objects as $fabric_obj) {
    if (is_array($fabric_obj) && (string) ($fabric_obj['name'] ?? '') === 'palco') {
        $tem_fabric_palco = true;
        break;
    }
}

function ep_premium_preco_partes(float $valor): array
{
    $formatado = number_format($valor, 2, ',', '.');
    [$reais, $centavos] = explode(',', $formatado);

    return ['reais' => $reais, 'centavos' => ',' . $centavos];
}

function ep_premium_feature_render(string $texto, array $modelo_config): string
{
    $tipo = (string) ($modelo_config['icones']['tipo'] ?? 'emoji');
    $textoLower = strtolower($texto);
    $mapa = is_array($modelo_config['icones']['mapa'] ?? null) ? $modelo_config['icones']['mapa'] : [];

    if ($tipo === 'fontawesome') {
        $classe = match (true) {
            str_contains($textoLower, 'luz'), str_contains($textoLower, 'led') => 'fa-lightbulb',
            str_contains($textoLower, 'wifi'), str_contains($textoLower, 'smart') => 'fa-wifi',
            str_contains($textoLower, 'segur'), str_contains($textoLower, 'prote') => 'fa-shield-halved',
            str_contains($textoLower, 'instal'), str_contains($textoLower, 'facil') => 'fa-wrench',
            str_contains($textoLower, 'tomada'), str_contains($textoLower, 'plug') => 'fa-plug',
            default => 'fa-bolt',
        };

        return '<i class="fa-solid ' . $classe . '" aria-hidden="true"></i>';
    }

    if (str_contains($textoLower, 'luz') || str_contains($textoLower, 'led')) {
        return (string) ($mapa['led'] ?? '💡');
    }
    if (str_contains($textoLower, 'wifi') || str_contains($textoLower, 'smart')) {
        return (string) ($mapa['wifi'] ?? '📶');
    }
    if (str_contains($textoLower, 'tomada') || str_contains($textoLower, 'plug')) {
        return (string) ($mapa['tomada'] ?? '🔌');
    }

    return (string) ($mapa['cabo'] ?? '⚡');
}

function ep_premium_features(array $item, array $modelo_config): array
{
    $desc = trim((string) ($item['descricao_complementar'] ?? ''));
    $partes = $desc !== ''
        ? array_values(array_filter(array_map('trim', preg_split('/\s*[|·\/]\s*/u', $desc))))
        : [];

    while (count($partes) < 3) {
        $defaults = ['Alta qualidade', 'Seguranca garantida', 'Melhor preco'];
        $partes[] = $defaults[count($partes)] ?? 'Eletropasso';
    }

    $features = [];
    foreach (array_slice($partes, 0, 3) as $texto) {
        $features[] = [
            'html'  => ep_premium_feature_render($texto, $modelo_config),
            'texto' => mb_strtoupper($texto),
        ];
    }

    return $features;
}

$palco_style = $tem_palco && !$tem_fabric_palco
    ? 'background-image: url(' . htmlspecialchars($fundo_src, ENT_QUOTES, 'UTF-8') . ');'
    : 'background-color: ' . htmlspecialchars((string) ($cores['fundo'] ?? '#030b17'), ENT_QUOTES, 'UTF-8') . ';';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Encarte Premium — Eletropasso</title>
<link rel="stylesheet" href="<?= htmlspecialchars($fonts_css, ENT_QUOTES, 'UTF-8') ?>">
<link rel="stylesheet" href="<?= htmlspecialchars($utils_css, ENT_QUOTES, 'UTF-8') ?>">
<?php if ($usar_fa): ?>
<link rel="stylesheet" href="<?= htmlspecialchars($fa_css, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>
<?php if (is_file(marketing_path('assets/encarte/tailwind.encarte.css'))): ?>
<link rel="stylesheet" href="<?= htmlspecialchars($tailwind_css, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>
<style>
:root {
  --ep-primary: <?= htmlspecialchars((string) ($cores['primary'] ?? '#dc2626'), ENT_QUOTES, 'UTF-8') ?>;
  --ep-dark: <?= htmlspecialchars((string) ($cores['dark'] ?? '#991b1b'), ENT_QUOTES, 'UTF-8') ?>;
  --ep-card-bg: <?= htmlspecialchars((string) ($cores['fundo_escuro'] ?? '#071326'), ENT_QUOTES, 'UTF-8') ?>;
  --ep-card-border: <?= htmlspecialchars((string) ($cores['badge_bg'] ?? '#1e3a8a'), ENT_QUOTES, 'UTF-8') ?>;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
  -webkit-font-smoothing: antialiased;
}

.encarte-container {
  width: <?= (int) $dims['width'] ?>px;
  height: <?= (int) $dims['height'] ?>px;
  position: relative;
  overflow: hidden;
  color: #fff;
  <?= $palco_style ?>
  background-size: cover;
  background-position: center top;
}

.card-body {
  display: flex;
  padding: 16px 10px;
  flex: 1;
  min-height: 0;
}

.features {
  width: 35%;
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding-top: 8px;
}

.feature-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  font-size: 11px;
  font-weight: 700;
  color: #d1d5db;
  line-height: 1.2;
}

.feature-icon {
  font-size: 18px;
  margin-bottom: 4px;
  border: 1px solid #4b5563;
  border-radius: 50%;
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255,255,255,0.05);
}

.image-area {
  width: 65%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.image-area img {
  max-width: 140%;
  max-height: 200px;
}

.card-price {
  background: linear-gradient(180deg, var(--ep-primary), var(--ep-dark));
  padding: 8px 12px;
  display: flex;
  align-items: baseline;
  justify-content: center;
  border-top: 1px solid #ff4d4d;
}

.card {
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border-radius: 12px;
  min-height: 0;
}

.card-header {
  padding: 10px 6px;
  font-size: 18px;
  line-height: 1.15;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.currency { font-size: 22px; font-weight: 700; margin-right: 4px; }
.value { font-size: 52px; font-weight: 900; line-height: 0.85; letter-spacing: -2px; }
.cents { font-size: 26px; font-weight: 800; position: relative; top: -14px; }

.footer-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.footer-text h4 {
  font-size: 13px;
  color: var(--ep-primary);
  font-weight: 700;
  text-transform: uppercase;
}

.footer-text p { font-size: 16px; font-weight: 900; }
.footer-text span { font-size: 11px; color: #9ca3af; }

.rodape-legal {
  text-align: center;
  font-size: 10px;
  color: #9ca3af;
  margin-top: 8px;
}

.encarte-footer-overlay {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}
</style>
</head>
<body>
<div class="encarte-container encarte-palco formato-<?= htmlspecialchars($formato, ENT_QUOTES, 'UTF-8') ?>">

  <?php if ($tem_fabric_overlay): ?>
  <div class="fabric-overlay" style="position:absolute;inset:0;z-index:1;pointer-events:none;overflow:hidden">
    <?php foreach ($fabric_objects as $obj):
        echo ep_render_fabric_object(is_array($obj) ? $obj : [], $base_path, $itens ?? []);
    endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="encarte-overlay-grid ep-absolute ep-inset-x-10 ep-top-132 ep-bottom-12 ep-grid ep-grid-cols-3 ep-grid-rows-2 ep-gap-6"<?= $tem_fabric_overlay ? ' style="z-index:2;position:relative"' : '' ?>>
    <?php foreach ($itensGrid as $item):
        $nome = htmlspecialchars($item['nome_comercial'] ?? '', ENT_QUOTES, 'UTF-8');
        $foto = marketing_encarte_foto_src($base_path, $item);
        $partes = ep_premium_preco_partes((float) ($item['preco_promocional'] ?? 0));
        $features = ep_premium_features($item, $modelo_config);
    ?>
    <article class="card card-glow">
      <div class="card-header card-glow-header font-display"><?= $nome ?></div>
      <div class="card-body">
        <div class="features">
          <?php foreach ($features as $feat): ?>
          <div class="feature-item">
            <span class="feature-icon"><?= $feat['html'] ?></span>
            <?= htmlspecialchars($feat['texto'], ENT_QUOTES, 'UTF-8') ?>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="image-area">
          <?php if ($foto !== ''): ?>
            <img class="foto-flutuante foto-flutuante--forte" src="<?= $foto ?>" alt="<?= $nome ?>">
          <?php endif; ?>
        </div>
      </div>
      <div class="card-price font-price">
        <span class="currency preco-3d">R$</span>
        <span class="value preco-3d"><?= $partes['reais'] ?></span>
        <span class="cents preco-3d"><?= $partes['centavos'] ?></span>
      </div>
    </article>
    <?php endforeach; ?>
  </div>

  <?php if (!$tem_fabric_overlay): ?>
  <footer class="encarte-footer-overlay">
    <div class="footer-info">
      <span class="feature-icon">📍</span>
      <div class="footer-text">
        <h4>Visite Nossa Loja</h4>
        <?php if ($footerEndereco !== ''): ?><p><?= $footerEndereco ?></p><?php endif; ?>
        <?php if ($footerCidade !== ''): ?><span><?= $footerCidade ?></span><?php endif; ?>
      </div>
    </div>
    <div class="footer-info" style="justify-content:flex-end">
      <div class="footer-text" style="text-align:right">
        <h4>Fale com a gente</h4>
        <?php if ($footerWhatsapp !== ''): ?><p><?= $footerWhatsapp ?></p><?php endif; ?>
        <span>WhatsApp Comercial</span>
      </div>
      <span class="feature-icon">💬</span>
    </div>
  </footer>
  <?php endif; ?>

  <?php if ($rodapeLegal !== ''): ?>
    <p class="rodape-legal encarte-footer-overlay" style="bottom:8px"><?= $rodapeLegal ?></p>
  <?php endif; ?>

</div>
</body>
</html>
