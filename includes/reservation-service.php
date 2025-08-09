<?php
if (!defined('ABSPATH')) {
    exit;
}

class MonthlyBooking_Reservation_Service {
    
    public function create_reservation($data) {
        global $wpdb;
        
        $validation = $this->validate_reservation_data($data);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        if ($this->check_reservation_conflict($data['room_id'], $data['checkin_date'], $data['checkout_date'])) {
            return new WP_Error('conflict', __('選択された期間は既に予約されています。', 'monthly-booking'));
        }
        
        $base_price = $this->calculate_base_price($data);
        $total_price = $this->calculate_total_price($data, $base_price);
        
        $table_name = $wpdb->prefix . 'monthly_reservations';
        $result = $wpdb->insert(
            $table_name,
            array(
                'room_id' => $data['room_id'],
                'guest_name' => $data['guest_name'],
                'guest_email' => $data['guest_email'],
                'checkin_date' => $data['checkin_date'],
                'checkout_date' => $data['checkout_date'],
                'status' => $data['status'] ?: 'confirmed',
                'base_daily_rate' => $base_price,
                'total_price' => $total_price,
                'notes' => $data['notes'],
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s')
        );
        
        return $result ? $wpdb->insert_id : new WP_Error('db_error', __('データベースエラーが発生しました。', 'monthly-booking'));
    }
    
    public function update_reservation($reservation_id, $data) {
        global $wpdb;
        
        $validation = $this->validate_reservation_data($data);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        if ($this->check_reservation_conflict($data['room_id'], $data['checkin_date'], $data['checkout_date'], $reservation_id)) {
            return new WP_Error('conflict', __('選択された期間は既に予約されています。', 'monthly-booking'));
        }
        
        $base_price = $this->calculate_base_price($data);
        $total_price = $this->calculate_total_price($data, $base_price);
        
        $table_name = $wpdb->prefix . 'monthly_reservations';
        $result = $wpdb->update(
            $table_name,
            array(
                'room_id' => $data['room_id'],
                'guest_name' => $data['guest_name'],
                'guest_email' => $data['guest_email'],
                'checkin_date' => $data['checkin_date'],
                'checkout_date' => $data['checkout_date'],
                'status' => $data['status'],
                'base_daily_rate' => $base_price,
                'total_price' => $total_price,
                'notes' => $data['notes'],
                'updated_at' => current_time('mysql')
            ),
            array('id' => $reservation_id),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s'),
            array('%d')
        );
        
        return $result !== false ? true : new WP_Error('db_error', __('データベースエラーが発生しました。', 'monthly-booking'));
    }
    
    public function delete_reservation($reservation_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_reservations';
        $result = $wpdb->delete(
            $table_name,
            array('id' => $reservation_id),
            array('%d')
        );
        
        return $result !== false ? true : new WP_Error('db_error', __('データベースエラーが発生しました。', 'monthly-booking'));
    }
    
    public function get_reservations($page = 1, $per_page = 20) {
        global $wpdb;
        
        $offset = ($page - 1) * $per_page;
        $table_name = $wpdb->prefix . 'monthly_reservations';
        $rooms_table = $wpdb->prefix . 'monthly_rooms';
        
        $sql = "SELECT r.*, rm.room_name, rm.property_name 
                FROM $table_name r 
                LEFT JOIN $rooms_table rm ON r.room_id = rm.room_id 
                ORDER BY r.created_at DESC 
                LIMIT %d OFFSET %d";
        
        $reservations = $wpdb->get_results($wpdb->prepare($sql, $per_page, $offset));
        
        $count_sql = "SELECT COUNT(*) FROM $table_name";
        $total = $wpdb->get_var($count_sql);
        
        return array(
            'reservations' => $reservations,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        );
    }
    
    public function get_reservation($reservation_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_reservations';
        $rooms_table = $wpdb->prefix . 'monthly_rooms';
        
        $sql = "SELECT r.*, rm.room_name, rm.property_name 
                FROM $table_name r 
                LEFT JOIN $rooms_table rm ON r.room_id = rm.room_id 
                WHERE r.id = %d";
        
        return $wpdb->get_row($wpdb->prepare($sql, $reservation_id));
    }
    
    private function validate_reservation_data($data) {
        if (empty($data['room_id'])) {
            return new WP_Error('validation', __('部屋を選択してください。', 'monthly-booking'));
        }
        
        if (empty($data['guest_name'])) {
            return new WP_Error('validation', __('ゲスト名を入力してください。', 'monthly-booking'));
        }
        
        if (!empty($data['guest_email']) && !is_email($data['guest_email'])) {
            return new WP_Error('validation', __('有効なメールアドレスを入力してください。', 'monthly-booking'));
        }
        
        if (empty($data['checkin_date']) || empty($data['checkout_date'])) {
            return new WP_Error('validation', __('チェックイン日とチェックアウト日を入力してください。', 'monthly-booking'));
        }
        
        $checkin = new DateTime($data['checkin_date']);
        $checkout = new DateTime($data['checkout_date']);
        
        if ($checkin >= $checkout) {
            return new WP_Error('validation', __('チェックアウト日はチェックイン日より後の日付を選択してください。', 'monthly-booking'));
        }
        
        if ($checkin < new DateTime('today')) {
            return new WP_Error('validation', __('チェックイン日は今日以降の日付を選択してください。', 'monthly-booking'));
        }
        
        return true;
    }
    
    private function check_reservation_conflict($room_id, $checkin, $checkout, $exclude_id = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_reservations';
        $sql = "SELECT id FROM $table_name 
                WHERE room_id = %d 
                AND status != 'canceled'
                AND NOT (checkout_date <= %s OR %s <= checkin_date)";
        
        $params = array($room_id, $checkin, $checkout);
        
        if ($exclude_id) {
            $sql .= " AND id != %d";
            $params[] = $exclude_id;
        }
        
        $conflict = $wpdb->get_row($wpdb->prepare($sql, $params));
        return !empty($conflict);
    }
    
    private function calculate_base_price($data) {
        global $wpdb;
        
        $rooms_table = $wpdb->prefix . 'monthly_rooms';
        $room = $wpdb->get_row($wpdb->prepare(
            "SELECT daily_rent FROM $rooms_table WHERE room_id = %d",
            $data['room_id']
        ));
        
        if (!$room || !$room->daily_rent) {
            return 0;
        }
        
        return intval($room->daily_rent);
    }
    
    private function calculate_total_price($data, $base_daily_rate) {
        $checkin = new DateTime($data['checkin_date']);
        $checkout = new DateTime($data['checkout_date']);
        $days = $checkin->diff($checkout)->days;
        
        return $base_daily_rate * $days;
    }
}
