<?php
/**
 * Footer Elementor (985) — estilos responsive compactos.
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return bool
 */
function gruposnap_should_enqueue_footer_mobile_styles(): bool
{
    if (is_admin()) {
        return false;
    }

    if (class_exists('\Elementor\Plugin')) {
        $plugin = \Elementor\Plugin::$instance;
        if ($plugin->editor && $plugin->editor->is_edit_mode()) {
            return false;
        }
        if ($plugin->preview && $plugin->preview->is_preview_mode()) {
            return false;
        }
    }

    return true;
}

function gruposnap_enqueue_footer_mobile_styles(): void
{
    if (!gruposnap_should_enqueue_footer_mobile_styles()) {
        return;
    }

    $deps = array('gruposnap-child');
    foreach (array('gruposnap-footer-payments', 'elementor-post-985', 'elementor-frontend') as $handle) {
        if (wp_style_is($handle, 'registered') || wp_style_is($handle, 'enqueued')) {
            $deps[] = $handle;
        }
    }

    wp_enqueue_style(
        'gruposnap-footer-mobile',
        get_stylesheet_directory_uri() . '/assets/css/footer-mobile.css',
        array_values(array_unique($deps)),
        GRUPOSNAP_THEME_VERSION
    );
}

add_action('wp_enqueue_scripts', 'gruposnap_enqueue_footer_mobile_styles', 110);
