<?php
namespace Elyxir;

defined('ABSPATH') || exit;

function versioned($path_rel) {
  $file = PATH . $path_rel;
  return file_exists($file) ? filemtime($file) : VER;
}

function enqueue() {
  // CSS principal (tu modern.css del canvas).
  wp_enqueue_style(
    'elyxir-modern',
    URI . '/assets/css/modern.css',
    [],
    versioned('/assets/css/modern.css')
  );

  // JS principal (opcional).
  wp_enqueue_script(
    'elyxir-app',
    URI . '/assets/js/app.js',
    [],
    versioned('/assets/js/app.js'),
    true
  );
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue', 20);

// Editor: estilos dentro del editor de bloques.
function editor_assets() {
  add_editor_style('assets/css/modern.css'); // heredará tokens básicos
}
add_action('after_setup_theme', __NAMESPACE__ . '\\editor_assets', 20);

// Defer/async para JS del tema (no tocar jQuery/admin).
function script_attrs($tag, $handle, $src) {
  if (is_admin()) return $tag;
  $defer = ['elyxir-app'];
  if (in_array($handle, $defer, true)) {
    return sprintf('<script src="%s" defer></script>' . PHP_EOL, esc_url($src));
  }
  return $tag;
}
add_filter('script_loader_tag', __NAMESPACE__ . '\\script_attrs', 10, 3);
