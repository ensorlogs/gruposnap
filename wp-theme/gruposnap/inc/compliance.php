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
    $lang   = function_exists('gruposnap_current_lang') ? gruposnap_current_lang() : 'es';
    $suffix = $lang === 'en' ? '.en' : '';
    $file   = get_stylesheet_directory() . '/seed-html/legal/' . $slug . $suffix . '.html';
    if (!is_readable($file)) {
        $file = get_stylesheet_directory() . '/seed-html/legal/' . $slug . '.html';
    }
    if (!is_readable($file)) {
        return '';
    }

    $html = (string) file_get_contents($file);
    $base = function_exists('gruposnap_lang_url') ? gruposnap_lang_url('/legal/') : trailingslashit(home_url('legal/'));

    return str_replace(
        array('href="/legal/', "href='/legal/"),
        array('href="' . $base, "href='" . $base),
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
    if (function_exists('gruposnap_lang_url')) {
        return gruposnap_lang_url('/legal/cookies/');
    }

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
                'url'   => function_exists('gruposnap_lang_url')
                    ? gruposnap_lang_url('/legal/' . $slug . '/')
                    : get_permalink($page),
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
 * Icono SVG del FAB de WhatsApp.
 */
function gruposnap_whatsapp_fab_icon_svg(): string
{
    return '<svg viewBox="0 0 448 512" aria-hidden="true" focusable="false">'
        . '<path fill="currentColor" d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>'
        . '</svg>';
}

/**
 * URL del FAB de WhatsApp (mismo enlace que el CTA de presupuesto).
 */
function gruposnap_whatsapp_fab_url(): string
{
    if (function_exists('gruposnap_mobile_menu_whatsapp_url')) {
        return gruposnap_mobile_menu_whatsapp_url();
    }

    return gruposnap_header_quote_url();
}

/**
 * Botón flotante de WhatsApp encima del de accesibilidad.
 */
function gruposnap_render_whatsapp_fab(): void
{
    if (is_admin()) {
        return;
    }

    $url = esc_url(gruposnap_whatsapp_fab_url());
    ?>
    <a
        class="gsnap-whatsapp-fab"
        href="<?php echo $url; ?>"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="<?php esc_attr_e('Escríbenos por WhatsApp', 'gruposnap'); ?>"
    >
        <?php echo gruposnap_whatsapp_fab_icon_svg(); ?>
    </a>
    <?php
}

add_action('wp_footer', 'gruposnap_render_whatsapp_fab', 5);

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
 * URL del crédito de desarrollo en el pie legal.
 */
function gruposnap_developer_credit_url(): string
{
    return (string) apply_filters('gruposnap_developer_credit_url', 'https://ensorlogs.com/');
}

/**
 * Etiqueta del crédito de desarrollo (enlace).
 */
function gruposnap_developer_credit_label(): string
{
    return (string) apply_filters('gruposnap_developer_credit_label', 'EnsorLOGS');
}

/**
 * Enlace a la sección Nosotros desde el pie legal.
 */
function gruposnap_legal_nosotros_url(): string
{
    $url = function_exists('gruposnap_lang_url') ? gruposnap_lang_url('/#nosotros') : home_url('/#nosotros');

    return (string) apply_filters('gruposnap_legal_nosotros_url', $url);
}

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
            <div class="gsnap-legal-row__links">
                <?php foreach ($links as $item) : ?>
                    <a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a>
                <?php endforeach; ?>
                <a href="#preferencias-cookies" class="gsnap-cookies-reopen" role="button" data-gsnap-cookies-open>
                    <?php esc_html_e('Preferencias de cookies', 'gruposnap'); ?>
                </a>
            </div>
            <p class="gsnap-legal-row__credit">
                <?php
                echo esc_html__('Desarrollador con Amor', 'gruposnap');
                echo ' ';
                ?>
                <span class="gsnap-legal-row__heart" aria-hidden="true">♥</span>
                <?php
                echo ' ' . esc_html__('por', 'gruposnap') . ' ';
                ?>
                <a href="<?php echo esc_url(gruposnap_legal_nosotros_url()); ?>">
                    <?php esc_html_e('Nosotros', 'gruposnap'); ?>
                </a>
            </p>
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
