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
function gruposnap_inject_single_post_title(string $html): string
{
    if (!is_singular('post') || strpos($html, 'gruposnap-article-head') !== false) {
        return $html;
    }

    $header = gruposnap_single_post_title_markup();
    if ($header === '') {
        return $html;
    }

    $replaced = preg_replace(
        '/(<div class="single-entry-body">)/',
        '$1' . $header,
        $html,
        1
    );

    return is_string($replaced) ? $replaced : $html;
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

    $overrides = array(
        'minimal/parts/author.php'  => 'author.php',
        'minimal/parts/comment.php' => 'comment.php',
    );

    $normalized = str_replace('\\', '/', $file_path);

    foreach ($overrides as $needle => $file) {
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
