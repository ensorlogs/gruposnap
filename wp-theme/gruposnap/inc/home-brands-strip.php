<?php
/**
 * Home hero — título sobre el carrusel de marcas (bfd27b4).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

const GRUPOSNAP_HOME_BRANDS_CAROUSEL_ID = 'bfd27b4';

/**
 * @return bool
 */
function gruposnap_should_show_brands_strip_heading(): bool
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
function gruposnap_brands_strip_widget_class($widget): void
{
    if (!gruposnap_should_show_brands_strip_heading() || $widget->get_id() !== GRUPOSNAP_HOME_BRANDS_CAROUSEL_ID) {
        return;
    }

    $widget->add_render_attribute('_wrapper', 'class', 'gruposnap-brands-strip__widget');
}

/**
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_brands_strip_wrap_content(string $content, $widget): string
{
    if (!gruposnap_should_show_brands_strip_heading() || $widget->get_id() !== GRUPOSNAP_HOME_BRANDS_CAROUSEL_ID) {
        return $content;
    }

    if (str_contains($content, 'gruposnap-brands-strip')) {
        return $content;
    }

    $title = sprintf(
        '<p class="gruposnap-brands-strip__title">%s</p>',
        esc_html__('Las mejores marcas trabajan con nosotros', 'gruposnap')
    );

    return '<div class="gruposnap-brands-strip">' . $title . '<div class="gruposnap-brands-strip__carousel">' . $content . '</div></div>';
}

function gruposnap_enqueue_home_brands_strip_styles(): void
{
    if (!gruposnap_should_show_brands_strip_heading()) {
        return;
    }

    $deps = array('gruposnap-child', 'gruposnap-home-hero');
    foreach (array('elementor-post-751', 'elementor-frontend') as $handle) {
        if (wp_style_is($handle, 'registered') || wp_style_is($handle, 'enqueued')) {
            $deps[] = $handle;
        }
    }

    wp_enqueue_style(
        'gruposnap-home-brands-strip',
        get_stylesheet_directory_uri() . '/assets/css/home-brands-strip.css',
        array_values(array_unique($deps)),
        GRUPOSNAP_THEME_VERSION
    );
}

function gruposnap_home_brands_strip_bust_elementor_cache(): void
{
    $version = get_option('gruposnap_home_brands_strip_cache_version');
    if ($version === GRUPOSNAP_THEME_VERSION) {
        return;
    }

    delete_post_meta(751, '_elementor_element_cache');
    update_option('gruposnap_home_brands_strip_cache_version', GRUPOSNAP_THEME_VERSION, false);
}

/**
 * Carrusel de marcas en loop continuo (marquee).
 */
function gruposnap_home_brands_strip_marquee_script(): void
{
    if (!gruposnap_should_show_brands_strip_heading()) {
        return;
    }

    $widget_id = esc_js(GRUPOSNAP_HOME_BRANDS_CAROUSEL_ID);
    ?>
    <script id="gruposnap-brands-strip-marquee">
    (function () {
        function buildMarquee() {
            var root = document.querySelector('.elementor-element-<?php echo $widget_id; ?> .gruposnap-brands-strip__carousel');
            if (!root) {
                return;
            }

            var carousel = root.querySelector('.elementor-image-carousel-wrapper');
            if (!carousel || carousel.classList.contains('gruposnap-brands-marquee')) {
                return;
            }

            var wrapper = carousel.querySelector('.swiper-wrapper');
            if (!wrapper) {
                return;
            }

            var slides = Array.from(wrapper.querySelectorAll('.swiper-slide'));
            if (slides.length < 2) {
                return;
            }

            if (carousel.swiper && typeof carousel.swiper.destroy === 'function') {
                carousel.swiper.destroy(true, true);
            }

            var track = document.createElement('div');
            track.className = 'gruposnap-brands-marquee__track';
            track.setAttribute('aria-hidden', 'true');

            function appendSet() {
                slides.forEach(function (slide) {
                    var item = slide.cloneNode(true);
                    item.classList.add('gruposnap-brands-marquee__slide');
                    item.classList.remove('swiper-slide-active', 'swiper-slide-next', 'swiper-slide-prev');
                    item.removeAttribute('role');
                    item.removeAttribute('aria-label');
                    track.appendChild(item);
                });
            }

            appendSet();
            appendSet();

            carousel.className = 'elementor-image-carousel-wrapper gruposnap-brands-marquee';
            carousel.removeAttribute('role');
            carousel.innerHTML = '';
            carousel.appendChild(track);
        }

        buildMarquee();
        document.addEventListener('DOMContentLoaded', buildMarquee);
        window.addEventListener('load', buildMarquee);

        if (window.jQuery) {
            window.jQuery(window).on('elementor/frontend/init', function () {
                window.setTimeout(buildMarquee, 120);
            });
        }
    })();
    </script>
    <?php
}

function gruposnap_home_brands_strip_dom_fallback(): void
{
    if (!gruposnap_should_show_brands_strip_heading()) {
        return;
    }

    $widget_id = esc_js(GRUPOSNAP_HOME_BRANDS_CAROUSEL_ID);
    $label     = wp_json_encode(__('Las mejores marcas trabajan con nosotros', 'gruposnap'));
    ?>
    <script id="gruposnap-brands-strip-fallback">
    (function () {
        function patchBrandsStrip() {
            var widget = document.querySelector('.elementor-element-<?php echo $widget_id; ?>');
            if (!widget || widget.querySelector('.gruposnap-brands-strip__title')) {
                return;
            }

            var container = widget.querySelector('.elementor-widget-container');
            if (!container || !container.innerHTML.trim()) {
                return;
            }

            var carouselHtml = container.innerHTML;
            container.innerHTML =
                '<div class="gruposnap-brands-strip">' +
                '<p class="gruposnap-brands-strip__title">' + <?php echo $label; ?> + '</p>' +
                '<div class="gruposnap-brands-strip__carousel">' + carouselHtml + '</div>' +
                '</div>';

            widget.classList.add('gruposnap-brands-strip__widget');
        }

        patchBrandsStrip();
        document.addEventListener('DOMContentLoaded', patchBrandsStrip);
    })();
    </script>
    <?php
}

add_action('init', 'gruposnap_home_brands_strip_bust_elementor_cache', 1);
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_home_brands_strip_styles', 1000);
add_action('elementor/widget/before_render_content', 'gruposnap_brands_strip_widget_class', 6);
add_filter('elementor/widget/render_content', 'gruposnap_brands_strip_wrap_content', 12, 2);
add_action('wp_footer', 'gruposnap_home_brands_strip_dom_fallback', 8);
add_action('wp_footer', 'gruposnap_home_brands_strip_marquee_script', 13);
