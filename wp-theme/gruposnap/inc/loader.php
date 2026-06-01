<?php
/**
 * Preloader GrupoSnap — animación en video (reemplaza loader del tema Printme).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return string
 */
function gruposnap_loader_video_url(): string
{
    return (string) apply_filters(
        'gruposnap_loader_video_url',
        get_stylesheet_directory_uri() . '/assets/video/gruposnap-loader-wide-hd.mp4'
    );
}

/**
 * @return bool
 */
function gruposnap_should_show_loader(): bool
{
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return false;
    }

    if (function_exists('wp_is_json_request') && wp_is_json_request()) {
        return false;
    }

    if (class_exists('\Elementor\Plugin')) {
        $plugin = \Elementor\Plugin::$instance;
        if (
            ($plugin->editor && $plugin->editor->is_edit_mode())
            || ($plugin->preview && $plugin->preview->is_preview_mode())
        ) {
            return false;
        }
    }

    return (bool) apply_filters('gruposnap_show_site_loader', true);
}

/**
 * Evita el preloader por defecto de Printme Plus (imagen animada).
 */
function gruposnap_disable_printme_site_loader(): void
{
    if (!class_exists('PrintmePlusSiteLoader')) {
        return;
    }

    remove_action('printme_hook_top', array(PrintmePlusSiteLoader::instance(), 'load_template'));
    remove_action('printme_after_main_css', array(PrintmePlusSiteLoader::instance(), 'enqueue_assets'));
}

function gruposnap_enqueue_loader_assets(): void
{
    if (!gruposnap_should_show_loader()) {
        return;
    }

    wp_enqueue_style(
        'gruposnap-loader',
        get_stylesheet_directory_uri() . '/assets/css/loader.css',
        array('gruposnap-child'),
        GRUPOSNAP_THEME_VERSION
    );

    wp_enqueue_script(
        'gruposnap-loader',
        get_stylesheet_directory_uri() . '/assets/js/gruposnap-loader.js',
        array(),
        GRUPOSNAP_THEME_VERSION,
        true
    );

    wp_localize_script(
        'gruposnap-loader',
        'gruposnapLoader',
        array(
            'videoUrl'      => gruposnap_loader_video_url(),
            'playbackRate'  => 1.75,
            'fadeMs'        => 280,
            'durationMs'    => 5000,
            'maxVideoSec'   => 5,
            'maxMs'         => 6500,
            'minMs'         => 300,
        )
    );
}

function gruposnap_render_site_loader(): void
{
    static $rendered = false;

    if ($rendered || !gruposnap_should_show_loader()) {
        return;
    }

    $rendered = true;

    $video = esc_url(gruposnap_loader_video_url());
    ?>
    <div id="gsnap-loader" class="gsnap-loader" role="presentation" aria-hidden="true">
        <div class="gsnap-loader__stage">
            <div class="gsnap-loader__video-wrap">
                <video
                    class="gsnap-loader__video"
                    src="<?php echo $video; ?>"
                    muted
                    playsinline
                    webkit-playsinline
                    preload="auto"
                    disablepictureinpicture
                    disableremoteplayback
                ></video>
            </div>
        </div>
    </div>
    <?php
}

add_action('after_setup_theme', 'gruposnap_disable_printme_site_loader', 20);
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_loader_assets', 5);
add_action('wp_body_open', 'gruposnap_render_site_loader', 1);
