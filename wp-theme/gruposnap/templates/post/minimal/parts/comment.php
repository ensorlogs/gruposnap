<?php
if (!defined('ABSPATH')) {
    exit;
}

if (post_password_required() || (!comments_open() && !get_comments_number())) {
    return;
}
?>
	<!-- Entry Comment -->
		<div class="single-entry-comments">
		<div class="comment-wrap"><?php
			comments_popup_link(
				esc_html__('Sin comentarios', 'gruposnap'),
				esc_html__('1 comentario', 'gruposnap'),
				esc_html__('% comentarios', 'gruposnap'),
				'',
				esc_html__('Comentarios cerrados', 'gruposnap')
			); ?>
		</div>
	</div><!-- Entry Comment -->
