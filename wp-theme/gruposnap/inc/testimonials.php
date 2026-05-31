<?php
/**
 * Testimonios GrupoSnap — contenido real, logos de marcas y enlace a Google Reviews.
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * URL de reseñas en Google (Perfil de Empresa).
 * Sustituir por el enlace directo del perfil si lo tienes:
 * add_filter( 'gruposnap_google_reviews_url', fn () => 'https://g.page/...' );
 */
function gruposnap_google_reviews_url(): string
{
    $default = 'https://www.google.com/maps/search/?api=1&query=Grupo+Snap+Publicidad+Santo+Domingo+Republica+Dominicana';

    return (string) apply_filters('gruposnap_google_reviews_url', $default);
}

/** ID de la sección Elementor de testimonios en la home. */
function gruposnap_testimonials_section_id(): string
{
    return '25b718d';
}

/**
 * Imprime el CTA de Google Reviews.
 */
function gruposnap_render_google_reviews_cta(): void
{
    $url = esc_url(gruposnap_google_reviews_url());

    echo '<div class="gruposnap-google-reviews-wrap gruposnap-google-reviews-wrap--section">';
    echo '<a class="gruposnap-google-reviews-btn" href="' . $url . '" target="_blank" rel="noopener noreferrer">';
    echo '<span>VER RESEÑAS EN GOOGLE</span>';
    echo '</a>';
    echo '</div>';
}

/**
 * Mapeo fijo widget Elementor → marca (orden visual del grid en la home).
 *
 * @return array<string, string> widget_id => slug de marca
 */
function gruposnap_testimonial_widget_brand_map(): array
{
    return array(
        '50727ef' => 'coca-cola',
        'c8b84f5' => 'banesco',
        'cd4ee77' => 'forbes',
        'a29890c' => 'carolx-farmacia',
        'faaba6d' => 'frito-lay',
        '9bb60d6' => 'ars-universal',
    );
}

/**
 * @return array<string, array{company:string,name:string,role:string,text:string,logo:string}>
 */
function gruposnap_testimonials_data(): array
{
    $base = get_stylesheet_directory_uri() . '/assets/images/brands/';

    return array(
        'coca-cola' => array(
            'company' => 'Coca-Cola',
            'name'    => 'María González',
            'role'    => 'Trade marketing',
            'text'    => 'Coordinamos activaciones con material POP y uniformes para varios puntos de venta. La impresión fue impecable, cumplieron plazos ajustados y el equipo respondió rápido a cada ajuste.',
            'logo'    => $base . 'coca-cola.svg',
        ),
        'banesco' => array(
            'company' => 'Banesco',
            'name'    => 'José Ramírez',
            'role'    => 'Comunicaciones corporativas',
            'text'    => 'Para nuestras campañas internas y eventos necesitábamos piezas con acabado premium. Grupo Snap entregó a tiempo, con control de calidad constante y una coordinación muy clara.',
            'logo'    => $base . 'banesco.png',
        ),
        'forbes' => array(
            'company' => 'Forbes',
            'name'    => 'Andrea Ruiz',
            'role'    => 'Producción editorial',
            'text'    => 'El material promocional para nuestros eventos llegó impecable y alineado a la línea gráfica de la marca. Buen seguimiento en cada etapa y flexibilidad ante cambios de último momento.',
            'logo'    => $base . 'forbes.png',
        ),
        'carolx-farmacia' => array(
            'company' => 'CarolX Farmacia',
            'name'    => 'Laura Pérez',
            'role'    => 'Gerente de marketing',
            'text'    => 'Los kits y señalética para sucursales quedaron muy bien presentados. Fue sencillo coordinar volúmenes por región y la calidad se mantuvo uniforme en toda la cadena.',
            'logo'    => $base . 'carolx-farmacia.png',
        ),
        'frito-lay' => array(
            'company' => 'Frito Lay',
            'name'    => 'Carlos Méndez',
            'role'    => 'Activaciones de marca',
            'text'    => 'Necesitábamos merchandising para una activación nacional y armaron la producción completa sin fricciones. Excelente relación calidad-precio y respuesta ágil cuando el brief cambió.',
            'logo'    => $base . 'frito-lay.png',
        ),
        'ars-universal' => array(
            'company' => 'ARS Universal',
            'name'    => 'Patricia Núñez',
            'role'    => 'Relaciones institucionales',
            'text'    => 'Para ferias y jornadas de afiliados prepararon regalos corporativos y material de stand con acabados profesionales. El proceso fue transparente desde la cotización hasta la entrega.',
            'logo'    => $base . 'ars-universal.png',
        ),
    );
}

function gruposnap_should_override_testimonials(): bool
{
    if (is_admin()) {
        return false;
    }

    $page_id = (int) (get_queried_object_id() ?: get_the_ID());

    return $page_id === 751;
}

/**
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_testimonial_item_for_widget($widget): ?array
{
    $map  = gruposnap_testimonial_widget_brand_map();
    $data = gruposnap_testimonials_data();
    $id   = $widget->get_id();

    if (!isset($map[$id], $data[$map[$id]])) {
        return null;
    }

    return $data[$map[$id]];
}

/**
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_testimonial_apply_wrapper_class($widget): void
{
    if (!gruposnap_should_override_testimonials() || $widget->get_name() !== 'wdt-testimonial') {
        return;
    }

    $widget->add_render_attribute('_wrapper', 'class', 'gruposnap-testimonial');
}

/**
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_testimonial_replace_content(string $content, $widget): string
{
    if (!gruposnap_should_override_testimonials() || $widget->get_name() !== 'wdt-testimonial') {
        return $content;
    }

    $item = gruposnap_testimonial_item_for_widget($widget);

    if ($item === null) {
        return $content;
    }

    $company = esc_html($item['company']);
    $meta    = esc_html($item['role'] . ' · ' . $item['name']);
    $text    = esc_html($item['text']);
    $logo    = esc_url($item['logo']);

    $patterns = array(
        '#(<div class="wdt-content-title"><h5><a[^>]*>)[^<]*(</a></h5></div>)#' => '$1' . $company . '$2',
        '#(<div class="wdt-content-subtitle">)\s*[^<]*\s*(</div>)#'           => '$1' . $meta . '$2',
        '#(<div class="wdt-content-description">).*?(</div>)#s'                => '$1' . $text . '$2',
    );

    foreach ($patterns as $pattern => $replacement) {
        $updated = preg_replace($pattern, $replacement, $content, 1);
        if (is_string($updated)) {
            $content = $updated;
        }
    }

    if ($logo !== '') {
        $content = preg_replace(
            '#(<div class="wdt-content-image"><a[^>]*><img[^>]*\s)src="[^"]+"#',
            '$1src="' . $logo . '"',
            $content,
            1
        );
        $content = preg_replace(
            '#(<div class="wdt-content-image"><a[^>]*><img[^>]*\s)srcset="[^"]*"#',
            '$1',
            $content,
            1
        );
        $content = preg_replace(
            '#(<div class="wdt-content-image"><a[^>]*><img[^>]*\s)sizes="[^"]*"#',
            '$1',
            $content,
            1
        );
        $updated = preg_replace(
            '/(<div class="wdt-content-image"><a[^>]*><img[^>]+)alt="[^"]*"/',
            '$1alt="' . esc_attr($item['company']) . '"',
            $content,
            1
        );
        if (is_string($updated)) {
            $content = $updated;
        }
    }

    return $content;
}

/**
 * Oculta fechas demo del tema (April 2023).
 *
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_testimonial_hide_demo_date(string $content, $widget): string
{
    if (!gruposnap_should_override_testimonials() || $widget->get_name() !== 'heading') {
        return $content;
    }

    if (stripos($content, 'april 2023') === false && stripos($content, 'abril 2023') === false) {
        return $content;
    }

    return '';
}

/**
 * Botón tras el último widget de testimonio (luego JS lo mueve al pie de la sección).
 *
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_testimonials_google_cta_after_last($widget): void
{
    if (!gruposnap_should_override_testimonials() || $widget->get_name() !== 'wdt-testimonial') {
        return;
    }

    if ($widget->get_id() !== '9bb60d6') {
        return;
    }

    gruposnap_render_google_reviews_cta();
}

/**
 * Reubica el CTA al final de la sección de testimonios en el DOM.
 */
function gruposnap_testimonials_reposition_cta_script(): void
{
    if (!gruposnap_should_override_testimonials()) {
        return;
    }

    $section_id = gruposnap_testimonials_section_id();
    ?>
    <script>
    (function () {
        function placeReviewsCta() {
            var section = document.querySelector('.elementor-element-<?php echo esc_js($section_id); ?>');
            var cta = document.querySelector('.gruposnap-google-reviews-wrap--section');
            if (section && cta && cta.parentNode !== section) {
                section.appendChild(cta);
            }
        }
        if (document.readyState !== 'loading') {
            placeReviewsCta();
        } else {
            document.addEventListener('DOMContentLoaded', placeReviewsCta);
        }
    })();
    </script>
    <?php
}

function gruposnap_enqueue_testimonials_assets(): void
{
    if (!gruposnap_should_override_testimonials()) {
        return;
    }

    wp_enqueue_style(
        'gruposnap-testimonials',
        get_stylesheet_directory_uri() . '/assets/css/testimonials.css',
        array('gruposnap-child'),
        GRUPOSNAP_THEME_VERSION
    );
}

add_action('wp_enqueue_scripts', 'gruposnap_enqueue_testimonials_assets', 100);
add_action('elementor/widget/before_render_content', 'gruposnap_testimonial_apply_wrapper_class', 5);
add_filter('elementor/widget/render_content', 'gruposnap_testimonial_replace_content', 12, 2);
add_filter('elementor/widget/render_content', 'gruposnap_testimonial_hide_demo_date', 12, 2);
add_action('elementor/frontend/widget/after_render', 'gruposnap_testimonials_google_cta_after_last', 20);
add_action('wp_footer', 'gruposnap_testimonials_reposition_cta_script', 25);
