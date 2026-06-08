<?php
/**
 * GrupoSnap — tema hijo (capa de personalización sobre tema padre licenciado en el servidor).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

define('GRUPOSNAP_THEME_VERSION', '1.7.221');

require_once get_stylesheet_directory() . '/inc/brand.php';
require_once get_stylesheet_directory() . '/inc/updater.php';
require_once get_stylesheet_directory() . '/inc/admin.php';

add_action(
    'wp_enqueue_scripts',
    static function (): void {
        $parent = get_template();
        $parent_style = $parent . '-parent';
        wp_enqueue_style(
            $parent_style,
            get_template_directory_uri() . '/style.css',
            array(),
            wp_get_theme($parent)->get('Version')
        );
        wp_enqueue_style(
            'gruposnap-child',
            get_stylesheet_uri(),
            array($parent_style),
            GRUPOSNAP_THEME_VERSION
        );
    },
    99
);
