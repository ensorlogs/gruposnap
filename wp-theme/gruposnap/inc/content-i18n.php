<?php
/**
 * GrupoSnap — traducción de contenido HTML (Elementor + tema) vía mapa ES→EN.
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return array<string, string>
 */
function gruposnap_en_translations(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }

    $file = get_stylesheet_directory() . '/data/en-translations.php';
    if (!is_readable($file)) {
        $map = array();

        return $map;
    }

    $loaded = require $file;
    $map    = is_array($loaded) ? $loaded : array();

    return $map;
}

/**
 * @return array<string, string>
 */
function gruposnap_en_translations_sorted(): array
{
    static $sorted = null;
    if ($sorted !== null) {
        return $sorted;
    }

    $map = gruposnap_en_translations();
    uksort(
        $map,
        static function (string $a, string $b): int {
            return strlen($b) <=> strlen($a);
        }
    );
    $sorted = $map;

    return $sorted;
}

/**
 * Traduce una cadena suelta si existe en el mapa.
 */
function gruposnap_translate_string(string $text): string
{
    if (gruposnap_current_lang() !== 'en' || $text === '') {
        return $text;
    }

    $map = gruposnap_en_translations();
    if (isset($map[$text])) {
        return $map[$text];
    }

    return $text;
}

/**
 * Traduce HTML completo sustituyendo cadenas del mapa (más largas primero).
 * Protege URLs y atributos técnicos para no corromper src/href (p. ej. portfolio → bytfolio).
 */
function gruposnap_translate_html(string $html): string
{
    if (gruposnap_current_lang() !== 'en' || $html === '') {
        return $html;
    }

    $placeholders = array();
    $index        = 0;

    $protect = static function (array $matches) use (&$placeholders, &$index): string {
        $key                      = '%%GSNAP' . $index . '%%';
        $placeholders[$key]       = $matches[0];
        ++$index;

        return $key;
    };

    // Bloques que no deben traducirse.
    $html = (string) preg_replace_callback('/<script\b[^>]*>[\s\S]*?<\/script>/i', $protect, $html);
    $html = (string) preg_replace_callback('/<style\b[^>]*>[\s\S]*?<\/style>/i', $protect, $html);

    // Atributos con rutas/URLs (src, href, srcset, data-* de medios, etc.).
    $html = (string) preg_replace_callback(
        '/\b(?:src|href|srcset|data-src|data-lazy-src|data-bg|data-background|poster|content)\s*=\s*(["\'])([\s\S]*?)\1/i',
        $protect,
        $html
    );

    // url(...) en estilos inline.
    $html = (string) preg_replace_callback('/url\(\s*[\'"]?[^)\'"]+[\'"]?\s*\)/i', $protect, $html);

    $html = strtr($html, gruposnap_en_translations_sorted());

    if ($placeholders !== []) {
        $html = strtr($html, $placeholders);
    }

    return $html;
}

/**
 * @param string $translated
 * @param string $text
 * @param string $domain
 */
function gruposnap_filter_gettext(string $translated, string $text, string $domain): string
{
    if ($domain !== 'gruposnap' || gruposnap_current_lang() !== 'en') {
        return $translated;
    }

    return gruposnap_translate_string($text);
}
add_filter('gettext', 'gruposnap_filter_gettext', 20, 3);

/**
 * @param string $translated
 * @param string $single
 * @param string $plural
 * @param int    $number
 * @param string $domain
 */
function gruposnap_filter_ngettext(string $translated, string $single, string $plural, int $number, string $domain): string
{
    if ($domain !== 'gruposnap' || gruposnap_current_lang() !== 'en') {
        return $translated;
    }

    $key = $number === 1 ? $single : $plural;

    return gruposnap_translate_string($key);
}
add_filter('ngettext', 'gruposnap_filter_ngettext', 20, 5);

/**
 * Traduce títulos y extractos de páginas legales en inglés.
 *
 * @param string $title
 * @param int    $post_id
 */
function gruposnap_i18n_legal_post_title(string $title, int $post_id): string
{
    if (gruposnap_current_lang() !== 'en') {
        return $title;
    }

    $slug = get_post_field('post_name', $post_id);
    if (!is_string($slug) || $slug === '') {
        return gruposnap_translate_string($title);
    }

    if (function_exists('gruposnap_legal_title')) {
        $localized = gruposnap_legal_title($slug);
        if ($localized !== $slug) {
            return $localized;
        }
    }

    return gruposnap_translate_string($title);
}
add_filter('the_title', 'gruposnap_i18n_legal_post_title', 20, 2);

/**
 * @param string       $excerpt
 * @param int|WP_Post  $post
 */
function gruposnap_i18n_legal_post_excerpt(string $excerpt, $post): string
{
    if (gruposnap_current_lang() !== 'en') {
        return $excerpt;
    }

    $post_id = $post instanceof WP_Post ? (int) $post->ID : (int) $post;
    $slug    = get_post_field('post_name', $post_id);
    if (is_string($slug) && $slug !== '' && function_exists('gruposnap_legal_lead')) {
        $lead = gruposnap_legal_lead($slug);
        if ($lead !== '') {
            return $lead;
        }
    }

    return gruposnap_translate_string($excerpt);
}
add_filter('get_the_excerpt', 'gruposnap_i18n_legal_post_excerpt', 20, 2);

/**
 * Sustituye cuerpo legal por seed EN en páginas /en/legal/*.
 *
 * @param string $content
 */
function gruposnap_i18n_legal_page_content(string $content): string
{
    if (gruposnap_current_lang() !== 'en' || !is_page() || !is_page_template('page-legal.php')) {
        return $content;
    }

    $slug = get_post_field('post_name', get_the_ID());
    if (!is_string($slug) || $slug === '') {
        return gruposnap_translate_html($content);
    }

    if (function_exists('gruposnap_legal_seed_html')) {
        $seed = gruposnap_legal_seed_html($slug);
        if ($seed !== '') {
            return $seed;
        }
    }

    return gruposnap_translate_html($content);
}
add_filter('the_content', 'gruposnap_i18n_legal_page_content', 999);

/**
 * Buffer de salida para traducir HTML renderizado (Elementor, menús, etc.).
 */
function gruposnap_i18n_start_buffer(): void
{
    if (is_admin() || gruposnap_current_lang() !== 'en') {
        return;
    }

    ob_start(
        static function (string $buffer): string {
            return gruposnap_translate_html($buffer);
        }
    );
}
add_action('template_redirect', 'gruposnap_i18n_start_buffer', 0);

/**
 * Cadenas para scripts de cookies y accesibilidad.
 *
 * @return array<string, string>
 */
function gruposnap_js_i18n_strings(): array
{
    return array(
        'skipLink'           => gruposnap_t('Saltar al contenido principal', 'Skip to main content'),
        'a11yOpen'           => gruposnap_t('Abrir opciones de accesibilidad', 'Open accessibility options'),
        'a11yClose'          => gruposnap_t('Cerrar panel de accesibilidad', 'Close accessibility panel'),
        'a11yTitle'          => gruposnap_t('Accesibilidad', 'Accessibility'),
        'a11yDialog'         => gruposnap_t('Opciones de accesibilidad', 'Accessibility options'),
        'textSize'           => gruposnap_t('Tamaño del texto', 'Text size'),
        'textNormal'         => gruposnap_t('Tamaño normal', 'Normal size'),
        'textLarge'          => gruposnap_t('Tamaño grande', 'Large size'),
        'textXLarge'         => gruposnap_t('Tamaño muy grande', 'Extra large size'),
        'spacing'            => gruposnap_t('Espaciado', 'Spacing'),
        'spacingHint'        => gruposnap_t('Más aire al leer', 'More reading space'),
        'contrast'           => gruposnap_t('Alto contraste', 'High contrast'),
        'contrastHint'       => gruposnap_t('Mejor legibilidad', 'Better readability'),
        'reset'              => gruposnap_t('Restablecer', 'Reset'),
        'yes'                => gruposnap_t('Sí', 'Yes'),
        'no'                 => gruposnap_t('No', 'No'),
        'cookiesDialog'      => gruposnap_t('Aviso de cookies', 'Cookie notice'),
        'cookiesTitle'       => gruposnap_t('Usamos cookies', 'We use cookies'),
        'cookiesText'        => gruposnap_t(
            'Usamos cookies técnicas necesarias para que el sitio funcione y, si tú lo aceptas, cookies de medición y marketing para entender qué contenido funciona mejor. Puedes aceptar, rechazar o ajustar por categoría. Más información en nuestra',
            'We use essential technical cookies so the site works and, if you accept, analytics and marketing cookies to understand what content performs best. You can accept, reject, or adjust by category. More information in our'
        ),
        'cookiesPolicy'      => gruposnap_t('política de cookies', 'cookie policy'),
        'rejectAll'          => gruposnap_t('Rechazar todo', 'Reject all'),
        'customize'          => gruposnap_t('Personalizar', 'Customize'),
        'acceptAll'          => gruposnap_t('Aceptar todo', 'Accept all'),
        'prefsTitle'         => gruposnap_t('Preferencias de cookies', 'Cookie preferences'),
        'prefsIntro'         => gruposnap_t(
            'Activa solo las categorías que quieras. Las cookies técnicas son imprescindibles y no se pueden desactivar.',
            'Enable only the categories you want. Technical cookies are essential and cannot be disabled.'
        ),
        'technical'          => gruposnap_t('Técnicas (necesarias)', 'Technical (required)'),
        'technicalDesc'      => gruposnap_t(
            'Sesión, carrito, accesibilidad y registro de consentimiento.',
            'Session, cart, accessibility, and consent storage.'
        ),
        'analytics'          => gruposnap_t('Analítica / medición', 'Analytics / measurement'),
        'analyticsDesc'      => gruposnap_t(
            'Datos agregados para mejorar el sitio y el catálogo.',
            'Aggregated data to improve the site and catalog.'
        ),
        'marketing'          => gruposnap_t('Marketing', 'Marketing'),
        'marketingDesc'      => gruposnap_t(
            'Recordar interacciones con campañas. Hoy no se cargan por defecto.',
            'Remember campaign interactions. They are not loaded by default today.'
        ),
        'cancel'             => gruposnap_t('Cancelar', 'Cancel'),
        'savePrefs'          => gruposnap_t('Guardar preferencias', 'Save preferences'),
    );
}

/**
 * Pasa cadenas traducidas a cookies.js y a11y.js.
 */
function gruposnap_localize_compliance_scripts(): void
{
    if (is_admin()) {
        return;
    }

    $strings = gruposnap_js_i18n_strings();
    wp_localize_script('gruposnap-a11y', 'GrupoSnapA11yConfig', array('strings' => $strings));
    wp_localize_script('gruposnap-cookies', 'GrupoSnapCookiesConfig', array('strings' => $strings));
}
add_action('wp_enqueue_scripts', 'gruposnap_localize_compliance_scripts', 120);
