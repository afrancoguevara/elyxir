<?php
namespace Elyxir;
defined('ABSPATH') || exit;
add_filter('xmlrpc_enabled', '__return_false');
function block_author_scan(){ if(!is_admin() && isset($_GET['author'])) wp_die('Forbidden','Forbidden',403); }
add_action('init', __NAMESPACE__ . '\block_author_scan');
function generic_login_errors(){ return __('Login failed. Please try again.','elyxir'); }
add_filter('login_errors', __NAMESPACE__ . '\generic_login_errors');
function rest_harden_users($endpoints){ if(isset($endpoints['/wp/v2/users'])) unset($endpoints['/wp/v2/users']); if(isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']); return $endpoints; }
add_filter('rest_endpoints', __NAMESPACE__ . '\rest_harden_users');
function security_headers(){ if(is_admin()) return; if(function_exists('header_remove')){@header_remove('X-Powered-By');@header_remove('Server');}
header('X-Frame-Options: SAMEORIGIN'); header('X-Content-Type-Options: nosniff'); header('Referrer-Policy: strict-origin-when-cross-origin');
$permissions = apply_filters('elyxir/security/permissions_policy', 'geolocation=(), microphone=(), camera=(), payment=()'); if(!empty($permissions)) header('Permissions-Policy: ' . $permissions);
$enable_hsts = (bool) apply_filters('elyxir/security/enable_hsts', false); if($enable_hsts && is_ssl()) header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
$default_csp = "default-src 'self'; img-src 'self' data:; font-src 'self' data:; media-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'; form-action 'self';";
$csp = apply_filters('elyxir/security/csp', $default_csp); if(is_string($csp) && $csp!=='') header('Content-Security-Policy: ' . $csp);
}
add_action('send_headers', __NAMESPACE__ . '\security_headers', 20);
function disable_author_archives(){ if(!is_admin() && is_author()){ wp_safe_redirect(home_url('/'),301); exit; } }
add_action('template_redirect', __NAMESPACE__ . '\disable_author_archives');
if(!defined('DISALLOW_FILE_EDIT')){ define('DISALLOW_FILE_EDIT', true); }