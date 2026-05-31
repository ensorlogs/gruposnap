/*!
 * GrupoSnap · Consentimiento de cookies
 */
(function () {
    'use strict';
    var d = document;
    var STORAGE_KEY = 'gruposnap_consent';
    var COOKIE_NAME = 'gruposnap_consent';
    var COOKIE_DAYS = 365;
    var CATEGORIES = ['necessary', 'analytics', 'marketing'];

    function readConsent() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            if (raw) {
                return JSON.parse(raw);
            }
        } catch (e) {}
        var m = d.cookie.match(new RegExp('(?:^|; )' + COOKIE_NAME + '=([^;]*)'));
        if (m) {
            try {
                return JSON.parse(decodeURIComponent(m[1]));
            } catch (e) {}
        }
        return null;
    }

    function writeConsent(consent) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(consent));
        } catch (e) {}
        var exp = new Date();
        exp.setDate(exp.getDate() + COOKIE_DAYS);
        d.cookie =
            COOKIE_NAME +
            '=' +
            encodeURIComponent(JSON.stringify(consent)) +
            '; expires=' +
            exp.toUTCString() +
            '; path=/; SameSite=Lax';
    }

    function clearConsent() {
        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch (e) {}
        d.cookie = COOKIE_NAME + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
    }

    function activateConsentedScripts(consent) {
        var nodes = Array.prototype.slice.call(
            d.querySelectorAll('script[type="text/plain"][data-gsnap-consent]')
        );
        nodes.forEach(function (s) {
            var need = s.getAttribute('data-gsnap-consent');
            if (!consent[need]) {
                return;
            }
            var n = d.createElement('script');
            for (var i = 0; i < s.attributes.length; i++) {
                var a = s.attributes[i];
                if (a.name === 'type' || a.name === 'data-gsnap-consent') {
                    continue;
                }
                n.setAttribute(a.name, a.value);
            }
            n.type = s.getAttribute('data-gsnap-type') || 'text/javascript';
            n.text = s.text || s.innerHTML;
            s.parentNode.insertBefore(n, s);
            s.parentNode.removeChild(s);
        });
        try {
            d.dispatchEvent(new CustomEvent('gruposnap:consent', { detail: consent }));
        } catch (e) {}
    }

    function getLegalUrl() {
        var m = d.querySelector('meta[name="gruposnap-cookies-url"]');
        return m && m.content ? m.content : '/legal/cookies/';
    }

    function ensureBanner(openPanelFn) {
        if (d.querySelector('.gsnap-cookies')) {
            return d.querySelector('.gsnap-cookies');
        }
        var legalUrl = getLegalUrl();
        var el = d.createElement('section');
        el.className = 'gsnap-cookies';
        el.setAttribute('role', 'dialog');
        el.setAttribute('aria-label', 'Aviso de cookies');
        el.innerHTML =
            '<h2 class="gsnap-cookies__title">Usamos cookies</h2>' +
            '<p class="gsnap-cookies__text">Usamos cookies técnicas necesarias para que el sitio funcione y, si tú lo aceptas, cookies de medición y marketing para entender qué contenido funciona mejor. Puedes aceptar, rechazar o ajustar por categoría. Más información en nuestra ' +
            '<a href="' +
            legalUrl +
            '" class="gsnap-cookies__link">política de cookies</a>.</p>' +
            '<div class="gsnap-cookies__actions">' +
            '<button type="button" class="gsnap-cookies__btn" data-action="reject">Rechazar todo</button>' +
            '<button type="button" class="gsnap-cookies__btn" data-action="customize">Personalizar</button>' +
            '<button type="button" class="gsnap-cookies__btn gsnap-cookies__btn--primary" data-action="accept">Aceptar todo</button>' +
            '</div>';
        d.body.appendChild(el);

        el.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-action]');
            if (!btn) {
                return;
            }
            var action = btn.getAttribute('data-action');
            if (action === 'accept') {
                writeAndApply({ necessary: true, analytics: true, marketing: true });
                el.classList.remove('is-visible');
            } else if (action === 'reject') {
                writeAndApply({ necessary: true, analytics: false, marketing: false });
                el.classList.remove('is-visible');
            } else if (action === 'customize') {
                openPanelFn();
            }
        });
        return el;
    }

    function ensureModal() {
        if (d.querySelector('.gsnap-cookies-modal')) {
            return d.querySelector('.gsnap-cookies-modal');
        }
        var modal = d.createElement('div');
        modal.className = 'gsnap-cookies-modal';
        modal.innerHTML =
            '<div class="gsnap-cookies-modal__panel" role="dialog" aria-modal="true" aria-labelledby="gsnap-ck-title">' +
            '<h2 id="gsnap-ck-title" class="gsnap-cookies-modal__title">Preferencias de cookies</h2>' +
            '<p class="gsnap-cookies-modal__intro">Activa solo las categorías que quieras. Las cookies técnicas son imprescindibles y no se pueden desactivar.</p>' +
            '<div class="gsnap-cookies-modal__row">' +
            '<div class="gsnap-cookies-modal__row-top">' +
            '<span class="gsnap-cookies-modal__row-title">Técnicas (necesarias)</span>' +
            '<label class="gsnap-cookies-switch"><input type="checkbox" checked disabled data-cat="necessary"><span class="gsnap-cookies-switch__slider"></span></label>' +
            '</div>' +
            '<p class="gsnap-cookies-modal__row-text">Sesión, carrito, accesibilidad y registro de consentimiento.</p>' +
            '</div>' +
            '<div class="gsnap-cookies-modal__row">' +
            '<div class="gsnap-cookies-modal__row-top">' +
            '<span class="gsnap-cookies-modal__row-title">Analítica / medición</span>' +
            '<label class="gsnap-cookies-switch"><input type="checkbox" data-cat="analytics"><span class="gsnap-cookies-switch__slider"></span></label>' +
            '</div>' +
            '<p class="gsnap-cookies-modal__row-text">Datos agregados para mejorar el sitio y el catálogo.</p>' +
            '</div>' +
            '<div class="gsnap-cookies-modal__row">' +
            '<div class="gsnap-cookies-modal__row-top">' +
            '<span class="gsnap-cookies-modal__row-title">Marketing</span>' +
            '<label class="gsnap-cookies-switch"><input type="checkbox" data-cat="marketing"><span class="gsnap-cookies-switch__slider"></span></label>' +
            '</div>' +
            '<p class="gsnap-cookies-modal__row-text">Recordar interacciones con campañas. Hoy no se cargan por defecto.</p>' +
            '</div>' +
            '<div class="gsnap-cookies-modal__actions">' +
            '<button type="button" class="gsnap-cookies__btn" data-action="cancel">Cancelar</button>' +
            '<button type="button" class="gsnap-cookies__btn gsnap-cookies__btn--primary" data-action="save">Guardar preferencias</button>' +
            '</div>' +
            '</div>';
        d.body.appendChild(modal);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.remove('is-open');
                return;
            }
            var btn = e.target.closest('[data-action]');
            if (!btn) {
                return;
            }
            if (btn.getAttribute('data-action') === 'cancel') {
                modal.classList.remove('is-open');
            } else if (btn.getAttribute('data-action') === 'save') {
                var c = { necessary: true };
                CATEGORIES.forEach(function (cat) {
                    if (cat === 'necessary') {
                        return;
                    }
                    var input = modal.querySelector('input[data-cat="' + cat + '"]');
                    c[cat] = !!(input && input.checked);
                });
                writeAndApply(c);
                modal.classList.remove('is-open');
                var b = d.querySelector('.gsnap-cookies');
                if (b) {
                    b.classList.remove('is-visible');
                }
            }
        });
        return modal;
    }

    function openModal(currentConsent) {
        var modal = ensureModal();
        CATEGORIES.forEach(function (cat) {
            if (cat === 'necessary') {
                return;
            }
            var input = modal.querySelector('input[data-cat="' + cat + '"]');
            if (input) {
                input.checked = !!(currentConsent && currentConsent[cat]);
            }
        });
        modal.classList.add('is-open');
    }

    function writeAndApply(consent) {
        writeConsent(consent);
        activateConsentedScripts(consent);
    }

    function init() {
        var consent = readConsent();
        if (consent) {
            activateConsentedScripts(consent);
        } else {
            var banner = ensureBanner(function () {
                openModal(null);
            });
            banner.classList.add('is-visible');
        }

        d.addEventListener('click', function (e) {
            var t = e.target.closest('[data-gsnap-cookies-open], .gsnap-cookies-reopen');
            if (!t) {
                return;
            }
            e.preventDefault();
            openModal(readConsent() || {});
        });
    }

    window.GrupoSnapCookies = {
        get: readConsent,
        clear: function () {
            clearConsent();
            location.reload();
        },
        open: function () {
            openModal(readConsent() || {});
        }
    };

    if (d.readyState !== 'loading') {
        init();
    } else {
        d.addEventListener('DOMContentLoaded', init);
    }
})();
