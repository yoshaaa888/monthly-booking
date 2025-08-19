<?php
/**
 * Plugin Name: Monthly Room Booking
 * Plugin URI: https://github.com/yoshaaa888/monthly-booking
 * Description: A WordPress plugin for managing monthly room bookings with property management, calendar display, pricing logic, and campaign management.
 * Version: 1.7.0-alpha
 * Author: Yoshi
 * License: GPL v2 or later
 * Text Domain: monthly-booking
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MONTHLY_BOOKING_VERSION', '1.7.0-alpha');
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
        
        $this->init_features();
        $this->include_files();
        $this->register_cpts_and_meta();
        
        if (is_admin()) {
            $this->init_admin();
        }
        
        $this->init_frontend();
        $this->init_ajax_handlers();
    }
    
    private function include_files() {
        require_once MONTHLY_BOOKING_PLUGIN_DIR . 'includes/admin-ui.php';
        require_once MONTHLY_BOOKING_PLUGIN_DIR . 'includes/calendar-render.php';
        require_once MONTHLY_BOOKING_PLUGIN_DIR . 'includes/booking-logic.php';
        require_once MONTHLY_BOOKING_PLUGIN_DIR . 'includes/campaign-manager.php';
        require_once MONTHLY_BOOKING_PLUGIN_DIR . 'includes/calendar-api.php';
        require_once MONTHLY_BOOKING_PLUGIN_DIR . 'includes/calendar-utils.php';
        
        if ($this->is_feature_enabled('reservations_mvp')) {
            require_once MONTHLY_BOOKING_PLUGIN_DIR . 'includes/reservation-service.php';
        }
        require_once MONTHLY_BOOKING_PLUGIN_DIR . 'includes/price-example.php';

    }
    
    private function init_admin() {
        new MonthlyBooking_Admin_UI();
    }
    
    private function init_frontend() {
        $calendar_render = new MonthlyBooking_Calendar_Render();
        
        add_shortcode('monthly_booking_estimate', array($calendar_render, 'render_estimate_shortcode'));
        add_shortcode('monthly_booking_calendar', array($calendar_render, 'render_calendar_shortcode'));
        add_shortcode('monthly_booking_admin', array($calendar_render, 'render_admin_shortcode'));
        
        new MonthlyBooking_Booking_Logic();
        new MonthlyBooking_Campaign_Manager();
    }
    
    private function register_cpts_and_meta() {
        register_post_type('mrb_booking', array(
            'labels' => array(
                'name' => __('Bookings', 'monthly-booking'),
                'singular_name' => __('Booking', 'monthly-booking'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-calendar-alt',
            'show_in_rest' => true,
            'supports' => array(),
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ));
        $booking_meta = array(
            'room_id' => array('type' => 'integer', 'sanitize' => 'absint'),
            'user_id' => array('type' => 'integer', 'sanitize' => 'absint'),
            'guest_name' => array('type' => 'string', 'sanitize' => 'sanitize_text_field'),
            'guest_email' => array('type' => 'string', 'sanitize' => 'sanitize_email'),
            'guest_phone' => array('type' => 'string', 'sanitize' => 'sanitize_text_field'),
            'checkin_date' => array('type' => 'string', 'sanitize' => 'mrb_sanitize_date_ymd'),
            'checkout_date' => array('type' => 'string', 'sanitize' => 'mrb_sanitize_date_ymd'),
            'nights' => array('type' => 'integer', 'sanitize' => 'absint'),
            'guests' => array('type' => 'integer', 'sanitize' => 'absint'),
            'rate_id' => array('type' => 'integer', 'sanitize' => 'absint'),
            'campaign_id' => array('type' => 'integer', 'sanitize' => 'absint'),
            'options_json' => array('type' => 'string', 'sanitize' => 'mrb_sanitize_json_options'),
            'subtotal' => array('type' => 'integer', 'sanitize' => 'absint'),
            'discount' => array('type' => 'integer', 'sanitize' => 'absint'),
            'total' => array('type' => 'integer', 'sanitize' => 'absint'),
            'status' => array('type' => 'string', 'sanitize' => 'mrb_sanitize_status'),
            'notes' => array('type' => 'string', 'sanitize' => 'sanitize_textarea_field'),
        );
        foreach ($booking_meta as $key => $def) {
            register_post_meta('mrb_booking', $key, array(
                'type' => $def['type'],
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => $def['sanitize'],
                'auth_callback' => 'mrb_auth_edit_post',
            ));
        }

        register_post_type('mrb_rate', array(
            'labels' => array(
                'name' => __('Rates', 'monthly-booking'),
                'singular_name' => __('Rate', 'monthly-booking'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-tickets',
            'show_in_rest' => true,
            'supports' => array(),
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ));
        $rate_meta = array(
            'room_id' => array('type' => 'integer', 'sanitize' => 'absint'),
            'plan_code' => array('type' => 'string', 'sanitize' => 'sanitize_key'),
            'name' => array('type' => 'string', 'sanitize' => 'sanitize_text_field'),
            'daily_rate' => array('type' => 'integer', 'sanitize' => 'absint'),
            'min_nights' => array('type' => 'integer', 'sanitize' => 'absint'),
            'max_nights' => array('type' => 'integer', 'sanitize' => 'absint'),
            'weekday_rules_json' => array('type' => 'string', 'sanitize' => 'mrb_sanitize_json_weekday_rules'),
            'seasonal_start' => array('type' => 'string', 'sanitize' => 'mrb_sanitize_date_ymd'),
            'seasonal_end' => array('type' => 'string', 'sanitize' => 'mrb_sanitize_date_ymd'),
            'cleaning_buffer_days' => array('type' => 'integer', 'sanitize' => 'absint'),
            'priority' => array('type' => 'integer', 'sanitize' => 'absint'),
            'is_active' => array('type' => 'integer', 'sanitize' => 'absint'),
        );
        foreach ($rate_meta as $key => $def) {
            register_post_meta('mrb_rate', $key, array(
                'type' => $def['type'],
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => $def['sanitize'],
                'auth_callback' => 'mrb_auth_edit_post',
            ));
        }

        register_post_type('mrb_campaign', array(
            'labels' => array(
                'name' => __('Campaigns', 'monthly-booking'),
                'singular_name' => __('Campaign', 'monthly-booking'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-megaphone',
            'show_in_rest' => true,
            'supports' => array(),
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ));
        $camp_meta = array(
            'type' => array('type' => 'string', 'sanitize' => 'sanitize_text_field'),
            'amount' => array('type' => 'integer', 'sanitize' => 'absint'),
            'start_date' => array('type' => 'string', 'sanitize' => 'mrb_sanitize_date_ymd'),
            'end_date' => array('type' => 'string', 'sanitize' => 'mrb_sanitize_date_ymd'),
            'room_ids_json' => array('type' => 'string', 'sanitize' => 'mrb_sanitize_json_array_ints'),
            'min_stay_days' => array('type' => 'integer', 'sanitize' => 'absint'),
            'booking_lead_days' => array('type' => 'integer', 'sanitize' => 'absint'),
            'flat_rate_nights' => array('type' => 'integer', 'sanitize' => 'absint'),
            'stackable' => array('type' => 'integer', 'sanitize' => 'absint'),
            'priority' => array('type' => 'integer', 'sanitize' => 'absint'),
            'is_active' => array('type' => 'integer', 'sanitize' => 'absint'),
        );
        foreach ($camp_meta as $key => $def) {
            register_post_meta('mrb_campaign', $key, array(
                'type' => $def['type'],
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => $def['sanitize'],
                'auth_callback' => 'mrb_auth_edit_post',
            ));
        }
    }

    
    private function init_features() {
        if (!defined('MB_FEATURE_RESERVATIONS_MVP')) {
            define('MB_FEATURE_RESERVATIONS_MVP', true);
        }
    }
    
    private function is_feature_enabled($feature) {
        return defined('MB_FEATURE_' . strtoupper($feature)) && constant('MB_FEATURE_' . strtoupper($feature));
    }
    
    private function init_ajax_handlers() {
        add_action('wp_ajax_mbp_load_calendar', array($this, 'ajax_load_calendar'));
        add_action('wp_ajax_nopriv_mbp_load_calendar', array($this, 'ajax_load_calendar'));
        add_action('wp_ajax_get_calendar_bookings', array($this, 'ajax_get_calendar_bookings'));
        add_action('wp_ajax_nopriv_get_calendar_bookings', array($this, 'ajax_get_calendar_bookings'));
        add_action('wp_ajax_mbp_get_calendar_bookings', array($this, 'ajax_get_calendar_bookings'));
        add_action('wp_ajax_nopriv_mbp_get_calendar_bookings', array($this, 'ajax_get_calendar_bookings'));
        
        if ($this->is_feature_enabled('reservations_mvp')) {
            add_action('wp_ajax_mbp_reservation_create', array($this, 'ajax_reservation_create'));
            add_action('wp_ajax_mbp_reservation_update', array($this, 'ajax_reservation_update'));
            add_action('wp_ajax_mbp_reservation_delete', array($this, 'ajax_reservation_delete'));
            add_action('wp_ajax_mbp_reservation_list', array($this, 'ajax_reservation_list'));
        }
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
            array(
                'option_name' => '調理器具セット', 
                'option_description' => 'まな板、お玉、フライ返し、包丁、菜箸(2本セット)、片手鍋、フライパン',
                'price' => 6600, 
                'is_discount_target' => 1, 
                'display_order' => 1
            ),
            array(
                'option_name' => '食器類', 
                'option_description' => 'スープ皿、大皿、小皿、茶碗、箸、スプーン、フォーク、コップ2個セット',
                'price' => 3900, 
                'is_discount_target' => 1, 
                'display_order' => 2
            ),
            array(
                'option_name' => '洗剤類', 
                'option_description' => 'トイレットペーパー、ウェットティッシュ、食器洗剤、浴室スポンジ、浴室洗剤、トイレ洗剤、ハンドソープ',
                'price' => 3800, 
                'is_discount_target' => 1, 
                'display_order' => 3
            ),
            array(
                'option_name' => 'タオル類', 
                'option_description' => 'フェイスタオル2枚、バスタオル',
                'price' => 2900, 
                'is_discount_target' => 1, 
                'display_order' => 4
            ),
            array(
                'option_name' => 'アメニティ類', 
                'option_description' => 'シャンプー、リンス、ボディーソープ',
                'price' => 3500, 
                'is_discount_target' => 1, 
                'display_order' => 5
            ),
            array(
                'option_name' => '寝具カバーセット', 
                'option_description' => '敷パット、掛布団、枕、各カバー',
                'price' => 4530, 
                'is_discount_target' => 1, 
                'display_order' => 6
            ),
            array(
                'option_name' => '毛布', 
                'option_description' => '毛布',
                'price' => 3950, 
                'is_discount_target' => 1, 
                'display_order' => 7
            ),
            array(
                'option_name' => 'アイロン', 
                'option_description' => 'アイロン＋アイロン台セット',
                'price' => 6860, 
                'is_discount_target' => 0, 
                'display_order' => 8
            ),
            array(
                'option_name' => '炊飯器（4合炊き）', 
                'option_description' => '炊飯器（4合炊き）※メーカー直送',
                'price' => 6600, 
                'is_discount_target' => 0, 
                'display_order' => 9
            )
        );
        
        foreach ($default_options as $option) {
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM $table_name WHERE option_name = %s",
                $option['option_name']
            ));
            
            if (!$existing) {
                $wpdb->insert($table_name, $option);
            } else {
                $wpdb->update(
                    $table_name,
                    array(
                        'option_description' => $option['option_description'],
                        'price' => $option['price'],
                        'is_discount_target' => $option['is_discount_target'],
                        'display_order' => $option['display_order']
                    ),
                    array('option_name' => $option['option_name'])
                );
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
                'campaign_name' => '早割10%',
                'campaign_description' => '入居30日以上前のご予約で賃料・共益費10%OFF 早割',
                'type' => 'earlybird',
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'min_stay_days' => 7,
                'earlybird_days' => 30,
                'max_discount_amount' => 50000.00,
                'max_discount_days' => 30,
                'tax_type' => 'taxable',
                'target_plan' => 'S,M,L',
                'applicable_rooms' => '',
                'start_date' => '2025-01-01',
                'end_date' => '2099-12-31',
                'booking_start_date' => '2025-01-01',
                'booking_end_date' => '2099-12-31',
                'usage_limit' => 100,
                'usage_count' => 0,
                'is_active' => 1
            ),
            array(
                'campaign_name' => '即入居割20%',
                'campaign_description' => '入居7日以内のご予約で賃料・共益費20%OFF 即入居',
                'type' => 'immediate',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'min_stay_days' => 7,
                'earlybird_days' => 0,
                'max_discount_amount' => 80000.00,
                'max_discount_days' => 30,
                'tax_type' => 'taxable',
                'target_plan' => 'ALL',
                'applicable_rooms' => '',
                'start_date' => '2025-01-01',
                'end_date' => '2099-12-31',
                'booking_start_date' => '2025-01-01',
                'booking_end_date' => '2099-12-31',
                'usage_limit' => 50,
                'usage_count' => 0,
                'is_active' => 1
            ),
            array(
                'campaign_name' => 'コミコミ10万円キャンペーン',
                'campaign_description' => '7〜10日滞在で全込み10万円の特別料金',
                'type' => 'flatrate',
                'discount_type' => 'flatrate',
                'discount_value' => 100000.00,
                'min_stay_days' => 7,
                'earlybird_days' => NULL,
                'max_discount_amount' => 999999.00,
                'max_discount_days' => 10,
                'tax_type' => 'taxable',
                'target_plan' => 'SS,S',
                'applicable_rooms' => '',
                'start_date' => '2025-01-01',
                'end_date' => '2099-12-31',
                'booking_start_date' => '2025-01-01',
                'booking_end_date' => '2099-12-31',
                'usage_limit' => 30,
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
            type varchar(20) DEFAULT NULL,
            discount_type varchar(20) NOT NULL,
            discount_value decimal(10,2) NOT NULL,
            min_stay_days int(3) DEFAULT 1,
            earlybird_days int(3) DEFAULT NULL,
            max_discount_amount decimal(10,2),
            max_discount_days int(3) DEFAULT 30,
            max_stay_days int(3) DEFAULT NULL,
            tax_type varchar(20) DEFAULT 'taxable',
            target_plan varchar(50) DEFAULT 'ALL',
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
            KEY type (type),
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
        
        $table_name = $wpdb->prefix . 'monthly_room_campaigns';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            room_id mediumint(9) NOT NULL,
            campaign_id mediumint(9) NOT NULL,
            start_date date NOT NULL,
            end_date date NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY room_campaign_period (room_id, start_date, end_date),
            KEY room_id (room_id),
            KEY campaign_id (campaign_id),
            KEY start_date (start_date),
            KEY end_date (end_date),
            KEY is_active (is_active)
        ) $charset_collate;";
        dbDelta($sql);
        
        $table_name = $wpdb->prefix . 'monthly_fee_settings';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            setting_key varchar(50) NOT NULL,
            setting_name varchar(100) NOT NULL,
            setting_value decimal(10,2) NOT NULL,
            unit_type enum('fixed', 'daily', 'monthly') NOT NULL DEFAULT 'fixed',
            category varchar(30) NOT NULL,
            description text,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            display_order int(11) NOT NULL DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key),
            KEY category (category),
            KEY display_order (display_order),
            KEY is_active (is_active)
        ) $charset_collate;";
        dbDelta($sql);
        
        if ($this->is_feature_enabled('reservations_mvp')) {
            $table_name = $wpdb->prefix . 'monthly_reservations';
            $sql = "CREATE TABLE $table_name (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                room_id BIGINT UNSIGNED NOT NULL,
                checkin_date DATE NOT NULL,
                checkout_date DATE NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'confirmed',
                guest_name VARCHAR(190) NOT NULL,
                guest_email VARCHAR(190) NULL,
                base_daily_rate INT NULL,
                total_price INT NULL,
                notes TEXT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY idx_room_period (room_id, checkin_date),
                KEY idx_room_period2 (room_id, checkout_date)
            ) $charset_collate;";
            dbDelta($sql);
        }
        
        $this->insert_default_fee_settings();
    }
    
    public function ajax_load_calendar() {
        check_ajax_referer('mbp_calendar_nonce', 'nonce');
        
        $room_id = intval($_POST['room_id']);
        $month = intval($_POST['month']);
        $year = intval($_POST['year']);
        
        if (!$room_id) {
            wp_send_json_error('Invalid room ID');
            return;
        }
        
        if ($month && $year) {
            if (!class_exists('MonthlyBooking_Calendar_API')) {
                require_once plugin_dir_path(__FILE__) . 'includes/calendar-api.php';
            }
            
            $api = new MonthlyBooking_Calendar_API();
            $from = sprintf('%04d-%02d-01', $year, $month);
            $to = date('Y-m-t', strtotime($from));
            
            $bookings = $api->mbp_get_bookings($room_id, $from, $to);
            $campaigns = $api->get_global_campaigns($from, $to);
            
            wp_send_json_success(array(
                'bookings' => $bookings,
                'campaigns' => $campaigns
            ));
            return;
        }
        
        if (!class_exists('MonthlyBooking_Calendar_Render')) {
            require_once plugin_dir_path(__FILE__) . 'includes/calendar-render.php';
        }
        
        $calendar_render = new MonthlyBooking_Calendar_Render();
        $calendar_html = $calendar_render->render_6_month_calendar($room_id);
        
        wp_send_json_success($calendar_html);
    }
    
    public function ajax_reservation_create() {
        check_ajax_referer('mbp_reservations_nonce', '_ajax_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('権限がありません。', 'monthly-booking'));
            return;
        }
        
        if (!$this->is_feature_enabled('reservations_mvp')) {
            wp_send_json_error(__('この機能は無効になっています。', 'monthly-booking'));
            return;
        }
        
        $service = new MonthlyBooking_Reservation_Service();
        $data = array(
            'room_id' => intval($_POST['room_id']),
            'guest_name' => sanitize_text_field($_POST['guest_name']),
            'guest_email' => sanitize_email($_POST['guest_email']),
            'checkin_date' => sanitize_text_field($_POST['checkin_date']),
            'checkout_date' => sanitize_text_field($_POST['checkout_date']),
            'status' => sanitize_text_field($_POST['status']) ?: 'confirmed',
            'notes' => sanitize_textarea_field($_POST['notes'])
        );
        
        $result = $service->create_reservation($data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success(array(
                'message' => __('予約が正常に作成されました。', 'monthly-booking'),
                'reservation_id' => $result
            ));
        }
    }
    
    public function ajax_reservation_update() {
        check_ajax_referer('mbp_reservations_nonce', '_ajax_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('権限がありません。', 'monthly-booking'));
            return;
        }
        
        if (!$this->is_feature_enabled('reservations_mvp')) {
            wp_send_json_error(__('この機能は無効になっています。', 'monthly-booking'));
            return;
        }
        
        $service = new MonthlyBooking_Reservation_Service();
        $reservation_id = intval($_POST['reservation_id']);
        $data = array(
            'room_id' => intval($_POST['room_id']),
            'guest_name' => sanitize_text_field($_POST['guest_name']),
            'guest_email' => sanitize_email($_POST['guest_email']),
            'checkin_date' => sanitize_text_field($_POST['checkin_date']),
            'checkout_date' => sanitize_text_field($_POST['checkout_date']),
            'status' => sanitize_text_field($_POST['status']),
            'notes' => sanitize_textarea_field($_POST['notes'])
        );
        
        $result = $service->update_reservation($reservation_id, $data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success(array(
                'message' => __('予約が正常に更新されました。', 'monthly-booking')
            ));
        }
    }
    
    public function ajax_reservation_delete() {
        check_ajax_referer('mbp_reservations_nonce', '_ajax_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('権限がありません。', 'monthly-booking'));
            return;
        }
        
        if (!$this->is_feature_enabled('reservations_mvp')) {
            wp_send_json_error(__('この機能は無効になっています。', 'monthly-booking'));
            return;
        }
        
        $service = new MonthlyBooking_Reservation_Service();
        $reservation_id = intval($_POST['reservation_id']);
        
        $result = $service->delete_reservation($reservation_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success(array(
                'message' => __('予約が正常に削除されました。', 'monthly-booking')
            ));
        }
    }
    
    public function ajax_reservation_list() {
        check_ajax_referer('mbp_reservations_nonce', '_ajax_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('権限がありません。', 'monthly-booking'));
            return;
        }
        
        if (!$this->is_feature_enabled('reservations_mvp')) {
            wp_send_json_error(__('この機能は無効になっています。', 'monthly-booking'));
            return;
        }
        
        $service = new MonthlyBooking_Reservation_Service();
        $page = intval($_POST['page']) ?: 1;
        $per_page = intval($_POST['per_page']) ?: 20;
        
        $result = $service->get_reservations($page, $per_page);
        
        wp_send_json_success($result);
    }
    public function ajax_get_calendar_bookings() {
        if (!isset($_POST['nonce'])) {
            wp_send_json_error(array('message' => 'Missing nonce'), 400);
        }
        $nonce = $_POST['nonce'];
        if (!wp_verify_nonce($nonce, 'mbp_calendar_nonce') && !wp_verify_nonce($nonce, 'monthly_booking_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce'), 403);
        }

        $month = isset($_POST['month']) ? intval($_POST['month']) : 0;
        $year = isset($_POST['year']) ? intval($_POST['year']) : 0;
        $room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;

        if ($month < 1 || $month > 12 || $year < 1970 || $year > 2100) {
            wp_send_json_error(array('message' => 'Invalid month/year'), 400);
        }

        $from = sprintf('%04d-%02d-01', $year, $month);
        $to_dt = DateTime::createFromFormat('Y-m-d', $from);
        if (!$to_dt) {
            wp_send_json_error(array('message' => 'Invalid date'), 400);
        }
        $to_dt->modify('last day of this month');
        $to = $to_dt->format('Y-m-d');

        if (!class_exists('MonthlyBooking_Calendar_API')) {
            require_once plugin_dir_path(__FILE__) . 'includes/calendar-api.php';
        }
        $api = new MonthlyBooking_Calendar_API();

        $rooms = array();
        $bookings = array();
        $campaign_days = array();

        if ($room_id > 0) {
            $rooms = array_filter($api->mbp_get_rooms(), function ($r) use ($room_id) {
                return intval($r->id) === $room_id;
            });
            $rooms = array_values($rooms);
            $bookings = $api->mbp_get_bookings($room_id, $from, $to);
            $campaign_days = $api->mbp_get_campaign_days($room_id, $from, $to);
        } else {
            $rooms = $api->mbp_get_rooms();
            foreach ($rooms as $r) {
                $r_id = intval($r->id);
                $room_bookings = $api->mbp_get_bookings($r_id, $from, $to);
                if (!empty($room_bookings)) {
                    foreach ($room_bookings as $b) {
                        $b->_room_id = $r_id;
                        $bookings[] = $b;
                    }
                }
            }
            $campaign_days = $api->get_global_campaigns($from, $to);
        }

        wp_send_json_success(array(
            'month' => $month,
            'year' => $year,
            'rooms' => $rooms,
            'bookings' => $bookings,
            'campaignDays' => $campaign_days,
        ));
    }


    
    private function insert_default_fee_settings() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_fee_settings';
        
        $default_fees = array(
            array(
                'setting_key' => 'cleaning_fee',
                'setting_name' => '清掃費',
                'setting_value' => 38500.00,
                'unit_type' => 'fixed',
                'category' => 'basic_fees',
                'description' => 'チェックアウト時の清掃費用',
                'display_order' => 1
            ),
            array(
                'setting_key' => 'key_fee',
                'setting_name' => '鍵手数料',
                'setting_value' => 11000.00,
                'unit_type' => 'fixed',
                'category' => 'basic_fees',
                'description' => '鍵の受け渡し手数料',
                'display_order' => 2
            ),
            array(
                'setting_key' => 'bedding_fee_daily',
                'setting_name' => '布団代（1日あたり）',
                'setting_value' => 1100.00,
                'unit_type' => 'daily',
                'category' => 'basic_fees',
                'description' => '追加布団の1日あたり料金',
                'display_order' => 3
            ),
            array(
                'setting_key' => 'utilities_ss_daily',
                'setting_name' => '光熱費（SSプラン・1日）',
                'setting_value' => 2500.00,
                'unit_type' => 'daily',
                'category' => 'utilities',
                'description' => 'SSプラン滞在時の1日あたり光熱費',
                'display_order' => 4
            ),
            array(
                'setting_key' => 'utilities_other_daily',
                'setting_name' => '光熱費（S/M/Lプラン・1日）',
                'setting_value' => 2000.00,
                'unit_type' => 'daily',
                'category' => 'utilities',
                'description' => 'S/M/Lプラン滞在時の1日あたり光熱費',
                'display_order' => 5
            ),
            array(
                'setting_key' => 'additional_adult_rent',
                'setting_name' => '追加大人・賃料（1日）',
                'setting_value' => 900.00,
                'unit_type' => 'daily',
                'category' => 'person_fees',
                'description' => '追加大人1名あたりの1日賃料',
                'display_order' => 6
            ),

            array(
                'setting_key' => 'additional_adult_utilities',
                'setting_name' => '追加大人・光熱費（1日）',
                'setting_value' => 200.00,
                'unit_type' => 'daily',
                'category' => 'person_fees',
                'description' => '追加大人1名あたりの1日光熱費',
                'display_order' => 7
            ),
            array(
                'setting_key' => 'additional_child_rent',
                'setting_name' => '追加子ども・賃料（1日）',
                'setting_value' => 450.00,
                'unit_type' => 'daily',
                'category' => 'person_fees',
                'description' => '追加子ども1名あたりの1日賃料',
                'display_order' => 8
            ),
            array(
                'setting_key' => 'additional_child_utilities',
                'setting_name' => '追加子ども・光熱費（1日）',
                'setting_value' => 100.00,
                'unit_type' => 'daily',
                'category' => 'person_fees',
                'description' => '追加子ども1名あたりの1日光熱費',
                'display_order' => 9
            ),
            array(
                'setting_key' => 'option_discount_max',
                'setting_name' => 'オプション割引上限額',
                'setting_value' => 2000.00,
                'unit_type' => 'fixed',
                'category' => 'discount_limits',
                'description' => 'オプション割引の最大金額',
                'display_order' => 14
            ),
            array(
                'setting_key' => 'option_discount_base',
                'setting_name' => 'オプション基本割引（2個）',
                'setting_value' => 500.00,
                'unit_type' => 'fixed',
                'category' => 'discount_limits',
                'description' => '2個選択時の基本割引額',
                'display_order' => 15
            ),
            array(
                'setting_key' => 'option_discount_additional',
                'setting_name' => 'オプション追加割引（3個以上）',
                'setting_value' => 300.00,
                'unit_type' => 'fixed',
                'category' => 'discount_limits',
                'description' => '3個以上選択時の追加割引額（1個あたり）',
                'display_order' => 16
            )
        );
        
        foreach ($default_fees as $fee) {
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM $table_name WHERE setting_key = %s",
                $fee['setting_key']
            ));
            
            if (!$existing) {
                $wpdb->insert($table_name, $fee);
            }
        }
    }
}
if (defined('WP_CLI') && WP_CLI) {

    $mb_db_cli = MONTHLY_BOOKING_PLUGIN_DIR . 'includes/cli/class-mb-db-cli.php';
    if (file_exists($mb_db_cli)) {
        require_once $mb_db_cli;
    }
    $mb_cli = MONTHLY_BOOKING_PLUGIN_DIR . 'includes/cli/class-mb-cli.php';
    if (file_exists($mb_cli)) {
        require_once $mb_cli;
    }
}

if (defined('WP_CLI') && WP_CLI) {

    require_once MONTHLY_BOOKING_PLUGIN_DIR . 'includes/cli/class-mb-db-cli.php';
}

new MonthlyBooking();
add_action('monthly_booking_reservation_saved', function($reservation_id, $data, $total_price) {
    if (function_exists('error_log')) {
        error_log('[MBP] reservation_saved id=' . intval($reservation_id) . ' total=' . intval($total_price));
    }
    if (is_admin()) {
        add_action('admin_notices', function() use ($reservation_id, $total_price) {
            echo '&lt;div class="notice notice-info is-dismissible"&gt;&lt;p&gt;' . esc_html__('価格再計算フックが実行されました: ', 'monthly-booking') . 'ID=' . intval($reservation_id) . ' ' . esc_html__('合計=', 'monthly-booking') . esc_html($total_price) . '&lt;/p&gt;&lt;/div&gt;';
        });
    }
}, 10, 3);

// DEV bootstrap (once-only on init)
if ( file_exists(__DIR__.'/mb-dev-bootstrap.php') ) require_once __DIR__.'/mb-dev-bootstrap.php';

// Dev bootstrap (always-on for wp-now)
if ( file_exists(__DIR__."/mb-dev-boot.php") ) require_once __DIR__."/mb-dev-boot.php";
add_filter('manage_edit-mrb_booking_columns', function ($columns) {
    $new = array();
    foreach ($columns as $key => $label) {
        $new[$key] = $label;
        if ($key === 'title') {
            $new['mrb_status'] = __('Status', 'monthly-booking');
            $new['mrb_room'] = __('Room', 'monthly-booking');
            $new['mrb_period'] = __('Period', 'monthly-booking');
            $new['mrb_total'] = __('Total', 'monthly-booking');
        }
    }
    if (!isset($new['mrb_status'])) {
        $new['mrb_status'] = __('Status', 'monthly-booking');
        $new['mrb_room'] = __('Room', 'monthly-booking');
        $new['mrb_period'] = __('Period', 'monthly-booking');
        $new['mrb_total'] = __('Total', 'monthly-booking');
    }
    return $new;
});

add_action('manage_mrb_booking_posts_custom_column', function ($column, $post_id) {
    if ($column === 'mrb_status') {
        echo esc_html(get_post_meta($post_id, 'status', true));
    } elseif ($column === 'mrb_room') {
        $room_id = intval(get_post_meta($post_id, 'room_id', true));
        echo $room_id ? intval($room_id) : '';
    } elseif ($column === 'mrb_period') {
        $ci = get_post_meta($post_id, 'checkin_date', true);
        $co = get_post_meta($post_id, 'checkout_date', true);
        if ($ci || $co) {
            echo esc_html(trim($ci . ' → ' . $co));
        }
    } elseif ($column === 'mrb_total') {
        $total = intval(get_post_meta($post_id, 'total', true));
        echo $total ? number_format_i18n($total) : '0';
    }
}, 10, 2);

function mrb_auth_edit_post($allowed, $meta_key, $post_id, $user_id, $cap, $caps) {
    if (!current_user_can('edit_post', $post_id)) {
        return false;
    }
    return true;
}

function mrb_sanitize_date_ymd($value) {
    $v = is_string($value) ? trim($value) : '';
    if ($v === '') return '';
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return $v;
    return '';
}

function mrb_sanitize_status($value) {
    $v = is_string($value) ? strtolower(trim($value)) : '';
    $allowed = array('pending','confirmed','canceled');
    return in_array($v, $allowed, true) ? $v : 'pending';
}

function mrb_sanitize_json_array_ints($value) {
    if (is_string($value) && $value === '') return '';
    $arr = is_string($value) ? json_decode($value, true) : (is_array($value) ? $value : array());
    if (!is_array($arr)) return '';
    $out = array();
    foreach ($arr as $item) {
        $out[] = absint($item);
    }
    return wp_json_encode(array_values(array_filter($out, function($n){ return is_int($n); })));
}

function mrb_sanitize_json_options($value) {
    if (is_string($value) && $value === '') return '';
    $arr = is_string($value) ? json_decode($value, true) : (is_array($value) ? $value : array());
    if (!is_array($arr)) return '';
    $out = array();
    foreach ($arr as $opt) {
        $key = isset($opt['key']) ? sanitize_key($opt['key']) : '';
        $qty = isset($opt['qty']) ? absint($opt['qty']) : 0;
        $amount = isset($opt['amount']) ? absint($opt['amount']) : 0;
        if ($key !== '' && $qty >= 0) {
            $out[] = array('key'=>$key,'qty'=>$qty,'amount'=>$amount);
        }
    }
    return wp_json_encode($out);
}

function mrb_sanitize_json_weekday_rules($value) {
    if (is_string($value) && $value === '') return '';
    $arr = is_string($value) ? json_decode($value, true) : (is_array($value) ? $value : array());
    if (!is_array($arr)) return '';
    $days = array('mon','tue','wed','thu','fri','sat','sun');
    $out = array();
    foreach ($days as $d) {
        if (isset($arr[$d]) && is_array($arr[$d])) {
            $rate = isset($arr[$d]['rate']) ? absint($arr[$d]['rate']) : null;
            if ($rate !== null) {
                $out[$d] = array('rate' => $rate);
            }
        }
    }
    return wp_json_encode($out);
}
