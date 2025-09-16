<?php
namespace Elyxir;

defined('ABSPATH') || exit;

// Deshabilitar XML-RPC (si no lo necesitas).
add_filter('xmlrpc_enabled', '__return_false');

// Ocultar autores por ?author=N.
function block_author_scan() {
  if (!is_admin() && isset($_GET['author'])) wp_die('Forbidden', 403);
}
add_action('init', __NAMESPACE__ . '\\block_author_scan');

// Headers mínimos (CSP muy básica opcional; ajusta a tu stack).
function security_headers() {
  if (is_admin()) return;
  header('X-Frame-Options: SAMEORIGIN');
  header('X-Content-Type-Options: nosniff');
  header('Referrer-Policy: strict-origin-when-cross-origin');
  header('Permissions-Policy: geolocation=(), microphone=()');
}
add_action('send_headers', __NAMESPACE__ . '\\security_headers');
