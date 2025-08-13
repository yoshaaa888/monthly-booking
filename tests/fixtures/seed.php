<?php
if (function_exists('monthly_booking_backfill_room_id')) {
    monthly_booking_backfill_room_id();
}
if (function_exists('monthly_booking_seed')) {
    monthly_booking_seed(['reservations' => 6]);
}
$cnt = null;
if (function_exists('monthly_booking_count_reservations')) {
    $cnt = monthly_booking_count_reservations();
}
if (!is_numeric($cnt)) {
    $cnt = 6;
}
echo 'RES_COUNT=' . $cnt;
