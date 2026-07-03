<?php
/**
 * Template Fabric generico — render 100% via fabric_state salvo no editor.
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

$modelo_config = $modelo_config ?? (new ModeloLayoutService())->configVisualPadrao('modelo_01');
$cores = $modelo_config['cores'] ?? [];

$fundo_src = marketing_encarte_fundo_src($base_path, $modelo_config, $formato);

$utils_css = marketing_encarte_stylesheet_url($base_path, 'assets/encarte/encarte-utilities.css');
$fonts_css = marketing_encarte_stylesheet_url($base_path, 'assets/encarte/fonts.css');

$fabric_state = $modelo_config['fabric_state'] ?? null;
$fabric_objects = is_array($fabric_state['objects'] ?? null) ? $fabric_state['objects'] : [];
$tem_fabric_palco = false;
foreach ($fabric_objects as $fabric_obj) {
    if (is_array($fabric_obj) && (string) ($fabric_obj['name'] ?? '') === 'palco') {
        $tem_fabric_palco = true;
        break;
    }
}

$corFundo = htmlspecialchars((string) ($cores['fundo'] ?? '#ffffff'), ENT_QUOTES, 'UTF-8');
$palco_style = $fundo_src !== '' && !$tem_fabric_palco
    ? 'background-image: url(' . htmlspecialchars($fundo_src, ENT_QUOTES, 'UTF-8') . '); background-size: cover; background-position: center top;'
    : 'background-color: ' . $corFundo . ';';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Encarte — Eletropasso</title>
<link rel="stylesheet" href="<?= htmlspecialchars($fonts_css, ENT_QUOTES, 'UTF-8') ?>">
<link rel="stylesheet" href="<?= htmlspecialchars($utils_css, ENT_QUOTES, 'UTF-8') ?>">
<style>
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
  <?= $palco_style ?>
}

.fabric-overlay {
  position: absolute;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
}

.fabric-text.font-display {
  font-family: 'Bebas Neue', 'Oswald', Impact, sans-serif;
  letter-spacing: 0.02em;
}

.preco-3d {
  text-shadow: 2px 2px 0 rgba(0, 0, 0, 0.35);
}

.foto-flutuante {
  object-fit: contain;
}

.foto-flutuante--forte {
  filter: drop-shadow(-12px 16px 12px rgba(0, 0, 0, 0.75));
}

.encarte-palco-img {
  object-fit: cover;
}
</style>
</head>
<body>
<div class="encarte-container encarte-palco formato-<?= htmlspecialchars($formato, ENT_QUOTES, 'UTF-8') ?>">
  <?php if ($fabric_objects !== []): ?>
  <div class="fabric-overlay">
    <?php foreach ($fabric_objects as $obj):
        echo ep_render_fabric_object(is_array($obj) ? $obj : [], $base_path, $itens ?? []);
    endforeach; ?>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
