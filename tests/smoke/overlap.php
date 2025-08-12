<?php

set_error_handler(function($errno, $errstr) {
    fwrite(STDERR, "[PHP ERROR] $errstr\n");
    return false;
});

$root = '/var/www/html';
$wp_load = $root . '/wp-load.php';
if (!file_exists($wp_load)) {
    fwrite(STDERR, "wp-load.php not found at $wp_load\n");
    exit(2);
}
require_once $wp_load;

if (!function_exists('is_plugin_active')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$plugin_slug = 'monthly-booking/monthly-booking.php';
if (!is_plugin_active($plugin_slug)) {
    activate_plugin($plugin_slug, '', false, true);
    if (!is_plugin_active($plugin_slug)) {
        fwrite(STDERR, "Plugin $plugin_slug is not active.\n");
        exit(3);
    }
}

if (!defined('MB_FEATURE_RESERVATIONS_MVP')) {
    define('MB_FEATURE_RESERVATIONS_MVP', true);
}

/* Ensure reservations table exists (same schema as admin-ui) */
global $wpdb;
$table_name = $wpdb->prefix . 'monthly_reservations';
$exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
if ($exists !== $table_name) {
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        room_id BIGINT UNSIGNED NOT NULL,
        checkin_date DATE NOT NULL,
        checkout_date DATE NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'confirmed',
        guest_name VARCHAR(190) NOT NULL,
        guest_email VARCHAR(190) NULL,
        base_daily_rate INT NULL,
        total_price INT NULL,
        notes TEXT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_room_period (room_id, checkin_date),
        KEY idx_room_period2 (room_id, checkout_date)
    ) $charset_collate;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

if (!class_exists('MonthlyBooking_Reservation_Service')) {
    $service_path = WP_PLUGIN_DIR . '/monthly-booking/includes/reservation-service.php';
    if (file_exists($service_path)) {
        require_once $service_path;
    }
}

global $wpdb;
$rooms_table = $wpdb->prefix . 'monthly_rooms';
$res_table = $wpdb->prefix . 'monthly_reservations';

$room_name = 'SMOKE-A';
$display_name = 'A';
$room_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$rooms_table} WHERE room_name=%s LIMIT 1", $room_name));
if (!$room_id) {
    $wpdb->insert($rooms_table, array(
        'room_name' => $room_name,
        'display_name' => $display_name,
        'is_active' => 1,
    ), array('%s','%s','%d'));
    $room_id = (int)$wpdb->insert_id;
    $wpdb->query($wpdb->prepare("UPDATE {$rooms_table} SET room_id = id WHERE id=%d", $room_id));
}

$wpdb->query($wpdb->prepare("DELETE FROM {$res_table} WHERE room_id=%d AND (guest_name LIKE %s OR notes='smoke')", $room_id, 'Smoke%'));

if (!class_exists('MonthlyBooking_Reservation_Service')) {
    fwrite(STDERR, "Reservation service not available.\n");
    exit(4);
}
$svc = new MonthlyBooking_Reservation_Service();

$base = array(
    'room_id' => $room_id,
    'guest_name' => 'Smoke Alpha',
    'guest_email' => 'alpha@example.com',
    'checkin_date' => '2025-09-01',
    'checkout_date' => '2025-09-15',
    'status' => 'confirmed',
    'notes' => 'smoke',
);
$res1 = $svc->create_reservation($base);
if (is_wp_error($res1)) {
    fwrite(STDERR, "Expected success creating first reservation, got error: " . $res1->get_error_message() . "\n");
    exit(10);
}
if (!is_int($res1) || $res1 <= 0) {
    fwrite(STDERR, "First reservation did not return a valid ID.\n");
    exit(11);
}

$overlap = $base;
$overlap['guest_name'] = 'Smoke Conflict';
$overlap['guest_email'] = 'conflict@example.com';
$overlap['checkin_date'] = '2025-09-10';
$overlap['checkout_date'] = '2025-09-20';
$res2 = $svc->create_reservation($overlap);
if (!is_wp_error($res2)) {
    fwrite(STDERR, "Expected overlap rejection, but reservation was created or returned non-error.\n");
    exit(12);
}

$edge = $base;
$edge['guest_name'] = 'Smoke Edge';
$edge['guest_email'] = 'edge@example.com';
$edge['checkin_date'] = '2025-09-15';
$edge['checkout_date'] = '2025-09-20';
$res3 = $svc->create_reservation($edge);
if (is_wp_error($res3)) {
    fwrite(STDERR, "Expected edge-touch success, got error: " . $res3->get_error_message() . "\n");
    exit(13);
}

echo "OK: overlap smoke passed (created1={$res1}, edge={$res3}, conflict rejected)\n";
exit(0);
