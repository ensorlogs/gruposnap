<?php
/**
 * Home — títulos de sección con branding marketing (colores, línea debajo, SNAP!).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Títulos del hero (sin estilo de sección). */
const GRUPOSNAP_HOME_HERO_HEADING_IDS = array(
    '8082631',
    'f428155',
);

/**
 * @return bool
 */
function gruposnap_should_style_home_headings(): bool
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
function gruposnap_should_apply_marketing_heading_to_widget($widget): bool
{
    if ($widget->get_name() !== 'wdt-heading') {
        return false;
    }

    return !in_array($widget->get_id(), GRUPOSNAP_HOME_HERO_HEADING_IDS, true);
}

/**
 * @return array<string, string>
 */
function gruposnap_marketing_heading_modifiers(): array
{
    return array(
        '0330a3d' => 'testimonials',
        '4d58045' => 'catalog',
        'efbc9f6' => 'catalog',
        '57435c6' => 'catalog-2026',
        '5d119c5' => 'catalog-2026',
        '682407d' => 'services',
        '424703b' => 'blog',
    );
}

/**
 * Título en mayúsculas → título legible.
 */
function gruposnap_marketing_friendly_title(string $title): string
{
    $title = trim($title);
    if ($title === '') {
        return $title;
    }

    if (preg_match('/SNAP!/u', $title)) {
        return $title;
    }

    $upper = mb_strtoupper($title, 'UTF-8');
    if ($title === $upper && mb_strlen($title) > 3) {
        $lower = mb_strtolower($title, 'UTF-8');

        return mb_strtoupper(mb_substr($lower, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($lower, 1, null, 'UTF-8');
    }

    return $title;
}

/**
 * Resalta «SNAP!» con color naranja.
 */
function gruposnap_marketing_highlight_snap(string $html): string
{
    if (!str_contains($html, 'SNAP!')) {
        return $html;
    }

    return (string) preg_replace(
        '/SNAP!/u',
        '<span class="gruposnap-heading-accent">SNAP!</span>',
        $html,
        1
    );
}

/**
 * Resalta «Experiencias» en el hero (mismo acento que «SNAP!»).
 */
function gruposnap_hero_heading_highlight(string $html): string
{
    if (!str_contains($html, 'Experiencias')) {
        return $html;
    }

    $html = (string) preg_replace(
        '/^20 años/u',
        '<span class="gruposnap-heading-accent-navy">20 años</span>',
        $html,
        1
    );

    return (string) preg_replace(
        '/Experiencias/u',
        '<span class="gruposnap-heading-accent">Experiencias</span>',
        $html,
        1
    );
}

/**
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_hero_heading_wrapper_class($widget): void
{
    if (!gruposnap_should_style_home_headings()) {
        return;
    }

    if ($widget->get_name() !== 'wdt-heading' || !in_array($widget->get_id(), GRUPOSNAP_HOME_HERO_HEADING_IDS, true)) {
        return;
    }

    $widget->add_render_attribute('_wrapper', 'class', 'gruposnap-hero-heading');
}

/**
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_hero_heading_enhance_content(string $content, $widget): string
{
    if (!gruposnap_should_style_home_headings()) {
        return $content;
    }

    if ($widget->get_name() !== 'wdt-heading' || !in_array($widget->get_id(), GRUPOSNAP_HOME_HERO_HEADING_IDS, true)) {
        return $content;
    }

    return (string) preg_replace_callback(
        '/(<span class="wdt-heading-title">)(.*?)(<\/span>)/s',
        static function (array $matches): string {
            $inner = (string) preg_replace('/<br\s*\/?>\s*/i', ' ', $matches[2]);
            $title = esc_html(wp_strip_all_tags($inner));
            $title = gruposnap_hero_heading_highlight($title);

            return $matches[1] . $title . $matches[3];
        },
        $content
    );
}

/**
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_marketing_heading_wrapper_class($widget): void
{
    if (!gruposnap_should_style_home_headings() || !gruposnap_should_apply_marketing_heading_to_widget($widget)) {
        return;
    }

    $widget->add_render_attribute('_wrapper', 'class', 'gruposnap-marketing-heading');

    $modifiers = gruposnap_marketing_heading_modifiers();
    $id        = $widget->get_id();
    if (isset($modifiers[$id])) {
        $widget->add_render_attribute('_wrapper', 'class', 'gruposnap-marketing-heading--' . $modifiers[$id]);
    }
}

/**
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_marketing_heading_enhance_content(string $content, $widget): string
{
    if (!gruposnap_should_style_home_headings() || !gruposnap_should_apply_marketing_heading_to_widget($widget)) {
        return $content;
    }

    $content = (string) preg_replace(
        '/<span class="gruposnap-heading-eyebrow">.*?<\/span>/s',
        '',
        $content
    );

    $content = (string) preg_replace_callback(
        '/(<span class="wdt-heading-title">)(.*?)(<\/span>)/s',
        static function (array $matches): string {
            $inner = (string) preg_replace('/<br\s*\/?>\s*/i', ' ', $matches[2]);
            $title = gruposnap_marketing_friendly_title(wp_strip_all_tags($inner));
            $title = esc_html($title);
            $title = gruposnap_marketing_highlight_snap($title);

            return $matches[1] . $title . $matches[3];
        },
        $content
    );

    return $content;
}

function gruposnap_enqueue_home_headings_styles(): void
{
    if (!gruposnap_should_style_home_headings()) {
        return;
    }

    $deps = array('gruposnap-child');
    foreach (array('elementor-post-751', 'elementor-frontend') as $handle) {
        if (wp_style_is($handle, 'registered') || wp_style_is($handle, 'enqueued')) {
            $deps[] = $handle;
        }
    }

    wp_enqueue_style(
        'gruposnap-home-headings',
        get_stylesheet_directory_uri() . '/assets/css/home-headings.css',
        $deps,
        GRUPOSNAP_THEME_VERSION
    );
}

function gruposnap_home_headings_bust_elementor_cache(): void
{
    $version = get_option('gruposnap_home_headings_cache_version');
    if ($version === GRUPOSNAP_THEME_VERSION) {
        return;
    }

    delete_post_meta(751, '_elementor_element_cache');
    update_option('gruposnap_home_headings_cache_version', GRUPOSNAP_THEME_VERSION, false);
}

add_action('init', 'gruposnap_home_headings_bust_elementor_cache', 1);
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_home_headings_styles', 105);
add_action('elementor/frontend/widget/before_render', 'gruposnap_hero_heading_wrapper_class', 6);
add_action('elementor/frontend/widget/before_render', 'gruposnap_marketing_heading_wrapper_class', 6);
add_filter('elementor/widget/render_content', 'gruposnap_hero_heading_enhance_content', 19, 2);
add_filter('elementor/widget/render_content', 'gruposnap_marketing_heading_enhance_content', 20, 2);
