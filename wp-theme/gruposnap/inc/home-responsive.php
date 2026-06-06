<?php
/**
 * Home — estilos responsive globales (márgenes, centrado, embeds).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

function gruposnap_enqueue_home_responsive_styles(): void
{
    if (!function_exists('gruposnap_is_home_front') || !gruposnap_is_home_front()) {
        return;
    }

    $deps = array('gruposnap-child', 'gruposnap-home-headings', 'gruposnap-home-services');

    foreach (array('gruposnap-home-hero', 'gruposnap-home-about', 'elementor-post-751') as $handle) {
        if (wp_style_is($handle, 'registered') || wp_style_is($handle, 'enqueued')) {
            $deps[] = $handle;
        }
    }

    wp_enqueue_style(
        'gruposnap-home-responsive',
        get_stylesheet_directory_uri() . '/assets/css/home-responsive.css',
        array_values(array_unique($deps)),
        GRUPOSNAP_THEME_VERSION
    );
}

add_action('wp_enqueue_scripts', 'gruposnap_enqueue_home_responsive_styles', 1005);
