<?php
namespace Elyxir;

defined('ABSPATH') || exit;

// Limpiar <head>.
function head_clean() {
  remove_action('wp_head', 'wp_generator');
  remove_action('wp_head', 'rsd_link');
  remove_action('wp_head', 'wlwmanifest_link');
  remove_action('wp_head', 'rest_output_link_wp_head', 10);
  remove_action('wp_head', 'wp_shortlink_wp_head', 10);
  remove_action('wp_head', 'feed_links_extra', 3);
  remove_action('wp_head', 'feed_links', 2);
  remove_action('wp_head', 'print_emoji_detection_script', 7);
  remove_action('wp_print_styles', 'print_emoji_styles');
}
add_action('init', __NAMESPACE__ . '\\head_clean');

// Quitar oEmbed discovery/embeds legacy si no usas.
add_action('after_setup_theme', function () {
  remove_action('wp_head', 'wp_oembed_add_discovery_links');
  remove_action('wp_head', 'wp_oembed_add_host_js');
}, 11);

// Desactivar comentarios si el sitio no los usa (opcional).
function disable_comments_everywhere() {
  // remove_post_type_support('post', 'comments');
  // remove_post_type_support('page', 'comments');
}
add_action('init', __NAMESPACE__ . '\\disable_comments_everywhere');
