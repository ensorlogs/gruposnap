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

add_action('elementor/widget/before_render_content', 'gruposnap_cta_apply_wrapper_class', 5);
add_filter('elementor/widget/render_content', 'gruposnap_cta_inject_button_class', 12, 2);
