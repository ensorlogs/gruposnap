/*!
 * GrupoSnap · Accesibilidad (toolbar flotante)
 */
(function () {
    'use strict';
    var d = document;
    var STORAGE_KEY = 'gruposnap_a11y_prefs_v1';

    function loadPrefs() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : {};
        } catch (e) {
            return {};
        }
    }

    function savePrefs(p) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(p));
        } catch (e) {}
    }

    function applyPrefs(p) {
        var root = d.documentElement;
        root.classList.toggle('gsnap-a11y-text-lg', p.text === 'lg');
        root.classList.toggle('gsnap-a11y-text-xl', p.text === 'xl');
        root.classList.toggle('gsnap-a11y-spacing', !!p.spacing);
        root.classList.toggle('gsnap-a11y-contrast', !!p.contrast);
    }

    function cfgStrings() {
        return (window.GrupoSnapA11yConfig && window.GrupoSnapA11yConfig.strings) || {};
    }

    function t(key, fallback) {
        var s = cfgStrings();
        return s[key] || fallback;
    }

    function yesNo(val) {
        return val ? t('yes', 'Sí') : t('no', 'No');
    }

    function ensureSkipLink() {
        if (d.querySelector('.gsnap-skip-link')) {
            return;
        }
        var a = d.createElement('a');
        a.href = '#main-content';
        a.className = 'gsnap-skip-link';
        a.textContent = t('skipLink', 'Saltar al contenido principal');
        if (d.body.firstChild) {
            d.body.insertBefore(a, d.body.firstChild);
        } else {
            d.body.appendChild(a);
        }
        if (!d.getElementById('main-content')) {
            var target = d.getElementById('main') || d.querySelector('main, #primary, .single-entry-body, article');
            if (target) {
                target.id = 'main-content';
            }
        }
    }

    function closePanel(panel, fab) {
        panel.classList.remove('is-open');
        fab.setAttribute('aria-expanded', 'false');
    }

    function openPanel(panel, fab) {
        panel.classList.add('is-open');
        fab.setAttribute('aria-expanded', 'true');
    }

    function ready(fn) {
        if (d.readyState !== 'loading') {
            fn();
        } else {
            d.addEventListener('DOMContentLoaded', fn);
        }
    }

    function buildToolbar(prefs) {
        var fab = d.createElement('button');
        fab.className = 'gsnap-a11y-fab';
        fab.type = 'button';
        fab.setAttribute('aria-label', t('a11yOpen', 'Abrir opciones de accesibilidad'));
        fab.setAttribute('aria-expanded', 'false');
        fab.setAttribute('aria-controls', 'gsnap-a11y-panel');
        fab.innerHTML =
            '<svg viewBox="0 0 24 24" aria-hidden="true">' +
            '<circle cx="12" cy="4" r="2"/>' +
            '<path d="M3 8h18v2l-6 1v3l2 7h-2l-2-6h-2l-2 6H7l2-7v-3L3 10z"/>' +
            '</svg>';

        var panel = d.createElement('section');
        panel.id = 'gsnap-a11y-panel';
        panel.className = 'gsnap-a11y-panel';
        panel.setAttribute('role', 'dialog');
        panel.setAttribute('aria-modal', 'false');
        panel.setAttribute('aria-label', t('a11yDialog', 'Opciones de accesibilidad'));
        panel.innerHTML = [
            '<header class="gsnap-a11y-panel__head">',
            '<h2>' + t('a11yTitle', 'Accesibilidad') + '</h2>',
            '<button type="button" class="gsnap-a11y-close" aria-label="' +
                t('a11yClose', 'Cerrar panel de accesibilidad') +
                '">&times;</button>',
            '</header>',
            '<div class="gsnap-a11y-panel__body">',
            '<div class="gsnap-a11y-field">',
            '<span class="gsnap-a11y-field__label">' + t('textSize', 'Tamaño del texto') + '</span>',
            '<div class="gsnap-a11y-seg" role="group" aria-label="' + t('textSize', 'Tamaño del texto') + '">',
            '<button type="button" class="gsnap-a11y-btn" data-text="md" aria-label="' +
                t('textNormal', 'Tamaño normal') +
                '">A</button>',
            '<button type="button" class="gsnap-a11y-btn" data-text="lg" aria-label="' +
                t('textLarge', 'Tamaño grande') +
                '">A+</button>',
            '<button type="button" class="gsnap-a11y-btn" data-text="xl" aria-label="' +
                t('textXLarge', 'Tamaño muy grande') +
                '">A++</button>',
            '</div>',
            '</div>',
            '<div class="gsnap-a11y-field gsnap-a11y-field--row">',
            '<div class="gsnap-a11y-field__copy">',
            '<span class="gsnap-a11y-field__label">' + t('spacing', 'Espaciado') + '</span>',
            '<span class="gsnap-a11y-field__hint">' + t('spacingHint', 'Más aire al leer') + '</span>',
            '</div>',
            '<button type="button" class="gsnap-a11y-btn" data-toggle="spacing" aria-pressed="false">' +
                t('no', 'No') +
                '</button>',
            '</div>',
            '<div class="gsnap-a11y-field gsnap-a11y-field--row">',
            '<div class="gsnap-a11y-field__copy">',
            '<span class="gsnap-a11y-field__label">' + t('contrast', 'Alto contraste') + '</span>',
            '<span class="gsnap-a11y-field__hint">' + t('contrastHint', 'Mejor legibilidad') + '</span>',
            '</div>',
            '<button type="button" class="gsnap-a11y-btn" data-toggle="contrast" aria-pressed="false">' +
                t('no', 'No') +
                '</button>',
            '</div>',
            '<button type="button" class="gsnap-a11y-btn gsnap-a11y-btn--reset" data-action="reset">' +
                t('reset', 'Restablecer') +
                '</button>',
            '</div>'
        ].join('');

        d.body.appendChild(fab);
        d.body.appendChild(panel);

        var closeBtn = panel.querySelector('.gsnap-a11y-close');

        function refreshUI() {
            panel.querySelectorAll('[data-text]').forEach(function (b) {
                b.classList.toggle('is-active', (prefs.text || 'md') === b.getAttribute('data-text'));
            });
            var sp = panel.querySelector('[data-toggle="spacing"]');
            sp.classList.toggle('is-active', !!prefs.spacing);
            sp.textContent = yesNo(prefs.spacing);
            sp.setAttribute('aria-pressed', prefs.spacing ? 'true' : 'false');
            var co = panel.querySelector('[data-toggle="contrast"]');
            co.classList.toggle('is-active', !!prefs.contrast);
            co.textContent = yesNo(prefs.contrast);
            co.setAttribute('aria-pressed', prefs.contrast ? 'true' : 'false');
        }

        fab.addEventListener('click', function () {
            if (panel.classList.contains('is-open')) {
                closePanel(panel, fab);
            } else {
                openPanel(panel, fab);
            }
        });

        closeBtn.addEventListener('click', function () {
            closePanel(panel, fab);
            fab.focus();
        });

        panel.addEventListener('click', function (e) {
            var t = e.target.closest('button');
            if (!t || t.classList.contains('gsnap-a11y-close')) {
                return;
            }
            if (t.hasAttribute('data-text')) {
                prefs.text = t.getAttribute('data-text');
            } else if (t.getAttribute('data-toggle') === 'spacing') {
                prefs.spacing = !prefs.spacing;
            } else if (t.getAttribute('data-toggle') === 'contrast') {
                prefs.contrast = !prefs.contrast;
            } else if (t.getAttribute('data-action') === 'reset') {
                prefs = {};
            }
            applyPrefs(prefs);
            savePrefs(prefs);
            refreshUI();
        });

        d.addEventListener('click', function (e) {
            if (
                panel.classList.contains('is-open') &&
                !panel.contains(e.target) &&
                !fab.contains(e.target)
            ) {
                closePanel(panel, fab);
            }
        });

        d.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && panel.classList.contains('is-open')) {
                closePanel(panel, fab);
                fab.focus();
            }
        });

        refreshUI();

        window.GrupoSnapA11y = {
            toggle: function () {
                if (panel.classList.contains('is-open')) {
                    closePanel(panel, fab);
                } else {
                    openPanel(panel, fab);
                }
            },
            open: function () {
                openPanel(panel, fab);
            },
            close: function () {
                closePanel(panel, fab);
            }
        };
    }

    ready(function () {
        var prefs = loadPrefs();
        if (Object.keys(prefs).length === 0 && window.matchMedia) {
            if (matchMedia('(prefers-contrast: more)').matches) {
                prefs.contrast = true;
            }
        }
        applyPrefs(prefs);
        ensureSkipLink();
        buildToolbar(prefs);
    });
})();
