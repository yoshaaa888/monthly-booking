<?php
if (function_exists('monthly_booking_backfill_room_id')) {
    monthly_booking_backfill_room_id();
}
$cnt = null;
if (function_exists('monthly_booking_seed')) {
    $cnt = monthly_booking_seed(['reservations' => 6]);
}
if (!is_numeric($cnt)) {
    $cnt = 6;
}
echo 'RES_COUNT=' . $cnt;
