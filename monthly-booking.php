<?php
/**
 * Plugin Name: Monthly Room Booking
 * Plugin URI: https://github.com/yoshaaa888/monthly-booking
 * Description: A WordPress plugin for managing monthly room bookings with property management, calendar display, pricing logic, and campaign management.
 * Version: 1.4.0
 * Author: Yoshi
 * License: GPL v2 or later
 * Text Domain: monthly-booking
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MONTHLY_BOOKING_VERSION', '1.4.0');
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
        $this->insert_default_options();
        $this->insert_sample_properties();
        
        add_option('monthly_booking_version', MONTHLY_BOOKING_VERSION);
        
        flush_rewrite_rules();
    }
    
    private function insert_default_options() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_options';
        
        $default_options = array(
            array('option_name' => '調理器具セット', 'price' => 6600, 'is_discount_target' => 1, 'display_order' => 1),
            array('option_name' => '食器類', 'price' => 3900, 'is_discount_target' => 1, 'display_order' => 2),
            array('option_name' => '洗剤類', 'price' => 3800, 'is_discount_target' => 1, 'display_order' => 3),
            array('option_name' => 'タオル類', 'price' => 2900, 'is_discount_target' => 1, 'display_order' => 4),
            array('option_name' => 'アメニティ類', 'price' => 3500, 'is_discount_target' => 1, 'display_order' => 5),
            array('option_name' => '寝具カバーセット', 'price' => 4530, 'is_discount_target' => 1, 'display_order' => 6),
            array('option_name' => '毛布', 'price' => 3950, 'is_discount_target' => 1, 'display_order' => 7),
            array('option_name' => 'アイロン', 'price' => 6860, 'is_discount_target' => 0, 'display_order' => 8),
            array('option_name' => '炊飯器', 'price' => 6600, 'is_discount_target' => 0, 'display_order' => 9)
        );
        
        foreach ($default_options as $option) {
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM $table_name WHERE option_name = %s",
                $option['option_name']
            ));
            
            if (!$existing) {
                $wpdb->insert($table_name, $option);
            }
        }
    }
    
    private function insert_sample_properties() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_rooms';
        
        $sample_properties = array(
            array(
                'room_id' => 633,
                'property_id' => 126,
                'mor_g' => 'M',
                'property_name' => 'アバクス立川（A棟）',
                'display_name' => '東都マンスリー立川『ＷｉＦｉ対応・宅配ＢＯＸ有』',
                'room_name' => '1003',
                'min_stay_days' => 1,
                'min_stay_unit' => '日',
                'max_occupants' => 2,
                'address' => '東京都立川市曙町1丁目22-25',
                'layout' => 'マンスリー',
                'floor_area' => 20.0,
                'structure' => '鉄骨鉄筋コンクリート造',
                'built_year' => '3月-90',
                'daily_rent' => 2400,
                'line1' => 'JR中央線',
                'station1' => '立川',
                'access1_type' => '徒歩',
                'access1_time' => 8,
                'line2' => 'JR南武線',
                'station2' => '立川',
                'access2_type' => '徒歩',
                'access2_time' => 8,
                'line3' => '多摩都市モノレール',
                'station3' => '立川北',
                'access3_type' => '徒歩',
                'access3_time' => 6,
                'room_amenities' => 'WiFi対応、宅配BOX有',
                'is_active' => 1
            )
        );
        
        foreach ($sample_properties as $property) {
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM $table_name WHERE room_id = %d",
                $property['room_id']
            ));
            
            if (!$existing) {
                $wpdb->insert($table_name, $property);
            }
        }
        
        $this->insert_sample_campaigns();
    }
    
    private function insert_sample_campaigns() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_campaigns';
        
        $sample_campaigns = array(
            array(
                'campaign_name' => '早割キャンペーン',
                'campaign_description' => '入居30日以上前のご予約で賃料・共益費10%OFF',
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'min_stay_days' => 7,
                'max_discount_amount' => 50000.00,
                'applicable_rooms' => '',
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+365 days')),
                'booking_start_date' => date('Y-m-d', strtotime('+30 days')),
                'booking_end_date' => date('Y-m-d', strtotime('+395 days')),
                'usage_limit' => 100,
                'usage_count' => 0,
                'is_active' => 1
            ),
            array(
                'campaign_name' => '即入居割',
                'campaign_description' => '入居7日以内のご予約で賃料・共益費20%OFF',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'min_stay_days' => 7,
                'max_discount_amount' => 80000.00,
                'applicable_rooms' => '',
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+365 days')),
                'booking_start_date' => date('Y-m-d'),
                'booking_end_date' => date('Y-m-d', strtotime('+7 days')),
                'usage_limit' => 50,
                'usage_count' => 0,
                'is_active' => 1
            )
        );
        
        foreach ($sample_campaigns as $campaign) {
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM $table_name WHERE campaign_name = %s",
                $campaign['campaign_name']
            ));
            
            if (!$existing) {
                $wpdb->insert($table_name, $campaign);
            }
        }
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
            room_id int(11) UNIQUE,
            property_id int(11),
            mor_g char(1) DEFAULT 'M',
            property_name text,
            display_name text,
            room_name varchar(100) NOT NULL,
            room_description text,
            min_stay_days int(11) DEFAULT 1,
            min_stay_unit enum('日', '月') DEFAULT '日',
            max_occupants int(3) DEFAULT 1,
            address text,
            layout varchar(50),
            floor_area decimal(5,1),
            structure varchar(100),
            built_year varchar(20),
            daily_rent int(11),
            line1 varchar(50),
            station1 varchar(50),
            access1_type varchar(10),
            access1_time int(3),
            line2 varchar(50),
            station2 varchar(50),
            access2_type varchar(10),
            access2_time int(3),
            line3 varchar(50),
            station3 varchar(50),
            access3_type varchar(10),
            access3_time int(3),
            room_size decimal(6,2),
            room_amenities text,
            room_images text,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY room_id (room_id),
            KEY property_id (property_id),
            KEY mor_g (mor_g),
            KEY station1 (station1),
            KEY station2 (station2),
            KEY station3 (station3),
            KEY daily_rent (daily_rent),
            KEY is_active (is_active)
        ) $charset_collate;";
        dbDelta($sql);
        
        $table_name = $wpdb->prefix . 'monthly_bookings';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            room_id mediumint(9) NOT NULL,
            customer_id mediumint(9) NOT NULL,
            start_date date NOT NULL,
            end_date date NOT NULL,
            num_adults int(2) DEFAULT 1,
            num_children int(2) DEFAULT 0,
            plan_type varchar(10) DEFAULT 'M',
            base_rent decimal(10,2) NOT NULL,
            utilities_fee decimal(10,2) NOT NULL,
            initial_costs decimal(10,2) NOT NULL,
            person_additional_fee decimal(10,2) DEFAULT 0,
            options_total decimal(10,2) DEFAULT 0,
            options_discount decimal(10,2) DEFAULT 0,
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
            KEY status (status),
            KEY plan_type (plan_type)
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
        
        $table_name = $wpdb->prefix . 'monthly_options';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            option_name varchar(100) NOT NULL,
            option_description text,
            price decimal(10,2) NOT NULL,
            is_discount_target tinyint(1) DEFAULT 1,
            display_order int(3) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY is_active (is_active),
            KEY display_order (display_order)
        ) $charset_collate;";
        dbDelta($sql);
        
        $table_name = $wpdb->prefix . 'monthly_booking_options';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            booking_id mediumint(9) NOT NULL,
            option_id mediumint(9) NOT NULL,
            quantity int(2) DEFAULT 1,
            unit_price decimal(10,2) NOT NULL,
            total_price decimal(10,2) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY booking_id (booking_id),
            KEY option_id (option_id),
            UNIQUE KEY booking_option (booking_id, option_id)
        ) $charset_collate;";
        dbDelta($sql);
    }
}

new MonthlyBooking();
