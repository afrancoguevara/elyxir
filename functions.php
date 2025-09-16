<?php
/**
 * Theme bootstrap.
 */
namespace Elyxir;

defined('ABSPATH') || exit;

define(__NAMESPACE__ . '\PATH', get_template_directory());
define(__NAMESPACE__ . '\URI', get_template_directory_uri());
define(__NAMESPACE__ . '\VER', wp_get_theme()->get('Version') ?: '1.0.0');

// Carga módulos.
require PATH . '/inc/setup.php';
require PATH . '/inc/assets.php';
require PATH . '/inc/cleanup.php';
require PATH . '/inc/security.hardened.php';
require PATH . '/inc/media.php';
require PATH . '/inc/editor.php';
require get_template_directory() . '/inc/template-tags.php';
