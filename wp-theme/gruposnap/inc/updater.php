<?php
/**
 * Auto-actualización del tema hijo desde GitHub Releases.
 *
 * Cada tag vX.Y.Z debe incluir el asset `gruposnap.zip` (workflow Release Theme).
 *
 * define('GRUPOSNAP_GITHUB_REPO', 'ensorlogs/gruposnap'); // opcional
 * define('GRUPOSNAP_GITHUB_TOKEN', '…'); // solo repo privado; no versionar
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('GRUPOSNAP_GITHUB_REPO')) {
    define('GRUPOSNAP_GITHUB_REPO', 'ensorlogs/gruposnap');
}
if (!defined('GRUPOSNAP_GITHUB_ASSET')) {
    define('GRUPOSNAP_GITHUB_ASSET', 'gruposnap.zip');
}

define('GRUPOSNAP_GH_PACKAGE_SCHEME', 'gruposnap-gh-tag://');

/** @var string|null */
$GLOBALS['gruposnap_github_last_error'] = null;

/**
 * @return array<string, string>
 */
function gruposnap_github_headers(): array
{
    $headers = array(
        'Accept'               => 'application/vnd.github+json',
        'X-GitHub-Api-Version' => '2022-11-28',
        'User-Agent'           => 'Gruposnap-Theme-Updater',
    );
    if (gruposnap_github_token_configured()) {
        $headers['Authorization'] = 'Bearer ' . GRUPOSNAP_GITHUB_TOKEN;
    }
    return $headers;
}

function gruposnap_github_token_configured(): bool
{
    return defined('GRUPOSNAP_GITHUB_TOKEN') && GRUPOSNAP_GITHUB_TOKEN !== '';
}

function gruposnap_theme_slug(): string
{
    return get_stylesheet();
}

function gruposnap_github_get_last_error(): string
{
    global $gruposnap_github_last_error;
    return is_string($gruposnap_github_last_error) ? $gruposnap_github_last_error : '';
}

/**
 * @param array<string, mixed> $args
 * @return array{code: int, body: mixed, error: string}
 */
function gruposnap_github_api_get(string $path, array $args = array()): array
{
    global $gruposnap_github_last_error;

    $repo = (string) apply_filters('gruposnap_github_repo', GRUPOSNAP_GITHUB_REPO);
    if ($repo === '') {
        $gruposnap_github_last_error = __('Repositorio GitHub vacío (`GRUPOSNAP_GITHUB_REPO`).', 'gruposnap');
        return array('code' => 0, 'body' => null, 'error' => $gruposnap_github_last_error);
    }

    $query = !empty($args) ? '?' . http_build_query($args) : '';
    $url   = sprintf(
        'https://api.github.com/repos/%s%s',
        gruposnap_rawurlencode_path($repo),
        $path . $query
    );

    $response = wp_remote_get(
        $url,
        array(
            'timeout' => 20,
            'headers' => gruposnap_github_headers(),
        )
    );

    if (is_wp_error($response)) {
        $gruposnap_github_last_error = $response->get_error_message();
        return array('code' => 0, 'body' => null, 'error' => $gruposnap_github_last_error);
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $raw  = (string) wp_remote_retrieve_body($response);
    $body = json_decode($raw, true);

    if ($code === 403) {
        $gruposnap_github_last_error = __(
            'GitHub devolvió 403. Añade `GRUPOSNAP_GITHUB_TOKEN` en wp-config.php (PAT con scope repo).',
            'gruposnap'
        );
        return array('code' => $code, 'body' => $body, 'error' => $gruposnap_github_last_error);
    }

    if ($code === 404) {
        $gruposnap_github_last_error = __(
            'No hay releases publicados. Publica un tag `vX.Y.Z` (workflow Release Theme).',
            'gruposnap'
        );
        return array('code' => $code, 'body' => $body, 'error' => $gruposnap_github_last_error);
    }

    if ($code < 200 || $code >= 300) {
        $msg = is_array($body) && isset($body['message']) ? (string) $body['message'] : '';
        $gruposnap_github_last_error = sprintf(
            __('GitHub respondió %1$d%2$s', 'gruposnap'),
            $code,
            $msg !== '' ? ': ' . $msg : ''
        );
        return array('code' => $code, 'body' => $body, 'error' => $gruposnap_github_last_error);
    }

    $gruposnap_github_last_error = null;
    return array('code' => $code, 'body' => $body, 'error' => '');
}

/**
 * @param array<int, array<string, mixed>> $releases
 * @return array<string, mixed>|null
 */
function gruposnap_github_pick_best_release(array $releases): ?array
{
    $best     = null;
    $best_ver = '0';

    foreach ($releases as $release) {
        if (!is_array($release)) {
            continue;
        }
        if (!empty($release['draft']) || !empty($release['prerelease'])) {
            continue;
        }
        $tag = isset($release['tag_name']) ? (string) $release['tag_name'] : '';
        if ($tag === '') {
            continue;
        }
        $ver      = gruposnap_normalize_version($tag);
        $has_zip  = gruposnap_github_asset_package_url($release) !== '';
        $best_zip = $best !== null ? gruposnap_github_asset_package_url($best) !== '' : false;
        if (
            $best === null
            || version_compare($ver, $best_ver, '>')
            || (version_compare($ver, $best_ver, '==') && $has_zip && !$best_zip)
        ) {
            $best     = $release;
            $best_ver = $ver;
        }
    }

    return $best;
}

function gruposnap_github_asset_package_url(array $release): string
{
    $asset_name = (string) apply_filters('gruposnap_github_asset_name', GRUPOSNAP_GITHUB_ASSET);
    if (empty($release['assets']) || !is_array($release['assets'])) {
        return '';
    }
    foreach ($release['assets'] as $asset) {
        if (!is_array($asset)) {
            continue;
        }
        $name = isset($asset['name']) ? (string) $asset['name'] : '';
        if ($name === $asset_name && !empty($asset['browser_download_url'])) {
            return (string) $asset['browser_download_url'];
        }
    }
    return '';
}

function gruposnap_github_resolve_package(array $release): string
{
    $zip_url = gruposnap_github_asset_package_url($release);
    if ($zip_url !== '') {
        return $zip_url;
    }
    if (gruposnap_github_token_configured()) {
        return gruposnap_github_package_descriptor($release);
    }
    return '';
}

/**
 * @return array<string, mixed>|null
 */
function gruposnap_github_get_latest_release(bool $force = false): ?array
{
    $key = 'gruposnap_gh_release';
    if (!$force) {
        $cached = get_transient($key);
        if (is_array($cached)) {
            return $cached;
        }
    }

    $release = null;
    $result  = gruposnap_github_api_get('/releases/latest');

    if ($result['code'] === 200 && is_array($result['body']) && !empty($result['body']['tag_name'])) {
        $release = $result['body'];
    }

    if ($release === null) {
        $list = gruposnap_github_api_get('/releases', array('per_page' => 30));
        if ($list['code'] === 200 && is_array($list['body'])) {
            $release = gruposnap_github_pick_best_release($list['body']);
        }
    }

    if ($release === null) {
        delete_transient($key);
        return null;
    }

    set_transient($key, $release, 6 * HOUR_IN_SECONDS);
    return $release;
}

function gruposnap_rawurlencode_path(string $path): string
{
    $parts = explode('/', $path);
    return implode('/', array_map('rawurlencode', $parts));
}

function gruposnap_normalize_version(string $tag): string
{
    $tag = trim($tag);
    if ($tag !== '' && (strncmp($tag, 'v', 1) === 0 || strncmp($tag, 'V', 1) === 0)) {
        $tag = substr($tag, 1);
    }
    return $tag;
}

function gruposnap_github_package_descriptor(array $release): string
{
    $tag = isset($release['tag_name']) ? (string) $release['tag_name'] : '';
    if ($tag === '') {
        return '';
    }
    return GRUPOSNAP_GH_PACKAGE_SCHEME . $tag;
}

function gruposnap_locate_theme_dir_in_tree(string $root): ?string
{
    $root = trailingslashit($root);
    $direct = array(
        $root . 'wp-theme/gruposnap',
        $root . 'gruposnap',
    );
    foreach ($direct as $path) {
        if (is_dir($path) && is_readable($path . '/style.css')) {
            return $path;
        }
    }

    $entries = @scandir($root);
    if (!is_array($entries)) {
        return null;
    }
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $nested = $root . $entry;
        if (!is_dir($nested)) {
            continue;
        }
        $theme = $nested . '/wp-theme/gruposnap';
        if (is_dir($theme) && is_readable($theme . '/style.css')) {
            return $theme;
        }
        if (is_dir($nested . '/gruposnap') && is_readable($nested . '/gruposnap/style.css')) {
            return $nested . '/gruposnap';
        }
    }

    return null;
}

/**
 * @return string|WP_Error
 */
function gruposnap_github_build_package_zip_from_tag(string $tag)
{
    if (!gruposnap_github_token_configured()) {
        return new WP_Error(
            'gruposnap_no_github_token',
            __('Define `GRUPOSNAP_GITHUB_TOKEN` en wp-config.php para actualizar desde GitHub.', 'gruposnap')
        );
    }

    $repo = (string) apply_filters('gruposnap_github_repo', GRUPOSNAP_GITHUB_REPO);
    $url  = sprintf(
        'https://api.github.com/repos/%s/zipball/%s',
        gruposnap_rawurlencode_path($repo),
        rawurlencode($tag)
    );

    $response = wp_remote_get(
        $url,
        array(
            'timeout'  => 300,
            'headers'  => array_merge(
                gruposnap_github_headers(),
                array('Accept' => 'application/vnd.github+json')
            ),
            'stream'   => true,
            'filename' => wp_tempnam('gruposnap-src'),
        )
    );

    if (is_wp_error($response)) {
        return $response;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 300) {
        return new WP_Error(
            'gruposnap_github_download',
            sprintf(__('No se pudo descargar el código del tag (%d).', 'gruposnap'), $code)
        );
    }

    $src_zip = $response['filename'] ?? '';
    if ($src_zip === '' || !is_readable($src_zip)) {
        return new WP_Error('gruposnap_github_download', __('Descarga vacía desde GitHub.', 'gruposnap'));
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';

    $unpacked = wp_tempnam('gruposnap-unpack');
    if (!$unpacked) {
        return new WP_Error('gruposnap_temp', __('No se pudo crear carpeta temporal.', 'gruposnap'));
    }
    @unlink($unpacked);

    $dest_dir = $unpacked . '-dir';
    wp_mkdir_p($dest_dir);

    $unzip = unzip_file($src_zip, $dest_dir);
    @unlink($src_zip);

    if (is_wp_error($unzip)) {
        return $unzip;
    }

    $theme_dir = gruposnap_locate_theme_dir_in_tree($dest_dir);
    if ($theme_dir === null) {
        return new WP_Error(
            'gruposnap_theme_path',
            __('El zip de GitHub no contiene wp-theme/gruposnap.', 'gruposnap')
        );
    }

    if (!class_exists('ZipArchive')) {
        return new WP_Error('gruposnap_zip', __('PHP ZipArchive no está disponible en el servidor.', 'gruposnap'));
    }

    $out_zip = wp_tempnam('gruposnap-package');
    if ($out_zip === false) {
        return new WP_Error('gruposnap_temp', __('No se pudo crear el zip del tema.', 'gruposnap'));
    }
    @unlink($out_zip);

    $zip = new ZipArchive();
    if ($zip->open($out_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return new WP_Error('gruposnap_zip', __('No se pudo abrir el zip de salida.', 'gruposnap'));
    }

    $slug   = gruposnap_theme_slug();
    $parent = $slug . '/';
    $zip->addEmptyDir($slug);

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($theme_dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $full = $file->getPathname();
        $rel  = $parent . substr($full, strlen(trailingslashit($theme_dir)));
        $zip->addFile($full, str_replace('\\', '/', $rel));
    }

    $zip->close();

    return $out_zip;
}

add_filter(
    'http_request_args',
    static function ($args, $url) {
        if (
            !gruposnap_github_token_configured()
            || !is_string($url)
            || strpos($url, 'api.github.com') === false
        ) {
            return $args;
        }
        if (!is_array($args['headers'])) {
            $args['headers'] = array();
        }
        $args['headers']['Authorization'] = 'Bearer ' . GRUPOSNAP_GITHUB_TOKEN;
        return $args;
    },
    10,
    2
);

add_filter(
    'pre_set_site_transient_update_themes',
    static function ($transient) {
        if (!is_object($transient)) {
            return $transient;
        }
        $release = gruposnap_github_get_latest_release();
        if ($release === null) {
            return $transient;
        }
        $tag = isset($release['tag_name']) ? (string) $release['tag_name'] : '';
        if ($tag === '') {
            return $transient;
        }
        $new_version = gruposnap_normalize_version($tag);
        $current     = defined('GRUPOSNAP_THEME_VERSION') ? GRUPOSNAP_THEME_VERSION : '0';
        if (version_compare($new_version, $current, '<=')) {
            return $transient;
        }
        $package = gruposnap_github_resolve_package($release);
        if ($package === '') {
            global $gruposnap_github_last_error;
            if ($gruposnap_github_last_error === null || $gruposnap_github_last_error === '') {
                $gruposnap_github_last_error = __(
                    'El release no incluye gruposnap.zip. Publica un tag nuevo con el workflow Release Theme.',
                    'gruposnap'
                );
            }
            return $transient;
        }
        $slug = gruposnap_theme_slug();
        if (!isset($transient->response)) {
            $transient->response = array();
        }
        $transient->response[ $slug ] = array(
            'theme'       => $slug,
            'new_version' => $new_version,
            'url'         => isset($release['html_url']) ? (string) $release['html_url'] : '',
            'package'     => $package,
        );
        return $transient;
    }
);

add_filter(
    'upgrader_pre_download',
    static function ($reply, $package, $upgrader, $hook_extra) {
        if (!is_string($package) || strpos($package, GRUPOSNAP_GH_PACKAGE_SCHEME) !== 0) {
            return $reply;
        }
        $slug = gruposnap_theme_slug();
        if (!isset($hook_extra['theme']) || $hook_extra['theme'] !== $slug) {
            return $reply;
        }

        $tag = substr($package, strlen(GRUPOSNAP_GH_PACKAGE_SCHEME));
        if ($tag === '') {
            return new WP_Error('gruposnap_package', __('Tag de release inválido.', 'gruposnap'));
        }

        return gruposnap_github_build_package_zip_from_tag($tag);
    },
    10,
    4
);

add_filter(
    'upgrader_source_selection',
    static function ($source, $remote_source, $upgrader, $hook_extra) {
        if (!is_string($source) || $source === '') {
            return $source;
        }
        $slug = gruposnap_theme_slug();
        if (!isset($hook_extra['theme']) || $hook_extra['theme'] !== $slug) {
            return $source;
        }
        global $wp_filesystem;
        if (!$wp_filesystem) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }
        $expected = trailingslashit(dirname($source)) . $slug;
        if (rtrim($source, '/\\') === rtrim($expected, '/\\')) {
            return $source;
        }
        if ($wp_filesystem->move(rtrim($source, '/\\'), rtrim($expected, '/\\'), true)) {
            return trailingslashit($expected);
        }
        return $source;
    },
    10,
    4
);

add_action(
    'admin_post_gruposnap_check_update',
    static function (): void {
        if (!current_user_can('update_themes')) {
            wp_die(esc_html__('Sin permisos.', 'gruposnap'), '', array('response' => 403));
        }
        check_admin_referer('gruposnap_check_update');
        delete_transient('gruposnap_gh_release');
        gruposnap_github_get_latest_release(true);
        delete_site_transient('update_themes');
        wp_safe_redirect(
            add_query_arg(
                array('page' => 'gruposnap-updates', 'checked' => '1'),
                admin_url('themes.php')
            )
        );
        exit;
    }
);
