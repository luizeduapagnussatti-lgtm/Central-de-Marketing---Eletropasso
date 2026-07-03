document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.modelo-preview-btn[data-preview-src]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const src = btn.dataset.previewSrc;
      const title = btn.dataset.previewTitle || 'Preview do modelo';
      if (!src) return;

      const lb = document.getElementById('lightbox');
      if (!lb) return;

      const img = lb.querySelector('img');
      const caption = lb.querySelector('.lightbox-caption');
      if (img) {
        img.src = src;
        img.alt = title;
      }
      if (caption) {
        caption.textContent = title;
      }

      lb.classList.add('active');
    });
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeLightbox();
    }
  });
});
