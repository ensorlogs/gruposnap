<?php
/**
 * GrupoSnap — idioma ES / EN (prefijo /en/ en URLs).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

define('GRUPOSNAP_LANG_QUERY_VAR', 'gruposnap_lang');
define('GRUPOSNAP_I18N_REWRITE_OPTION', 'gruposnap_i18n_rewrite_ver');

/**
 * Ruta relativa al home de WordPress (p. ej. /en/legal/cookies/).
 */
function gruposnap_i18n_relative_path(): string
{
    $request = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '/';
    $path    = (string) wp_parse_url($request, PHP_URL_PATH);
    if ($path === '') {
        $path = '/';
    }

    $home_path = (string) wp_parse_url(home_url('/'), PHP_URL_PATH);
    $home_path = $home_path !== '' ? untrailingslashit($home_path) : '';
    if ($home_path !== '' && str_starts_with($path, $home_path)) {
        $path = substr($path, strlen($home_path)) ?: '/';
    }

    $path = '/' . ltrim($path, '/');

    return $path === '/' ? '/' : rtrim($path, '/') . '/';
}

/**
 * ¿La URL actual está bajo /en/?
 */
function gruposnap_i18n_uri_is_en(): bool
{
    $path = gruposnap_i18n_relative_path();

    return $path === '/en/' || str_starts_with($path, '/en/');
}

/**
 * Registra query var y reglas de reescritura.
 */
function gruposnap_i18n_register(): void
{
    add_rewrite_tag('%' . GRUPOSNAP_LANG_QUERY_VAR . '%', '([^&]+)');

    add_rewrite_rule('^en/?$', 'index.php?' . GRUPOSNAP_LANG_QUERY_VAR . '=en', 'top');
    add_rewrite_rule('^en/legal/([^/]+)/?$', 'index.php?pagename=legal/$matches[1]&' . GRUPOSNAP_LANG_QUERY_VAR . '=en', 'top');
    add_rewrite_rule('^en/([^/]+)/?$', 'index.php?pagename=$matches[1]&' . GRUPOSNAP_LANG_QUERY_VAR . '=en', 'top');
}
add_action('init', 'gruposnap_i18n_register', 10);

/**
 * Fuerza gruposnap_lang=en en cualquier petición bajo /en/ (antes de la consulta).
 *
 * @param WP $wp
 */
function gruposnap_i18n_parse_request_lang(WP $wp): void
{
    if (!gruposnap_i18n_uri_is_en()) {
        return;
    }
    $wp->query_vars[GRUPOSNAP_LANG_QUERY_VAR] = 'en';
}
add_action('parse_request', 'gruposnap_i18n_parse_request_lang', 1);

/**
 * Evita que redirect_canonical mande /en/ → /.
 *
 * @param string|false $redirect_url
 * @param string       $requested_url
 * @return string|false
 */
function gruposnap_i18n_disable_canonical_redirect($redirect_url, string $requested_url)
{
    unset($requested_url);
    if (gruposnap_i18n_uri_is_en()) {
        return false;
    }
    if (get_query_var(GRUPOSNAP_LANG_QUERY_VAR) === 'en') {
        return false;
    }

    return $redirect_url;
}
add_filter('redirect_canonical', 'gruposnap_i18n_disable_canonical_redirect', 0, 2);

/**
 * Canonical correcto en rutas /en/.
 *
 * @param string|false $canonical_url
 */
function gruposnap_i18n_canonical_url($canonical_url)
{
    if (!gruposnap_i18n_uri_is_en() && get_query_var(GRUPOSNAP_LANG_QUERY_VAR) !== 'en') {
        return $canonical_url;
    }
    $rel = gruposnap_i18n_relative_path();
    if ($rel === '/') {
        return $canonical_url;
    }

    return trailingslashit(home_url($rel));
}
add_filter('wp_get_canonical_url', 'gruposnap_i18n_canonical_url', 10, 1);

/**
 * Refresca permalinks cuando cambia la versión del tema.
 */
function gruposnap_i18n_maybe_flush_rewrites(): void
{
    if (!function_exists('get_option') || !defined('GRUPOSNAP_THEME_VERSION')) {
        return;
    }
    $stored = (string) get_option(GRUPOSNAP_I18N_REWRITE_OPTION, '');
    if ($stored === GRUPOSNAP_THEME_VERSION) {
        return;
    }
    flush_rewrite_rules(false);
    update_option(GRUPOSNAP_I18N_REWRITE_OPTION, GRUPOSNAP_THEME_VERSION);
}
add_action('init', 'gruposnap_i18n_maybe_flush_rewrites', 99);

/**
 * @param string[] $vars
 * @return string[]
 */
function gruposnap_i18n_query_vars(array $vars): array
{
    $vars[] = GRUPOSNAP_LANG_QUERY_VAR;

    return $vars;
}
add_filter('query_vars', 'gruposnap_i18n_query_vars');

/**
 * Resuelve ID de página o entrada desde ruta /en/...
 */
function gruposnap_i18n_resolve_singular_id_from_path(string $rel_path): int
{
    $rel_path = '/' . trim($rel_path, '/');
    if ($rel_path === '/en' || $rel_path === '/en/') {
        if (get_option('show_on_front') === 'page') {
            return (int) get_option('page_on_front');
        }

        return 0;
    }

    if (!str_starts_with($rel_path, '/en/')) {
        return 0;
    }

    $sub = trim(substr($rel_path, 4), '/');
    if ($sub === '') {
        return (int) get_option('page_on_front');
    }

    $page = get_page_by_path($sub);
    if ($page instanceof WP_Post) {
        return (int) $page->ID;
    }

    $posts = get_posts(
        array(
            'name'           => $sub,
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
        )
    );
    if ($posts) {
        return (int) $posts[0];
    }

    return 0;
}

/**
 * Asigna page_id a /en/ (portada) antes de la consulta principal.
 *
 * @param array<string, mixed> $query_vars
 * @return array<string, mixed>
 */
function gruposnap_i18n_request(array $query_vars): array
{
    $lang = isset($query_vars[GRUPOSNAP_LANG_QUERY_VAR]) ? (string) $query_vars[GRUPOSNAP_LANG_QUERY_VAR] : '';
    if ($lang !== 'en') {
        return $query_vars;
    }

    if (!empty($query_vars['pagename']) || !empty($query_vars['page_id']) || !empty($query_vars['name'])) {
        return $query_vars;
    }

    if (get_option('show_on_front') === 'page') {
        $front = (int) get_option('page_on_front');
        if ($front > 0) {
            $query_vars['page_id'] = $front;
        }
    }

    return $query_vars;
}
add_filter('request', 'gruposnap_i18n_request');

/**
 * Evita 404 en /en/ si los permalinks no se regeneraron o la consulta falló.
 *
 * @param bool     $preempt
 * @param WP_Query $wp_query
 */
function gruposnap_i18n_pre_handle_404(bool $preempt, WP_Query $wp_query): bool
{
    if ($preempt || !$wp_query->is_main_query()) {
        return $preempt;
    }

    $rel = gruposnap_i18n_relative_path();
    if (!str_starts_with($rel, '/en')) {
        return $preempt;
    }

    $object_id = gruposnap_i18n_resolve_singular_id_from_path($rel);
    if ($object_id <= 0) {
        return $preempt;
    }

    $post = get_post($object_id);
    if (!$post instanceof WP_Post) {
        return $preempt;
    }

    if ($post->post_type === 'page') {
        $wp_query->query(
            array(
                'page_id'                => $object_id,
                GRUPOSNAP_LANG_QUERY_VAR => 'en',
            )
        );
        $wp_query->is_page       = true;
        $wp_query->is_singular   = true;
        $wp_query->is_front_page = (
            get_option('show_on_front') === 'page'
            && $object_id === (int) get_option('page_on_front')
        );
    } else {
        $wp_query->query(
            array(
                'p'                      => $object_id,
                GRUPOSNAP_LANG_QUERY_VAR => 'en',
            )
        );
        $wp_query->is_single   = true;
        $wp_query->is_singular = true;
    }

    $wp_query->is_404      = false;
    $wp_query->is_archive  = false;
    $wp_query->is_home     = false;

    return true;
}
add_filter('pre_handle_404', 'gruposnap_i18n_pre_handle_404', 10, 2);

/**
 * Idioma activo: es | en.
 */
function gruposnap_current_lang(): string
{
    $lang = get_query_var(GRUPOSNAP_LANG_QUERY_VAR);
    if ($lang === 'en') {
        return 'en';
    }
    if (gruposnap_i18n_uri_is_en()) {
        return 'en';
    }

    return 'es';
}

/**
 * Traducción inline (sin .mo) para cadenas del tema.
 */
function gruposnap_t(string $es, string $en): string
{
    return gruposnap_current_lang() === 'en' ? $en : $es;
}

/**
 * URL con prefijo /en/ si corresponde.
 *
 * @param string $path Ruta relativa, p. ej. /contact/ o /legal/cookies/.
 */
function gruposnap_lang_url(string $path = '/'): string
{
    $path = '/' . ltrim($path, '/');
    if (gruposnap_current_lang() === 'en') {
        if ($path === '/') {
            return trailingslashit(home_url('/en'));
        }

        return home_url('/en' . $path);
    }

    return home_url($path);
}

/**
 * URL de la versión en el otro idioma (para el conmutador).
 */
function gruposnap_lang_alternate_url(): string
{
    $lang = gruposnap_current_lang();
    $path = gruposnap_i18n_relative_path();

    if ($lang === 'en') {
        if (str_starts_with($path, '/en/')) {
            $path = substr($path, 3) ?: '/';
        } elseif ($path === '/en/') {
            $path = '/';
        }

        return home_url($path === '/' ? '/' : $path);
    }

    if ($path === '/') {
        return trailingslashit(home_url('/en'));
    }

    return home_url('/en' . $path);
}

/**
 * Meta idioma + alternativa para gruposnap-lang-switch.js
 */
function gruposnap_i18n_head_meta(): void
{
    if (is_admin()) {
        return;
    }

    $lang        = gruposnap_current_lang();
    $alt         = gruposnap_lang_alternate_url();
    $assets_base = trailingslashit(get_stylesheet_directory_uri()) . 'assets';
    echo '<meta name="gruposnap-lang" content="' . esc_attr($lang) . '">' . "\n";
    echo '<meta name="gruposnap-lang-alt" content="' . esc_url($alt) . '">' . "\n";
    echo '<meta name="gruposnap-assets-base" content="' . esc_attr($assets_base) . '">' . "\n";
}
add_action('wp_head', 'gruposnap_i18n_head_meta', 2);

/**
 * Atributo lang en <html>.
 *
 * @param string $output
 */
function gruposnap_i18n_language_attributes(string $output): string
{
    if (gruposnap_current_lang() === 'en') {
        return preg_replace('/lang="[^"]*"/', 'lang="en-US"', $output) ?: 'lang="en-US"';
    }

    return $output;
}
add_filter('language_attributes', 'gruposnap_i18n_language_attributes');

/**
 * Enlaces hreflang básicos en páginas estáticas y portada.
 */
function gruposnap_i18n_hreflang(): void
{
    if (!is_page() && !is_front_page() && !is_singular('post')) {
        return;
    }

    $alt = gruposnap_lang_alternate_url();

    if (gruposnap_current_lang() === 'en') {
        $here = trailingslashit(home_url(gruposnap_i18n_relative_path()));
    } elseif (is_singular()) {
        $here = get_permalink();
    } else {
        $here = home_url('/');
    }
    if (!is_string($here) || $here === '') {
        $here = gruposnap_current_lang() === 'en' ? trailingslashit(home_url('/en')) : home_url('/');
    }
    if (gruposnap_current_lang() === 'en') {
        echo '<link rel="alternate" hreflang="en" href="' . esc_url($here) . '">' . "\n";
        echo '<link rel="alternate" hreflang="es" href="' . esc_url($alt) . '">' . "\n";
    } else {
        echo '<link rel="alternate" hreflang="es" href="' . esc_url($here) . '">' . "\n";
        echo '<link rel="alternate" hreflang="en" href="' . esc_url($alt) . '">' . "\n";
    }
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url(home_url('/')) . '">' . "\n";
}
add_action('wp_head', 'gruposnap_i18n_hreflang', 4);

/**
 * Encola conmutador ES/EN.
 */
function gruposnap_enqueue_lang_switch_assets(): void
{
    if (is_admin()) {
        return;
    }

    $uri  = get_stylesheet_directory_uri();
    $ver  = GRUPOSNAP_THEME_VERSION;
    $deps = array('gruposnap-child');

    foreach (array('printme-parent', 'gruposnap-a11y') as $handle) {
        if (wp_style_is($handle, 'registered') || wp_style_is($handle, 'enqueued')) {
            $deps[] = $handle;
        }
    }

    wp_enqueue_style(
        'gruposnap-lang-switch',
        $uri . '/assets/css/gruposnap-lang-switch.css',
        $deps,
        $ver
    );
    wp_enqueue_script(
        'gruposnap-lang-switch',
        $uri . '/assets/js/gruposnap-lang-switch.js',
        array(),
        $ver,
        true
    );
}
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_lang_switch_assets', 9998);

/**
 * Carga dominio de traducción del tema hijo.
 */
function gruposnap_load_textdomain(): void
{
    load_child_theme_textdomain('gruposnap', get_stylesheet_directory() . '/languages');
}
add_action('after_setup_theme', 'gruposnap_load_textdomain');
