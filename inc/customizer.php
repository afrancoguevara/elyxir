<?php
/**
 * Theme Customizer (moderno, 2025)
 * - Namespaced
 * - postMessage + selective refresh
 * - Ajustes útiles (accent color, container width) con sanitización
 * - Variables CSS inyectadas en <head> para integrarse con modern.css
 *
 * @package Elyxir
 */

namespace Elyxir\Customize;

use WP_Customize_Manager;

defined('ABSPATH') || exit;

/**
 * Registro de ajustes/controles del Customizer.
 */
function register(WP_Customize_Manager $wp_customize) {
    // Core: título, descripción, color de header -> en vivo.
    foreach (['blogname', 'blogdescription', 'header_textcolor'] as $setting_id) {
        $setting = $wp_customize->get_setting($setting_id);
        if ($setting) $setting->transport = 'postMessage';
    }

    // Selective refresh para título y descripción.
    if (isset($wp_customize->selective_refresh)) {
        $wp_customize->selective_refresh->add_partial('blogname', [
            'selector'        => '.site-title a',
            'render_callback' => __NAMESPACE__ . '\\partial_blogname',
        ]);
        $wp_customize->selective_refresh->add_partial('blogdescription', [
            'selector'        => '.site-description',
            'render_callback' => __NAMESPACE__ . '\\partial_blogdescription',
        ]);
    }

    // === Elyxir: Apariencia ===
    $wp_customize->add_section('elyxir_appearance', [
        'title'    => __('Elyxir • Appearance', 'elyxir'),
        'priority' => 35,
    ]);

    // Color de acento -> mapea a --c-link en :root
    $wp_customize->add_setting('elyxir_accent_color', [
        'default'           => '#1a73e8',
        'transport'         => 'postMessage',
        'sanitize_callback' => 'sanitize_hex_color',
    ]);
    $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'elyxir_accent_color', [
        'label'   => __('Accent color', 'elyxir'),
        'section' => 'elyxir_appearance',
    ]));

    // Ancho de container (px)
    $wp_customize->add_setting('elyxir_container_width', [
        'default'           => 1200,
        'transport'         => 'postMessage',
        'sanitize_callback' => __NAMESPACE__ . '\\sanitize_int_range',
    ]);
    $wp_customize->add_control('elyxir_container_width', [
        'type'        => 'number',
        'label'       => __('Container width (px)', 'elyxir'),
        'description' => __('Affects --container CSS variable', 'elyxir'),
        'section'     => 'elyxir_appearance',
        'input_attrs' => [ 'min' => 960, 'max' => 1440, 'step' => 10 ],
    ]);
}
add_action('customize_register', __NAMESPACE__ . '\\register');

/**
 * Partials
 */
function partial_blogname() { bloginfo('name'); }
function partial_blogdescription() { bloginfo('description'); }

/**
 * Sanitizador para enteros en rango.
 */
function sanitize_int_range($value) {
    $v = absint($value);
    if ($v < 480)  $v = 480;
    if ($v > 1920) $v = 1920;
    return $v;
}

/**
 * Inyecta variables CSS en <head> a partir de los ajustes.
 */
function head_css_vars() {
    $accent   = get_theme_mod('elyxir_accent_color', '#1a73e8');
    $container = (int) get_theme_mod('elyxir_container_width', 1200);

    // Seguridad básica
    $accent = sanitize_hex_color($accent) ?: '#1a73e8';
    $container = max(480, min(1920, $container));
    ?>
<style id="elyxir-customizer-vars">:root{--c-link: <?php echo esc_html($accent); ?>; --container: <?php echo esc_html($container); ?>px;}</style>
<?php
}
add_action('wp_head', __NAMESPACE__ . '\\head_css_vars', 20);

/**
 * JS para vista previa en vivo (customize-preview).
 * Crea assets/js/customizer-preview.js y escucha cambios para aplicar al DOM.
 */
function preview_js() {
    // Intentamos usar helper de versión del tema si existe.
    $path_rel = '/assets/js/customizer-preview.js';
    $file = get_template_directory() . $path_rel;
    $ver = file_exists($file) ? filemtime($file) : (defined('Elyxir\\VER') ? \Elyxir\VER : wp_get_theme()->get('Version'));

    wp_enqueue_script(
        'elyxir-customizer-preview',
        get_template_directory_uri() . $path_rel,
        [ 'customize-preview' ],
        $ver,
        true
    );
}
add_action('customize_preview_init', __NAMESPACE__ . '\\preview_js');
