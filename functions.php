<?php
/**
 * Theme bootstrap.
 */
namespace Elyxir;

defined('ABSPATH') || exit;

define(__NAMESPACE__ . '\PATH', get_template_directory());
define(__NAMESPACE__ . '\URI', get_template_directory_uri());
define(__NAMESPACE__ . '\VER', wp_get_theme()->get('Version') ?: '1.0.0');

// Habilitar HSTS (asegúrate de HTTPS en TODO el dominio)
add_filter('elyxir_mu/security/enable_hsts', '__return_true');

// Ajustar Permissions-Policy
add_filter('elyxir_mu/security/permissions_policy', function($p) {
  return 'geolocation=(), microphone=(), camera=(), payment=()';
});

// Endurecer CSP (valida que no rompa assets externos/inline)
add_filter('elyxir_mu/security/csp', function($csp) {
  return "default-src 'self'; img-src 'self' data: https:; font-src 'self' data:; script-src 'self'; style-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'";
});


// Carga módulos.
require PATH . '/inc/setup.php';
require PATH . '/inc/assets.php';
require PATH . '/inc/cleanup.php';
require PATH . '/inc/security.php';
require PATH . '/inc/media.php';
require PATH . '/inc/editor.php';
require get_template_directory() . '/inc/template-tags.php';
