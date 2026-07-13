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

  if (options.signal) {
    fetchOpts.signal = options.signal;
  }

  if (options.body) {
    fetchOpts.headers['Content-Type'] = 'application/json';
    fetchOpts.body = JSON.stringify(options.body);
  }

  if (options.formData) {
    fetchOpts.method = 'POST';
    fetchOpts.body = options.formData;
  }

  const resp = await fetch(url, fetchOpts);
  const raw = await resp.text();

  let json;
  try {
    json = raw ? JSON.parse(raw) : {};
  } catch {
    const snippet = raw.replace(/\s+/g, ' ').trim().slice(0, 180);
    throw new Error(
      snippet
        ? `Resposta invalida do servidor (${resp.status}): ${snippet}`
        : `Resposta invalida do servidor (${resp.status}).`
    );
  }

  if (!json.success) {
    throw new Error(json.error || 'Erro desconhecido.');
  }

  return json.data;
}

function formatMoney(value) {
  const num = typeof value === 'string' ? brToFloat(value) : Number(value);
  if (Number.isNaN(num)) return 'R$ --';
  return 'R$ ' + num.toFixed(2).replace('.', ',');
}

/** Converte float ou string numerica para exibicao BR (ex.: 14.99 -> "14,99"). */
function floatToBr(value) {
  const num = typeof value === 'string' ? brToFloat(value) : Number(value);
  if (Number.isNaN(num) || num === 0) return '';
  const fixed = num.toFixed(2);
  const [intPart, decPart] = fixed.split('.');
  const intFormatted = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  return `${intFormatted},${decPart}`;
}

/** Converte string BR (ex.: "14,99" ou "1.499,99") para float. */
function brToFloat(str) {
  if (str === null || str === undefined || str === '') return NaN;
  if (typeof str === 'number') return str;
  const cleaned = String(str).trim().replace(/\./g, '').replace(',', '.');
  return parseFloat(cleaned);
}

/** Aplica mascara de centavos BR em um input (digita da direita p/ esquerda). */
function maskPriceInput(input) {
  const digits = input.value.replace(/\D/g, '');
  if (digits === '') {
    input.value = '';
    return '';
  }
  const cents = parseInt(digits, 10);
  const num = cents / 100;
  input.value = floatToBr(num);
  return input.value;
}

/** Vincula mascara BR a inputs de preco dentro de um container. */
function bindPriceMasks(container) {
  if (!container) return;
  container.querySelectorAll('[data-price-mask]').forEach((input) => {
    if (input.dataset.priceBound === '1') return;
    input.dataset.priceBound = '1';
    input.addEventListener('input', () => maskPriceInput(input));
    input.addEventListener('blur', () => {
      if (input.value && brToFloat(input.value) === 0) {
        input.value = '';
      }
    });
  });
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
