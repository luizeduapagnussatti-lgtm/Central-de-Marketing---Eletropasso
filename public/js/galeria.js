document.addEventListener('DOMContentLoaded', async () => {
  const container = document.getElementById('galeria');
  if (!container) return;

  try {
    const data = await apiCall('encarte', 'listar');
    renderGaleria(container, data.grupos || {});
  } catch (e) {
    container.innerHTML = `<div class="alert alert-error">${escHtml(e.message)}</div>`;
  }
});

const STATUS_LABELS = {
  rascunho: 'Rascunho',
  gerando: 'Gerando',
  concluido: 'Concluido',
  erro: 'Erro',
};

function encartePreviewUrl(path) {
  if (!path) return '';
  const normalized = String(path).replace(/\\/g, '/');
  return new URL(normalized, window.location.href).href;
}

function thumbMarkup(enc) {
  if (!enc.caminho_imagem_final) {
    const msg = enc.status === 'rascunho' ? 'Rascunho — sem preview' : 'Sem imagem';
    return `<div class="placeholder">${escHtml(msg)}</div>`;
  }

  const url = encartePreviewUrl(enc.caminho_imagem_final);
  const cacheBust = enc.updated_at ? `?v=${encodeURIComponent(enc.updated_at)}` : '';
  return `<img src="${escAttr(url + cacheBust)}" alt="${escAttr(enc.titulo_campanha)}" loading="lazy" onerror="this.classList.add('thumb-erro'); this.closest('.encarte-card-thumb')?.classList.add('is-empty');">`;
}

function renderGaleria(container, grupos) {
  const keys = Object.keys(grupos);

  if (keys.length === 0) {
    container.innerHTML = `
      <div class="empty-state">
        <h2>Nenhum encarte ainda</h2>
        <p>Crie seu primeiro encarte promocional para a Eletropasso.</p>
        <a href="modelos.php" class="btn btn-primary" style="margin-top:20px">+ Novo Encarte</a>
      </div>`;
    return;
  }

  let html = `
    <div class="galeria-toolbar">
      <a href="modelos.php" class="btn btn-primary">+ Novo Encarte</a>
    </div>`;
  keys.forEach(key => {
    const grupo = grupos[key];
    const total = (grupo.encartes || []).length;
    html += `
      <section class="grupo-mes">
        <div class="grupo-mes-head">
          <h2>${escHtml(grupo.label)}</h2>
          <span class="grupo-mes-count">${total} encarte${total !== 1 ? 's' : ''}</span>
        </div>
        <div class="encartes-grid">`;

    (grupo.encartes || []).forEach(enc => {
      const imgUrl = encartePreviewUrl(enc.caminho_imagem_final);
      const statusLabel = STATUS_LABELS[enc.status] || enc.status;

      html += `
        <article class="encarte-card">
          <div class="encarte-card-thumb">
            <span class="encarte-format-tag">${escHtml(enc.formato.toUpperCase())}</span>
            ${thumbMarkup(enc)}
          </div>
          <div class="encarte-card-body">
            <h3>${escHtml(enc.titulo_campanha)}</h3>
            <div class="encarte-card-top">
              <div class="encarte-meta">v${enc.versao} · ${enc.quantidade_itens} item${enc.quantidade_itens !== 1 ? 's' : ''}</div>
              <span class="status-badge status-${enc.status}">${escHtml(statusLabel)}</span>
            </div>
            <div class="encarte-actions">
              ${enc.caminho_imagem_final ? `
                <button type="button" class="btn btn-sm btn-secondary" onclick="openLightbox('${escAttr(imgUrl)}')">Visualizar</button>
                <a href="${escAttr(imgUrl)}" download class="btn btn-sm btn-primary">Baixar PNG</a>
              ` : ''}
              <a href="criar.php?id=${enc.id}" class="btn btn-sm btn-secondary">Editar</a>
              <button type="button" class="btn btn-sm btn-secondary" onclick="novaVersao(${enc.id})">Nova Versao</button>
            </div>
            <div class="encarte-actions-secondary">
              <button type="button" class="btn btn-sm btn-ghost" onclick="excluirEncarte(${enc.id}, '${escAttr(enc.titulo_campanha)}')">Excluir</button>
            </div>
          </div>
        </article>`;
    });

    html += '</div></section>';
  });

  container.innerHTML = html;
}

async function novaVersao(id) {
  if (!confirm('Criar nova versao deste encarte para editar precos?')) return;
  try {
    const data = await apiCall('encarte', 'nova_versao', { method: 'POST', body: { id } });
    window.location.href = `criar.php?id=${data.id}`;
  } catch (e) {
    alert(e.message);
  }
}

async function excluirEncarte(id, titulo) {
  if (!confirm(`Excluir permanentemente o encarte "${titulo}"?\n\nEsta acao nao pode ser desfeita.`)) return;

  try {
    await apiCall('encarte', 'excluir', { method: 'POST', body: { id } });
    const container = document.getElementById('galeria');
    const data = await apiCall('encarte', 'listar');
    renderGaleria(container, data.grupos || {});
  } catch (e) {
    alert(e.message);
  }
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str ?? '';
  return d.innerHTML;
}

function escAttr(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
    .replace(/</g, '&lt;');
}
