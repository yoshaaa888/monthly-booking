<?php
require_once('/var/www/html/monthly-booking/wp-config.php');

global $wpdb;

echo "=== ADMIN ROOM SELECTION DEBUG ===\n\n";

$rooms_table = $wpdb->prefix . 'monthly_rooms';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$rooms_table'");

if (!$table_exists) {
    echo "❌ ERROR: Table '$rooms_table' does not exist!\n";
    echo "Solution: Run the plugin activation to create tables.\n\n";
} else {
    echo "✅ Table '$rooms_table' exists\n\n";
    
    $total_rooms = $wpdb->get_var("SELECT COUNT(*) FROM $rooms_table");
    echo "Total rooms in database: $total_rooms\n";
    
    $active_rooms = $wpdb->get_var("SELECT COUNT(*) FROM $rooms_table WHERE is_active = 1");
    echo "Active rooms (is_active = 1): $active_rooms\n\n";
    
    if ($active_rooms == 0) {
        echo "❌ PROBLEM: No active rooms found!\n";
        echo "This is why the dropdown is empty.\n\n";
        
        $all_rooms = $wpdb->get_results("SELECT id, room_id, display_name, room_name, property_name, is_active FROM $rooms_table ORDER BY property_name, room_name");
        
        if (empty($all_rooms)) {
            echo "No rooms exist in the database at all.\n";
            echo "Solution: Import room data using seed_data.sql\n\n";
        } else {
            echo "Existing rooms and their status:\n";
            foreach ($all_rooms as $room) {
                $status = $room->is_active ? '✅ Active' : '❌ Inactive';
                echo "- ID: {$room->id}, Room: {$room->display_name} ({$room->room_name}), Status: $status\n";
            }
            echo "\nSolution: Update rooms to active status:\n";
            echo "UPDATE $rooms_table SET is_active = 1 WHERE is_active = 0;\n\n";
        }
    } else {
        echo "✅ Found $active_rooms active rooms\n";
        
        $active_room_list = $wpdb->get_results("SELECT id, room_id, display_name, room_name, property_name FROM $rooms_table WHERE is_active = 1 ORDER BY property_name, room_name");
        
        echo "Active rooms that should appear in dropdown:\n";
        foreach ($active_room_list as $room) {
            echo "- ID: {$room->id}, Room: {$room->display_name} ({$room->room_name}), Property: {$room->property_name}\n";
        }
        echo "\nIf dropdown is still empty, check for JavaScript errors in browser console.\n";
    }
}

echo "\n=== DEBUGGING COMPLETE ===\n";
?>
