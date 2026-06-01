<?php
/**
 * Entrada single — minimal (sin meta de fecha, comentarios ni autor arriba).
 *
 * @package Gruposnap
 */

$template_args['post_ID']    = $ID;
$template_args['post_Style'] = $Post_Style;
$template_args               = array_merge($template_args, printme_single_post_params());

printme_template_part('post', 'templates/' . $Post_Style . '/parts/image', '', $template_args);

echo apply_filters(
    'printme_single_post_dynamic_template_part',
    printme_get_template_part('post', 'templates/' . $Post_Style . '/parts/dynamic', '', $template_args)
);
