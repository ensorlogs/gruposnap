<?php
/**
 * Menú móvil Printme — logo arriba y contacto (WhatsApp + correo) abajo.
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

function gruposnap_header_logo_url(): string
{
    $logo_id = (int) get_theme_mod('custom_logo');
    if ($logo_id) {
        $url = wp_get_attachment_image_url($logo_id, 'medium');
        if (is_string($url) && $url !== '') {
            return (string) apply_filters('gruposnap_header_logo_url', $url);
        }
    }

    return (string) apply_filters('gruposnap_header_logo_url', '');
}

function gruposnap_mobile_menu_whatsapp_url(): string
{
    return (string) apply_filters('gruposnap_mobile_menu_whatsapp_url', gruposnap_header_quote_url());
}

function gruposnap_mobile_menu_email(): string
{
    if (function_exists('gruposnap_footer_office_rd_email')) {
        return (string) apply_filters('gruposnap_mobile_menu_email', gruposnap_footer_office_rd_email());
    }

    if (function_exists('gruposnap_legal_contact_email')) {
        return (string) apply_filters('gruposnap_mobile_menu_email', gruposnap_legal_contact_email());
    }

    return (string) apply_filters('gruposnap_mobile_menu_email', 'contacto@gruposnap.com');
}

/**
 * @param string $icon whatsapp|email
 */
function gruposnap_mobile_menu_icon_svg(string $icon): string
{
    switch ($icon) {
        case 'whatsapp':
            return '<svg viewBox="0 0 448 512" width="20" height="20" aria-hidden="true" focusable="false"><path fill="currentColor" d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>';
        case 'email':
            return '<svg viewBox="0 0 512 512" width="20" height="20" aria-hidden="true" focusable="false"><path fill="currentColor" d="M48 64C21.5 64 0 85.5 0 112v288c0 26.5 21.5 48 48 48h416c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H48zm0 48h416c8.8 0 16 7.2 16 16v11.2l-208 130-208-130V128c0-8.8 7.2-16 16-16zm0 96.8L250.5 320.8c6.1 3.8 14.1 3.8 20.2 0L464 208.8V400c0 8.8-7.2 16-16 16H48c-8.8 0-16-7.2-16-16V208.8z"/></svg>';
        default:
            return '';
    }
}

function gruposnap_render_mobile_menu_extras_template(): void
{
    if (is_admin()) {
        return;
    }

    $logo_url  = gruposnap_header_logo_url();
    $home_url  = esc_url(home_url('/'));
    $whatsapp  = esc_url(gruposnap_mobile_menu_whatsapp_url());
    $email     = gruposnap_mobile_menu_email();
    $email_esc = esc_attr($email);
    $email_lbl = esc_html($email);
    ?>
    <div id="gruposnap-mobile-menu-template" class="gruposnap-mobile-menu__template" hidden>
        <div class="gruposnap-mobile-menu__brand" data-logo-fallback="<?php echo esc_attr($logo_url); ?>">
            <a class="gruposnap-mobile-menu__logo-link" href="<?php echo $home_url; ?>">
                <?php if ($logo_url !== '') : ?>
                    <img class="gruposnap-mobile-menu__logo" src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" width="180" height="52" decoding="async" />
                <?php else : ?>
                    <img class="gruposnap-mobile-menu__logo" src="" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" width="180" height="52" decoding="async" />
                <?php endif; ?>
            </a>
        </div>
        <div class="gruposnap-mobile-menu__contact">
            <h3 class="gruposnap-mobile-menu__heading"><?php esc_html_e('Contacto', 'gruposnap'); ?></h3>
            <a class="gruposnap-mobile-menu__btn gruposnap-mobile-menu__btn--whatsapp" href="<?php echo $whatsapp; ?>" target="_blank" rel="noopener noreferrer">
                <?php echo gruposnap_mobile_menu_icon_svg('whatsapp'); ?>
                <span><?php esc_html_e('WhatsApp', 'gruposnap'); ?></span>
            </a>
            <a class="gruposnap-mobile-menu__btn gruposnap-mobile-menu__btn--email" href="mailto:<?php echo $email_esc; ?>">
                <?php echo gruposnap_mobile_menu_icon_svg('email'); ?>
                <span><?php echo $email_lbl; ?></span>
            </a>
        </div>
    </div>
    <?php
}

function gruposnap_enqueue_mobile_menu_assets(): void
{
    if (is_admin()) {
        return;
    }

    wp_enqueue_style(
        'gruposnap-header-mobile-menu',
        get_stylesheet_directory_uri() . '/assets/css/header-mobile-menu.css',
        array('gruposnap-header-mobile'),
        GRUPOSNAP_THEME_VERSION
    );

    wp_enqueue_script(
        'gruposnap-header-mobile-menu',
        get_stylesheet_directory_uri() . '/assets/js/header-mobile-menu.js',
        array('jquery', 'printme-jqcustom'),
        GRUPOSNAP_THEME_VERSION,
        true
    );
}

add_action('wp_footer', 'gruposnap_render_mobile_menu_extras_template', 4);
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_mobile_menu_assets', 125);
