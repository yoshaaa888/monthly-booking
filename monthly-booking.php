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
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $table_name = $wpdb->prefix . 'monthly_rooms';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            room_name varchar(100) NOT NULL,
            room_description text,
            room_address varchar(255),
            room_capacity int(3) DEFAULT 1,
            room_size decimal(6,2),
            room_amenities text,
            room_images text,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql);
        
        $table_name = $wpdb->prefix . 'monthly_bookings';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            room_id mediumint(9) NOT NULL,
            customer_id mediumint(9) NOT NULL,
            start_date date NOT NULL,
            end_date date NOT NULL,
            total_price decimal(10,2) NOT NULL,
            discount_amount decimal(10,2) DEFAULT 0,
            final_price decimal(10,2) NOT NULL,
            status varchar(20) DEFAULT 'pending',
            payment_status varchar(20) DEFAULT 'unpaid',
            notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY room_id (room_id),
            KEY customer_id (customer_id),
            KEY start_date (start_date),
            KEY end_date (end_date),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql);
        
        $table_name = $wpdb->prefix . 'monthly_rates';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            room_id mediumint(9) NOT NULL,
            rate_type varchar(20) DEFAULT 'monthly',
            base_price decimal(10,2) NOT NULL,
            cleaning_fee decimal(10,2) DEFAULT 0,
            service_fee decimal(10,2) DEFAULT 0,
            tax_rate decimal(5,2) DEFAULT 0,
            currency varchar(3) DEFAULT 'JPY',
            valid_from date NOT NULL,
            valid_to date,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY room_id (room_id),
            KEY rate_type (rate_type),
            KEY valid_from (valid_from),
            KEY valid_to (valid_to)
        ) $charset_collate;";
        dbDelta($sql);
        
        $table_name = $wpdb->prefix . 'monthly_campaigns';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            campaign_name varchar(100) NOT NULL,
            campaign_description text,
            discount_type varchar(20) NOT NULL,
            discount_value decimal(10,2) NOT NULL,
            min_stay_days int(3) DEFAULT 1,
            max_discount_amount decimal(10,2),
            applicable_rooms text,
            start_date date NOT NULL,
            end_date date NOT NULL,
            booking_start_date date,
            booking_end_date date,
            usage_limit int(5),
            usage_count int(5) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY discount_type (discount_type),
            KEY start_date (start_date),
            KEY end_date (end_date),
            KEY is_active (is_active)
        ) $charset_collate;";
        dbDelta($sql);
        
        $table_name = $wpdb->prefix . 'monthly_customers';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            first_name varchar(50) NOT NULL,
            last_name varchar(50) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20),
            address varchar(255),
            city varchar(50),
            postal_code varchar(10),
            country varchar(50) DEFAULT 'Japan',
            date_of_birth date,
            emergency_contact_name varchar(100),
            emergency_contact_phone varchar(20),
            identification_type varchar(20),
            identification_number varchar(50),
            notes text,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY last_name (last_name),
            KEY phone (phone),
            KEY is_active (is_active)
        ) $charset_collate;";
        dbDelta($sql);
    }
}

new MonthlyBooking();
