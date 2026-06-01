<?php
/**
 * Home — servicios: estilos del grid portfolio y enlaces a #nosotros.
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Secciones Elementor del grid de servicios (portfolio). */
const GRUPOSNAP_HOME_SERVICES_PORTFOLIO_SECTION_IDS = array(
    'edfa8e2',
    '0f7d283',
    '1d1ab7d',
    '5ae8011',
);

/** Catálogo: desktop (1d1ab7d + 4d58045) vs móvil/tablet (5ae8011 + efbc9f6). */
const GRUPOSNAP_HOME_CATALOG_DESKTOP_SECTION_ID = '1d1ab7d';
const GRUPOSNAP_HOME_CATALOG_MOBILE_SECTION_ID  = '5ae8011';
const GRUPOSNAP_HOME_CATALOG_DESKTOP_HEADING_ID = '4d58045';
const GRUPOSNAP_HOME_CATALOG_MOBILE_HEADING_ID  = 'efbc9f6';

/**
 * Estilos compactos para tarjetas de servicios en móvil/tablet.
 */
function gruposnap_enqueue_home_services_styles(): void
{
    if (!function_exists('gruposnap_is_home_front') || !gruposnap_is_home_front()) {
        return;
    }

    $deps = array('gruposnap-child');

    if (wp_style_is('printme-layout', 'registered') || wp_style_is('printme-layout', 'enqueued')) {
        $deps[] = 'printme-layout';
    }

    if (wp_style_is('elementor-post-751', 'registered') || wp_style_is('elementor-post-751', 'enqueued')) {
        $deps[] = 'elementor-post-751';
    }

    wp_enqueue_style(
        'gruposnap-home-services',
        get_stylesheet_directory_uri() . '/assets/css/home-services.css',
        array_values(array_unique($deps)),
        GRUPOSNAP_THEME_VERSION
    );

    $catalog_sections = array(
        GRUPOSNAP_HOME_CATALOG_MOBILE_SECTION_ID,
        '0f7d283',
        'acaee6e',
    );

    $catalog_scope = array();
    foreach ($catalog_sections as $section_id) {
        $catalog_scope[] = '.elementor-751 .elementor-element-' . $section_id;
    }

    $catalog_scope = implode(', ', $catalog_scope);

    wp_add_inline_style(
        'gruposnap-home-services',
        '@media (max-width:767px){'
        . $catalog_scope . ' .elementor-widget.elementor-widget-wdt-image-box{'
        . 'width:100%!important;max-width:100%!important;--container-widget-width:100%!important;}'
        . $catalog_scope . ' .elementor-widget-wdt-image-box>.elementor-widget-container{'
        . 'width:100%!important;max-width:min(440px,92vw)!important;margin-left:auto!important;margin-right:auto!important;}'
        . $catalog_scope . ' .wdt-custom-portfolio-image-box .wdt-content-item,'
        . $catalog_scope . ' .wdt-custom-portfolio-image-box .wdt-image-box-holder{'
        . 'display:block!important;height:auto!important;min-height:0!important;}'
        . $catalog_scope . ' .wdt-custom-portfolio-image-box .wdt-content-detail-group{'
        . 'position:static!important;height:auto!important;min-height:0!important;padding:0!important;margin-top:.4rem!important;}'
        . $catalog_scope . ' .wdt-media-image-overlay>.wdt-media-image-overlay-container{display:none!important;height:0!important;}'
        . $catalog_scope . ' .wdt-content-image-wrapper .wdt-content-image,'
        . $catalog_scope . ' .wdt-content-image-wrapper .wdt-content-image>a,'
        . $catalog_scope . ' .wdt-content-image-wrapper .wdt-content-image>span{'
        . 'width:100%!important;min-width:100%!important;min-height:clamp(11.25rem,54vw,15.5rem)!important;}'
        . '.elementor-751 .elementor-element-acaee6e .wdt-column-gap-custom{'
        . 'display:flex!important;flex-direction:column!important;margin:0!important;padding:0!important;gap:.7rem!important;}'
        . '.elementor-751 .elementor-element-acaee6e .wdt-column-gap-custom .wdt-column{'
        . 'width:100%!important;max-width:100%!important;padding:0!important;}'
        . '}'
    );
}

/**
 * Ancla de las tarjetas de servicios.
 */
function gruposnap_home_services_nosotros_hash(): string
{
    return (string) apply_filters('gruposnap_home_services_nosotros_hash', '#nosotros');
}

/**
 * Selectores JS de tarjetas que deben ir a #nosotros.
 *
 * @return string[]
 */
function gruposnap_home_services_nosotros_card_selectors(): array
{
    $selectors = array(
        '#servicios .wdt-custom-home-image-box .wdt-content-item',
        '#servicios .wdt-custom-portfolio-image-box .wdt-content-item',
        '.elementor-element-693ab46 .wdt-custom-home-image-box .wdt-content-item',
        '.elementor-element-693ab46 .wdt-custom-portfolio-image-box .wdt-content-item',
        '.elementor-element-acaee6e .wdt-custom-home-image-box .wdt-content-item',
        '.elementor-element-acaee6e .wdt-custom-portfolio-image-box .wdt-content-item',
    );

    foreach (GRUPOSNAP_HOME_SERVICES_PORTFOLIO_SECTION_IDS as $section_id) {
        $selectors[] = '.elementor-element-' . $section_id . ' .wdt-custom-portfolio-image-box .wdt-content-item';
    }

    return array_values(array_unique($selectors));
}

/**
 * Tarjetas de servicios → enlace / scroll a #nosotros.
 */
function gruposnap_home_services_link_to_nosotros_script(): void
{
    if (!function_exists('gruposnap_is_home_front') || !gruposnap_is_home_front()) {
        return;
    }

    $hash = esc_js(gruposnap_home_services_nosotros_hash());
    $card_selector = implode(',', gruposnap_home_services_nosotros_card_selectors());
    ?>
    <script id="gruposnap-home-services-link-nosotros">
    (function () {
        var hash = '<?php echo $hash; ?>';
        var cardSelector = <?php echo wp_json_encode($card_selector); ?>;

        function getNosotrosTarget() {
            return (
                document.getElementById('nosotros') ||
                document.querySelector('section#nosotros') ||
                document.querySelector('.elementor-element-87ef98c') ||
                document.querySelector('.elementor-element-46cb44b')
            );
        }

        function scrollToNosotros() {
            var target = getNosotrosTarget();
            if (!target) {
                return;
            }

            var header = document.getElementById('header-wrapper') || document.querySelector('.header-wrapper');
            var offset = header ? header.offsetHeight + 16 : 96;
            var top = target.getBoundingClientRect().top + window.pageYOffset - offset;

            window.scrollTo({
                top: Math.max(0, top),
                behavior: 'smooth'
            });
        }

        function bindServiceCards() {
            document.querySelectorAll(cardSelector).forEach(function (card) {
                if (card.dataset.gruposnapLinkNosotros === '1') {
                    return;
                }

                card.dataset.gruposnapLinkNosotros = '1';
                card.classList.add('gruposnap-service-card--link-nosotros');

                var holder = card.closest('.wdt-image-box-holder');
                if (holder) {
                    holder.classList.remove('wdt-image-lightbox-popup');
                }

                var links = card.querySelectorAll('a[href]');
                if (links.length) {
                    links.forEach(function (link) {
                        link.setAttribute('href', hash);
                        link.removeAttribute('target');
                        link.removeAttribute('rel');
                    });
                } else {
                    card.setAttribute('role', 'link');
                    card.setAttribute('tabindex', '0');
                    card.setAttribute('aria-label', <?php echo wp_json_encode(__('Ir a Nosotros', 'gruposnap')); ?>);
                }

                card.addEventListener('click', function (event) {
                    var link = event.target.closest('a[href]');
                    if (link && link.getAttribute('href') && link.getAttribute('href').indexOf('#') !== 0) {
                        return;
                    }

                    event.preventDefault();
                    if (window.history && window.history.pushState) {
                        window.history.pushState(null, '', hash);
                    }
                    scrollToNosotros();
                });

                card.addEventListener('keydown', function (event) {
                    if (event.key !== 'Enter' && event.key !== ' ') {
                        return;
                    }

                    event.preventDefault();
                    scrollToNosotros();
                });
            });
        }

        function init() {
            bindServiceCards();
        }

        init();
        document.addEventListener('DOMContentLoaded', init);
        window.addEventListener('load', init);
        [200, 600, 1200].forEach(function (delay) {
            window.setTimeout(init, delay);
        });

        if (window.jQuery) {
            window.jQuery(window).on('elementor/frontend/init', function () {
                window.setTimeout(init, 150);
            });
        }
    })();
    </script>
    <?php
}

add_action('wp_enqueue_scripts', 'gruposnap_enqueue_home_services_styles', 999);
add_action('wp_footer', 'gruposnap_home_services_link_to_nosotros_script', 14);
