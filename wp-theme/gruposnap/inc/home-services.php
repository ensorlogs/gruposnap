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
    'fcb5fb4', /* Merchandising promocional */
    '303fa43', /* Branding de espacios */
    '0a88f17', /* Desarrollo web / APPS */
    'e65dc9f', /* Uniformes y textil */
);

/** Tarjetas ocultas al reducir el catálogo a 5 (Productos que marcan, Catálogos y brochures). */
const GRUPOSNAP_HOME_CATALOG_HIDDEN_WIDGET_IDS = array(
    '6d42155',
    '07d5a30',
);

/** Widget «Branding de espacios» — sin subtítulo «Personalización 100%». */
const GRUPOSNAP_HOME_CATALOG_BRANDING_WIDGET_ID = '303fa43';

/** Widget catálogo (antes «Material corporativo» → Merchandising promocional). */
const GRUPOSNAP_HOME_CATALOG_MERCH_WIDGET_ID = 'fcb5fb4';

/** Widget catálogo (antes «Activación de eventos» → Desarrollo web / APPS). */
const GRUPOSNAP_HOME_CATALOG_EVENTS_WIDGET_ID = '0a88f17';

/**
 * Título visible de la tarjeta «Branding espacios».
 */
function gruposnap_home_catalog_branding_card_title(): string
{
    return (string) apply_filters(
        'gruposnap_home_catalog_branding_card_title',
        'Branding Espacios'
    );
}

/**
 * Imagen de la tarjeta «Branding espacios».
 */
function gruposnap_home_catalog_branding_card_image_url(): string
{
    $theme_path = get_stylesheet_directory() . '/assets/images/services/branding-espacios.jpg';
    $theme_uri  = get_stylesheet_directory_uri() . '/assets/images/services/branding-espacios.jpg';

    return (string) apply_filters(
        'gruposnap_home_catalog_branding_card_image_url',
        file_exists($theme_path) ? $theme_uri : $theme_uri
    );
}

/**
 * Título visible de la tarjeta «Merchandising promocional» (widget fcb5fb4).
 */
function gruposnap_home_catalog_merch_card_title(): string
{
    return (string) apply_filters(
        'gruposnap_home_catalog_merch_card_title',
        'Merchandising Promocional'
    );
}

/**
 * Imagen de la tarjeta «Merchandising promocional».
 */
function gruposnap_home_catalog_merch_card_image_url(): string
{
    $theme_path = get_stylesheet_directory() . '/assets/images/services/merchandising-promocional.jpg';
    $theme_uri  = get_stylesheet_directory_uri() . '/assets/images/services/merchandising-promocional.jpg';

    return (string) apply_filters(
        'gruposnap_home_catalog_merch_card_image_url',
        file_exists($theme_path) ? $theme_uri : $theme_uri
    );
}

/**
 * Título visible de la tarjeta «Desarrollo web / APPS» (widget 0a88f17).
 */
function gruposnap_home_catalog_events_card_title(): string
{
    return (string) apply_filters(
        'gruposnap_home_catalog_events_card_title',
        'Desarrollo web / APPS'
    );
}

/**
 * Imagen de la tarjeta «Desarrollo web / APPS».
 */
function gruposnap_home_catalog_events_card_image_url(): string
{
    $theme_path = get_stylesheet_directory() . '/assets/images/services/desarrollo-web-apps.jpg';
    $theme_uri  = get_stylesheet_directory_uri() . '/assets/images/services/desarrollo-web-apps.jpg';

    return (string) apply_filters(
        'gruposnap_home_catalog_events_card_image_url',
        file_exists($theme_path) ? $theme_uri : $theme_uri
    );
}

/**
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_home_catalog_card_titles_content(string $content, $widget): string
{
    if (!gruposnap_should_patch_home_services_grid()) {
        return $content;
    }

    $widget_id = $widget->get_id();

    if ($widget_id === GRUPOSNAP_HOME_CATALOG_BRANDING_WIDGET_ID) {
        $title = gruposnap_home_catalog_branding_card_title();
        $image = gruposnap_home_catalog_branding_card_image_url();
        $alt   = esc_attr__('Branding de espacios corporativos', 'gruposnap');

        $content = (string) preg_replace(
            '/Branding\s+de\s+espacios/iu',
            $title,
            $content
        );

        if (preg_match('/<img\b/i', $content)) {
            $content = (string) preg_replace(
                '/(<img\b[^>]*\bsrc=")([^"]+)(")/i',
                '$1' . esc_url($image) . '$3',
                $content,
                1
            );
            $content = (string) preg_replace('/\bsrcset="[^"]*"/i', '', $content);
            $content = (string) preg_replace('/\bsizes="[^"]*"/i', '', $content);

            if (preg_match('/<img\b[^>]*\balt="/i', $content)) {
                $content = (string) preg_replace(
                    '/(<img\b[^>]*\bsrc="' . preg_quote(esc_url($image), '/') . '"[^>]*?)alt="[^"]*"/i',
                    '$1alt="' . $alt . '"',
                    $content,
                    1
                );
            } else {
                $content = (string) preg_replace(
                    '/(<img\b[^>]*\bsrc="' . preg_quote(esc_url($image), '/') . '")/i',
                    '$1 alt="' . $alt . '"',
                    $content,
                    1
                );
            }
        }

        return $content;
    }

    if ($widget_id === GRUPOSNAP_HOME_CATALOG_MERCH_WIDGET_ID) {
        $title = gruposnap_home_catalog_merch_card_title();
        $image = gruposnap_home_catalog_merch_card_image_url();
        $alt   = esc_attr__('Merchandising promocional', 'gruposnap');

        $content = (string) preg_replace(
            '/Material\s+corporativo/iu',
            $title,
            $content
        );

        if (preg_match('/<img\b/i', $content)) {
            $content = (string) preg_replace(
                '/(<img\b[^>]*\bsrc=")([^"]+)(")/i',
                '$1' . esc_url($image) . '$3',
                $content,
                1
            );
            $content = (string) preg_replace('/\bsrcset="[^"]*"/i', '', $content);
            $content = (string) preg_replace('/\bsizes="[^"]*"/i', '', $content);

            if (preg_match('/<img\b[^>]*\balt="/i', $content)) {
                $content = (string) preg_replace(
                    '/(<img\b[^>]*\bsrc="' . preg_quote(esc_url($image), '/') . '"[^>]*?)alt="[^"]*"/i',
                    '$1alt="' . $alt . '"',
                    $content,
                    1
                );
            } else {
                $content = (string) preg_replace(
                    '/(<img\b[^>]*\bsrc="' . preg_quote(esc_url($image), '/') . '")/i',
                    '$1 alt="' . $alt . '"',
                    $content,
                    1
                );
            }
        }

        return $content;
    }

    if ($widget_id === GRUPOSNAP_HOME_CATALOG_EVENTS_WIDGET_ID) {
        $title = gruposnap_home_catalog_events_card_title();
        $image = gruposnap_home_catalog_events_card_image_url();
        $alt   = esc_attr__('Desarrollo web y aplicaciones', 'gruposnap');

        $content = (string) preg_replace(
            '/Activaci[oó]n(\s+de\s+eventos|\s+eventos|Eventos)?/iu',
            $title,
            $content
        );

        if (preg_match('/<img\b/i', $content)) {
            $content = (string) preg_replace(
                '/(<img\b[^>]*\bsrc=")([^"]+)(")/i',
                '$1' . esc_url($image) . '$3',
                $content,
                1
            );
            $content = (string) preg_replace('/\bsrcset="[^"]*"/i', '', $content);
            $content = (string) preg_replace('/\bsizes="[^"]*"/i', '', $content);

            if (preg_match('/<img\b[^>]*\balt="/i', $content)) {
                $content = (string) preg_replace(
                    '/(<img\b[^>]*\bsrc="' . preg_quote(esc_url($image), '/') . '"[^>]*?)alt="[^"]*"/i',
                    '$1alt="' . $alt . '"',
                    $content,
                    1
                );
            } else {
                $content = (string) preg_replace(
                    '/(<img\b[^>]*\bsrc="' . preg_quote(esc_url($image), '/') . '")/i',
                    '$1 alt="' . $alt . '"',
                    $content,
                    1
                );
            }
        }

        return $content;
    }

    return $content;
}

/** Catálogo: bloque Elementor 1d1ab7d (4d58045 + edfa8e2). IDs 5ae8011/0f7d283 = legacy. */
const GRUPOSNAP_HOME_CATALOG_DESKTOP_SECTION_ID = '1d1ab7d';
const GRUPOSNAP_HOME_CATALOG_MOBILE_SECTION_ID  = '5ae8011';
const GRUPOSNAP_HOME_CATALOG_DESKTOP_HEADING_ID = '4d58045';
const GRUPOSNAP_HOME_CATALOG_MOBILE_HEADING_ID  = 'efbc9f6';

/** Sección «Soluciones para tu marca» (#servicios) — solo escritorio/tablet. */
const GRUPOSNAP_HOME_SERVICES_SECTION_ID = '693ab46';

/** Legacy móvil (si existe en datos Elementor). */
const GRUPOSNAP_HOME_SERVICES_MOBILE_SECTION_ID = 'acaee6e';

/** Versión del parche hide_mobile en la sección servicios. */
const GRUPOSNAP_HOME_SERVICES_FIX_VERSION = '2';

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
 * Oculta #servicios (693ab46) en móvil vía datos Elementor (hide_mobile).
 */
function gruposnap_home_services_fix_elementor_data(): void
{
    if (get_option('gruposnap_home_services_fix_version') === GRUPOSNAP_HOME_SERVICES_FIX_VERSION) {
        return;
    }

    $raw = get_post_meta(751, '_elementor_data', true);
    if (!is_string($raw) || $raw === '') {
        return;
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return;
    }

    $needs_save = false;

    $legacy_top_ids = array(
        GRUPOSNAP_HOME_CATALOG_MOBILE_SECTION_ID,
        '0f7d283',
    );

    $before = count($data);
    $data   = array_values(
        array_filter(
            $data,
            static function ($node) use ($legacy_top_ids): bool {
                return is_array($node) && !in_array($node['id'] ?? '', $legacy_top_ids, true);
            }
        )
    );
    if (count($data) !== $before) {
        $needs_save = true;
    }

    $walk = static function (array &$nodes) use (&$walk, &$needs_save, $legacy_top_ids): void {
        foreach ($nodes as $key => &$node) {
            if (!is_array($node)) {
                continue;
            }

            $id       = $node['id'] ?? '';
            $settings = &$node['settings'];

            if (in_array($id, $legacy_top_ids, true)) {
                unset($nodes[$key]);
                $needs_save = true;
                continue;
            }

            if (
                $id === GRUPOSNAP_HOME_SERVICES_SECTION_ID
                || $id === GRUPOSNAP_HOME_SERVICES_MOBILE_SECTION_ID
            ) {
                if (($settings['hide_mobile'] ?? '') !== 'hidden-mobile') {
                    $settings['hide_mobile'] = 'hidden-mobile';
                    $needs_save              = true;
                }
            }

            if (!empty($node['elements']) && is_array($node['elements'])) {
                $walk($node['elements']);
                $node['elements'] = array_values($node['elements']);
            }
        }

        $nodes = array_values($nodes);
    };

    $walk($data);

    if (!$needs_save) {
        update_option('gruposnap_home_services_fix_version', GRUPOSNAP_HOME_SERVICES_FIX_VERSION, false);

        return;
    }

    update_post_meta(751, '_elementor_data', wp_slash(wp_json_encode($data)));
    delete_post_meta(751, '_elementor_element_cache');
    delete_post_meta(751, '_elementor_css');
    update_option('gruposnap_home_services_fix_version', GRUPOSNAP_HOME_SERVICES_FIX_VERSION, false);
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
        . 'width:100%!important;max-width:100%!important;margin-left:auto!important;margin-right:auto!important;}'
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
    $branding_title = gruposnap_home_catalog_branding_card_title();
    $events_title   = gruposnap_home_catalog_events_card_title();
    $events_image   = esc_url(gruposnap_home_catalog_events_card_image_url());
    $events_alt     = esc_attr__('Desarrollo web y aplicaciones', 'gruposnap');
    ?>
    <script id="gruposnap-home-services-link-nosotros">
    (function () {
        var hash = '<?php echo $hash; ?>';
        var cardSelector = <?php echo wp_json_encode($card_selector); ?>;
        var brandingTitle = <?php echo wp_json_encode($branding_title); ?>;
        var eventsTitle = <?php echo wp_json_encode($events_title); ?>;
        var eventsImage = <?php echo wp_json_encode($events_image); ?>;
        var eventsAlt = <?php echo wp_json_encode($events_alt); ?>;

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

        function patchCatalogCardTitles() {
            document.querySelectorAll('.elementor-element-303fa43 .wdt-content-title h5 a, .elementor-element-303fa43 .wdt-content-title h5').forEach(function (el) {
                if (/branding\s+de\s+espacios/i.test(el.textContent)) {
                    el.textContent = brandingTitle;
                }
            });

            document.querySelectorAll('.elementor-element-0a88f17 .wdt-content-title h5 a, .elementor-element-0a88f17 .wdt-content-title h5').forEach(function (el) {
                if (/activaci[oó]n(\s+de\s+eventos|\s+eventos)?/i.test(el.textContent)) {
                    el.textContent = eventsTitle;
                }
            });

            document.querySelectorAll('.elementor-element-0a88f17 img').forEach(function (img) {
                if (!eventsImage || img.getAttribute('src') === eventsImage) {
                    return;
                }

                img.setAttribute('src', eventsImage);
                img.removeAttribute('srcset');
                img.removeAttribute('sizes');
                img.setAttribute('alt', eventsAlt);
            });
        }

        function init() {
            patchCatalogCardTitles();
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

add_action('init', 'gruposnap_home_services_fix_elementor_data', 5);
add_action('init', 'gruposnap_home_services_bust_elementor_cache');
add_filter('elementor/widget/render_content', 'gruposnap_home_catalog_card_titles_content', 13, 2);
add_filter('elementor/widget/render_content', 'gruposnap_home_services_replace_merchandising_card', 14, 2);
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_home_services_styles', 999);
add_action('wp_footer', 'gruposnap_home_services_web_apps_card_fallback_script', 13);
add_action('wp_footer', 'gruposnap_home_services_link_to_nosotros_script', 14);
