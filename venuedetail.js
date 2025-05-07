document.addEventListener('DOMContentLoaded', function() {
  // Preloader fade-out
  const preloader = document.getElementById('preloader');
  setTimeout(() => {
    preloader.style.opacity = '0';
    setTimeout(() => {
      preloader.style.display = 'none';
    }, 500);
  }, 1000);

  // Gallery Lightbox
  document.querySelectorAll('.gallery-grid img').forEach((img) => {
    img.addEventListener('click', () => {
      const modal = document.createElement('div');
      modal.classList.add('modal');
      const modalImg = document.createElement('img');
      modalImg.src = img.src;
      modalImg.alt = img.alt;
      modalImg.classList.add('modal-img');
      modal.appendChild(modalImg);
      document.body.appendChild(modal);
      modal.addEventListener('click', () => {
        modal.remove();
      });
    });
  });
});
