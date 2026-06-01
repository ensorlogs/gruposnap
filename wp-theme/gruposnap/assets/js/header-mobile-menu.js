/*!
 * GrupoSnap · enriquece el menú móvil Printme (body > .mobile-menu) con logo y contacto.
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

        var headerImg = document.querySelector(
            '.elementor-640 .elementor-element-b928f2e .wdt-logo-container img'
        );
        if (headerImg && headerImg.getAttribute('src')) {
            $img.attr('src', headerImg.getAttribute('src'));
            if (headerImg.getAttribute('srcset')) {
                $img.attr('srcset', headerImg.getAttribute('srcset'));
            }
            return;
        }

        var fallback = $brand.attr('data-logo-fallback');
        if (fallback) {
            $img.attr('src', fallback);
        }
    }

    /**
     * Solo el panel que Printme crea en body al abrir MENU (evita duplicar con el placeholder del header).
     *
     * @returns {jQuery}
     */
    function getPrintmeMobileMenu() {
        var menus = document.body.querySelectorAll(':scope > .mobile-menu');

        for (var i = 0; i < menus.length; i++) {
            var menu = menus[i];
            if (menu.classList.contains(ENHANCED_CLASS)) {
                continue;
            }
            if (menu.querySelector('ul.wdt-primary-nav')) {
                return $(menu);
            }
        }

        return $();
    }

    function enhanceMobileMenu() {
        var $menu = getPrintmeMobileMenu();
        if (!$menu.length) {
            return;
        }

        var $template = $('#' + TEMPLATE_ID);
        if (!$template.length) {
            return;
        }

        var $brand = $template.find('.gruposnap-mobile-menu__brand').first().clone();
        var $contact = $template.find('.gruposnap-mobile-menu__contact').first().clone();

        resolveLogoSrc($brand);

        $menu.addClass(ENHANCED_CLASS);
        $menu.prepend($brand);
        $menu.append($contact);
    }

    function scheduleEnhance() {
        window.setTimeout(enhanceMobileMenu, 0);
        window.setTimeout(enhanceMobileMenu, 80);
        window.setTimeout(enhanceMobileMenu, 220);
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
        });

        observer.observe(document.body, { childList: true });
    }
})(jQuery);
