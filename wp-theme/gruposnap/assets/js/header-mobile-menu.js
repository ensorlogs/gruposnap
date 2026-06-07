/*!
 * GrupoSnap · enriquece el menú móvil Printme (body > .mobile-menu) con logo y pie.
 */
(function ($) {
    'use strict';

    var ENHANCED_CLASS = 'gruposnap-mobile-menu';
    var TEMPLATE_ID = 'gruposnap-mobile-menu-template';

    function resolveLogoSrc($brand) {
        var $img = $brand.find('.gruposnap-mobile-menu__logo');
        if ($img.attr('src')) {
            return;
        }

        var fallback = $brand.attr('data-logo-fallback');
        if (fallback) {
            $img.attr('src', fallback);
            return;
        }

        var headerImg = document.querySelector(
            '.elementor-640 .elementor-element-b928f2e .wdt-logo-container img'
        );
        if (headerImg && headerImg.getAttribute('src')) {
            $img.attr('src', headerImg.getAttribute('src'));
            if (headerImg.getAttribute('srcset')) {
                $img.attr('srcset', headerImg.getAttribute('srcset'));
            }
        }
    }

    function closeMobileMenu() {
        var overlay = document.querySelector('.mobile-menu-overlay.is-visible');
        if (overlay) {
            overlay.click();
            return;
        }
        var closeNav = document.querySelector('body > .mobile-menu.gruposnap-mobile-menu li.close-nav');
        if (closeNav) {
            closeNav.click();
        }
    }

    function bindMobileMenuClose($menu) {
        $menu.find('.gruposnap-mobile-menu__close').off('click.gruposnap').on('click.gruposnap', function () {
            closeMobileMenu();
        });
    }

    function bindSwipeClose($menu) {
        var menuEl = $menu[0];
        if (!menuEl || menuEl.getAttribute('data-gruposnap-swipe') === '1') {
            return;
        }
        menuEl.setAttribute('data-gruposnap-swipe', '1');

        var startX = 0;
        var startY = 0;
        var tracking = false;
        var threshold = 72;

        function resetTransform() {
            menuEl.style.transition = 'transform 0.22s ease';
            menuEl.style.transform = '';
            window.setTimeout(function () {
                menuEl.style.transition = '';
            }, 240);
        }

        menuEl.addEventListener(
            'touchstart',
            function (event) {
                if (!event.touches || !event.touches.length) {
                    return;
                }
                startX = event.touches[0].clientX;
                startY = event.touches[0].clientY;
                tracking = true;
                menuEl.style.transition = '';
            },
            { passive: true }
        );

        menuEl.addEventListener(
            'touchmove',
            function (event) {
                if (!tracking || !event.touches.length) {
                    return;
                }
                var dx = event.touches[0].clientX - startX;
                var dy = event.touches[0].clientY - startY;
                if (Math.abs(dx) < Math.abs(dy)) {
                    return;
                }
                if (dx > 8) {
                    menuEl.style.transform = 'translateX(' + Math.min(dx, 140) + 'px)';
                }
            },
            { passive: true }
        );

        menuEl.addEventListener(
            'touchend',
            function (event) {
                if (!tracking || !event.changedTouches.length) {
                    return;
                }
                tracking = false;
                var dx = event.changedTouches[0].clientX - startX;
                if (dx > threshold) {
                    closeMobileMenu();
                    resetTransform();
                    return;
                }
                resetTransform();
            },
            { passive: true }
        );

        menuEl.addEventListener(
            'touchcancel',
            function () {
                tracking = false;
                resetTransform();
            },
            { passive: true }
        );
    }

    function bindMobileMenuFooter($menu) {
        $menu.find('.gruposnap-mobile-menu__a11y').off('click.gruposnap').on('click.gruposnap', function () {
            if (window.GrupoSnapA11y && typeof window.GrupoSnapA11y.toggle === 'function') {
                window.GrupoSnapA11y.toggle();
                return;
            }
            var fab = document.querySelector('.gsnap-a11y-fab');
            if (fab) {
                fab.click();
            }
        });
    }

    /**
     * Panel Printme en body al abrir MENU.
     *
     * @returns {jQuery}
     */
    function getPrintmeMobileMenu() {
        var menus = document.body.querySelectorAll(':scope > .mobile-menu');

        for (var i = 0; i < menus.length; i++) {
            var menu = menus[i];
            if (!menu.querySelector('ul.wdt-primary-nav')) {
                continue;
            }
            if (menu.querySelector('.gruposnap-mobile-menu__footer')) {
                continue;
            }
            return $(menu);
        }

        return $();
    }

    function mountLangSwitchLater() {
        if (!window.GrupoSnapLangSwitch || typeof window.GrupoSnapLangSwitch.mountMobile !== 'function') {
            return;
        }
        window.GrupoSnapLangSwitch.mountMobile();
        window.setTimeout(window.GrupoSnapLangSwitch.mountMobile, 60);
        window.setTimeout(window.GrupoSnapLangSwitch.mountMobile, 200);
    }

    function enhanceMobileMenu() {
        var $menu = getPrintmeMobileMenu();
        if (!$menu.length) {
            mountLangSwitchLater();
            return;
        }

        var template = document.getElementById(TEMPLATE_ID);
        if (!template) {
            return;
        }

        var brand = template.querySelector('.gruposnap-mobile-menu__brand');
        var footer = template.querySelector('.gruposnap-mobile-menu__footer');
        if (!brand || !footer) {
            return;
        }

        var $brand = $(brand.cloneNode(true));
        var $footer = $(footer.cloneNode(true));

        resolveLogoSrc($brand);

        $menu.addClass(ENHANCED_CLASS);
        $menu.prepend($brand);
        $menu.append($footer);

        bindMobileMenuFooter($menu);
        bindMobileMenuClose($menu);
        bindSwipeClose($menu);
        mountLangSwitchLater();
    }

    function scheduleEnhance() {
        window.setTimeout(enhanceMobileMenu, 0);
        window.setTimeout(enhanceMobileMenu, 50);
        window.setTimeout(enhanceMobileMenu, 150);
        window.setTimeout(enhanceMobileMenu, 350);
    }

    $(document).on('click', '.menu-trigger', scheduleEnhance);

    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                var nodes = mutations[i].addedNodes;
                for (var j = 0; j < nodes.length; j++) {
                    var node = nodes[j];
                    if (
                        node.nodeType === 1 &&
                        node.classList &&
                        node.classList.contains('mobile-menu')
                    ) {
                        scheduleEnhance();
                        return;
                    }
                }
            }

            if (document.body.classList.contains('nav-is-visible')) {
                scheduleEnhance();
            }
        });

        observer.observe(document.body, { childList: true, attributes: true, attributeFilter: ['class'] });
    }
})(jQuery);
