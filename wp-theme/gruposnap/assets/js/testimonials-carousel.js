/**
 * Carrusel de testimonios en la home (12 marcas).
 */
(function () {
	'use strict';

	function getSwiperConstructor() {
		if (typeof elementorFrontend !== 'undefined' && elementorFrontend.utils && elementorFrontend.utils.swiper) {
			return elementorFrontend.utils.swiper;
		}
		if (typeof Swiper !== 'undefined') {
			return Swiper;
		}
		return null;
	}

	function mountCarousel() {
		var mount = document.getElementById('gruposnap-testimonials-carousel-mount');
		if (!mount || mount.dataset.gsMounted === '1') {
			return;
		}

		var sectionId = mount.getAttribute('data-section-id') || '25b718d';
		var section = document.querySelector('.elementor-element-' + sectionId);
		if (!section) {
			return;
		}

		var container = section.querySelector(':scope > .elementor-container');
		var carouselEl = mount.querySelector('.gruposnap-testimonials-carousel');
		if (!container || !carouselEl) {
			return;
		}

		mount.hidden = false;
		mount.removeAttribute('hidden');
		container.innerHTML = '';
		container.appendChild(carouselEl);
		section.classList.add('gruposnap-testimonials-carousel-active');
		mount.dataset.gsMounted = '1';

		var SwiperCtor = getSwiperConstructor();
		if (!SwiperCtor) {
			return;
		}

		var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		var options = {
			slidesPerView: 1,
			spaceBetween: 20,
			loop: true,
			speed: 500,
			autoplay: prefersReducedMotion
				? false
				: {
						delay: 3800,
						disableOnInteraction: false,
						pauseOnMouseEnter: true,
					},
			breakpoints: {
				640: {
					slidesPerView: 1,
					spaceBetween: 22,
				},
				900: {
					slidesPerView: 2,
					spaceBetween: 24,
				},
				1200: {
					slidesPerView: 3,
					spaceBetween: 28,
				},
			},
		};

		carouselEl.querySelectorAll('.gruposnap-brand-logo, .wdt-content-image img').forEach(function (img) {
			img.removeAttribute('width');
			img.removeAttribute('height');
			img.style.setProperty('width', 'auto', 'important');
			img.style.setProperty('height', 'auto', 'important');
			img.style.setProperty('max-width', '100%', 'important');
			img.style.setProperty('max-height', '100%', 'important');
			img.style.setProperty('object-fit', 'contain', 'important');
		});

		var instance = new SwiperCtor(carouselEl, options);
		if (instance && typeof instance.then === 'function') {
			instance.then(function (swiper) {
				carouselEl.gruposnapSwiper = swiper;
			});
		} else {
			carouselEl.gruposnapSwiper = instance;
		}
	}

	function boot() {
		if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
			elementorFrontend.hooks.addAction('frontend/element_ready/global', function () {
				mountCarousel();
			});
		}
		mountCarousel();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}

	window.addEventListener('load', mountCarousel);
})();
