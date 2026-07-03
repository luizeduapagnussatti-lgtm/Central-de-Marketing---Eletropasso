<?php
declare(strict_types=1);
require_once __DIR__ . '/config/bootstrap.php';

$modelo_id = (int) ($_GET['id'] ?? 0);
if ($modelo_id <= 0) {
    header('Location: gerenciar-modelos.php');
    exit;
}

$modeloService = new ModeloLayoutService();
$modelo = $modeloService->buscarPorId($modelo_id);

if ($modelo === null) {
    header('Location: gerenciar-modelos.php');
    exit;
}

$page_title = 'Editor Visual';
$formatos = marketing_formatos();
$config = $modeloService->configVisualMerged($modelo);
$formatosSuportados = $modelo['formatos_suportados'] ?? [];
$formatoAtual = (string) ($formatosSuportados[0] ?? '9x16');

$editorData = [
    'modelo' => [
        'id'                  => (int) $modelo['id'],
        'codigo'              => (string) $modelo['codigo'],
        'nome_exibicao'       => (string) $modelo['nome_exibicao'],
        'arquivo_template'    => (string) ($modelo['arquivo_template'] ?? 'modelo_fabric.php'),
        'formatos_suportados' => $formatosSuportados,
        'max_itens_default'   => (int) ($modelo['max_itens_default'] ?? 12),
    ],
    'config'     => $config,
    'formatos'   => $formatos,
    'formato'    => $formatoAtual,
    'apiBase'    => 'api/index.php',
    'assetsBase' => '',
];

$coresLabels = [
    'primary'      => 'Primaria',
    'dark'         => 'Escuro',
    'fundo'        => 'Fundo claro',
    'fundo_escuro' => 'Fundo escuro',
    'preco_bg'     => 'Fundo do preco',
    'preco_texto'  => 'Texto do preco',
    'badge_bg'     => 'Fundo do badge',
    'badge_texto'  => 'Texto do badge',
];

$textosEditor = [
    'titulo_linha1'   => ['label' => 'Titulo linha 1', 'id' => 'texto-titulo-linha1'],
    'titulo_linha2'   => ['label' => 'Titulo linha 2', 'id' => 'texto-titulo-linha2'],
    'badge_oferta'    => ['label' => 'Badge oferta', 'id' => 'texto-badge-oferta'],
    'faixa_oferta'    => ['label' => 'Faixa oferta', 'id' => 'texto-faixa-oferta'],
    'subtitulo'       => ['label' => 'Subtitulo', 'id' => 'texto-subtitulo'],
    'footer_endereco' => ['label' => 'Endereco', 'id' => 'texto-footer-endereco'],
    'footer_cidade'   => ['label' => 'Cidade', 'id' => 'texto-footer-cidade'],
    'footer_whatsapp' => ['label' => 'WhatsApp', 'id' => 'texto-footer-whatsapp'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?> — Eletropasso</title>
<link rel="stylesheet" href="assets/brand/tokens.css">
<link rel="stylesheet" href="public/css/editor-modelo.css">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        ep: { DEFAULT: '#b91c1c', dark: '#7f1d1d' }
      }
    }
  }
};
</script>
<script src="https://cdn.jsdelivr.net/npm/fabric@5/dist/fabric.min.js"></script>
</head>
<body class="editor-workstation text-white flex flex-col h-screen overflow-hidden">

<header class="editor-topbar flex items-center justify-between px-6 py-3 shrink-0">
  <div class="flex flex-col gap-1 min-w-0">
    <nav class="editor-breadcrumb text-xs flex items-center gap-2">
      <a href="gerenciar-modelos.php">Modelos</a>
      <span aria-hidden="true">/</span>
      <span>Editor visual</span>
    </nav>
    <div class="flex items-center gap-3 flex-wrap">
      <input
        type="text"
        id="input-nome"
        class="editor-nome-input"
        value="<?= htmlspecialchars((string) $modelo['nome_exibicao'], ENT_QUOTES, 'UTF-8') ?>"
        placeholder="Nome do modelo"
        aria-label="Nome do modelo"
      >
      <span class="text-xs text-slate-500 font-mono"><?= htmlspecialchars((string) $modelo['codigo'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </div>
  <div class="flex items-center gap-3 shrink-0">
    <select id="select-formato" class="editor-select">
      <?php foreach ($formatosSuportados as $fmt):
          if (!isset($formatos[$fmt])) continue;
      ?>
        <option value="<?= htmlspecialchars($fmt, ENT_QUOTES, 'UTF-8') ?>" <?= $fmt === $formatoAtual ? 'selected' : '' ?>>
          <?= htmlspecialchars($formatos[$fmt]['label'], ENT_QUOTES, 'UTF-8') ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="button" id="btn-salvar" class="editor-btn editor-btn--primary">Salvar</button>
    <button type="button" id="btn-exportar-png" class="editor-btn editor-btn--secondary">Exportar PNG</button>
    <a href="gerenciar-modelos.php" class="editor-btn editor-btn--ghost">Voltar</a>
  </div>
</header>

<div id="editor-alert" class="editor-alert-slot" aria-live="polite"></div>

<div class="editor-layout flex flex-1 min-h-0">
  <aside class="editor-sidebar flex flex-col shrink-0">
    <nav class="editor-tabs flex" role="tablist">
      <button type="button" class="editor-tab is-active" data-tab="upload" role="tab" aria-selected="true">Upload</button>
      <button type="button" class="editor-tab" data-tab="biblioteca" role="tab" aria-selected="false">Elementos</button>
      <button type="button" class="editor-tab" data-tab="fundo" role="tab" aria-selected="false">Fundo</button>
      <button type="button" class="editor-tab" data-tab="textos" role="tab" aria-selected="false">Textos</button>
      <button type="button" class="editor-tab" data-tab="produtos" role="tab" aria-selected="false">Produtos</button>
      <button type="button" class="editor-tab" data-tab="camadas" role="tab" aria-selected="false">Camadas</button>
    </nav>

    <div class="editor-tab-panels flex-1 overflow-y-auto">
      <section id="tab-upload" class="editor-tab-panel" role="tabpanel">
        <div class="editor-section">
          <h4 class="editor-section-title">Upload de elemento</h4>
          <p class="editor-section-desc">Envie PNG/JPG/WebP. O Rembg remove o fundo e insere no canvas.</p>
          <label class="editor-upload-btn">
            <input type="file" id="input-upload-elemento" accept="image/png,image/jpeg,image/webp" hidden>
            <span>Selecionar imagem</span>
          </label>
        </div>
        <div class="editor-section editor-section--bordered">
          <h4 class="editor-section-title">Miniatura do canvas</h4>
          <p class="editor-section-desc">Atualizada ao salvar o modelo.</p>
          <div class="editor-canvas-thumb-wrap">
            <img id="preview-canvas-thumb" src="" alt="Miniatura do canvas" hidden>
            <div id="preview-canvas-empty" class="editor-canvas-thumb-empty">Salve para gerar miniatura</div>
          </div>
        </div>
      </section>

      <section id="tab-biblioteca" class="editor-tab-panel hidden" role="tabpanel" hidden>
        <div class="editor-section">
          <h4 class="editor-section-title">Biblioteca visual</h4>
          <p class="editor-section-desc">Clique para inserir no centro do canvas. Vetores escalaveis — altere cor e tamanho livremente.</p>
          <div id="asset-library" class="asset-library"></div>
        </div>
      </section>

      <section id="tab-fundo" class="editor-tab-panel hidden" role="tabpanel" hidden>
        <div class="editor-section">
          <h4 class="editor-section-title">Cores do modelo</h4>
          <div class="editor-color-grid">
            <?php foreach ($coresLabels as $chave => $label):
                $valor = htmlspecialchars((string) ($config['cores'][$chave] ?? '#b91c1c'), ENT_QUOTES, 'UTF-8');
            ?>
            <div class="editor-color-field">
              <label for="cor-<?= $chave ?>" class="editor-field-label"><?= $label ?></label>
              <div class="editor-color-row">
                <input type="color" id="cor-<?= $chave ?>" data-cor="<?= $chave ?>" value="<?= $valor ?>" class="editor-color-picker">
                <input type="text" class="editor-color-hex" data-cor-hex="<?= $chave ?>" value="<?= $valor ?>" maxlength="7">
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="editor-section editor-section--bordered">
          <h4 class="editor-section-title">Palco estatico</h4>
          <p class="editor-section-desc">PNG/JPEG com dimensao exata do formato selecionado.</p>
          <label class="editor-upload-btn">
            <input type="file" id="input-upload-fundo" accept="image/png,image/jpeg" hidden>
            <span>Enviar palco</span>
          </label>
          <p id="fundo-status" class="editor-status-text"></p>
        </div>
      </section>

      <section id="tab-textos" class="editor-tab-panel hidden" role="tabpanel" hidden>
        <div class="editor-section">
          <h4 class="editor-section-title">Textos promocionais</h4>
          <p class="editor-section-desc">Alteracoes refletem no canvas em tempo real.</p>
          <?php foreach ($textosEditor as $chave => $meta):
              if (!array_key_exists($chave, $config['textos'] ?? []) && !in_array($chave, ['titulo_linha1', 'titulo_linha2', 'badge_oferta'], true)) {
                  continue;
              }
              $valor = htmlspecialchars((string) ($config['textos'][$chave] ?? ''), ENT_QUOTES, 'UTF-8');
          ?>
          <div class="editor-text-field">
            <label for="<?= $meta['id'] ?>" class="editor-field-label"><?= $meta['label'] ?></label>
            <input
              type="text"
              id="<?= $meta['id'] ?>"
              class="editor-text-input"
              data-texto="<?= $chave ?>"
              value="<?= $valor ?>"
            >
          </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section id="tab-produtos" class="editor-tab-panel hidden" role="tabpanel" hidden>
        <div class="editor-section">
          <h4 class="editor-section-title">Zona de produto</h4>
          <p class="editor-section-desc">Define onde a foto do produto aparecera no encarte final.</p>
          <button type="button" id="btn-add-zona" class="editor-btn editor-btn--primary editor-btn--block">
            + Zona de Produto
          </button>
        </div>
        <div class="editor-section editor-section--bordered">
          <h4 class="editor-section-title">Variaveis de texto</h4>
          <p class="editor-section-desc">Inseridas no canvas e vinculadas a um produto na geracao do encarte.</p>
          <div class="var-btn-grid">
            <button type="button" class="var-btn" data-vartype="nome_produto">[NOME_PRODUTO]</button>
            <button type="button" class="var-btn" data-vartype="preco_normal">[PRECO_NORMAL] riscado</button>
            <button type="button" class="var-btn" data-vartype="preco_promo">[PRECO_PROMO] destaque</button>
            <button type="button" class="var-btn" data-vartype="unidade">[UNIDADE]</button>
          </div>
        </div>
      </section>

      <section id="tab-camadas" class="editor-tab-panel hidden" role="tabpanel" hidden>
        <div class="editor-section">
          <div class="editor-section-header">
            <h4 class="editor-section-title">Camadas</h4>
            <button type="button" id="btn-atualizar-camadas" class="editor-link-btn">Atualizar</button>
          </div>
          <ul id="lista-camadas" class="editor-layers-list"></ul>
          <div class="editor-layer-actions">
            <button type="button" id="btn-trazer-frente" class="editor-btn editor-btn--secondary editor-btn--sm">Trazer frente</button>
            <button type="button" id="btn-enviar-fundo" class="editor-btn editor-btn--secondary editor-btn--sm">Enviar fundo</button>
          </div>
          <button type="button" id="btn-remover-camada" class="editor-btn editor-btn--danger editor-btn--sm editor-btn--block">Remover selecionado</button>
        </div>
      </section>
    </div>
  </aside>

  <main class="editor-workspace flex-1 overflow-auto flex items-start justify-center">
    <div id="canvas-wrapper" class="relative shadow-2xl">
      <canvas id="editor-canvas"></canvas>
    </div>
  </main>

  <aside id="painel-props" class="editor-props-panel hidden" aria-label="Propriedades do elemento">
    <header class="editor-props-header">
      <h3>Propriedades</h3>
      <button type="button" id="btn-fechar-props" class="editor-props-close" aria-label="Fechar painel">&times;</button>
    </header>

    <div class="editor-props-body">
      <section id="props-texto" class="props-section hidden">
        <h4 class="editor-section-title">Tipografia</h4>
        <div class="props-field">
          <label for="prop-font-size" class="editor-field-label">Tamanho da fonte</label>
          <div class="props-range-row">
            <input type="range" id="prop-font-size" min="8" max="200" step="1" class="props-range">
            <span class="props-range-val"><span id="prop-font-size-val">32</span>px</span>
          </div>
        </div>
        <div class="props-field">
          <label for="prop-text-color" class="editor-field-label">Cor do texto</label>
          <input type="color" id="prop-text-color" class="editor-color-picker props-color-picker">
        </div>
        <div class="props-field">
          <span class="editor-field-label">Alinhamento</span>
          <div class="props-align-buttons">
            <button type="button" class="props-align-btn" data-align="left" title="Esquerda">Esq</button>
            <button type="button" class="props-align-btn" data-align="center" title="Centro">Centro</button>
            <button type="button" class="props-align-btn" data-align="right" title="Direita">Dir</button>
          </div>
        </div>

        <h4 class="editor-section-title editor-section-title--spaced">Efeito 3D (sombra)</h4>
        <div class="props-field">
          <label for="prop-shadow-x" class="editor-field-label">Offset X</label>
          <div class="props-range-row">
            <input type="range" id="prop-shadow-x" min="-30" max="30" step="1" class="props-range">
            <span class="props-range-val" id="prop-shadow-x-val">0</span>
          </div>
        </div>
        <div class="props-field">
          <label for="prop-shadow-y" class="editor-field-label">Offset Y</label>
          <div class="props-range-row">
            <input type="range" id="prop-shadow-y" min="-30" max="30" step="1" class="props-range">
            <span class="props-range-val" id="prop-shadow-y-val">0</span>
          </div>
        </div>
        <div class="props-field">
          <label for="prop-shadow-blur" class="editor-field-label">Desfoque</label>
          <div class="props-range-row">
            <input type="range" id="prop-shadow-blur" min="0" max="40" step="1" class="props-range">
            <span class="props-range-val" id="prop-shadow-blur-val">0</span>
          </div>
        </div>
        <div class="props-field">
          <label for="prop-shadow-color" class="editor-field-label">Cor da sombra</label>
          <input type="color" id="prop-shadow-color" class="editor-color-picker props-color-picker" value="#000000">
        </div>
      </section>

      <section id="props-texto-dinamico" class="props-section hidden">
        <div class="dynamic-text-badge">Texto Dinamico</div>
        <h4 class="editor-section-title editor-section-title--spaced">Vinculo de Produto</h4>
        <div class="props-field">
          <label for="prop-linked-zone" class="editor-field-label">Produto N.</label>
          <input type="number" id="prop-linked-zone" min="1" max="24" class="editor-text-input">
        </div>
        <p class="editor-section-desc">
          Qual produto deste encarte deve fornecer o valor para este texto?
          Exemplo: 1 = primeiro produto, 2 = segundo produto.
        </p>
      </section>

      <section id="props-zona" class="props-section hidden">
        <div class="zone-badge">Zona de Produto</div>
        <p class="props-zone-info">
          Esta zona sera substituida automaticamente pela foto, nome e preco do produto
          selecionado no momento da geracao do encarte.
        </p>
        <div class="props-field">
          <label for="prop-zone-id" class="editor-field-label">ID da Zona</label>
          <input type="number" id="prop-zone-id" min="1" max="24" class="editor-text-input" readonly>
        </div>
      </section>

      <section id="props-imagem" class="props-section hidden">
        <h4 class="editor-section-title">Imagem</h4>
        <div class="props-field">
          <label for="prop-opacity" class="editor-field-label">Opacidade</label>
          <div class="props-range-row">
            <input type="range" id="prop-opacity" min="0" max="100" step="1" class="props-range">
            <span class="props-range-val"><span id="prop-opacity-val">100</span>%</span>
          </div>
        </div>
        <button type="button" id="prop-duplicar" class="editor-btn editor-btn--secondary editor-btn--block">Duplicar</button>
      </section>

      <section id="props-vetor" class="props-section hidden">
        <h4 class="editor-section-title">Vetor</h4>
        <div class="props-field">
          <label for="prop-vetor-fill" class="editor-field-label">Cor do icone</label>
          <input type="color" id="prop-vetor-fill" class="editor-color-picker props-color-picker" value="#b91c1c">
        </div>
        <div class="props-field">
          <label for="prop-vetor-opacity" class="editor-field-label">Opacidade</label>
          <div class="props-range-row">
            <input type="range" id="prop-vetor-opacity" min="0" max="100" step="1" class="props-range">
            <span class="props-range-val"><span id="prop-vetor-opacity-val">100</span>%</span>
          </div>
        </div>
      </section>

      <section id="props-comum" class="props-section hidden">
        <h4 class="editor-section-title">Camadas</h4>
        <div class="props-layer-grid">
          <button type="button" id="prop-layer-forward" class="props-layer-btn" title="Trazer para frente">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M4 16h4v-4H4v4zm0 4h4v-4H4v4zm0-8h4V8H4v4zm4 4h12v-4H8v4zm0 4h12v-4H8v4zm0-8h12V8H8v4z"/></svg>
            <span>Frente</span>
          </button>
          <button type="button" id="prop-layer-front" class="props-layer-btn" title="Trazer para o topo">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M2 18h2v-2H2v2zm0-4h2v-2H2v2zm0-4h2V8H2v2zm4 8h16v-2H6v2zm0-4h16v-2H6v2zm0-4h16V8H6v2z"/></svg>
            <span>Topo</span>
          </button>
          <button type="button" id="prop-layer-backward" class="props-layer-btn" title="Enviar para tras">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M16 16h4v-4h-4v4zm0 4h4v-4h-4v4zm0-8h4V8h-4v4zM4 16h8v-4H4v4zm0 4h8v-4H4v4zm0-8h8V8H4v4z"/></svg>
            <span>Tras</span>
          </button>
          <button type="button" id="prop-layer-back" class="props-layer-btn" title="Enviar para o fundo">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M2 18h16v-2H2v2zm0-4h16v-2H2v2zm0-4h16V8H2v2z"/></svg>
            <span>Fundo</span>
          </button>
        </div>
        <button type="button" id="prop-excluir" class="editor-btn editor-btn--danger editor-btn--block editor-btn--delete">
          <svg viewBox="0 0 24 24" aria-hidden="true" class="editor-btn-icon"><path fill="currentColor" d="M9 3h6l1 2h4v2H4V5h4l1-2zm1 6h2v9h-2V9zm4 0h2v9h-2V9zM7 9h2v9H7V9z"/></svg>
          Excluir elemento
        </button>
      </section>

      <p id="props-empty" class="editor-props-empty">Selecione um elemento no canvas para editar propriedades.</p>
    </div>
  </aside>
</div>

<div id="loader-overlay" class="loader-overlay">
  <div class="loader-box">
    <div class="loader-spinner"></div>
    <strong>Processando...</strong>
    <p class="loader-msg">Aguarde.</p>
  </div>
</div>

<script>
const EDITOR_DATA = <?= json_encode($editorData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="public/js/app.js"></script>
<script src="public/js/editor-modelo.js"></script>
</body>
</html>
