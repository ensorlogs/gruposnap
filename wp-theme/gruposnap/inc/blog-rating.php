<?php
/**
 * GrupoSnap — valoración por estrellas al final de entradas de blog.
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

const GRUPOSNAP_RATING_META_COUNT = '_gruposnap_rating_count';
const GRUPOSNAP_RATING_META_SUM   = '_gruposnap_rating_sum';

/**
 * @return string
 */
function gruposnap_rating_cookie_name(int $post_id): string
{
    return 'gruposnap_rated_' . $post_id;
}

/**
 * @return array{count: int, sum: int, average: float}
 */
function gruposnap_get_post_rating_stats(int $post_id): array
{
    $count = max(0, (int) get_post_meta($post_id, GRUPOSNAP_RATING_META_COUNT, true));
    $sum   = max(0, (int) get_post_meta($post_id, GRUPOSNAP_RATING_META_SUM, true));

    if ($count > 0 && $sum < $count) {
        $sum = $count;
    }

    return array(
        'count'   => $count,
        'sum'     => $sum,
        'average' => $count > 0 ? round($sum / $count, 1) : 0.0,
    );
}

/**
 * @return int 0–5
 */
function gruposnap_get_user_post_rating(int $post_id): int
{
    $cookie_name = gruposnap_rating_cookie_name($post_id);

    if (!isset($_COOKIE[$cookie_name])) {
        return 0;
    }

    return max(0, min(5, (int) $_COOKIE[$cookie_name]));
}

/**
 * @return string
 */
function gruposnap_post_rating_status_message(int $post_id, int $user_rating, array $stats): string
{
    if ($user_rating > 0) {
        return __('¡Gracias por tu valoración!', 'gruposnap');
    }

    if ($stats['count'] === 0) {
        return __('Sé la primera persona en valorar este blog.', 'gruposnap');
    }

    if ($stats['count'] === 1) {
        return sprintf(
            /* translators: %s: average rating out of 5 */
            __('Valoración media: %s de 5 · 1 valoración', 'gruposnap'),
            number_format_i18n($stats['average'], 1)
        );
    }

    return sprintf(
        /* translators: 1: average rating, 2: number of ratings */
        __('Valoración media: %1$s de 5 · %2$s valoraciones', 'gruposnap'),
        number_format_i18n($stats['average'], 1),
        number_format_i18n($stats['count'])
    );
}

/**
 * @return string
 */
function gruposnap_get_post_rating_markup(int $post_id): string
{
    if ($post_id <= 0 || get_post_type($post_id) !== 'post') {
        return '';
    }

    $stats       = gruposnap_get_post_rating_stats($post_id);
    $user_rating = gruposnap_get_user_post_rating($post_id);
    $has_voted   = $user_rating > 0;
    $status      = gruposnap_post_rating_status_message($post_id, $user_rating, $stats);

    ob_start();
    ?>
    <section
        class="gruposnap-post-rating"
        data-post-id="<?php echo esc_attr((string) $post_id); ?>"
        data-user-rating="<?php echo esc_attr((string) $user_rating); ?>"
        data-has-voted="<?php echo $has_voted ? '1' : '0'; ?>"
        aria-label="<?php echo esc_attr__('Valoración del contenido', 'gruposnap'); ?>"
    >
        <h2 class="gruposnap-post-rating__title">
            <?php echo esc_html__('¿De cuánta utilidad te ha parecido este contenido?', 'gruposnap'); ?>
        </h2>
        <p class="gruposnap-post-rating__hint">
            <?php echo esc_html__('¡Haz clic en las estrellas para valorarlo!', 'gruposnap'); ?>
        </p>

        <div
            class="gruposnap-post-rating__stars"
            role="radiogroup"
            aria-label="<?php echo esc_attr__('Selecciona una valoración de 1 a 5 estrellas', 'gruposnap'); ?>"
        >
            <?php for ($star = 1; $star <= 5; ++$star) : ?>
                <button
                    type="button"
                    class="gruposnap-post-rating__star"
                    data-rating="<?php echo esc_attr((string) $star); ?>"
                    role="radio"
                    aria-checked="<?php echo ($user_rating === $star) ? 'true' : 'false'; ?>"
                    aria-label="<?php echo esc_attr(sprintf(/* translators: %d: star count */ _n('%d estrella', '%d estrellas', $star, 'gruposnap'), $star)); ?>"
                    <?php echo $has_voted ? ' disabled' : ''; ?>
                >
                    <svg class="gruposnap-post-rating__star-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M12 2.5l2.86 5.79 6.39.93-4.62 4.51 1.09 6.36L12 17.77l-5.72 3.01 1.09-6.36L2.75 9.22l6.39-.93L12 2.5z"/>
                    </svg>
                </button>
            <?php endfor; ?>
        </div>

        <p class="gruposnap-post-rating__status" aria-live="polite"><?php echo esc_html($status); ?></p>
    </section>
    <?php

    return (string) ob_get_clean();
}

/**
 * Inserta la valoración justo después del cuerpo del artículo.
 *
 * @param string $html
 * @return string
 */
function gruposnap_inject_post_rating(string $html): string
{
    if (!is_singular('post') || $html === '') {
        return $html;
    }

    if (strpos($html, 'gruposnap-post-rating') !== false) {
        return $html;
    }

    $post_id = get_the_ID();
    $rating  = gruposnap_get_post_rating_markup($post_id);

    if ($rating === '') {
        return $html;
    }

    $marker = '</div><!-- Entry Content -->';
    if (strpos($html, $marker) !== false) {
        return str_replace($marker, $marker . $rating, $html);
    }

    return $html . $rating;
}

add_filter('printme_single_post_dynamic_template_part', 'gruposnap_inject_post_rating', 25);

/**
 * Encola JS de valoración en entradas individuales.
 */
function gruposnap_enqueue_post_rating_assets(): void
{
    if (!is_singular('post')) {
        return;
    }

    wp_enqueue_script(
        'gruposnap-blog-rating',
        get_stylesheet_directory_uri() . '/assets/js/blog-rating.js',
        array(),
        GRUPOSNAP_THEME_VERSION,
        true
    );

    wp_localize_script(
        'gruposnap-blog-rating',
        'gruposnapPostRating',
        array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('gruposnap_post_rating'),
            'i18n'    => array(
                'thanks'  => __('¡Gracias por tu valoración!', 'gruposnap'),
                'error'   => __('No se pudo guardar tu valoración. Inténtalo de nuevo.', 'gruposnap'),
                'first'   => __('Sé la primera persona en valorar este blog.', 'gruposnap'),
                'average' => __('Valoración media: %s de 5 · %s valoraciones', 'gruposnap'),
            ),
        )
    );
}

add_action('wp_enqueue_scripts', 'gruposnap_enqueue_post_rating_assets', 105);

/**
 * Guarda una valoración vía AJAX.
 */
function gruposnap_ajax_submit_post_rating(): void
{
    check_ajax_referer('gruposnap_post_rating', 'nonce');

    $post_id = isset($_POST['postId']) ? (int) $_POST['postId'] : 0;
    $rating  = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;

    if ($post_id <= 0 || get_post_type($post_id) !== 'post' || $rating < 1 || $rating > 5) {
        wp_send_json_error(array('message' => __('Datos no válidos.', 'gruposnap')), 400);
    }

    if (gruposnap_get_user_post_rating($post_id) > 0) {
        wp_send_json_error(array('message' => __('Ya has valorado esta entrada.', 'gruposnap')), 409);
    }

    $stats = gruposnap_get_post_rating_stats($post_id);

    update_post_meta($post_id, GRUPOSNAP_RATING_META_COUNT, $stats['count'] + 1);
    update_post_meta($post_id, GRUPOSNAP_RATING_META_SUM, $stats['sum'] + $rating);

    $cookie_name = gruposnap_rating_cookie_name($post_id);
    $secure      = is_ssl();
    $expires     = time() + YEAR_IN_SECONDS;

    setcookie($cookie_name, (string) $rating, $expires, COOKIEPATH, COOKIE_DOMAIN, $secure, true);
    if (COOKIEPATH !== SITECOOKIEPATH) {
        setcookie($cookie_name, (string) $rating, $expires, SITECOOKIEPATH, COOKIE_DOMAIN, $secure, true);
    }

    $_COOKIE[$cookie_name] = (string) $rating;

    $updated_stats = gruposnap_get_post_rating_stats($post_id);

    wp_send_json_success(
        array(
            'rating'  => $rating,
            'count'   => $updated_stats['count'],
            'average' => $updated_stats['average'],
            'message' => gruposnap_post_rating_status_message($post_id, $rating, $updated_stats),
        )
    );
}

add_action('wp_ajax_gruposnap_post_rating', 'gruposnap_ajax_submit_post_rating');
add_action('wp_ajax_nopriv_gruposnap_post_rating', 'gruposnap_ajax_submit_post_rating');
