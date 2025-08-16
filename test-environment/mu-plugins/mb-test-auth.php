<?php
/**
 * Plugin Name: MB Test Auth
 * Description: Creates a test admin user for Playwright authentication in the test environment.
 */

add_action('init', function() {
    if (defined('WP_INSTALLING') && WP_INSTALLING) {
        return;
    }

    if (is_multisite() && !is_main_site()) {
        return;
    }

    $username = 'mb_admin';
    $password = 'mbpass';
    $email    = 'mb_admin@example.com';

    if (!username_exists($username)) {
        $user_id = wp_create_user($username, $password, $email);
        if (!is_wp_error($user_id)) {
            $user = new WP_User($user_id);
            $user->set_role('administrator');
        }
    }
});
