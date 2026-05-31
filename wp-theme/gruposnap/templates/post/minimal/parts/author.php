<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Entry Author -->
<div class="single-entry-author">
	<div class="meta-author-info">
		<span><?php esc_html_e('Por', 'gruposnap'); ?></span>
		<a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>" title="<?php echo esc_attr(sprintf(__('Ver todas las entradas de %s', 'gruposnap'), get_the_author())); ?>"><?php echo esc_html(get_the_author()); ?></a>
    </div>
</div><!-- Entry Author -->
