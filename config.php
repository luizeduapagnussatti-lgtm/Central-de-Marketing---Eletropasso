<?php

declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';

$page_title = 'Configuracoes';

$nav_active = 'config';

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= htmlspecialchars($page_title) ?> — Central de Marketing</title>

<link rel="stylesheet" href="assets/brand/tokens.css">

<link rel="stylesheet" href="public/css/app.css">

</head>

<body class="app-shell">



<?php require marketing_path('views/partials/app_header.php'); ?>



<main class="app-main app-main--config">

  <div class="page-hero page-hero--compact">

    <div class="page-hero-text">

      <p class="page-hero-kicker">Central de Marketing</p>

      <h1 class="page-hero-title">Configuracoes</h1>

      <p class="page-hero-desc">Integracoes, fotos de produtos e textos padrao dos encartes.</p>

    </div>

  </div>



  <div id="config-alerts" aria-live="polite"></div>



  <section class="config-status" id="config-status" aria-label="Status das integracoes">

    <p class="config-status-loading">Verificando integracoes...</p>

  </section>



  <form id="form-config" class="config-form">

    <section class="form-panel">

      <div class="panel-head">

        <h2>Inteligencia artificial</h2>

        <p>Normaliza nomes de produtos e sugere titulos de campanha via Google Gemini.</p>

      </div>

      <div class="form-grid form-grid--config">

        <div class="form-field form-field--wide">

          <label for="cfg-gemini-key">Chave da API Gemini</label>

          <input type="password" name="gemini_api_key" id="cfg-gemini-key" autocomplete="off"

                 placeholder="Cole a chave do Google AI Studio">

          <p class="form-field-hint">Obrigatoria para nomes comerciais e titulos com IA. Sem chave, o sistema usa textos basicos.</p>

        </div>

        <div class="form-field">

          <label for="cfg-gemini-model">Modelo</label>

          <select name="gemini_model" id="cfg-gemini-model">

            <option value="gemini-2.5-flash">gemini-2.5-flash (recomendado)</option>

            <option value="gemini-2.0-flash">gemini-2.0-flash</option>

            <option value="gemini-1.5-flash">gemini-1.5-flash</option>

          </select>

        </div>

      </div>

    </section>



    <section class="form-panel">

      <div class="panel-head">

        <h2>Hub de Precificacao</h2>

        <p>Busca automatica de SKU, nome ERP e preco ao criar encartes.</p>

      </div>

      <div class="form-grid form-grid--config">

        <div class="form-field form-field--wide">

          <label for="cfg-hub-url">URL da API</label>

          <input type="url" name="hub_api_url" id="cfg-hub-url"

                 placeholder="http://localhost/orcador_dev/api/marketing/produto.php">

        </div>

        <div class="form-field form-field--wide">

          <label for="cfg-hub-token">Token Bearer</label>

          <input type="password" name="hub_api_token" id="cfg-hub-token" autocomplete="off"

                 placeholder="Token de autenticacao do Hub">

          <p class="form-field-hint">Mesmo token configurado no orcador_dev para o endpoint de marketing.</p>

        </div>

      </div>

    </section>



    <section class="form-panel">

      <div class="panel-head">

        <h2>Encartes</h2>

        <p>Texto legal exibido no rodape quando a campanha nao define um proprio.</p>

      </div>

      <div class="form-grid form-grid--config">

        <div class="form-field form-field--wide">

          <label for="cfg-rodape">Texto legal padrao (rodape)</label>

          <textarea name="watermark_rodape" id="cfg-rodape" rows="3"

                    placeholder="Ex.: Ofertas validas enquanto durarem os estoques. Imagens meramente ilustrativas."></textarea>

        </div>

      </div>

    </section>



    <section class="form-panel">

      <div class="panel-head">

        <h2>Fotos de produtos</h2>

        <p>Upload na lista de itens com remocao automatica de fundo (Rembg).</p>

      </div>

      <div class="form-grid form-grid--config">

        <div class="form-field">

          <label for="cfg-max-upload">Tamanho maximo (MB)</label>

          <input type="number" name="max_upload_size_mb" id="cfg-max-upload" min="1" max="50" value="10">

          <p class="form-field-hint">JPG, PNG ou WebP enviados pelo operador.</p>

        </div>

        <div class="form-field form-field--wide">

          <details class="config-advanced">

            <summary>Configuracao tecnica do Rembg</summary>

            <div class="config-advanced-body">

              <label for="cfg-rembg-bin">Executavel Rembg</label>

              <input type="text" name="rembg_bin" id="cfg-rembg-bin"

                     placeholder="rembg ou caminho completo (ex.: C:\Python313\Scripts\rembg.exe)">

              <p class="form-field-hint">Requer Python com <code>pip install rembg[cli] onnxruntime</code>. Usado apenas no servidor.</p>

            </div>

          </details>

        </div>

      </div>

    </section>



    <div class="config-form-actions">

      <button type="submit" class="btn btn-primary">Salvar configuracoes</button>

    </div>

  </form>

</main>



<script src="public/js/app.js"></script>

<script src="public/js/config.js"></script>

</body>

</html>

