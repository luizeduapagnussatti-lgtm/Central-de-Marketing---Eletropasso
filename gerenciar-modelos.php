<?php
declare(strict_types=1);
require_once __DIR__ . '/config/bootstrap.php';

$page_title = 'Gerenciar Modelos';
$nav_active = 'gerenciar_modelos';

$modeloService = new ModeloLayoutService();
$modelos = $modeloService->listarTodos();
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
  <div class="page-hero page-hero--compact">
    <div class="page-hero-text">
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="config.php">Configuracoes</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">Modelos</span>
      </nav>
      <p class="page-hero-kicker">Administracao</p>
      <h1 class="page-hero-title">Gerenciar modelos de encarte</h1>
      <p class="page-hero-desc">Edite cores, textos e formatos. Modelos inativos nao aparecem na selecao de novo encarte.</p>
    </div>
    <div class="page-hero-actions">
      <a href="novo-modelo.php" class="btn btn-primary">+ Novo modelo</a>
      <a href="modelos.php" class="btn btn-secondary">Ver selecao do usuario</a>
    </div>
  </div>

  <div id="modelos-admin-alert" class="form-alert-slot" aria-live="polite"></div>

  <?php if ($modelos === []): ?>
    <div class="empty-state">
      <p>Nenhum modelo cadastrado.</p>
      <p class="empty-state-desc">Envie um design de fundo e defina as zonas de produto no editor visual.</p>
      <a href="novo-modelo.php" class="btn btn-primary">+ Criar primeiro modelo</a>
    </div>
  <?php else: ?>
    <div class="modelos-grid modelos-grid--admin" role="list">
      <?php foreach ($modelos as $modelo):
          $id = (int) $modelo['id'];
          $codigoRaw = (string) $modelo['codigo'];
          $codigo = htmlspecialchars($codigoRaw, ENT_QUOTES, 'UTF-8');
          $nome = htmlspecialchars((string) $modelo['nome_exibicao'], ENT_QUOTES, 'UTF-8');
          $preview = marketing_modelo_preview_src($codigoRaw);
          $formatosLabel = marketing_modelo_formatos_label($modelo);
          $maxItens = (int) ($modelo['max_itens_default'] ?? 12);
          $ativo = (int) ($modelo['ativo'] ?? 0) === 1;
          $descricao = marketing_modelo_descricao($modelo);
      ?>
      <article class="modelo-card modelo-card--admin" role="listitem" data-modelo-id="<?= $id ?>">
        <div class="modelo-card-status">
          <span class="status-badge status-badge--<?= $ativo ? 'ativo' : 'inativo' ?>">
            <?= $ativo ? 'Ativo' : 'Inativo' ?>
          </span>
          <span class="modelo-codigo"><?= $codigo ?></span>
        </div>
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
          <p class="modelo-card-desc"><?= htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8') ?></p>
          <ul class="modelo-meta">
            <li>Até <?= $maxItens ?> produtos</li>
            <?php if ($formatosLabel !== ''): ?>
              <li><?= htmlspecialchars($formatosLabel, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endif; ?>
          </ul>
          <div class="modelo-card-actions modelo-card-actions--admin">
            <a href="editar-modelo.php?id=<?= $id ?>" class="btn btn-primary btn-sm">Editar</a>
            <button
              type="button"
              class="btn btn-secondary btn-sm js-toggle-ativo"
              data-id="<?= $id ?>"
              data-ativo="<?= $ativo ? '1' : '0' ?>"
            ><?= $ativo ? 'Inativar' : 'Reativar' ?></button>
            <button
              type="button"
              class="btn btn-danger btn-sm js-excluir-modelo"
              data-id="<?= $id ?>"
              data-nome="<?= $nome ?>"
            >Excluir</button>
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
<script src="public/js/gerenciar-modelos.js"></script>
</body>
</html>
