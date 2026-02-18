import jQuery from 'jquery';
window.jQuery = window.$ = jQuery;

/**
 * Mobile Navigation Logic
 */
const initMobileNav = () => {
  jQuery(".navbar-toggler").click(function () {
    jQuery(this).toggleClass("closed");
    const menu = jQuery(".navigation-mobile");
    menu.toggleClass("visible");

    if (menu.classList?.contains("visible")) {
      document.body.classList.add("no-scroll");
    } else {
      document.body.classList.remove("no-scroll");
    }
  });

  document.querySelectorAll('.navigation-mobile a, .close-menu').forEach(el => {
    el.addEventListener('click', () => {
      document.body.classList.remove('no-scroll');
    });
  });
};

/**
 * Dynamic Swiper Loader
 */
const initSwipers = async () => {
  // Find all swiper containers (generic .swiper and specific blocks)
  const swiperElements = document.querySelectorAll('.swiper, .block-cards-list');

  if (swiperElements.length > 0) {
    try {
      // Import Swiper and its modules (Swiper 9+ uses swiper/modules)
      const { default: Swiper } = await import('swiper');
      const { Navigation, Pagination, Autoplay } = await import('swiper/modules');

      await import('swiper/css');
      await import('swiper/css/navigation');
      await import('swiper/css/pagination');

      swiperElements.forEach((el) => {
        // Skip if already initialized
        if (el.swiper) return;

        // Count actual slides
        const slides = el.querySelectorAll('.swiper-slide');
        const slidesCount = slides.length;

        // If no slides, hide the element and bail
        if (slidesCount === 0) {
          el.style.display = 'none';
          return;
        }

        const isBlockCards = el.classList.contains('block-cards-list');

        // Navigation elements
        const nextEl = el.querySelector('.swiper-button-next');
        const prevEl = el.querySelector('.swiper-button-prev');
        const paginationEl = el.querySelector('.swiper-pagination');

        // Default options
        const options = {
          modules: [Navigation, Pagination, Autoplay],
          spaceBetween: 30,
          grabCursor: true,
          loop: slidesCount > 1, // Loop only if more than 1 slide (for single view)
          breakpoints: {
            640: { slidesPerView: 1, spaceBetween: 20 },
            768: { slidesPerView: isBlockCards ? 3 : 1, spaceBetween: 30 },
            1024: { slidesPerView: isBlockCards ? 4 : 1, spaceBetween: 30 },
          },
        };

        // For grid/multiple slides, disable loop if slides < max slidesPerView
        if (isBlockCards && slidesCount <= 4) {
          options.loop = false;
        }

        if (nextEl && prevEl) {
          // Only show navigation if we have more slides than visible
          if (options.loop || slidesCount > 1) {
             options.navigation = { nextEl, prevEl };
          } else {
             nextEl.style.display = 'none';
             prevEl.style.display = 'none';
          }
        }

        if (paginationEl) {
          if (slidesCount > 1) {
            options.pagination = { el: paginationEl, clickable: true };
          } else {
            paginationEl.style.display = 'none';
          }
        }

        // Custom options for slider.php style (single slide, autoplay)
        if (!isBlockCards) {
          options.slidesPerView = 1;
          if (slidesCount > 1) {
            options.autoplay = {
              delay: 8000,
              disableOnInteraction: false,
            };
          }
          options.watchSlidesProgress = true;
        }

        new Swiper(el, options);
        el.classList.remove('skeleton', 'loading');
      });
    } catch (e) {
      console.error('Failed to load Swiper:', e);
    }
  }
};

/**
 * Dynamic Map Loader (Leaflet/Mapbox fallback)
 */
const initMaps = async () => {
  const mapboxElements = document.querySelectorAll('#map, #map-sede');
  if (mapboxElements.length > 0) {
    try {
      const { default: mapboxgl } = await import('mapbox-gl');
      await import('mapbox-gl/dist/mapbox-gl.css');
      window.mapboxgl = mapboxgl;
      document.dispatchEvent(new CustomEvent('mapbox-ready'));
      console.log('Mapbox dynamic load complete');
    } catch (e) {
      console.error('Failed to load Mapbox:', e);
    }
  }

  const leafletElements = document.querySelectorAll('.leaflet-map');
  if (leafletElements.length > 0) {
    try {
      const { default: L } = await import('leaflet');
      await import('leaflet/dist/leaflet.css');
      window.L = L;
      document.dispatchEvent(new CustomEvent('leaflet-ready'));
      console.log('Leaflet dynamic load complete');
    } catch (e) {
      console.error('Failed to load Leaflet:', e);
    }
  }
};

/**
 * Initialization
 */
document.addEventListener("DOMContentLoaded", () => {
  initMobileNav();
  initSwipers();
  initMaps();
});
