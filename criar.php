<?php
declare(strict_types=1);
require_once __DIR__ . '/config/bootstrap.php';

$encarte_id = (int) ($_GET['id'] ?? 0);
$modelo_codigo = trim((string) ($_GET['modelo'] ?? ''));
$encarteService = new EncarteService();
$formatos = marketing_formatos();
$modelo_selecionado = null;

if ($encarte_id <= 0) {
    if ($modelo_codigo === '') {
        header('Location: modelos.php');
        exit;
    }

    $modelo_selecionado = $encarteService->buscarModeloPorCodigo($modelo_codigo);
    if ($modelo_selecionado === null) {
        header('Location: modelos.php');
        exit;
    }
} else {
    $encarte_existente = $encarteService->buscarPorId($encarte_id);
    if ($encarte_existente === null) {
        header('Location: index.php');
        exit;
    }

    $modelo_codigo = (string) ($encarte_existente['modelo_layout'] ?? 'modelo_01');
    $modelo_selecionado = $encarteService->buscarModeloPorCodigoQualquer($modelo_codigo);
}

$page_title = $encarte_id ? 'Editar Encarte' : 'Novo Encarte';
$nav_active = 'criar';
$modelo_nome = htmlspecialchars(
    (string) ($modelo_selecionado['nome_exibicao'] ?? $modelo_codigo),
    ENT_QUOTES,
    'UTF-8'
);
$modeloService = new ModeloLayoutService();
$modelo_config_merged = $modelo_selecionado !== null
    ? $modeloService->configVisualMerged($modelo_selecionado)
    : [];
$slots_modelo = $modeloService->contarSlotsProduto($modelo_config_merged);
$max_itens_default = (int) ($modelo_selecionado['max_itens_default'] ?? 12);
if ($slots_modelo > 0) {
    $max_itens_default = $slots_modelo;
}
$max_itens_readonly = true;
$formatos_permitidos = json_decode((string) ($modelo_selecionado['formatos_suportados'] ?? '[]'), true);
if (!is_array($formatos_permitidos) || $formatos_permitidos === []) {
    $formatos_permitidos = array_keys($formatos);
}
$formatos_filtrados = array_intersect_key($formatos, array_flip($formatos_permitidos));
if ($formatos_filtrados === []) {
    $formatos_filtrados = $formatos;
}
$formato_default = (string) ($formatos_permitidos[0] ?? array_key_first($formatos_filtrados));
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
  <input type="hidden" id="encarte-id" value="<?= $encarte_id ?>">
  <input type="hidden" id="modelo-layout" value="<?= htmlspecialchars($modelo_codigo, ENT_QUOTES, 'UTF-8') ?>">

  <div class="page-hero page-hero--compact">
    <div class="page-hero-text">
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Galeria</a>
        <span aria-hidden="true">/</span>
        <?php if (!$encarte_id): ?>
          <a href="modelos.php">Modelos</a>
          <span aria-hidden="true">/</span>
        <?php endif; ?>
        <span aria-current="page"><?= $encarte_id ? 'Editar' : 'Novo' ?></span>
      </nav>
      <p class="page-hero-kicker"><?= $encarte_id ? 'Edicao de campanha' : 'Novo encarte' ?></p>
      <h1 class="page-hero-title"><?= htmlspecialchars($page_title) ?></h1>
      <p class="page-hero-desc">Configure a campanha, adicione produtos e gere o PNG nos formatos de rede social.</p>
    </div>
  </div>

  <div class="modelo-selecionado-banner">
    <div class="modelo-selecionado-info">
      <span class="modelo-selecionado-kicker">Modelo selecionado</span>
      <strong><?= $modelo_nome ?></strong>
    </div>
    <?php if (!$encarte_id): ?>
      <a href="modelos.php" class="btn btn-sm btn-secondary">Trocar modelo</a>
    <?php endif; ?>
  </div>

  <section class="form-panel">
    <div class="panel-head">
      <h2>Configuracao do Encarte</h2>
      <p>Dados gerais da campanha promocional</p>
    </div>
    <div class="form-grid form-grid--config">
      <div class="form-field form-field--wide">
        <label for="titulo-campanha">Titulo da Campanha</label>
        <input type="text" id="titulo-campanha" placeholder="Ex: Super Ofertas de Iluminacao">
        <button type="button" id="btn-titulos-ia" class="btn btn-sm btn-secondary btn-ia">Gerar titulos com IA</button>
        <div id="titulos-ia" class="titulos-ia"></div>
      </div>
      <div class="form-field">
        <label for="formato">Formato</label>
        <select id="formato">
          <?php foreach ($formatos_filtrados as $codigo => $info): ?>
            <option value="<?= htmlspecialchars($codigo) ?>"<?= $codigo === $formato_default ? ' selected' : '' ?>><?= htmlspecialchars($info['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-field">
        <label for="max-itens">Max. Itens</label>
        <input type="number" id="max-itens" value="<?= $max_itens_default ?>" min="1" max="<?= $max_itens_default ?>" readonly aria-readonly="true">
        <p class="form-field-hint">Definido pelo modelo (<?= $max_itens_default ?> produto<?= $max_itens_default === 1 ? '' : 's' ?>).</p>
      </div>
      <div class="form-field">
        <label for="validade-inicio">Validade Inicio</label>
        <input type="date" id="validade-inicio">
      </div>
      <div class="form-field">
        <label for="validade-fim">Validade Fim</label>
        <input type="date" id="validade-fim">
      </div>
      <div class="form-field form-field--wide">
        <label for="texto-rodape">Texto Legal (Rodape)</label>
        <textarea id="texto-rodape" placeholder="Ofertas validas enquanto durarem os estoques.">Ofertas validas enquanto durarem os estoques.</textarea>
      </div>
    </div>
  </section>

  <section class="form-panel">
    <div class="panel-head panel-head--row">
      <div>
        <h2>Produtos</h2>
        <p>Busque pelo SKU ou preencha manualmente. Arraste para reordenar. Este modelo comporta ate <strong><?= $max_itens_default ?></strong> produto<?= $max_itens_default === 1 ? '' : 's' ?> — produto 1 preenche o card 1, produto 2 o card 2, e assim por diante.</p>
      </div>
      <button type="button" id="btn-add-item" class="btn btn-secondary">+ Adicionar Produto</button>
    </div>
    <div id="itens-lista" class="itens-lista"></div>
    <div class="form-actions">
      <button type="button" id="btn-salvar" class="btn btn-secondary">Salvar Rascunho</button>
      <button type="button" id="btn-gerar" class="btn btn-primary">Gerar Encarte PNG</button>
    </div>
    <div id="encarte-preview-result" class="encarte-preview-result hidden" hidden>
      <h3 class="encarte-preview-title">Encarte gerado</h3>
      <div class="encarte-preview-frame">
        <img id="encarte-preview-img" alt="Preview do encarte gerado">
      </div>
      <div class="form-actions">
        <a href="index.php" class="btn btn-primary">Ver galeria</a>
      </div>
    </div>
  </section>
</main>

<div id="loader-overlay" class="loader-overlay">
  <div class="loader-box">
    <div class="loader-spinner"></div>
    <strong>Processando...</strong>
    <p class="loader-msg">Aguarde.</p>
  </div>
</div>

<script src="public/js/app.js"></script>
<script src="public/js/criar.js"></script>
</body>
</html>
