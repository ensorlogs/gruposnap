<?php
/**
 * GrupoSnap — carga de estilos CTA (sin recolorear el tema completo).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once get_stylesheet_directory() . '/inc/i18n.php';
require_once get_stylesheet_directory() . '/inc/legal-i18n.php';
require_once get_stylesheet_directory() . '/inc/content-i18n.php';
require_once get_stylesheet_directory() . '/inc/loader.php';
require_once get_stylesheet_directory() . '/inc/cta.php';
require_once get_stylesheet_directory() . '/inc/home-hero.php';
require_once get_stylesheet_directory() . '/inc/home-brands-strip.php';
require_once get_stylesheet_directory() . '/inc/testimonials.php';
require_once get_stylesheet_directory() . '/inc/blog.php';
require_once get_stylesheet_directory() . '/inc/blog-rating.php';
require_once get_stylesheet_directory() . '/inc/compliance.php';
require_once get_stylesheet_directory() . '/inc/header.php';
require_once get_stylesheet_directory() . '/inc/header-mobile-menu.php';
require_once get_stylesheet_directory() . '/inc/footer-payments.php';
require_once get_stylesheet_directory() . '/inc/footer-mobile.php';
require_once get_stylesheet_directory() . '/inc/footer-compact.php';
require_once get_stylesheet_directory() . '/inc/footer-offices.php'; /* después de footer-compact (constantes columnas) */
require_once get_stylesheet_directory() . '/inc/instagram.php';
require_once get_stylesheet_directory() . '/inc/home-services.php';
require_once get_stylesheet_directory() . '/inc/home-headings.php';
require_once get_stylesheet_directory() . '/inc/home-about.php';
require_once get_stylesheet_directory() . '/inc/home-responsive.php';
require_once get_stylesheet_directory() . '/inc/custom-cursor.php';

/**
 * CTA styles must load after WDT button + Elementor page CSS (accent #61CE70).
 */
function gruposnap_enqueue_cta_styles(): void
{
    $deps = array('gruposnap-child');

    foreach (array('wdt-button-css', 'elementor-frontend') as $handle) {
        if (wp_style_is($handle, 'registered') || wp_style_is($handle, 'enqueued')) {
            $deps[] = $handle;
        }
    }

    wp_enqueue_style(
        'gruposnap-cta',
        get_stylesheet_directory_uri() . '/assets/css/cta.css',
        $deps,
        GRUPOSNAP_THEME_VERSION
    );
}

/**
 * Tras renderizar el primer botón WDT (ahí se encola wdt-button-css).
 *
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_cta_enqueue_after_wdt_button($widget): void
{
    if ($widget->get_name() !== 'wdt-button') {
        return;
    }

    static $done = false;
    if ($done) {
        return;
    }

    $done = true;
    gruposnap_enqueue_cta_styles();
}

add_action('elementor/frontend/widget/after_render', 'gruposnap_cta_enqueue_after_wdt_button', 15);

add_action(
    'wp_print_styles',
    static function (): void {
        if (!wp_style_is('gruposnap-cta', 'enqueued') && wp_style_is('wdt-button-css', 'enqueued')) {
            gruposnap_enqueue_cta_styles();
        }
    },
    999
);

add_action(
    'wp_enqueue_scripts',
    static function (): void {
        if (!class_exists('\Elementor\Plugin')) {
            gruposnap_enqueue_cta_styles();
        }
    },
    9999
);

add_action(
    'elementor/editor/after_enqueue_styles',
    static function (): void {
        wp_enqueue_style(
            'gruposnap-cta',
            get_stylesheet_directory_uri() . '/assets/css/cta.css',
            array(),
            GRUPOSNAP_THEME_VERSION
        );
    }
);

/**
 * Botón «volver arriba» en azul corporativo (sin degradado naranja del tema).
 */
function gruposnap_enqueue_go_to_top_styles(): void
{
    if (is_admin()) {
        return;
    }

    $deps = array('gruposnap-child');
    if (wp_style_is('site-to-top', 'registered') || wp_style_is('site-to-top', 'enqueued')) {
        $deps[] = 'site-to-top';
    }

    wp_enqueue_style(
        'gruposnap-go-to-top',
        get_stylesheet_directory_uri() . '/assets/css/go-to-top.css',
        $deps,
        GRUPOSNAP_THEME_VERSION
    );
}

add_action('wp_enqueue_scripts', 'gruposnap_enqueue_go_to_top_styles', 130);
