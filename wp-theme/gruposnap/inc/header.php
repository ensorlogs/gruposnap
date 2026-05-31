<?php
/**
 * GrupoSnap — cabecera (menú centrado, CTA catálogo, móvil).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * URL del catálogo 2026 (Flipsnack u otra landing).
 */
function gruposnap_catalog_url(): string
{
    $default = 'https://www.flipsnack.com/gruposnap/cat-logo-grupo-snap/full-view.html';

    return (string) apply_filters('gruposnap_catalog_url', $default);
}

/**
 * Sustituye el buscador del header por un CTA al catálogo.
 *
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_header_catalog_cta_replace_search(string $content, $widget): string
{
    if (is_admin() || $widget->get_name() !== 'wdt-header-icons' || $widget->get_id() !== 'a5a5440') {
        return $content;
    }

    $url = esc_url(gruposnap_catalog_url());

    ob_start();
    ?>
    <div class="elementor-widget-container">
        <div class="gruposnap-header-catalog-wrap">
            <a class="gruposnap-header-catalog-cta" href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                <span class="gruposnap-header-catalog-cta__icon" aria-hidden="true">
                    <svg viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg" focusable="false">
                        <path d="M542.22 32.05c-54.8 3.11-163.72 14.43-230.96 55.59-4.64 2.84-7.27 7.89-7.27 13.17v363.87c0 11.55 12.63 18.85 23.28 13.49 69.18-34.82 169.23-44.32 218.7-46.92 16.89-.89 30.02-14.43 30.02-30.66V62.75c.01-17.71-15.35-31.74-33.77-30.7zM264.73 87.64C197.5 46.48 88.58 35.17 33.78 32.05 15.36 31.01 0 45.04 0 62.75V400.6c0 16.24 13.13 29.78 30.02 30.66 49.49 2.6 149.59 12.11 218.77 46.95 10.62 5.35 23.21-1.94 23.21-13.46V100.63c0-5.29-2.62-10.14-7.27-12.99z"/>
                    </svg>
                </span>
                <span class="gruposnap-header-catalog-cta__text"><?php esc_html_e('Catálogo 2026', 'gruposnap'); ?></span>
            </a>
        </div>
    </div>
    <?php
    return (string) ob_get_clean();
}

function gruposnap_enqueue_header_styles(): void
{
    if (is_admin()) {
        return;
    }

    wp_enqueue_style(
        'gruposnap-header-desktop',
        get_stylesheet_directory_uri() . '/assets/css/header-desktop.css',
        array('gruposnap-child', 'printme-header'),
        GRUPOSNAP_THEME_VERSION
    );

    wp_enqueue_style(
        'gruposnap-header-mobile',
        get_stylesheet_directory_uri() . '/assets/css/header-mobile.css',
        array('gruposnap-header-desktop'),
        GRUPOSNAP_THEME_VERSION
    );
}

add_action('wp_enqueue_scripts', 'gruposnap_enqueue_header_styles', 120);
add_filter('elementor/widget/render_content', 'gruposnap_header_catalog_cta_replace_search', 12, 2);

/**
 * Etiqueta MENU en el disparador móvil (el HTML trae "Menu").
 */
function gruposnap_header_menu_label_script(): void
{
    if (is_admin()) {
        return;
    }
    ?>
    <script>
    (function () {
        function labelMenuTriggers() {
            document.querySelectorAll('.elementor-640 .menu-trigger > span').forEach(function (el) {
                if (el.textContent.trim().toLowerCase() === 'menu') {
                    el.textContent = 'MENU';
                }
            });
        }
        if (document.readyState !== 'loading') {
            labelMenuTriggers();
        } else {
            document.addEventListener('DOMContentLoaded', labelMenuTriggers);
        }
    })();
    </script>
    <?php
}

add_action('wp_footer', 'gruposnap_header_menu_label_script', 5);
