<?php
/**
 * Campaign management functionality for Monthly Booking plugin
 * 
 * @package MonthlyBooking
 */

if (!defined('ABSPATH')) {
    exit;
}

class MonthlyBooking_Campaign_Manager {
    
    public function __construct() {
        add_action('wp_ajax_create_campaign', array($this, 'ajax_create_campaign'));
        add_action('wp_ajax_update_campaign', array($this, 'ajax_update_campaign'));
        add_action('wp_ajax_delete_campaign', array($this, 'ajax_delete_campaign'));
        add_action('wp_ajax_toggle_campaign', array($this, 'ajax_toggle_campaign'));
    }
    
    /**
     * Create new campaign via AJAX
     */
    public function ajax_create_campaign() {
        check_ajax_referer('monthly_booking_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'monthly-booking'));
        }
        
        $campaign_data = array(
            'name' => sanitize_text_field($_POST['name']),
            'discount_type' => sanitize_text_field($_POST['discount_type']),
            'discount_value' => floatval($_POST['discount_value']),
            'start_date' => sanitize_text_field($_POST['start_date']),
            'end_date' => sanitize_text_field($_POST['end_date'])
        );
        
        $validation = $this->validate_campaign_data($campaign_data);
        if (is_wp_error($validation)) {
            wp_send_json_error($validation->get_error_message());
        }
        
        $campaign_id = $this->create_campaign($campaign_data);
        
        if ($campaign_id) {
            wp_send_json_success(array(
                'campaign_id' => $campaign_id,
                'message' => __('Campaign created successfully!', 'monthly-booking')
            ));
        } else {
            wp_send_json_error(__('Failed to create campaign.', 'monthly-booking'));
        }
    }
    
    /**
     * Update existing campaign via AJAX
     */
    public function ajax_update_campaign() {
        check_ajax_referer('monthly_booking_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'monthly-booking'));
        }
        
        $campaign_id = intval($_POST['campaign_id']);
        $campaign_data = array(
            'name' => sanitize_text_field($_POST['name']),
            'discount_type' => sanitize_text_field($_POST['discount_type']),
            'discount_value' => floatval($_POST['discount_value']),
            'start_date' => sanitize_text_field($_POST['start_date']),
            'end_date' => sanitize_text_field($_POST['end_date'])
        );
        
        $validation = $this->validate_campaign_data($campaign_data);
        if (is_wp_error($validation)) {
            wp_send_json_error($validation->get_error_message());
        }
        
        $result = $this->update_campaign($campaign_id, $campaign_data);
        
        if ($result) {
            wp_send_json_success(__('Campaign updated successfully!', 'monthly-booking'));
        } else {
            wp_send_json_error(__('Failed to update campaign.', 'monthly-booking'));
        }
    }
    
    /**
     * Delete campaign via AJAX
     */
    public function ajax_delete_campaign() {
        check_ajax_referer('monthly_booking_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'monthly-booking'));
        }
        
        $campaign_id = intval($_POST['campaign_id']);
        
        $result = $this->delete_campaign($campaign_id);
        
        if ($result) {
            wp_send_json_success(__('Campaign deleted successfully!', 'monthly-booking'));
        } else {
            wp_send_json_error(__('Failed to delete campaign.', 'monthly-booking'));
        }
    }
    
    /**
     * Toggle campaign active status via AJAX
     */
    public function ajax_toggle_campaign() {
        check_ajax_referer('monthly_booking_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'monthly-booking'));
        }
        
        $campaign_id = intval($_POST['campaign_id']);
        $is_active = intval($_POST['is_active']);
        
        $result = $this->toggle_campaign_status($campaign_id, $is_active);
        
        if ($result) {
            $message = $is_active ? __('Campaign activated!', 'monthly-booking') : __('Campaign deactivated!', 'monthly-booking');
            wp_send_json_success($message);
        } else {
            wp_send_json_error(__('Failed to update campaign status.', 'monthly-booking'));
        }
    }
    
    /**
     * Get all campaigns
     */
    public function get_campaigns($active_only = false) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_campaigns';
        
        $where_clause = $active_only ? 'WHERE is_active = 1' : '';
        
        return $wpdb->get_results("SELECT * FROM $table_name $where_clause ORDER BY created_at DESC");
    }
    
    /**
     * Get campaign by ID
     */
    public function get_campaign($campaign_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_campaigns';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $campaign_id
        ));
    }
    
    /**
     * Validate campaign data
     */
    private function validate_campaign_data($data) {
        if (empty($data['name'])) {
            return new WP_Error('invalid_name', __('Campaign name is required.', 'monthly-booking'));
        }
        
        if (!in_array($data['discount_type'], array('percentage', 'fixed'))) {
            return new WP_Error('invalid_discount_type', __('Invalid discount type.', 'monthly-booking'));
        }
        
        if ($data['discount_value'] <= 0) {
            return new WP_Error('invalid_discount_value', __('Discount value must be greater than 0.', 'monthly-booking'));
        }
        
        if ($data['discount_type'] === 'percentage' && $data['discount_value'] > 100) {
            return new WP_Error('invalid_percentage', __('Percentage discount cannot exceed 100%.', 'monthly-booking'));
        }
        
        if (empty($data['start_date']) || empty($data['end_date'])) {
            return new WP_Error('invalid_dates', __('Start and end dates are required.', 'monthly-booking'));
        }
        
        $start = new DateTime($data['start_date']);
        $end = new DateTime($data['end_date']);
        
        if ($start >= $end) {
            return new WP_Error('invalid_date_range', __('End date must be after start date.', 'monthly-booking'));
        }
        
        return true;
    }
    
    /**
     * Create new campaign
     */
    private function create_campaign($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_campaigns';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => $data['name'],
                'discount_type' => $data['discount_type'],
                'discount_value' => $data['discount_value'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_active' => 1,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%f', '%s', '%s', '%d', '%s')
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Update existing campaign
     */
    private function update_campaign($campaign_id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_campaigns';
        
        return $wpdb->update(
            $table_name,
            array(
                'name' => $data['name'],
                'discount_type' => $data['discount_type'],
                'discount_value' => $data['discount_value'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date']
            ),
            array('id' => $campaign_id),
            array('%s', '%s', '%f', '%s', '%s'),
            array('%d')
        );
    }
    
    /**
     * Delete campaign
     */
    private function delete_campaign($campaign_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_campaigns';
        
        return $wpdb->delete(
            $table_name,
            array('id' => $campaign_id),
            array('%d')
        );
    }
    
    /**
     * Toggle campaign active status
     */
    private function toggle_campaign_status($campaign_id, $is_active) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_campaigns';
        
        return $wpdb->update(
            $table_name,
            array('is_active' => $is_active),
            array('id' => $campaign_id),
            array('%d'),
            array('%d')
        );
    }
    
    /**
     * Get applicable campaigns for a given check-in date
     * Automatically determines early booking and last-minute campaigns
     * 
     * @param string $checkin_date Check-in date in Y-m-d format
     * @return array|null Campaign information or null if no applicable campaign
     */
    public function get_applicable_campaigns($checkin_date) {
        if (empty($checkin_date)) {
            return null;
        }
        
        $today = new DateTime();
        $checkin = new DateTime($checkin_date);
        $days_until_checkin = $today->diff($checkin)->days;
        
        if ($checkin < $today) {
            return null;
        }
        
        $applicable_campaigns = array();
        
        if ($days_until_checkin <= 7) {
            $campaign = $this->get_campaign_by_type('last_minute');
            if ($campaign) {
                $applicable_campaigns[] = array(
                    'id' => $campaign->id,
                    'name' => $campaign->campaign_name,
                    'type' => 'last_minute',
                    'discount_type' => $campaign->discount_type,
                    'discount_value' => $campaign->discount_value,
                    'badge' => '即入居',
                    'description' => '入居7日以内の即入居キャンペーン',
                    'days_until_checkin' => $days_until_checkin
                );
            }
        }
        
        if ($days_until_checkin >= 30) {
            $campaign = $this->get_campaign_by_type('early');
            if ($campaign) {
                $applicable_campaigns[] = array(
                    'id' => $campaign->id,
                    'name' => $campaign->campaign_name,
                    'type' => 'early',
                    'discount_type' => $campaign->discount_type,
                    'discount_value' => $campaign->discount_value,
                    'badge' => '早割',
                    'description' => '入居30日以上前の早期予約キャンペーン',
                    'days_until_checkin' => $days_until_checkin
                );
            }
        }
        
        return !empty($applicable_campaigns) ? $applicable_campaigns : null;
    }
    
    /**
     * Get campaign by type from monthly_campaigns table
     * 
     * @param string $type Campaign type ('early' or 'last_minute')
     * @return object|null Campaign object or null if not found
     */
    private function get_campaign_by_type($type) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_campaigns';
        $today = date('Y-m-d');
        
        $type_conditions = array(
            'early' => "campaign_description LIKE '%早割%' OR campaign_description LIKE '%early%'",
            'last_minute' => "campaign_description LIKE '%即入居%' OR campaign_description LIKE '%last_minute%'"
        );
        
        if (!isset($type_conditions[$type])) {
            return null;
        }
        
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE is_active = 1 
             AND start_date <= %s 
             AND end_date >= %s 
             AND ({$type_conditions[$type]})
             ORDER BY discount_value DESC 
             LIMIT 1",
            $today,
            $today
        );
        
        return $wpdb->get_row($sql);
    }
    
    /**
     * Calculate campaign discount for given parameters
     * 
     * @param string $checkin_date Check-in date
     * @param float $base_total Base total amount (rent + utilities)
     * @param float $total_amount Total booking amount
     * @return array Discount information
     */
    public function calculate_campaign_discount($checkin_date, $base_total, $total_amount) {
        $campaigns = $this->get_applicable_campaigns($checkin_date);
        
        if (!$campaigns) {
            return array(
                'discount_amount' => 0,
                'campaign_name' => null,
                'campaign_badge' => null,
                'campaign_type' => null
            );
        }
        
        $campaign = $campaigns[0];
        $discount_amount = 0;
        
        if ($campaign['discount_type'] === 'percentage') {
            $discount_amount = $base_total * ($campaign['discount_value'] / 100);
        } elseif ($campaign['discount_type'] === 'fixed') {
            $discount_amount = $campaign['discount_value'];
        }
        
        $discount_amount = min($discount_amount, $total_amount);
        
        return array(
            'discount_amount' => $discount_amount,
            'campaign_name' => $campaign['name'],
            'campaign_badge' => $campaign['badge'],
            'campaign_type' => $campaign['type'],
            'campaign_description' => $campaign['description'],
            'days_until_checkin' => $campaign['days_until_checkin']
        );
    }
    
    /**
     * Check if a specific date qualifies for any campaign
     * 
     * @param string $checkin_date Check-in date in Y-m-d format
     * @return bool True if qualifies for campaign, false otherwise
     */
    public function has_applicable_campaign($checkin_date) {
        $campaigns = $this->get_applicable_campaigns($checkin_date);
        return !empty($campaigns);
    }
    
    /**
     * Get campaign badge for calendar display
     * 
     * @param string $checkin_date Check-in date
     * @return string|null Campaign badge or null
     */
    public function get_campaign_badge($checkin_date) {
        $campaigns = $this->get_applicable_campaigns($checkin_date);
        
        if (!$campaigns) {
            return null;
        }
        
        // Return the badge of the first applicable campaign
        return $campaigns[0]['badge'];
    }
}
