const API_BASE = 'api/index.php';

async function apiCall(recurso, acao, options = {}) {
  const url = new URL(API_BASE, window.location.href);
  url.searchParams.set('recurso', recurso);
  url.searchParams.set('acao', acao);

  if (options.query) {
    Object.entries(options.query).forEach(([k, v]) => url.searchParams.set(k, v));
  }

  const fetchOpts = {
    method: options.method || 'GET',
    headers: {},
  };

  if (options.body) {
    fetchOpts.headers['Content-Type'] = 'application/json';
    fetchOpts.body = JSON.stringify(options.body);
  }

  if (options.formData) {
    fetchOpts.method = 'POST';
    fetchOpts.body = options.formData;
  }

  const resp = await fetch(url, fetchOpts);
  const json = await resp.json();

  if (!json.success) {
    throw new Error(json.error || 'Erro desconhecido.');
  }

  return json.data;
}

function formatMoney(value) {
  return 'R$ ' + Number(value).toFixed(2).replace('.', ',');
}

function showLoader(msg = 'Gerando encarte...') {
  const overlay = document.getElementById('loader-overlay');
  if (overlay) {
    overlay.querySelector('.loader-msg').textContent = msg;
    overlay.classList.add('active');
  }
}

function hideLoader() {
  const overlay = document.getElementById('loader-overlay');
  if (overlay) overlay.classList.remove('active');
}

function showAlert(container, msg, type = 'error') {
  const el = document.createElement('div');
  el.className = `alert alert-${type}`;
  el.textContent = msg;
  container.prepend(el);
  setTimeout(() => el.remove(), 5000);
}

function openLightbox(src) {
  const lb = document.getElementById('lightbox');
  if (!lb) return;
  lb.querySelector('img').src = src;
  lb.classList.add('active');
}

function closeLightbox() {
  const lb = document.getElementById('lightbox');
  if (lb) lb.classList.remove('active');
}

document.addEventListener('click', (e) => {
  if (e.target.id === 'lightbox' || e.target.classList.contains('lightbox-close')) {
    closeLightbox();
  }
});

async function pollStatus(encarteId, onDone) {
  const maxAttempts = 60;
  for (let i = 0; i < maxAttempts; i++) {
    await new Promise(r => setTimeout(r, 2000));
    try {
      const data = await apiCall('encarte', 'status', { query: { id: encarteId } });
      if (data.status === 'concluido') {
        onDone(data);
        return;
      }
      if (data.status === 'erro') {
        throw new Error(data.erro_geracao || 'Erro na geracao.');
      }
    } catch (e) {
      if (i === maxAttempts - 1) throw e;
    }
  }
  throw new Error('Timeout aguardando geracao.');
}
