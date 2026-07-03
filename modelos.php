<?php

declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';



$page_title = 'Escolher Modelo';

$nav_active = 'modelos';



$encarteService = new EncarteService();

$modelos = $encarteService->listarModelos();

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= htmlspecialchars($page_title) ?> — Eletropasso</title>

<link rel="stylesheet" href="assets/brand/tokens.css">

<link rel="stylesheet" href="public/css/app.css">

</head>

<body class="app-shell">



<?php require marketing_path('views/partials/app_header.php'); ?>



<main class="app-main">

  <div class="page-hero">

    <div class="page-hero-text">

      <nav class="breadcrumb" aria-label="Breadcrumb">

        <a href="index.php">Galeria</a>

        <span aria-hidden="true">/</span>

        <span aria-current="page">Escolher modelo</span>

      </nav>

      <p class="page-hero-kicker">Novo encarte</p>

      <h1 class="page-hero-title">Escolha o modelo visual</h1>

      <p class="page-hero-desc">Selecione o layout da campanha. Clique na imagem para ver o preview em tela cheia antes de continuar.</p>

    </div>

  </div>



  <?php if ($modelos === []): ?>

    <div class="empty-state">

      <p>Nenhum modelo de encarte disponivel no momento.</p>

      <a href="index.php" class="btn btn-secondary">Voltar para galeria</a>

    </div>

  <?php else: ?>

    <div class="modelos-grid" role="list">

      <?php foreach ($modelos as $modelo):

          $codigoRaw = (string) $modelo['codigo'];

          $codigo = htmlspecialchars($codigoRaw, ENT_QUOTES, 'UTF-8');

          $nome = htmlspecialchars((string) $modelo['nome_exibicao'], ENT_QUOTES, 'UTF-8');

          $preview = marketing_modelo_preview_src($codigoRaw);

          $formatosLabel = marketing_modelo_formatos_label($modelo);

          $maxItens = (int) ($modelo['max_itens_default'] ?? 12);

      ?>

      <article class="modelo-card" role="listitem">

        <div class="modelo-preview-frame">

        <?php if ($preview !== ''): ?>

          <button

            type="button"

            class="modelo-preview-btn"

            data-preview-src="<?= htmlspecialchars($preview, ENT_QUOTES, 'UTF-8') ?>"

            data-preview-title="<?= $nome ?>"

            aria-label="Ver preview completo do modelo <?= $nome ?>"

          >

            <img src="<?= htmlspecialchars($preview, ENT_QUOTES, 'UTF-8') ?>" alt="Preview do modelo <?= $nome ?>">

            <span class="modelo-preview-overlay">

              <span class="modelo-preview-zoom">Ver em tela cheia</span>

            </span>

          </button>

        <?php else: ?>

          <div class="modelo-preview modelo-preview--empty">

            <div class="modelo-preview-placeholder">

              <span class="modelo-preview-icon" aria-hidden="true">&#9889;</span>

              <span>Preview em breve</span>

            </div>

          </div>

        <?php endif; ?>

        </div>

        <div class="modelo-card-body">

          <h3 class="modelo-card-title"><?= $nome ?></h3>

          <p class="modelo-card-desc"><?= htmlspecialchars(marketing_modelo_descricao($modelo), ENT_QUOTES, 'UTF-8') ?></p>

          <ul class="modelo-meta">

            <li>Até <?= $maxItens ?> produtos</li>

            <?php if ($formatosLabel !== ''): ?>

              <li><?= htmlspecialchars($formatosLabel, ENT_QUOTES, 'UTF-8') ?></li>

            <?php endif; ?>

          </ul>

          <div class="modelo-card-actions">

            <a href="criar.php?modelo=<?= $codigo ?>" class="btn btn-primary modelo-card-cta">Usar este modelo</a>

          </div>

        </div>

      </article>

      <?php endforeach; ?>

    </div>

  <?php endif; ?>

</main>



<div id="lightbox" class="lightbox" role="dialog" aria-modal="true" aria-label="Preview do modelo">

  <button class="lightbox-close" type="button" aria-label="Fechar preview">&times;</button>

  <figure class="lightbox-figure">

    <img src="" alt="">

    <figcaption class="lightbox-caption"></figcaption>

  </figure>

</div>



<script src="public/js/app.js"></script>

<script src="public/js/modelos.js"></script>

</body>

</html>

