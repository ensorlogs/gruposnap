<?php
/**
 * Home — sección «Sobre GrupoSnap» (legibilidad sobre degradado).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

const GRUPOSNAP_HOME_ABOUT_SECTION_ID        = '46cb44b';
const GRUPOSNAP_HOME_ABOUT_MOBILE_SECTION_ID = 'a783519';
const GRUPOSNAP_HOME_ABOUT_COLUMN_ID         = '7332389';
/** Escritorio (46cb44b, oculto en móvil). */
const GRUPOSNAP_HOME_ABOUT_HEADING_ID = 'f92bae7';
/** Móvil (a783519, oculto en escritorio/tablet). */
const GRUPOSNAP_HOME_ABOUT_MOBILE_HEADING_ID = 'f29b9db';

const GRUPOSNAP_HOME_ABOUT_INTRO_HEADING_IDS = array(
    GRUPOSNAP_HOME_ABOUT_HEADING_ID,
    GRUPOSNAP_HOME_ABOUT_MOBILE_HEADING_ID,
);
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
    $text = 'Más de 20 años impulsando marcas a nivel internacional. En GrupoSnap combinamos diseño, producción y ejecución para crear soluciones publicitarias que conectan con los clientes y generan impacto real.';

    if (function_exists('gruposnap_t')) {
        $text = gruposnap_t(
            $text,
            'For over 20 years we have been empowering brands internationally. At GrupoSnap we combine design, production, and execution to create advertising solutions that connect with customers and deliver real impact.'
        );
    }

    return (string) apply_filters('gruposnap_home_about_intro_text', $text);
}

/**
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_home_about_is_intro_heading($widget): bool
{
    return in_array($widget->get_id(), GRUPOSNAP_HOME_ABOUT_INTRO_HEADING_IDS, true);
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
 * URL del icono de contacto (teléfono / correo).
 *
 * @param 'phone'|'email' $type
 * @return string
 */
function gruposnap_home_about_contact_icon_url(string $type): string
{
    $file = ('phone' === $type) ? 'CA-call-icon.svg' : 'icon-box-icon-mail.svg';
    $paths = array(
        WP_CONTENT_DIR . '/uploads/2023/09/' . $file,
        get_template_directory() . '/ocdi/uploads/2023/09/' . $file,
    );

    foreach ($paths as $path) {
        if (!is_readable($path)) {
            continue;
        }

        if (str_starts_with($path, WP_CONTENT_DIR . '/uploads/')) {
            return content_url('uploads/2023/09/' . $file);
        }

        if (str_starts_with($path, get_template_directory())) {
            return get_template_directory_uri() . '/ocdi/uploads/2023/09/' . $file;
        }
    }

    return content_url('uploads/2023/09/' . $file);
}

/**
 * Fila de contacto: icono a la izquierda, etiqueta + enlace a la derecha (como Llámanos).
 *
 * @param 'phone'|'email' $type
 * @return string
 */
function gruposnap_home_about_contact_row_markup(string $type): string
{
    if ('phone' === $type) {
        $label = __('Llámanos', 'gruposnap');
        $value = gruposnap_home_about_phone();
        $href  = 'tel:+18098654576';
    } else {
        $label = __('Escríbenos', 'gruposnap');
        $value = gruposnap_home_about_email();
        $href  = 'mailto:' . $value;
    }

    $icon = esc_url(gruposnap_home_about_contact_icon_url($type));

    return '<div class="wdt-icon-box-holder wdt-content-item-holder wdt-rc-template-default">'
        . '<div id="wdt-icon-box-' . esc_attr(('phone' === $type) ? GRUPOSNAP_HOME_ABOUT_PHONE_WIDGET_ID : GRUPOSNAP_HOME_ABOUT_EMAIL_WIDGET_ID) . '" '
        . 'class="wdt-icon-box-container">'
        . '<div class="wdt-content-item gruposnap-about-contact__item gruposnap-about-contact__item--' . esc_attr($type) . '">'
        . '<div class="wdt-content-media-group">'
        . '<div class="wdt-content-elements-group wdt-media-group">'
        . '<div class="wdt-content-icon-wrapper">'
        . '<div class="wdt-content-icon">'
        . '<span class="gruposnap-about-contact__icon" aria-hidden="true">'
        . '<img class="gruposnap-about-contact__icon-img" src="' . $icon . '" alt="" width="22" height="22" loading="lazy" decoding="async" />'
        . '</span>'
        . '</div></div></div></div>'
        . '<div class="wdt-content-detail-group">'
        . '<div class="wdt-content-group">'
        . '<div class="wdt-content-title"><h5><span class="gruposnap-about-contact__label">' . esc_html($label) . '</span></h5></div>'
        . '<div class="wdt-content-subtitle"><h5><a class="gruposnap-about-contact__value" href="' . esc_attr($href) . '">'
        . esc_html($value)
        . '</a></h5></div>'
        . '</div></div></div></div></div>';
}

/**
 * Sustituye el HTML de los icon-box de contacto por filas controladas.
 *
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_home_about_contact_content(string $content, $widget): string
{
    if (!gruposnap_should_style_home_about()) {
        return $content;
    }

    if ($widget->get_id() === GRUPOSNAP_HOME_ABOUT_PHONE_WIDGET_ID) {
        return gruposnap_home_about_contact_row_markup('phone');
    }

    if ($widget->get_id() === GRUPOSNAP_HOME_ABOUT_EMAIL_WIDGET_ID) {
        return gruposnap_home_about_contact_row_markup('email');
    }

    return $content;
}

/**
 * Oculta el badge duplicado sobre la foto (2454fd0 / 95e0f84); el unificado está en f92bae7.
 *
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_home_about_hide_photo_experience_widgets(string $content, $widget): string
{
    if (!gruposnap_should_style_home_about()) {
        return $content;
    }

    if (in_array($widget->get_id(), GRUPOSNAP_HOME_EXPERIENCE_WIDGET_IDS, true)) {
        return '';
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
    if (!gruposnap_should_style_home_about() || !gruposnap_home_about_is_intro_heading($widget)) {
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

        var introHeadingIds = <?php echo wp_json_encode(GRUPOSNAP_HOME_ABOUT_INTRO_HEADING_IDS); ?>;

        function patchAboutIntro() {
            introHeadingIds.forEach(function (headingId) {
                var about = document.querySelector('.elementor-element-' + headingId + ' .wdt-heading-holder');
                if (!about) {
                    return;
                }

                var body = about.querySelector('.wdt-heading-content-wrapper');
                if (!body) {
                    return;
                }

                if (!body.classList.contains('gruposnap-about-intro')) {
                    body.outerHTML = introHtml;
                    body = about.querySelector('.wdt-heading-content-wrapper');
                }

                if (!body) {
                    return;
                }

                var p = body.querySelector('.gruposnap-about-intro__text, p');
                if (p) {
                    p.textContent = <?php echo wp_json_encode(gruposnap_home_about_intro_text()); ?>;
                }

                body.querySelectorAll('p').forEach(function (para, index) {
                    if (index > 0) {
                        para.remove();
                    }
                });
            });
        }

        function applyAboutContactRowLayout(root) {
            if (!root) {
                return;
            }

            root.querySelectorAll('.wdt-content-item, .wdt-content-item-holder').forEach(function (el) {
                el.style.setProperty('display', 'flex', 'important');
                el.style.setProperty('flex-direction', 'row', 'important');
                el.style.setProperty('flex-wrap', 'nowrap', 'important');
                el.style.setProperty('align-items', 'center', 'important');
                el.style.setProperty('justify-content', 'flex-start', 'important');
                el.style.setProperty('text-align', 'left', 'important');
            });

            root.querySelectorAll('.wdt-content-detail-group').forEach(function (el) {
                el.style.setProperty('display', 'flex', 'important');
                el.style.setProperty('flex-direction', 'column', 'important');
                el.style.setProperty('align-items', 'flex-start', 'important');
                el.style.setProperty('justify-content', 'center', 'important');
            });
        }

        function patchAboutContact() {
            var phone = document.querySelector('.elementor-element-<?php echo esc_js(GRUPOSNAP_HOME_ABOUT_PHONE_WIDGET_ID); ?>');
            var email = document.querySelector('.elementor-element-<?php echo esc_js(GRUPOSNAP_HOME_ABOUT_EMAIL_WIDGET_ID); ?>');
            if (!phone || !email) {
                return;
            }

            var phoneContainer = phone.querySelector(':scope > .elementor-widget-container');
            if (phoneContainer && phoneContainer.contains(email) && email.parentNode !== phone.parentNode) {
                phone.parentNode.insertBefore(email, phone.nextElementSibling);
            }

            var wrap = document.getElementById('gruposnap-about-contact');
            if (!wrap) {
                wrap = document.createElement('div');
                wrap.className = 'gruposnap-about-contact';
                wrap.id = 'gruposnap-about-contact';
                wrap.innerHTML = '<div class="gruposnap-about-contact__grid"></div>';
                phone.parentNode.insertBefore(wrap, phone);
            }

            var grid = wrap.querySelector('.gruposnap-about-contact__grid');
            if (!grid) {
                grid = document.createElement('div');
                grid.className = 'gruposnap-about-contact__grid';
                wrap.appendChild(grid);
            }

            if (phone.parentNode !== grid) {
                grid.appendChild(phone);
            }
            if (email.parentNode !== grid) {
                grid.appendChild(email);
            }

            [phone, email].forEach(function (widget) {
                widget.style.setProperty('width', '100%', 'important');
                widget.style.setProperty('max-width', '100%', 'important');
            });

            applyAboutContactRowLayout(grid);
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

        function patchExperienceHeadLayout() {
            ['2454fd0', '95e0f84'].forEach(function (id) {
                var duplicate = document.querySelector('.elementor-element-' + id);
                if (duplicate) {
                    duplicate.remove();
                }
            });

            if (!window.matchMedia('(min-width: 1025px)').matches) {
                return;
            }

            document.querySelectorAll('.elementor-element-f92bae7 .gruposnap-experience__head').forEach(function (head) {
                head.style.setProperty('display', 'flex', 'important');
                head.style.setProperty('flex-direction', 'row', 'important');
                head.style.setProperty('flex-wrap', 'nowrap', 'important');
                head.style.setProperty('align-items', 'flex-end', 'important');
                head.style.setProperty('justify-content', 'flex-start', 'important');
            });
        }

        patchAboutTitle();
        patchAboutIntro();
        patchAboutContact();
        patchExperienceBadge();
        patchExperienceHeadLayout();
        document.addEventListener('DOMContentLoaded', function () {
            patchAboutTitle();
            patchAboutIntro();
            patchAboutContact();
            patchExperienceBadge();
            patchExperienceHeadLayout();
        });
        window.addEventListener('load', patchExperienceHeadLayout);
        window.matchMedia('(min-width: 1025px)').addEventListener('change', patchExperienceHeadLayout);
    })();
    </script>
    <?php
}

add_action('init', 'gruposnap_home_about_bust_elementor_cache', 1);
add_filter('elementor/widget/render_content', 'gruposnap_home_about_hide_photo_experience_widgets', 4, 2);
add_filter('elementor/widget/render_content', 'gruposnap_home_about_heading_content', 10, 2);
add_filter('elementor/widget/render_content', 'gruposnap_home_about_intro_content', 11, 2);
add_filter('elementor/widget/render_content', 'gruposnap_home_about_contact_content', 12, 2);
add_filter('elementor/widget/render_content', 'gruposnap_home_experience_heading_content', 15, 2);
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_home_about_styles', 1005);
add_action('wp_footer', 'gruposnap_home_experience_dom_fallback', 8);
