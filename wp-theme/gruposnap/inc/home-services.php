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

/** Catálogo edfa8e2 — 5 tarjetas verticales tipo story (escritorio). */
const GRUPOSNAP_HOME_CATALOG_STORY_WIDGET_IDS = array(
    'f6d4d5a', /* Pendones y posters */
    'fcb5fb4', /* Material corporativo */
    '303fa43', /* Branding de espacios */
    '0a88f17', /* Activación de eventos */
    'e65dc9f', /* Uniformes y textil */
);

/** Tarjetas ocultas al reducir el catálogo a 5 (Productos que marcan, Catálogos y brochures). */
const GRUPOSNAP_HOME_CATALOG_HIDDEN_WIDGET_IDS = array(
    '6d42155',
    '07d5a30',
);

/** Widget «Branding de espacios» — sin subtítulo «Personalización 100%». */
const GRUPOSNAP_HOME_CATALOG_BRANDING_WIDGET_ID = '303fa43';

/** Catálogo: bloque Elementor 1d1ab7d (4d58045 + edfa8e2). IDs 5ae8011/0f7d283 = legacy. */
const GRUPOSNAP_HOME_CATALOG_DESKTOP_SECTION_ID = '1d1ab7d';
const GRUPOSNAP_HOME_CATALOG_MOBILE_SECTION_ID  = '5ae8011';
const GRUPOSNAP_HOME_CATALOG_DESKTOP_HEADING_ID = '4d58045';
const GRUPOSNAP_HOME_CATALOG_MOBILE_HEADING_ID  = 'efbc9f6';

/** Grid «Soluciones para tu marca» (sección #servicios / 693ab46). */
const GRUPOSNAP_HOME_SERVICES_GRID_WIDGET_ID = '9a378ea';

/** Imagen legacy de la tarjeta que antes decía Merchandising. */
const GRUPOSNAP_HOME_SERVICES_LEGACY_MERCH_IMAGE_FRAGMENT = 'naomi-august-ZQPekfTkImw-unsplash1';

/**
 * @return bool
 */
function gruposnap_should_patch_home_services_grid(): bool
{
    if (is_admin()) {
        return false;
    }

    if (function_exists('gruposnap_is_home_front')) {
        return gruposnap_is_home_front();
    }

    $page_id = (int) (get_queried_object_id() ?: get_the_ID());

    return $page_id === 751 || (is_front_page() && (int) get_option('page_on_front') === 751);
}

/**
 * Título de la tarjeta de desarrollo web (sustituye Merchandising).
 */
function gruposnap_home_services_web_apps_card_title(): string
{
    return (string) apply_filters(
        'gruposnap_home_services_web_apps_card_title',
        'Desarrollo Web/APPS y Tiendas Online'
    );
}

/**
 * Imagen de la tarjeta de desarrollo web.
 */
function gruposnap_home_services_web_apps_card_image_url(): string
{
    $upload = content_url('uploads/2026/06/daniel-korpai-pKRNxEguRgM-unsplash-desarrollo-web.jpg');
    $theme  = get_stylesheet_directory_uri() . '/assets/images/services/desarrollo-web-apps-tiendas-online.jpg';

    return (string) apply_filters(
        'gruposnap_home_services_web_apps_card_image_url',
        file_exists(WP_CONTENT_DIR . '/uploads/2026/06/daniel-korpai-pKRNxEguRgM-unsplash-desarrollo-web.jpg')
            ? $upload
            : $theme
    );
}

/**
 * Sustituye la tarjeta Merchandising por Desarrollo Web/APPS y Tiendas Online.
 *
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_home_services_replace_merchandising_card(string $content, $widget): string
{
    if (
        !gruposnap_should_patch_home_services_grid()
        || $widget->get_id() !== GRUPOSNAP_HOME_SERVICES_GRID_WIDGET_ID
        || $widget->get_name() !== 'wdt-image-box'
    ) {
        return $content;
    }

    $title      = gruposnap_home_services_web_apps_card_title();
    $image      = gruposnap_home_services_web_apps_card_image_url();
    $legacy_img = GRUPOSNAP_HOME_SERVICES_LEGACY_MERCH_IMAGE_FRAGMENT;

    if (
        !str_contains($content, 'Merchandising')
        && !str_contains($content, $legacy_img)
        && !str_contains($content, $title)
    ) {
        return $content;
    }

    if (!str_contains($content, $title)) {
        $replaced = preg_replace(
            '/(<div class="wdt-content-title"><h5><a[^>]*>)Merchandising(<\/a><\/h5><\/div>)/i',
            '$1' . esc_html($title) . '$2',
            $content,
            1
        );
        $content = is_string($replaced) ? $replaced : $content;
    }

    if (str_contains($content, $legacy_img)) {
        $replaced = preg_replace(
            '/src="[^"]*' . preg_quote($legacy_img, '/') . '[^"]*"/',
            'src="' . esc_url($image) . '"',
            $content,
            1
        );
        $content = is_string($replaced) ? $replaced : $content;

        $replaced = preg_replace(
            '/srcset="[^"]*' . preg_quote($legacy_img, '/') . '[^"]*"/',
            '',
            $content,
            1
        );
        $content = is_string($replaced) ? $replaced : $content;

        $replaced = preg_replace(
            '/sizes="[^"]*' . preg_quote($legacy_img, '/') . '[^"]*"/',
            '',
            $content,
            1
        );
        $content = is_string($replaced) ? $replaced : $content;

        $alt = esc_attr__('Desarrollo Web, apps y tiendas online', 'gruposnap');
        $replaced = preg_replace(
            '/(<img[^>]*src="' . preg_quote(esc_url($image), '/') . '"[^>]*?)alt="[^"]*"/',
            '$1alt="' . $alt . '"',
            $content,
            1
        );
        if (is_string($replaced) && $replaced !== $content) {
            $content = $replaced;
        } else {
            $replaced = preg_replace(
                '/(<img[^>]*src="' . preg_quote(esc_url($image), '/') . '")/',
                '$1 alt="' . $alt . '"',
                $content,
                1
            );
            $content = is_string($replaced) ? $replaced : $content;
        }
    }

    return $content;
}

function gruposnap_home_services_web_apps_card_fallback_script(): void
{
    if (!gruposnap_should_patch_home_services_grid()) {
        return;
    }

    $widget_id  = GRUPOSNAP_HOME_SERVICES_GRID_WIDGET_ID;
    $title      = gruposnap_home_services_web_apps_card_title();
    $image      = esc_url(gruposnap_home_services_web_apps_card_image_url());
    $legacy_img = GRUPOSNAP_HOME_SERVICES_LEGACY_MERCH_IMAGE_FRAGMENT;
    ?>
    <script id="gruposnap-home-services-web-apps-card">
    (function () {
        var widgetSel = '.elementor-element-<?php echo esc_js($widget_id); ?>';
        var title = <?php echo wp_json_encode($title); ?>;
        var image = <?php echo wp_json_encode($image); ?>;
        var legacy = <?php echo wp_json_encode($legacy_img); ?>;

        function patchCard() {
            var root = document.querySelector(widgetSel);
            if (!root) {
                return;
            }

            root.querySelectorAll('.wdt-content-title h5 a, .wdt-content-title h5').forEach(function (node) {
                if (node.textContent.trim().toLowerCase() === 'merchandising') {
                    node.textContent = title;
                }
            });

            root.querySelectorAll('img[src*="' + legacy + '"]').forEach(function (img) {
                img.src = image;
                img.removeAttribute('srcset');
                img.removeAttribute('sizes');
                img.alt = <?php echo wp_json_encode(__('Desarrollo Web, apps y tiendas online', 'gruposnap')); ?>;
                var item = img.closest('.wdt-content-item');
                if (item) {
                    item.classList.add('gruposnap-service-card--web-apps');
                }
            });

            root.querySelectorAll('.wdt-content-title h5 a, .wdt-content-title h5').forEach(function (node) {
                if (node.textContent.trim() === title) {
                    var item = node.closest('.wdt-content-item');
                    if (item) {
                        item.classList.add('gruposnap-service-card--web-apps');
                    }
                }
            });
        }

        patchCard();
        document.addEventListener('DOMContentLoaded', patchCard);
        window.addEventListener('load', patchCard);
        [200, 700, 1400].forEach(function (ms) {
            window.setTimeout(patchCard, ms);
        });
    })();
    </script>
    <?php
}

function gruposnap_home_services_bust_elementor_cache(): void
{
    $version = get_option('gruposnap_home_services_cache_version');
    if ($version === GRUPOSNAP_THEME_VERSION) {
        return;
    }

    delete_post_meta(751, '_elementor_element_cache');
    update_option('gruposnap_home_services_cache_version', GRUPOSNAP_THEME_VERSION, false);
}

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
        GRUPOSNAP_HOME_CATALOG_DESKTOP_SECTION_ID,
        'edfa8e2',
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
        . 'width:100%!important;min-width:0!important;min-height:clamp(11.25rem,54vw,15.5rem)!important;}'
        . '.elementor-751 .wdt-custom-home-image-box .wdt-column:nth-child(even) .wdt-content-item{'
        . 'flex-direction:column!important;margin-top:0!important;}'
        . '.elementor-751 .wdt-custom-home-image-box .wdt-column:nth-child(odd) .wdt-content-item .wdt-content-media-group,'
        . '.elementor-751 .wdt-custom-home-image-box .wdt-column:nth-child(even) .wdt-content-item .wdt-content-media-group{'
        . 'margin-top:0!important;margin-bottom:.45rem!important;}'
        . '.elementor-751 .elementor-element-acaee6e .wdt-column-gap-custom{'
        . 'display:flex!important;flex-direction:column!important;margin:0!important;padding:0!important;gap:1rem!important;}'
        . '.elementor-751 .elementor-element-acaee6e .wdt-column-gap-custom .wdt-column{'
        . 'width:100%!important;max-width:100%!important;padding:0!important;margin:0!important;}'
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

add_action('init', 'gruposnap_home_services_bust_elementor_cache');
add_filter('elementor/widget/render_content', 'gruposnap_home_services_replace_merchandising_card', 14, 2);
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_home_services_styles', 999);
add_action('wp_footer', 'gruposnap_home_services_web_apps_card_fallback_script', 13);
add_action('wp_footer', 'gruposnap_home_services_link_to_nosotros_script', 14);
