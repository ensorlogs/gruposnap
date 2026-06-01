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
        if (
            method_exists($current, 'get_id')
            && in_array(
                $current->get_id(),
                array(
                    GRUPOSNAP_HOME_HERO_SECTION_ID,
                    'a5d8162',
                    '47df02b',
                ),
                true
            )
        ) {
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

    // Solo el bloque móvil (47df02b): quitar hidden-desktop/tablet para que el HTML en caché se vea en teléfono.
    $html = preg_replace_callback(
        '#(<section\b[^>]*\belementor-element-47df02b\b[^>]*\bclass=")([^"]*)(")#i',
        static function (array $m): string {
            $classes = preg_replace(
                '/\belementor-hidden-(?:desktop|tablet|tablet_extra|laptop|widescreen)\b/',
                '',
                $m[2]
            ) ?? $m[2];

            return $m[1] . trim(preg_replace('/\s+/', ' ', $classes) ?? $classes) . $m[3];
        },
        $html
    ) ?? $html;

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
    $fixed = gruposnap_home_hero_patch_media(
        gruposnap_home_hero_strip_animation_settings($hero_html)
    );

    return substr($content, 0, $section_start) . $fixed . substr($content, $section_end);
}

/**
 * Elementor: a5d8162 = desktop/tablet, 47df02b = solo móvil. Ajustamos el bloque móvil al renderizar.
 *
 * @param \Elementor\Element_Base $element
 */
function gruposnap_home_hero_prepare_section($element): void
{
    if (!gruposnap_is_home_page() || !method_exists($element, 'get_id')) {
        return;
    }

    $id = $element->get_id();

    if ($id === '47df02b') {
        $element->set_settings('hide_mobile', '');
        $element->set_settings('hide_desktop', 'hidden-desktop');
        $element->set_settings('hide_tablet', 'hidden-tablet');

        foreach (
            array(
                'elementor-hidden-desktop',
                'elementor-hidden-tablet',
            ) as $class
        ) {
            $element->remove_render_attribute('_wrapper', 'class', $class);
        }
    }

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
    @media (max-width: 1024px) {
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> {
            margin-top: 0 !important;
            overflow: visible !important;
        }
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> > .elementor-container {
            z-index: 5 !important;
            position: relative !important;
            min-height: 480px !important;
        }
    }
    @media (max-width: 767px) {
        .elementor.elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162 {
            display: none !important;
        }
        .elementor.elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-47df02b,
        .elementor.elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-47df02b.elementor-hidden-desktop,
        .elementor.elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-47df02b.elementor-hidden-tablet,
        body.home .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-47df02b,
        body.page-id-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-47df02b {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        .elementor-751 .elementor-section.elementor-element-47df02b.wdt-section-wrap-col > .elementor-container,
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-47df02b > .elementor-container {
            flex-flow: column nowrap !important;
            display: flex !important;
        }
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-47df02b > .elementor-container > .elementor-column {
            flex: 0 0 100% !important;
            max-width: 100% !important;
            width: 100% !important;
        }
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-f428155,
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-f428155 .wdt-heading-holder {
            visibility: visible !important;
            opacity: 1 !important;
            color: #111 !important;
        }
    }
    @media (min-width: 768px) and (max-width: 1024px) {
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-47df02b {
            display: none !important;
        }
        .elementor.elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162 {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162 > .elementor-container {
            flex-flow: column nowrap !important;
            display: flex !important;
        }
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162 > .elementor-container > .elementor-column {
            flex: 0 0 100% !important;
            max-width: 100% !important;
            width: 100% !important;
        }
    }
    .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-invisible,
    .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .animated,
    .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-8082631 .wdt-heading-holder,
    .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-f428155 .wdt-heading-holder {
        visibility: visible !important;
        opacity: 1 !important;
        animation: none !important;
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

        function stackHeroSection(section) {
            if (!section) {
                return;
            }

            section.classList.remove('elementor-hidden-desktop', 'elementor-hidden-tablet');
            section.style.setProperty('display', 'block', 'important');
            section.style.setProperty('visibility', 'visible', 'important');
            section.style.setProperty('opacity', '1', 'important');

            var row = section.querySelector(':scope > .elementor-container');
            if (row) {
                row.style.setProperty('display', 'flex', 'important');
                row.style.setProperty('flex-flow', 'column nowrap', 'important');
                row.style.setProperty('flex-direction', 'column', 'important');
            }

            section.querySelectorAll(':scope > .elementor-container > .elementor-column').forEach(function (col) {
                col.style.setProperty('flex', '0 0 100%', 'important');
                col.style.setProperty('max-width', '100%', 'important');
                col.style.setProperty('width', '100%', 'important');
                col.style.setProperty('min-width', '0', 'important');
            });
        }

        function forceMobileHeroLayout(hero) {
            if (!hero) {
                return;
            }

            var main = hero.querySelector(':scope > .elementor-container');
            if (main) {
                main.style.setProperty('position', 'relative', 'important');
                main.style.setProperty('z-index', '5', 'important');
            }

            hero.querySelectorAll('.elementor-background-video-container, .elementor-background-overlay').forEach(function (el) {
                el.style.setProperty('z-index', '0', 'important');
            });

            var desktop = hero.querySelector('.elementor-element-a5d8162');
            var mobile = hero.querySelector('.elementor-element-47df02b');

            if (window.innerWidth <= 767) {
                if (desktop) {
                    desktop.style.setProperty('display', 'none', 'important');
                }
                stackHeroSection(mobile);
            } else if (window.innerWidth <= 1024) {
                if (mobile) {
                    mobile.style.setProperty('display', 'none', 'important');
                }
                stackHeroSection(desktop);
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

            forceMobileHeroLayout(hero);

            hero.querySelectorAll(
                '.elementor-element-8082631, .elementor-element-f428155, .gruposnap-cta--whatsapp, .gruposnap-cta--secondary, .wdt-heading-holder, .wdt-button-holder'
            ).forEach(function (el) {
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

        window.addEventListener('resize', function () {
            var hero = document.querySelector(sel);
            if (hero) {
                forceMobileHeroLayout(hero);
            }
        });
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
add_action('elementor/frontend/section/before_render', 'gruposnap_home_hero_prepare_section', 1);
add_filter('elementor/widget/render_content', 'gruposnap_home_hero_hide_highlights_widget', 8, 2);
add_action('wp_head', 'gruposnap_home_hero_reveal_style', 99);
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_home_hero_assets', 9999);
add_action('elementor/frontend/widget/before_render', 'gruposnap_home_hero_disable_widget_entrance_animation', 1);
add_action('elementor/frontend/widget/before_render', 'gruposnap_home_hero_remove_invisible_render_attr', 99);
add_filter('elementor/widget/render_content', 'gruposnap_home_hero_strip_invisible_class', 5, 2);
add_filter('elementor/frontend/the_content', 'gruposnap_home_hero_fix_section_html', 99);
add_action('wp_head', 'gruposnap_home_hero_reveal_script', 4);
