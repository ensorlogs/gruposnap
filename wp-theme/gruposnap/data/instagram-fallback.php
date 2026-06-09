<?php
/**
 * Posts de respaldo cuando el servidor no puede consultar la API de Instagram.
 * Imágenes en assets/images/instagram/.
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

return array(
    array(
        'permalink'  => 'https://www.instagram.com/p/DZVLCsGnPNg/',
        'image_file' => 'post-1.jpg',
        'caption'    => '🎨 Tu diseño merece verse EXACTAMENTE como lo imaginaste. Con impresión DTF podemos plasmar imágenes con calidad fotográfica y alta resolución sobre prácticamente cualquier tipo de textil.',
    ),
    array(
        'permalink'  => 'https://www.instagram.com/p/DZNqZrBiDwU/',
        'image_file' => 'post-2.jpg',
        'caption'    => 'Una buena gorra protege del sol. Una personalizada además promociona tu marca gratis todo el día.',
    ),
    array(
        'permalink'  => 'https://www.instagram.com/p/DZFNEXogdsf/',
        'image_file' => 'post-3.jpg',
        'caption'    => '🌴 En Punta Cana el sol vende… pero un buen sombrero personalizado vende más.',
    ),
);
