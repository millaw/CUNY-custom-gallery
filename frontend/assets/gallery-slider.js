document.addEventListener('DOMContentLoaded', function () {
    const IMAGES_PER_CHUNK = 8;
    let currentCount = 0;
    let currentModalIndex = 0;
    let currentSlide = 0;
    let currentGallerySlide = 0;
  
    const gallery = document.getElementById('gallery');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const sliderTrack = document.getElementById('sliderTrack');
    const sliderGalleryTrack = document.getElementById('sliderGalleryTrack');
    const modal = document.getElementById('modal');
    const modalImg = modal.querySelector('img');
  
    const closeModalBtn = document.getElementById('modalClose');
    const prevModalBtn = document.getElementById('modalPrev');
    const nextModalBtn = document.getElementById('modalNext');
  
    function openModal(index) {
      currentModalIndex = index;
      updateModal();
      modal.classList.add('is-active');
      modal.setAttribute('aria-hidden', 'false');
    }
  
    function closeModal() {
      modal.classList.remove('is-active');
      modal.setAttribute('aria-hidden', 'true');
    }
  
    function updateModal() {
      const { href, alt } = images[currentModalIndex];
      modalImg.src = href;
      modalImg.alt = alt;
    }
  
    function showPrevInModal() {
      currentModalIndex = (currentModalIndex - 1 + images.length) % images.length;
      updateModal();
    }
  
    function showNextInModal() {
      currentModalIndex = (currentModalIndex + 1) % images.length;
      updateModal();
    }
  
    // === GALLERY MODE ===
    if (gallery) {
      function renderGalleryChunk(start, end) {
        for (let i = start; i < end && i < images.length; i++) {
          const img = images[i];
  
          const article = document.createElement('article');
          article.className = 'gallery-item';
          article.dataset.index = i;
  
          const link = document.createElement('a');
          link.href = img.href;
          link.setAttribute('aria-label', `View full-size image: ${img.alt}`);
  
          const image = document.createElement('img');
          image.src = img.src;
          image.alt = img.alt;
          image.loading = 'lazy';
  
          const overlay = document.createElement('div');
          overlay.className = 'overlay';
          overlay.innerHTML = `<span>${img.description || ''}</span>`;
  
          link.appendChild(image);
          link.appendChild(overlay);
          article.appendChild(link);
          gallery.appendChild(article);
        }
  
        currentCount = end;
        if (currentCount >= images.length && loadMoreBtn) {
          loadMoreBtn.style.display = 'none';
        }
      }
  
      renderGalleryChunk(0, IMAGES_PER_CHUNK);
  
      if (loadMoreBtn) {
        loadMoreBtn.style.display = 'block';
        loadMoreBtn.addEventListener('click', function () {
          renderGalleryChunk(currentCount, currentCount + IMAGES_PER_CHUNK);
        });
      }
  
      gallery.addEventListener('click', function (e) {
        const item = e.target.closest('.gallery-item');
        if (item) {
          e.preventDefault();
          openModal(Number(item.dataset.index));
        }
      });
    }
  
    // === SLIDER MODE ===
    if (sliderTrack) {
      const prevBtn = document.getElementById('prevBtn');
      const nextBtn = document.getElementById('nextBtn');
  
      function showSlide(index) {
        if (index < 0) index = images.length - 1;
        if (index >= images.length) index = 0;
        currentSlide = index;
        sliderTrack.style.transform = `translateX(-${100 * index}%)`;
      }
  
      prevBtn && prevBtn.addEventListener('click', () => showSlide(currentSlide - 1));
      nextBtn && nextBtn.addEventListener('click', () => showSlide(currentSlide + 1));
  
      sliderTrack.addEventListener('click', function (e) {
        const slide = e.target.closest('.slide');
        if (slide) openModal(Number(slide.dataset.index));
      });
  
      showSlide(0);
    }
  
    // === SLIDER GALLERY MODE ===
    if (sliderGalleryTrack) {
      const prevGalleryBtn = document.getElementById('prevGalleryBtn');
      const nextGalleryBtn = document.getElementById('nextGalleryBtn');
  
      function showGallerySlide(index) {
        const total = Math.ceil(images.length / IMAGES_PER_CHUNK);
        if (index < 0) index = total - 1;
        if (index >= total) index = 0;
        currentGallerySlide = index;
        sliderGalleryTrack.style.transform = `translateX(-${100 * index}%)`;
      }
  
      prevGalleryBtn && prevGalleryBtn.addEventListener('click', () => showGallerySlide(currentGallerySlide - 1));
      nextGalleryBtn && nextGalleryBtn.addEventListener('click', () => showGallerySlide(currentGallerySlide + 1));
  
      sliderGalleryTrack.addEventListener('click', function (e) {
        const item = e.target.closest('.slider-gallery-item');
        if (item) openModal(Number(item.dataset.index));
      });
  
      showGallerySlide(0);
    }
  
    // === MODAL CONTROLS ===
    closeModalBtn && closeModalBtn.addEventListener('click', closeModal);
    prevModalBtn && prevModalBtn.addEventListener('click', showPrevInModal);
    nextModalBtn && nextModalBtn.addEventListener('click', showNextInModal);
    modal && modal.addEventListener('click', e => {
      if (e.target === modal) closeModal();
    });
  
    // === KEYBOARD CONTROLS ===
    document.addEventListener('keydown', e => {
      if (modal.classList.contains('is-active')) {
        if (e.key === 'Escape') closeModal();
        if (e.key === 'ArrowLeft') showPrevInModal();
        if (e.key === 'ArrowRight') showNextInModal();
        return;
      }
  
      if (sliderTrack && sliderTrack.offsetParent !== null) {
        if (e.key === 'ArrowLeft') showSlide(currentSlide - 1);
        if (e.key === 'ArrowRight') showSlide(currentSlide + 1);
      }
  
      if (sliderGalleryTrack && sliderGalleryTrack.offsetParent !== null) {
        if (e.key === 'ArrowLeft') showGallerySlide(currentGallerySlide - 1);
        if (e.key === 'ArrowRight') showGallerySlide(currentGallerySlide + 1);
      }
    });
  });
  