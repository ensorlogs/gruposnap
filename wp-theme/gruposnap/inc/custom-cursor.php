<?php
/**
 * GrupoSnap — cursor del logo (mano snap) en el front-end.
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return bool
 */
function gruposnap_should_use_custom_cursor(): bool
{
    if (is_admin()) {
        return false;
    }

    if (wp_doing_ajax() || wp_doing_cron()) {
        return false;
    }

    if (class_exists('\Elementor\Plugin')) {
        $plugin = \Elementor\Plugin::$instance;
        if ($plugin && $plugin->editor && $plugin->editor->is_edit_mode()) {
            return false;
        }
        if ($plugin && $plugin->preview && $plugin->preview->is_preview_mode()) {
            return false;
        }
    }

    return true;
}

function gruposnap_enqueue_custom_cursor_styles(): void
{
    if (!gruposnap_should_use_custom_cursor()) {
        return;
    }

    wp_enqueue_style(
        'gruposnap-custom-cursor',
        get_stylesheet_directory_uri() . '/assets/css/custom-cursor.css',
        array('gruposnap-child'),
        GRUPOSNAP_THEME_VERSION
    );
}

function gruposnap_custom_cursor_body_class(array $classes): array
{
    if (gruposnap_should_use_custom_cursor()) {
        $classes[] = 'gruposnap-custom-cursor';
    }

    return $classes;
}

add_action('wp_enqueue_scripts', 'gruposnap_enqueue_custom_cursor_styles', 120);
add_filter('body_class', 'gruposnap_custom_cursor_body_class');
