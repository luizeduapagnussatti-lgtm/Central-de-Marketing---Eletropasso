document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-novo-modelo');
  const alertSlot = document.getElementById('novo-modelo-alert');
  const selectFormato = document.getElementById('select-formato-modelo');
  const dimsHint = document.getElementById('formato-dims-hint');
  const btnSubmit = document.getElementById('btn-criar-modelo');
  const fileInput = document.getElementById('input-fundo-modelo');
  const uploadZone = document.querySelector('.novo-modelo-upload');
  const uploadFileName = document.getElementById('upload-file-name');

  function updateDimsHint() {
    if (!selectFormato || !dimsHint) return;
    const opt = selectFormato.options[selectFormato.selectedIndex];
    const w = opt?.dataset.width || '1080';
    const h = opt?.dataset.height || '1920';
    dimsHint.innerHTML =
      'Tamanho ideal: ' +
      `<span class="format-dims-badge"><strong>${w}×${h} px</strong> · PNG ou JPEG</span>` +
      ' · mesma proporcao (ex.: metade da resolucao) sera ajustada automaticamente';
  }

  function updateFileLabel() {
    const file = fileInput?.files?.[0];
    if (!uploadFileName || !uploadZone) return;

    if (file) {
      uploadFileName.textContent = file.name;
      uploadZone.classList.add('has-file');
      return;
    }

    uploadFileName.textContent = 'Nenhum arquivo selecionado';
    uploadZone.classList.remove('has-file');
  }

  selectFormato?.addEventListener('change', updateDimsHint);
  fileInput?.addEventListener('change', updateFileLabel);
  updateDimsHint();

  if (uploadZone && fileInput) {
    ['dragenter', 'dragover'].forEach((evt) => {
      uploadZone.addEventListener(evt, (e) => {
        e.preventDefault();
        uploadZone.classList.add('is-dragover');
      });
    });

    ['dragleave', 'drop'].forEach((evt) => {
      uploadZone.addEventListener(evt, (e) => {
        e.preventDefault();
        uploadZone.classList.remove('is-dragover');
      });
    });

    uploadZone.addEventListener('drop', (e) => {
      const file = e.dataTransfer?.files?.[0];
      if (!file || !file.type.match(/^image\/(png|jpeg)$/)) {
        if (alertSlot) showAlert(alertSlot, 'Use um arquivo PNG ou JPEG.');
        return;
      }
      const dt = new DataTransfer();
      dt.items.add(file);
      fileInput.files = dt.files;
      updateFileLabel();
    });
  }

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const nome = document.getElementById('input-nome-modelo')?.value.trim() || '';
    const formato = selectFormato?.value || '';
    const file = fileInput?.files?.[0];

    if (!nome) {
      if (alertSlot) showAlert(alertSlot, 'Informe o nome do modelo.');
      return;
    }

    if (!formato) {
      if (alertSlot) showAlert(alertSlot, 'Selecione um formato.');
      return;
    }

    if (!file) {
      if (alertSlot) showAlert(alertSlot, 'Selecione o arquivo de fundo.');
      return;
    }

    const fd = new FormData();
    fd.append('nome_exibicao', nome);
    fd.append('formato', formato);
    fd.append('fundo', file);

    btnSubmit.disabled = true;
    showLoader('Criando modelo e preparando editor...');

    try {
      const data = await apiCall('modelo', 'criar', { formData: fd });
      if (data.redirect) {
        window.location.href = data.redirect;
        return;
      }
      if (data.id) {
        window.location.href = `editar-modelo.php?id=${data.id}`;
      }
    } catch (err) {
      if (alertSlot) showAlert(alertSlot, err.message);
      btnSubmit.disabled = false;
    } finally {
      hideLoader();
    }
  });
});
