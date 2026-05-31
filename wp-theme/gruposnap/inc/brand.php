<?php
/**
 * Colores y variables de marca GrupoSnap (tema hijo).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return array<string, string>
 */
function gruposnap_brand_colors(): array
{
    return array(
        'orange'    => '#F26200',
        'orange_rgb'=> '242, 98, 0',
        'blue'      => '#061F5E',
        'blue_rgb'  => '6, 31, 94',
        'whatsapp'  => '#25D366',
        'dark'      => '#000000',
    );
}

add_filter(
    'printme_primary_color_css_var',
    static function (string $css): string {
        $c = gruposnap_brand_colors();
        return '--wdtPrimaryColor: ' . $c['orange'] . ';';
    }
);

add_filter(
    'printme_primary_rgb_color_css_var',
    static function (string $css): string {
        $c = gruposnap_brand_colors();
        return '--wdtPrimaryColorRgb: ' . $c['orange_rgb'] . ';';
    }
);

add_filter(
    'printme_secondary_color_css_var',
    static function (string $css): string {
        $c = gruposnap_brand_colors();
        return '--wdtSecondaryColor: ' . $c['blue'] . ';';
    }
);

add_filter(
    'printme_secondary_rgb_color_css_var',
    static function (string $css): string {
        $c = gruposnap_brand_colors();
        return '--wdtSecondaryColorRgb: ' . $c['blue_rgb'] . ';';
    }
);

add_filter(
    'printme_tertiary_color_css_var',
    static function (string $css): string {
        $c = gruposnap_brand_colors();
        return '--wdtTertiaryColor: ' . $c['blue'] . ';';
    }
);

add_filter(
    'printme_tertiary_rgb_color_css_var',
    static function (string $css): string {
        $c = gruposnap_brand_colors();
        return '--wdtTertiaryColorRgb: ' . $c['blue_rgb'] . ';';
    }
);

add_filter(
    'printme_link_hover_color_css_var',
    static function (string $css): string {
        $c = gruposnap_brand_colors();
        return '--wdtLinkHoverColor: ' . $c['orange'] . ';';
    }
);

add_filter(
    'printme_link_hover_rgb_color_css_var',
    static function (string $css): string {
        $c = gruposnap_brand_colors();
        return '--wdtLinkHoverColorRgb: ' . $c['orange_rgb'] . ';';
    }
);

add_action(
    'wp_enqueue_scripts',
    static function (): void {
        wp_enqueue_style(
            'gruposnap-brand',
            get_stylesheet_directory_uri() . '/assets/css/brand.css',
            array('gruposnap-child'),
            GRUPOSNAP_THEME_VERSION
        );
    },
    120
);

add_action(
    'elementor/editor/after_enqueue_styles',
    static function (): void {
        wp_enqueue_style(
            'gruposnap-brand',
            get_stylesheet_directory_uri() . '/assets/css/brand.css',
            array(),
            GRUPOSNAP_THEME_VERSION
        );
    }
);
