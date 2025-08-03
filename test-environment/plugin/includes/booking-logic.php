<?php
/**
 * Booking logic and pricing calculations for Monthly Booking plugin
 * 
 * @package MonthlyBooking
 */

if (!defined('ABSPATH')) {
    exit;
}

class MonthlyBooking_Booking_Logic {
    
    public function __construct() {
        add_action('wp_ajax_calculate_booking_price', array($this, 'ajax_calculate_price'));
        add_action('wp_ajax_nopriv_calculate_booking_price', array($this, 'ajax_calculate_price'));
        add_action('wp_ajax_calculate_estimate', array($this, 'ajax_calculate_estimate'));
        add_action('wp_ajax_nopriv_calculate_estimate', array($this, 'ajax_calculate_estimate'));
        add_action('wp_ajax_submit_booking', array($this, 'ajax_submit_booking'));
        add_action('wp_ajax_nopriv_submit_booking', array($this, 'ajax_submit_booking'));
        add_action('wp_ajax_get_booking_options', array($this, 'ajax_get_options'));
        add_action('wp_ajax_nopriv_get_booking_options', array($this, 'ajax_get_options'));
        add_action('wp_ajax_search_properties', array($this, 'ajax_search_properties'));
        add_action('wp_ajax_nopriv_search_properties', array($this, 'ajax_search_properties'));
        add_action('wp_ajax_get_search_filters', array($this, 'ajax_get_search_filters'));
        add_action('wp_ajax_nopriv_get_search_filters', array($this, 'ajax_get_search_filters'));
    }
    
    /**
     * Calculate booking price via AJAX
     */
    public function ajax_calculate_price() {
        check_ajax_referer('monthly_booking_nonce', 'nonce');
        
        $start_date = sanitize_text_field($_POST['start_date']);
        $end_date = sanitize_text_field($_POST['end_date']);
        $property_id = intval($_POST['property_id']);
        
        if (empty($start_date) || empty($end_date)) {
            wp_send_json_error(__('Invalid dates provided.', 'monthly-booking'));
        }
        
        $price_data = $this->calculate_booking_price($start_date, $end_date, $property_id);
        
        wp_send_json_success($price_data);
    }
    
    /**
     * Submit booking via AJAX
     */
    public function ajax_submit_booking() {
        check_ajax_referer('monthly_booking_nonce', 'nonce');
        
        $booking_data = array(
            'room_id' => intval($_POST['room_id']),
            'move_in_date' => sanitize_text_field($_POST['move_in_date']),
            'move_out_date' => sanitize_text_field($_POST['move_out_date']),
            'stay_months' => intval($_POST['stay_months']),
            'plan_type' => sanitize_text_field($_POST['plan_type']),
            'num_adults' => intval($_POST['num_adults']),
            'num_children' => intval($_POST['num_children']),
            'selected_options' => isset($_POST['selected_options']) ? $_POST['selected_options'] : array(),
            
            'daily_rent' => floatval($_POST['daily_rent']),
            'total_rent' => floatval($_POST['total_rent']),
            'daily_utilities' => floatval($_POST['daily_utilities']),
            'total_utilities' => floatval($_POST['total_utilities']),
            'cleaning_fee' => floatval($_POST['cleaning_fee']),
            'key_fee' => floatval($_POST['key_fee']),
            'bedding_fee' => floatval($_POST['bedding_fee']),
            'initial_costs' => floatval($_POST['initial_costs']),
            'person_additional_fee' => floatval($_POST['person_additional_fee']),
            'options_total' => floatval($_POST['options_total']),
            'options_discount' => floatval($_POST['options_discount']),
            'total_price' => floatval($_POST['total_price']),
            'campaign_discount' => floatval($_POST['campaign_discount']),
            'final_price' => floatval($_POST['final_price']),
            
            'guest_name' => sanitize_text_field($_POST['guest_name']),
            'guest_email' => sanitize_email($_POST['guest_email']),
            'guest_phone' => sanitize_text_field($_POST['guest_phone']),
            'company_name' => sanitize_text_field($_POST['company_name']),
            'special_requests' => sanitize_textarea_field($_POST['special_requests'])
        );
        
        $validation = $this->validate_step4_booking_data($booking_data);
        if (is_wp_error($validation)) {
            wp_send_json_error($validation->get_error_message());
        }
        
        $customer_id = $this->create_or_get_customer($booking_data);
        if (!$customer_id) {
            wp_send_json_error(__('Failed to create customer record.', 'monthly-booking'));
        }
        
        $booking_data['customer_id'] = $customer_id;
        $booking_id = $this->create_step4_booking($booking_data);
        
        if ($booking_id) {
            $this->save_booking_options($booking_id, $booking_data['selected_options']);
            
            $external_response = $this->send_to_external_accounting_system($booking_id, $booking_data);
            
            wp_send_json_success(array(
                'booking_id' => $booking_id,
                'customer_id' => $customer_id,
                'external_response' => $external_response,
                'message' => __('仮予約が完了しました！', 'monthly-booking')
            ));
        } else {
            wp_send_json_error(__('Failed to create booking. Please try again.', 'monthly-booking'));
        }
    }
    
    /**
     * Calculate booking price with campaigns and cleaning periods
     */
    public function calculate_booking_price($start_date, $end_date, $property_id = 1) {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = $start->diff($end);
        $days = $interval->days;
        
        $options = get_option('monthly_booking_options');
        $base_price = isset($options['default_price']) ? floatval($options['default_price']) : 100000;
        $cleaning_days = isset($options['cleaning_days']) ? intval($options['cleaning_days']) : 3;
        
        $months = ceil($days / 30);
        $base_total = $base_price * $months;
        
        $campaign_discount = $this->calculate_campaign_discount($start_date, $end_date, $base_total);
        
        $final_price = $base_total - $campaign_discount;
        
        return array(
            'start_date' => $start_date,
            'end_date' => $end_date,
            'days' => $days,
            'months' => $months,
            'base_price' => $base_price,
            'base_total' => $base_total,
            'campaign_discount' => $campaign_discount,
            'final_price' => $final_price,
            'cleaning_days' => $cleaning_days,
            'currency' => '¥'
        );
    }
    
    /**
     * Calculate campaign discounts
     */
    private function calculate_campaign_discount($start_date, $end_date, $base_total) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_booking_campaigns';
        
        $campaigns = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE is_active = 1 
             AND start_date <= %s 
             AND end_date >= %s",
            $start_date,
            $end_date
        ));
        
        $total_discount = 0;
        
        foreach ($campaigns as $campaign) {
            if ($campaign->discount_type === 'percentage') {
                $discount = ($base_total * $campaign->discount_value) / 100;
            } else {
                $discount = $campaign->discount_value;
            }
            
            $total_discount += $discount;
        }
        
        return min($total_discount, $base_total * 0.5);
    }
    
    /**
     * Validate booking data
     */
    private function validate_booking_data($data) {
        if (empty($data['guest_name'])) {
            return new WP_Error('invalid_name', __('Guest name is required.', 'monthly-booking'));
        }
        
        if (empty($data['guest_email']) || !is_email($data['guest_email'])) {
            return new WP_Error('invalid_email', __('Valid email address is required.', 'monthly-booking'));
        }
        
        if (empty($data['start_date']) || empty($data['end_date'])) {
            return new WP_Error('invalid_dates', __('Start and end dates are required.', 'monthly-booking'));
        }
        
        $start = new DateTime($data['start_date']);
        $end = new DateTime($data['end_date']);
        
        if ($start >= $end) {
            return new WP_Error('invalid_date_range', __('End date must be after start date.', 'monthly-booking'));
        }
        
        if ($this->check_booking_conflict($data['start_date'], $data['end_date'], $data['property_id'])) {
            return new WP_Error('booking_conflict', __('Selected dates are not available.', 'monthly-booking'));
        }
        
        return true;
    }
    
    /**
     * Check for booking conflicts
     */
    private function check_booking_conflict($start_date, $end_date, $property_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_bookings';
        
        $conflict = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name 
             WHERE property_id = %d 
             AND status != 'cancelled'
             AND (
                 (start_date <= %s AND end_date >= %s) OR
                 (start_date <= %s AND end_date >= %s) OR
                 (start_date >= %s AND end_date <= %s)
             )",
            $property_id,
            $start_date, $start_date,
            $end_date, $end_date,
            $start_date, $end_date
        ));
        
        return !empty($conflict);
    }
    
    /**
     * Create new booking
     */
    private function create_booking($data) {
        global $wpdb;
        
        $price_data = $this->calculate_booking_price($data['start_date'], $data['end_date'], $data['property_id']);
        
        $table_name = $wpdb->prefix . 'monthly_bookings';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'property_id' => $data['property_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'guest_name' => $data['guest_name'],
                'guest_email' => $data['guest_email'],
                'total_price' => $price_data['final_price'],
                'status' => 'pending',
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%f', '%s', '%s')
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    private function create_or_get_customer($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_customers';
        
        $existing_customer = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE email = %s AND is_active = 1",
            $data['guest_email']
        ));
        
        if ($existing_customer) {
            return $existing_customer->id;
        }
        
        $name_parts = explode(' ', trim($data['guest_name']), 2);
        $first_name = $name_parts[0];
        $last_name = isset($name_parts[1]) ? $name_parts[1] : '';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $data['guest_email'],
                'phone' => $data['guest_phone'],
                'notes' => !empty($data['company_name']) ? 'Company: ' . $data['company_name'] . "\n" . $data['special_requests'] : $data['special_requests'],
                'is_active' => 1,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s', '%d', '%s')
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    private function validate_step4_booking_data($data) {
        if (empty($data['guest_name'])) {
            return new WP_Error('invalid_name', __('お名前を入力してください。', 'monthly-booking'));
        }
        
        if (empty($data['guest_email']) || !is_email($data['guest_email'])) {
            return new WP_Error('invalid_email', __('有効なメールアドレスを入力してください。', 'monthly-booking'));
        }
        
        if (empty($data['move_in_date']) || empty($data['move_out_date'])) {
            return new WP_Error('invalid_dates', __('入居日と退去日を入力してください。', 'monthly-booking'));
        }
        
        if (empty($data['room_id']) || $data['room_id'] <= 0) {
            return new WP_Error('invalid_room', __('部屋を選択してください。', 'monthly-booking'));
        }
        
        if ($data['num_adults'] < 1 || $data['num_adults'] > 10) {
            return new WP_Error('invalid_adults', __('大人の人数は1〜10名で入力してください。', 'monthly-booking'));
        }
        
        if ($data['num_children'] < 0 || $data['num_children'] > 10) {
            return new WP_Error('invalid_children', __('子どもの人数は0〜10名で入力してください。', 'monthly-booking'));
        }
        
        return true;
    }
    
    private function create_step4_booking($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_bookings';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'room_id' => $data['room_id'],
                'customer_id' => $data['customer_id'],
                'start_date' => $data['move_in_date'],
                'end_date' => $data['move_out_date'],
                'num_adults' => $data['num_adults'],
                'num_children' => $data['num_children'],
                'plan_type' => $data['plan_type'],
                'base_rent' => $data['total_rent'],
                'utilities_fee' => $data['total_utilities'],
                'initial_costs' => $data['initial_costs'],
                'person_additional_fee' => $data['person_additional_fee'],
                'options_total' => $data['options_total'],
                'options_discount' => $data['options_discount'],
                'total_price' => $data['total_price'],
                'discount_amount' => $data['campaign_discount'],
                'final_price' => $data['final_price'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'notes' => $data['special_requests'],
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%s', '%d', '%d', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%s', '%s', '%s')
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    private function save_booking_options($booking_id, $selected_options) {
        global $wpdb;
        
        if (empty($selected_options)) {
            return true;
        }
        
        $options_table = $wpdb->prefix . 'monthly_options';
        $booking_options_table = $wpdb->prefix . 'monthly_booking_options';
        
        foreach ($selected_options as $option_id => $quantity) {
            $option = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $options_table WHERE id = %d AND is_active = 1",
                intval($option_id)
            ));
            
            if ($option) {
                $wpdb->insert(
                    $booking_options_table,
                    array(
                        'booking_id' => $booking_id,
                        'option_id' => $option_id,
                        'quantity' => intval($quantity),
                        'unit_price' => $option->price,
                        'total_price' => $option->price * intval($quantity),
                        'created_at' => current_time('mysql')
                    ),
                    array('%d', '%d', '%d', '%f', '%f', '%s')
                );
            }
        }
        
        return true;
    }
    
    private function send_to_external_accounting_system($booking_id, $data) {
        $external_url = apply_filters('monthly_booking_external_accounting_url', 'https://api.example.com/accounting/bookings');
        
        $payload = array(
            'booking_id' => $booking_id,
            'customer_email' => $data['guest_email'],
            'customer_name' => $data['guest_name'],
            'company_name' => $data['company_name'],
            'room_id' => $data['room_id'],
            'check_in_date' => $data['move_in_date'],
            'check_out_date' => $data['move_out_date'],
            'plan_type' => $data['plan_type'],
            'num_adults' => $data['num_adults'],
            'num_children' => $data['num_children'],
            'pricing_breakdown' => array(
                'daily_rent' => $data['daily_rent'],
                'total_rent' => $data['total_rent'],
                'utilities_fee' => $data['total_utilities'],
                'initial_costs' => $data['initial_costs'],
                'person_additional_fee' => $data['person_additional_fee'],
                'options_total' => $data['options_total'],
                'options_discount' => $data['options_discount'],
                'campaign_discount' => $data['campaign_discount'],
                'final_price' => $data['final_price']
            ),
            'selected_options' => $data['selected_options'],
            'special_requests' => $data['special_requests'],
            'created_at' => current_time('mysql')
        );
        
        $response = wp_remote_post($external_url, array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'User-Agent' => 'Monthly-Booking-Plugin/1.5.5'
            ),
            'body' => json_encode($payload)
        ));
        
        if (is_wp_error($response)) {
            error_log('Monthly Booking: External accounting system error - ' . $response->get_error_message());
            return array('success' => false, 'error' => $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        error_log('Monthly Booking: External accounting system response - Code: ' . $response_code . ', Body: ' . $response_body);
        
        return array(
            'success' => $response_code >= 200 && $response_code < 300,
            'response_code' => $response_code,
            'response_body' => $response_body
        );
    }
    
    /**
     * Calculate estimate via AJAX
     */
    public function ajax_calculate_estimate() {
        check_ajax_referer('monthly_booking_nonce', 'nonce');
        
        $room_id = intval($_POST['room_id']);
        $move_in_date = sanitize_text_field($_POST['move_in_date']);
        $move_out_date = sanitize_text_field($_POST['move_out_date']);
        $stay_months = intval($_POST['stay_months']);
        $num_adults = isset($_POST['num_adults']) ? intval($_POST['num_adults']) : 1;
        $num_children = isset($_POST['num_children']) ? intval($_POST['num_children']) : 0;
        $selected_options = isset($_POST['selected_options']) ? $_POST['selected_options'] : array();
        $guest_name = sanitize_text_field($_POST['guest_name']);
        $company_name = sanitize_text_field($_POST['company_name']);
        $guest_email = sanitize_email($_POST['guest_email']);
        $guest_phone = sanitize_text_field($_POST['guest_phone']);
        $special_requests = sanitize_textarea_field($_POST['special_requests']);
        
        $stay_days = $this->calculate_stay_days($move_in_date, $move_out_date);
        $plan = $this->determine_plan_by_days($stay_days);
        
        if (!$plan) {
            wp_send_json_error(__('滞在期間は最低7日間必要です。', 'monthly-booking'));
        }
        
        if (empty($room_id) || empty($move_in_date) || empty($move_out_date) || empty($stay_months)) {
            wp_send_json_error(__('必須項目をすべて入力してください。', 'monthly-booking'));
        }
        
        if (empty($guest_name) || empty($guest_email)) {
            wp_send_json_error(__('お名前とメールアドレスを入力してください。', 'monthly-booking'));
        }
        
        if (!is_email($guest_email)) {
            wp_send_json_error(__('Please enter a valid email address.', 'monthly-booking'));
        }
        
        if ($num_adults < 1 || $num_adults > 10) {
            wp_send_json_error(__('Number of adults must be between 1 and 10.', 'monthly-booking'));
        }
        
        if ($num_children < 0 || $num_children > 10) {
            wp_send_json_error(__('Number of children must be between 0 and 10.', 'monthly-booking'));
        }
        
        $estimate_data = $this->calculate_plan_estimate($plan, $move_in_date, $stay_months, $num_adults, $num_children, $selected_options, $room_id);
        
        $estimate_data['guest_info'] = array(
            'name' => $guest_name,
            'company' => $company_name,
            'email' => $guest_email
        );
        
        wp_send_json_success($estimate_data);
    }
    
    /**
     * Calculate estimate based on plan and duration with person count and options
     */
    public function calculate_plan_estimate($plan, $move_in_date, $stay_months, $num_adults = 1, $num_children = 0, $selected_options = array(), $room_id = null) {
        global $wpdb;
        
        $move_out_date = $this->calculate_move_out_date($move_in_date, $stay_months);
        $stay_days = $this->calculate_stay_days($move_in_date, $move_out_date);
        
        $room_info = $this->get_room_info_for_estimate($room_id);
        $daily_rent = $room_info ? $room_info->daily_rent : $this->get_default_daily_rent($plan);
        
        
        $total_rent = $daily_rent * $stay_days;
        
        $daily_utilities = ($plan === 'SS') ? 2500 : 2000;
        $total_utilities = $daily_utilities * $stay_days;
        
        $cleaning_fee = 38500;  // 清掃費
        $key_fee = 11000;       // 鍵手数料
        $bedding_fee = 11000;   // 布団代
        $initial_costs = $cleaning_fee + $key_fee + $bedding_fee;
        
        $adult_additional_fee = max(0, ($num_adults - 1)) * 1000 * $stay_days;
        $children_additional_fee = $num_children * 500 * $stay_days;
        $person_additional_fee = $adult_additional_fee + $children_additional_fee;
        
        // 5. Options calculation with bundle discounts
        $options_data = $this->calculate_options_total($selected_options);
        $options_total = $options_data['total'];
        $options_discount = $options_data['discount'];
        $options_final = $options_total - $options_discount;
        
        $subtotal = $total_rent + $total_utilities + $initial_costs + $person_additional_fee + $options_final;
        
        // 6. Campaign discounts (早割・即入居割)
        $campaign_data = $this->calculate_step3_campaign_discount($move_in_date, $move_out_date, $subtotal);
        
        $final_total = $subtotal - $campaign_data['discount_amount'];
        
        return array(
            'plan' => $plan,
            'plan_name' => $this->get_plan_name($plan),
            'move_in_date' => $move_in_date,
            'move_out_date' => $move_out_date,
            'stay_months' => $stay_months,
            'stay_days' => $stay_days,
            'num_adults' => $num_adults,
            'num_children' => $num_children,
            
            'daily_rent' => $daily_rent,
            'total_rent' => $total_rent,
            'daily_utilities' => $daily_utilities,
            'total_utilities' => $total_utilities,
            
            'cleaning_fee' => $cleaning_fee,
            'key_fee' => $key_fee,
            'bedding_fee' => $bedding_fee,
            'initial_costs' => $initial_costs,
            
            'adult_additional_fee' => $adult_additional_fee,
            'children_additional_fee' => $children_additional_fee,
            'person_additional_fee' => $person_additional_fee,
            
            'options_total' => $options_total,
            'options_discount' => $options_discount,
            'options_final' => $options_final,
            'selected_options' => $options_data['details'],
            'options_discount_eligible_count' => $options_data['discount_eligible_count'],
            
            // Campaign discounts
            'campaign_discount' => $campaign_data['discount_amount'],
            'campaign_details' => $campaign_data['campaigns'],
            'campaign_badge' => isset($campaign_data['campaign_badge']) ? $campaign_data['campaign_badge'] : null,
            'campaign_type' => isset($campaign_data['campaign_type']) ? $campaign_data['campaign_type'] : null,
            
            'subtotal' => $subtotal,
            'final_total' => $final_total,
            'currency' => '¥',
            
            'tax_note' => __('全て税込価格です', 'monthly-booking')
        );
    }
    
    /**
     * Get pricing for specific plan
     */
    private function get_plan_pricing($plan) {
        global $wpdb;
        
        $default_prices = array(
            'SS' => array('base_price' => 2500, 'service_fee' => 2500, 'cleaning_fee' => 38500, 'key_fee' => 11000, 'bedding_fee' => 11000, 'tax_rate' => 10),
            'S'  => array('base_price' => 2000, 'service_fee' => 2000, 'cleaning_fee' => 38500, 'key_fee' => 11000, 'bedding_fee' => 11000, 'tax_rate' => 10),
            'M'  => array('base_price' => 1900, 'service_fee' => 2000, 'cleaning_fee' => 38500, 'key_fee' => 11000, 'bedding_fee' => 11000, 'tax_rate' => 10),
            'L'  => array('base_price' => 1800, 'service_fee' => 2000, 'cleaning_fee' => 38500, 'key_fee' => 11000, 'bedding_fee' => 11000, 'tax_rate' => 10)
        );
        
        $table_name = $wpdb->prefix . 'monthly_rates';
        $rate = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE rate_type = %s 
             AND is_active = 1 
             AND (valid_to IS NULL OR valid_to >= CURDATE())
             ORDER BY valid_from DESC 
             LIMIT 1",
            $plan
        ));
        
        if ($rate) {
            return array(
                'base_price' => floatval($rate->base_price),
                'service_fee' => floatval($rate->service_fee),
                'cleaning_fee' => floatval($rate->cleaning_fee),
                'key_fee' => isset($rate->key_fee) ? floatval($rate->key_fee) : 11000,
                'bedding_fee' => isset($rate->bedding_fee) ? floatval($rate->bedding_fee) : 11000,
                'tax_rate' => floatval($rate->tax_rate)
            );
        }
        
        return isset($default_prices[$plan]) ? $default_prices[$plan] : $default_prices['M'];
    }
    
    /**
     * Automatically determine plan based on stay duration
     */
    private function determine_plan_by_duration($stay_months) {
        $stay_days = $stay_months * 30;
        
        if ($stay_days >= 7 && $stay_days <= 29) {
            return 'SS';
        } elseif ($stay_days >= 30 && $stay_days <= 89) {
            return 'S';
        } elseif ($stay_days >= 90 && $stay_days <= 179) {
            return 'M';
        } elseif ($stay_days >= 180) {
            return 'L';
        } else {
            return null;
        }
    }
    
    /**
     * Determine plan based on exact number of days
     */
    private function determine_plan_by_days($stay_days) {
        if ($stay_days >= 7 && $stay_days <= 29) {
            return 'SS';
        } elseif ($stay_days >= 30 && $stay_days <= 89) {
            return 'S';
        } elseif ($stay_days >= 90 && $stay_days <= 179) {
            return 'M';
        } elseif ($stay_days >= 180) {
            return 'L';
        } else {
            return null;
        }
    }
    
    /**
     * Calculate exact days between two dates
     */
    private function calculate_stay_days($move_in_date, $move_out_date) {
        $check_in = new DateTime($move_in_date);
        $check_out = new DateTime($move_out_date);
        
        if ($check_out <= $check_in) {
            return 0;
        }
        
        $interval = $check_in->diff($check_out);
        return $interval->days;
    }
    
    /**
     * Get plan display name
     */
    private function get_plan_name($plan) {
        $plan_names = array(
            'SS' => __('SS Plan - スーパーショートプラン', 'monthly-booking'),
            'S'  => __('S Plan - ショートプラン', 'monthly-booking'),
            'M'  => __('M Plan - ミドルプラン', 'monthly-booking'),
            'L'  => __('L Plan - ロングプラン', 'monthly-booking')
        );
        
        return isset($plan_names[$plan]) ? $plan_names[$plan] : $plan_names['M'];
    }
    
    /**
     * Calculate move-out date from move-in date and stay months
     */
    private function calculate_move_out_date($move_in_date, $stay_months) {
        $start_date = new DateTime($move_in_date);
        $end_date = clone $start_date;
        $end_date->modify("+{$stay_months} months");
        return $end_date->format('Y-m-d');
    }
    
    /**
     * Get room information for estimate calculation
     */
    private function get_room_info_for_estimate($room_id = null) {
        global $wpdb;
        
        if (!$room_id) {
            $table_name = $wpdb->prefix . 'monthly_rooms';
            return $wpdb->get_row(
                "SELECT * FROM $table_name WHERE is_active = 1 ORDER BY id LIMIT 1"
            );
        }
        
        $table_name = $wpdb->prefix . 'monthly_rooms';
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d AND is_active = 1",
            $room_id
        ));
    }
    
    /**
     * Get default daily rent for plan if no room specified
     */
    private function get_default_daily_rent($plan) {
        $default_rents = array(
            'SS' => 2500,
            'S'  => 2000,
            'M'  => 1900,
            'L'  => 1800
        );
        
        return isset($default_rents[$plan]) ? $default_rents[$plan] : 2000;
    }
    
    /**
     * Calculate Step 3 campaign discounts using new campaign manager
     */
    private function calculate_step3_campaign_discount($move_in_date, $move_out_date, $base_total) {
        if (!class_exists('MonthlyBooking_Campaign_Manager')) {
            require_once plugin_dir_path(__FILE__) . 'campaign-manager.php';
        }
        
        $campaign_manager = new MonthlyBooking_Campaign_Manager();
        $campaign_info = $campaign_manager->calculate_campaign_discount($move_in_date, $base_total, $base_total);
        
        $applied_campaigns = array();
        if ($campaign_info['campaign_name']) {
            $applied_campaigns[] = array(
                'name' => $campaign_info['campaign_name'],
                'description' => $campaign_info['campaign_description'],
                'discount_type' => 'percentage',
                'discount_value' => $campaign_info['campaign_type'] === 'early' ? 10 : 20,
                'discount_amount' => $campaign_info['discount_amount'],
                'badge' => $campaign_info['campaign_badge']
            );
        }
        
        return array(
            'discount_amount' => $campaign_info['discount_amount'],
            'campaigns' => $applied_campaigns,
            'campaign_badge' => $campaign_info['campaign_badge'],
            'campaign_type' => $campaign_info['campaign_type']
        );
    }
    
    /**
     * Calculate campaign discounts for estimate (legacy method)
     */
    private function calculate_estimate_campaign_discount($start_date, $end_date, $base_total) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_campaigns';
        
        $campaigns = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE is_active = 1 
             AND start_date <= %s 
             AND end_date >= %s
             ORDER BY discount_value DESC",
            $start_date,
            $end_date
        ));
        
        $total_discount = 0;
        $applied_campaigns = array();
        
        foreach ($campaigns as $campaign) {
            if ($campaign->discount_type === 'percentage') {
                $discount = ($base_total * $campaign->discount_value) / 100;
                if (isset($campaign->max_discount_amount) && $discount > $campaign->max_discount_amount) {
                    $discount = $campaign->max_discount_amount;
                }
            } else {
                $discount = $campaign->discount_value;
            }
            
            $total_discount += $discount;
            
            $applied_campaigns[] = array(
                'name' => $campaign->campaign_name,
                'description' => $campaign->campaign_description,
                'discount_type' => $campaign->discount_type,
                'discount_value' => $campaign->discount_value,
                'discount_amount' => $discount
            );
        }
        
        $total_discount = min($total_discount, $base_total * 0.5);
        
        return array(
            'discount_amount' => $total_discount,
            'campaigns' => $applied_campaigns
        );
    }
    
    /**
     * Calculate options total with bundle discounts
     */
    private function calculate_options_total($selected_options) {
        global $wpdb;
        
        if (empty($selected_options)) {
            return array(
                'total' => 0,
                'discount' => 0,
                'details' => array()
            );
        }
        
        $table_name = $wpdb->prefix . 'monthly_options';
        $option_ids = array_map('intval', array_keys($selected_options));
        $placeholders = implode(',', array_fill(0, count($option_ids), '%d'));
        
        $options = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id IN ($placeholders) AND is_active = 1",
            ...$option_ids
        ));
        
        $total = 0;
        $discount_eligible_count = 0;
        $option_details = array();
        
        foreach ($options as $option) {
            $quantity = isset($selected_options[$option->id]) ? intval($selected_options[$option->id]) : 1;
            $option_total = $option->price * $quantity;
            $total += $option_total;
            
            if ($option->is_discount_target) {
                $discount_eligible_count += $quantity;
            }
            
            $option_details[] = array(
                'id' => $option->id,
                'name' => $option->option_name,
                'price' => $option->price,
                'quantity' => $quantity,
                'total' => $option_total,
                'is_discount_target' => $option->is_discount_target
            );
        }
        
        $discount = $this->calculate_option_bundle_discount($discount_eligible_count);
        
        return array(
            'total' => $total,
            'discount' => $discount,
            'details' => $option_details,
            'discount_eligible_count' => $discount_eligible_count
        );
    }
    
    /**
     * Calculate option bundle discount based on count
     * 2 options: -¥500
     * 3+ options: -¥300 per additional option (max -¥2,000)
     */
    private function calculate_option_bundle_discount($count) {
        if ($count < 2) {
            return 0;
        }
        
        $discount = 0;
        
        if ($count >= 2) {
            $discount += 500;
        }
        
        if ($count >= 3) {
            $additional_options = $count - 2;
            $additional_discount = $additional_options * 300;
            $discount += $additional_discount;
        }
        
        return min($discount, 2000);
    }
    
    /**
     * Get available options for selection
     */
    public function get_available_options() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_options';
        
        return $wpdb->get_results(
            "SELECT * FROM $table_name WHERE is_active = 1 ORDER BY display_order ASC"
        );
    }
    
    /**
     * AJAX handler for getting available options
     */
    public function ajax_get_options() {
        check_ajax_referer('monthly_booking_nonce', 'nonce');
        
        $options = $this->get_available_options();
        
        wp_send_json_success($options);
    }
    
    /**
     * AJAX handler for property search
     */
    public function ajax_search_properties() {
        check_ajax_referer('monthly_booking_nonce', 'nonce');
        
        $start_date = sanitize_text_field($_POST['start_date']);
        $end_date = sanitize_text_field($_POST['end_date']);
        
        $filters = array();
        if (!empty($_POST['station'])) {
            $filters['station'] = sanitize_text_field($_POST['station']);
        }
        if (!empty($_POST['max_occupants'])) {
            $filters['max_occupants'] = intval($_POST['max_occupants']);
        }
        if (!empty($_POST['max_rent'])) {
            $filters['max_rent'] = intval($_POST['max_rent']);
        }
        if (!empty($_POST['structure'])) {
            $filters['structure'] = sanitize_text_field($_POST['structure']);
        }
        
        $properties = $this->get_available_rooms($start_date, $end_date, $filters);
        
        wp_send_json_success($properties);
    }
    
    /**
     * Get available rooms for booking with enhanced property information
     */
    public function get_available_rooms($start_date, $end_date, $filters = array()) {
        global $wpdb;
        
        $rooms_table = $wpdb->prefix . 'monthly_rooms';
        $bookings_table = $wpdb->prefix . 'monthly_bookings';
        
        $where_conditions = array('r.is_active = 1');
        $where_values = array();
        
        if (!empty($filters['station'])) {
            $where_conditions[] = "(r.station1 LIKE %s OR r.station2 LIKE %s OR r.station3 LIKE %s)";
            $station_filter = '%' . $filters['station'] . '%';
            $where_values[] = $station_filter;
            $where_values[] = $station_filter;
            $where_values[] = $station_filter;
        }
        
        if (!empty($filters['max_occupants'])) {
            $where_conditions[] = "r.max_occupants >= %d";
            $where_values[] = intval($filters['max_occupants']);
        }
        
        if (!empty($filters['max_rent'])) {
            $where_conditions[] = "r.daily_rent <= %d";
            $where_values[] = intval($filters['max_rent']);
        }
        
        if (!empty($filters['structure'])) {
            $where_conditions[] = "r.structure LIKE %s";
            $where_values[] = '%' . $filters['structure'] . '%';
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $sql = "SELECT r.*, 
                CASE 
                    WHEN r.station1 IS NOT NULL THEN CONCAT(r.line1, ' ', r.station1, ' ', r.access1_type, r.access1_time, '分')
                    ELSE ''
                END as access_info1,
                CASE 
                    WHEN r.station2 IS NOT NULL THEN CONCAT(r.line2, ' ', r.station2, ' ', r.access2_type, r.access2_time, '分')
                    ELSE ''
                END as access_info2,
                CASE 
                    WHEN r.station3 IS NOT NULL THEN CONCAT(r.line3, ' ', r.station3, ' ', r.access3_type, r.access3_time, '分')
                    ELSE ''
                END as access_info3
                FROM $rooms_table r 
                WHERE $where_clause
                AND r.id NOT IN (
                    SELECT DISTINCT b.room_id 
                    FROM $bookings_table b 
                    WHERE b.status != 'cancelled' 
                    AND (
                        (b.start_date <= %s AND b.end_date >= %s) OR
                        (b.start_date <= %s AND b.end_date >= %s) OR
                        (b.start_date >= %s AND b.end_date <= %s)
                    )
                )
                ORDER BY r.property_id, r.room_name";
        
        $all_values = array_merge($where_values, array($start_date, $start_date, $end_date, $end_date, $start_date, $end_date));
        
        return $wpdb->get_results($wpdb->prepare($sql, ...$all_values));
    }
    
    /**
     * Get property search filters for frontend
     */
    public function get_property_search_filters() {
        global $wpdb;
        
        $rooms_table = $wpdb->prefix . 'monthly_rooms';
        
        $stations = $wpdb->get_col("
            SELECT DISTINCT station1 FROM $rooms_table WHERE station1 IS NOT NULL AND station1 != ''
            UNION
            SELECT DISTINCT station2 FROM $rooms_table WHERE station2 IS NOT NULL AND station2 != ''
            UNION
            SELECT DISTINCT station3 FROM $rooms_table WHERE station3 IS NOT NULL AND station3 != ''
            ORDER BY station1
        ");
        
        $structures = $wpdb->get_col("
            SELECT DISTINCT structure FROM $rooms_table 
            WHERE structure IS NOT NULL AND structure != '' AND is_active = 1
            ORDER BY structure
        ");
        
        $rent_ranges = array(
            array('label' => '¥2,000以下', 'max' => 2000),
            array('label' => '¥2,001-¥3,000', 'min' => 2001, 'max' => 3000),
            array('label' => '¥3,001-¥4,000', 'min' => 3001, 'max' => 4000),
            array('label' => '¥4,001以上', 'min' => 4001)
        );
        
        return array(
            'stations' => $stations,
            'structures' => $structures,
            'rent_ranges' => $rent_ranges,
            'occupancy_options' => range(1, 10)
        );
    }
    
    /**
     * AJAX handler for getting search filters
     */
    public function ajax_get_search_filters() {
        check_ajax_referer('monthly_booking_nonce', 'nonce');
        
        $filters = $this->get_property_search_filters();
        
        wp_send_json_success($filters);
    }
}
