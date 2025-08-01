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
        
        wp_localize_script('monthly-booking-calendar', 'monthlyBookingAjax', array(
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
}
