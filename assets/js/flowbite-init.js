(function () {
  function init() {
    if (typeof window.initFlowbite === 'function') {
      window.initFlowbite();
    }

    // Inisialisasi Flowbite Carousel untuk hero section
    if (typeof window.Carousel !== 'undefined') {
      document.querySelectorAll('[data-carousel]').forEach(function (carouselEl) {
        new window.Carousel(carouselEl, {
          indicators: false,
        });
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
