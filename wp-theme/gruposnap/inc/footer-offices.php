<?php
/**
 * Footer 985 — oficinas RD / Venezuela (emails, banderas, layout).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

const GRUPOSNAP_FOOTER_OFFICE_RD_PHONE_LIST   = '8c76b7b';
const GRUPOSNAP_FOOTER_OFFICE_VE_TITLE        = 'a51201a';
const GRUPOSNAP_FOOTER_OFFICE_VE_ADDRESS_LIST = 'ed8191b';
const GRUPOSNAP_FOOTER_OFFICE_VE_EMAIL_LABEL  = 'b29effd';
const GRUPOSNAP_FOOTER_OFFICE_VE_EMAIL_LIST   = '69048ef';

/**
 * @return bool
 */
function gruposnap_should_enhance_footer_offices(): bool
{
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
 * @return string
 */
function gruposnap_footer_office_rd_email(): string
{
    return (string) apply_filters('gruposnap_footer_office_rd_email', 'contacto@gruposnap.com');
}

/**
 * @return string
 */
function gruposnap_footer_office_ve_email(): string
{
    return (string) apply_filters('gruposnap_footer_office_ve_email', 've@gruposnap.com');
}

/**
 * Bloque email (mismo layout en RD y Venezuela).
 *
 * @param string $email
 */
function gruposnap_footer_office_email_markup(string $email): string
{
    return sprintf(
        '<div class="gruposnap-footer-office__block gruposnap-footer-office__block--email">' .
        '<p class="gruposnap-footer-office__label">%1$s</p>' .
        '<a class="gruposnap-footer-office__value" href="mailto:%2$s">%3$s</a>' .
        '</div>',
        esc_html__('Email', 'gruposnap'),
        esc_attr($email),
        esc_html($email)
    );
}

/**
 * @return string
 */
function gruposnap_footer_office_rd_email_markup(): string
{
    return gruposnap_footer_office_email_markup(gruposnap_footer_office_rd_email());
}

/**
 * @return string
 */
function gruposnap_footer_office_ve_email_markup(): string
{
    return gruposnap_footer_office_email_markup(gruposnap_footer_office_ve_email());
}

/**
 * Título Venezuela (solo país; el email va debajo como en RD).
 */
function gruposnap_footer_office_ve_title_markup(): string
{
    return '<h5 class="elementor-heading-title elementor-size-default gruposnap-footer-office__country">'
        . esc_html__('Venezuela', 'gruposnap')
        . '</h5>';
}

/**
 * Encabezado de sección sobre las tarjetas RD / Venezuela.
 */
function gruposnap_footer_offices_intro_markup(): string
{
    return '<div class="gruposnap-footer-offices__intro">'
        . '<h3 id="gruposnap-footer-offices-title" class="gruposnap-footer-offices__title">'
        . esc_html__('Nuestras oficinas', 'gruposnap')
        . '</h3>'
        . '</div>';
}

/**
 * @param \Elementor\Element_Base $element
 */
function gruposnap_footer_offices_render_intro($element): void
{
    if (!gruposnap_should_enhance_footer_offices() || $element->get_name() !== 'column') {
        return;
    }

    if ($element->get_id() !== GRUPOSNAP_FOOTER_OFFICE_RD_COLUMN) {
        return;
    }

    static $rendered = false;
    if ($rendered) {
        return;
    }

    $rendered = true;
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo gruposnap_footer_offices_intro_markup();
}

/**
 * @param \Elementor\Element_Base $element
 */
function gruposnap_footer_office_column_country_class($element): void
{
    if (!gruposnap_should_enhance_footer_offices() || $element->get_name() !== 'column') {
        return;
    }

    $id = $element->get_id();
    if ($id === GRUPOSNAP_FOOTER_OFFICE_RD_COLUMN) {
        $element->add_render_attribute('_wrapper', 'class', 'gruposnap-footer-office--rd');
    }
    if ($id === GRUPOSNAP_FOOTER_OFFICE_VE_COLUMN) {
        $element->add_render_attribute('_wrapper', 'class', 'gruposnap-footer-office--ve');
    }
}

/**
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_footer_office_widget_content(string $content, $widget): string
{
    if (!gruposnap_should_enhance_footer_offices()) {
        return $content;
    }

    $id = $widget->get_id();

    if ($id === GRUPOSNAP_FOOTER_OFFICE_RD_PHONE_LIST) {
        return $content . gruposnap_footer_office_rd_email_markup();
    }

    if ($id === GRUPOSNAP_FOOTER_OFFICE_VE_TITLE) {
        return gruposnap_footer_office_ve_title_markup();
    }

    if ($id === GRUPOSNAP_FOOTER_OFFICE_VE_ADDRESS_LIST) {
        return $content . gruposnap_footer_office_ve_email_markup();
    }

    if ($id === GRUPOSNAP_FOOTER_OFFICE_VE_EMAIL_LABEL || $id === GRUPOSNAP_FOOTER_OFFICE_VE_EMAIL_LIST) {
        return '';
    }

    return $content;
}

function gruposnap_footer_offices_dom_fallback(): void
{
    if (!gruposnap_should_enhance_footer_offices()) {
        return;
    }

    $rd_email = wp_json_encode(gruposnap_footer_office_rd_email());
    $ve_email = wp_json_encode(gruposnap_footer_office_ve_email());
    ?>
    <script id="gruposnap-footer-offices-fallback">
    (function () {
        function patchOffices() {
            var rd = document.querySelector('.elementor-element-<?php echo esc_js(GRUPOSNAP_FOOTER_OFFICE_RD_COLUMN); ?>');
            var ve = document.querySelector('.elementor-element-<?php echo esc_js(GRUPOSNAP_FOOTER_OFFICE_VE_COLUMN); ?>');

            if (rd && !document.querySelector('.gruposnap-footer-offices__intro')) {
                rd.insertAdjacentHTML('beforebegin', <?php echo wp_json_encode(gruposnap_footer_offices_intro_markup()); ?>);
            }

            if (rd && !rd.querySelector('.gruposnap-footer-office__block--email')) {
                var phone = rd.querySelector('.elementor-element-<?php echo esc_js(GRUPOSNAP_FOOTER_OFFICE_RD_PHONE_LIST); ?> .elementor-widget-container');
                if (phone) {
                    var block = document.createElement('div');
                    block.className = 'gruposnap-footer-office__block gruposnap-footer-office__block--email';
                    block.innerHTML =
                        '<p class="gruposnap-footer-office__label">Email</p>' +
                        '<a class="gruposnap-footer-office__value" href="mailto:' + <?php echo $rd_email; ?> + '">' + <?php echo $rd_email; ?> + '</a>';
                    phone.appendChild(block);
                }
            }

            if (ve) {
                var title = ve.querySelector('.elementor-element-<?php echo esc_js(GRUPOSNAP_FOOTER_OFFICE_VE_TITLE); ?> .elementor-widget-container');
                if (title && !title.querySelector('.gruposnap-footer-office__country')) {
                    title.innerHTML =
                        '<h5 class="elementor-heading-title elementor-size-default gruposnap-footer-office__country">Venezuela</h5>';
                }

                var address = ve.querySelector('.elementor-element-<?php echo esc_js(GRUPOSNAP_FOOTER_OFFICE_VE_ADDRESS_LIST); ?> .elementor-widget-container');
                if (address && !address.querySelector('.gruposnap-footer-office__block--email')) {
                    var emailBlock = document.createElement('div');
                    emailBlock.className = 'gruposnap-footer-office__block gruposnap-footer-office__block--email';
                    emailBlock.innerHTML =
                        '<p class="gruposnap-footer-office__label">Email</p>' +
                        '<a class="gruposnap-footer-office__value" href="mailto:' + <?php echo $ve_email; ?> + '">' + <?php echo $ve_email; ?> + '</a>';
                    address.appendChild(emailBlock);
                }

                ['<?php echo esc_js(GRUPOSNAP_FOOTER_OFFICE_VE_EMAIL_LABEL); ?>', '<?php echo esc_js(GRUPOSNAP_FOOTER_OFFICE_VE_EMAIL_LIST); ?>'].forEach(function (wid) {
                    var el = ve.querySelector('.elementor-element-' + wid);
                    if (el) {
                        el.style.display = 'none';
                    }
                });
            }
        }

        patchOffices();
        document.addEventListener('DOMContentLoaded', patchOffices);
    })();
    </script>
    <?php
}

/**
 * En la home, coloca el bloque de oficinas justo debajo de Instagram.
 */
function gruposnap_footer_offices_should_relocate_after_instagram(): bool
{
    if (!function_exists('gruposnap_is_home_front') || !gruposnap_is_home_front()) {
        return false;
    }

    return gruposnap_should_enhance_footer_offices();
}

function gruposnap_footer_offices_relocate_after_instagram_script(): void
{
    if (!gruposnap_footer_offices_should_relocate_after_instagram()) {
        return;
    }

    $section_id = GRUPOSNAP_FOOTER_OFFICES_SECTION;
    ?>
    <script id="gruposnap-footer-offices-after-instagram">
    (function () {
        var officesSectionId = '<?php echo esc_js($section_id); ?>';
        var wrapSectionId = '<?php echo esc_js(GRUPOSNAP_FOOTER_OFFICES_WRAP_SECTION); ?>';
        var newsletterSectionId = 'f67e719';

        function hideEmptyOfficesShell() {
            var parentSection = document.querySelector(
                '#footer .elementor-element-' + wrapSectionId
            );
            if (parentSection && !parentSection.querySelector('.gruposnap-footer-offices')) {
                var column = parentSection.querySelector('.elementor-element-b0dd5e1 .elementor-widget-wrap');
                if (column && !column.querySelector('.elementor-widget, .elementor-inner-section')) {
                    parentSection.style.display = 'none';
                }
            }
        }

        /**
         * Home: Instagram → Nuestras oficinas → Email marketing (newsletter).
         */
        function findOfficesSection() {
            var footer = document.getElementById('footer');
            var scoped = document.querySelector('.gruposnap-home-offices-wrap .gruposnap-footer-offices');

            if (scoped) {
                return scoped;
            }

            if (!footer) {
                return null;
            }

            return (
                footer.querySelector('.elementor-element-' + officesSectionId + '.gruposnap-footer-offices') ||
                footer.querySelector('.gruposnap-footer-offices')
            );
        }

        function findNewsletterSection() {
            var node = document.querySelector('.elementor-element-' + newsletterSectionId);

            if (!node) {
                return null;
            }

            return node.closest('.elementor-top-section') || node;
        }

        function getOrCreateOfficesWrap(offices) {
            var wrap = offices.closest('.gruposnap-home-offices-wrap');

            if (wrap) {
                return wrap;
            }

            wrap = document.createElement('div');
            wrap.className = 'gruposnap-home-offices-wrap elementor elementor-985';
            wrap.setAttribute('data-gruposnap-offices-placement', 'after-instagram');
            offices.parentNode.removeChild(offices);
            wrap.appendChild(offices);

            return wrap;
        }

        function getOrCreateNewsletterWrap(newsletter) {
            var wrap = newsletter.closest('.gruposnap-home-newsletter-wrap');

            if (wrap) {
                return wrap;
            }

            wrap = document.createElement('div');
            wrap.className = 'gruposnap-home-newsletter-wrap elementor elementor-985';
            wrap.setAttribute('data-gruposnap-newsletter-placement', 'after-offices');
            newsletter.parentNode.removeChild(newsletter);
            wrap.appendChild(newsletter);

            return wrap;
        }

        function relocateHomeBottomStack() {
            var instagram = document.getElementById('instagram');
            var offices = findOfficesSection();

            if (!instagram || !offices) {
                return false;
            }

            var officesWrap = getOrCreateOfficesWrap(offices);

            if (instagram.nextElementSibling !== officesWrap) {
                instagram.insertAdjacentElement('afterend', officesWrap);
            }

            var newsletter = findNewsletterSection();

            if (newsletter) {
                var newsletterWrap = getOrCreateNewsletterWrap(newsletter);

                if (officesWrap.nextElementSibling !== newsletterWrap) {
                    officesWrap.insertAdjacentElement('afterend', newsletterWrap);
                }
            }

            hideEmptyOfficesShell();
            return true;
        }

        function scheduleRelocate() {
            relocateHomeBottomStack();
        }

        scheduleRelocate();
        document.addEventListener('DOMContentLoaded', scheduleRelocate);
        window.addEventListener('load', scheduleRelocate);
        [120, 350, 700, 1200].forEach(function (delay) {
            window.setTimeout(scheduleRelocate, delay);
        });

        if (window.jQuery) {
            window.jQuery(window).on('elementor/frontend/init', function () {
                window.setTimeout(scheduleRelocate, 150);
            });
        }
    })();
    </script>
    <?php
}

function gruposnap_footer_offices_bust_elementor_cache(): void
{
    $version = get_option('gruposnap_footer_offices_cache_version');
    if ($version === GRUPOSNAP_THEME_VERSION) {
        return;
    }

    delete_post_meta(985, '_elementor_element_cache');
    update_option('gruposnap_footer_offices_cache_version', GRUPOSNAP_THEME_VERSION, false);
}

/**
 * Asegura los estilos del footer 985 en home (newsletter fuera de #footer).
 */
function gruposnap_enqueue_home_bottom_stack_styles(): void
{
    if (!gruposnap_footer_offices_should_relocate_after_instagram()) {
        return;
    }

    if (wp_style_is('elementor-post-985', 'registered') && !wp_style_is('elementor-post-985', 'enqueued')) {
        wp_enqueue_style('elementor-post-985');
    }
}

add_action('init', 'gruposnap_footer_offices_bust_elementor_cache', 1);
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_home_bottom_stack_styles', 120);
add_action('elementor/frontend/column/before_render', 'gruposnap_footer_offices_render_intro', 5);
add_action('elementor/frontend/column/before_render', 'gruposnap_footer_office_column_country_class', 6);
add_filter('elementor/widget/render_content', 'gruposnap_footer_office_widget_content', 12, 2);
add_action('wp_footer', 'gruposnap_footer_offices_dom_fallback', 9);
add_action('wp_footer', 'gruposnap_footer_offices_relocate_after_instagram_script', 11);
