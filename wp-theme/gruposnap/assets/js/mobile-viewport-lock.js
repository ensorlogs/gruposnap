/*!
 * GrupoSnap — bloquea zoom/pan lateral en home móvil (viewport fijo).
 */
(function () {
    'use strict';

    var MQ = '(max-width: 1024px)';

    function isLockedViewport() {
        return window.matchMedia(MQ).matches;
    }

    function applyViewportMeta() {
        var meta = document.querySelector('meta[name="viewport"]');
        if (!meta) {
            return;
        }

        meta.setAttribute(
            'content',
            'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'
        );
    }

    function lockRootClasses() {
        document.documentElement.classList.add('gruposnap-home-lock');
        if (document.body) {
            document.body.classList.add('gruposnap-home-lock');
        }
    }

    function preventPinchZoom(event) {
        if (!isLockedViewport()) {
            return;
        }

        if (event.touches && event.touches.length > 1) {
            event.preventDefault();
        }
    }

    function preventGestureZoom(event) {
        if (!isLockedViewport()) {
            return;
        }

        event.preventDefault();
    }

    function preventHorizontalPan(event) {
        if (!isLockedViewport() || !event.touches || event.touches.length !== 1) {
            return;
        }

        if (typeof event.touches[0].clientX !== 'number' || typeof event.touches[0].clientY !== 'number') {
            return;
        }

        if (!preventHorizontalPan.startX) {
            preventHorizontalPan.startX = event.touches[0].clientX;
            preventHorizontalPan.startY = event.touches[0].clientY;
            return;
        }

        var dx = Math.abs(event.touches[0].clientX - preventHorizontalPan.startX);
        var dy = Math.abs(event.touches[0].clientY - preventHorizontalPan.startY);

        if (dx <= dy || dx < 8) {
            return;
        }

        var node = event.target;
        while (node && node !== document.body) {
            if (
                node.classList &&
                (
                    node.classList.contains('swiper') ||
                    node.classList.contains('swiper-wrapper') ||
                    node.classList.contains('gruposnap-brands-marquee') ||
                    node.classList.contains('gruposnap-brands-marquee__track')
                )
            ) {
                return;
            }

            node = node.parentElement;
        }

        if (document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1) {
            event.preventDefault();
        }
    }

    function resetPanOrigin() {
        preventHorizontalPan.startX = 0;
        preventHorizontalPan.startY = 0;
    }

    function init() {
        if (!isLockedViewport()) {
            return;
        }

        applyViewportMeta();
        lockRootClasses();

        document.addEventListener('gesturestart', preventGestureZoom, { passive: false });
        document.addEventListener('gesturechange', preventGestureZoom, { passive: false });
        document.addEventListener('gestureend', preventGestureZoom, { passive: false });
        document.addEventListener('touchstart', preventPinchZoom, { passive: false });
        document.addEventListener('touchmove', preventPinchZoom, { passive: false });
        document.addEventListener('touchstart', preventHorizontalPan, { passive: true });
        document.addEventListener('touchmove', preventHorizontalPan, { passive: false });
        document.addEventListener('touchend', resetPanOrigin, { passive: true });
        document.addEventListener('touchcancel', resetPanOrigin, { passive: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.matchMedia(MQ).addEventListener('change', function () {
        if (isLockedViewport()) {
            init();
        } else {
            document.documentElement.classList.remove('gruposnap-home-lock');
            if (document.body) {
                document.body.classList.remove('gruposnap-home-lock');
            }
        }
    });
})();
