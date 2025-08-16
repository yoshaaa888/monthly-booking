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
add_action('admin_menu', function () {
    add_management_page('MB Test Nonce', 'MB Test Nonce', 'manage_options', 'mb-test-nonce', function () {
        wp_enqueue_script('mb-test-admin', plugins_url('mb-test-admin.js', __FILE__), ['jquery'], '1.0', true);
        wp_localize_script('mb-test-admin', 'monthlyBookingAdmin', [
            'nonce' => wp_create_nonce('monthly_booking_admin'),
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);
        echo '<div class="wrap"><h1>MB Test Nonce</h1><pre id="nonce-dump"></pre></div>';
    });
});

add_action('admin_print_footer_scripts', function () {
    if (isset($_GET['page']) && $_GET['page'] === 'mb-test-nonce') {
        echo "<script>document.getElementById('nonce-dump').textContent = JSON.stringify(monthlyBookingAdmin, null, 2);</script>";
    }
});
