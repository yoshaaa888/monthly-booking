<?php
/**
 * Plugin Name: Monthly Room Booking
 * Plugin URI: https://github.com/yoshaaa888/monthly-booking
 * Description: A WordPress plugin for managing monthly room bookings with property management, calendar display, pricing logic, and campaign management.
 * Version: 1.0.0
 * Author: Yoshi
 * License: GPL v2 or later
 * Text Domain: monthly-booking
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MONTHLY_BOOKING_VERSION', '1.0.0');
define('MONTHLY_BOOKING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MONTHLY_BOOKING_PLUGIN_URL', plugin_dir_url(__FILE__));

class MonthlyBooking {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        load_plugin_textdomain('monthly-booking', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        $this->include_files();
        
        if (is_admin()) {
            $this->init_admin();
        }
        
        $this->init_frontend();
    }
    
    private function include_files() {
        require_once MONTHLY_BOOKING_PLUGIN_DIR . 'includes/admin-ui.php';
        require_once MONTHLY_BOOKING_PLUGIN_DIR . 'includes/calendar-render.php';
        require_once MONTHLY_BOOKING_PLUGIN_DIR . 'includes/booking-logic.php';
        require_once MONTHLY_BOOKING_PLUGIN_DIR . 'includes/campaign-manager.php';
    }
    
    private function init_admin() {
        new MonthlyBooking_Admin_UI();
    }
    
    private function init_frontend() {
        new MonthlyBooking_Calendar_Render();
        new MonthlyBooking_Booking_Logic();
        new MonthlyBooking_Campaign_Manager();
    }
    
    public function activate() {
        $this->create_tables();
        
        add_option('monthly_booking_version', MONTHLY_BOOKING_VERSION);
        
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $table_name = $wpdb->prefix . 'monthly_bookings';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            property_id mediumint(9) NOT NULL,
            start_date date NOT NULL,
            end_date date NOT NULL,
            guest_name varchar(100) NOT NULL,
            guest_email varchar(100) NOT NULL,
            total_price decimal(10,2) NOT NULL,
            status varchar(20) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        $table_name = $wpdb->prefix . 'monthly_booking_campaigns';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            discount_type varchar(20) NOT NULL,
            discount_value decimal(10,2) NOT NULL,
            start_date date NOT NULL,
            end_date date NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
}

new MonthlyBooking();
