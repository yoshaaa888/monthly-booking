<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('MonthlyBooking_Calendar_API')) {
class MonthlyBooking_Calendar_API {

    public function mbp_get_rooms($room_ids = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'monthly_rooms';
        if (!empty($room_ids)) {
            $in = implode(',', array_map('intval', $room_ids));
            return $wpdb->get_results("SELECT room_id AS id, room_name AS room_name, display_name AS name FROM $table WHERE room_id IN ($in) AND is_active=1 ORDER BY room_id");
        }
        return $wpdb->get_results("SELECT room_id AS id, room_name AS room_name, display_name AS name FROM $table WHERE is_active=1 ORDER BY room_id");
    }

    public function mbp_get_bookings($room_id, $from, $to) {
        $args = array(
            'post_type' => 'mrb_booking',
            'post_status' => array('publish','pending','draft'),
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array('key'=>'room_id','value'=>intval($room_id),'compare'=>'=','type'=>'NUMERIC'),
                array('key'=>'checkin_date','value'=>$to,'compare'=>'<','type'=>'CHAR'),
                array('key'=>'checkout_date','value'=>$from,'compare'=>'>','type'=>'CHAR'),
                array('key'=>'status','value'=>'cancelled','compare'=>'!=','type'=>'CHAR'),
            ),
            'fields' => 'ids',
        );
        $q = new WP_Query($args);
        $res = array();
        foreach ($q->posts as $pid) {
            $ci = get_post_meta($pid, 'checkin_date', true);
            $co = get_post_meta($pid, 'checkout_date', true);
            if (!$ci || !$co) continue;
            $status = get_post_meta($pid, 'status', true);
            if ($status === 'cancelled') continue;
            $res[] = array('id'=>$pid,'checkin'=>$ci,'checkout'=>$co,'status'=>$status);
        }
        return $res;
    }

    public function mbp_get_bookings_for_rooms($room_ids, $from, $to) {
        if (empty($room_ids)) return array();
        $args = array(
            'post_type' => 'mrb_booking',
            'post_status' => array('publish','pending','draft'),
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key'=>'room_id',
                    'value'=> array_map('intval', $room_ids),
                    'compare'=>'IN',
                ),
                array('key'=>'checkin_date','value'=>$to,'compare'=>'<','type'=>'CHAR'),
                array('key'=>'checkout_date','value'=>$from,'compare'=>'>','type'=>'CHAR'),
                array('key'=>'status','value'=>'cancelled','compare'=>'!=','type'=>'CHAR'),
            ),
            'fields' => 'ids',
        );
        $q = new WP_Query($args);
        $out = array();
        foreach ($room_ids as $rid) { $out[intval($rid)] = array(); }
        foreach ($q->posts as $pid) {
            $rid = intval(get_post_meta($pid, 'room_id', true));
            $ci = get_post_meta($pid, 'checkin_date', true);
            $co = get_post_meta($pid, 'checkout_date', true);
            if (!$rid || !$ci || !$co) continue;
            $status = get_post_meta($pid, 'status', true);
            if ($status === 'cancelled') continue;
            $out[$rid][] = array('id'=>$pid,'checkin'=>$ci,'checkout'=>$co,'status'=>$status);
        }
        return $out;
    }

    public function mbp_get_campaign_days($room_id, $from, $to) {
        return array();
    }

    public function get_global_campaigns($from, $to) {
        return array();
    }

    public function mbp_get_assignments_for_rooms($room_ids, $from, $to) {
        global $wpdb;
        if (empty($room_ids)) return array();
        $table = $wpdb->prefix . 'monthly_room_campaigns';
        $in = implode(',', array_map('intval', $room_ids));
        $from_esc = esc_sql($from);
        $to_esc = esc_sql($to);
        $sql = "
            SELECT room_id, campaign_id, start_date, end_date, is_active
            FROM $table
            WHERE room_id IN ($in)
              AND is_active = 1
              AND (
                    (end_date IS NULL OR end_date > '$from_esc')
                AND (start_date IS NULL OR start_date < '$to_esc')
              )
        ";
        $rows = $wpdb->get_results($sql, ARRAY_A);
        $out = array();
        foreach ($room_ids as $rid) { $out[intval($rid)] = array(); }
        foreach ($rows as $r) {
            $rid = intval($r['room_id']);
            if (!isset($out[$rid])) $out[$rid] = array();
            $out[$rid][] = array(
                'campaign_id' => intval($r['campaign_id']),
                'start_date' => $r['start_date'],
                'end_date' => $r['end_date'],
                'is_active' => intval($r['is_active'])
            );
        }
        return $out;
    }
}}
