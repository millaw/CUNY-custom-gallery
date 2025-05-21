document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.cuny-gallery-wrapper').forEach(function(wrapper) {
    const galleryId = wrapper.dataset.galleryId;
    const images = window['galleryImages_' + galleryId] || [];
    const IMAGES_PER_CHUNK = 8;
    let currentCount = 0;
    let currentModalIndex = 0;
    let currentSlide = 0;
    let currentGallerySlide = 0;
    let isSliding = false;
    let queuedSlideIndex = null;
    let pendingFocusDirection = null;

    const gallery = wrapper.querySelector('#gallery-' + galleryId);
    const loadMoreBtn = wrapper.querySelector('#loadMoreBtn-' + galleryId);
    const sliderTrack = wrapper.querySelector('#sliderTrack-' + galleryId);
    const sliderGalleryTrack = wrapper.querySelector('#sliderGalleryTrack-' + galleryId);
    const modal = wrapper.querySelector('#modal-' + galleryId);
    const modalImg = modal ? modal.querySelector('img') : null;
    const modalLabel = modal ? modal.querySelector('h2') : null;

    const closeModalBtn = wrapper.querySelector('#modalClose-' + galleryId);
    const prevModalBtn = wrapper.querySelector('#modalPrev-' + galleryId);
    const nextModalBtn = wrapper.querySelector('#modalNext-' + galleryId);

    function openModal(index) {
      currentModalIndex = index;
      updateModal();
      modal.classList.add('is-active');
      modal.setAttribute('aria-hidden', 'false');
      modal.setAttribute('role', 'dialog');
      modal.setAttribute('aria-modal', 'true');
      if (modalLabel) modal.setAttribute('aria-labelledby', modalLabel.id || 'modalLabel-' + galleryId);
      closeModalBtn?.focus();
    }

    function closeModal() {
      modal.classList.remove('is-active');
      modal.setAttribute('aria-hidden', 'true');
      const focusable = wrapper.querySelector('[data-index="' + currentModalIndex + '"]');
      focusable?.focus();
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
          article.setAttribute('tabindex', '0');
          article.setAttribute('role', 'button');
          article.setAttribute('aria-label', img.alt || `Image ${i + 1}`);

          const link = document.createElement('a');
          link.href = img.href;
          link.setAttribute('aria-hidden', 'true');

          const image = document.createElement('img');
          image.src = img.src;
          image.alt = img.alt;
          image.loading = 'lazy';

          const overlay = document.createElement('div');
          overlay.className = 'overlay';
          overlay.innerHTML = `<span>${img.alt || ''}</span>`;

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
          const startIndex = currentCount;
          const endIndex = currentCount + IMAGES_PER_CHUNK;
          renderGalleryChunk(startIndex, endIndex);

          // Move focus to the first new image
          const newItem = wrapper.querySelector(`.gallery-item[data-index="${startIndex}"]`);
          newItem?.focus();
        });
      }

      gallery.addEventListener('click', function (e) {
        const item = e.target.closest('.gallery-item');
        if (item) {
          e.preventDefault();
          openModal(Number(item.dataset.index));
        }
      });

      gallery.addEventListener('keydown', function (e) {
        const item = e.target.closest('.gallery-item');
        if (!item) return;

        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          openModal(Number(item.dataset.index));
          return;
        }

        if (e.key === 'Tab') {
          const focusableItems = wrapper.querySelectorAll('.gallery-item[tabindex="0"]');
          const first = focusableItems[0];
          const last = focusableItems[focusableItems.length - 1];

          if (!e.shiftKey && e.target === last) {
            // Tab forward from last image → send focus to Load More button
            const btn = wrapper.querySelector('#loadMoreBtn-' + galleryId);
            if (btn) {
              e.preventDefault();
              btn.focus();
            }
          }

          if (e.shiftKey && e.target === first) {
            // Optional: go back to some heading or skip focus trap
          }
        }
      });
    }

    // === SLIDER MODE ===
    if (sliderTrack) {
      const prevBtn = wrapper.querySelector('#prevBtn-' + galleryId);
      const nextBtn = wrapper.querySelector('#nextBtn-' + galleryId);

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

      sliderTrack.addEventListener('keydown', function (e) {
        const slide = e.target.closest('.slide');
        if (slide && (e.key === 'Enter' || e.key === ' ')) {
          e.preventDefault();
          openModal(Number(slide.dataset.index));
        }
      });

      showSlide(0);
    }

    // === SLIDER GALLERY MODE ===
    if (sliderGalleryTrack) {
      const prevGalleryBtn = wrapper.querySelector('#prevGalleryBtn-' + galleryId);
      const nextGalleryBtn = wrapper.querySelector('#nextGalleryBtn-' + galleryId);

      function updateFocusableItems() {
        const items = sliderGalleryTrack.querySelectorAll('.slider-gallery-item');
        const start = currentGallerySlide * IMAGES_PER_CHUNK;
        const end = start + IMAGES_PER_CHUNK;
        items.forEach((item, index) => {
          item.setAttribute('tabindex', (index >= start && index < end) ? '0' : '-1');
        });
      }

      function showGallerySlide(index) {
        const total = Math.ceil(images.length / IMAGES_PER_CHUNK);
        if (index < 0) index = total - 1;
        if (index >= total) index = 0;
        if (isSliding) {
          queuedSlideIndex = index;
          return;
        }
        isSliding = true;
        currentGallerySlide = index;
        sliderGalleryTrack.style.transform = `translateX(-${100 * index}%)`;
      }

      sliderGalleryTrack.addEventListener('transitionend', (e) => {
        if (e.propertyName === 'transform') {
          updateFocusableItems();
          isSliding = false;
          if (pendingFocusDirection) {
            const focusableItems = Array.from(sliderGalleryTrack.querySelectorAll('.slider-gallery-item[tabindex="0"]'));
            if (pendingFocusDirection === 'forward') {
              focusableItems[0]?.focus();
            } else if (pendingFocusDirection === 'backward') {
              focusableItems[focusableItems.length - 1]?.focus();
            }
            pendingFocusDirection = null;
          }
          if (queuedSlideIndex !== null) {
            const nextIndex = queuedSlideIndex;
            queuedSlideIndex = null;
            showGallerySlide(nextIndex);
          }
        }
      });

      prevGalleryBtn && prevGalleryBtn.addEventListener('click', () => showGallerySlide(currentGallerySlide - 1));
      nextGalleryBtn && nextGalleryBtn.addEventListener('click', () => showGallerySlide(currentGallerySlide + 1));

      sliderGalleryTrack.addEventListener('click', function (e) {
        const item = e.target.closest('.slider-gallery-item');
        if (item) openModal(Number(item.dataset.index));
      });

      sliderGalleryTrack.addEventListener('keydown', function (e) {
        const item = e.target.closest('.slider-gallery-item');
        if (!item) return;

        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          openModal(Number(item.dataset.index));
        }

        if (e.key === 'Tab') {
          if (isSliding || pendingFocusDirection) {
            e.preventDefault();
            return;
          }

          const focusableElements = Array.from(sliderGalleryTrack.querySelectorAll('.slider-gallery-item[tabindex="0"]'));
          const first = focusableElements[0];
          const last = focusableElements[focusableElements.length - 1];

          if (!e.shiftKey && e.target === last) {
            e.preventDefault();
            pendingFocusDirection = 'forward';
            showGallerySlide(currentGallerySlide + 1);
          } else if (e.shiftKey && e.target === first) {
            e.preventDefault();
            pendingFocusDirection = 'backward';
            showGallerySlide(currentGallerySlide - 1);
          }
        }
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

    modal && modal.addEventListener('keydown', function(e) {
      const focusable = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
      const first = focusable[0];
      const last = focusable[focusable.length - 1];

      if (e.key === 'Tab') {
        if (e.shiftKey && document.activeElement === first) {
          e.preventDefault();
          last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
          e.preventDefault();
          first.focus();
        }
      }
    });

    // === GLOBAL KEYBOARD CONTROLS FOR THIS GALLERY ===
    document.addEventListener('keydown', function(e) {
      if (!wrapper.offsetParent) return;

      if (modal && modal.classList.contains('is-active')) {
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
});
