<?php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

require_once('/var/www/html/monthly-booking/wp-config.php');

echo "=== ADMIN ROOM LOADING DEBUG ===\n\n";

echo "1. WordPress Context:\n";
echo "Current user ID: " . get_current_user_id() . "\n";
echo "User can manage options: " . (current_user_can('manage_options') ? 'Yes' : 'No') . "\n";

global $wpdb;
$rooms_table = $wpdb->prefix . 'monthly_rooms';

echo "\n2. Direct Database Test:\n";
$sql = "SELECT id, room_id, display_name, room_name, property_name 
        FROM $rooms_table 
        WHERE is_active = 1 
        ORDER BY property_name, room_name";

$rooms = $wpdb->get_results($sql);

if ($wpdb->last_error) {
    echo "❌ Database Error: " . $wpdb->last_error . "\n";
} else {
    echo "✅ Query successful, found " . count($rooms) . " rooms\n";
    foreach ($rooms as $room) {
        echo "- {$room->display_name}\n";
    }
}

echo "\n3. WordPress Error Log Check:\n";
$log_file = WP_CONTENT_DIR . '/debug.log';
if (file_exists($log_file)) {
    echo "✅ Debug log exists\n";
    echo "Recent entries:\n";
    $log_lines = file($log_file);
    $recent_lines = array_slice($log_lines, -5);
    foreach ($recent_lines as $line) {
        echo "  " . trim($line) . "\n";
    }
} else {
    echo "❌ Debug log not found\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
?>
