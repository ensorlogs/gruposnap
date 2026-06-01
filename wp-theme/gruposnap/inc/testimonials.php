<?php
/**
 * Testimonios GrupoSnap — contenido real, logos de marcas y carrusel en la home.
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

/** ID de la sección Elementor de testimonios en la home. */
function gruposnap_testimonials_section_id(): string
{
    return '25b718d';
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
 * SVG de marcas en uploads (mismo orden que el carrusel del hero).
 *
 * @return array<string, string> slug => nombre de archivo
 */
function gruposnap_brand_svg_upload_sources(): array
{
    /*
     * Mismo orden que el carrusel del hero (bfd27b4):
     * 1 MAPFRE · 2–3 otras · 4 Carrefour · 5 CEMEX · 6 Claro · 7 KIA · 8 Nestlé · 9 BlueMall · 10 Huawei
     */
    return array(
        'carrefour' => '7c2ef0_e9224989efbb45859a0c3a5f82fb061b_mv2_1_.svg',
        'cemex'     => '7c2ef0_e24cb8b3744a4c9d991261f5a91d1154_mv2.svg',
        'claro'     => '7c2ef0_d945c000fec740f5af175c255802d4a9_mv2.svg',
        'nestle'    => '7c2ef0_d2dc2368e9174bb7a6c4f1d67cf29361_mv2_1_.svg',
        'bluemall'  => '7c2ef0_843e215208bb4d079db890a41becb6acmv21.svg',
        'huawei'    => '7c2ef0_66e1738d6d474a52bb26d36fc0edb8e8mv21.svg',
    );
}

/**
 * Sincroniza los SVG del tema con uploads (evita logos desfasados en caché).
 */
function gruposnap_sync_brand_logo_assets(): void
{
    $upload_dir = WP_CONTENT_DIR . '/uploads/2026/04';
    $brand_dir  = get_stylesheet_directory() . '/assets/images/brands';

    if (!is_dir($upload_dir) || !is_dir($brand_dir)) {
        return;
    }

    foreach (gruposnap_brand_svg_upload_sources() as $slug => $filename) {
        $source = $upload_dir . '/' . $filename;

        if (!is_file($source)) {
            $matches = glob($upload_dir . '/*' . pathinfo($filename, PATHINFO_FILENAME) . '*');
            $source  = is_array($matches) && isset($matches[0]) ? $matches[0] : '';
        }

        if ($source !== '' && is_file($source)) {
            copy($source, $brand_dir . '/' . $slug . '.svg');
        }
    }
}

add_action('after_setup_theme', 'gruposnap_sync_brand_logo_assets', 20);

/**
 * URL del logo de marca (tema hijo; respaldo en uploads).
 */
function gruposnap_brand_logo_url(string $slug): string
{
    $brand_dir = get_stylesheet_directory() . '/assets/images/brands';
    $extensions = array('svg', 'png');

    foreach ($extensions as $extension) {
        $theme_file = $brand_dir . '/' . $slug . '.' . $extension;
        if (is_file($theme_file)) {
            return get_stylesheet_directory_uri() . '/assets/images/brands/' . $slug . '.' . $extension
                . '?v=' . (string) filemtime($theme_file);
        }
    }

    $sources = gruposnap_brand_svg_upload_sources();
    if (isset($sources[$slug])) {
        return content_url('/uploads/2026/04/' . $sources[$slug]);
    }

    return '';
}

/**
 * Bloque HTML del logo en testimonios (sin fotos demo ni atributos que estiran).
 *
 * @param array{company:string,name:string,role:string,text:string,logo:string} $item
 */
function gruposnap_testimonial_logo_image_markup(array $item): string
{
    $company = esc_html($item['company']);
    $alt     = esc_attr($item['company']);
    $logo    = esc_url($item['logo']);

    if ($logo !== '') {
        return '<div class="wdt-content-image-wrapper gruposnap-testimonial-logo-wrap">'
            . '<div class="wdt-content-image gruposnap-testimonial-logo">'
            . '<img class="gruposnap-brand-logo" loading="lazy" decoding="async" src="' . $logo . '" alt="' . $alt . '" />'
            . '</div></div>';
    }

    return '<div class="wdt-content-image-wrapper gruposnap-testimonial-logo-wrap">'
        . '<div class="wdt-content-image gruposnap-testimonial-logo gruposnap-testimonial-logo--fallback" role="img" aria-label="' . $alt . '">'
        . '<span class="gruposnap-testimonial-logo__fallback">' . $company . '</span>'
        . '</div></div>';
}

/**
 * @return array<string, array{company:string,name:string,role:string,text:string,logo:string}>
 */
function gruposnap_testimonials_data(): array
{
    return array(
        'coca-cola' => array(
            'company' => 'Coca-Cola',
            'name'    => 'María González',
            'role'    => 'Trade marketing',
            'text'    => 'Coordinamos activaciones con material POP y uniformes para varios puntos de venta. La impresión fue impecable, cumplieron plazos ajustados y el equipo respondió rápido a cada ajuste.',
            'logo'    => gruposnap_brand_logo_url('coca-cola'),
        ),
        'banesco' => array(
            'company' => 'Banesco',
            'name'    => 'José Ramírez',
            'role'    => 'Comunicaciones corporativas',
            'text'    => 'Para nuestras campañas internas y eventos necesitábamos piezas con acabado premium. Grupo Snap entregó a tiempo, con control de calidad constante y una coordinación muy clara.',
            'logo'    => gruposnap_brand_logo_url('banesco'),
        ),
        'forbes' => array(
            'company' => 'Forbes',
            'name'    => 'Andrea Ruiz',
            'role'    => 'Producción editorial',
            'text'    => 'El material promocional para nuestros eventos llegó impecable y alineado a la línea gráfica de la marca. Buen seguimiento en cada etapa y flexibilidad ante cambios de último momento.',
            'logo'    => gruposnap_brand_logo_url('forbes'),
        ),
        'carolx-farmacia' => array(
            'company' => 'CarolX Farmacia',
            'name'    => 'Laura Pérez',
            'role'    => 'Gerente de marketing',
            'text'    => 'Los kits y señalética para sucursales quedaron muy bien presentados. Fue sencillo coordinar volúmenes por región y la calidad se mantuvo uniforme en toda la cadena.',
            'logo'    => gruposnap_brand_logo_url('carolx-farmacia'),
        ),
        'frito-lay' => array(
            'company' => 'Frito Lay',
            'name'    => 'Carlos Méndez',
            'role'    => 'Activaciones de marca',
            'text'    => 'Necesitábamos merchandising para una activación nacional y armaron la producción completa sin fricciones. Excelente relación calidad-precio y respuesta ágil cuando el brief cambió.',
            'logo'    => gruposnap_brand_logo_url('frito-lay'),
        ),
        'ars-universal' => array(
            'company' => 'ARS Universal',
            'name'    => 'Patricia Núñez',
            'role'    => 'Relaciones institucionales',
            'text'    => 'Para ferias y jornadas de afiliados prepararon regalos corporativos y material de stand con acabados profesionales. El proceso fue transparente desde la cotización hasta la entrega.',
            'logo'    => gruposnap_brand_logo_url('ars-universal'),
        ),
        'carrefour' => array(
            'company' => 'Carrefour',
            'name'    => 'Daniela Romero',
            'role'    => 'Trade marketing',
            'text'    => 'Para campañas en piso de venta y lanzamientos coordinamos POP, exhibidores y material de punto de venta con tiempos muy ajustados. La ejecución fue uniforme en todas las sucursales.',
            'logo'    => gruposnap_brand_logo_url('carrefour'),
        ),
        'claro' => array(
            'company' => 'Claro',
            'name'    => 'Miguel Herrera',
            'role'    => 'Activaciones de marca',
            'text'    => 'El merchandising para activaciones en calle y centros comerciales llegó con excelente acabado. El equipo de Grupo Snap fue proactivo y resolvió ajustes de diseño sin retrasar la campaña.',
            'logo'    => gruposnap_brand_logo_url('claro'),
        ),
        'cemex' => array(
            'company' => 'CEMEX',
            'name'    => 'Sofía Martínez',
            'role'    => 'Marketing institucional',
            'text'    => 'Necesitábamos material corporativo y regalos para eventos con imagen sobria y profesional. Cumplieron especificaciones técnicas, plazos y estándares de calidad en cada entrega.',
            'logo'    => gruposnap_brand_logo_url('cemex'),
        ),
        'nestle' => array(
            'company' => 'Nestlé',
            'name'    => 'Ricardo Peña',
            'role'    => 'Activaciones de marca',
            'text'    => 'Armamos kits promocionales y material POP para una activación regional. La producción fue consistente, el empaque impecable y la logística de entrega muy bien coordinada.',
            'logo'    => gruposnap_brand_logo_url('nestle'),
        ),
        'bluemall' => array(
            'company' => 'BlueMall',
            'name'    => 'Valentina Soto',
            'role'    => 'Marketing comercial',
            'text'    => 'Para temporadas comerciales y eventos en el mall requeríamos señalética, regalos y piezas de alto impacto visual. Grupo Snap entregó con calidad premium y gran atención al detalle.',
            'logo'    => gruposnap_brand_logo_url('bluemall'),
        ),
        'huawei' => array(
            'company' => 'Huawei',
            'name'    => 'Alejandro Vega',
            'role'    => 'Comunicaciones',
            'text'    => 'El material para lanzamientos y capacitaciones internas quedó alineado a nuestra línea gráfica. Respuesta rápida, buena asesoría en acabados y entregas puntuales en cada fase del proyecto.',
            'logo'    => gruposnap_brand_logo_url('huawei'),
        ),
    );
}

/**
 * Orden de tarjetas en el carrusel de testimonios (12 marcas).
 *
 * @return string[]
 */
function gruposnap_testimonials_carousel_slugs(): array
{
    return array(
        'coca-cola',
        'forbes',
        'frito-lay',
        'banesco',
        'carolx-farmacia',
        'ars-universal',
        'carrefour',
        'claro',
        'cemex',
        'nestle',
        'bluemall',
        'huawei',
    );
}

/**
 * Icono de comillas del diseño Printme.
 */
function gruposnap_testimonial_quote_icon_markup(): string
{
    return '<div class="wdt-content-icon-wrapper"><div class="wdt-content-icon"><span><i><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M42.82,51.76c-.34,14.06-6.8,25.08-18.38,33a39.22,39.22,0,0,1-22,6.87c-.24,0-.51,0-.75,0V77.85c1.35-.08,2.71,0,4-.24a27.5,27.5,0,0,0,22.94-22c.35-1.69.41-3.43.61-5.22H1.71V9.27c.34,0,.69-.06,1-.06H42.88c0,.33,0,.53,0,.75C42.92,23.89,43.16,37.83,42.82,51.76Z"></path><path d="M97.86,50.26c-.37,17.82-8.94,30.54-25,38.09a36.72,36.72,0,0,1-15.32,3.27h-.7V78a27.33,27.33,0,0,0,22.1-11.38,26.72,26.72,0,0,0,5.28-16.14H56.77V9.27H97.92v1C97.92,23.58,98.14,36.92,97.86,50.26Z"></path></svg></i></span></div></div>';
}

/**
 * Una tarjeta de testimonio (misma estructura que el widget WDT).
 *
 * @param array{company:string,name:string,role:string,text:string,logo:string} $item
 */
function gruposnap_testimonial_slide_markup(array $item, string $slug): string
{
    $company = esc_html($item['company']);
    $meta    = esc_html($item['role'] . ' · ' . $item['name']);
    $text    = esc_html($item['text']);

    return sprintf(
        '<div class="swiper-slide gruposnap-testimonial-slide" data-brand="%1$s">'
        . '<div class="elementor-column wdt-custom-testmonial gruposnap-testimonial-slide__col">'
        . '<div class="elementor-widget-wrap elementor-element-populated">'
        . '<div class="gruposnap-testimonial gruposnap-testimonial--carousel">'
        . '<div class="elementor-widget-container">'
        . '<div class="wdt-testimonial-holder wdt-content-item-holder wdt-column-holder wdt-rc-template-custom-template">'
        . '<div class="wdt-column-wrapper wdt-column-gap-no"><div class="wdt-column">'
        . '<div class="wdt-content-item">'
        . '<div class="wdt-content-media-group">'
        . gruposnap_testimonial_logo_image_markup($item)
        . '<div class="wdt-content-elements-group wdt-media-group wdt-media-image-default">'
        . '<div class="wdt-content-title"><h5>%2$s</h5></div>'
        . '<div class="wdt-content-subtitle">%3$s</div>'
        . '</div>'
        . '%4$s'
        . '</div>'
        . '<div class="wdt-content-detail-group"><div class="wdt-content-description">%5$s</div></div>'
        . '</div></div></div></div></div></div></div></div></div>',
        esc_attr($slug),
        $company,
        $meta,
        gruposnap_testimonial_quote_icon_markup(),
        $text
    );
}

/**
 * Carrusel de testimonios (se inserta en la sección vía JS).
 */
function gruposnap_render_testimonials_carousel_mount(): void
{
    if (!gruposnap_should_override_testimonials()) {
        return;
    }

    $data  = gruposnap_testimonials_data();
    $slugs = gruposnap_testimonials_carousel_slugs();
    $slides = '';

    foreach ($slugs as $slug) {
        if (!isset($data[$slug])) {
            continue;
        }
        $slides .= gruposnap_testimonial_slide_markup($data[$slug], $slug);
    }

    if ($slides === '') {
        return;
    }

    $section_id = gruposnap_testimonials_section_id();
    ?>
    <div id="gruposnap-testimonials-carousel-mount" class="gruposnap-testimonials-carousel-mount" hidden
         data-section-id="<?php echo esc_attr($section_id); ?>">
        <div class="gruposnap-testimonials-carousel swiper">
            <div class="swiper-wrapper">
                <?php echo $slides; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped per slide. ?>
            </div>
        </div>
    </div>
    <?php
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

    $logo_markup = gruposnap_testimonial_logo_image_markup($item);
    $replaced    = preg_replace(
        '#<div class="wdt-content-image-wrapper"[^>]*>\s*<div class="wdt-content-image"[^>]*>.*?</div>\s*</div>#s',
        $logo_markup,
        $content,
        1
    );

    if (is_string($replaced) && $replaced !== '') {
        $content = $replaced;
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

    $script_deps = array('jquery');
    if (wp_script_is('elementor-frontend', 'registered')) {
        $script_deps[] = 'elementor-frontend';
    }

    wp_enqueue_script(
        'gruposnap-testimonials-carousel',
        get_stylesheet_directory_uri() . '/assets/js/testimonials-carousel.js',
        $script_deps,
        GRUPOSNAP_THEME_VERSION,
        true
    );
}

add_action('wp_enqueue_scripts', 'gruposnap_enqueue_testimonials_assets', 100);
add_action('elementor/widget/before_render_content', 'gruposnap_testimonial_apply_wrapper_class', 5);
add_filter('elementor/widget/render_content', 'gruposnap_testimonial_replace_content', 12, 2);
add_filter('elementor/widget/render_content', 'gruposnap_testimonial_hide_demo_date', 12, 2);
add_action('wp_footer', 'gruposnap_render_testimonials_carousel_mount', 18);
