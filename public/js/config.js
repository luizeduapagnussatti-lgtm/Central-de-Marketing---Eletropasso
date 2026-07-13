'use strict';

function esc(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

const CFG_IDS = {
  gemini_api_key: 'cfg-gemini-key',
  gemini_model: 'cfg-gemini-model',
  rembg_bin: 'cfg-rembg-bin',
  rembg_model: 'cfg-rembg-model',
  rembg_max_edge: 'cfg-rembg-max-edge',
  rembg_alpha_matting: 'cfg-rembg-alpha',
  rembg_post_process_mask: 'cfg-rembg-ppm',
  rembg_white_refine: 'cfg-rembg-white-refine',
  hub_api_url: 'cfg-hub-url',
  hub_api_token: 'cfg-hub-token',
  max_upload_size_mb: 'cfg-max-upload',
  watermark_rodape: 'cfg-rodape',
};

function configAlertHost() {
  return document.getElementById('config-alerts');
}

function renderStatusCards(data) {
  const host = document.getElementById('config-status');
  if (!host) return;

  const items = [
    ...(data.integracoes ? Object.values(data.integracoes) : []),
    ...(data.servicos ? Object.values(data.servicos) : []),
  ];

  host.innerHTML = items.map((item) => {
    const state = item.ok ? 'ok' : 'warn';
    const icon = item.ok ? '&#10003;' : '!';
    const detalhe = item.detalhe ? `<span class="config-status-card-detail">${esc(item.detalhe)}</span>` : '';
    return `
      <div class="config-status-card config-status-card--${state}">
        <span class="config-status-card-icon" aria-hidden="true">${icon}</span>
        <div class="config-status-card-text">
          <strong>${esc(item.label)}</strong>
          ${detalhe}
        </div>
        <span class="config-status-card-badge">${item.ok ? 'OK' : 'Atencao'}</span>
      </div>`;
  }).join('');
}

async function loadConfig() {
  const data = await apiCall('config', 'listar');
  const cfg = data.config || {};

  Object.entries(CFG_IDS).forEach(([key, id]) => {
    const el = document.getElementById(id);
    if (!el || !cfg[key]) return;
    if (el.type === 'checkbox') {
      el.checked = ['1', 'true', 'yes', 'on', 'sim'].includes(String(cfg[key].valor ?? '').toLowerCase());
      return;
    }
    el.value = cfg[key].valor ?? '';
  });

  ['rembg_alpha_matting', 'rembg_post_process_mask', 'rembg_white_refine'].forEach((key) => {
    const el = document.getElementById(CFG_IDS[key]);
    if (!el || cfg[key]) return;
    el.checked = key === 'rembg_white_refine';
  });

  const modelEl = document.getElementById('cfg-gemini-model');
  if (modelEl && cfg.gemini_model?.valor) {
    const val = cfg.gemini_model.valor;
    if (![...modelEl.options].some((o) => o.value === val)) {
      const opt = document.createElement('option');
      opt.value = val;
      opt.textContent = val;
      modelEl.appendChild(opt);
    }
    modelEl.value = val;
  }
}

async function loadDiagnostics() {
  try {
    const data = await apiCall('config', 'diagnosticos');
    renderStatusCards(data);
  } catch {
    const host = document.getElementById('config-status');
    if (host) {
      host.innerHTML = '<p class="config-status-loading">Nao foi possivel verificar os servicos.</p>';
    }
  }
}

document.addEventListener('DOMContentLoaded', async () => {
  try {
    await Promise.all([loadConfig(), loadDiagnostics()]);
  } catch (e) {
    showAlert(configAlertHost(), e.message);
  }
});

document.getElementById('form-config')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const payload = Object.fromEntries(fd.entries());

  ['rembg_alpha_matting', 'rembg_post_process_mask', 'rembg_white_refine'].forEach((key) => {
    payload[key] = payload[key] ? '1' : '0';
  });

  try {
    await apiCall('config', 'salvar', { method: 'POST', body: payload });
    showAlert(configAlertHost(), 'Configuracoes salvas com sucesso.', 'success');
    await loadDiagnostics();
  } catch (err) {
    showAlert(configAlertHost(), err.message);
  }
});
