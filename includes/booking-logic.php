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
        add_action('wp_ajax_submit_booking', array($this, 'ajax_submit_booking'));
        add_action('wp_ajax_nopriv_submit_booking', array($this, 'ajax_submit_booking'));
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
            'property_id' => intval($_POST['property_id']),
            'start_date' => sanitize_text_field($_POST['start_date']),
            'end_date' => sanitize_text_field($_POST['end_date']),
            'guest_name' => sanitize_text_field($_POST['guest_name']),
            'guest_email' => sanitize_email($_POST['guest_email'])
        );
        
        $validation = $this->validate_booking_data($booking_data);
        if (is_wp_error($validation)) {
            wp_send_json_error($validation->get_error_message());
        }
        
        $booking_id = $this->create_booking($booking_data);
        
        if ($booking_id) {
            wp_send_json_success(array(
                'booking_id' => $booking_id,
                'message' => __('Booking submitted successfully!', 'monthly-booking')
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
}
