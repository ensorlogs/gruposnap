/*!
 * GrupoSnap · Language switch — un botón ES/EN con ambas banderas (toggle).
 */
(function () {
    'use strict';
    var d = document;

    function readMeta(name) {
        var el = d.querySelector('meta[name="' + name + '"]');
        return el ? (el.getAttribute('content') || '').trim() : '';
    }

    function assetsPrefix() {
        var base = readMeta('gruposnap-assets-base');
        if (base) {
            return base.replace(/\/?$/, '/');
        }
        return '/wp-content/themes/gruposnap/assets/';
    }

    function isEnPath(path) {
        return /\/en(\/|$)/.test(path);
    }

    function detectLang() {
        var path = (d.location && d.location.pathname) || '/';
        if (isEnPath(path)) {
            return 'en';
        }
        var explicit = readMeta('gruposnap-lang');
        if (explicit === 'en' || explicit === 'es') {
            return explicit;
        }
        return 'es';
    }

    function resolveAltUrl(targetLang) {
        var alt = readMeta('gruposnap-lang-alt');
        if (alt) {
            try {
                return new URL(alt, d.location.href).href;
            } catch (e) {
                if (alt.indexOf('http') === 0) {
                    return alt;
                }
            }
        }

        var path = (d.location && d.location.pathname) || '/';
        var parts = path.split('/').filter(Boolean);
        var onEn = parts[0] === 'en';
        if (onEn) {
            parts.shift();
        }
        var relPath = parts.join('/');

        function absUrl(target) {
            try {
                return new URL(target, d.location.href).href;
            } catch (e) {
                return target;
            }
        }

        if (targetLang === 'en') {
            if (onEn) {
                return d.location.href;
            }
            return relPath ? absUrl('en/' + relPath + '/') : absUrl('en/');
        }

        if (!onEn) {
            return d.location.href;
        }
        return relPath ? absUrl('/' + relPath + '/') : absUrl('/');
    }

    function ready(fn) {
        if (d.readyState !== 'loading') {
            fn();
        } else {
            d.addEventListener('DOMContentLoaded', fn);
        }
    }

    function makeFlagImg(code) {
        var img = d.createElement('img');
        var prefix = assetsPrefix();
        img.src = prefix + 'img/flag-' + (code === 'en' ? 'usa' : 'venezuela') + '.svg';
        img.alt = '';
        img.width = 16;
        img.height = 11;
        img.setAttribute('width', '16');
        img.setAttribute('height', '11');
        img.decoding = 'async';
        img.loading = 'eager';
        img.className = 'gruposnap-lang-switch__flag gruposnap-lang-switch__flag--' + code;
        img.setAttribute('aria-hidden', 'true');
        return img;
    }

    function makeToggleBtn(lang) {
        var targetLang = lang === 'en' ? 'es' : 'en';
        var btn = d.createElement('button');
        btn.type = 'button';
        btn.className = 'gruposnap-lang-switch__btn is-' + lang;
        btn.setAttribute(
            'aria-label',
            lang === 'en' ? 'Cambiar a español (ES)' : 'Switch to English (EN)'
        );
        btn.setAttribute('title', lang === 'en' ? 'Español' : 'English');

        var flags = d.createElement('span');
        flags.className = 'gruposnap-lang-switch__flags';
        flags.setAttribute('aria-hidden', 'true');
        flags.appendChild(makeFlagImg('es'));
        flags.appendChild(makeFlagImg('en'));
        btn.appendChild(flags);

        var text = d.createElement('span');
        text.className = 'gruposnap-lang-switch__code';
        text.textContent = 'ES/EN';
        btn.appendChild(text);

        btn.addEventListener('click', function () {
            var url = resolveAltUrl(targetLang);
            if (!url) {
                return;
            }
            try {
                localStorage.setItem('gruposnap_lang', targetLang);
            } catch (e) {}
            d.location.assign(url);
        });

        return btn;
    }

    function buildNav(extraClass, id) {
        var lang = detectLang();
        var nav = d.createElement('nav');
        nav.id = id;
        nav.className = 'gruposnap-lang-switch ' + extraClass;
        nav.setAttribute('aria-label', lang === 'en' ? 'Language' : 'Idioma');
        nav.appendChild(makeToggleBtn(lang));
        return nav;
    }

    function buildSwitcher() {
        if (d.getElementById('gruposnap-lang-switch')) {
            return;
        }

        var lang = detectLang();
        var floating = buildNav('gruposnap-lang-switch--floating', 'gruposnap-lang-switch');
        d.body.appendChild(floating);

        mountMobileLangSwitch();

        if (d.documentElement) {
            d.documentElement.setAttribute('lang', lang === 'en' ? 'en' : 'es');
        }
    }

    function mountMobileLangSwitch() {
        var slot = d.querySelector(
            'body > .mobile-menu.gruposnap-mobile-menu .gruposnap-mobile-menu__lang'
        );
        if (!slot || slot.querySelector('.gruposnap-lang-switch')) {
            return;
        }

        var mobile = buildNav('gruposnap-lang-switch--mobile', 'gruposnap-lang-switch-mobile');
        slot.appendChild(mobile);
    }

    function watchMobileMenu() {
        mountMobileLangSwitch();

        if (typeof MutationObserver === 'undefined') {
            return;
        }

        var observer = new MutationObserver(function () {
            mountMobileLangSwitch();
        });

        observer.observe(d.body, { childList: true, subtree: false });
    }

    window.GrupoSnapLangSwitch = {
        mountMobile: mountMobileLangSwitch
    };

    ready(function () {
        buildSwitcher();
        watchMobileMenu();
    });
})();
