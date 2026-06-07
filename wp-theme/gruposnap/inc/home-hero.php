<?php
/**
 * Home hero (650305b) — restaura slider como Elementor original + quita animaciones que ocultan contenido.
 *
 * @package Gruposnap
 */

if (!defined('ABSPATH')) {
    exit;
}

const GRUPOSNAP_HOME_HERO_SECTION_ID = '650305b';

/** Inner section escritorio/tablet (Elementor: hide_mobile). */
const GRUPOSNAP_HOME_HERO_INNER_SECTION_ID = 'a5d8162';

/** Inner section móvil (Elementor: hide_desktop + hide_tablet). */
const GRUPOSNAP_HOME_HERO_MOBILE_SECTION_ID = '47df02b';

/** Versión del parche de datos/caché del hero (incrementar al cambiar visibilidad móvil). */
const GRUPOSNAP_HOME_HERO_FIX_VERSION = '5';

/** Columna vacía que reemplazó al bloque móvil en Elementor (live corrupto). */
const GRUPOSNAP_HOME_HERO_BROKEN_MOBILE_COLUMN_ID = '078d495';

/** Widget icon-list oculto en home (+500 marcas / producción personalizada). */
const GRUPOSNAP_HOME_HERO_HIGHLIGHTS_WIDGET_ID = 'bf70122';

function gruposnap_home_hero_media_urls(): array
{
    $base = get_template_directory_uri() . '/ocdi/uploads/2023';

    return array(
        'image' => $base . '/09/Home-1-Slider-bg.png',
        'video' => $base . '/10/Home-1-Slider-Bg-Vid.mp4',
    );
}

function gruposnap_is_home_page(): bool
{
    if (is_admin()) {
        return false;
    }

    $page_id = (int) (get_queried_object_id() ?: get_the_ID());

    return $page_id === 751 || (is_front_page() && (int) get_option('page_on_front') === 751);
}

/**
 * ID de la página home hero (751) en peticiones Elementor (editor, AJAX, preview).
 */
function gruposnap_home_hero_editing_post_id(): int
{
    if (isset($_GET['elementor-preview'])) {
        return (int) $_GET['elementor-preview'];
    }

    if (isset($_REQUEST['editor_post_id'])) {
        return (int) $_REQUEST['editor_post_id'];
    }

    if (isset($_GET['post'])) {
        return (int) $_GET['post'];
    }

    if (isset($_POST['post_id'])) {
        return (int) $_POST['post_id'];
    }

    return 0;
}

/**
 * Editor / preview / AJAX de Elementor para la página 751.
 */
function gruposnap_is_elementor_editing_home_hero(): bool
{
    if (gruposnap_home_hero_editing_post_id() !== 751) {
        return false;
    }

    if (!class_exists('\Elementor\Plugin')) {
        return false;
    }

    $plugin = \Elementor\Plugin::$instance;

    if (isset($plugin->editor) && $plugin->editor->is_edit_mode()) {
        return true;
    }

    if (isset($plugin->preview) && $plugin->preview->is_preview_mode()) {
        return true;
    }

    if (wp_doing_ajax()) {
        $action = isset($_REQUEST['action']) ? (string) $_REQUEST['action'] : '';

        return $action !== '' && strpos($action, 'elementor') !== false;
    }

    return false;
}

/**
 * Home hero: frontend, vista previa de Elementor, editor y página 751.
 */
function gruposnap_is_home_hero_context(): bool
{
    if (isset($_GET['elementor-preview']) && (int) $_GET['elementor-preview'] === 751) {
        return true;
    }

    if (gruposnap_is_elementor_editing_home_hero()) {
        return true;
    }

    if (is_admin() && !wp_doing_ajax()) {
        return false;
    }

    $page_id = (int) (get_queried_object_id() ?: get_the_ID());

    return $page_id === 751;
}

/**
 * Carga CSS/JS del hero en frontend, preview y editor Elementor (página 751).
 */
function gruposnap_should_enqueue_home_hero_assets(): bool
{
    if (gruposnap_is_home_hero_context()) {
        return true;
    }

    return gruposnap_home_hero_editing_post_id() === 751;
}

/**
 * @param array<string, mixed> $node
 */
function gruposnap_home_hero_node_contains_id(array $node, string $target_id): bool
{
    if (($node['id'] ?? '') === $target_id) {
        return true;
    }

    foreach ($node['elements'] ?? [] as $child) {
        if (is_array($child) && gruposnap_home_hero_node_contains_id($child, $target_id)) {
            return true;
        }
    }

    return false;
}

/**
 * El bloque 47df02b quedó vacío en live (solo columna 078d495).
 *
 * @param array<string, mixed> $node
 */
function gruposnap_home_hero_mobile_block_is_broken(array $node): bool
{
    $elements = $node['elements'] ?? [];

    if (
        count($elements) === 1
        && ($elements[0]['id'] ?? '') === GRUPOSNAP_HOME_HERO_BROKEN_MOBILE_COLUMN_ID
    ) {
        return true;
    }

    return !gruposnap_home_hero_node_contains_id($node, 'f428155')
        && !gruposnap_home_hero_node_contains_id($node, '8ef28c6');
}

/**
 * @return array<string, mixed>|null
 */
function gruposnap_home_hero_mobile_block_backup(): ?array
{
    static $backup = null;

    if ($backup !== null) {
        return $backup ?: null;
    }

    $path = get_stylesheet_directory() . '/inc/home-hero-mobile-backup.json';
    if (!is_readable($path)) {
        $backup = false;

        return null;
    }

    $decoded = json_decode((string) file_get_contents($path), true);
    $backup  = is_array($decoded) ? $decoded : false;

    return $backup ?: null;
}

/**
 * @param array<int, array<string, mixed>> $nodes
 * @param array<string, mixed>             $replacement
 */
function gruposnap_home_hero_replace_node_by_id(array &$nodes, string $id, array $replacement): bool
{
    foreach ($nodes as &$node) {
        if (!is_array($node)) {
            continue;
        }

        if (($node['id'] ?? '') === $id) {
            $node = $replacement;

            return true;
        }

        if (!empty($node['elements']) && is_array($node['elements'])) {
            if (gruposnap_home_hero_replace_node_by_id($node['elements'], $id, $replacement)) {
                return true;
            }
        }
    }

    return false;
}

/**
 * @param \Elementor\Element_Base $element
 */
function gruposnap_home_hero_is_inside_element($element, string $target_id): bool
{
    $current = $element;

    while ($current) {
        if (method_exists($current, 'get_id') && $current->get_id() === $target_id) {
            return true;
        }

        if (!method_exists($current, 'get_parent')) {
            break;
        }

        $current = $current->get_parent();
    }

    return false;
}

/**
 * @param \Elementor\Element_Base $element
 */
function gruposnap_widget_in_home_hero($element): bool
{
    return gruposnap_home_hero_is_inside_element($element, GRUPOSNAP_HOME_HERO_SECTION_ID);
}

/**
 * @param \Elementor\Element_Base $element
 */
function gruposnap_widget_in_home_hero_mobile_block($element): bool
{
    return gruposnap_home_hero_is_inside_element($element, GRUPOSNAP_HOME_HERO_MOBILE_SECTION_ID);
}

/**
 * @param array<string, mixed> $settings
 */
function gruposnap_home_hero_apply_desktop_block_settings(array &$settings, bool &$needs_save): void
{
    gruposnap_home_hero_clear_responsive_hide_settings($settings, $needs_save);
}

/**
 * Oculta el duplicado móvil legacy (47df02b) en todos los breakpoints.
 *
 * @param array<string, mixed> $settings
 */
function gruposnap_home_hero_apply_mobile_block_settings(array &$settings, bool &$needs_save): void
{
    foreach (
        array(
            'hide_desktop' => 'hidden-desktop',
            'hide_tablet'  => 'hidden-tablet',
            'hide_mobile'  => 'hidden-mobile',
        ) as $key => $value
    ) {
        if (($settings[$key] ?? '') !== $value) {
            $settings[$key] = $value;
            $needs_save     = true;
        }
    }
}

/**
 * Quita 47df02b del árbol en frontend (evita hero duplicado). Se conserva en el editor.
 *
 * @param array<int, array<string, mixed>> $nodes
 */
function gruposnap_home_hero_prune_duplicate_mobile_block(array &$nodes): void
{
    if (gruposnap_is_elementor_editing_home_hero()) {
        return;
    }

    foreach ($nodes as &$node) {
        if (!is_array($node)) {
            continue;
        }

        if (($node['id'] ?? '') === '85b941c' && !empty($node['elements']) && is_array($node['elements'])) {
            $node['elements'] = array_values(
                array_filter(
                    $node['elements'],
                    static function ($child): bool {
                        return is_array($child) && ($child['id'] ?? '') !== GRUPOSNAP_HOME_HERO_MOBILE_SECTION_ID;
                    }
                )
            );
        }

        if (!empty($node['elements']) && is_array($node['elements'])) {
            gruposnap_home_hero_prune_duplicate_mobile_block($node['elements']);
        }
    }
}

/**
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_home_hero_disable_widget_entrance_animation($widget): void
{
    if (!gruposnap_is_home_hero_context() || !gruposnap_widget_in_home_hero($widget)) {
        return;
    }

    $widget->set_settings('_animation', '');
    $widget->set_settings('_animation_delay', 0);
}

function gruposnap_home_hero_strip_invisible_class(string $content, $widget): string
{
    if (!gruposnap_is_home_hero_context() || !gruposnap_widget_in_home_hero($widget)) {
        return $content;
    }

    return str_replace('elementor-invisible', '', $content);
}

function gruposnap_home_hero_strip_animation_settings(string $html): string
{
    $html = str_replace('elementor-invisible', '', $html);

    $hidden_mobile_pattern = '/\belementor-hidden-(?:mobile|mobile_extra|phone)\b/';

    // Inner sections pueden ser <section> o <div>.
    $html = preg_replace_callback(
        '#(<(?:section|div)\b[^>]*\belementor-element-a5d8162\b[^>]*\bclass=")([^"]*)(")#i',
        static function (array $m) use ($hidden_mobile_pattern): string {
            $classes = preg_replace($hidden_mobile_pattern, '', $m[2]) ?? $m[2];

            return $m[1] . trim(preg_replace('/\s+/', ' ', $classes) ?? $classes) . $m[3];
        },
        $html
    ) ?? $html;

    $html = preg_replace_callback(
        '#(<(?:section|div)\b[^>]*\belementor-element-a5d8162\b[^>]*\bclass=")([^"]*)(")#i',
        static function (array $m) use ($hidden_mobile_pattern): string {
            $classes = preg_replace($hidden_mobile_pattern, '', $m[2]) ?? $m[2];
            $classes = str_replace('elementor-invisible', '', $classes);

            return $m[1] . trim(preg_replace('/\s+/', ' ', $classes) ?? $classes) . $m[3];
        },
        $html
    ) ?? $html;

    // Columna principal del hero y bloque interior.
    $html = preg_replace_callback(
        '#(\belementor-element-(?:85b941c|a5d8162|c4dc9ca)\b[^>]*\bclass=")([^"]*)(")#i',
        static function (array $m) use ($hidden_mobile_pattern): string {
            $classes = preg_replace($hidden_mobile_pattern, '', $m[2]) ?? $m[2];

            return $m[1] . trim(preg_replace('/\s+/', ' ', $classes) ?? $classes) . $m[3];
        },
        $html
    ) ?? $html;

    // Hero escritorio (tablet): columnas sin hidden-mobile en HTML.
    $html = preg_replace_callback(
        '#(\belementor-element-(?:82f3f66|04501ce|8082631|f694331|a35e83e|87e8edf)\b[^>]*\bclass=")([^"]*)(")#i',
        static function (array $m) use ($hidden_mobile_pattern): string {
            $classes = preg_replace($hidden_mobile_pattern, '', $m[2]) ?? $m[2];

            return $m[1] . trim(preg_replace('/\s+/', ' ', $classes) ?? $classes) . $m[3];
        },
        $html
    ) ?? $html;

    $patterns = array(
        '/,&quot;_animation&quot;:&quot;[^&]*&quot;/',
        '/&quot;_animation&quot;:&quot;[^&]*&quot;,/',
        '/,&quot;_animation_delay&quot;:\d+/',
        '/&quot;_animation_delay&quot;:\d+,/',
    );

    $html = preg_replace($patterns, '', $html) ?? $html;

    return $html;
}

function gruposnap_home_hero_patch_media(string $html): string
{
    $video = esc_url(gruposnap_home_hero_media_urls()['video']);

    return preg_replace(
        '/<video class="elementor-background-video-hosted"([^>]*)>/',
        '<video class="elementor-background-video-hosted"$1 src="' . $video . '">',
        $html,
        1
    ) ?? $html;
}

function gruposnap_home_hero_fix_section_html(string $content): string
{
    if (!gruposnap_is_home_hero_context()) {
        return $content;
    }

    $needle = 'elementor-element-' . GRUPOSNAP_HOME_HERO_SECTION_ID;
    $marker = strpos($content, $needle);
    if (false === $marker) {
        return $content;
    }

    $section_start = strrpos(substr($content, 0, $marker), '<section');
    if (false === $section_start) {
        return $content;
    }

    $depth       = 0;
    $len         = strlen($content);
    $i           = $section_start;
    $section_end = null;

    while ($i < $len) {
        if (!preg_match('/<\/?section\b/i', $content, $match, PREG_OFFSET_CAPTURE, $i)) {
            break;
        }

        $tag = $match[0][0];
        $pos = (int) $match[0][1];

        if (stripos($tag, '</section') === 0) {
            --$depth;
            if ($depth === 0) {
                $gt = strpos($content, '>', $pos);
                $section_end = false !== $gt ? $gt + 1 : $pos + strlen($tag);
                break;
            }
        } else {
            ++$depth;
        }

        $i = $pos + strlen($tag);
    }

    if (null === $section_end) {
        return $content;
    }

    $hero_html = substr($content, $section_start, $section_end - $section_start);
    $fixed = gruposnap_home_hero_patch_media(
        gruposnap_home_hero_strip_animation_settings($hero_html)
    );

    return substr($content, 0, $section_start) . $fixed . substr($content, $section_end);
}

/**
 * Elementor: a5d8162 = bloque principal; 47df02b = duplicado móvil (si existe).
 *
 * @param \Elementor\Element_Base $element
 */
function gruposnap_home_hero_prepare_section($element): void
{
    if (!gruposnap_is_home_hero_context() || !method_exists($element, 'get_id')) {
        return;
    }

    $id = $element->get_id();

    if ($id === GRUPOSNAP_HOME_HERO_INNER_SECTION_ID) {
        foreach (array('hide_mobile', 'hide_tablet', 'hide_desktop', 'hide_phone') as $setting) {
            $element->set_settings($setting, '');
        }

        foreach (
            array(
                'elementor-hidden-mobile',
                'elementor-hidden-phone',
                'elementor-hidden-mobile_extra',
                'elementor-hidden-tablet',
                'elementor-hidden-desktop',
            ) as $class
        ) {
            $element->remove_render_attribute('_wrapper', 'class', $class);
        }
    }
}

/**
 * Evita que columnas/widgets del hero queden con elementor-hidden-mobile.
 *
 * @param \Elementor\Element_Base $element
 */
function gruposnap_home_hero_clear_mobile_hide($element): void
{
    if (!gruposnap_is_home_hero_context() || !gruposnap_widget_in_home_hero($element)) {
        return;
    }

    if (gruposnap_home_hero_is_inside_element($element, GRUPOSNAP_HOME_HERO_MOBILE_SECTION_ID)) {
        return;
    }

    $element->set_settings('hide_mobile', '');

    foreach (
        array(
            'elementor-hidden-mobile',
            'elementor-hidden-phone',
            'elementor-hidden-mobile_extra',
        ) as $class
    ) {
        $element->remove_render_attribute('_wrapper', 'class', $class);
    }
}

/**
 * @param array<int, array<string, mixed>> $nodes
 * @return array<string, mixed>|null
 */
function gruposnap_home_hero_find_node(array $nodes, string $id): ?array
{
    foreach ($nodes as $node) {
        if (!is_array($node)) {
            continue;
        }

        if (($node['id'] ?? '') === $id) {
            return $node;
        }

        if (!empty($node['elements']) && is_array($node['elements'])) {
            $found = gruposnap_home_hero_find_node($node['elements'], $id);
            if ($found) {
                return $found;
            }
        }
    }

    return null;
}

/**
 * @param array<string, mixed> $settings
 */
function gruposnap_home_hero_clear_responsive_hide_settings(array &$settings, bool &$needs_save): void
{
    foreach (array_keys($settings) as $key) {
        if (strpos($key, 'hide_') === 0 && !empty($settings[$key])) {
            unset($settings[$key]);
            $needs_save = true;
        }
    }
}

/**
 * Corrige visibilidad del hero: un solo bloque (a5d8162); oculta duplicado 47df02b.
 */
function gruposnap_home_hero_fix_elementor_data(): void
{
    if (get_option('gruposnap_home_hero_fix_version') === GRUPOSNAP_HOME_HERO_FIX_VERSION) {
        return;
    }

    $raw = get_post_meta(751, '_elementor_data', true);
    if (!is_string($raw) || $raw === '') {
        return;
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return;
    }

    $needs_save = false;

    $walk = static function (array &$nodes, bool $in_hero = false, bool $in_desktop = false, bool $in_mobile = false) use (&$walk, &$needs_save): void {
        foreach ($nodes as &$node) {
            if (!is_array($node)) {
                continue;
            }

            $id         = $node['id'] ?? '';
            $in_hero    = $in_hero || $id === GRUPOSNAP_HOME_HERO_SECTION_ID;
            $in_desktop = $in_desktop || $id === GRUPOSNAP_HOME_HERO_INNER_SECTION_ID;
            $in_mobile  = $in_mobile || $id === GRUPOSNAP_HOME_HERO_MOBILE_SECTION_ID;
            $settings   = &$node['settings'];

            if ($in_hero && $id === GRUPOSNAP_HOME_HERO_SECTION_ID) {
                if (($settings['padding_mobile']['top'] ?? '') !== '100') {
                    $settings['padding_mobile'] = array(
                        'unit'     => 'px',
                        'top'      => '100',
                        'right'    => '0',
                        'bottom'   => '24',
                        'left'     => '0',
                        'isLinked' => false,
                    );
                    $needs_save = true;
                }

                if (($settings['margin_mobile']['top'] ?? '') !== '0') {
                    $settings['margin_mobile'] = array(
                        'unit'     => 'px',
                        'top'      => '0',
                        'right'    => '0',
                        'bottom'   => '0',
                        'left'     => '0',
                        'isLinked' => false,
                    );
                    $needs_save = true;
                }
            } elseif ($in_hero && $id === GRUPOSNAP_HOME_HERO_INNER_SECTION_ID) {
                gruposnap_home_hero_apply_desktop_block_settings($settings, $needs_save);
            } elseif ($in_hero && $id === GRUPOSNAP_HOME_HERO_MOBILE_SECTION_ID) {
                gruposnap_home_hero_apply_mobile_block_settings($settings, $needs_save);
            } elseif ($in_hero && $id === '85b941c') {
                if (($settings['margin_mobile']['top'] ?? '') !== '0') {
                    $settings['margin_mobile'] = array(
                        'unit'     => 'px',
                        'top'      => '0',
                        'right'    => '0',
                        'bottom'   => '0',
                        'left'     => '0',
                        'isLinked' => false,
                    );
                    $needs_save = true;
                }
            } elseif ($in_hero && $in_desktop && !$in_mobile) {
                foreach (array_keys($settings) as $key) {
                    if (strpos($key, 'hide_') === 0 && !empty($settings[$key])) {
                        unset($settings[$key]);
                        $needs_save = true;
                    }
                }

                if (($node['elType'] ?? '') === 'widget' && !empty($settings['_animation'])) {
                    $settings['_animation']       = '';
                    $settings['_animation_delay'] = 0;
                    $needs_save                   = true;
                }
            }

            if (!empty($node['elements']) && is_array($node['elements'])) {
                $walk($node['elements'], $in_hero, $in_desktop, $in_mobile);
            }
        }
    };

    $walk($data);

    if (!$needs_save) {
        return;
    }

    update_post_meta(751, '_elementor_data', wp_slash(wp_json_encode($data)));
    delete_post_meta(751, '_elementor_element_cache');
    delete_post_meta(751, '_elementor_css');
    update_option('gruposnap_home_hero_fix_version', GRUPOSNAP_HOME_HERO_FIX_VERSION, false);

    if (class_exists('\Elementor\Plugin')) {
        $plugin = \Elementor\Plugin::$instance;
        if (isset($plugin->files_manager) && method_exists($plugin->files_manager, 'clear_cache')) {
            $plugin->files_manager->clear_cache();
        }
    }

    delete_option('gruposnap_home_hero_cache_version');
}

/**
 * @param \Elementor\Widget_Base $widget
 */
function gruposnap_home_hero_remove_invisible_render_attr($widget): void
{
    if (!gruposnap_is_home_hero_context() || !gruposnap_widget_in_home_hero($widget)) {
        return;
    }

    $widget->remove_render_attribute('_wrapper', 'class', 'elementor-invisible');
}

function gruposnap_home_hero_bust_elementor_cache(): void
{
    $version = get_option('gruposnap_home_hero_cache_version');
    $fix     = get_option('gruposnap_home_hero_fix_version');

    if ($version === GRUPOSNAP_THEME_VERSION && $fix === GRUPOSNAP_HOME_HERO_FIX_VERSION) {
        return;
    }

    delete_post_meta(751, '_elementor_element_cache');
    delete_post_meta(751, '_elementor_css');
    update_option('gruposnap_home_hero_cache_version', GRUPOSNAP_THEME_VERSION, false);
    update_option('gruposnap_home_hero_fix_version', GRUPOSNAP_HOME_HERO_FIX_VERSION, false);
}

/**
 * Sin caché de Elementor en la home: el HTML cacheado conservaba elementor-hidden-mobile en a5d8162.
 */
function gruposnap_home_hero_purge_element_cache_on_frontend(): void
{
    if (is_admin() || !gruposnap_is_home_hero_context()) {
        return;
    }

    delete_post_meta(751, '_elementor_element_cache');
}

function gruposnap_enqueue_home_hero_assets(): void
{
    if (!gruposnap_should_enqueue_home_hero_assets()) {
        return;
    }

    $deps = array('gruposnap-child');
    foreach (array('elementor-post-751', 'elementor-frontend') as $handle) {
        if (wp_style_is($handle, 'registered') || wp_style_is($handle, 'enqueued')) {
            $deps[] = $handle;
        }
    }

    wp_enqueue_style(
        'gruposnap-home-hero',
        get_stylesheet_directory_uri() . '/assets/css/home-hero.css',
        $deps,
        GRUPOSNAP_THEME_VERSION
    );
}

function gruposnap_home_hero_reveal_style(): void
{
    if (!gruposnap_should_enqueue_home_hero_assets()) {
        return;
    }

    $section = GRUPOSNAP_HOME_HERO_SECTION_ID;
    ?>
    <style id="gruposnap-home-hero-critical">
    @media (max-width: 1024px) {
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> {
            margin-top: 0 !important;
            overflow: visible !important;
        }
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> > .elementor-container {
            z-index: 5 !important;
            position: relative !important;
            min-height: 480px !important;
        }
    }
    @media (max-width: 767px) {
        .elementor .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-47df02b {
            display: none !important;
        }
        .elementor .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162,
        .elementor .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162.elementor-hidden-mobile,
        .elementor .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162.elementor-hidden-phone,
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162 {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            height: auto !important;
            min-height: 1px !important;
            width: 100% !important;
            position: relative !important;
            z-index: 12 !important;
        }
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> > .elementor-container,
        .elementor-element-<?php echo esc_attr($section); ?> > .elementor-container {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            margin-inline: unset !important;
            min-height: min(72vh, 680px) !important;
        }
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162 .elementor-widget,
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162 .elementor-column,
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-8082631,
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-87e8edf,
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-widget-wdt-heading,
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-widget-wdt-button {
            visibility: visible !important;
            opacity: 1 !important;
            display: block !important;
            position: relative !important;
            z-index: 13 !important;
        }
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162 > .elementor-container {
            display: flex !important;
            flex-direction: column !important;
            z-index: 12 !important;
        }
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-82f3f66,
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-04501ce {
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
            z-index: 13 !important;
        }
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-85b941c > .elementor-element-populated,
        .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-85b941c .elementor-widget-wrap {
            position: relative !important;
            z-index: 20 !important;
        }
    }
    @media (min-width: 768px) and (max-width: 1024px) {
        .elementor.elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162 {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
    }
    .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-invisible,
    .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .animated,
    .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-8082631 .wdt-heading-holder,
    .elementor-751 .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-f428155 .wdt-heading-holder {
        visibility: visible !important;
        opacity: 1 !important;
        animation: none !important;
    }
    </style>
    <?php
}

function gruposnap_home_hero_reveal_script(): void
{
    if (!gruposnap_should_enqueue_home_hero_assets()) {
        return;
    }

    $section = GRUPOSNAP_HOME_HERO_SECTION_ID;
    $video   = esc_js(gruposnap_home_hero_media_urls()['video']);
    ?>
    <script id="gruposnap-home-hero-reveal-js">
    (function () {
        var sel = '.elementor-element-<?php echo esc_js($section); ?>';
        var videoSrc = '<?php echo $video; ?>';

        function playHeroVideo(hero) {
            var container = hero.querySelector('.elementor-background-video-container');
            var video = hero.querySelector('.elementor-background-video-hosted');

            if (!video) {
                return;
            }

            if (!video.getAttribute('src')) {
                video.setAttribute('src', videoSrc);
            }

            video.muted = true;
            video.playsInline = true;
            video.loop = true;

            var playPromise = video.play();
            if (playPromise && playPromise.catch) {
                playPromise.catch(function () {});
            }

            if (container) {
                container.classList.remove('elementor-loading', 'elementor-invisible');
            }
        }

        function revealHeroSection(section) {
            if (!section) {
                return;
            }

            section.classList.remove('elementor-hidden-desktop', 'elementor-hidden-tablet');
            section.style.setProperty('display', 'block', 'important');
            section.style.setProperty('visibility', 'visible', 'important');
            section.style.setProperty('opacity', '1', 'important');
        }

        function unstackHeroSection(section) {
            if (!section) {
                return;
            }

            var row = section.querySelector(':scope > .elementor-container');
            if (row) {
                row.style.removeProperty('display');
                row.style.removeProperty('flex-flow');
                row.style.removeProperty('flex-direction');
                row.style.removeProperty('align-items');
                row.style.removeProperty('gap');
            }

            section.querySelectorAll(':scope > .elementor-container > .elementor-column').forEach(function (col) {
                col.style.removeProperty('flex');
                col.style.removeProperty('max-width');
                col.style.removeProperty('width');
                col.style.removeProperty('min-width');
                col.style.removeProperty('order');
                col.style.removeProperty('display');
                col.style.removeProperty('z-index');
            });
        }

        function stackMobileHeroSection(section) {
            if (!section) {
                return;
            }

            revealHeroSection(section);
            unstackHeroSection(section);

            var row = section.querySelector(':scope > .elementor-container');
            if (row) {
                row.style.setProperty('display', 'flex', 'important');
                row.style.setProperty('flex-flow', 'column nowrap', 'important');
                row.style.setProperty('flex-direction', 'column', 'important');
                row.style.setProperty('align-items', 'stretch', 'important');
                row.style.setProperty('gap', '1rem', 'important');
                row.style.setProperty('z-index', '10', 'important');
            }

            section.querySelectorAll(':scope > .elementor-container > .elementor-column').forEach(function (col) {
                if (window.innerWidth <= 767 && col.classList.contains('elementor-element-c4dc9ca')) {
                    col.style.setProperty('display', 'none', 'important');
                    return;
                }
                col.style.setProperty('width', '100%', 'important');
                col.style.setProperty('max-width', '100%', 'important');
                col.style.setProperty('flex', '0 0 auto', 'important');
                col.style.setProperty('min-width', '0', 'important');
                col.style.setProperty('display', 'block', 'important');
                col.style.setProperty('z-index', '11', 'important');
            });
        }

        function forceMobileHeroLayout(hero) {
            if (!hero) {
                return;
            }

            var main = hero.querySelector(':scope > .elementor-container');
            if (main) {
                main.style.setProperty('position', 'relative', 'important');
                main.style.setProperty('z-index', '5', 'important');
                if (window.innerWidth <= 767) {
                    main.style.setProperty('width', '100%', 'important');
                    main.style.setProperty('max-width', '100%', 'important');
                    main.style.setProperty('margin-left', '0', 'important');
                    main.style.setProperty('margin-right', '0', 'important');
                } else {
                    main.style.removeProperty('width');
                    main.style.removeProperty('max-width');
                    main.style.removeProperty('margin-left');
                    main.style.removeProperty('margin-right');
                }
            }

            hero.querySelectorAll('.elementor-background-video-container, .elementor-background-overlay').forEach(function (el) {
                el.style.setProperty('z-index', '0', 'important');
            });

            var desktop = hero.querySelector('.elementor-element-a5d8162');
            var mobile = hero.querySelector('.elementor-element-47df02b');

            if (mobile) {
                mobile.style.setProperty('display', 'none', 'important');
            }

            if (!desktop) {
                return;
            }

            desktop.classList.remove('elementor-hidden-mobile', 'elementor-hidden-phone', 'elementor-hidden-tablet', 'elementor-hidden-desktop');

            if (window.innerWidth <= 767) {
                stackMobileHeroSection(desktop);
            } else {
                revealHeroSection(desktop);
                unstackHeroSection(desktop);
            }
        }

        function revealHero() {
            var hero = document.querySelector(sel);
            if (!hero) {
                return;
            }

            playHeroVideo(hero);

            hero.querySelectorAll('.elementor-invisible, .animated').forEach(function (el) {
                el.classList.remove('elementor-invisible', 'animated');
            });

            forceMobileHeroLayout(hero);

            if (window.innerWidth <= 767) {
                hero.querySelectorAll(
                    '.elementor-element-c4dc9ca, .elementor-element-3eb4a8b, .elementor-element-8afe48e, .wdt-custom-slider-scroll, .wdt-custom-slider-social-icons'
                ).forEach(function (el) {
                    el.style.setProperty('display', 'none', 'important');
                    el.style.setProperty('visibility', 'hidden', 'important');
                    el.style.setProperty('height', '0', 'important');
                });
            }

            hero.querySelectorAll(
                '.elementor-element-85b941c, .elementor-element-82f3f66, .elementor-element-8082631, .gruposnap-cta--whatsapp, .gruposnap-cta--secondary, .wdt-heading-holder, .wdt-button-holder, .elementor-widget-wdt-heading, .elementor-widget-wdt-button'
            ).forEach(function (el) {
                el.classList.remove('elementor-invisible', 'elementor-hidden-mobile', 'elementor-hidden-phone', 'animated');
                el.style.setProperty('visibility', 'visible', 'important');
                el.style.setProperty('opacity', '1', 'important');
                if (!el.classList.contains('elementor-widget-wdt-button')
                    && !el.classList.contains('wdt-button-holder')
                    && !el.classList.contains('gruposnap-cta--whatsapp')
                    && !el.classList.contains('gruposnap-cta--secondary')) {
                    el.style.setProperty('display', 'block', 'important');
                }
            });

            var heroColumn = hero.querySelector('.elementor-element-85b941c');
            if (heroColumn) {
                heroColumn.style.setProperty('position', 'relative', 'important');
                heroColumn.style.setProperty('z-index', '20', 'important');
                if (window.innerWidth <= 767) {
                    heroColumn.style.setProperty('flex', '1 1 100%', 'important');
                    heroColumn.style.removeProperty('width');
                    heroColumn.style.removeProperty('max-width');
                } else {
                    heroColumn.style.removeProperty('flex');
                    heroColumn.style.removeProperty('width');
                    heroColumn.style.removeProperty('max-width');
                }
            }

            var desktopBlock = hero.querySelector('.elementor-element-a5d8162 > .elementor-container');
            if (desktopBlock && window.innerWidth <= 767) {
                desktopBlock.style.setProperty('display', 'flex', 'important');
                desktopBlock.style.setProperty('flex-flow', 'column nowrap', 'important');
                desktopBlock.style.setProperty('flex-direction', 'column', 'important');
                desktopBlock.querySelectorAll(':scope > .elementor-column').forEach(function (col) {
                    if (col.classList.contains('elementor-element-c4dc9ca')) {
                        col.style.setProperty('display', 'none', 'important');
                        return;
                    }
                    col.style.setProperty('width', '100%', 'important');
                    col.style.setProperty('max-width', '100%', 'important');
                    col.style.setProperty('flex', '0 0 auto', 'important');
                    col.style.setProperty('display', 'block', 'important');
                });
            }
        }

        revealHero();
        document.addEventListener('DOMContentLoaded', revealHero);
        window.addEventListener('load', revealHero);
        [100, 400, 1000, 2500].forEach(function (ms) {
            window.setTimeout(revealHero, ms);
        });

        if (window.jQuery) {
            window.jQuery(window).on('elementor/frontend/init', revealHero);
        }

        window.addEventListener('resize', function () {
            var hero = document.querySelector(sel);
            if (hero) {
                forceMobileHeroLayout(hero);
            }
        });
    })();
    </script>
    <?php
}

/**
 * Oculta el bloque de icon-list del hero en la home.
 *
 * @param string               $content
 * @param \Elementor\Widget_Base $widget
 */
/**
 * CSS de rescate (última prioridad): evita hero vacío por min-height + flex centrado.
 */
function gruposnap_home_hero_rescue_footer_css(): void
{
    if (!gruposnap_should_enqueue_home_hero_assets()) {
        return;
    }

    $section = GRUPOSNAP_HOME_HERO_SECTION_ID;
    ?>
    <style id="gruposnap-home-hero-rescue">
    @media (max-width: 767px) {
        .elementor-element-<?php echo esc_attr($section); ?> {
            overflow: visible !important;
            min-height: auto !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?>.elementor-section-height-min-height > .elementor-container,
        .elementor-element-<?php echo esc_attr($section); ?>.elementor-section-items-middle > .elementor-container,
        .elementor-element-<?php echo esc_attr($section); ?>.elementor-section-items-top > .elementor-container,
        .elementor-element-<?php echo esc_attr($section); ?> > .elementor-container {
            align-items: flex-start !important;
            justify-content: flex-start !important;
            min-height: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            margin-inline: unset !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-85b941c > .elementor-element-populated {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-47df02b {
            display: none !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162,
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162.elementor-hidden-mobile,
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162.elementor-hidden-phone {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            height: auto !important;
            overflow: visible !important;
            position: relative !important;
            z-index: 20 !important;
            min-height: 280px !important;
            width: 100% !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-35290fa {
            display: none !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> {
            overflow: visible !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-85b941c,
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-85b941c > .elementor-element-populated,
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-85b941c > .elementor-element-populated > .elementor-widget-wrap,
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162 {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            width: 100% !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162.wdt-section-wrap-col > .elementor-container {
            display: flex !important;
            flex-flow: column nowrap !important;
            flex-direction: column !important;
            flex-wrap: nowrap !important;
            align-items: stretch !important;
            min-height: 260px !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162 .elementor-column,
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-82f3f66,
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-04501ce {
            width: 100% !important;
            max-width: 100% !important;
            flex: 0 0 auto !important;
            min-height: 40px !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-82f3f66 {
            order: 1 !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-04501ce {
            order: 2 !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-widget,
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-widget-container,
        .elementor-element-<?php echo esc_attr($section); ?> .wdt-heading-holder,
        .elementor-element-<?php echo esc_attr($section); ?> .wdt-button-holder {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            transform: none !important;
            animation: none !important;
            min-height: 1px !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-invisible,
        .elementor-element-<?php echo esc_attr($section); ?> .animated {
            visibility: visible !important;
            opacity: 1 !important;
            animation: none !important;
            transform: none !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-c4dc9ca,
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-3eb4a8b,
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-8afe48e {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .wdt-heading-subtitle,
        .elementor-element-<?php echo esc_attr($section); ?> .wdt-heading-content-wrapper,
        .elementor-element-<?php echo esc_attr($section); ?> .wdt-heading-content-wrapper p {
            color: #111111 !important;
            -webkit-text-fill-color: #111111 !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-8082631 .wdt-heading-title {
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-8082631 .wdt-heading-title .gruposnap-heading-accent-navy {
            color: #112b75 !important;
            -webkit-text-fill-color: #112b75 !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-8082631 .wdt-heading-title .gruposnap-heading-accent {
            color: #f57c20 !important;
            -webkit-text-fill-color: #f57c20 !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-8082631 .wdt-heading-subtitle {
            color: #112b75 !important;
            -webkit-text-fill-color: #112b75 !important;
            text-align: center !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-8082631 .wdt-heading-subtitle .gruposnap-heading-accent {
            color: #f57c20 !important;
            -webkit-text-fill-color: #f57c20 !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-8082631 .wdt-heading-subtitle .gruposnap-heading-accent-navy {
            color: #112b75 !important;
            -webkit-text-fill-color: #112b75 !important;
        }
        .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-8082631 .wdt-heading-subtitle-wrapper {
            width: 100% !important;
            max-width: 100% !important;
            display: flex !important;
            justify-content: center !important;
            text-align: center !important;
        }
    }
    </style>
    <?php
}

function gruposnap_home_hero_hide_highlights_widget(string $content, $widget): string
{
    if (!gruposnap_is_home_hero_context() || $widget->get_id() !== GRUPOSNAP_HOME_HERO_HIGHLIGHTS_WIDGET_ID) {
        return $content;
    }

    return '';
}

/**
 * Parchea el JSON de Elementor al cargar (editor + frontend). Evita hide_mobile en caché vieja.
 *
 * @param array                         $data
 * @param \Elementor\Core\Base\Document $document
 */
function gruposnap_home_hero_patch_document_data(array $data, $post_id): array
{
    if ((int) $post_id !== 751 || !is_array($data)) {
        return $data;
    }

    gruposnap_home_hero_prune_duplicate_mobile_block($data);

    $walk = static function (array &$nodes, bool $in_hero = false, bool $in_desktop = false, bool $in_mobile = false) use (&$walk): void {
        foreach ($nodes as &$node) {
            if (!is_array($node)) {
                continue;
            }

            $id         = $node['id'] ?? '';
            $in_hero    = $in_hero || $id === GRUPOSNAP_HOME_HERO_SECTION_ID;
            $in_desktop = $in_desktop || $id === GRUPOSNAP_HOME_HERO_INNER_SECTION_ID;
            $in_mobile  = $in_mobile || $id === GRUPOSNAP_HOME_HERO_MOBILE_SECTION_ID;
            $settings   = &$node['settings'];
            $needs_save = false;

            if ($in_hero && $id === GRUPOSNAP_HOME_HERO_INNER_SECTION_ID) {
                gruposnap_home_hero_apply_desktop_block_settings($settings, $needs_save);
            } elseif ($in_hero && $id === GRUPOSNAP_HOME_HERO_MOBILE_SECTION_ID) {
                gruposnap_home_hero_apply_mobile_block_settings($settings, $needs_save);
            } elseif ($in_hero && $in_desktop && !$in_mobile) {
                foreach (array_keys($settings) as $key) {
                    if (strpos($key, 'hide_') === 0) {
                        unset($settings[$key]);
                    }
                }

                if (($node['elType'] ?? '') === 'widget') {
                    $settings['_animation']       = '';
                    $settings['_animation_delay'] = 0;
                }
            }

            if (!empty($node['elements']) && is_array($node['elements'])) {
                $walk($node['elements'], $in_hero, $in_desktop, $in_mobile);
            }
        }
    };

    $walk($data);

    return $data;
}

/**
 * La caché de Elementor omite hooks before_render; forzar render fresco en la home.
 *
 * @param \Elementor\Core\Base\Document $document
 */
function gruposnap_home_hero_clear_document_cache($document): void
{
    if (!is_object($document) || !method_exists($document, 'get_main_id')) {
        return;
    }

    if ((int) $document->get_main_id() !== 751) {
        return;
    }

    delete_post_meta(751, '_elementor_element_cache');
}

/**
 * CSS inline en el editor Elementor (canvas móvil no usa siempre @media).
 */
function gruposnap_home_hero_editor_rescue_css(): void
{
    if (!gruposnap_should_enqueue_home_hero_assets()) {
        return;
    }

    $section = GRUPOSNAP_HOME_HERO_SECTION_ID;
    ?>
    <style id="gruposnap-home-hero-editor-rescue">
    body.elementor-editor-active.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-47df02b {
        display: none !important;
    }
    body.elementor-editor-active.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?>,
    body.elementor-editor-active.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162,
    body.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162,
    #elementor-preview-responsive-wrapper .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162 {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        overflow: visible !important;
    }
    body.elementor-editor-active.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162 > .elementor-container,
    body.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162 > .elementor-container {
        display: flex !important;
        flex-flow: column nowrap !important;
        align-items: stretch !important;
    }
    body.elementor-editor-active.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162 .elementor-column,
    body.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?> .elementor-element-a5d8162 .elementor-column {
        width: 100% !important;
        max-width: 100% !important;
        flex: 0 0 auto !important;
    }
    body.elementor-editor-active.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?> .elementor-invisible,
    body.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?> .elementor-invisible,
    body.elementor-editor-active.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?> .wdt-heading-holder,
    body.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?> .wdt-heading-holder {
        visibility: visible !important;
        opacity: 1 !important;
        animation: none !important;
        transform: none !important;
    }
    body.elementor-editor-active.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?> .wdt-heading-title,
    body.elementor-editor-active.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?> .wdt-heading-subtitle,
    body.elementor-editor-active.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?> .wdt-heading-content-wrapper,
    body.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?> .wdt-heading-title,
    body.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?> .wdt-heading-subtitle,
    body.elementor-device-mobile .elementor-element-<?php echo esc_attr($section); ?> .wdt-heading-content-wrapper {
        color: #111111 !important;
        -webkit-text-fill-color: #111111 !important;
    }
    </style>
    <?php
}

add_action('init', 'gruposnap_home_hero_fix_elementor_data', 0);
add_action('admin_init', 'gruposnap_home_hero_fix_elementor_data', 0);
add_action('init', 'gruposnap_home_hero_bust_elementor_cache', 1);
add_action('template_redirect', 'gruposnap_home_hero_purge_element_cache_on_frontend', 0);
add_action('elementor/preview/enqueue_styles', 'gruposnap_enqueue_home_hero_assets', 9999);
add_action('elementor/editor/after_enqueue_styles', 'gruposnap_enqueue_home_hero_assets', 9999);
add_action('elementor/editor/after_enqueue_styles', 'gruposnap_home_hero_editor_rescue_css', 10000);
add_action('elementor/frontend/section/before_render', 'gruposnap_home_hero_prepare_section', 9999);
add_action('elementor/frontend/column/before_render', 'gruposnap_home_hero_clear_mobile_hide', 1);
add_action('elementor/frontend/widget/before_render', 'gruposnap_home_hero_clear_mobile_hide', 1);
add_action('elementor/frontend/widget/before_render', 'gruposnap_home_hero_disable_widget_entrance_animation', 1);
add_action('elementor/frontend/widget/before_render', 'gruposnap_home_hero_remove_invisible_render_attr', 99);
add_filter('elementor/widget/render_content', 'gruposnap_home_hero_hide_highlights_widget', 8, 2);
add_filter('elementor/widget/render_content', 'gruposnap_home_hero_strip_invisible_class', 5, 2);
add_filter('elementor/frontend/the_content', 'gruposnap_home_hero_fix_section_html', 8);
add_filter('elementor/frontend/builder_content_data', 'gruposnap_home_hero_patch_document_data', 8, 2);
add_action('wp_enqueue_scripts', 'gruposnap_enqueue_home_hero_assets', 9999);
add_action('wp_head', 'gruposnap_home_hero_reveal_style', 3);
add_action('wp_head', 'gruposnap_home_hero_reveal_script', 4);
add_action('wp_footer', 'gruposnap_home_hero_rescue_footer_css', 9999);
