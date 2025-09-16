<?php
namespace Elyxir;

defined('ABSPATH') || exit;

// --- Core hardening toggles ---
add_filter('xmlrpc_enabled', '__return_false');

function block_author_scan() {
  if (!is_admin() && isset($_GET['author'])) {
    wp_die(__('Forbidden', 'elyxir'), __('Forbidden', 'elyxir'), 403);
  }
}
add_action('init', __NAMESPACE__ . '\\block_author_scan');

function generic_login_errors() { return __('Login failed. Please try again.', 'elyxir'); }
add_filter('login_errors', __NAMESPACE__ . '\\generic_login_errors');

// Remove user endpoints ONLY for unauthenticated requests (avoid breaking editors).
function rest_harden_users($endpoints) {
  if (!is_user_logged_in()) {
    if (isset($endpoints['/wp/v2/users'])) { unset($endpoints['/wp/v2/users']); }
    if (isset($endpoints['/wp/v2/users/(?P<id>[\\d]+)'])) { unset($endpoints['/wp/v2/users/(?P<id>[\\d]+)']); }
  }
  return $endpoints;
}
add_filter('rest_endpoints', __NAMESPACE__ . '\\rest_harden_users');

// Security headers with Elementor/admin exceptions and safety guards
function security_headers() {
  // Si ya se enviaron headers o es preflight, no tocar.
  if (headers_sent() || (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'OPTIONS')) {
    return;
  }

  // Evitar romper respuestas puras JSON/REST (plugins/editores dependen de esto)
  if ((defined('REST_REQUEST') && REST_REQUEST) || (function_exists('wp_is_json_request') && wp_is_json_request())) {
    return;
  }

  // Contexto: admin/editor vs front
  $is_editor = is_admin()
    || (defined('REST_REQUEST') && REST_REQUEST && is_user_logged_in())
    || isset($_GET['elementor-preview'])
    || isset($_GET['elementor_library']);

  if (function_exists('header_remove')) { @header_remove('X-Powered-By'); @header_remove('Server'); }

  // Headers comunes
  header('X-Frame-Options: SAMEORIGIN');
  header('X-Content-Type-Options: nosniff');
  header('Referrer-Policy: strict-origin-when-cross-origin');

  if ($is_editor) {
    // Relaxed CSP para admin/Elementor/editor
    $permissions = apply_filters('elyxir/security/permissions_policy_admin', 'geolocation=(), microphone=(), camera=(), payment=()');
    if (!empty($permissions)) { header('Permissions-Policy: ' . $permissions); }

    $csp_admin = apply_filters(
      'elyxir/security/csp_admin',
      "default-src 'self' https: data: blob:; " .
      "script-src 'self' https: 'unsafe-inline' 'unsafe-eval' blob: data:; " .
      "style-src 'self' https: 'unsafe-inline' data:; " .
      "img-src 'self' https: data: blob:; " .
      "font-src 'self' https: data:; " .
      "connect-src 'self' https: data: blob:; " .
      "media-src 'self' https: data: blob:; " .
      "frame-ancestors 'self'; base-uri 'self'; form-action 'self';"
    );
    header('Content-Security-Policy: ' . $csp_admin);
    return;
  }

  // Front (visitantes): CSP estricta
  $permissions = apply_filters('elyxir/security/permissions_policy', 'geolocation=(), microphone=(), camera=(), payment=()');
  if (!empty($permissions)) { header('Permissions-Policy: ' . $permissions); }

  $enable_hsts = (bool) apply_filters('elyxir/security/enable_hsts', false);
  if ($enable_hsts && is_ssl()) { header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload'); }

  $csp = apply_filters(
  'elyxir/security/csp',
  "default-src 'self'; " .
  // --- AÑADIDOS PARA TU CASO ---
  "style-src 'self' 'unsafe-inline' https: data:; " .   // permite CSS inline y de CDNs
  "script-src 'self' 'unsafe-inline' https: data: blob:; " . // permite JS inline (y blob, por si hay builders)
  // --------------------------------
  "img-src 'self' data: https:; " .
  "font-src 'self' data: https:; " .
  "media-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'; form-action 'self'; " .
  "connect-src 'self' https:;"
);

  if (is_string($csp) && $csp !== '') { header('Content-Security-Policy: ' . $csp); }
}
add_action('send_headers', __NAMESPACE__ . '\\security_headers', 20);

// Optionally redirect author archives (keep disabled if you use them)
function disable_author_archives() {
  if (!is_admin() && is_author()) { wp_safe_redirect(home_url('/'), 301); exit; }
}
add_action('template_redirect', __NAMESPACE__ . '\\disable_author_archives');

if (!defined('DISALLOW_FILE_EDIT')) { define('DISALLOW_FILE_EDIT', true); }
