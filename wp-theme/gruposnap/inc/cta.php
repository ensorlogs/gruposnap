<?php
/**
 * CTAs GrupoSnap — clases y variantes según texto, enlace y contexto visual.
 * No modifica enlaces de contenido ni colores globales del tema.
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Paleta oficial (solo botones CTA).
 *
 * @return array<string, string>
 */
function gruposnap_cta_palette(): array
{
    return array(
        'orange'         => '#F57C20',
        'orange_hover'   => '#E56712',
        'blue'           => '#112B75',
        'blue_hover'     => '#0C1E55',
        'white'          => '#FFFFFF',
        'whatsapp'       => '#25D366',
        'whatsapp_hover' => '#1DA851',
    );
}

/**
 * @return ''|'primary'|'secondary'|'whatsapp'
 */
function gruposnap_resolve_cta_variant(string $text, string $url, string $css_classes): string
{
    if (str_contains($css_classes, 'wdt-custom-slider-scroll')) {
        return '';
    }

    $blob = strtolower($text . ' ' . $url . ' ' . $css_classes);
    $blob = strtr($blob, array(
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
    ));

    if (preg_match('/wa\.me|whatsapp\.com|api\.whatsapp|wa\.link/i', $blob)) {
        return 'whatsapp';
    }

    if (
        preg_match('/catalogo|catlaogo|catalog|flipsnack\.com/i', $blob)
        || preg_match('/\.pdf(\?|$)/i', $url)
        || preg_match('/\b(ver cat|ver catalogo|ver catálogo)\b/i', $blob)
    ) {
        return 'secondary';
    }

    if (
        str_contains($css_classes, 'wdt-custom-button')
        || preg_match('/\b(ver mas|view more|leer mas|read more|saber mas|conocer mas|mas informacion|ver servicios|ver opciones|ver soluciones|ver ejemplos|see all)\b/i', $blob)
    ) {
        return 'secondary';
    }

    if (
        str_contains($css_classes, 'wdt-custom-gr-bg-btn')
        && !preg_match('/catalogo|catlaogo|catalog|flipsnack/i', $blob)
    ) {
        return 'primary';
    }

    if (preg_match('/\b(cotizar|cotiza|contactar|contacto|solicitar|informacion|llamanos|escribenos|contáctanos|contactanos)\b/i', $blob)) {
        return 'primary';
    }

    if (preg_match('/\b(comprar|add to cart|añadir|carrito|shop now)\b/i', $blob)) {
        return 'primary';
    }

    return '';
}

/**
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_cta_apply_wrapper_class($widget): void
{
    if ($widget->get_name() !== 'wdt-button') {
        return;
    }

    $settings = $widget->get_settings_for_display();
    $text     = (string) ($settings['text'] ?? '');
    $url      = (string) ($settings['link']['url'] ?? '');
    $classes  = (string) ($settings['_css_classes'] ?? '');

    $variant = gruposnap_resolve_cta_variant($text, $url, $classes);
    if ($variant === '') {
        return;
    }

    $widget->add_render_attribute('_wrapper', 'class', 'gruposnap-cta--' . $variant);
}

/**
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_cta_inject_button_class(string $content, $widget): string
{
    if ($widget->get_name() !== 'wdt-button') {
        return $content;
    }

    $settings = $widget->get_settings_for_display();
    $text     = (string) ($settings['text'] ?? '');
    $url      = (string) ($settings['link']['url'] ?? '');
    $classes  = (string) ($settings['_css_classes'] ?? '');

    $variant = gruposnap_resolve_cta_variant($text, $url, $classes);
    if ($variant === '') {
        return $content;
    }

    $class = 'gruposnap-cta--' . $variant;

    $updated = preg_replace(
        '/class="((?:wdt-button)[^"]*)"/',
        'class="$1 ' . esc_attr($class) . '"',
        $content,
        1
    );

    return is_string($updated) ? $updated : $content;
}

/** CTAs bajo catálogo / portafolio en home (ocultos). */
const GRUPOSNAP_HOME_CATALOG_FOOTER_WIDGET_IDS = array(
    '037651e', /* VER CATLAOGO — desktop */
    '071b17c', /* texto contacto — desktop */
    '465050a', /* VER CATLAOGO — móvil */
    'b52be7a', /* texto contacto — móvil */
    'e4b12f6', /* COTIZA CON NOSOTROS — catálogo 2026 */
    '088741d', /* COTIZA CON NOSOTROS — catálogo (tablet/móvil) */
);

/**
 * @return bool
 */
function gruposnap_is_home_catalog_footer_context(): bool
{
    if (is_admin()) {
        return false;
    }

    if (function_exists('gruposnap_is_home_front')) {
        return gruposnap_is_home_front();
    }

    $page_id = (int) (get_queried_object_id() ?: get_the_ID());

    return $page_id === 751 || (is_front_page() && (int) get_option('page_on_front') === 751);
}

/**
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_should_hide_home_catalog_footer_widget($widget): bool
{
    if (!gruposnap_is_home_catalog_footer_context()) {
        return false;
    }

    return in_array($widget->get_id(), GRUPOSNAP_HOME_CATALOG_FOOTER_WIDGET_IDS, true);
}

/**
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_hide_home_catalog_footer_cta(string $content, $widget): string
{
    if (!gruposnap_should_hide_home_catalog_footer_widget($widget)) {
        return $content;
    }

    return '';
}

function gruposnap_enqueue_home_catalog_footer_hide_styles(): void
{
    if (!gruposnap_is_home_catalog_footer_context()) {
        return;
    }

    $css = '.elementor-751 .elementor-element-037651e,.elementor-751 .elementor-element-071b17c,.elementor-751 .elementor-element-465050a,.elementor-751 .elementor-element-b52be7a,.elementor-751 .elementor-element-e4b12f6,.elementor-751 .elementor-element-088741d{display:none!important;margin:0!important;padding:0!important;height:0!important;overflow:hidden!important;}';

    wp_register_style('gruposnap-home-catalog-footer-hide', false, array(), GRUPOSNAP_THEME_VERSION);
    wp_enqueue_style('gruposnap-home-catalog-footer-hide');
    wp_add_inline_style('gruposnap-home-catalog-footer-hide', $css);
}

add_action('elementor/widget/before_render_content', 'gruposnap_cta_apply_wrapper_class', 5);
add_filter('elementor/widget/render_content', 'gruposnap_cta_inject_button_class', 12, 2);
add_filter('elementor/widget/render_content', 'gruposnap_hide_home_catalog_footer_cta', 5, 2);
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_home_catalog_footer_hide_styles', 120);
