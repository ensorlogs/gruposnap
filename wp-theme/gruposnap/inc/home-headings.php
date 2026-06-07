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
    $is_en = function_exists('gruposnap_current_lang') && gruposnap_current_lang() === 'en';

    if ($is_en) {
        if (!str_contains($html, 'Experiences')) {
            return $html;
        }

        $html = (string) preg_replace(
            '/^20 years/u',
            '<span class="gruposnap-heading-accent-navy">20 years</span>',
            $html,
            1
        );

        return (string) preg_replace(
            '/Experiences/u',
            '<span class="gruposnap-heading-accent">Experiences</span>',
            $html,
            1
        );
    }

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
 * Subtítulo hero: «Material POP» naranja · «Marketing Experiencial» azul.
 */
function gruposnap_hero_subtitle_has_brand_spans(string $html): bool
{
    return (
        preg_match('/gruposnap-heading-accent[^>]*>\s*(Material\s+POP|POP\s+MATERIALS)/iu', $html) === 1
        && preg_match('/gruposnap-heading-accent-navy[^>]*>\s*(Marketing\s+Experiencial|Experiential\s+Marketing|EXPERIENTIAL\s+MARKETING)/iu', $html) === 1
    );
}

function gruposnap_hero_build_subtitle_brand_html(string $pop, string $separator, string $marketingExperiencial): string
{
    $navyPart = (string) preg_replace(
        '/\s+(Experiencial|EXPERIENCIAL)/u',
        ' <br class="gruposnap-hero-subtitle-br" aria-hidden="true" />$1',
        $marketingExperiencial
    );

    return '<span class="gruposnap-hero-subtitle-stack">'
        . '<span class="gruposnap-heading-accent">' . $pop . '</span>'
        . '<span class="gruposnap-hero-subtitle-sep" aria-hidden="true">' . $separator . '</span>'
        . '<span class="gruposnap-heading-accent-navy">' . $navyPart . '</span>'
        . '</span>';
}

function gruposnap_hero_wrap_subtitle_stack(string $content): string
{
    if (str_contains($content, 'gruposnap-hero-subtitle-stack')) {
        return $content;
    }

    if (!preg_match('/<span class="gruposnap-heading-accent">/i', $content)) {
        return $content;
    }

    return (string) preg_replace(
        '/(<span class="gruposnap-heading-accent">.*?<\/span>)(\s*[·•]\s*)(<span class="gruposnap-heading-accent-navy">.*?<\/span>)/is',
        '<span class="gruposnap-hero-subtitle-stack">$1<span class="gruposnap-hero-subtitle-sep" aria-hidden="true">$2</span>$3</span>',
        $content,
        1
    );
}

function gruposnap_hero_fix_subtitle_spacing(string $content): string
{
    $content = (string) preg_replace('/MarketingExperiencial/iu', 'Marketing Experiencial', $content);
    $content = (string) preg_replace('/MARKETINGEXPERIENCIAL/u', 'MARKETING EXPERIENCIAL', $content);

    $content = (string) preg_replace(
        '/(Marketing|MARKETING)<br class="gruposnap-hero-subtitle-br"[^>]*\/?>\s*(Experiencial|EXPERIENCIAL)/iu',
        '$1 <br class="gruposnap-hero-subtitle-br" aria-hidden="true" />$2',
        $content
    );

    return gruposnap_hero_wrap_subtitle_stack($content);
}

function gruposnap_hero_apply_subtitle_brand_colors(string $content): string
{
    if (gruposnap_hero_subtitle_has_brand_spans($content)) {
        return gruposnap_hero_fix_subtitle_spacing($content);
    }

    if (!preg_match('/material\s+pop|pop\s+materials|marketing\s+experiencial|experiential\s+marketing/iu', wp_strip_all_tags($content))) {
        return $content;
    }

    $content = (string) preg_replace_callback(
        '/(Material\s+POP|MATERIAL\s+POP|Material\s+Pop|POP\s+MATERIALS|Pop\s+Materials)(\s*[·•]\s*)(Marketing\s+Experiencial|MARKETING\s+EXPERIENCIAL|Experiential\s+Marketing|EXPERIENTIAL\s+MARKETING)/u',
        static function (array $matches): string {
            return gruposnap_hero_build_subtitle_brand_html($matches[1], $matches[2], $matches[3]);
        },
        $content,
        1
    );

    return gruposnap_hero_fix_subtitle_spacing($content);
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

    $content = (string) preg_replace_callback(
        '/(<span class="wdt-heading-title">)(.*?)(<\/span>)/s',
        static function (array $matches): string {
            $inner = (string) preg_replace('/<br\s*\/?>\s*/i', ' ', $matches[2]);
            $title = esc_html(wp_strip_all_tags($inner));
            $title = gruposnap_hero_heading_highlight($title);

            return $matches[1] . $title . $matches[3];
        },
        $content
    );

    return gruposnap_hero_apply_subtitle_brand_colors($content);
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

function gruposnap_hero_subtitle_brand_colors_fallback(): void
{
    if (!gruposnap_should_style_home_headings()) {
        return;
    }
    ?>
    <script id="gruposnap-hero-subtitle-brand-colors">
    (function () {
        var pattern = /(Material\s+POP|MATERIAL\s+POP|Material\s+Pop|POP\s+MATERIALS|Pop\s+Materials|POP\s+Materials)(\s*[·•]\s*)(Marketing\s+Experiencial|MARKETING\s+EXPERIENCIAL|Experiential\s+Marketing|EXPERIENTIAL\s+MARKETING)/;

        function buildHeroSubtitleHtml(match) {
            var navyPart = match[3].replace(/\s+(Experiencial|EXPERIENCIAL|Experiential|EXPERIENTIAL)/, ' <br class="gruposnap-hero-subtitle-br" aria-hidden="true" />$1');
            return '<span class="gruposnap-hero-subtitle-stack">'
                + '<span class="gruposnap-heading-accent">' + match[1] + '</span>'
                + '<span class="gruposnap-hero-subtitle-sep" aria-hidden="true">' + match[2] + '</span>'
                + '<span class="gruposnap-heading-accent-navy">' + navyPart + '</span>'
                + '</span>';
        }

        function wrapHeroSubtitleStack(html) {
            if (/gruposnap-hero-subtitle-stack/.test(html)) {
                return html;
            }

            return html.replace(
                /(<span class="gruposnap-heading-accent">[\s\S]*?<\/span>)(\s*[·•]\s*)(<span class="gruposnap-heading-accent-navy">[\s\S]*?<\/span>)/i,
                '<span class="gruposnap-hero-subtitle-stack">$1<span class="gruposnap-hero-subtitle-sep" aria-hidden="true">$2</span>$3</span>'
            );
        }

        function normalizeHeroSubtitleHtml(html) {
            return wrapHeroSubtitleStack(
                html
                    .replace(/MarketingExperiencial/gi, 'Marketing Experiencial')
                    .replace(/MARKETINGEXPERIENCIAL/g, 'MARKETING EXPERIENCIAL')
                    .replace(/MarketingExperiential/gi, 'Experiential Marketing')
                    .replace(/MARKETINGEXPERIENTIAL/g, 'EXPERIENTIAL MARKETING')
                    .replace(/(Marketing|MARKETING)<br class="gruposnap-hero-subtitle-br"[^>]*\/?>\s*(Experiencial|EXPERIENCIAL)/gi, '$1 <br class="gruposnap-hero-subtitle-br" aria-hidden="true" />$2')
                    .replace(/(Experiential|EXPERIENTIAL)<br class="gruposnap-hero-subtitle-br"[^>]*\/?>\s*(Marketing|MARKETING)/gi, '$1 <br class="gruposnap-hero-subtitle-br" aria-hidden="true" />$2')
            );
        }

        function patchHeroSubtitle() {
            document.querySelectorAll(
                '.elementor-element-8082631 .wdt-heading-subtitle, .elementor-element-f428155 .wdt-heading-subtitle'
            ).forEach(function (el) {
                var html = el.innerHTML;
                if (!/material\s+pop|pop\s+materials|marketing\s+experiencial|experiential\s+marketing/i.test(html)) {
                    return;
                }

                if (el.querySelector('.gruposnap-heading-accent-navy') && /gruposnap-heading-accent[^>]*>\s*(Material\s+POP|POP\s+MATERIALS)/i.test(el.innerHTML)) {
                    el.innerHTML = normalizeHeroSubtitleHtml(html);
                    return;
                }

                el.innerHTML = normalizeHeroSubtitleHtml(html.replace(pattern, function () {
                    return buildHeroSubtitleHtml(Array.prototype.slice.call(arguments, 0, 4));
                }));
            });
        }

        patchHeroSubtitle();
        document.addEventListener('DOMContentLoaded', patchHeroSubtitle);
        window.addEventListener('load', patchHeroSubtitle);
    })();
    </script>
    <?php
}

add_action('init', 'gruposnap_home_headings_bust_elementor_cache', 1);
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_home_headings_styles', 105);
add_action('wp_footer', 'gruposnap_hero_subtitle_brand_colors_fallback', 12);
add_action('elementor/frontend/widget/before_render', 'gruposnap_hero_heading_wrapper_class', 6);
add_action('elementor/frontend/widget/before_render', 'gruposnap_marketing_heading_wrapper_class', 6);
add_filter('elementor/widget/render_content', 'gruposnap_hero_heading_enhance_content', 19, 2);
add_filter('elementor/widget/render_content', 'gruposnap_marketing_heading_enhance_content', 20, 2);
