let encarteId = null;
let itens = [];
let dragSrcIndex = null;
/** @type {Map<string, AbortController>} */
const pendingFotoJobs = new Map();

document.addEventListener('DOMContentLoaded', () => {
  encarteId = parseInt(document.getElementById('encarte-id')?.value || '0', 10) || null;

  document.getElementById('btn-add-item')?.addEventListener('click', () => addItem());
  document.getElementById('btn-salvar')?.addEventListener('click', () => salvarEncarte(false));
  document.getElementById('btn-gerar')?.addEventListener('click', () => salvarEncarte(true));
  document.getElementById('btn-titulos-ia')?.addEventListener('click', () => gerarTitulosIa());

  if (encarteId) {
    carregarEncarte(encarteId);
  } else {
    addItem();
  }
  updateGerarButtonState();
});

async function carregarEncarte(id) {
  try {
    const data = await apiCall('encarte', 'detalhe', { query: { id } });
    const enc = data.encarte;
    document.getElementById('titulo-campanha').value = enc.titulo_campanha;
    document.getElementById('formato').value = enc.formato;
    document.getElementById('max-itens').value = enc.max_itens;
    document.getElementById('modelo-layout').value = enc.modelo_layout || document.getElementById('modelo-layout').value;
    document.getElementById('validade-inicio').value = enc.validade_inicio || '';
    document.getElementById('validade-fim').value = enc.validade_fim || '';
    document.getElementById('texto-rodape').value = enc.texto_legal_rodape || '';
    itens = enc.itens || [];
    renderItens();
    updateGerarButtonState();
  } catch (e) {
    showAlert(document.querySelector('.app-main'), e.message);
  }
}

function addItem(data = {}) {
  if (itens.length >= parseInt(document.getElementById('max-itens').value || '12', 10)) {
    alert('Limite maximo de itens atingido.');
    return;
  }

  itens.push({
    sku: data.sku || '',
    nome_erp: data.nome_erp || '',
    nome_comercial: data.nome_comercial || '',
    descricao_complementar: data.descricao_complementar || '',
    preco_normal: data.preco_normal || '',
    preco_promocional: data.preco_promocional || '',
    unidade: data.unidade || 'und',
    caminho_foto_original: data.caminho_foto_original || '',
    caminho_foto_limpa: data.caminho_foto_limpa || '',
    processamento_imagem_status: data.processamento_imagem_status || 'pendente',
    processamento_imagem_erro: data.processamento_imagem_erro || '',
  });

  renderItens();
}

function removeItem(index) {
  const nome = itens[index]?.nome_comercial?.trim() || `produto ${index + 1}`;
  if (!confirm(`Excluir "${nome}" da lista?`)) return;
  const original = itens[index]?.caminho_foto_original;
  if (original) cancelFotoJob(original);
  itens.splice(index, 1);
  renderItens();
  updateGerarButtonState();
}

function renderItens() {
  const lista = document.getElementById('itens-lista');
  if (!lista) return;

  lista.innerHTML = itens.map((item, i) => `
    <div class="item-row" draggable="true" data-index="${i}">
      <div class="item-row-header">
        <span class="item-row-title">Produto ${i + 1}</span>
        <button type="button" class="btn btn-sm btn-danger item-btn-excluir" onclick="removeItem(${i})" title="Excluir produto">
          Excluir produto
        </button>
      </div>
      <div class="item-row-body">
      <div class="item-row-main">
        <div class="item-handle-col">
          <div class="item-drag" title="Arrastar para reordenar">&#9776;</div>
          <span class="item-index">${i + 1}</span>
          <div class="item-foto-preview${item.processamento_imagem_status === 'processando' ? ' item-foto-preview--processing' : ''}">
            ${item.caminho_foto_limpa || item.caminho_foto_original
              ? `<img src="${escAttr(item.caminho_foto_limpa || item.caminho_foto_original)}" alt="">`
              : '<span style="font-size:10px;color:#9ca3af;text-align:center;padding:4px">Sem foto</span>'}
            ${item.processamento_imagem_status === 'processando'
              ? '<span class="item-foto-spinner" aria-hidden="true"></span>'
              : ''}
          </div>
          ${renderFotoStatus(item, i)}
        </div>
        <div class="item-fields">
          <div class="item-field-row item-field-row--sku">
            <div class="item-field-wrap">
              <span class="item-field-label">SKU</span>
              <input type="text" placeholder="Ex: 25693" value="${esc(item.sku)}" data-field="sku" data-index="${i}">
            </div>
            <button type="button" class="btn btn-sm btn-secondary btn-hub-busca" data-hub-btn="${i}" onclick="buscarSku(${i})">
              <span class="btn-hub-label">Buscar Hub</span>
            </button>
            <p class="hub-busca-status" data-hub-status="${i}" role="status" aria-live="polite" hidden></p>
          </div>
          <div class="item-field-row item-field-row--nome">
            <div class="item-field-wrap">
              <span class="item-field-label">Nome comercial</span>
              <input type="text" placeholder="Nome do produto" value="${esc(item.nome_comercial)}" data-field="nome_comercial" data-index="${i}">
            </div>
            <div class="item-field-wrap">
              <span class="item-field-label">Descricao / Ref</span>
              <input type="text" placeholder="Referencia ou detalhe" value="${esc(item.descricao_complementar)}" data-field="descricao_complementar" data-index="${i}">
            </div>
          </div>
          <div class="item-field-row item-field-row--precos">
            <div class="item-field-wrap">
              <span class="item-field-label">Preco De</span>
              <input type="text" inputmode="decimal" class="input-preco-br" placeholder="0,00" value="${esc(formatPrecoDisplay(item.preco_normal))}" data-field="preco_normal" data-price-mask data-index="${i}">
            </div>
            <div class="item-field-wrap item-field-wrap--preco-por">
              <span class="item-field-label">Preco Por</span>
              <input type="text" inputmode="decimal" class="input-preco-por input-preco-br" placeholder="0,00" value="${esc(formatPrecoDisplay(item.preco_promocional))}" data-field="preco_promocional" data-price-mask data-index="${i}">
            </div>
            <div class="item-field-wrap">
              <span class="item-field-label">Unidade</span>
              <input type="text" placeholder="und" value="${esc(item.unidade)}" data-field="unidade" data-index="${i}">
            </div>
            <label class="btn btn-sm btn-secondary item-upload-label">
              Enviar foto
              <input type="file" accept="image/*" hidden onchange="uploadFoto(${i}, this)">
            </label>
            ${item.caminho_foto_original && item.processamento_imagem_status === 'erro'
              ? `<button type="button" class="btn btn-sm btn-secondary" onclick="reprocessarFundo(${i})">Reprocessar fundo</button>`
              : ''}
          </div>
        </div>
      </div>
      <aside class="item-row-aside">
        <div class="preview-card">
          <span class="badge">Oferta Especial!</span>
          <div class="nome">${esc(item.nome_comercial || 'Produto')}</div>
          <div class="preco-de">${item.preco_normal ? formatMoney(brToFloat(item.preco_normal)) : 'De: --'}</div>
          <div class="preco-por">${item.preco_promocional ? formatMoney(brToFloat(item.preco_promocional)) : 'Por: --'}</div>
        </div>
      </aside>
      </div>
    </div>
  `).join('');

  lista.querySelectorAll('.item-row').forEach(row => {
    row.addEventListener('dragstart', onDragStart);
    row.addEventListener('dragover', onDragOver);
    row.addEventListener('drop', onDrop);
    row.addEventListener('dragend', () => row.classList.remove('dragging'));
  });

  lista.querySelectorAll('[data-field]').forEach(input => {
    input.addEventListener('change', () => {
      const idx = parseInt(input.dataset.index, 10);
      itens[idx][input.dataset.field] = input.value;
      renderItens();
    });
  });

  lista.querySelectorAll('[data-field="sku"]').forEach(input => {
    input.addEventListener('keydown', (e) => {
      if (e.key !== 'Enter') return;
      e.preventDefault();
      const idx = parseInt(input.dataset.index, 10);
      if (Number.isNaN(idx)) return;
      itens[idx].sku = input.value.trim();
      buscarSku(idx);
    });
  });

  bindPriceMasks(lista);
  updateGerarButtonState();
  updateFotosProgresso();
}

function formatPrecoDisplay(value) {
  if (value === null || value === undefined || value === '') return '';
  if (typeof value === 'string' && value.includes(',')) return value;
  return floatToBr(value);
}

function renderFotoStatus(item, index) {
  const status = item.processamento_imagem_status || 'pendente';
  if (status === 'pendente' && !item.caminho_foto_limpa && !item.caminho_foto_original) return '';

  if (status === 'processando') {
    return `<div class="foto-status foto-status--processando" data-foto-status="${index}">
      <span class="foto-status-badge"><span class="spinner-inline spinner-inline--xs" aria-hidden="true"></span> Removendo fundo...</span>
    </div>`;
  }

  const cls = status === 'ok' ? 'foto-status--ok' : 'foto-status--erro';
  const label = status === 'ok' ? 'Fundo removido' : 'Fundo nao removido';
  const erro = item.processamento_imagem_erro
    ? `<span class="foto-status-erro-msg" title="${escAttr(item.processamento_imagem_erro)}">${esc(item.processamento_imagem_erro)}</span>`
    : '';

  return `<div class="foto-status ${cls}" data-foto-status="${index}">
    <span class="foto-status-badge">${label}</span>${erro}
  </div>`;
}

function onDragStart(e) {
  dragSrcIndex = parseInt(e.currentTarget.dataset.index, 10);
  e.currentTarget.classList.add('dragging');
}

function onDragOver(e) {
  e.preventDefault();
}

function onDrop(e) {
  e.preventDefault();
  const targetIndex = parseInt(e.currentTarget.dataset.index, 10);
  if (dragSrcIndex === null || dragSrcIndex === targetIndex) return;
  const moved = itens.splice(dragSrcIndex, 1)[0];
  itens.splice(targetIndex, 0, moved);
  dragSrcIndex = null;
  renderItens();
}

async function buscarSku(index) {
  const sku = itens[index]?.sku?.trim();
  if (!sku) { alert('Informe o SKU.'); return; }

  setHubBuscaLoading(index, true, 'Consultando estoque no Hub...');

  try {
    const data = await apiCall('encarte', 'buscar_sku', { query: { sku } });
    itens[index].sku = data.sku;
    itens[index].nome_erp = data.nome_erp;
    itens[index].nome_comercial = data.nome_comercial;
    itens[index].preco_normal = floatToBr(data.preco_venda_atual);
    itens[index].unidade = normalizeUnidade(data.unidade || 'und');
    renderItens();
    flashHubStatus(index, 'Produto encontrado no Hub.', 'success');
  } catch (e) {
    setHubBuscaLoading(index, false);
    flashHubStatus(index, e.message, 'error');
    alert('Hub: ' + e.message);
  }
}

function normalizeUnidade(unidade) {
  const u = String(unidade || 'und').trim().toUpperCase();
  if (['M', 'MT', 'MET', 'METRO', 'METROS', 'METR'].includes(u)) return 'm';
  if (['RL', 'ROLO', 'ROLOS'].includes(u)) return 'rl';
  if (['UN', 'UND', 'UNID', 'UNIDADE', 'PC', 'PÇ', 'PCA', 'PECA', 'PEÇA'].includes(u)) return 'und';
  return u ? u.toLowerCase() : 'und';
}

function setHubBuscaLoading(index, loading, message = '') {
  const row = document.querySelector(`.item-row[data-index="${index}"]`);
  const btn = document.querySelector(`[data-hub-btn="${index}"]`);
  const status = document.querySelector(`[data-hub-status="${index}"]`);
  const skuInput = row?.querySelector('[data-field="sku"]');

  if (row) {
    row.classList.toggle('item-row--hub-loading', loading);
    row.setAttribute('aria-busy', loading ? 'true' : 'false');
  }

  if (btn) {
    btn.disabled = loading;
    btn.classList.toggle('is-loading', loading);
    btn.innerHTML = loading
      ? '<span class="spinner-inline" aria-hidden="true"></span><span>Buscando...</span>'
      : '<span class="btn-hub-label">Buscar Hub</span>';
  }

  if (skuInput) skuInput.disabled = loading;

  if (status) {
    if (loading && message) {
      status.hidden = false;
      status.className = 'hub-busca-status hub-busca-status--loading';
      status.innerHTML = '<span class="spinner-inline spinner-inline--xs" aria-hidden="true"></span> ' + esc(message);
    } else if (!loading) {
      status.hidden = true;
      status.textContent = '';
      status.className = 'hub-busca-status';
    }
  }
}

function flashHubStatus(index, message, type = 'success') {
  const status = document.querySelector(`[data-hub-status="${index}"]`);
  if (!status) return;
  status.hidden = false;
  status.className = `hub-busca-status hub-busca-status--${type}`;
  status.textContent = message;
  setTimeout(() => {
    if (status.textContent === message) {
      status.hidden = true;
      status.textContent = '';
    }
  }, 4000);
}

async function uploadFoto(index, input) {
  if (!input.files[0]) return;

  const prevOriginal = itens[index]?.caminho_foto_original;
  if (prevOriginal) cancelFotoJob(prevOriginal);

  const fd = new FormData();
  fd.append('foto', input.files[0]);

  try {
    const data = await apiCall('encarte', 'upload_foto', { formData: fd });
    itens[index].caminho_foto_original = data.caminho_foto_original;
    itens[index].caminho_foto_limpa = data.caminho_foto_limpa || data.caminho_foto_original;
    itens[index].processamento_imagem_status = 'processando';
    itens[index].processamento_imagem_erro = '';
    renderItens();
    processarFundoAsync(data.caminho_foto_original);
  } catch (e) {
    alert('Upload: ' + e.message);
    updateGerarButtonState();
  } finally {
    input.value = '';
  }
}

function cancelFotoJob(caminhoOriginal) {
  const key = String(caminhoOriginal || '');
  if (!key || !pendingFotoJobs.has(key)) return;
  try {
    pendingFotoJobs.get(key).abort();
  } catch (_) { /* ignore */ }
  pendingFotoJobs.delete(key);
}

function countFotosProcessando() {
  return itens.filter((i) => i.caminho_foto_original && i.processamento_imagem_status === 'processando').length;
}

function countFotosComArquivo() {
  return itens.filter((i) => i.caminho_foto_original || i.caminho_foto_limpa).length;
}

function countFotosProntas() {
  return itens.filter((i) => {
    if (!i.caminho_foto_original && !i.caminho_foto_limpa) return false;
    return i.processamento_imagem_status === 'ok' || i.processamento_imagem_status === 'erro';
  }).length;
}

function updateFotosProgresso() {
  let el = document.getElementById('fotos-processamento-status');
  const actions = document.querySelector('.form-actions');
  if (!el && actions) {
    el = document.createElement('p');
    el.id = 'fotos-processamento-status';
    el.className = 'fotos-processamento-status';
    el.setAttribute('role', 'status');
    el.setAttribute('aria-live', 'polite');
    actions.parentNode?.insertBefore(el, actions);
  }
  if (!el) return;

  const total = countFotosComArquivo();
  const processando = countFotosProcessando();
  const prontas = countFotosProntas();

  if (total === 0) {
    el.hidden = true;
    el.textContent = '';
    return;
  }

  el.hidden = false;
  if (processando > 0) {
    el.className = 'fotos-processamento-status fotos-processamento-status--busy';
    el.textContent = `Fotos: ${prontas} de ${total} prontas — removendo fundo em ${processando}...`;
  } else {
    el.className = 'fotos-processamento-status';
    el.textContent = `Fotos: ${prontas} de ${total} prontas`;
  }
}

function updateGerarButtonState() {
  const btn = document.getElementById('btn-gerar');
  if (!btn) return;
  const processando = countFotosProcessando();
  if (processando > 0) {
    btn.disabled = true;
    btn.dataset.labelDefault = btn.dataset.labelDefault || btn.textContent;
    btn.textContent = 'Aguardando imagens...';
  } else {
    btn.disabled = false;
    if (btn.dataset.labelDefault) {
      btn.textContent = btn.dataset.labelDefault;
    } else {
      btn.textContent = 'Gerar Encarte PNG';
    }
  }
  updateFotosProgresso();
}

async function processarFundoAsync(caminhoOriginal) {
  const key = String(caminhoOriginal || '');
  if (!key) return;

  cancelFotoJob(key);
  const controller = new AbortController();
  pendingFotoJobs.set(key, controller);

  try {
    const data = await apiCall('encarte', 'processar_fundo', {
      method: 'POST',
      body: { caminho_foto_original: key },
      signal: controller.signal,
    });

    const idx = itens.findIndex((i) => i.caminho_foto_original === key);
    if (idx < 0) return;

    itens[idx].caminho_foto_limpa = data.caminho_foto_limpa;
    itens[idx].processamento_imagem_status = data.processamento_imagem_status;
    itens[idx].processamento_imagem_erro = data.processamento_imagem_erro || '';
    renderItens();

    if (data.processamento_imagem_status === 'erro') {
      flashHubStatus(
        idx,
        'Fundo nao removido — o encarte usara a foto original. Voce pode reprocessar.',
        'error'
      );
    }
  } catch (e) {
    if (e?.name === 'AbortError' || String(e?.message || '').includes('aborted')) {
      return;
    }
    const idx = itens.findIndex((i) => i.caminho_foto_original === key);
    if (idx >= 0) {
      itens[idx].processamento_imagem_status = 'erro';
      itens[idx].processamento_imagem_erro = e.message || 'Falha ao processar fundo.';
      if (!itens[idx].caminho_foto_limpa) {
        itens[idx].caminho_foto_limpa = key;
      }
      renderItens();
    }
  } finally {
    if (pendingFotoJobs.get(key) === controller) {
      pendingFotoJobs.delete(key);
    }
    updateGerarButtonState();
  }
}

async function reprocessarFundo(index) {
  const original = itens[index]?.caminho_foto_original;
  if (!original) {
    alert('Envie uma foto antes de reprocessar.');
    return;
  }

  itens[index].processamento_imagem_status = 'processando';
  itens[index].processamento_imagem_erro = '';
  renderItens();
  processarFundoAsync(original);
}

async function gerarTitulosIa() {
  const nomes = itens.map(i => i.nome_comercial).filter(Boolean);
  if (nomes.length === 0) { alert('Adicione itens primeiro.'); return; }

  try {
    showLoader('Gerando titulos com IA...');
    const data = await apiCall('encarte', 'titulos_ia', { method: 'POST', body: { nomes } });
    const container = document.getElementById('titulos-ia');
    container.innerHTML = (data.opcoes || []).map(t =>
      `<button type="button" onclick="document.getElementById('titulo-campanha').value=${JSON.stringify(t)}">${esc(t)}</button>`
    ).join('');
  } catch (e) {
    alert('IA: ' + e.message);
  } finally {
    hideLoader();
  }
}

function validarFormulario() {
  const titulo = document.getElementById('titulo-campanha').value.trim();
  if (!titulo) { alert('Informe o titulo da campanha.'); return false; }

  const itensValidos = itens.filter(i => i.nome_comercial.trim());
  if (itensValidos.length === 0) { alert('Adicione ao menos um produto.'); return false; }

  for (const item of itensValidos) {
    const de = brToFloat(item.preco_normal);
    const por = brToFloat(item.preco_promocional);
    if (isNaN(de) || isNaN(por) || por <= 0) {
      alert(`Precos invalidos para "${item.nome_comercial}".`);
      return false;
    }
    if (por >= de) {
      alert(`Preco promocional deve ser menor que o preco normal em "${item.nome_comercial}".`);
      return false;
    }
  }

  return true;
}

async function salvarEncarte(gerar = false) {
  if (!validarFormulario()) return;

  if (gerar) {
    const processando = countFotosProcessando();
    if (processando > 0) {
      alert(`Aguarde o fim da remocao de fundo (${processando} foto(s) ainda processando).`);
      updateGerarButtonState();
      return;
    }

    const comErro = itens.filter(
      (i) => i.nome_comercial?.trim() && i.caminho_foto_original && i.processamento_imagem_status === 'erro'
    );
    if (comErro.length > 0) {
      const ok = confirm(
        `${comErro.length} foto(s) sem fundo removido. O encarte usara a imagem original nesses itens.\n\nDeseja continuar?`
      );
      if (!ok) return;
    }
  }

  const payload = {
    id: encarteId,
    titulo_campanha: document.getElementById('titulo-campanha').value.trim(),
    modelo_layout: document.getElementById('modelo-layout')?.value || 'modelo_01',
    formato: document.getElementById('formato').value,
    max_itens: parseInt(document.getElementById('max-itens').value, 10),
    validade_inicio: document.getElementById('validade-inicio').value || null,
    validade_fim: document.getElementById('validade-fim').value || null,
    texto_legal_rodape: document.getElementById('texto-rodape').value.trim(),
    mes_vigencia: new Date().getMonth() + 1,
    ano_vigencia: new Date().getFullYear(),
    status: 'rascunho',
    itens: itens.filter(i => i.nome_comercial.trim()).map(item => ({
      ...item,
      preco_normal: brToFloat(item.preco_normal),
      preco_promocional: brToFloat(item.preco_promocional),
    })),
  };

  try {
    showLoader(gerar ? 'Salvando e gerando encarte...' : 'Salvando...');
    const data = await apiCall('encarte', 'salvar', { method: 'POST', body: payload });
    encarteId = data.id;
    document.getElementById('encarte-id').value = encarteId;

    if (gerar) {
      const result = await apiCall('encarte', 'gerar', { method: 'POST', body: { id: encarteId } });
      hideLoader();
      if (!result.caminho) {
        showAlert(document.querySelector('.app-main'), 'Erro ao gerar encarte: arquivo PNG nao foi criado.', 'error');
        return;
      }
      showEncartePreview(result.caminho);
    } else {
      hideLoader();
      showAlert(document.querySelector('.app-main'), 'Encarte salvo como rascunho.', 'success');
    }
  } catch (e) {
    hideLoader();
    const msg = String(e.message || 'Erro ao processar encarte.');
    showAlert(document.querySelector('.app-main'), msg, 'error');
    alert(msg);
  }
}

function showEncartePreview(caminho) {
  const box = document.getElementById('encarte-preview-result');
  const img = document.getElementById('encarte-preview-img');
  if (!box || !img) {
    window.location.href = 'index.php';
    return;
  }

  const url = new URL(String(caminho).replace(/\\/g, '/'), window.location.href).href;
  img.src = `${url}?v=${Date.now()}`;
  box.hidden = false;
  box.classList.remove('hidden');
  box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  showAlert(document.querySelector('.app-main'), 'Encarte gerado com sucesso.', 'success');
}

function esc(str) {
  const d = document.createElement('div');
  d.textContent = str || '';
  return d.innerHTML;
}

function escAttr(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
    .replace(/</g, '&lt;');
}
