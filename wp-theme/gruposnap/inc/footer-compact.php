<?php
/**
 * Footer 985 — layout compacto (pagos + oficinas).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

const GRUPOSNAP_FOOTER_OFFICE_RD_COLUMN = '2631571';
const GRUPOSNAP_FOOTER_OFFICE_VE_COLUMN = '0d42af0';
const GRUPOSNAP_FOOTER_OFFICES_SECTION      = '317c69b';
const GRUPOSNAP_FOOTER_OFFICES_WRAP_SECTION = '15ddfc8';

/**
 * @return bool
 */
function gruposnap_should_apply_footer_compact_layout(): bool
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

/**
 * @param \Elementor\Element_Base $element
 */
function gruposnap_footer_compact_section_class($element): void
{
    if (!gruposnap_should_apply_footer_compact_layout()) {
        return;
    }

    if ($element->get_name() !== 'section' || $element->get_id() !== GRUPOSNAP_FOOTER_OFFICES_SECTION) {
        return;
    }

    $element->add_render_attribute('_wrapper', 'class', 'gruposnap-footer-offices');
}

/**
 * @param \Elementor\Element_Base $element
 */
function gruposnap_footer_compact_column_class($element): void
{
    if (!gruposnap_should_apply_footer_compact_layout()) {
        return;
    }

    if ($element->get_name() !== 'column') {
        return;
    }

    $id = $element->get_id();
    if ($id === GRUPOSNAP_FOOTER_OFFICE_RD_COLUMN || $id === GRUPOSNAP_FOOTER_OFFICE_VE_COLUMN) {
        $element->add_render_attribute('_wrapper', 'class', 'gruposnap-footer-office');
    }
}

function gruposnap_enqueue_footer_compact_styles(): void
{
    if (!gruposnap_should_apply_footer_compact_layout()) {
        return;
    }

    $deps = array('gruposnap-child', 'gruposnap-footer-payments');
    foreach (array('gruposnap-footer-mobile', 'elementor-post-985', 'elementor-frontend') as $handle) {
        if (wp_style_is($handle, 'registered') || wp_style_is($handle, 'enqueued')) {
            $deps[] = $handle;
        }
    }

    wp_enqueue_style(
        'gruposnap-footer-compact',
        get_stylesheet_directory_uri() . '/assets/css/footer-compact.css',
        array_values(array_unique($deps)),
        GRUPOSNAP_THEME_VERSION
    );
}

add_action('wp_enqueue_scripts', 'gruposnap_enqueue_footer_compact_styles', 115);
add_action('elementor/frontend/section/before_render', 'gruposnap_footer_compact_section_class', 5);
add_action('elementor/frontend/column/before_render', 'gruposnap_footer_compact_column_class', 5);
