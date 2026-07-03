document.addEventListener('DOMContentLoaded', () => {
  const alertSlot = document.getElementById('modelos-admin-alert');

  document.querySelectorAll('.js-toggle-ativo').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const id = Number(btn.dataset.id);
      const eraAtivo = btn.dataset.ativo === '1';
      const acao = eraAtivo ? 'inativar' : 'reativar';

      if (!confirm(`Deseja ${acao} este modelo?`)) return;

      btn.disabled = true;
      try {
        const data = await apiCall('modelo', 'alternar_ativo', {
          method: 'POST',
          body: { id },
        });
        const ativo = Number(data.modelo.ativo) === 1;
        btn.dataset.ativo = ativo ? '1' : '0';
        btn.textContent = ativo ? 'Inativar' : 'Reativar';

        const card = btn.closest('.modelo-card--admin');
        const badge = card?.querySelector('.status-badge');
        if (badge) {
          badge.textContent = ativo ? 'Ativo' : 'Inativo';
          badge.classList.toggle('status-badge--ativo', ativo);
          badge.classList.toggle('status-badge--inativo', !ativo);
        }

        if (alertSlot) {
          showAlert(alertSlot, `Modelo ${ativo ? 'reativado' : 'inativado'} com sucesso.`, 'success');
        }
      } catch (e) {
        if (alertSlot) showAlert(alertSlot, e.message);
      } finally {
        btn.disabled = false;
      }
    });
  });

  document.querySelectorAll('.js-excluir-modelo').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const id = Number(btn.dataset.id);
      const nome = btn.dataset.nome || 'este modelo';

      if (!confirm(`Excluir "${nome}"? Esta acao nao pode ser desfeita.`)) return;

      btn.disabled = true;
      try {
        await apiCall('modelo', 'excluir', {
          method: 'POST',
          body: { id },
        });

        const card = btn.closest('.modelo-card--admin');
        card?.remove();

        if (alertSlot) {
          showAlert(alertSlot, 'Modelo excluido com sucesso.', 'success');
        }

        if (!document.querySelector('.modelo-card--admin')) {
          const grid = document.querySelector('.modelos-grid--admin');
          if (grid) {
            grid.outerHTML =
              '<div class="empty-state">' +
              '<p>Nenhum modelo cadastrado.</p>' +
              '<p class="empty-state-desc">Envie um design de fundo e defina as zonas de produto no editor visual.</p>' +
              '<a href="novo-modelo.php" class="btn btn-primary">+ Criar primeiro modelo</a>' +
              '</div>';
          }
        }
      } catch (e) {
        if (alertSlot) showAlert(alertSlot, e.message);
        btn.disabled = false;
      }
    });
  });
});
