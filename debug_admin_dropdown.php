<?php
require_once('/var/www/html/monthly-booking/wp-config.php');

global $wpdb;

echo "=== ADMIN DROPDOWN HTML DEBUG ===\n\n";

$rooms_table = $wpdb->prefix . 'monthly_rooms';
$sql = "SELECT id, room_id, display_name, room_name, property_name 
        FROM $rooms_table 
        WHERE is_active = 1 
        ORDER BY property_name, room_name";

$rooms = $wpdb->get_results($sql);

echo "Rooms query result:\n";
var_dump($rooms);

echo "\nGenerated HTML dropdown options:\n";
echo '<select id="room_select" name="room_id">' . "\n";
echo '    <option value="0">部屋を選択してください</option>' . "\n";

foreach ($rooms as $room) {
    $option_html = sprintf(
        '    <option value="%d">%s (%s)</option>',
        $room->id,
        htmlspecialchars($room->display_name . ' (' . $room->room_name . ')'),
        htmlspecialchars($room->property_name)
    );
    echo $option_html . "\n";
}

echo '</select>' . "\n";

echo "\nJavaScript onchange URL generation test:\n";
$admin_url = 'http://t-monthlybookig.local/wp-admin/admin.php?page=monthly-room-booking-calendar&room_id=';
echo "Base URL: $admin_url\n";
echo "Example URL for room ID 1: {$admin_url}1\n";

echo "\n=== DEBUG COMPLETE ===\n";
?>
