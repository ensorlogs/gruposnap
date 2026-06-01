<?php
/**
 * GrupoSnap — lectura y layout de entradas de blog.
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return bool
 */
function gruposnap_is_blog_context(): bool
{
    return is_singular('post') || is_home() || is_category() || is_tag() || is_date() || (is_archive() && get_post_type() === 'post');
}

/**
 * Encola estilos de blog tras los del padre.
 */
function gruposnap_enqueue_blog_styles(): void
{
    if (!gruposnap_is_blog_context()) {
        return;
    }

    $deps = array('gruposnap-child');

    foreach (array('printme-post-minimal', 'printme-post', 'wdt-blog-css', 'wdt-blog-archive-classic-css') as $handle) {
        if (wp_style_is($handle, 'registered') || wp_style_is($handle, 'enqueued')) {
            $deps[] = $handle;
        }
    }

    wp_enqueue_style(
        'gruposnap-blog',
        get_stylesheet_directory_uri() . '/assets/css/blog.css',
        array_values(array_unique($deps)),
        GRUPOSNAP_THEME_VERSION
    );
}

add_action('wp_enqueue_scripts', 'gruposnap_enqueue_blog_styles', 100);

/**
 * HTML de cabecera con H1 para entradas.
 *
 * @return string
 */
function gruposnap_single_post_title_markup(): string
{
    $title = get_the_title();
    if ($title === '') {
        return '';
    }

    return sprintf(
        '<header class="gruposnap-article-head"><h1 class="gruposnap-article-title">%s</h1></header>',
        esc_html($title)
    );
}

/**
 * Inserta el H1 al inicio del cuerpo (Elementor no siempre pasa por the_content).
 *
 * @param string $html
 * @return string
 */
function gruposnap_normalize_title_for_compare(string $text): string
{
    $text = wp_strip_all_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = remove_accents($text);
    $text = strtolower($text);
    $text = preg_replace('/\s+/', ' ', $text);

    return is_string($text) ? trim($text) : '';
}

/**
 * Quita títulos Elementor duplicados del contenido del post.
 *
 * @param string $html
 */
function gruposnap_strip_duplicate_elementor_titles(string $html): string
{
    if (!is_singular('post') || $html === '') {
        return $html;
    }

    $post_title = gruposnap_normalize_title_for_compare(get_the_title());
    if ($post_title === '') {
        return $html;
    }

    $stripped = 0;
    $max_strip  = 3;

    return preg_replace_callback(
        '/<div class="elementor-element[^"]*\belementor-widget-(?:wdt-)?heading\b[^"]*"[^>]*>[\s\S]*?<\/div>/',
        static function (array $match) use ($post_title, &$stripped, $max_strip): string {
            if ($stripped >= $max_strip) {
                return $match[0];
            }

            $heading_text = gruposnap_normalize_title_for_compare($match[0]);
            if ($heading_text === '') {
                return $match[0];
            }

            similar_text($post_title, $heading_text, $percent);
            $contains = str_contains($heading_text, $post_title) || str_contains($post_title, $heading_text);

            $title_words = preg_split('/\s+/', $post_title) ?: array();
            $prefix      = implode(' ', array_slice($title_words, 0, min(7, count($title_words))));
            $shared_prefix = $prefix !== '' && strlen($prefix) >= 24
                && str_starts_with($heading_text, $prefix);

            if ($percent >= 72 || $contains || $shared_prefix) {
                ++$stripped;
                return '';
            }

            return $match[0];
        },
        $html
    ) ?? $html;
}

function gruposnap_inject_single_post_title(string $html): string
{
    if (!is_singular('post')) {
        return $html;
    }

    if (strpos($html, 'single-entry-body') !== false && strpos($html, 'gruposnap-article-head') === false) {
        $header = gruposnap_single_post_title_markup();
        if ($header !== '') {
            $replaced = preg_replace(
                '/(<div class="single-entry-body">)/',
                '$1' . $header,
                $html,
                1
            );
            if (is_string($replaced)) {
                $html = $replaced;
            }
        }
    }

    return gruposnap_strip_duplicate_elementor_titles($html);
}

add_filter('printme_single_post_dynamic_template_part', 'gruposnap_inject_single_post_title', 20);

/**
 * Plantillas de meta del post en español (tema hijo).
 *
 * @param string $file_path
 * @return string
 */
function gruposnap_override_post_template_parts(string $file_path): string
{
    if ($file_path === '') {
        return $file_path;
    }

    $part_overrides = array(
        'minimal/parts/author.php'  => 'author.php',
        'minimal/parts/comment.php' => 'comment.php',
    );

    $normalized = str_replace('\\', '/', $file_path);

    if (substr($normalized, -strlen('minimal/post.php')) === 'minimal/post.php') {
        $post_template = get_stylesheet_directory() . '/templates/post/minimal/post.php';
        if (is_file($post_template)) {
            return $post_template;
        }
    }

    foreach ($part_overrides as $needle => $file) {
        if (substr($normalized, -strlen($needle)) !== $needle) {
            continue;
        }

        $override = get_stylesheet_directory() . '/templates/post/minimal/parts/' . $file;
        if (is_file($override)) {
            return $override;
        }
    }

    return $file_path;
}

add_filter('printme_get_template_plugin_part', 'gruposnap_override_post_template_parts', 30, 1);

/**
 * Clases de cuerpo para estilos de blog.
 *
 * @param string[] $classes
 * @return string[]
 */
function gruposnap_blog_body_class(array $classes): array
{
    if (is_singular('post')) {
        $classes[] = 'gruposnap-blog-single';
    }

    if (is_home() || is_category() || is_tag() || is_date() || (is_archive() && get_post_type() === 'post')) {
        $classes[] = 'gruposnap-blog-archive';
    }

    return $classes;
}

add_filter('body_class', 'gruposnap_blog_body_class');

/** Botones «See all Blogs» en la home (Elementor 751) — desktop y móvil. */
const GRUPOSNAP_HOME_SEE_ALL_BLOG_WIDGET_IDS = array('d1800c7', 'c5d3e4f');

/** Carrusel de entradas en home. */
const GRUPOSNAP_HOME_BLOG_POSTS_WIDGET_IDS = array('bf2da19', '1984522');

/**
 * @return bool
 */
function gruposnap_is_home_front(): bool
{
    if (is_admin()) {
        return false;
    }

    $page_id = (int) (get_queried_object_id() ?: get_the_ID());

    return $page_id === 751 || (is_front_page() && (int) get_option('page_on_front') === 751);
}

/**
 * Oculta el CTA «See all Blogs» bajo el carrusel (deja espacio extra).
 *
 * @param string                  $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_hide_home_see_all_blogs_button(string $content, $widget): string
{
    if (!gruposnap_is_home_front() || $widget->get_name() !== 'wdt-button') {
        return $content;
    }

    if (!in_array($widget->get_id(), GRUPOSNAP_HOME_SEE_ALL_BLOG_WIDGET_IDS, true)) {
        return $content;
    }

    return '';
}

/**
 * Estilos home: sin hueco del botón eliminado.
 */
function gruposnap_enqueue_home_blog_layout_styles(): void
{
    if (!gruposnap_is_home_front()) {
        return;
    }

    $deps = array('gruposnap-child');
    foreach (array('wdt-blog-css', 'elementor-post-751', 'elementor-frontend') as $handle) {
        if (wp_style_is($handle, 'registered') || wp_style_is($handle, 'enqueued')) {
            $deps[] = $handle;
        }
    }

    wp_enqueue_style(
        'gruposnap-home-blog',
        get_stylesheet_directory_uri() . '/assets/css/home-blog.css',
        array_values(array_unique($deps)),
        GRUPOSNAP_THEME_VERSION
    );
}

function gruposnap_home_blog_bust_elementor_cache(): void
{
    $version = get_option('gruposnap_home_blog_cache_version');
    if ($version === GRUPOSNAP_THEME_VERSION) {
        return;
    }

    delete_post_meta(751, '_elementor_element_cache');
    update_option('gruposnap_home_blog_cache_version', GRUPOSNAP_THEME_VERSION, false);
}

add_action('init', 'gruposnap_home_blog_bust_elementor_cache', 1);

/**
 * Quita fecha y botón Read More de las tarjetas del blog en home.
 *
 * @param string                  $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_home_blog_strip_card_meta(string $content, $widget): string
{
    if (!gruposnap_is_home_front() || $widget->get_name() !== 'wdt-blog-posts') {
        return $content;
    }

    if (!in_array($widget->get_id(), GRUPOSNAP_HOME_BLOG_POSTS_WIDGET_IDS, true)) {
        return $content;
    }

    $content = preg_replace('/<div class="entry-date">[\s\S]*?<\/div><!-- Entry Date -->/', '', $content);
    $content = preg_replace('/<!-- Entry Button -->[\s\S]*?<!-- Entry Button -->/', '', $content);
    $content = preg_replace('/<div class="entry-button[^"]*">[\s\S]*?<\/div>/', '', $content);

    return is_string($content) ? $content : '';
}

/**
 * En móvil, anula posiciones de Isotope que dejan las tarjetas pegadas a la izquierda.
 */
function gruposnap_home_blog_mobile_layout_fallback(): void
{
    if (!gruposnap_is_home_front()) {
        return;
    }
    ?>
    <script id="gruposnap-home-blog-mobile-layout">
    (function () {
        var mq = window.matchMedia('(max-width: 767px)');

        function resetBlogIsotope() {
            if (!mq.matches) {
                return;
            }

            document
                .querySelectorAll('.elementor-751 .elementor-element-98071b4 .tpl-blog-holder.apply-isotope .wdt-post-entry')
                .forEach(function (entry) {
                    entry.style.position = 'relative';
                    entry.style.left = 'auto';
                    entry.style.right = 'auto';
                    entry.style.top = 'auto';
                    entry.style.transform = 'none';
                    entry.style.width = '100%';
                    entry.style.maxWidth = '100%';
                    entry.style.marginLeft = 'auto';
                    entry.style.marginRight = 'auto';
                });

            document
                .querySelectorAll('.elementor-751 .elementor-element-98071b4 .tpl-blog-holder.apply-isotope')
                .forEach(function (holder) {
                    holder.style.height = 'auto';
                    holder.style.width = '100%';
                });
        }

        resetBlogIsotope();
        document.addEventListener('DOMContentLoaded', resetBlogIsotope);
        window.addEventListener('load', resetBlogIsotope);
        mq.addEventListener('change', resetBlogIsotope);
    })();
    </script>
    <?php
}

add_filter('elementor/widget/render_content', 'gruposnap_hide_home_see_all_blogs_button', 20, 2);
add_filter('elementor/widget/render_content', 'gruposnap_home_blog_strip_card_meta', 18, 2);
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_home_blog_layout_styles', 110);
add_action('wp_footer', 'gruposnap_home_blog_mobile_layout_fallback', 12);
