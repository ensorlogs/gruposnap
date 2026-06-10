<?php
/**
 * Bloqueo de pan horizontal en tablet (toda la web).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

function gruposnap_enqueue_viewport_pan_lock(): void
{
    if (is_admin()) {
        return;
    }

    wp_enqueue_style(
        'gruposnap-viewport-pan-lock',
        get_stylesheet_directory_uri() . '/assets/css/viewport-pan-lock.css',
        array('gruposnap-child'),
        GRUPOSNAP_THEME_VERSION
    );
}

add_action('wp_enqueue_scripts', 'gruposnap_enqueue_viewport_pan_lock', 1010);
