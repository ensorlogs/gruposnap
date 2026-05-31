/*!
 * GrupoSnap · Preloader (logo snap + apertura cremallera)
 */
(function () {
    'use strict';

    var MIN_MS = 2400;
    var EXIT_MS = 950;
    var MAX_MS = 8000;

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function finish(loader) {
        if (!loader || loader.classList.contains('is-exiting')) {
            return;
        }

        loader.classList.add('is-exiting');
        document.documentElement.classList.remove('gsnap-loader-active');

        window.setTimeout(function () {
            loader.classList.add('is-done');
            loader.remove();
        }, EXIT_MS + 80);
    }

    ready(function () {
        var loader = document.getElementById('gsnap-loader');
        if (!loader) {
            return;
        }

        document.documentElement.classList.add('gsnap-loader-active');

        var started = Date.now();
        var done = false;

        function tryFinish() {
            if (done) {
                return;
            }
            var elapsed = Date.now() - started;
            var wait = Math.max(0, MIN_MS - elapsed);
            window.setTimeout(function () {
                done = true;
                finish(loader);
            }, wait);
        }

        if (document.readyState === 'complete') {
            tryFinish();
        } else {
            window.addEventListener('load', tryFinish);
        }

        window.setTimeout(function () {
            tryFinish();
        }, MAX_MS);
    });
})();
