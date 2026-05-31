<?php
/**
 * Panel de actualizaciones GitHub (tema hijo GrupoSnap).
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action(
    'admin_menu',
    static function (): void {
        add_theme_page(
            __('GrupoSnap · Actualizaciones', 'gruposnap'),
            __('GrupoSnap · Updates', 'gruposnap'),
            'update_themes',
            'gruposnap-updates',
            'gruposnap_render_updates_page'
        );
    }
);

function gruposnap_render_updates_page(): void
{
    if (!current_user_can('update_themes')) {
        wp_die(esc_html__('Sin permisos.', 'gruposnap'), '', array('response' => 403));
    }

    $current  = defined('GRUPOSNAP_THEME_VERSION') ? GRUPOSNAP_THEME_VERSION : '0';
    $release  = gruposnap_github_get_latest_release();
    $latest   = '';
    $has_new  = false;
    $gh_error = gruposnap_github_get_last_error();

    if (is_array($release) && !empty($release['tag_name'])) {
        $latest  = gruposnap_normalize_version((string) $release['tag_name']);
        $has_new = version_compare($latest, $current, '>');
    }

    $has_token = gruposnap_github_token_configured();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('GrupoSnap · Actualizaciones', 'gruposnap'); ?></h1>
        <?php if (isset($_GET['checked'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Se consultó GitHub de nuevo.', 'gruposnap'); ?></p>
            </div>
        <?php endif; ?>

        <p class="description">
            <?php
            esc_html_e(
                'Tema activo: GrupoSnap (tema hijo). Las mejoras de este proyecto se publican desde GitHub Releases.',
                'gruposnap'
            );
            ?>
        </p>

        <hr>

        <h2 class="title"><?php esc_html_e('Actualizaciones del tema (GitHub)', 'gruposnap'); ?></h2>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e('Versión instalada', 'gruposnap'); ?></th>
                    <td><code><?php echo esc_html((string) $current); ?></code></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Última versión en GitHub', 'gruposnap'); ?></th>
                    <td>
                        <?php if ($latest !== '') : ?>
                            <code><?php echo esc_html($latest); ?></code>
                            <?php if ($has_new) : ?>
                                <span style="color:#d63638;font-weight:600;">
                                    <?php esc_html_e('Actualización disponible', 'gruposnap'); ?>
                                </span>
                            <?php else : ?>
                                <span style="color:#1a7f37;font-weight:600;">
                                    <?php esc_html_e('Estás al día', 'gruposnap'); ?>
                                </span>
                            <?php endif; ?>
                        <?php else : ?>
                            <em><?php esc_html_e('No se pudo consultar GitHub.', 'gruposnap'); ?></em>
                            <?php if ($gh_error !== '') : ?>
                                <p class="description" style="margin-top:0.5em;">
                                    <?php echo esc_html($gh_error); ?>
                                </p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Repositorio configurado', 'gruposnap'); ?></th>
                    <td>
                        <code><?php echo esc_html((string) (defined('GRUPOSNAP_GITHUB_REPO') ? GRUPOSNAP_GITHUB_REPO : '')); ?></code>
                        <p class="description">
                            <?php
                            esc_html_e(
                                'Cada tag publica gruposnap.zip en el release. WordPress lo instala desde Actualizaciones.',
                                'gruposnap'
                            );
                            ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Token GitHub (wp-config)', 'gruposnap'); ?></th>
                    <td>
                        <?php if ($has_token) : ?>
                            <span style="color:#1a7f37;font-weight:600;">
                                <?php esc_html_e('Configurado (repo privado)', 'gruposnap'); ?>
                            </span>
                        <?php else : ?>
                            <span style="color:#1a7f37;font-weight:600;">
                                <?php esc_html_e('No necesario si el repo es público y el release trae gruposnap.zip', 'gruposnap'); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="gruposnap_check_update">
            <?php wp_nonce_field('gruposnap_check_update'); ?>
            <?php submit_button(__('Buscar actualizaciones ahora', 'gruposnap'), 'secondary', 'submit', false); ?>
        </form>
        <?php if ($has_new) : ?>
            <p style="margin-top:1em;">
                <?php
                printf(
                    esc_html__('Ve a %s para instalar la nueva versión.', 'gruposnap'),
                    '<a href="' . esc_url(admin_url('update-core.php')) . '">' . esc_html__('Escritorio → Actualizaciones', 'gruposnap') . '</a>'
                );
                ?>
            </p>
        <?php endif; ?>
    </div>
    <?php
}
