<?php
/**
 * Admin UI functionality for Monthly Room Booking plugin
 * 
 * @package MonthlyRoomBooking
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
            __('Monthly Room Booking', 'monthly-booking'),      // Page title
            __('Monthly Room Booking', 'monthly-booking'),      // Menu title
            'manage_options',                                   // Capability
            'monthly-room-booking',                             // Menu slug
            array($this, 'admin_page_property_management'),     // Callback function
            'dashicons-building',                               // Icon
            30                                                  // Position
        );
        
        add_submenu_page(
            'monthly-room-booking',                             // Parent slug
            __('物件管理', 'monthly-booking'),                   // Page title
            __('物件管理', 'monthly-booking'),                   // Menu title
            'manage_options',                                   // Capability
            'monthly-room-booking',                             // Menu slug (same as parent for first submenu)
            array($this, 'admin_page_property_management')      // Callback function
        );
        
        add_submenu_page(
            'monthly-room-booking',
            __('予約カレンダー', 'monthly-booking'),
            __('予約カレンダー', 'monthly-booking'),
            'manage_options',
            'monthly-room-booking-calendar',
            array($this, 'admin_page_booking_calendar')
        );
        
        add_submenu_page(
            'monthly-room-booking',
            __('予約登録', 'monthly-booking'),
            __('予約登録', 'monthly-booking'),
            'manage_options',
            'monthly-room-booking-registration',
            array($this, 'admin_page_booking_registration')
        );
        
        add_submenu_page(
            'monthly-room-booking',
            __('売上サマリー', 'monthly-booking'),
            __('売上サマリー', 'monthly-booking'),
            'manage_options',
            'monthly-room-booking-sales',
            array($this, 'admin_page_sales_summary')
        );
        
        add_submenu_page(
            'monthly-room-booking',
            __('キャンペーン設定', 'monthly-booking'),
            __('キャンペーン設定', 'monthly-booking'),
            'manage_options',
            'monthly-room-booking-campaigns',
            array($this, 'admin_page_campaign_settings')
        );
        
        add_submenu_page(
            'monthly-room-booking',
            __('プラグイン設定', 'monthly-booking'),
            __('プラグイン設定', 'monthly-booking'),
            'manage_options',
            'monthly-room-booking-settings',
            array($this, 'admin_page_plugin_settings')
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
     * Admin page: 物件管理 (Property Management)
     */
    public function admin_page_property_management() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'monthly-booking'));
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="monthly-booking-admin-content">
                <h2><?php _e('物件管理', 'monthly-booking'); ?></h2>
                <p><?php _e('物件の登録・編集・削除機能をここに実装します。', 'monthly-booking'); ?></p>
                
                <div class="notice notice-info">
                    <p><?php _e('機能実装予定: 物件一覧表示、物件詳細編集、新規物件登録', 'monthly-booking'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Admin page: 予約カレンダー (Booking Calendar)
     */
    public function admin_page_booking_calendar() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'monthly-booking'));
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="monthly-booking-admin-content">
                <h2><?php _e('予約カレンダー', 'monthly-booking'); ?></h2>
                <p><?php _e('月別予約状況をカレンダー形式で表示します。', 'monthly-booking'); ?></p>
                
                <div class="notice notice-info">
                    <p><?php _e('機能実装予定: 月別カレンダー表示、予約状況確認、空室状況表示', 'monthly-booking'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Admin page: 予約登録 (Booking Registration)
     */
    public function admin_page_booking_registration() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'monthly-booking'));
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="monthly-booking-admin-content">
                <h2><?php _e('予約登録', 'monthly-booking'); ?></h2>
                <p><?php _e('新規予約の登録・既存予約の編集を行います。', 'monthly-booking'); ?></p>
                
                <div class="notice notice-info">
                    <p><?php _e('機能実装予定: 予約フォーム、ゲスト情報入力、料金計算、予約確認', 'monthly-booking'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Admin page: 売上サマリー (Sales Summary)
     */
    public function admin_page_sales_summary() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'monthly-booking'));
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="monthly-booking-admin-content">
                <h2><?php _e('売上サマリー', 'monthly-booking'); ?></h2>
                <p><?php _e('月別・年別の売上統計とレポートを表示します。', 'monthly-booking'); ?></p>
                
                <div class="notice notice-info">
                    <p><?php _e('機能実装予定: 売上グラフ、月別統計、年別比較、収益分析', 'monthly-booking'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Admin page: キャンペーン設定 (Campaign Settings)
     */
    public function admin_page_campaign_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'monthly-booking'));
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="monthly-booking-admin-content">
                <h2><?php _e('キャンペーン設定', 'monthly-booking'); ?></h2>
                <p><?php _e('割引キャンペーンの作成・管理を行います。', 'monthly-booking'); ?></p>
                
                <div class="notice notice-info">
                    <p><?php _e('機能実装予定: キャンペーン一覧、新規作成、期間設定、割引率設定', 'monthly-booking'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Admin page: プラグイン設定 (Plugin Settings)
     */
    public function admin_page_plugin_settings() {
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
