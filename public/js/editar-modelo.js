document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-editar-modelo');
  const alertSlot = document.getElementById('editar-modelo-alert');
  if (!form) return;

  document.querySelectorAll('input[type="color"]').forEach((picker) => {
    const chave = picker.dataset.cor;
    const hexInput = document.querySelector(`[data-cor-hex="${chave}"]`);
    if (!hexInput) return;

    picker.addEventListener('input', () => {
      hexInput.value = picker.value;
    });

    hexInput.addEventListener('input', () => {
      let val = hexInput.value.trim();
      if (!val.startsWith('#')) val = '#' + val;
      if (/^#[0-9a-fA-F]{6}$/.test(val)) {
        picker.value = val.toLowerCase();
      }
    });
  });

  function coletarCores() {
    const cores = {};
    document.querySelectorAll('input[type="color"][data-cor]').forEach((el) => {
      cores[el.dataset.cor] = el.value;
    });
    return cores;
  }

  function coletarIcones() {
    const mapa = {};
    document.querySelectorAll('.icone-emoji[data-icone]').forEach((el) => {
      mapa[el.dataset.icone] = el.value.trim();
    });
    return mapa;
  }

  function coletarPayload() {
    const formatos = [...form.querySelectorAll('input[name="formatos[]"]:checked')].map((el) => el.value);

    return {
      id: Number(document.getElementById('modelo-id')?.value),
      nome_exibicao: document.getElementById('modelo-nome')?.value.trim(),
      descricao: document.getElementById('modelo-descricao')?.value.trim(),
      max_itens_default: Number(document.getElementById('modelo-max-itens')?.value),
      formatos_suportados: formatos,
      config_visual: {
        cores: coletarCores(),
        textos: {
          badge_oferta: document.getElementById('texto-badge-oferta')?.value.trim(),
          clube_badge: document.getElementById('texto-clube-badge')?.value.trim(),
          mostrar_clube: document.getElementById('texto-mostrar-clube')?.checked ?? false,
        },
        icones: {
          auto: document.getElementById('icones-auto')?.checked ?? true,
          tipo: document.getElementById('icones-tipo')?.value || 'emoji',
          mapa: coletarIcones(),
        },
      },
    };
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const submitBtn = form.querySelector('[type="submit"]');
    submitBtn.disabled = true;

    try {
      await apiCall('modelo', 'salvar', {
        method: 'POST',
        body: coletarPayload(),
      });
      if (alertSlot) showAlert(alertSlot, 'Modelo salvo com sucesso.', 'success');
    } catch (err) {
      if (alertSlot) showAlert(alertSlot, err.message);
    } finally {
      submitBtn.disabled = false;
    }
  });

  document.querySelectorAll('.js-upload-fundo').forEach((input) => {
    input.addEventListener('change', async () => {
      const file = input.files?.[0];
      if (!file) return;

      const id = Number(document.getElementById('modelo-id')?.value);
      const formato = input.dataset.formato;
      const fd = new FormData();
      fd.append('id', String(id));
      fd.append('formato', formato);
      fd.append('fundo', file);

      showLoader('Carregando fundo...');
      try {
        const url = new URL('api/index.php', window.location.href);
        url.searchParams.set('recurso', 'modelo');
        url.searchParams.set('acao', 'upload_fundo');
        const resp = await fetch(url, { method: 'POST', body: fd });
        const json = await resp.json();
        if (!json.success) throw new Error(json.error || 'Erro ao enviar palco.');
        if (alertSlot) showAlert(alertSlot, 'Palco atualizado com sucesso.', 'success');
        setTimeout(() => window.location.reload(), 800);
      } catch (err) {
        if (alertSlot) showAlert(alertSlot, err.message);
      } finally {
        hideLoader();
        input.value = '';
      }
    });
  });

  document.getElementById('btn-regenerar-preview')?.addEventListener('click', async () => {
    const btn = document.getElementById('btn-regenerar-preview');
    btn.disabled = true;
    showLoader('Regenerando preview...');

    try {
      const id = Number(document.getElementById('modelo-id')?.value);
      const data = await apiCall('modelo', 'gerar_preview', {
        method: 'POST',
        body: { id },
      });

      const img = document.getElementById('modelo-preview-img');
      const empty = document.getElementById('modelo-preview-empty');
      if (img && data.preview) {
        img.src = data.preview;
        img.hidden = false;
        empty?.remove();
      }

      if (alertSlot) showAlert(alertSlot, 'Preview regenerado com sucesso.', 'success');
    } catch (err) {
      if (alertSlot) showAlert(alertSlot, err.message);
    } finally {
      hideLoader();
      btn.disabled = false;
    }
  });
});
