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
        
        add_action('wp_ajax_save_campaign_assignment', array($this, 'ajax_save_campaign_assignment'));
        add_action('wp_ajax_delete_campaign_assignment', array($this, 'ajax_delete_campaign_assignment'));
        add_action('wp_ajax_check_campaign_period_overlap', array($this, 'ajax_check_campaign_period_overlap'));
        add_action('wp_ajax_get_room_campaign_assignments', array($this, 'ajax_get_room_campaign_assignments'));
        add_action('wp_ajax_get_active_campaigns', array($this, 'ajax_get_active_campaigns'));
        add_action('wp_ajax_get_campaign_assignment', array($this, 'ajax_get_campaign_assignment'));
        add_action('wp_ajax_toggle_assignment_status', array($this, 'ajax_toggle_assignment_status'));
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
        
        global $wpdb;
        $campaigns_table = $wpdb->prefix . 'monthly_campaigns';
        $existing_campaign = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $campaigns_table WHERE name = %s AND id != %d",
            $data['name'],
            isset($data['campaign_id']) ? intval($data['campaign_id']) : 0
        ));
        
        if ($existing_campaign) {
            return new WP_Error('duplicate_name', __('キャンペーン名が既に存在します。別の名前を選択してください。', 'monthly-booking'));
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
        
        $max_date = new DateTime();
        $max_date->add(new DateInterval('P180D'));
        
        if ($end > $max_date) {
            return new WP_Error('invalid_end_date', __('終了日は今日から180日以内に設定してください。', 'monthly-booking'));
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
    public function get_applicable_campaigns($checkin_date, $stay_days = null) {
        if (empty($checkin_date)) {
            return null;
        }
        
        $today = new DateTime();
        $checkin = new DateTime($checkin_date);
        $days_until_checkin = $today->diff($checkin)->days;
        
        if ($days_until_checkin < 0) {
            return null;
        }
        
        $eligible_campaigns = array();
        
        if ($stay_days && $stay_days >= 7 && $stay_days <= 10) {
            $campaign = $this->get_campaign_by_type('flatrate');
            if ($campaign && isset($campaign->id)) {
                $eligible_campaigns[] = array(
                    'id' => $campaign->id,
                    'name' => $campaign->campaign_name,
                    'type' => 'flatrate',
                    'discount_type' => $campaign->discount_type,
                    'discount_value' => $campaign->discount_value,
                    'badge' => 'コミコミ10万円',
                    'description' => '7〜10日滞在で全込み10万円',
                    'days_until_checkin' => $days_until_checkin,
                    'priority' => 999999 // Highest priority for flatrate
                );
            }
        }
        
        if ($days_until_checkin <= 7) {
            $campaign = $this->get_campaign_by_type('immediate');
            if ($campaign && isset($campaign->id)) {
                $eligible_campaigns[] = array(
                    'id' => $campaign->id,
                    'name' => $campaign->campaign_name,
                    'type' => 'immediate',
                    'discount_type' => $campaign->discount_type,
                    'discount_value' => $campaign->discount_value,
                    'badge' => '即入居',
                    'description' => '入居7日以内の即入居キャンペーン',
                    'days_until_checkin' => $days_until_checkin,
                    'priority' => $campaign->discount_value
                );
            }
        }
        
        if ($days_until_checkin >= 30) {
            $campaign = $this->get_campaign_by_type('earlybird');
            if ($campaign && isset($campaign->id)) {
                $eligible_campaigns[] = array(
                    'id' => $campaign->id,
                    'name' => $campaign->campaign_name,
                    'type' => 'earlybird',
                    'discount_type' => $campaign->discount_type,
                    'discount_value' => $campaign->discount_value,
                    'badge' => '早割',
                    'description' => '入居30日以上前の早期予約キャンペーン',
                    'days_until_checkin' => $days_until_checkin,
                    'priority' => $campaign->discount_value
                );
            }
        }
        
        if (!empty($eligible_campaigns)) {
            usort($eligible_campaigns, function($a, $b) {
                return $b['priority'] <=> $a['priority'];
            });
            return array($eligible_campaigns[0]);
        }
        
        return null;
    }
    
    /**
     * Get campaign by type from monthly_campaigns table
     * 
     * @param string $type Campaign type ('early' or 'last_minute')
     * @return object|null Campaign object or null if not found
     */
    /**
     * Get campaign by type (instant or earlybird)
     * 
     * 現在の実装: 説明文マッチングによるキャンペーン判定
     * 将来拡張の余地: type列による判定方式への移行可能
     * 例: 「早割」「即入居」「季節割」「10万円コミコミ割」など
     * 
     * @param string $type キャンペーンタイプ ('instant' または 'earlybird')
     * @return object|null キャンペーンオブジェクト
     */
    private function get_campaign_by_type($type) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_campaigns';
        $today = date('Y-m-d');
        
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE is_active = 1 
             AND start_date <= %s 
             AND end_date >= %s 
             AND type = %s
             ORDER BY discount_value DESC 
             LIMIT 1",
            $today,
            $today,
            $type === 'instant' ? 'immediate' : $type
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
    public function calculate_campaign_discount($checkin_date, $base_total, $total_amount, $stay_days = null) {
        $campaigns = $this->get_applicable_campaigns($checkin_date, $stay_days);
        
        if (!$campaigns) {
            return array(
                'discount_amount' => 0,
                'campaign_name' => null,
                'campaign_badge' => null,
                'campaign_type' => null,
                'campaign_description' => null,
                'discount_type' => null,
                'discount_value' => null
            );
        }
        
        $campaign = $campaigns[0];
        $discount_amount = 0;
        
        if ($campaign['discount_type'] === 'percentage') {
            $discount_amount = $base_total * ($campaign['discount_value'] / 100);
        } elseif ($campaign['discount_type'] === 'fixed') {
            $discount_amount = $campaign['discount_value'];
        } elseif ($campaign['discount_type'] === 'flatrate') {
            $flatrate_price = $campaign['discount_value'];
            $discount_amount = max(0, $total_amount - $flatrate_price);
        }
        
        $discount_amount = min($discount_amount, $total_amount);
        
        return array(
            'discount_amount' => $discount_amount,
            'campaign_name' => $campaign['name'],
            'campaign_badge' => $campaign['badge'],
            'campaign_type' => $campaign['type'],
            'campaign_description' => $campaign['description'],
            'discount_type' => $campaign['discount_type'],
            'discount_value' => $campaign['discount_value'],
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
    
    /**
     * Get best applicable campaign for a specific room and period
     * 
     * @param int $room_id Room ID
     * @param string $checkin_date Check-in date in Y-m-d format
     * @param string $checkout_date Check-out date in Y-m-d format
     * @param float $base_price Base price for discount calculation
     * @return array|null Best campaign object or null if no applicable campaign
     */
    public function get_best_applicable_campaign_for_room($room_id, $checkin_date, $checkout_date, $base_price) {
        if (empty($room_id) || empty($checkin_date) || empty($checkout_date)) {
            return null;
        }
        
        global $wpdb;
        $assignments_table = $wpdb->prefix . 'monthly_room_campaigns';
        $campaigns_table = $wpdb->prefix . 'monthly_campaigns';
        
        $sql = "SELECT c.*, a.start_date as assignment_start, a.end_date as assignment_end
                FROM $assignments_table a 
                INNER JOIN $campaigns_table c ON a.campaign_id = c.id 
                WHERE a.room_id = %d 
                AND a.is_active = 1 
                AND c.is_active = 1
                AND (
                    (%s BETWEEN a.start_date AND a.end_date) OR 
                    (%s BETWEEN a.start_date AND a.end_date) OR 
                    (a.start_date BETWEEN %s AND %s)
                )
                ORDER BY c.type, c.discount_value DESC";
        
        $room_campaigns = $wpdb->get_results($wpdb->prepare(
            $sql, 
            $room_id, 
            $checkin_date, 
            $checkout_date, 
            $checkin_date, 
            $checkout_date
        ));
        
        if (empty($room_campaigns)) {
            return null;
        }
        
        $eligible_campaigns = array();
        $today = strtotime('today midnight');
        $checkin_day = strtotime($checkin_date);
        $days_until_checkin = floor(($checkin_day - $today) / (60 * 60 * 24));
        
        $checkin = new DateTime($checkin_date);
        $checkout = new DateTime($checkout_date);
        $stay_days = $checkin->diff($checkout)->days;
        
        if ($days_until_checkin < 0) {
            return null;
        }
        
        foreach ($room_campaigns as $campaign) {
            $is_eligible = true;
            
            if ($campaign->type === 'flatrate' && ($stay_days < 7 || $stay_days > 10)) {
                $is_eligible = false;
            }
            
            if ($campaign->type === 'immediate' && $days_until_checkin > 7) {
                $is_eligible = false;
            }
            
            if ($campaign->type === 'earlybird' && $days_until_checkin < 30) {
                $is_eligible = false;
            }
            
            if ($is_eligible) {
                $discount_amount = 0;
                if ($campaign->discount_type === 'percentage') {
                    $discount_amount = $base_price * ($campaign->discount_value / 100);
                } elseif ($campaign->discount_type === 'fixed') {
                    $discount_amount = $campaign->discount_value;
                } elseif ($campaign->discount_type === 'flatrate') {
                    $discount_amount = max(0, $base_price - $campaign->discount_value);
                }
                
                $eligible_campaigns[] = array(
                    'id' => $campaign->id,
                    'name' => $campaign->campaign_name,
                    'type' => $campaign->type,
                    'discount_type' => $campaign->discount_type,
                    'discount_value' => $campaign->discount_value,
                    'badge' => $this->get_campaign_badge_by_type($campaign->type),
                    'description' => $campaign->campaign_description,
                    'days_until_checkin' => $days_until_checkin,
                    'priority' => $campaign->type === 'flatrate' ? 999999 : $discount_amount,
                    'discount_amount' => $discount_amount
                );
            }
        }
        
        if (empty($eligible_campaigns)) {
            return null;
        }
        
        usort($eligible_campaigns, function($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });
        
        return array($eligible_campaigns[0]);
    }
    
    /**
     * Calculate stay days between two dates
     */
    private function calculate_stay_days($checkin_date, $checkout_date) {
        $checkin = new DateTime($checkin_date);
        $checkout = new DateTime($checkout_date);
        return $checkin->diff($checkout)->days;
    }
    
    /**
     * Get campaign badge by type
     */
    private function get_campaign_badge_by_type($type) {
        switch ($type) {
            case 'flatrate':
                return 'コミコミ10万円';
            case 'immediate':
                return '即入居';
            case 'earlybird':
                return '早割';
            default:
                return null;
        }
    }
    
    /**
     * Save campaign assignment via AJAX
     */
    public function ajax_save_campaign_assignment() {
        check_ajax_referer('monthly_booking_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'monthly-booking'));
        }
        
        $assignment_id = sanitize_text_field($_POST['assignment_id']);
        $room_id = intval($_POST['room_id']);
        $campaign_id = intval($_POST['campaign_id']);
        $start_date = sanitize_text_field($_POST['start_date']);
        $end_date = sanitize_text_field($_POST['end_date']);
        $is_active = intval($_POST['is_active']);
        
        $validation_result = $this->validate_campaign_assignment($room_id, $campaign_id, $start_date, $end_date, $assignment_id);
        if (is_wp_error($validation_result)) {
            wp_send_json_error($validation_result->get_error_message());
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'monthly_room_campaigns';
        
        $data = array(
            'room_id' => $room_id,
            'campaign_id' => $campaign_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'is_active' => $is_active,
            'updated_at' => current_time('mysql')
        );
        
        if ($assignment_id) {
            $result = $wpdb->update($table_name, $data, array('id' => $assignment_id));
        } else {
            $data['created_at'] = current_time('mysql');
            $result = $wpdb->insert($table_name, $data);
            $assignment_id = $wpdb->insert_id;
        }
        
        if ($result !== false) {
            wp_send_json_success(array('assignment_id' => $assignment_id));
        } else {
            wp_send_json_error(__('Failed to save campaign assignment.', 'monthly-booking'));
        }
    }
    
    /**
     * Delete campaign assignment via AJAX
     */
    public function ajax_delete_campaign_assignment() {
        check_ajax_referer('monthly_booking_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'monthly-booking'));
        }
        
        $assignment_id = intval($_POST['assignment_id']);
        
        if (!$assignment_id) {
            wp_send_json_error(__('Invalid assignment ID.', 'monthly-booking'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'monthly_room_campaigns';
        
        $result = $wpdb->delete($table_name, array('id' => $assignment_id));
        
        if ($result !== false) {
            wp_send_json_success();
        } else {
            wp_send_json_error(__('Failed to delete campaign assignment.', 'monthly-booking'));
        }
    }
    
    /**
     * Check for campaign period overlap via AJAX
     */
    public function ajax_check_campaign_period_overlap() {
        check_ajax_referer('monthly_booking_nonce', 'nonce');
        
        $room_id = intval($_POST['room_id']);
        $start_date = sanitize_text_field($_POST['start_date']);
        $end_date = sanitize_text_field($_POST['end_date']);
        $exclude_assignment_id = intval($_POST['assignment_id']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'monthly_room_campaigns';
        
        $sql = "SELECT COUNT(*) FROM $table_name 
                WHERE room_id = %d 
                AND is_active = 1 
                AND (
                    (%s BETWEEN start_date AND end_date) OR 
                    (%s BETWEEN start_date AND end_date) OR 
                    (start_date BETWEEN %s AND %s)
                )";
        
        $params = array($room_id, $start_date, $end_date, $start_date, $end_date);
        
        if ($exclude_assignment_id) {
            $sql .= " AND id != %d";
            $params[] = $exclude_assignment_id;
        }
        
        $overlap_count = $wpdb->get_var($wpdb->prepare($sql, $params));
        
        if ($overlap_count > 0) {
            wp_send_json_error(__('Period overlaps with existing campaign assignment.', 'monthly-booking'));
        } else {
            wp_send_json_success();
        }
    }
    
    /**
     * Get room campaign assignments via AJAX
     */
    public function ajax_get_room_campaign_assignments() {
        check_ajax_referer('monthly_booking_nonce', 'nonce');
        
        $room_id = intval($_POST['room_id']);
        
        global $wpdb;
        $assignments_table = $wpdb->prefix . 'monthly_room_campaigns';
        $campaigns_table = $wpdb->prefix . 'monthly_campaigns';
        
        $sql = "SELECT a.*, c.campaign_name, c.discount_type, c.discount_value 
                FROM $assignments_table a 
                LEFT JOIN $campaigns_table c ON a.campaign_id = c.id 
                WHERE a.room_id = %d 
                ORDER BY a.start_date DESC";
        
        $assignments = $wpdb->get_results($wpdb->prepare($sql, $room_id));
        
        wp_send_json_success($assignments);
    }
    
    /**
     * Get active campaigns via AJAX
     */
    public function ajax_get_active_campaigns() {
        check_ajax_referer('monthly_booking_nonce', 'nonce');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'monthly_campaigns';
        
        $campaigns = $wpdb->get_results(
            "SELECT id, campaign_name, discount_type, discount_value 
             FROM $table_name 
             WHERE is_active = 1 
             ORDER BY campaign_name"
        );
        
        wp_send_json_success($campaigns);
    }
    
    /**
     * Get single campaign assignment via AJAX
     */
    public function ajax_get_campaign_assignment() {
        check_ajax_referer('monthly_booking_nonce', 'nonce');
        
        $assignment_id = intval($_POST['assignment_id']);
        
        if (!$assignment_id) {
            wp_send_json_error(__('Invalid assignment ID.', 'monthly-booking'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'monthly_room_campaigns';
        
        $assignment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d", 
            $assignment_id
        ));
        
        if ($assignment) {
            wp_send_json_success($assignment);
        } else {
            wp_send_json_error(__('Assignment not found.', 'monthly-booking'));
        }
    }
    
    /**
     * Toggle assignment status via AJAX
     */
    public function ajax_toggle_assignment_status() {
        check_ajax_referer('monthly_booking_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'monthly-booking'));
        }
        
        $assignment_id = intval($_POST['assignment_id']);
        $is_active = intval($_POST['is_active']);
        
        if (!$assignment_id) {
            wp_send_json_error(__('Invalid assignment ID.', 'monthly-booking'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'monthly_room_campaigns';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'is_active' => $is_active,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $assignment_id)
        );
        
        if ($result !== false) {
            wp_send_json_success();
        } else {
            wp_send_json_error(__('Failed to toggle assignment status.', 'monthly-booking'));
        }
    }
    
    /**
     * Validate campaign assignment data
     */
    private function validate_campaign_assignment($room_id, $campaign_id, $start_date, $end_date, $assignment_id = null) {
        if (!$room_id || !$campaign_id || !$start_date || !$end_date) {
            return new WP_Error('missing_fields', __('All fields are required.', 'monthly-booking'));
        }
        
        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);
        
        if (!$start_timestamp || !$end_timestamp) {
            return new WP_Error('invalid_date', __('Invalid date format.', 'monthly-booking'));
        }
        
        if ($start_timestamp >= $end_timestamp) {
            return new WP_Error('invalid_range', __('End date must be after start date.', 'monthly-booking'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'monthly_room_campaigns';
        
        $sql = "SELECT COUNT(*) FROM $table_name 
                WHERE room_id = %d 
                AND is_active = 1 
                AND (
                    (%s BETWEEN start_date AND end_date) OR 
                    (%s BETWEEN start_date AND end_date) OR 
                    (start_date BETWEEN %s AND %s)
                )";
        
        $params = array($room_id, $start_date, $end_date, $start_date, $end_date);
        
        if ($assignment_id) {
            $sql .= " AND id != %d";
            $params[] = $assignment_id;
        }
        
        $overlap_count = $wpdb->get_var($wpdb->prepare($sql, $params));
        
        if ($overlap_count > 0) {
            return new WP_Error('period_overlap', __('Period overlaps with existing campaign assignment.', 'monthly-booking'));
        }
        
        $rooms_table = $wpdb->prefix . 'monthly_rooms';
        $campaigns_table = $wpdb->prefix . 'monthly_campaigns';
        
        $room_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $rooms_table WHERE room_id = %d", 
            $room_id
        ));
        
        $campaign_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $campaigns_table WHERE id = %d AND is_active = 1", 
            $campaign_id
        ));
        
        if (!$room_exists) {
            return new WP_Error('invalid_room', __('Room not found.', 'monthly-booking'));
        }
        
        if (!$campaign_exists) {
            return new WP_Error('invalid_campaign', __('Campaign not found or inactive.', 'monthly-booking'));
        }
        
        return true;
    }
}
