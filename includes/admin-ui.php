<?php
/**
 * Admin UI functionality for Monthly Booking plugin
 * 
 * @package MonthlyBooking
 */

if (!defined('ABSPATH')) {
    exit;
}

class MonthlyBooking_Admin_UI {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add admin menu pages using WordPress standards
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Monthly Booking', 'monthly-booking'),           // Page title
            __('Monthly Booking', 'monthly-booking'),           // Menu title
            'manage_options',                                   // Capability
            'monthly-booking',                                  // Menu slug
            array($this, 'admin_page_bookings'),               // Callback function
            'dashicons-calendar-alt',                           // Icon
            30                                                  // Position
        );
        
        add_submenu_page(
            'monthly-booking',                                  // Parent slug
            __('All Bookings', 'monthly-booking'),             // Page title
            __('All Bookings', 'monthly-booking'),             // Menu title
            'manage_options',                                   // Capability
            'monthly-booking',                                  // Menu slug (same as parent for first submenu)
            array($this, 'admin_page_bookings')                // Callback function
        );
        
        add_submenu_page(
            'monthly-booking',
            __('Calendar View', 'monthly-booking'),
            __('Calendar View', 'monthly-booking'),
            'manage_options',
            'monthly-booking-calendar',
            array($this, 'admin_page_calendar')
        );
        
        add_submenu_page(
            'monthly-booking',
            __('Campaigns', 'monthly-booking'),
            __('Campaigns', 'monthly-booking'),
            'manage_options',
            'monthly-booking-campaigns',
            array($this, 'admin_page_campaigns')
        );
        
        add_submenu_page(
            'monthly-booking',
            __('Settings', 'monthly-booking'),
            __('Settings', 'monthly-booking'),
            'manage_options',
            'monthly-booking-settings',
            array($this, 'admin_page_settings')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'monthly-booking') === false) {
            return;
        }
        
        wp_enqueue_style(
            'monthly-booking-admin',
            MONTHLY_BOOKING_PLUGIN_URL . 'assets/admin.css',
            array(),
            MONTHLY_BOOKING_VERSION
        );
        
        wp_enqueue_script(
            'monthly-booking-admin',
            MONTHLY_BOOKING_PLUGIN_URL . 'assets/admin.js',
            array('jquery'),
            MONTHLY_BOOKING_VERSION,
            true
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('monthly_booking_settings', 'monthly_booking_options');
        
        add_settings_section(
            'monthly_booking_general',
            __('General Settings', 'monthly-booking'),
            array($this, 'settings_section_callback'),
            'monthly_booking_settings'
        );
        
        add_settings_field(
            'default_price',
            __('Default Monthly Price', 'monthly-booking'),
            array($this, 'default_price_callback'),
            'monthly_booking_settings',
            'monthly_booking_general'
        );
        
        add_settings_field(
            'cleaning_days',
            __('Cleaning Period (days)', 'monthly-booking'),
            array($this, 'cleaning_days_callback'),
            'monthly_booking_settings',
            'monthly_booking_general'
        );
    }
    
    /**
     * Admin page: All Bookings
     */
    public function admin_page_bookings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'monthly-booking'));
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="monthly-booking-admin-content">
                <h2><?php _e('Recent Bookings', 'monthly-booking'); ?></h2>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('ID', 'monthly-booking'); ?></th>
                            <th><?php _e('Guest Name', 'monthly-booking'); ?></th>
                            <th><?php _e('Email', 'monthly-booking'); ?></th>
                            <th><?php _e('Start Date', 'monthly-booking'); ?></th>
                            <th><?php _e('End Date', 'monthly-booking'); ?></th>
                            <th><?php _e('Total Price', 'monthly-booking'); ?></th>
                            <th><?php _e('Status', 'monthly-booking'); ?></th>
                            <th><?php _e('Actions', 'monthly-booking'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $this->display_bookings_table(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    /**
     * Admin page: Calendar View
     */
    public function admin_page_calendar() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'monthly-booking'));
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="monthly-booking-admin-content">
                <h2><?php _e('Booking Calendar', 'monthly-booking'); ?></h2>
                <p><?php _e('Calendar view will be implemented here.', 'monthly-booking'); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Admin page: Campaigns
     */
    public function admin_page_campaigns() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'monthly-booking'));
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="monthly-booking-admin-content">
                <h2><?php _e('Campaign Management', 'monthly-booking'); ?></h2>
                <p><?php _e('Campaign management interface will be implemented here.', 'monthly-booking'); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Admin page: Settings
     */
    public function admin_page_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'monthly-booking'));
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('monthly_booking_settings');
                do_settings_sections('monthly_booking_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Display bookings table
     */
    private function display_bookings_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_bookings';
        $bookings = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 20");
        
        if (empty($bookings)) {
            echo '<tr><td colspan="8">' . __('No bookings found.', 'monthly-booking') . '</td></tr>';
            return;
        }
        
        foreach ($bookings as $booking) {
            echo '<tr>';
            echo '<td>' . esc_html($booking->id) . '</td>';
            echo '<td>' . esc_html($booking->guest_name) . '</td>';
            echo '<td>' . esc_html($booking->guest_email) . '</td>';
            echo '<td>' . esc_html($booking->start_date) . '</td>';
            echo '<td>' . esc_html($booking->end_date) . '</td>';
            echo '<td>¥' . esc_html(number_format($booking->total_price)) . '</td>';
            echo '<td>' . esc_html(ucfirst($booking->status)) . '</td>';
            echo '<td><a href="#" class="button button-small">' . __('Edit', 'monthly-booking') . '</a></td>';
            echo '</tr>';
        }
    }
    
    /**
     * Settings callbacks
     */
    public function settings_section_callback() {
        echo '<p>' . __('Configure general settings for the Monthly Booking plugin.', 'monthly-booking') . '</p>';
    }
    
    public function default_price_callback() {
        $options = get_option('monthly_booking_options');
        $value = isset($options['default_price']) ? $options['default_price'] : '100000';
        echo '<input type="number" name="monthly_booking_options[default_price]" value="' . esc_attr($value) . '" min="0" step="1000" />';
        echo '<p class="description">' . __('Default monthly rental price in yen.', 'monthly-booking') . '</p>';
    }
    
    public function cleaning_days_callback() {
        $options = get_option('monthly_booking_options');
        $value = isset($options['cleaning_days']) ? $options['cleaning_days'] : '3';
        echo '<input type="number" name="monthly_booking_options[cleaning_days]" value="' . esc_attr($value) . '" min="1" max="7" />';
        echo '<p class="description">' . __('Number of days required for cleaning between bookings.', 'monthly-booking') . '</p>';
    }
}
