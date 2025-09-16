<?php
namespace Elyxir;

defined('ABSPATH') || exit;

function setup() {
  load_theme_textdomain('elyxir', PATH . '/languages');

  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('custom-logo', [
    'height' => 250, 'width' => 250, 'flex-width' => true, 'flex-height' => true,
  ]);

  // Soportes modernos de bloques.
  add_theme_support('wp-block-styles');
  add_theme_support('responsive-embeds');
  add_theme_support('editor-styles');               // Para editor.css
  add_theme_support('align-wide');

  register_nav_menus([
    'primary' => __('Primary Menu', 'elyxir'),
  ]);
}
add_action('after_setup_theme', __NAMESPACE__ . '\\setup', 9);

// Content width (legacy, por compatibilidad con plugins).
function content_width() {
  $GLOBALS['content_width'] = apply_filters('elyxir_content_width', 800);
}
add_action('after_setup_theme', __NAMESPACE__ . '\\content_width', 0);
