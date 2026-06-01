<?php
/**
 * Home — últimos posts de Instagram (@gruposnap) debajo del blog.
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Sección Elementor «Ideas y tendencias» en la home. */
const GRUPOSNAP_HOME_BLOG_SECTION_ID = '98071b4';

/**
 * @return string
 */
function gruposnap_instagram_username(): string
{
    return (string) apply_filters('gruposnap_instagram_username', 'gruposnap');
}

/**
 * @return string
 */
function gruposnap_instagram_profile_url(): string
{
    $user = gruposnap_instagram_username();

    return (string) apply_filters('gruposnap_instagram_profile_url', 'https://www.instagram.com/' . rawurlencode($user) . '/');
}

/**
 * @return int Segundos de caché del feed.
 */
function gruposnap_instagram_cache_ttl(): int
{
    return (int) apply_filters('gruposnap_instagram_cache_ttl', HOUR_IN_SECONDS);
}

/**
 * @return string
 */
function gruposnap_instagram_app_id(): string
{
    return (string) apply_filters('gruposnap_instagram_app_id', '936619743392459');
}

/**
 * @return bool
 */
function gruposnap_should_show_instagram_feed(): bool
{
    if (!function_exists('gruposnap_is_home_front') || !gruposnap_is_home_front()) {
        return false;
    }

    if (is_admin()) {
        return false;
    }

    if (class_exists('\Elementor\Plugin')) {
        $plugin = \Elementor\Plugin::$instance;
        if ($plugin->editor && $plugin->editor->is_edit_mode()) {
            return false;
        }
        if ($plugin->preview && $plugin->preview->is_preview_mode()) {
            return false;
        }
    }

    return true;
}

/**
 * @return array<int, array{permalink:string,image:string,caption:string}>
 */
function gruposnap_instagram_get_posts(): array
{
    $manual = apply_filters('gruposnap_instagram_posts', null);
    if (is_array($manual) && $manual !== array()) {
        return array_slice(array_values($manual), 0, 3);
    }

    $cached = get_transient(gruposnap_instagram_transient_key());
    if (is_array($cached)) {
        return $cached;
    }

    $posts = gruposnap_instagram_fetch_posts_from_api();
    if ($posts !== array()) {
        set_transient(gruposnap_instagram_transient_key(), $posts, gruposnap_instagram_cache_ttl());
    }

    return $posts;
}

/**
 * @return string
 */
function gruposnap_instagram_transient_key(): string
{
    return 'gruposnap_ig_' . sanitize_key(gruposnap_instagram_username());
}

/**
 * @return array<int, array{permalink:string,image:string,caption:string}>
 */
function gruposnap_instagram_fetch_posts_from_api(): array
{
    $username = gruposnap_instagram_username();
    $url      = sprintf(
        'https://www.instagram.com/api/v1/users/web_profile_info/?username=%s',
        rawurlencode($username)
    );

    $response = wp_remote_get(
        $url,
        array(
            'timeout' => 15,
            'headers' => array(
                'User-Agent'      => 'Mozilla/5.0 (compatible; GrupoSnap/1.0; WordPress)',
                'X-IG-App-ID'     => gruposnap_instagram_app_id(),
                'Accept-Language' => 'es-ES,es;q=0.9',
            ),
        )
    );

    if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
        return array();
    }

    $body = json_decode((string) wp_remote_retrieve_body($response), true);
    if (!is_array($body)) {
        return array();
    }

    $edges = $body['data']['user']['edge_owner_to_timeline_media']['edges'] ?? null;
    if (!is_array($edges)) {
        return array();
    }

    $posts = array();

    foreach (array_slice($edges, 0, 3) as $edge) {
        if (!is_array($edge) || empty($edge['node']) || !is_array($edge['node'])) {
            continue;
        }

        $node      = $edge['node'];
        $shortcode = isset($node['shortcode']) ? (string) $node['shortcode'] : '';
        $image     = isset($node['display_url']) ? (string) $node['display_url'] : '';

        if ($shortcode === '' || $image === '') {
            continue;
        }

        $caption = '';
        if (!empty($node['edge_media_to_caption']['edges'][0]['node']['text'])) {
            $caption = (string) $node['edge_media_to_caption']['edges'][0]['node']['text'];
        }

        $posts[] = array(
            'permalink' => 'https://www.instagram.com/p/' . rawurlencode($shortcode) . '/',
            'image'     => $image,
            'caption'   => $caption,
        );
    }

    return $posts;
}

/**
 * Icono Instagram inline (SVG).
 */
function gruposnap_instagram_icon_svg(): string
{
    return '<svg class="gruposnap-instagram__icon-svg" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">'
        . '<path fill="currentColor" d="M12 2.163c3.204 0 3.584.012 4.85.07 1.17.054 1.97.24 2.427.403a4.92 4.92 0 0 1 1.77 1.153 4.92 4.92 0 0 1 1.153 1.77c.163.457.349 1.257.403 2.427.058 1.266.07 1.646.07 4.85s-.012 3.584-.07 4.85c-.054 1.17-.24 1.97-.403 2.427a4.92 4.92 0 0 1-1.153 1.77 4.92 4.92 0 0 1-1.77 1.153c-.457.163-1.257.349-2.427.403-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.17-.054-1.97-.24-2.427-.403a4.92 4.92 0 0 1-1.77-1.153 4.92 4.92 0 0 1-1.153-1.77c-.163-.457-.349-1.257-.403-2.427C2.175 15.747 2.163 15.367 2.163 12s.012-3.584.07-4.85c.054-1.17.24-1.97.403-2.427a4.92 4.92 0 0 1 1.153-1.77 4.92 4.92 0 0 1 1.77-1.153c.457-.163 1.257-.349 2.427-.403C8.416 2.175 8.796 2.163 12 2.163zm0-2.163C8.741 0 8.333.014 7.053.072 5.775.13 4.905.333 4.14.63a6.98 6.98 0 0 0-2.126 1.384A6.98 6.98 0 0 0 .63 4.14C.333 4.905.13 5.775.072 7.053.014 8.333 0 8.741 0 12c0 3.259.014 3.667.072 4.947.058 1.278.261 2.148.558 2.913a6.98 6.98 0 0 0 1.384 2.126 6.98 6.98 0 0 0 2.126 1.384c.765.297 1.635.5 2.913.558C8.333 23.986 8.741 24 12 24c3.259 0 3.667-.014 4.947-.072 1.278-.058 2.148-.261 2.913-.558a6.98 6.98 0 0 0 2.126-1.384 6.98 6.98 0 0 0 1.384-2.126c.297-.765.5-1.635.558-2.913.058-1.28.072-1.688.072-4.947 0-3.259-.014-3.667-.072-4.947-.058-1.278-.261-2.148-.558-2.913a6.98 6.98 0 0 0-1.384-2.126A6.98 6.98 0 0 0 19.86.63c-.765-.297-1.635-.5-2.913-.558C15.667.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/>'
        . '</svg>';
}

/**
 * Imprime la sección completa.
 */
function gruposnap_render_instagram_section(): void
{
    $posts       = gruposnap_instagram_get_posts();
    $profile_url = esc_url(gruposnap_instagram_profile_url());
    $username    = esc_html(gruposnap_instagram_username());

    echo '<section class="gruposnap-instagram" id="instagram" aria-labelledby="gruposnap-instagram-title">';
    echo '<div class="gruposnap-instagram__inner">';

    echo '<header class="gruposnap-instagram__head">';
    echo '<p class="gruposnap-instagram__eyebrow">';
    echo gruposnap_instagram_icon_svg();
    echo '<span>@' . $username . '</span>';
    echo '</p>';
    echo '<h2 class="gruposnap-instagram__title" id="gruposnap-instagram-title">';
    esc_html_e('Síguenos en Instagram', 'gruposnap');
    echo '</h2>';
    echo '<p class="gruposnap-instagram__subtitle">';
    esc_html_e('Merch, activaciones y publicidad en República Dominicana — contenido fresco cada semana.', 'gruposnap');
    echo '</p>';
    echo '</header>';

    if ($posts !== array()) {
        echo '<ul class="gruposnap-instagram__grid">';
        foreach ($posts as $index => $post) {
            $permalink = esc_url($post['permalink'] ?? $profile_url);
            $image     = esc_url($post['image'] ?? '');
            $caption   = isset($post['caption']) ? wp_strip_all_tags((string) $post['caption']) : '';

            if ($image === '') {
                continue;
            }

            echo '<li class="gruposnap-instagram__item">';
            echo '<a class="gruposnap-instagram__card" href="' . $permalink . '" target="_blank" rel="noopener noreferrer">';
            echo '<span class="gruposnap-instagram__media">';
            echo '<img src="' . $image . '" alt="" loading="lazy" decoding="async" width="600" height="600" />';
            echo '<span class="gruposnap-instagram__overlay" aria-hidden="true">';
            echo gruposnap_instagram_icon_svg();
            echo '<span class="gruposnap-instagram__overlay-text">' . esc_html__('Ver en Instagram', 'gruposnap') . '</span>';
            echo '</span>';
            echo '</span>';
            if ($caption !== '') {
                echo '<span class="gruposnap-instagram__caption">' . esc_html(wp_trim_words($caption, 14, '…')) . '</span>';
            }
            echo '</a>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p class="gruposnap-instagram__fallback">';
        echo esc_html__('Visita nuestro perfil para ver las últimas publicaciones.', 'gruposnap');
        echo '</p>';
    }

    echo '<div class="gruposnap-instagram__cta-wrap">';
    echo '<a class="gruposnap-instagram__cta" href="' . $profile_url . '" target="_blank" rel="noopener noreferrer">';
    echo gruposnap_instagram_icon_svg();
    echo '<span>' . esc_html(sprintf(/* translators: %s: Instagram username */ __('Seguir @%s', 'gruposnap'), gruposnap_instagram_username())) . '</span>';
    echo '</a>';
    echo '</div>';

    echo '</div>';
    echo '</section>';
}

/**
 * Inserta la sección tras «Ideas y tendencias».
 *
 * @param \Elementor\Element_Base $element
 */
function gruposnap_instagram_after_blog_section($element): void
{
    if (!gruposnap_should_show_instagram_feed()) {
        return;
    }

    if ($element->get_name() !== 'section' || $element->get_id() !== GRUPOSNAP_HOME_BLOG_SECTION_ID) {
        return;
    }

    static $done = false;
    if ($done) {
        return;
    }

    $done = true;
    gruposnap_render_instagram_section();
}

/**
 * Respaldo si la sección está en HTML cacheado sin hooks.
 */
function gruposnap_instagram_dom_insert_script(): void
{
    if (!gruposnap_should_show_instagram_feed()) {
        return;
    }

    ob_start();
    gruposnap_render_instagram_section();
    $html = (string) ob_get_clean();

    if ($html === '') {
        return;
    }
    ?>
    <script>
    (function () {
        var blog = document.querySelector('.elementor-751 .elementor-element-<?php echo esc_js(GRUPOSNAP_HOME_BLOG_SECTION_ID); ?>');
        if (!blog || document.getElementById('instagram')) {
            return;
        }
        var wrap = document.createElement('div');
        wrap.innerHTML = <?php echo wp_json_encode($html); ?>;
        var section = wrap.firstElementChild;
        if (section) {
            blog.insertAdjacentElement('afterend', section);
        }
    })();
    </script>
    <?php
}

function gruposnap_enqueue_instagram_styles(): void
{
    if (!gruposnap_should_show_instagram_feed()) {
        return;
    }

    wp_enqueue_style(
        'gruposnap-home-instagram',
        get_stylesheet_directory_uri() . '/assets/css/home-instagram.css',
        array('gruposnap-child', 'gruposnap-home-blog'),
        GRUPOSNAP_THEME_VERSION
    );
}

add_action('wp_enqueue_scripts', 'gruposnap_enqueue_instagram_styles', 115);
add_action('elementor/frontend/section/after_render', 'gruposnap_instagram_after_blog_section', 15);
add_action('wp_footer', 'gruposnap_instagram_dom_insert_script', 10);
