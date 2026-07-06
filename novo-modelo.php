<?php
declare(strict_types=1);
require_once __DIR__ . '/config/bootstrap.php';

$page_title = 'Novo Modelo';
$nav_active = 'gerenciar_modelos';
$formatos = marketing_formatos();
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

<main class="app-main novo-modelo-page">
  <div class="page-hero page-hero--compact">
    <div class="page-hero-text">
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="config.php">Configuracoes</a>
        <span aria-hidden="true">/</span>
        <a href="gerenciar-modelos.php">Modelos</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">Novo modelo</span>
      </nav>
      <p class="page-hero-kicker">Administracao</p>
      <h1 class="page-hero-title">Criar modelo de encarte</h1>
      <p class="page-hero-desc">
        Envie o design de fundo pronto. Apos a criacao, o editor visual abrira para voce
        definir zonas de produto, precos e textos dinamicos.
      </p>
    </div>
    <div class="page-hero-actions">
      <a href="gerenciar-modelos.php" class="btn btn-secondary">Voltar</a>
    </div>
  </div>

  <div class="novo-modelo-shell">
    <div id="novo-modelo-alert" class="form-alert-slot" aria-live="polite"></div>

    <section class="form-panel novo-modelo-panel" aria-labelledby="novo-modelo-panel-title">
      <div class="panel-head">
        <h2 id="novo-modelo-panel-title">Dados do modelo</h2>
        <p>O fundo deve ter exatamente as dimensoes do formato escolhido.</p>
      </div>

      <ol class="novo-modelo-steps" aria-label="Etapas do fluxo">
        <li class="novo-modelo-step is-current">
          <span class="novo-modelo-step-num" aria-hidden="true">1</span>
          <span>Enviar fundo</span>
        </li>
        <li class="novo-modelo-step">
          <span class="novo-modelo-step-num" aria-hidden="true">2</span>
          <span>Editor visual</span>
        </li>
        <li class="novo-modelo-step">
          <span class="novo-modelo-step-num" aria-hidden="true">3</span>
          <span>Salvar modelo</span>
        </li>
      </ol>

      <form id="form-novo-modelo" class="config-form" enctype="multipart/form-data">
        <div class="form-grid form-grid--novo-modelo">
          <div class="form-field form-field--wide">
            <label for="input-nome-modelo">Nome do modelo</label>
            <input
              type="text"
              id="input-nome-modelo"
              name="nome_exibicao"
              placeholder="Ex.: Encarte Promocional Verao"
              required
              maxlength="100"
              autocomplete="off"
            >
            <p class="form-field-hint">Nome exibido na selecao de modelos e na galeria administrativa.</p>
          </div>

          <div class="form-field form-field--wide">
            <label for="select-formato-modelo">Formato inicial</label>
            <select id="select-formato-modelo" name="formato" required>
              <?php foreach ($formatos as $codigo => $meta): ?>
                <option
                  value="<?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?>"
                  data-width="<?= (int) $meta['width'] ?>"
                  data-height="<?= (int) $meta['height'] ?>"
                  <?= $codigo === '9x16' ? 'selected' : '' ?>
                >
                  <?= htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
            <p id="formato-dims-hint" class="form-field-hint">
              Tamanho ideal:
              <span class="format-dims-badge"><strong>1080×1920 px</strong> · PNG ou JPEG</span>
            </p>
          </div>

          <div class="form-field form-field--wide">
            <span class="form-field-label-block" id="upload-label-text">Design de fundo</span>
            <label class="novo-modelo-upload" for="input-fundo-modelo">
              <input
                type="file"
                id="input-fundo-modelo"
                name="fundo"
                accept="image/png,image/jpeg"
                required
                hidden
              >
              <span class="novo-modelo-upload-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.75">
                  <path d="M12 16V4m0 0l-4 4m4-4l4 4M4 17v1a2 2 0 002 2h12a2 2 0 002-2v-1"/>
                </svg>
              </span>
              <span class="novo-modelo-upload-title">Clique para escolher ou arraste o arquivo</span>
              <span id="upload-file-name" class="novo-modelo-upload-file">Nenhum arquivo selecionado</span>
            </label>
            <p class="form-field-hint">Use a arte base final, sem produtos — apenas o layout de fundo.</p>
          </div>
        </div>

        <div class="form-actions form-actions--sticky">
          <button type="submit" id="btn-criar-modelo" class="btn btn-primary btn-lg">
            Criar e abrir editor
          </button>
        </div>
      </form>
    </section>
  </div>
</main>

<div id="loader-overlay" class="loader-overlay">
  <div class="loader-box">
    <div class="loader-spinner"></div>
    <strong>Processando...</strong>
    <p class="loader-msg">Criando modelo...</p>
  </div>
</div>

<script src="public/js/app.js"></script>
<script src="public/js/novo-modelo.js"></script>
</body>
</html>
