<?php
if (!defined('ABSPATH')) {
    exit;
}
if (apply_filters('mb_enable_price_example', false)) {
    add_action('monthly_booking_reservation_saved', function($id, $data, $total) {
        $nights = 0;
        try {
            $in = new DateTime($data['checkin_date']);
            $out = new DateTime($data['checkout_date']);
            $nights = max(0, $out->diff($in)->days);
        } catch (Throwable $e) {
        }
        $per_day = 0;
        if (isset($data['base_daily_price'])) {
            $per_day = intval($data['base_daily_price']);
        } elseif (isset($data['daily_rent'])) {
            $per_day = intval($data['daily_rent']);
        }
        $subtotal = $per_day * $nights;
        $sum = 0;
        if (isset($data['discounts']) && is_array($data['discounts'])) {
            foreach ($data['discounts'] as $d) {
                if (isset($d['amount'])) {
                    $sum += intval($d['amount']);
                }
            }
        }
        $recalc = $subtotal + $sum;
        error_log(sprintf('[MB price-example] id=%d nights=%d per_day=%d saved_total=%s recalc=%d', $id, $nights, $per_day, (string)$total, $recalc));
    }, 10, 3);
}
