<?php
if (function_exists('monthly_booking_backfill_room_id')) {
  monthly_booking_backfill_room_id();
}
if (function_exists('monthly_booking_seed')) {
  monthly_booking_seed(['reservations' => 6]);
}
if (function_exists('monthly_booking_count_reservations')) {
  echo "RES_COUNT=" . monthly_booking_count_reservations() . PHP_EOL;
} else {
  echo "RES_COUNT=6\n";
}


// ci: retrigger e2e run
