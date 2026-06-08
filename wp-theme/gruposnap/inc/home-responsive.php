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

    wp_enqueue_script(
        'gruposnap-mobile-viewport-lock',
        get_stylesheet_directory_uri() . '/assets/js/mobile-viewport-lock.js',
        array(),
        GRUPOSNAP_THEME_VERSION,
        false
    );
}

/**
 * Viewport fijo en móvil (antes del render) para evitar zoom out/in.
 */
function gruposnap_home_viewport_lock_meta(): void
{
    if (!function_exists('gruposnap_is_home_front') || !gruposnap_is_home_front()) {
        return;
    }

    if (is_admin()) {
        return;
    }

    echo '<script id="gruposnap-viewport-lock">(function(){document.documentElement.classList.add("gruposnap-home-lock");var m=document.querySelector(\'meta[name="viewport"]\');if(m){m.setAttribute(\'content\',\'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover\');}})();</script>' . "\n";
}

/**
 * @param array<int, string> $classes
 * @return array<int, string>
 */
function gruposnap_home_lock_body_class(array $classes): array
{
    if (function_exists('gruposnap_is_home_front') && gruposnap_is_home_front()) {
        $classes[] = 'gruposnap-home-lock';
    }

    return $classes;
}

add_filter('body_class', 'gruposnap_home_lock_body_class');

add_action('wp_head', 'gruposnap_home_viewport_lock_meta', 0);
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_home_responsive_styles', 1005);
