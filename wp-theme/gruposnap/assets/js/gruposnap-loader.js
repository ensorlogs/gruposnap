/*!
 * GrupoSnap · Preloader (video recortado ~5 s → fade → sitio visible)
 */
(function () {
    'use strict';

    var cfg = window.gruposnapLoader || {};
    var FADE_MS = cfg.fadeMs || 280;
    var DURATION_MS = cfg.durationMs || 5000;
    var MAX_VIDEO_SEC = cfg.maxVideoSec || 5;
    var MAX_MS = cfg.maxMs || 6500;
    var MIN_MS = cfg.minMs || 300;
    var PLAYBACK_RATE = typeof cfg.playbackRate === 'number' ? cfg.playbackRate : 1.75;

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function unlockPage() {
        document.documentElement.classList.remove('gsnap-loader-active');
        document.body.classList.remove('gsnap-loader-active');
        document.documentElement.style.removeProperty('overflow');
        document.body.style.removeProperty('overflow');
        document.documentElement.style.removeProperty('height');
        document.body.style.removeProperty('height');
    }

    function finish(loader) {
        if (!loader || loader.classList.contains('is-hiding')) {
            return;
        }

        unlockPage();
        loader.classList.add('is-hiding');

        window.setTimeout(function () {
            if (loader.parentNode) {
                loader.parentNode.removeChild(loader);
            }
        }, FADE_MS + 40);
    }

    function pauseVideo(video) {
        if (!video) {
            return;
        }
        try {
            video.pause();
        } catch (e) {
            /* ignore */
        }
    }

    function applyPlaybackRate(video) {
        if (!video) {
            return;
        }

        var rate = PLAYBACK_RATE;
        if (rate < 1) {
            rate = 1;
        }
        if (rate > 3) {
            rate = 3;
        }

        try {
            video.defaultPlaybackRate = rate;
            video.playbackRate = rate;
        } catch (e) {
            /* ignore */
        }
    }

    function playVideo(video) {
        if (!video) {
            return Promise.resolve();
        }

        video.muted = true;
        video.playsInline = true;
        video.setAttribute('playsinline', '');
        video.setAttribute('webkit-playsinline', '');

        if (cfg.videoUrl && !video.getAttribute('src')) {
            video.setAttribute('src', cfg.videoUrl);
        }

        applyPlaybackRate(video);

        var playPromise = video.play();
        if (playPromise && playPromise.catch) {
            return playPromise.catch(function () {});
        }

        return Promise.resolve();
    }

    ready(function () {
        var loader = document.getElementById('gsnap-loader');
        if (!loader) {
            return;
        }

        document.documentElement.classList.add('gsnap-loader-active');
        document.body.classList.add('gsnap-loader-active');

        var video = loader.querySelector('.gsnap-loader__video');
        var started = Date.now();
        var done = false;
        var videoDone = false;

        function markVideoDone() {
            if (videoDone) {
                return;
            }
            videoDone = true;
            pauseVideo(video);
            tryFinish();
        }

        playVideo(video);

        if (video) {
            video.addEventListener('loadedmetadata', function () {
                applyPlaybackRate(video);
            });

            video.addEventListener('timeupdate', function () {
                if (video.currentTime >= MAX_VIDEO_SEC - 0.05) {
                    markVideoDone();
                }
            });

            video.addEventListener('ended', markVideoDone);
            video.addEventListener('error', markVideoDone);
        } else {
            videoDone = true;
        }

        window.setTimeout(markVideoDone, DURATION_MS);

        function tryFinish() {
            if (done) {
                return;
            }

            if (!videoDone) {
                return;
            }

            var elapsed = Date.now() - started;
            var wait = Math.max(0, MIN_MS - elapsed);

            window.setTimeout(function () {
                if (done) {
                    return;
                }
                done = true;
                finish(loader);
            }, wait);
        }

        window.setTimeout(markVideoDone, MAX_MS);

        if (videoDone) {
            tryFinish();
        }
    });
})();
