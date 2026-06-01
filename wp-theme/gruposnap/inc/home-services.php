<?php
/**
 * Home — servicios: tarjetas compactas tipo «Activación de eventos» en móvil/tablet.
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
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
}

add_action('wp_enqueue_scripts', 'gruposnap_enqueue_home_services_styles', 130);
