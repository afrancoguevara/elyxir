<?php
/**
 * Custom Header – versión moderna (2025)
 * - Namespaced
 * - CSS variables en :root para color/imagen del header
 * - Callbacks seguros con escape
 * - Helper para atributos inline del header
 *
 * Uso sugerido en header.php (tema clásico):
 * <header id="masthead" <?php echo \Elyxir\Header\header_attrs(['class' => 'site-header']); ?>>
 *   ...branding/nav...
 * </header>
 *
 * @package Elyxir
 */

namespace Elyxir\Header;

defined('ABSPATH') || exit;

/**
 * Registrar soporte de custom-header.
 */
function setup() {
    add_theme_support('custom-header', apply_filters('elyxir/custom_header_args', [
        'default-image'      => '',
        'default-text-color' => '111111', // Mejor contraste que 000
        'width'              => 1600,
        'height'             => 400,
        'flex-height'        => true,
        'flex-width'         => true,
        'video'              => false,
        'wp-head-callback'   => __NAMESPACE__ . '\\head_styles',
    ]));
}
add_action('after_setup_theme', __NAMESPACE__ . '\\setup');

/**
 * Imprime estilos en <head> basados en la config del header.
 * Define variables CSS para integrarse con tu modern.css.
 */
function head_styles() {
    $text_color = get_header_textcolor();
    $image      = get_header_image();

    // Valores procesados
    $var_text  = display_header_text() ? ('#' . preg_replace('/[^0-9a-fA-F]/', '', (string) $text_color)) : 'transparent';
    $var_image = $image ? 'url("' . esc_url($image) . '")' : 'none';
    ?>
<style id="elyxir-custom-header" type="text/css">
:root{
  --header-text-color: <?php echo esc_html($var_text); ?>;
  --header-image: <?php echo esc_html($var_image); ?>;
}
/* Aplicación por defecto (puedes sobreescribir en modern.css) */
.site-header{ background-image: var(--header-image); background-size: cover; background-position: center; }
.site-title a, .site-description{ color: var(--header-text-color); }
<?php if ( ! display_header_text() ) : ?>
.site-title, .site-description{
  position:absolute!important; clip: rect(1px,1px,1px,1px); clip-path: inset(50%);
  height:1px; width:1px; overflow:hidden; white-space:nowrap; border:0;
}
<?php endif; ?>
</style>
<?php
}

/**
 * Helper para generar atributos style/class en el wrapper del header.
 *
 * @param array $attrs ['class' => '...', 'style' => '...']
 * @return string
 */
function header_attrs(array $attrs = []): string {
    $style = [];
    $img = get_header_image();
    if ($img) {
        $style[] = 'background-image:url(' . esc_url($img) . ')';
        $style[] = 'background-size:cover';
        $style[] = 'background-position:center';
    }
    if (!empty($attrs['style'])) {
        $style[] = trim((string) $attrs['style'], '; ');
    }

    $out = '';
    $class = !empty($attrs['class']) ? ' ' . sanitize_html_class($attrs['class']) : '';
    $out .= ' class="site-header' . $class . '"';
    if (!empty($style)) {
        $out .= ' style="' . esc_attr(implode(';', $style)) . '"';
    }
    return $out;
}

/**
 * Wrapper que devuelve el tag <img> del custom header.
 * Mantiene the_header_image_tag() pero devolviendo string para facilitar plantillas.
 */
function header_image_tag(array $attr = []): string {
    if (!get_header_image()) return '';
    ob_start();
    the_header_image_tag($attr);
    return (string) ob_get_clean();
}
