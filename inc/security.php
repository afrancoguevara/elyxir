<?php
/**
 * Security hardening for Elyxir theme.
 *
 * Safe by default, configurable via filters:
 * - elyxir/security/enable_hsts (bool)
 * - elyxir/security/csp (string|null)  // null disables CSP header
 * - elyxir/security/permissions_policy (string)
 *
 * @package Elyxir
 */

namespace Elyxir;

defined('ABSPATH') || exit;

/**
 * ----------------------
 * Disable XML-RPC (opt-in)
 * ----------------------
 * XML-RPC is rarely needed nowadays; keep disabled unless explicitly required.
 */
add_filter('xmlrpc_enabled', '__return_false');

/**
 * ------------------------------------
 * Block author scans (?author=) on FE
 * ------------------------------------
 */
function block_author_scan() {
  if (!is_admin() && isset($_GET['author'])) {
    // Avoid leaking author IDs / usernames
    wp_die('Forbidden', 'Forbidden', 403);
  }
}
add_action('init', __NAMESPACE__ . '\\block_author_scan');

/**
 * -------------------------------
 * Reduce login error information
 * -------------------------------
 */
function generic_login_errors() {
  // Prevents disclosing whether username or password was wrong
  return __('Login failed. Please try again.', 'elyxir');
}
add_filter('login_errors', __NAMESPACE__ . '\\generic_login_errors');

/**
 * --------------------------------------
 * Remove users endpoints from REST API
 * --------------------------------------
 * This prevents unauthenticated enumeration of users.
 * (Admin/UI and authenticated code can still query users via PHP.)
 */
function rest_harden_users($endpoints) {
  if (isset($endpoints['/wp/v2/users'])) {
    unset($endpoints['/wp/v2/users']);
  }
  if (isset($endpoints['/wp/v2/users/(?P<id>[\\d]+)'])) {
    unset($endpoints['/wp/v2/users/(?P<id>[\\d]+)']);
  }
  return $endpoints;
}
add_filter('rest_endpoints', __NAMESPACE__ . '\\rest_harden_users');

/**
 * ----------------------------
 * Clean up discovery in <head>
 * ----------------------------
 * Reduce attack surface & info leaks on the front-end.
 */
function cleanup_head_links() {
  if (is_admin()) return;
  remove_action('wp_head', 'wp_generator');                              // WP version
  remove_action('wp_head', 'rsd_link');                                  // Really Simple Discovery
  remove_action('wp_head', 'wlwmanifest_link');                          // Windows Live Writer
  remove_action('wp_head', 'wp_shortlink_wp_head');                      // Shortlink
  remove_action('wp_head', 'rest_output_link_wp_head');                  // REST API link tag
  remove_action('template_redirect', 'rest_output_link_header', 11);     // REST API header
  remove_action('wp_head', 'print_emoji_detection_script', 7);
  remove_action('wp_print_styles', 'print_emoji_styles');
}
add_action('init', __NAMESPACE__ . '\\cleanup_head_links');

/**
 * ----------------
 * Security headers
 * ----------------
 * Sent on the front-end only.
 */
function security_headers() {
  if (is_admin()) return;

  // Try to remove potentially sensitive defaults (may be ignored by some SAPIs)
  if (function_exists('header_remove')) {
    @header_remove('X-Powered-By');
    @header_remove('Server');
  }

  // Frame busting
  header('X-Frame-Options: SAMEORIGIN');
  // MIME sniffing protection
  header('X-Content-Type-Options: nosniff');
  // Referrer policy
  header('Referrer-Policy: strict-origin-when-cross-origin');

  // Permissions-Policy — customize via filter if needed
  $permissions = apply_filters(
    'elyxir/security/permissions_policy',
    'geolocation=(), microphone=(), camera=(), payment=()'
  );
  if (!empty($permissions)) {
    header('Permissions-Policy: ' . $permissions);
  }

  // Optional HSTS (enable only if your entire domain uses HTTPS)
  $enable_hsts = (bool) apply_filters('elyxir/security/enable_hsts', false);
  if ($enable_hsts && is_ssl()) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
  }

  // Basic CSP — keep minimal to avoid breaking site; override via filter for stricter policies.
  // Default allows same-origin assets plus data: images/fonts; disallows inline/eval by default.
  $default_csp = "default-src 'self'; img-src 'self' data:; font-src 'self' data:; media-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'; form-action 'self';";
  $csp = apply_filters('elyxir/security/csp', $default_csp);
  if (is_string($csp) && $csp !== '') {
    header('Content-Security-Policy: ' . $csp);
  }
}
add_action('send_headers', __NAMESPACE__ . '\\security_headers', 20);

/**
 * ---------------------------------
 * Deny author archive enumeration
 * ---------------------------------
 * If you don't use author archives publicly, redirect them.
 * Comment this out if author archives are needed.
 */
function disable_author_archives() {
  if (!is_admin() && is_author()) {
    wp_safe_redirect(home_url('/'), 301);
    exit;
  }
}
add_action('template_redirect', __NAMESPACE__ . '\\disable_author_archives');

/**
 * -----------------------------
 * Disallow file editing in WP
 * -----------------------------
 * NOTE: Do this in wp-config.php ideally, but we keep a safety net here.
 */
if (!defined('DISALLOW_FILE_EDIT')) {
  define('DISALLOW_FILE_EDIT', true);
}
