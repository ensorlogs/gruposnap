(function () {
    'use strict';

    var cfg = window.gruposnapPostRating || {};

    function formatMessage(template, average, count) {
        if (!template) {
            return '';
        }

        return template
            .replace('%s', String(average))
            .replace('%s', String(count));
    }

    function setActiveStars(stars, value) {
        stars.forEach(function (star) {
            var rating = parseInt(star.getAttribute('data-rating'), 10);
            star.classList.toggle('is-active', rating <= value);
        });
    }

    function initRating(section) {
        var postId = parseInt(section.getAttribute('data-post-id'), 10);
        var userRating = parseInt(section.getAttribute('data-user-rating'), 10) || 0;
        var hasVoted = section.getAttribute('data-has-voted') === '1';
        var stars = Array.prototype.slice.call(section.querySelectorAll('.gruposnap-post-rating__star'));
        var statusEl = section.querySelector('.gruposnap-post-rating__status');

        if (!stars.length || !postId) {
            return;
        }

        if (userRating > 0) {
            setActiveStars(stars, userRating);
        }

        if (hasVoted) {
            return;
        }

        stars.forEach(function (star) {
            star.addEventListener('mouseenter', function () {
                setActiveStars(stars, parseInt(star.getAttribute('data-rating'), 10));
            });

            star.addEventListener('focus', function () {
                setActiveStars(stars, parseInt(star.getAttribute('data-rating'), 10));
            });

            star.addEventListener('click', function () {
                var rating = parseInt(star.getAttribute('data-rating'), 10);

                if (!rating || section.classList.contains('is-submitting')) {
                    return;
                }

                section.classList.add('is-submitting');

                var body = new URLSearchParams();
                body.set('action', 'gruposnap_post_rating');
                body.set('nonce', cfg.nonce || '');
                body.set('postId', String(postId));
                body.set('rating', String(rating));

                fetch(cfg.ajaxUrl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    },
                    body: body.toString(),
                })
                    .then(function (response) {
                        return response.json().then(function (payload) {
                            return { ok: response.ok, payload: payload };
                        });
                    })
                    .then(function (result) {
                        if (!result.ok || !result.payload || !result.payload.success) {
                            throw new Error(
                                (result.payload && result.payload.data && result.payload.data.message) ||
                                    (cfg.i18n && cfg.i18n.error) ||
                                    'Error'
                            );
                        }

                        var data = result.payload.data || {};

                        setActiveStars(stars, rating);
                        stars.forEach(function (item) {
                            item.disabled = true;
                            item.setAttribute('aria-checked', item.getAttribute('data-rating') === String(rating) ? 'true' : 'false');
                        });

                        section.setAttribute('data-has-voted', '1');
                        section.setAttribute('data-user-rating', String(rating));
                        section.classList.add('has-voted');

                        if (statusEl && data.message) {
                            statusEl.textContent = data.message;
                        } else if (statusEl && cfg.i18n && cfg.i18n.thanks) {
                            statusEl.textContent = cfg.i18n.thanks;
                        }
                    })
                    .catch(function () {
                        if (statusEl && cfg.i18n && cfg.i18n.error) {
                            statusEl.textContent = cfg.i18n.error;
                        }
                        setActiveStars(stars, 0);
                    })
                    .finally(function () {
                        section.classList.remove('is-submitting');
                    });
            });
        });

        section.addEventListener('mouseleave', function () {
            setActiveStars(stars, userRating);
        });
    }

    function boot() {
        document.querySelectorAll('.gruposnap-post-rating').forEach(initRating);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
