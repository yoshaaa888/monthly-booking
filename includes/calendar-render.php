<?php
/**
 * Calendar rendering functionality for Monthly Booking plugin
 * 
 * @package MonthlyBooking
 */

if (!defined('ABSPATH')) {
    exit;
}

class MonthlyBooking_Calendar_Render {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_shortcode('monthly_booking_calendar', array($this, 'render_calendar_shortcode'));
        add_shortcode('monthly_booking_estimate', array($this, 'render_estimate_shortcode'));
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style(
            'monthly-booking-calendar',
            MONTHLY_BOOKING_PLUGIN_URL . 'assets/calendar.css',
            array(),
            MONTHLY_BOOKING_VERSION
        );
        
        wp_enqueue_script(
            'monthly-booking-calendar',
            MONTHLY_BOOKING_PLUGIN_URL . 'assets/calendar.js',
            array('jquery'),
            MONTHLY_BOOKING_VERSION,
            true
        );
        
        wp_enqueue_script(
            'monthly-booking-estimate',
            MONTHLY_BOOKING_PLUGIN_URL . 'assets/estimate.js',
            array('jquery'),
            MONTHLY_BOOKING_VERSION,
            true
        );
        
        wp_localize_script('monthly-booking-estimate', 'monthlyBookingAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('monthly_booking_nonce')
        ));
    }
    
    /**
     * Render calendar shortcode
     */
    public function render_calendar_shortcode($atts) {
        $atts = shortcode_atts(array(
            'property_id' => 1,
            'months' => 12
        ), $atts, 'monthly_booking_calendar');
        
        ob_start();
        ?>
        <div class="monthly-booking-calendar" data-property-id="<?php echo esc_attr($atts['property_id']); ?>">
            <div class="calendar-header">
                <h3><?php _e('Availability Calendar', 'monthly-booking'); ?></h3>
            </div>
            <div class="calendar-grid">
                <?php echo $this->render_calendar_months(intval($atts['months'])); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render calendar months
     */
    private function render_calendar_months($months_count) {
        $output = '';
        $current_date = new DateTime();
        
        for ($i = 0; $i < $months_count; $i++) {
            $month_date = clone $current_date;
            $month_date->modify("+{$i} months");
            
            $output .= '<div class="calendar-month">';
            $output .= '<h4>' . $month_date->format('Y年n月') . '</h4>';
            $output .= '<div class="month-grid">';
            $output .= $this->render_month_days($month_date);
            $output .= '</div>';
            $output .= '</div>';
        }
        
        return $output;
    }
    
    /**
     * Render days for a specific month
     */
    private function render_month_days($month_date) {
        $output = '';
        $days_in_month = $month_date->format('t');
        $first_day = clone $month_date;
        $first_day->modify('first day of this month');
        
        for ($day = 1; $day <= $days_in_month; $day++) {
            $current_day = clone $first_day;
            $current_day->modify("+".($day-1)." days");
            
            $is_available = $this->check_availability($current_day);
            $css_class = $is_available ? 'available' : 'unavailable';
            
            $output .= '<div class="calendar-day ' . $css_class . '" data-date="' . $current_day->format('Y-m-d') . '">';
            $output .= '<span class="day-number">' . $day . '</span>';
            $output .= '</div>';
        }
        
        return $output;
    }
    
    /**
     * Check if a date is available for booking
     */
    private function check_availability($date) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_bookings';
        $date_string = $date->format('Y-m-d');
        
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE %s BETWEEN start_date AND end_date AND status != 'cancelled'",
            $date_string
        ));
        
        return empty($booking);
    }
    
    /**
     * Render estimate form shortcode
     */
    public function render_estimate_shortcode($atts) {
        $atts = shortcode_atts(array(
            'default_plan' => 'M'
        ), $atts, 'monthly_booking_estimate');
        
        ob_start();
        ?>
        <div class="monthly-booking-estimate-form">
            <div class="estimate-header">
                <h3><?php _e('Monthly Room Booking Estimate', 'monthly-booking'); ?></h3>
                <p><?php _e('Select your plan and move-in date to get an instant estimate.', 'monthly-booking'); ?></p>
            </div>
            
            <form id="monthly-estimate-form" class="estimate-form">
                <div class="form-section">
                    <h4><?php _e('Plan Selection', 'monthly-booking'); ?></h4>
                    <div class="plan-options">
                        <label class="plan-option">
                            <input type="radio" name="plan" value="SS" <?php checked($atts['default_plan'], 'SS'); ?>>
                            <span class="plan-label">
                                <strong>SS Plan</strong>
                                <small><?php _e('Compact Studio (15-20㎡)', 'monthly-booking'); ?></small>
                            </span>
                        </label>
                        <label class="plan-option">
                            <input type="radio" name="plan" value="S" <?php checked($atts['default_plan'], 'S'); ?>>
                            <span class="plan-label">
                                <strong>S Plan</strong>
                                <small><?php _e('Standard Studio (20-25㎡)', 'monthly-booking'); ?></small>
                            </span>
                        </label>
                        <label class="plan-option">
                            <input type="radio" name="plan" value="M" <?php checked($atts['default_plan'], 'M'); ?>>
                            <span class="plan-label">
                                <strong>M Plan</strong>
                                <small><?php _e('Medium Room (25-35㎡)', 'monthly-booking'); ?></small>
                            </span>
                        </label>
                        <label class="plan-option">
                            <input type="radio" name="plan" value="L" <?php checked($atts['default_plan'], 'L'); ?>>
                            <span class="plan-label">
                                <strong>L Plan</strong>
                                <small><?php _e('Large Room (35㎡+)', 'monthly-booking'); ?></small>
                            </span>
                        </label>
                    </div>
                </div>
                
                <div class="form-section">
                    <h4><?php _e('Move-in Information', 'monthly-booking'); ?></h4>
                    <div class="form-row">
                        <label for="move_in_date"><?php _e('Move-in Date', 'monthly-booking'); ?> <span class="required">*</span></label>
                        <input type="date" id="move_in_date" name="move_in_date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-row">
                        <label for="stay_months"><?php _e('Stay Duration (months)', 'monthly-booking'); ?> <span class="required">*</span></label>
                        <select id="stay_months" name="stay_months" required>
                            <option value=""><?php _e('Select duration', 'monthly-booking'); ?></option>
                            <option value="1">1 <?php _e('month', 'monthly-booking'); ?></option>
                            <option value="2">2 <?php _e('months', 'monthly-booking'); ?></option>
                            <option value="3">3 <?php _e('months', 'monthly-booking'); ?></option>
                            <option value="6">6 <?php _e('months', 'monthly-booking'); ?></option>
                            <option value="12">12 <?php _e('months', 'monthly-booking'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="form-section">
                    <h4><?php _e('Contact Information', 'monthly-booking'); ?></h4>
                    <div class="form-row">
                        <label for="guest_name"><?php _e('Name', 'monthly-booking'); ?> <span class="required">*</span></label>
                        <input type="text" id="guest_name" name="guest_name" required>
                    </div>
                    <div class="form-row">
                        <label for="company_name"><?php _e('Company Name (Optional)', 'monthly-booking'); ?></label>
                        <input type="text" id="company_name" name="company_name">
                    </div>
                    <div class="form-row">
                        <label for="guest_email"><?php _e('Email Address', 'monthly-booking'); ?> <span class="required">*</span></label>
                        <input type="email" id="guest_email" name="guest_email" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" id="calculate-estimate" class="estimate-button">
                        <?php _e('Calculate Estimate', 'monthly-booking'); ?>
                    </button>
                </div>
            </form>
            
            <div id="estimate-result" class="estimate-result" style="display: none;">
                <div class="result-header">
                    <h4><?php _e('Estimate Result', 'monthly-booking'); ?></h4>
                </div>
                <div class="result-content">
                    <div class="estimate-loading" style="display: none;">
                        <p><?php _e('Calculating estimate...', 'monthly-booking'); ?></p>
                    </div>
                    <div class="estimate-details" style="display: none;">
                        <table class="estimate-table">
                            <tr>
                                <td><?php _e('Selected Plan', 'monthly-booking'); ?>:</td>
                                <td class="plan-display"></td>
                            </tr>
                            <tr>
                                <td><?php _e('Move-in Date', 'monthly-booking'); ?>:</td>
                                <td class="date-display"></td>
                            </tr>
                            <tr>
                                <td><?php _e('Stay Duration', 'monthly-booking'); ?>:</td>
                                <td class="duration-display"></td>
                            </tr>
                            <tr class="price-row">
                                <td><?php _e('Monthly Rent', 'monthly-booking'); ?>:</td>
                                <td class="rent-amount"></td>
                            </tr>
                            <tr class="price-row">
                                <td><?php _e('Utilities & Service Fee', 'monthly-booking'); ?>:</td>
                                <td class="utilities-amount"></td>
                            </tr>
                            <tr class="price-row">
                                <td><?php _e('Initial Costs', 'monthly-booking'); ?>:</td>
                                <td class="initial-costs-amount"></td>
                            </tr>
                            <tr class="subtotal-row">
                                <td><?php _e('Subtotal', 'monthly-booking'); ?>:</td>
                                <td class="subtotal-amount"></td>
                            </tr>
                            <tr class="campaign-row" style="display: none;">
                                <td><?php _e('Campaign Discount', 'monthly-booking'); ?>:</td>
                                <td class="campaign-discount"></td>
                            </tr>
                            <tr class="total-row">
                                <td><strong><?php _e('Total Amount', 'monthly-booking'); ?>:</strong></td>
                                <td class="total-amount"></td>
                            </tr>
                        </table>
                        <div class="campaign-details" style="display: none;">
                            <h5><?php _e('Applied Campaigns', 'monthly-booking'); ?></h5>
                            <div class="campaign-list"></div>
                        </div>
                    </div>
                    <div class="estimate-error" style="display: none;">
                        <p class="error-message"></p>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .monthly-booking-estimate-form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fff;
        }
        .estimate-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .form-section:last-of-type {
            border-bottom: none;
        }
        .form-section h4 {
            margin-bottom: 15px;
            color: #333;
        }
        .plan-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .plan-option {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .plan-option:hover {
            border-color: #007cba;
        }
        .plan-option input[type="radio"] {
            margin-right: 10px;
        }
        .plan-option input[type="radio"]:checked + .plan-label {
            color: #007cba;
        }
        .plan-option:has(input[type="radio"]:checked) {
            border-color: #007cba;
            background-color: #f0f8ff;
        }
        .plan-label strong {
            display: block;
            margin-bottom: 5px;
        }
        .plan-label small {
            color: #666;
        }
        .form-row {
            margin-bottom: 15px;
        }
        .form-row label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .required {
            color: #d63638;
        }
        .form-row input,
        .form-row select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-actions {
            text-align: center;
            margin-top: 20px;
        }
        .estimate-button {
            background: #007cba;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .estimate-button:hover {
            background: #005a87;
        }
        .estimate-result {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #f9f9f9;
        }
        .estimate-table {
            width: 100%;
            border-collapse: collapse;
        }
        .estimate-table td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .estimate-table td:last-child {
            text-align: right;
            font-weight: 600;
        }
        .total-row td {
            font-size: 18px;
            padding-top: 15px;
            border-top: 2px solid #007cba;
            border-bottom: none;
        }
        .campaign-details {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        .campaign-details h5 {
            margin-bottom: 10px;
            color: #007cba;
        }
        .error-message {
            color: #d63638;
            font-weight: 600;
        }
        </style>
        <?php
        return ob_get_clean();
    }
}
