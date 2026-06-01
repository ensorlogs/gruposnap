<?php
/**
 * Footer — bloque «Aceptamos pagos» (Elementor footer 985).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Widget heading «Aceptamos pagos». */
const GRUPOSNAP_FOOTER_PAYMENTS_HEADING_ID = 'c5e544e';

/** Widget imagen payment.png. */
const GRUPOSNAP_FOOTER_PAYMENTS_IMAGE_ID = '43ca02e';

/** Widget copyright (© GrupoSnap). */
const GRUPOSNAP_FOOTER_COPYRIGHT_WIDGET_ID = '42f81a5';

/**
 * @return bool
 */
function gruposnap_should_enhance_footer_payments(): bool
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
 * HTML del bloque de métodos de pago.
 */
function gruposnap_render_footer_payments_block(): void
{
    ?>
    <div class="gruposnap-payments" role="group" aria-label="<?php echo esc_attr__('Métodos de pago aceptados', 'gruposnap'); ?>">
        <ul class="gruposnap-payments__logos">
            <li class="gruposnap-payments__logo gruposnap-payments__logo--visa">
                <span class="gruposnap-payments__mark" aria-hidden="true">VISA</span>
            </li>
            <li class="gruposnap-payments__logo gruposnap-payments__logo--mastercard">
                <span class="gruposnap-payments__mark gruposnap-payments__mark--mc" aria-hidden="true">
                    <span class="gruposnap-payments__mc-circle gruposnap-payments__mc-circle--red"></span>
                    <span class="gruposnap-payments__mc-circle gruposnap-payments__mc-circle--yellow"></span>
                </span>
            </li>
            <li class="gruposnap-payments__logo gruposnap-payments__logo--paypal">
                <span class="gruposnap-payments__mark" aria-hidden="true">PayPal</span>
            </li>
        </ul>
        <p class="gruposnap-payments__labels">VISA · MASTERCARD · PAYPAL</p>
    </div>
    <?php
}

/**
 * Bloque completo bajo el copyright (título + logos).
 */
function gruposnap_render_footer_payments_bar(): void
{
    ?>
    <div class="gruposnap-payments-bar">
        <h5 class="gruposnap-payments__title"><?php echo esc_html__('Aceptamos pagos', 'gruposnap'); ?></h5>
        <?php gruposnap_render_footer_payments_block(); ?>
    </div>
    <?php
}

/**
 * Título más limpio (sin espacio ni dos puntos sueltos).
 *
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 * @return string
 */
function gruposnap_footer_payments_heading_content(string $content, $widget): string
{
    if (!gruposnap_should_enhance_footer_payments()) {
        return $content;
    }

    if ($widget->get_id() !== GRUPOSNAP_FOOTER_PAYMENTS_HEADING_ID || $widget->get_name() !== 'heading') {
        return $content;
    }

    return '<h5 class="elementor-heading-title elementor-size-default gruposnap-payments__title">'
        . esc_html__('Aceptamos pagos', 'gruposnap')
        . '</h5>';
}

/**
 * Sustituye la imagen compuesta por el bloque con marcas y texto inferior.
 *
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 * @return string
 */
function gruposnap_footer_payments_replace_image(string $content, $widget): string
{
    if (!gruposnap_should_enhance_footer_payments()) {
        return $content;
    }

    if ($widget->get_id() !== GRUPOSNAP_FOOTER_PAYMENTS_IMAGE_ID || $widget->get_name() !== 'image') {
        return $content;
    }

    ob_start();
    gruposnap_render_footer_payments_block();

    return (string) ob_get_clean();
}

/**
 * Muestra métodos de pago debajo del texto de copyright.
 *
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 * @return string
 */
function gruposnap_footer_payments_append_after_copyright(string $content, $widget): string
{
    if (!gruposnap_should_enhance_footer_payments()) {
        return $content;
    }

    if ($widget->get_id() !== GRUPOSNAP_FOOTER_COPYRIGHT_WIDGET_ID || $widget->get_name() !== 'text-editor') {
        return $content;
    }

    if (strpos($content, 'gruposnap-payments-bar') !== false) {
        return $content;
    }

    ob_start();
    gruposnap_render_footer_payments_bar();

    return $content . (string) ob_get_clean();
}

/**
 * Marca el contenedor de la columna para estilos locales.
 *
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_footer_payments_column_class($widget): void
{
    if (!gruposnap_should_enhance_footer_payments()) {
        return;
    }

    if (!in_array($widget->get_id(), array(GRUPOSNAP_FOOTER_PAYMENTS_HEADING_ID, GRUPOSNAP_FOOTER_PAYMENTS_IMAGE_ID), true)) {
        return;
    }

    $widget->add_render_attribute('_wrapper', 'class', 'gruposnap-payments-wrap');
}

function gruposnap_enqueue_footer_payments_styles(): void
{
    if (!gruposnap_should_enhance_footer_payments()) {
        return;
    }

    wp_enqueue_style(
        'gruposnap-footer-payments',
        get_stylesheet_directory_uri() . '/assets/css/footer-payments.css',
        array('gruposnap-child'),
        GRUPOSNAP_THEME_VERSION
    );
}

/**
 * Footer Elementor (plantilla 985). Al cambiar el tema, vacía la caché de documento
 * para que vuelva a ejecutarse render_content (la caché guardaba el HTML antiguo).
 */
function gruposnap_footer_payments_bust_elementor_document_cache(): void
{
    $option_key = 'gruposnap_footer_payments_cache_ver';
    $stored     = (string) get_option($option_key, '');

    if ($stored === GRUPOSNAP_THEME_VERSION) {
        return;
    }

    delete_post_meta(985, '_elementor_element_cache');
    update_option($option_key, GRUPOSNAP_THEME_VERSION, false);
}

/**
 * Si la caché del footer sigue sirviendo payment.png, sustituye el bloque en el DOM.
 */
function gruposnap_footer_payments_dom_fallback_script(): void
{
    if (!gruposnap_should_enhance_footer_payments()) {
        return;
    }

    ob_start();
    ?>
    <script>
    (function () {
        var paymentsBarHtml = <?php
            ob_start();
            gruposnap_render_footer_payments_bar();
            echo wp_json_encode((string) ob_get_clean());
        ?>;

        function hideTopPaymentsColumn() {
            var col = document.querySelector('.elementor-element-2e90e67');
            if (col) {
                col.style.setProperty('display', 'none', 'important');
            }
        }

        function patchPaymentsBar() {
            hideTopPaymentsColumn();

            var copyright = document.querySelector('.elementor-element-<?php echo esc_js(GRUPOSNAP_FOOTER_COPYRIGHT_WIDGET_ID); ?> .elementor-widget-container');
            if (!copyright || copyright.querySelector('.gruposnap-payments-bar')) {
                return;
            }

            copyright.insertAdjacentHTML('beforeend', paymentsBarHtml);
        }

        function patchPayments() {
            hideTopPaymentsColumn();

            var wrap = document.querySelector('.elementor-element-<?php echo esc_js(GRUPOSNAP_FOOTER_PAYMENTS_IMAGE_ID); ?> .elementor-widget-container');
            if (!wrap || wrap.querySelector('.gruposnap-payments')) {
                return;
            }

            var title = document.querySelector('.elementor-element-<?php echo esc_js(GRUPOSNAP_FOOTER_PAYMENTS_HEADING_ID); ?> .elementor-heading-title');
            if (title) {
                title.textContent = <?php echo wp_json_encode(__('Aceptamos pagos', 'gruposnap')); ?>;
                title.classList.add('gruposnap-payments__title');
            }

            wrap.innerHTML = <?php
                ob_start();
                gruposnap_render_footer_payments_block();
                echo wp_json_encode((string) ob_get_clean());
            ?>;
        }

        patchPaymentsBar();
        patchPayments();
        document.addEventListener('DOMContentLoaded', function () {
            patchPaymentsBar();
            patchPayments();
        });
    })();
    </script>
    <?php
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo ob_get_clean();
}

add_action('init', 'gruposnap_footer_payments_bust_elementor_document_cache', 1);
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_footer_payments_styles', 100);
add_action('elementor/widget/before_render_content', 'gruposnap_footer_payments_column_class', 5);
add_filter('elementor/widget/render_content', 'gruposnap_footer_payments_heading_content', 12, 2);
add_filter('elementor/widget/render_content', 'gruposnap_footer_payments_replace_image', 12, 2);
add_filter('elementor/widget/render_content', 'gruposnap_footer_payments_append_after_copyright', 12, 2);
add_action('wp_footer', 'gruposnap_footer_payments_dom_fallback_script', 8);
