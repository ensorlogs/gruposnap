<?php
/**
 * GrupoSnap — legal, cookies y accesibilidad (estilo ensorlogs.com).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Correo único para avisos legales, privacidad y cookies. */
const GRUPOSNAP_LEGAL_CONTACT_EMAIL = 'contacto@gruposnap.com';

/**
 * @return string
 */
function gruposnap_legal_contact_email(): string
{
    return (string) apply_filters('gruposnap_legal_contact_email', GRUPOSNAP_LEGAL_CONTACT_EMAIL);
}

/**
 * @return string
 */
function gruposnap_legal_seed_html(string $slug): string
{
    $file = get_stylesheet_directory() . '/seed-html/legal/' . $slug . '.html';
    if (!is_readable($file)) {
        return '';
    }

    $html = (string) file_get_contents($file);
    $home = trailingslashit(home_url());

    return str_replace(
        array('href="/legal/', "href='/legal/"),
        array('href="' . $home . 'legal/', "href='" . $home . 'legal/'),
        $html
    );
}

/**
 * Sincroniza el contenido de las páginas /legal/* desde los HTML semilla.
 */
function gruposnap_sync_legal_pages_from_seed(): void
{
    $version = get_option('gruposnap_legal_content_version');
    if ($version === GRUPOSNAP_THEME_VERSION) {
        return;
    }

    $slugs = array_keys(gruposnap_legal_cross_labels());
    foreach ($slugs as $slug) {
        $page = get_page_by_path('legal/' . $slug);
        $body = gruposnap_legal_seed_html($slug);
        if (!$page || $body === '') {
            continue;
        }

        wp_update_post(
            array(
                'ID'           => (int) $page->ID,
                'post_content' => $body,
            )
        );
    }

    update_option('gruposnap_legal_content_version', GRUPOSNAP_THEME_VERSION, false);
}

/**
 * @return array<string, string>
 */
function gruposnap_legal_cross_labels(): array
{
    return array(
        'aviso-legal'   => __('Aviso legal', 'gruposnap'),
        'privacidad'    => __('Privacidad', 'gruposnap'),
        'cookies'       => __('Cookies', 'gruposnap'),
        'accesibilidad' => __('Accesibilidad', 'gruposnap'),
    );
}

/**
 * Crea páginas legales bajo /legal/ (idempotente).
 */
function gruposnap_seed_legal_pages(): void
{
    $parent = get_page_by_path('legal');
    if (!$parent) {
        $pid = wp_insert_post(
            array(
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_title'   => __('Legal', 'gruposnap'),
                'post_name'    => 'legal',
                'post_content' => '<p>' . esc_html__('Documentos legales y de transparencia de Grupo Snap Publicidad.', 'gruposnap') . '</p>',
            ),
            true
        );
        $parent_id = is_wp_error($pid) ? 0 : (int) $pid;
    } else {
        $parent_id = (int) $parent->ID;
    }

    if ($parent_id <= 0) {
        return;
    }

    $pages = array(
        'aviso-legal' => array(
            'title' => __('Aviso legal y condiciones de uso', 'gruposnap'),
            'lead'  => __('Información del titular del sitio, condiciones de uso y marco legal aplicable.', 'gruposnap'),
        ),
        'privacidad' => array(
            'title' => __('Política de privacidad', 'gruposnap'),
            'lead'  => __('Qué datos personales tratamos, con qué finalidad, durante cuánto tiempo y qué derechos puedes ejercer.', 'gruposnap'),
        ),
        'cookies' => array(
            'title' => __('Política de cookies', 'gruposnap'),
            'lead'  => __('Qué cookies y tecnologías similares usamos y cómo puedes gestionarlas.', 'gruposnap'),
        ),
        'accesibilidad' => array(
            'title' => __('Declaración de accesibilidad', 'gruposnap'),
            'lead'  => __('Compromiso de accesibilidad, medidas aplicadas y cómo reportar barreras.', 'gruposnap'),
        ),
    );

    foreach ($pages as $slug => $meta) {
        $existing = get_page_by_path('legal/' . $slug);
        if ($existing) {
            if (get_post_meta($existing->ID, '_wp_page_template', true) !== 'page-legal.php') {
                update_post_meta($existing->ID, '_wp_page_template', 'page-legal.php');
            }
            continue;
        }

        $body = gruposnap_legal_seed_html($slug);
        if ($body === '') {
            $body = '<p>' . esc_html($meta['title']) . '</p>';
        }

        $new_id = wp_insert_post(
            array(
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_title'   => $meta['title'],
                'post_name'    => $slug,
                'post_parent'  => $parent_id,
                'post_content' => $body,
                'post_excerpt' => $meta['lead'],
            ),
            true
        );

        if (!is_wp_error($new_id) && $new_id) {
            update_post_meta((int) $new_id, '_wp_page_template', 'page-legal.php');
        }
    }
}

add_action('init', 'gruposnap_seed_legal_pages', 20);
add_action('init', 'gruposnap_sync_legal_pages_from_seed', 21);
add_filter(
    'gruposnap_legal_contact_email',
    static function (): string {
        return GRUPOSNAP_LEGAL_CONTACT_EMAIL;
    }
);

/**
 * URL de la política de cookies para el banner.
 */
function gruposnap_cookies_policy_url(): string
{
    $page = get_page_by_path('legal/cookies');
    return $page ? get_permalink($page) : home_url('/legal/cookies/');
}

/**
 * @return array<string, string>
 */
function gruposnap_legal_footer_links(): array
{
    $links = array();
    foreach (gruposnap_legal_cross_labels() as $slug => $label) {
        $page = get_page_by_path('legal/' . $slug);
        if ($page) {
            $links[$slug] = array(
                'label' => $label,
                'url'   => get_permalink($page),
            );
        }
    }
    return $links;
}

/**
 * Meta para el script de cookies.
 */
function gruposnap_compliance_head_meta(): void
{
    if (is_admin()) {
        return;
    }
    echo '<meta name="gruposnap-cookies-url" content="' . esc_url(gruposnap_cookies_policy_url()) . '">' . "\n";
}

add_action('wp_head', 'gruposnap_compliance_head_meta', 2);

/**
 * Encola CSS/JS de cumplimiento en el front.
 */
function gruposnap_enqueue_compliance_assets(): void
{
    if (is_admin()) {
        return;
    }

    $uri = get_stylesheet_directory_uri();
    $ver = GRUPOSNAP_THEME_VERSION;

    wp_enqueue_style(
        'gruposnap-a11y',
        $uri . '/assets/css/gruposnap-a11y.css',
        array('gruposnap-child', 'printme-theme'),
        $ver
    );
    wp_enqueue_style(
        'gruposnap-cookies',
        $uri . '/assets/css/gruposnap-cookies.css',
        array('gruposnap-child'),
        $ver
    );

    wp_enqueue_script(
        'gruposnap-a11y',
        $uri . '/assets/js/gruposnap-a11y.js',
        array(),
        $ver,
        true
    );
    wp_enqueue_script(
        'gruposnap-cookies',
        $uri . '/assets/js/gruposnap-cookies.js',
        array(),
        $ver,
        true
    );

    if (is_page() && is_page_template('page-legal.php')) {
        wp_enqueue_style(
            'gruposnap-legal',
            $uri . '/assets/css/gruposnap-legal.css',
            array('gruposnap-child'),
            $ver
        );
    }
}

add_action('wp_enqueue_scripts', 'gruposnap_enqueue_compliance_assets', 110);

/**
 * Fila de enlaces legales tras el footer Elementor.
 */
function gruposnap_render_legal_footer_row(): void
{
    if (is_admin()) {
        return;
    }

    $links = gruposnap_legal_footer_links();
    if (!$links) {
        return;
    }
    ?>
    <div id="gsnap-legal-row">
        <nav aria-label="<?php esc_attr_e('Enlaces legales', 'gruposnap'); ?>">
            <?php foreach ($links as $item) : ?>
                <a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a>
            <?php endforeach; ?>
            <a href="#preferencias-cookies" class="gsnap-cookies-reopen" role="button" data-gsnap-cookies-open>
                <?php esc_html_e('Preferencias de cookies', 'gruposnap'); ?>
            </a>
        </nav>
    </div>
    <?php
}

add_action('wp_footer', 'gruposnap_render_legal_footer_row', 15);

/**
 * Clase de cuerpo en páginas legales.
 *
 * @param string[] $classes
 * @return string[]
 */
function gruposnap_legal_body_class(array $classes): array
{
    if (is_page() && is_page_template('page-legal.php')) {
        $classes[] = 'gruposnap-legal-document';
    }
    return $classes;
}

add_filter('body_class', 'gruposnap_legal_body_class');
