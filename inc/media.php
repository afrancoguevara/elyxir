<?php
namespace Elyxir;

defined('ABSPATH') || exit;

// Tamaños de imagen personalizados.
function image_sizes() {
  add_image_size('cover-xl', 1920, 1080, true);
  add_image_size('card-lg', 1200, 800, true);
  add_image_size('thumb-sm', 480, 320, true);
}
add_action('after_setup_theme', __NAMESPACE__ . '\\image_sizes');

// Permitir SVG con una validación simple.
// Nota: Para sanitización completa sueles usar plugin (Safe SVG). Aquí solo mime + ext.
function allow_svg_upload($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', __NAMESPACE__ . '\\allow_svg_upload');

// Forzar correct type para SVG.
function fix_svg_filetype($data, $file, $filename, $mimes, $real_mime = '') {
  $ext = pathinfo($filename, PATHINFO_EXTENSION);
  if (strtolower($ext) === 'svg') {
    $data['ext']  = 'svg';
    $data['type'] = 'image/svg+xml';
  }
  return $data;
}
add_filter('wp_check_filetype_and_ext', __NAMESPACE__ . '\\fix_svg_filetype', 10, 5);
