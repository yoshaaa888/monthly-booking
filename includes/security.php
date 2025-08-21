<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!function_exists('mb_check_ajax_nonce_any')) {
    function mb_check_ajax_nonce_any($candidates) {
        foreach ($candidates as $c) {
            $action = isset($c['action']) ? $c['action'] : '';
            $field  = isset($c['field']) ? $c['field'] : '_ajax_nonce';
            if (check_ajax_referer($action, $field, false)) {
                return true;
            }
        }
        wp_send_json_error(__('Invalid or missing security token.', 'monthly-booking'), 403);
    }
}
