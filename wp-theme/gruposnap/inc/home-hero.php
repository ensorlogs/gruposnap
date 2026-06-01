<?php
/**
 * Home hero (650305b) — restaura slider como Elementor original + quita animaciones que ocultan contenido.
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

const GRUPOSNAP_HOME_HERO_SECTION_ID = '650305b';

/** Widget icon-list oculto en home (+500 marcas / producción personalizada). */
const GRUPOSNAP_HOME_HERO_HIGHLIGHTS_WIDGET_ID = 'bf70122';

function gruposnap_home_hero_media_urls(): array
{
    $base = get_template_directory_uri() . '/ocdi/uploads/2023';

    return array(
        'image' => $base . '/09/Home-1-Slider-bg.png',
        'video' => $base . '/10/Home-1-Slider-Bg-Vid.mp4',
    );
}

function gruposnap_is_home_page(): bool
{
    if (is_admin()) {
        return false;
    }

    $page_id = (int) (get_queried_object_id() ?: get_the_ID());

    return $page_id === 751 || (is_front_page() && (int) get_option('page_on_front') === 751);
}

/**
 * @param \Elementor\Element_Base $element
 */
function gruposnap_widget_in_home_hero($element): bool
{
    $current = $element;

    while ($current) {
        if (method_exists($current, 'get_id') && $current->get_id() === GRUPOSNAP_HOME_HERO_SECTION_ID) {
            return true;
        }

        if (!method_exists($current, 'get_parent')) {
            break;
        }

        $current = $current->get_parent();
    }

    return false;
}

/**
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_home_hero_disable_widget_entrance_animation($widget): void
{
    if (!gruposnap_is_home_page() || !gruposnap_widget_in_home_hero($widget)) {
        return;
    }

    $widget->set_settings('_animation', '');
    $widget->set_settings('_animation_delay', 0);
}

function gruposnap_home_hero_strip_invisible_class(string $content, $widget): string
{
    if (!gruposnap_is_home_page() || !gruposnap_widget_in_home_hero($widget)) {
        return $content;
    }

    return str_replace('elementor-invisible', '', $content);
}

function gruposnap_home_hero_strip_animation_settings(string $html): string
{
    $html = str_replace('elementor-invisible', '', $html);

    $patterns = array(
        '/,&quot;_animation&quot;:&quot;[^&]*&quot;/',
        '/&quot;_animation&quot;:&quot;[^&]*&quot;,/',
        '/,&quot;_animation_delay&quot;:\d+/',
        '/&quot;_animation_delay&quot;:\d+,/',
    );

    $html = preg_replace($patterns, '', $html) ?? $html;

    return $html;
}

function gruposnap_home_hero_patch_media(string $html): string
{
    $video = esc_url(gruposnap_home_hero_media_urls()['video']);

    return preg_replace(
        '/<video class="elementor-background-video-hosted"([^>]*)>/',
        '<video class="elementor-background-video-hosted"$1 src="' . $video . '">',
        $html,
        1
    ) ?? $html;
}

function gruposnap_home_hero_fix_section_html(string $content): string
{
    if (!gruposnap_is_home_page()) {
        return $content;
    }

    $needle = 'elementor-element-' . GRUPOSNAP_HOME_HERO_SECTION_ID;
    $marker = strpos($content, $needle);
    if (false === $marker) {
        return $content;
    }

    $section_start = strrpos(substr($content, 0, $marker), '<section');
    if (false === $section_start) {
        return $content;
    }

    $depth       = 0;
    $len         = strlen($content);
    $i           = $section_start;
    $section_end = null;

    while ($i < $len) {
        if (!preg_match('/<\/?section\b/i', $content, $match, PREG_OFFSET_CAPTURE, $i)) {
            break;
        }

        $tag = $match[0][0];
        $pos = (int) $match[0][1];

        if (stripos($tag, '</section') === 0) {
            --$depth;
            if ($depth === 0) {
                $gt = strpos($content, '>', $pos);
                $section_end = false !== $gt ? $gt + 1 : $pos + strlen($tag);
                break;
            }
        } else {
            ++$depth;
        }

        $i = $pos + strlen($tag);
    }

    if (null === $section_end) {
        return $content;
    }

    $hero_html = substr($content, $section_start, $section_end - $section_start);
    $fixed     = gruposnap_home_hero_patch_media(
        gruposnap_home_hero_strip_animation_settings($hero_html)
    );

    return substr($content, 0, $section_start) . $fixed . substr($content, $section_end);
}

/**
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_home_hero_remove_invisible_render_attr($widget): void
{
    if (!gruposnap_is_home_page() || !gruposnap_widget_in_home_hero($widget)) {
        return;
    }

    $widget->remove_render_attribute('_wrapper', 'class', 'elementor-invisible');
}

function gruposnap_home_hero_bust_elementor_cache(): void
{
    $version = get_option('gruposnap_home_hero_cache_version');
    if ($version === GRUPOSNAP_THEME_VERSION) {
        return;
    }

    delete_post_meta(751, '_elementor_element_cache');
    update_option('gruposnap_home_hero_cache_version', GRUPOSNAP_THEME_VERSION, false);
}

function gruposnap_enqueue_home_hero_assets(): void
{
    if (!gruposnap_is_home_page()) {
        return;
    }

    $deps = array('gruposnap-child');
    foreach (array('elementor-post-751', 'elementor-frontend') as $handle) {
        if (wp_style_is($handle, 'registered') || wp_style_is($handle, 'enqueued')) {
            $deps[] = $handle;
        }
    }

    wp_enqueue_style(
        'gruposnap-home-hero',
        get_stylesheet_directory_uri() . '/assets/css/home-hero.css',
        $deps,
        GRUPOSNAP_THEME_VERSION
    );
}

function gruposnap_home_hero_reveal_style(): void
{
    if (!gruposnap_is_home_page()) {
        return;
    }

    $section = GRUPOSNAP_HOME_HERO_SECTION_ID;
    ?>
    <style id="gruposnap-home-hero-critical">
    .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-widget,
    .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-invisible,
    .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .animated {
        visibility: visible !important;
        opacity: 1 !important;
        animation: none !important;
        transform: none !important;
    }
    </style>
    <?php
}

function gruposnap_home_hero_reveal_script(): void
{
    if (!gruposnap_is_home_page()) {
        return;
    }

    $section = GRUPOSNAP_HOME_HERO_SECTION_ID;
    $video   = esc_js(gruposnap_home_hero_media_urls()['video']);
    ?>
    <script id="gruposnap-home-hero-reveal-js">
    (function () {
        var sel = '.elementor-751 .elementor-element-<?php echo esc_js($section); ?>';
        var videoSrc = '<?php echo $video; ?>';

        function playHeroVideo(hero) {
            var container = hero.querySelector('.elementor-background-video-container');
            var video = hero.querySelector('.elementor-background-video-hosted');

            if (!video) {
                return;
            }

            if (!video.getAttribute('src')) {
                video.setAttribute('src', videoSrc);
            }

            video.muted = true;
            video.playsInline = true;
            video.loop = true;

            var playPromise = video.play();
            if (playPromise && playPromise.catch) {
                playPromise.catch(function () {});
            }

            if (container) {
                container.classList.remove('elementor-loading', 'elementor-invisible');
            }
        }

        function revealHero() {
            var hero = document.querySelector(sel);
            if (!hero) {
                return;
            }

            playHeroVideo(hero);

            hero.querySelectorAll('.elementor-invisible, .animated').forEach(function (el) {
                el.classList.remove('elementor-invisible', 'animated');
            });

            hero.querySelectorAll('.elementor-widget, .elementor-element, .wdt-heading-holder, .wdt-button-holder').forEach(function (el) {
                el.style.setProperty('visibility', 'visible', 'important');
                el.style.setProperty('opacity', '1', 'important');
            });
        }

        revealHero();
        document.addEventListener('DOMContentLoaded', revealHero);
        window.addEventListener('load', revealHero);
        [100, 400, 1000, 2500].forEach(function (ms) {
            window.setTimeout(revealHero, ms);
        });

        if (window.jQuery) {
            window.jQuery(window).on('elementor/frontend/init', revealHero);
        }
    })();
    </script>
    <?php
}

/**
 * Oculta el bloque de icon-list del hero en la home.
 *
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_home_hero_hide_highlights_widget(string $content, $widget): string
{
    if (!gruposnap_is_home_page() || $widget->get_id() !== GRUPOSNAP_HOME_HERO_HIGHLIGHTS_WIDGET_ID) {
        return $content;
    }

    return '';
}

add_action('init', 'gruposnap_home_hero_bust_elementor_cache', 1);
add_filter('elementor/widget/render_content', 'gruposnap_home_hero_hide_highlights_widget', 8, 2);
add_action('wp_head', 'gruposnap_home_hero_reveal_style', 3);
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_home_hero_assets', 999);
add_action('elementor/frontend/widget/before_render', 'gruposnap_home_hero_disable_widget_entrance_animation', 1);
add_action('elementor/frontend/widget/before_render', 'gruposnap_home_hero_remove_invisible_render_attr', 99);
add_filter('elementor/widget/render_content', 'gruposnap_home_hero_strip_invisible_class', 5, 2);
add_filter('elementor/frontend/the_content', 'gruposnap_home_hero_fix_section_html', 99);
add_action('wp_head', 'gruposnap_home_hero_reveal_script', 4);
