<?php
declare(strict_types=1);
require_once __DIR__ . '/config/bootstrap.php';
$page_title = 'Galeria de Encartes';
$nav_active = 'galeria';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<?php require marketing_path('views/partials/app_head.php'); ?>
<link rel="stylesheet" href="assets/brand/tokens.css">
<link rel="stylesheet" href="public/css/app.css">
</head>
<body class="app-shell">

<?php require marketing_path('views/partials/app_header.php'); ?>

<main class="app-main">
  <div class="page-hero">
    <div class="page-hero-text">
      <p class="page-hero-kicker">Encartes promocionais</p>
      <h1 class="page-hero-title">Sua galeria</h1>
      <p class="page-hero-desc">Visualize, baixe, edite ou exclua encartes gerados por mes de vigencia.</p>
    </div>
  </div>

  <div id="galeria">
    <p class="loading-msg">Carregando encartes...</p>
  </div>
</main>

<div id="loader-overlay" class="loader-overlay">
  <div class="loader-box">
    <div class="loader-spinner"></div>
    <strong>Gerando encarte...</strong>
    <p class="loader-msg">Aguarde, processando imagem.</p>
  </div>
</div>

<div id="lightbox" class="lightbox">
  <button class="lightbox-close" type="button" aria-label="Fechar">&times;</button>
  <img src="" alt="Encarte">
</div>

<script src="public/js/app.js"></script>
<script src="public/js/galeria.js"></script>
</body>
</html>
