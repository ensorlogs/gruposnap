<?php
/**
 * Home — sección «Sobre GrupoSnap» (legibilidad sobre degradado).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

const GRUPOSNAP_HOME_ABOUT_SECTION_ID = '46cb44b';
const GRUPOSNAP_HOME_ABOUT_COLUMN_ID  = '7332389';
const GRUPOSNAP_HOME_ABOUT_HEADING_ID      = 'f92bae7';
const GRUPOSNAP_HOME_ABOUT_PHONE_WIDGET_ID = 'a233653';
const GRUPOSNAP_HOME_ABOUT_EMAIL_WIDGET_ID = 'b9714b1';
const GRUPOSNAP_HOME_ABOUT_PHOTO_COLUMN_ID = 'fd7205c';
const GRUPOSNAP_HOME_EXPERIENCE_WIDGET_IDS = array('2454fd0', '95e0f84');

/**
 * @return bool
 */
function gruposnap_should_style_home_about(): bool
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
 * Párrafo introductorio de la sección Nosotros (sin texto adicional de Elementor).
 *
 * @return string
 */
function gruposnap_home_about_intro_text(): string
{
    return (string) apply_filters(
        'gruposnap_home_about_intro_text',
        'Más de 20 años impulsando marcas a nivel internacional. En GrupoSnap combinamos diseño, producción y ejecución para crear soluciones publicitarias que conectan con los clientes y generan impacto real.'
    );
}

/**
 * @return string
 */
function gruposnap_home_about_intro_markup(): string
{
    return '<div class="wdt-heading-content-wrapper gruposnap-about-intro">'
        . '<p class="gruposnap-about-intro__text">' . esc_html(gruposnap_home_about_intro_text()) . '</p>'
        . '</div>';
}

/**
 * @return string
 */
function gruposnap_home_about_phone(): string
{
    return (string) apply_filters('gruposnap_home_about_phone', '+1 (809) 865-4576');
}

/**
 * @return string
 */
function gruposnap_home_about_email(): string
{
    if (function_exists('gruposnap_legal_contact_email')) {
        return (string) apply_filters('gruposnap_home_about_email', gruposnap_legal_contact_email());
    }

    return (string) apply_filters('gruposnap_home_about_email', 'contacto@gruposnap.com');
}

/**
 * @return string
 */
function gruposnap_home_about_contact_bar_markup(): string
{
    return '<div class="gruposnap-about-contact__bar" role="heading" aria-level="3">'
        . esc_html__('CONTÁCTANOS', 'gruposnap')
        . '</div>';
}

/**
 * Envuelve teléfono + email bajo el párrafo en Nosotros.
 *
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_home_about_contact_wrap_start(string $content, $widget): string
{
    if (!gruposnap_should_style_home_about() || $widget->get_id() !== GRUPOSNAP_HOME_ABOUT_PHONE_WIDGET_ID) {
        return $content;
    }

    if (str_contains($content, 'gruposnap-about-contact')) {
        return $content;
    }

    return '<div class="gruposnap-about-contact" id="gruposnap-about-contact">'
        . gruposnap_home_about_contact_bar_markup()
        . '<div class="gruposnap-about-contact__grid">' . $content;
}

/**
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_home_about_contact_wrap_end(string $content, $widget): string
{
    if (!gruposnap_should_style_home_about() || $widget->get_id() !== GRUPOSNAP_HOME_ABOUT_EMAIL_WIDGET_ID) {
        return $content;
    }

    return $content . '</div></div>';
}

/**
 * Corrige enlaces y textos de los icon-box de contacto.
 *
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_home_about_contact_content(string $content, $widget): string
{
    if (!gruposnap_should_style_home_about()) {
        return $content;
    }

    $id = $widget->get_id();

    if ($id === GRUPOSNAP_HOME_ABOUT_PHONE_WIDGET_ID) {
        $content = preg_replace(
            '/>\s*\+?1?\s*\(?809\)?[^<]*865[^<]*4576\s*</',
            '>' . esc_html(gruposnap_home_about_phone()) . '<',
            $content,
            1
        ) ?? $content;
        $content = preg_replace(
            '/href="tel:[^"]*"/',
            'href="tel:+18098654576"',
            $content,
            1
        ) ?? $content;
    }

    if ($id === GRUPOSNAP_HOME_ABOUT_EMAIL_WIDGET_ID) {
        $email = gruposnap_home_about_email();
        $content = preg_replace(
            '/href="mailto:[^"]*"/',
            'href="mailto:' . esc_attr($email) . '"',
            $content,
            1
        ) ?? $content;
        $content = preg_replace(
            '/>[^<]*@[^<]*</',
            '>' . esc_html($email) . '<',
            $content,
            1
        ) ?? $content;
    }

    return $content;
}

/**
 * HTML del encabezado «20+ Años · Impulsando Marcas» (sustituye «Sobre GrupoSnap»).
 *
 * @return string
 */
function gruposnap_home_experience_badge_markup(): string
{
    return '<div class="gruposnap-experience__head" role="heading" aria-level="2">'
        . '<div class="gruposnap-experience__stat">'
        . '<span class="gruposnap-experience__number">20+</span>'
        . '<span class="gruposnap-experience__years">'
        . esc_html__('Años', 'gruposnap')
        . '</span>'
        . '</div>'
        . '<p class="gruposnap-experience__tagline">'
        . '<span class="gruposnap-experience__tagline-line">'
        . esc_html__('Impulsando', 'gruposnap')
        . '</span>'
        . '<span class="gruposnap-experience__tagline-line">'
        . esc_html__('Marcas', 'gruposnap')
        . '</span>'
        . '</p>'
        . '</div>';
}

/**
 * Sustituye el título «Sobre GrupoSnap» por el badge de experiencia.
 *
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_home_about_heading_content(string $content, $widget): string
{
    if (!gruposnap_should_style_home_about() || $widget->get_id() !== GRUPOSNAP_HOME_ABOUT_HEADING_ID) {
        return $content;
    }

    if (str_contains($content, 'gruposnap-experience__head')) {
        return $content;
    }

    $replaced = preg_replace(
        '/<h1[^>]*wdt-heading-title-wrapper[^>]*>[\s\S]*?<\/h1>/',
        gruposnap_home_experience_badge_markup(),
        $content,
        1
    );

    if (!is_string($replaced)) {
        return $content;
    }

    return str_replace(
        'class="wdt-heading-holder "',
        'class="wdt-heading-holder gruposnap-about-heading"',
        $replaced
    );
}

/**
 * Deja solo el párrafo introductorio en el cuerpo del heading Nosotros.
 *
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_home_about_intro_content(string $content, $widget): string
{
    if (!gruposnap_should_style_home_about() || $widget->get_id() !== GRUPOSNAP_HOME_ABOUT_HEADING_ID) {
        return $content;
    }

    if (str_contains($content, 'gruposnap-about-intro')) {
        return $content;
    }

    $replaced = preg_replace(
        '/<div class="wdt-heading-content-wrapper"[^>]*>[\s\S]*?<\/div>/',
        gruposnap_home_about_intro_markup(),
        $content,
        1
    );

    return is_string($replaced) && $replaced !== '' ? $replaced : $content;
}

/**
 * Corrige el HTML del badge y deja «Años» en su propia línea.
 *
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_home_experience_heading_content(string $content, $widget): string
{
    if (!gruposnap_should_style_home_about() || $widget->get_name() !== 'wdt-heading') {
        return $content;
    }

    if (!in_array($widget->get_id(), GRUPOSNAP_HOME_EXPERIENCE_WIDGET_IDS, true)) {
        return $content;
    }

    $fixed = preg_replace(
        '/<span class="wdt-heading-title">20 \+<\/span>Años<\/span>/',
        '<span class="wdt-heading-title gruposnap-experience__number">20+</span><span class="gruposnap-experience__years">Años</span>',
        $content
    );

    return is_string($fixed) ? $fixed : $content;
}

function gruposnap_enqueue_home_about_styles(): void
{
    if (!gruposnap_should_style_home_about()) {
        return;
    }

    $deps = array('gruposnap-child', 'gruposnap-home-headings');
    foreach (array('elementor-post-751', 'elementor-frontend') as $handle) {
        if (wp_style_is($handle, 'registered') || wp_style_is($handle, 'enqueued')) {
            $deps[] = $handle;
        }
    }

    wp_enqueue_style(
        'gruposnap-home-about',
        get_stylesheet_directory_uri() . '/assets/css/home-about.css',
        array_values(array_unique($deps)),
        GRUPOSNAP_THEME_VERSION
    );
}

function gruposnap_home_about_bust_elementor_cache(): void
{
    $version = get_option('gruposnap_home_about_cache_version');
    if ($version === GRUPOSNAP_THEME_VERSION) {
        return;
    }

    delete_post_meta(751, '_elementor_element_cache');
    update_option('gruposnap_home_about_cache_version', GRUPOSNAP_THEME_VERSION, false);
}

function gruposnap_home_experience_dom_fallback(): void
{
    if (!gruposnap_should_style_home_about()) {
        return;
    }
    ?>
    <script id="gruposnap-experience-badge-fallback">
    (function () {
        var introHtml = <?php echo wp_json_encode(gruposnap_home_about_intro_markup()); ?>;

        function patchAboutIntro() {
            var about = document.querySelector('.elementor-element-<?php echo esc_js(GRUPOSNAP_HOME_ABOUT_HEADING_ID); ?> .wdt-heading-holder');
            if (!about) {
                return;
            }

            var body = about.querySelector('.wdt-heading-content-wrapper');
            if (!body) {
                return;
            }

            if (!body.classList.contains('gruposnap-about-intro')) {
                body.outerHTML = introHtml;
            } else {
                var p = body.querySelector('.gruposnap-about-intro__text, p');
                if (p) {
                    p.textContent = <?php echo wp_json_encode(gruposnap_home_about_intro_text()); ?>;
                }
            }
        }

        function patchAboutContact() {
            var phone = document.querySelector('.elementor-element-<?php echo esc_js(GRUPOSNAP_HOME_ABOUT_PHONE_WIDGET_ID); ?>');
            var email = document.querySelector('.elementor-element-<?php echo esc_js(GRUPOSNAP_HOME_ABOUT_EMAIL_WIDGET_ID); ?>');
            if (!phone || !email || document.getElementById('gruposnap-about-contact')) {
                return;
            }

            var wrap = document.createElement('div');
            wrap.className = 'gruposnap-about-contact';
            wrap.id = 'gruposnap-about-contact';
            wrap.innerHTML = <?php echo wp_json_encode(gruposnap_home_about_contact_bar_markup()); ?>
                + '<div class="gruposnap-about-contact__grid"></div>';
            var grid = wrap.querySelector('.gruposnap-about-contact__grid');
            phone.parentNode.insertBefore(wrap, phone);
            grid.appendChild(phone);
            grid.appendChild(email);
        }

        function patchAboutTitle() {
            var about = document.querySelector('.elementor-element-<?php echo esc_js(GRUPOSNAP_HOME_ABOUT_HEADING_ID); ?> .wdt-heading-holder');
            if (!about || about.querySelector('.gruposnap-experience__head')) {
                return;
            }

            var title = about.querySelector('h1.wdt-heading-title-wrapper, .wdt-heading-title-wrapper');
            if (!title || title.textContent.indexOf('GrupoSnap') === -1) {
                return;
            }

                title.outerHTML = <?php echo wp_json_encode(gruposnap_home_experience_badge_markup()); ?>;
            about.classList.add('gruposnap-about-heading');
        }

        function patchExperienceBadge() {
            document.querySelectorAll('.wdt-about-experience .wdt-heading-title-wrapper').forEach(function (wrap) {
                if (wrap.querySelector('.gruposnap-experience__number')) {
                    return;
                }

                var title = wrap.querySelector('.wdt-heading-title');
                if (!title || title.textContent.indexOf('20') === -1) {
                    return;
                }

                wrap.innerHTML =
                    '<span class="wdt-heading-title gruposnap-experience__number">20+</span>' +
                    '<span class="gruposnap-experience__years">Años</span>';
            });
        }

        patchAboutTitle();
        patchAboutIntro();
        patchAboutContact();
        patchExperienceBadge();
        document.addEventListener('DOMContentLoaded', function () {
            patchAboutTitle();
            patchAboutIntro();
            patchAboutContact();
            patchExperienceBadge();
        });
    })();
    </script>
    <?php
}

add_action('init', 'gruposnap_home_about_bust_elementor_cache', 1);
add_filter('elementor/widget/render_content', 'gruposnap_home_about_heading_content', 10, 2);
add_filter('elementor/widget/render_content', 'gruposnap_home_about_intro_content', 11, 2);
add_filter('elementor/widget/render_content', 'gruposnap_home_about_contact_wrap_start', 12, 2);
add_filter('elementor/widget/render_content', 'gruposnap_home_about_contact_content', 13, 2);
add_filter('elementor/widget/render_content', 'gruposnap_home_about_contact_wrap_end', 14, 2);
add_filter('elementor/widget/render_content', 'gruposnap_home_experience_heading_content', 15, 2);
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_home_about_styles', 1005);
add_action('wp_footer', 'gruposnap_home_experience_dom_fallback', 8);
