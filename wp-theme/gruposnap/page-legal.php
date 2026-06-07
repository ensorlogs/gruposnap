<?php
/**
 * Template Name: Documento legal (GrupoSnap)
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    $page_slug    = get_post_field('post_name', get_the_ID());
    $page_excerpt = has_excerpt() ? get_the_excerpt() : '';
    $updated_ts   = (int) get_post_modified_time('U', true);
    $updated      = $updated_ts ? wp_date('d/m/Y', $updated_ts) : '';

    $contact_email = apply_filters('gruposnap_legal_contact_email', get_option('admin_email'));
    $contact_page  = get_page_by_path('contact');
    $contact_url   = function_exists('gruposnap_lang_url')
        ? gruposnap_lang_url('/contact/')
        : ($contact_page ? get_permalink($contact_page) : home_url('/contact/'));

    $cross = function_exists('gruposnap_legal_cross_labels') ? gruposnap_legal_cross_labels() : array();
    ?>
    <main class="gsnap-legal-page" id="main-content">
        <section class="gsnap-legal-hero" aria-labelledby="gsnap-legal-title">
            <div class="gsnap-legal-hero__inner">
                <p class="gsnap-legal-hero__eyebrow"><?php esc_html_e('Documento legal · Grupo Snap', 'gruposnap'); ?></p>
                <h1 id="gsnap-legal-title" class="gsnap-legal-hero__title"><?php the_title(); ?></h1>
                <?php if ($page_excerpt !== '') : ?>
                    <p class="gsnap-legal-hero__lead"><?php echo esc_html($page_excerpt); ?></p>
                <?php endif; ?>
                <?php if ($updated !== '') : ?>
                    <div class="gsnap-legal-hero__meta">
                        <span class="gsnap-legal-pill"><?php printf(esc_html__('Actualizado · %s', 'gruposnap'), esc_html($updated)); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <div class="gsnap-legal-wrap">
            <article <?php post_class('gsnap-legal-body entry-content'); ?>>
                <?php
                the_content();
                if (!get_the_content()) {
                    $seed = function_exists('gruposnap_legal_seed_html') ? gruposnap_legal_seed_html($page_slug) : '';
                    if ($seed !== '') {
                        echo wp_kses_post($seed);
                    }
                }
                ?>
            </article>
        </div>

        <div class="gsnap-legal-foot">
            <div class="gsnap-legal-cta-card">
                <div>
                    <p class="gsnap-legal-cta-card__title"><?php esc_html_e('¿Te queda alguna duda con este documento?', 'gruposnap'); ?></p>
                    <p class="gsnap-legal-cta-card__sub">
                        <?php
                        printf(
                            /* translators: 1: open mailto, 2: email, 3: close anchor */
                            esc_html__('Escríbenos a %1$s%2$s%3$s o usa el formulario de contacto.', 'gruposnap'),
                            '<a href="mailto:' . esc_attr($contact_email) . '">',
                            esc_html($contact_email),
                            '</a>'
                        );
                        ?>
                    </p>
                </div>
                <a class="gsnap-legal-cta-card__action" href="<?php echo esc_url($contact_url); ?>">
                    <?php esc_html_e('Contactar', 'gruposnap'); ?>
                </a>
            </div>
            <?php if ($cross) : ?>
                <nav class="gsnap-legal-cross" aria-label="<?php esc_attr_e('Otros documentos legales', 'gruposnap'); ?>">
                    <p class="gsnap-legal-cross__title"><?php esc_html_e('Documentos relacionados', 'gruposnap'); ?></p>
                    <?php
                    foreach ($cross as $slug => $label) :
                        $page = get_page_by_path('legal/' . $slug);
                        if (!$page) {
                            continue;
                        }
                        $url = function_exists('gruposnap_lang_url')
                            ? gruposnap_lang_url('/legal/' . $slug . '/')
                            : get_permalink($page);
                        $current = ($slug === $page_slug);
                        ?>
                        <a href="<?php echo esc_url($url); ?>"<?php echo $current ? ' class="is-current" aria-current="page"' : ''; ?>><?php echo esc_html($label); ?></a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
        </div>
    </main>
    <?php
endwhile;

get_footer();
