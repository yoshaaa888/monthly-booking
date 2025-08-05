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
            __('物件マスタ管理', 'monthly-booking'),                   // Page title
            __('物件マスタ管理', 'monthly-booking'),                   // Menu title
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
            __('オプション管理', 'monthly-booking'),
            __('オプション管理', 'monthly-booking'),
            'manage_options',
            'monthly-room-booking-options',
            array($this, 'admin_page_options_management')
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
        error_log("Admin hook: $hook");
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
     * Admin page: 物件マスタ管理 (Property Master Management)
     */
    public function admin_page_property_management() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'monthly-booking'));
        }
        
        global $wpdb;
        
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
        $property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($action === 'edit' && $property_id) {
            $this->render_property_edit_form($property_id);
            return;
        }
        
        if ($action === 'add') {
            $this->render_property_edit_form(0);
            return;
        }
        
        if (isset($_POST['submit_property'])) {
            $this->handle_property_save();
        }
        
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && $property_id) {
            $this->handle_property_delete($property_id);
        }
        
        $table_name = $wpdb->prefix . 'monthly_rooms';
        $properties = $wpdb->get_results("SELECT * FROM $table_name ORDER BY property_id, room_name");
        
        ?>
        <div class="wrap">
            <h1><?php _e('Property Master Management', 'monthly-booking'); ?></h1>
            
            <div class="tablenav top">
                <div class="alignleft actions">
                    <a href="<?php echo admin_url('admin.php?page=monthly-room-booking&action=add'); ?>" class="button button-primary"><?php _e('Add New Property', 'monthly-booking'); ?></a>
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Property ID', 'monthly-booking'); ?></th>
                        <th><?php _e('Room ID', 'monthly-booking'); ?></th>
                        <th><?php _e('Display Name', 'monthly-booking'); ?></th>
                        <th><?php _e('Room', 'monthly-booking'); ?></th>
                        <th><?php _e('Daily Rent', 'monthly-booking'); ?></th>
                        <th><?php _e('Max Occupants', 'monthly-booking'); ?></th>
                        <th><?php _e('Station Access', 'monthly-booking'); ?></th>
                        <th><?php _e('Status', 'monthly-booking'); ?></th>
                        <th><?php _e('Actions', 'monthly-booking'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($properties)): ?>
                        <tr>
                            <td colspan="9"><?php _e('No properties found. Add your first property to get started.', 'monthly-booking'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($properties as $property): ?>
                            <tr>
                                <td><?php echo esc_html($property->property_id); ?></td>
                                <td><?php echo esc_html($property->room_id); ?></td>
                                <td><?php echo esc_html($property->display_name); ?></td>
                                <td><?php echo esc_html($property->room_name); ?></td>
                                <td>¥<?php echo number_format($property->daily_rent); ?></td>
                                <td><?php echo esc_html($property->max_occupants); ?></td>
                                <td>
                                    <?php if ($property->station1): ?>
                                        <?php echo esc_html($property->line1 . ' ' . $property->station1 . ' ' . $property->access1_type . $property->access1_time . '分'); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-<?php echo $property->is_active ? 'active' : 'inactive'; ?>">
                                        <?php echo $property->is_active ? __('Active', 'monthly-booking') : __('Inactive', 'monthly-booking'); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=monthly-room-booking&action=edit&id=' . $property->room_id); ?>" class="button button-small"><?php _e('Edit', 'monthly-booking'); ?></a>
                                    <a href="<?php echo admin_url('admin.php?page=monthly-room-booking&action=delete&id=' . $property->room_id); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php _e('Are you sure you want to delete this property?', 'monthly-booking'); ?>')"><?php _e('Delete', 'monthly-booking'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <style>
        .status-active { color: #46b450; font-weight: bold; }
        .status-inactive { color: #dc3232; font-weight: bold; }
        .form-table th { width: 200px; }
        .form-section { margin-bottom: 30px; padding: 20px; background: #f9f9f9; border-left: 4px solid #0073aa; }
        .form-section h3 { margin-top: 0; color: #0073aa; }
        .station-group { border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; background: #fff; }
        .station-group h4 { margin-top: 0; }
        </style>
        <?php
    }
    
    /**
     * Render property edit form
     */
    private function render_property_edit_form($property_id) {
        global $wpdb;
        
        $property = null;
        if ($property_id) {
            $table_name = $wpdb->prefix . 'monthly_rooms';
            $property = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE room_id = %d", $property_id));
        }
        
        $is_edit = $property_id > 0;
        $page_title = $is_edit ? __('Edit Property', 'monthly-booking') : __('Add New Property', 'monthly-booking');
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html($page_title); ?></h1>
            
            <form method="post" action="<?php echo admin_url('admin.php?page=monthly-room-booking'); ?>">
                <?php wp_nonce_field('monthly_booking_property_save', 'monthly_booking_property_nonce'); ?>
                <input type="hidden" name="property_db_id" value="<?php echo $property_id; ?>">
                
                <div class="form-section">
                    <h3><?php _e('Basic Information', 'monthly-booking'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><label for="property_id"><?php _e('Property ID', 'monthly-booking'); ?></label></th>
                            <td><input type="number" id="property_id" name="property_id" value="<?php echo $property ? esc_attr($property->property_id) : ''; ?>" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="room_id"><?php _e('Room ID', 'monthly-booking'); ?></label></th>
                            <td><input type="number" id="room_id" name="room_id" value="<?php echo $property ? esc_attr($property->room_id) : ''; ?>" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="mor_g"><?php _e('Classification', 'monthly-booking'); ?></label></th>
                            <td>
                                <select id="mor_g" name="mor_g" required>
                                    <option value="M" <?php selected($property ? $property->mor_g : '', 'M'); ?>>M (マンスリー)</option>
                                    <option value="G" <?php selected($property ? $property->mor_g : '', 'G'); ?>>G (ゲストハウス)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="property_name"><?php _e('Property Name (Internal)', 'monthly-booking'); ?></label></th>
                            <td><input type="text" id="property_name" name="property_name" value="<?php echo $property ? esc_attr($property->property_name) : ''; ?>" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="display_name"><?php _e('Display Name', 'monthly-booking'); ?></label></th>
                            <td><input type="text" id="display_name" name="display_name" value="<?php echo $property ? esc_attr($property->display_name) : ''; ?>" class="large-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="room_name"><?php _e('Room Number/Name', 'monthly-booking'); ?></label></th>
                            <td><input type="text" id="room_name" name="room_name" value="<?php echo $property ? esc_attr($property->room_name) : ''; ?>" class="regular-text" required></td>
                        </tr>
                    </table>
                </div>
                
                <div class="form-section">
                    <h3><?php _e('Occupancy & Pricing', 'monthly-booking'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><label for="min_stay_days"><?php _e('Minimum Stay', 'monthly-booking'); ?></label></th>
                            <td>
                                <input type="number" id="min_stay_days" name="min_stay_days" value="<?php echo $property ? esc_attr($property->min_stay_days) : '1'; ?>" class="small-text" min="1">
                                <select name="min_stay_unit">
                                    <option value="日" <?php selected($property ? $property->min_stay_unit : '', '日'); ?>>日</option>
                                    <option value="月" <?php selected($property ? $property->min_stay_unit : '', '月'); ?>>月</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="max_occupants"><?php _e('Maximum Occupants', 'monthly-booking'); ?></label></th>
                            <td><input type="number" id="max_occupants" name="max_occupants" value="<?php echo $property ? esc_attr($property->max_occupants) : '1'; ?>" class="small-text" min="1" max="10" required></td>
                        </tr>
                        <tr>
                            <th><label for="daily_rent"><?php _e('Daily Rent (¥)', 'monthly-booking'); ?></label></th>
                            <td><input type="number" id="daily_rent" name="daily_rent" value="<?php echo $property ? esc_attr($property->daily_rent) : ''; ?>" class="regular-text" min="0" required></td>
                        </tr>
                    </table>
                </div>
                
                <div class="form-section">
                    <h3><?php _e('Location & Property Details', 'monthly-booking'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><label for="address"><?php _e('Address', 'monthly-booking'); ?></label></th>
                            <td><textarea id="address" name="address" class="large-text" rows="3"><?php echo $property ? esc_textarea($property->address) : ''; ?></textarea></td>
                        </tr>
                        <tr>
                            <th><label for="layout"><?php _e('Layout', 'monthly-booking'); ?></label></th>
                            <td><input type="text" id="layout" name="layout" value="<?php echo $property ? esc_attr($property->layout) : ''; ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th><label for="floor_area"><?php _e('Floor Area (㎡)', 'monthly-booking'); ?></label></th>
                            <td><input type="number" id="floor_area" name="floor_area" value="<?php echo $property ? esc_attr($property->floor_area) : ''; ?>" class="regular-text" step="0.1" min="0"></td>
                        </tr>
                        <tr>
                            <th><label for="structure"><?php _e('Building Structure', 'monthly-booking'); ?></label></th>
                            <td><input type="text" id="structure" name="structure" value="<?php echo $property ? esc_attr($property->structure) : ''; ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th><label for="built_year"><?php _e('Built Year', 'monthly-booking'); ?></label></th>
                            <td><input type="text" id="built_year" name="built_year" value="<?php echo $property ? esc_attr($property->built_year) : ''; ?>" class="regular-text"></td>
                        </tr>
                    </table>
                </div>
                
                <div class="form-section">
                    <h3><?php _e('Station Access Information', 'monthly-booking'); ?></h3>
                    
                    <div class="station-group">
                        <h4><?php _e('Station 1', 'monthly-booking'); ?></h4>
                        <table class="form-table">
                            <tr>
                                <th><label for="line1"><?php _e('Line', 'monthly-booking'); ?></label></th>
                                <td><input type="text" id="line1" name="line1" value="<?php echo $property ? esc_attr($property->line1) : ''; ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="station1"><?php _e('Station', 'monthly-booking'); ?></label></th>
                                <td><input type="text" id="station1" name="station1" value="<?php echo $property ? esc_attr($property->station1) : ''; ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="access1_type"><?php _e('Access Type', 'monthly-booking'); ?></label></th>
                                <td>
                                    <select id="access1_type" name="access1_type">
                                        <option value=""><?php _e('Select...', 'monthly-booking'); ?></option>
                                        <option value="徒歩" <?php selected($property ? $property->access1_type : '', '徒歩'); ?>>徒歩</option>
                                        <option value="バス" <?php selected($property ? $property->access1_type : '', 'バス'); ?>>バス</option>
                                        <option value="自転車" <?php selected($property ? $property->access1_type : '', '自転車'); ?>>自転車</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="access1_time"><?php _e('Access Time (minutes)', 'monthly-booking'); ?></label></th>
                                <td><input type="number" id="access1_time" name="access1_time" value="<?php echo $property ? esc_attr($property->access1_time) : ''; ?>" class="small-text" min="0"></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="station-group">
                        <h4><?php _e('Station 2', 'monthly-booking'); ?></h4>
                        <table class="form-table">
                            <tr>
                                <th><label for="line2"><?php _e('Line', 'monthly-booking'); ?></label></th>
                                <td><input type="text" id="line2" name="line2" value="<?php echo $property ? esc_attr($property->line2) : ''; ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="station2"><?php _e('Station', 'monthly-booking'); ?></label></th>
                                <td><input type="text" id="station2" name="station2" value="<?php echo $property ? esc_attr($property->station2) : ''; ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="access2_type"><?php _e('Access Type', 'monthly-booking'); ?></label></th>
                                <td>
                                    <select id="access2_type" name="access2_type">
                                        <option value=""><?php _e('Select...', 'monthly-booking'); ?></option>
                                        <option value="徒歩" <?php selected($property ? $property->access2_type : '', '徒歩'); ?>>徒歩</option>
                                        <option value="バス" <?php selected($property ? $property->access2_type : '', 'バス'); ?>>バス</option>
                                        <option value="自転車" <?php selected($property ? $property->access2_type : '', '自転車'); ?>>自転車</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="access2_time"><?php _e('Access Time (minutes)', 'monthly-booking'); ?></label></th>
                                <td><input type="number" id="access2_time" name="access2_time" value="<?php echo $property ? esc_attr($property->access2_time) : ''; ?>" class="small-text" min="0"></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="station-group">
                        <h4><?php _e('Station 3', 'monthly-booking'); ?></h4>
                        <table class="form-table">
                            <tr>
                                <th><label for="line3"><?php _e('Line', 'monthly-booking'); ?></label></th>
                                <td><input type="text" id="line3" name="line3" value="<?php echo $property ? esc_attr($property->line3) : ''; ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="station3"><?php _e('Station', 'monthly-booking'); ?></label></th>
                                <td><input type="text" id="station3" name="station3" value="<?php echo $property ? esc_attr($property->station3) : ''; ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="access3_type"><?php _e('Access Type', 'monthly-booking'); ?></label></th>
                                <td>
                                    <select id="access3_type" name="access3_type">
                                        <option value=""><?php _e('Select...', 'monthly-booking'); ?></option>
                                        <option value="徒歩" <?php selected($property ? $property->access3_type : '', '徒歩'); ?>>徒歩</option>
                                        <option value="バス" <?php selected($property ? $property->access3_type : '', 'バス'); ?>>バス</option>
                                        <option value="自転車" <?php selected($property ? $property->access3_type : '', '自転車'); ?>>自転車</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="access3_time"><?php _e('Access Time (minutes)', 'monthly-booking'); ?></label></th>
                                <td><input type="number" id="access3_time" name="access3_time" value="<?php echo $property ? esc_attr($property->access3_time) : ''; ?>" class="small-text" min="0"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3><?php _e('Additional Information', 'monthly-booking'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><label for="room_amenities"><?php _e('Room Amenities', 'monthly-booking'); ?></label></th>
                            <td><textarea id="room_amenities" name="room_amenities" class="large-text" rows="3"><?php echo $property ? esc_textarea($property->room_amenities) : ''; ?></textarea></td>
                        </tr>
                        <tr>
                            <th><label for="is_active"><?php _e('Status', 'monthly-booking'); ?></label></th>
                            <td>
                                <label>
                                    <input type="checkbox" id="is_active" name="is_active" value="1" <?php checked($property ? $property->is_active : 1, 1); ?>>
                                    <?php _e('Active', 'monthly-booking'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit_property" class="button-primary" value="<?php echo $is_edit ? __('Update Property', 'monthly-booking') : __('Add Property', 'monthly-booking'); ?>">
                    <a href="<?php echo admin_url('admin.php?page=monthly-room-booking'); ?>" class="button"><?php _e('Cancel', 'monthly-booking'); ?></a>
                </p>
            </form>
        </div>
        <?php
    }
    
    /**
     * Handle property save
     */
    private function handle_property_save() {
        if (!isset($_POST['monthly_booking_property_nonce']) || !wp_verify_nonce($_POST['monthly_booking_property_nonce'], 'monthly_booking_property_save')) {
            wp_die(__('Security check failed.', 'monthly-booking'));
        }
        
        global $wpdb;
        
        $property_db_id = intval($_POST['property_db_id']);
        $table_name = $wpdb->prefix . 'monthly_rooms';
        
        $data = array(
            'property_id' => intval($_POST['property_id']),
            'room_id' => intval($_POST['room_id']),
            'mor_g' => sanitize_text_field($_POST['mor_g']),
            'property_name' => sanitize_text_field($_POST['property_name']),
            'display_name' => sanitize_text_field($_POST['display_name']),
            'room_name' => sanitize_text_field($_POST['room_name']),
            'min_stay_days' => intval($_POST['min_stay_days']),
            'min_stay_unit' => sanitize_text_field($_POST['min_stay_unit']),
            'max_occupants' => intval($_POST['max_occupants']),
            'address' => sanitize_textarea_field($_POST['address']),
            'layout' => sanitize_text_field($_POST['layout']),
            'floor_area' => floatval($_POST['floor_area']),
            'structure' => sanitize_text_field($_POST['structure']),
            'built_year' => sanitize_text_field($_POST['built_year']),
            'daily_rent' => intval($_POST['daily_rent']),
            'line1' => sanitize_text_field($_POST['line1']),
            'station1' => sanitize_text_field($_POST['station1']),
            'access1_type' => sanitize_text_field($_POST['access1_type']),
            'access1_time' => intval($_POST['access1_time']),
            'line2' => sanitize_text_field($_POST['line2']),
            'station2' => sanitize_text_field($_POST['station2']),
            'access2_type' => sanitize_text_field($_POST['access2_type']),
            'access2_time' => intval($_POST['access2_time']),
            'line3' => sanitize_text_field($_POST['line3']),
            'station3' => sanitize_text_field($_POST['station3']),
            'access3_type' => sanitize_text_field($_POST['access3_type']),
            'access3_time' => intval($_POST['access3_time']),
            'room_amenities' => sanitize_textarea_field($_POST['room_amenities']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        );
        
        if ($property_db_id > 0) {
            $result = $wpdb->update($table_name, $data, array('room_id' => $property_db_id));
            $message = __('Property updated successfully.', 'monthly-booking');
        } else {
            $data['created_at'] = current_time('mysql');
            $result = $wpdb->insert($table_name, $data);
            $message = __('Property added successfully.', 'monthly-booking');
        }
        
        if ($result !== false) {
            wp_redirect(admin_url('admin.php?page=monthly-room-booking&message=success'));
            exit;
        } else {
            wp_redirect(admin_url('admin.php?page=monthly-room-booking&message=error'));
            exit;
        }
    }
    
    /**
     * Handle property delete
     */
    private function handle_property_delete($property_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_rooms';
        $result = $wpdb->delete($table_name, array('room_id' => $property_id), array('%d'));
        
        if ($result !== false) {
            wp_redirect(admin_url('admin.php?page=monthly-room-booking&message=deleted'));
            exit;
        } else {
            wp_redirect(admin_url('admin.php?page=monthly-room-booking&message=error'));
            exit;
        }
    }
    
    /**
     * Admin page: 予約カレンダー (Booking Calendar)
     */
    public function admin_page_booking_calendar() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'monthly-booking'));
        }
        
        $selected_room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
        
        error_log("Admin Calendar Debug - Starting room loading process");
        $rooms = $this->get_all_rooms();
        error_log("Admin Calendar Debug - Rooms loaded: " . count($rooms));
        
        if (empty($rooms)) {
            error_log("Admin Calendar Debug - CRITICAL: No rooms found! Checking database connection...");
            global $wpdb;
            $test_query = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}monthly_rooms WHERE is_active = 1");
            error_log("Admin Calendar Debug - Direct query result: " . $test_query);
            if ($wpdb->last_error) {
                error_log("Admin Calendar Debug - Database error: " . $wpdb->last_error);
            }
            
            error_log("Admin Calendar Debug - Using fallback direct query");
            $rooms_table = $wpdb->prefix . 'monthly_rooms';
            $rooms = $wpdb->get_results("SELECT id, room_id, display_name, room_name, property_name FROM $rooms_table WHERE is_active = 1 ORDER BY property_name, room_name");
            error_log("Admin Calendar Debug - Fallback query returned: " . count($rooms) . " rooms");
        } else {
            $room_names = array_map(function($r) { return $r->display_name; }, $rooms);
            error_log("Admin Calendar Debug - Room names: " . implode(', ', $room_names));
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="monthly-booking-admin-content">
                <div class="calendar-controls">
                    <div class="room-selector">
                        <label for="room_select"><?php _e('部屋選択', 'monthly-booking'); ?>:</label>
                        <select id="room_select" name="room_id" onchange="try { console.log('Room selected:', this.value); var url = '<?php echo admin_url('admin.php?page=monthly-room-booking-calendar&room_id='); ?>' + this.value; console.log('Redirecting to:', url); window.location.href = url; } catch(e) { console.error('Dropdown error:', e); alert('Error selecting room: ' + e.message); }">
                            <option value="0"><?php _e('部屋を選択してください', 'monthly-booking'); ?></option>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo esc_attr($room->room_id); ?>" <?php selected($selected_room_id, $room->room_id); ?>>
                                    <?php echo esc_html($room->display_name . ' (' . $room->room_name . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <?php if ($selected_room_id > 0): ?>
                    <?php $this->render_plan_availability_calendar($selected_room_id); ?>
                <?php else: ?>
                    <div class="notice notice-info">
                        <p><?php _e('部屋を選択すると、180日間の予約可能状況がプラン別に表示されます。', 'monthly-booking'); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="calendar-legend">
                    <h3><?php _e('Legend', 'monthly-booking'); ?></h3>
                    <div class="legend-items">
                        <div class="legend-item">
                            <span class="legend-symbol available">〇</span>
                            <span><?php _e('予約可', 'monthly-booking'); ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-symbol unavailable">×</span>
                            <span><?php _e('予約不可', 'monthly-booking'); ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-symbol campaign">△</span>
                            <span><?php _e('キャンペーン適用あり', 'monthly-booking'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .monthly-booking-admin-content {
            max-width: 100%;
            overflow-x: auto;
        }
        .calendar-controls {
            margin-bottom: 20px;
            padding: 20px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .room-selector label {
            font-weight: 600;
            margin-right: 10px;
        }
        .room-selector select {
            min-width: 300px;
            padding: 5px;
        }
        .plan-availability-calendar {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow-x: auto;
            margin-bottom: 20px;
        }
        .availability-table {
            width: 100%;
            min-width: 2000px;
            border-collapse: collapse;
            font-size: 12px;
        }
        .availability-table th,
        .availability-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            white-space: nowrap;
        }
        .availability-table th {
            background: #0073aa;
            color: #fff;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .availability-table th.plan-header {
            background: #005a87;
            left: 0;
            z-index: 11;
            min-width: 80px;
        }
        .availability-table td.plan-cell {
            background: #f8f9fa;
            font-weight: 600;
            position: sticky;
            left: 0;
            z-index: 9;
            min-width: 80px;
        }
        .availability-table .date-header {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            min-width: 30px;
            max-width: 30px;
        }
        .availability-table .date-header.today {
            background: #2196f3;
        }
        .availability-table .date-header.weekend {
            background: #ff5722;
        }
        .availability-cell {
            font-size: 16px;
            font-weight: bold;
            min-width: 30px;
            max-width: 30px;
        }
        .availability-cell.available {
            color: #4caf50;
            background: #e8f5e8;
        }
        .availability-cell.unavailable {
            color: #f44336;
            background: #ffebee;
        }
        .availability-cell.campaign {
            color: #856404;
            background: #fff3cd;
            border: 2px solid #ffc107;
            animation: campaign-highlight 2s ease-in-out infinite alternate;
        }
        .campaign-symbol {
            font-size: 10px;
            font-weight: bold;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
        }
        @keyframes campaign-highlight {
            0% { background-color: #fff3cd; }
            100% { background-color: #ffeaa7; }
        }
        .availability-cell.today {
            border: 2px solid #2196f3;
        }
        .calendar-legend {
            padding: 20px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .calendar-legend h3 {
            margin-top: 0;
            margin-bottom: 15px;
        }
        .legend-items {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .legend-symbol {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }
        .legend-symbol.available {
            color: #4caf50;
            background: #e8f5e8;
        }
        .legend-symbol.unavailable {
            color: #f44336;
            background: #ffebee;
        }
        .legend-symbol.campaign {
            color: #ff9800;
            background: #fff3e0;
        }
        </style>
        <?php
    }
    
    /**
     * Get all active rooms for dropdown selection
     */
    private function get_all_rooms() {
        global $wpdb;
        
        $rooms_table = $wpdb->prefix . 'monthly_rooms';
        
        error_log("get_all_rooms Debug - Executing query on table: " . $rooms_table);
        
        $sql = "SELECT room_id, display_name, room_name, property_name 
                FROM $rooms_table 
                WHERE is_active = 1 
                ORDER BY property_name, room_name";
        
        $results = $wpdb->get_results($sql);
        
        if ($wpdb->last_error) {
            error_log("get_all_rooms Debug - SQL Error: " . $wpdb->last_error);
            return array();
        }
        
        error_log("get_all_rooms Debug - Query successful, returned " . count($results) . " rooms");
        return $results;
    }
    
    /**
     * Render the 180-day plan availability calendar for a specific room
     */
    private function render_plan_availability_calendar($room_id) {
        $room_bookings = $this->get_room_bookings_180_days($room_id);
        $campaigns = $this->get_active_campaigns();
        $plans = array('SS', 'S', 'M', 'L');
        $today = date('Y-m-d');
        
        ?>
        <div class="plan-availability-calendar">
            <table class="availability-table">
                <thead>
                    <tr>
                        <th class="plan-header"><?php _e('プラン', 'monthly-booking'); ?></th>
                        <?php
                        for ($i = 0; $i < 180; $i++) {
                            $date = date('Y-m-d', strtotime("+$i days"));
                            $day_of_week = date('w', strtotime($date));
                            $is_today = ($date === $today);
                            $is_weekend = ($day_of_week == 0 || $day_of_week == 6);
                            
                            $header_classes = array('date-header');
                            if ($is_today) $header_classes[] = 'today';
                            if ($is_weekend) $header_classes[] = 'weekend';
                            
                            echo '<th class="' . implode(' ', $header_classes) . '">';
                            echo date('n/j', strtotime($date)) . '<br>';
                            echo date('(D)', strtotime($date));
                            echo '</th>';
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plans as $plan): ?>
                        <tr>
                            <td class="plan-cell"><?php echo esc_html($plan); ?></td>
                            <?php
                            for ($i = 0; $i < 180; $i++) {
                                $date = date('Y-m-d', strtotime("+$i days"));
                                $availability = $this->get_plan_availability($room_id, $plan, $date, $room_bookings, $campaigns);
                                $is_today = ($date === $today);
                                
                                $cell_classes = array('availability-cell', $availability['status']);
                                if ($is_today) $cell_classes[] = 'today';
                                
                                echo '<td class="' . implode(' ', $cell_classes) . '">';
                                if ($availability['status'] === 'campaign') {
                                    echo '<span class="campaign-symbol" title="' . esc_attr($availability['campaign_name']) . '">';
                                    echo esc_html($availability['symbol']) . ' ' . esc_html($availability['campaign_name']);
                                    echo '</span>';
                                } else {
                                    echo esc_html($availability['symbol']);
                                }
                                echo '</td>';
                            }
                            ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="campaign-legend">
            <h3>表示記号の説明</h3>
            <div class="legend-items">
                <div class="legend-item">
                    <span class="legend-symbol available">〇</span>
                    <span>予約可能</span>
                </div>
                <div class="legend-item">
                    <span class="legend-symbol unavailable">×</span>
                    <span>予約不可</span>
                </div>
                <div class="legend-item">
                    <span class="legend-symbol campaign">△</span>
                    <span>キャンペーン適用可能</span>
                </div>
            </div>
            <div style="margin-top: 15px; padding: 10px; background: #e3f2fd; border-radius: 4px;">
                <strong>キャンペーン詳細:</strong><br>
                <span style="color: #1976d2;">• 早割:</span> 入居30日以上前のご予約で賃料・共益費10%OFF<br>
                <span style="color: #1976d2;">• 即入居:</span> 入居7日以内のご予約で賃料・共益費20%OFF
            </div>
        </div>
        <?php
    }
    
    /**
     * Get bookings for a room for the next 180 days plus buffer for cleaning periods
     */
    private function get_room_bookings_180_days($room_id) {
        global $wpdb;
        
        $bookings_table = $wpdb->prefix . 'monthly_bookings';
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d', strtotime('+365 days'));
        
        $sql = "SELECT * FROM $bookings_table 
                WHERE room_id = %d 
                AND (start_date <= %s AND end_date >= %s) 
                AND status != 'cancelled'
                ORDER BY start_date";
        
        return $wpdb->get_results($wpdb->prepare($sql, $room_id, $end_date, $start_date));
    }
    
    /**
     * Get active campaigns
     */
    private function get_active_campaigns() {
        global $wpdb;
        
        $campaigns_table = $wpdb->prefix . 'monthly_campaigns';
        $today = date('Y-m-d');
        
        $sql = "SELECT * FROM $campaigns_table 
                WHERE is_active = 1 
                AND start_date <= %s 
                AND end_date >= %s";
        
        return $wpdb->get_results($wpdb->prepare($sql, $today, $today));
    }
    
    /**
     * Get plan availability for a specific date with real booking conflict detection
     */
    private function get_plan_availability($room_id, $plan, $date, $bookings, $campaigns) {
        $today = date('Y-m-d');
        
        if ($date < $today) {
            return array('status' => 'unavailable', 'symbol' => '×');
        }
        
        $plan_days = $this->get_plan_duration($plan);
        $cleaning_grace_days = 5;
        
        $proposed_start = $date;
        $proposed_end = date('Y-m-d', strtotime($date . ' +' . ($plan_days - 1) . ' days'));
        $proposed_end_with_cleaning = date('Y-m-d', strtotime($proposed_end . ' +' . $cleaning_grace_days . ' days'));
        
        foreach ($bookings as $booking) {
            $booking_start = $booking->start_date;
            $booking_end = $booking->end_date;
            $booking_end_with_cleaning = date('Y-m-d', strtotime($booking_end . ' +' . $cleaning_grace_days . ' days'));
            
            if ($this->date_ranges_overlap($proposed_start, $proposed_end_with_cleaning, $booking_start, $booking_end_with_cleaning)) {
                return array('status' => 'unavailable', 'symbol' => '×');
            }
        }
        
        $applicable_campaign = $this->check_campaign_eligibility($date, $campaigns);
        
        if ($applicable_campaign) {
            return array(
                'status' => 'campaign', 
                'symbol' => '△', 
                'campaign_name' => $applicable_campaign['name'],
                'campaign_type' => $applicable_campaign['type']
            );
        }
        
        return array('status' => 'available', 'symbol' => '〇');
    }
    
    /**
     * Get the duration in days for each plan type
     */
    private function get_plan_duration($plan) {
        $plan_durations = array(
            'SS' => 7,
            'S' => 30,
            'M' => 90,
            'L' => 180
        );
        
        return isset($plan_durations[$plan]) ? $plan_durations[$plan] : 30;
    }
    
    /**
     * Check if two date ranges overlap
     */
    private function date_ranges_overlap($start1, $end1, $start2, $end2) {
        return ($start1 <= $end2) && ($end1 >= $start2);
    }
    
    /**
     * Check campaign eligibility for a specific date
     */
    private function check_campaign_eligibility($date, $campaigns) {
        $today = date('Y-m-d');
        
        foreach ($campaigns as $campaign) {
            if ($campaign->is_active != 1) {
                continue;
            }
            
            if ($date < $campaign->start_date || $date > $campaign->end_date) {
                continue;
            }
            
            $days_until_checkin = (strtotime($date) - strtotime($today)) / (60 * 60 * 24);
            
            if (!class_exists('MonthlyBooking_Campaign_Manager')) {
                require_once plugin_dir_path(__FILE__) . 'campaign-manager.php';
            }
            
            $campaign_manager = new MonthlyBooking_Campaign_Manager();
            $campaigns = $campaign_manager->get_applicable_campaigns($date);
            
            if ($campaigns && !empty($campaigns)) {
                $campaign = $campaigns[0];
                return array(
                    'name' => $campaign['badge'],
                    'type' => $campaign['type']
                );
            }
        }
        
        return false;
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
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'monthly_campaigns';
        
        if (isset($_POST['action']) && $_POST['action'] === 'create_campaign') {
            echo '<div class="notice notice-success"><p>' . __('キャンペーンが作成されました（仮実装）', 'monthly-booking') . '</p></div>';
        }
        
        if (isset($_POST['action']) && $_POST['action'] === 'edit_campaign') {
            echo '<div class="notice notice-success"><p>' . __('キャンペーンが更新されました（仮実装）', 'monthly-booking') . '</p></div>';
        }
        
        $campaigns = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="monthly-booking-admin-content">
                <h2><?php _e('キャンペーン設定', 'monthly-booking'); ?></h2>
                <p><?php _e('割引キャンペーンの作成・管理を行います。', 'monthly-booking'); ?></p>
                
                <!-- New Campaign Button -->
                <div style="margin-bottom: 20px;">
                    <button type="button" class="button button-primary" onclick="showCampaignModal()"><?php _e('新規作成', 'monthly-booking'); ?></button>
                </div>
                
                <!-- Campaign List Table -->
                <table class="monthly-booking-table widefat">
                    <thead>
                        <tr>
                            <th><?php _e('キャンペーン名', 'monthly-booking'); ?></th>
                            <th><?php _e('タイプ', 'monthly-booking'); ?></th>
                            <th><?php _e('割引率', 'monthly-booking'); ?></th>
                            <th><?php _e('適用期間', 'monthly-booking'); ?></th>
                            <th><?php _e('対象プラン', 'monthly-booking'); ?></th>
                            <th><?php _e('ステータス', 'monthly-booking'); ?></th>
                            <th><?php _e('操作', 'monthly-booking'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($campaigns)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">
                                <?php _e('キャンペーンが登録されていません。', 'monthly-booking'); ?>
                                <br>
                                <small><?php _e('「新規作成」ボタンからキャンペーンを追加してください。', 'monthly-booking'); ?></small>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($campaigns as $campaign): ?>
                        <tr>
                            <td><strong><?php echo esc_html($campaign->campaign_name); ?></strong></td>
                            <td>
                                <?php 
                                $type_labels = array(
                                    'immediate' => '即入居割',
                                    'earlybird' => '早割',
                                    'flatrate' => 'コミコミ10万円'
                                );
                                echo esc_html($type_labels[$campaign->type] ?? $campaign->type);
                                ?>
                            </td>
                            <td>
                                <?php if ($campaign->discount_type === 'percentage'): ?>
                                    <?php echo esc_html($campaign->discount_value); ?>%
                                <?php elseif ($campaign->discount_type === 'fixed'): ?>
                                    ¥<?php echo esc_html(number_format($campaign->discount_value)); ?>
                                <?php else: ?>
                                    ¥<?php echo esc_html(number_format($campaign->discount_value)); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo esc_html($campaign->start_date); ?> ～ <?php echo esc_html($campaign->end_date); ?>
                            </td>
                            <td><?php echo esc_html($campaign->target_plan); ?></td>
                            <td>
                                <span class="campaign-status <?php echo $campaign->is_active ? 'active' : 'inactive'; ?>">
                                    <?php echo $campaign->is_active ? __('有効', 'monthly-booking') : __('無効', 'monthly-booking'); ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="button button-small" onclick="editCampaign(<?php echo esc_attr($campaign->id); ?>)"><?php _e('編集', 'monthly-booking'); ?></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <div class="notice notice-info" style="margin-top: 20px;">
                    <p><strong><?php _e('キャンペーンルール:', 'monthly-booking'); ?></strong></p>
                    <ul>
                        <li><?php _e('即入居割: チェックイン7日以内で自動適用', 'monthly-booking'); ?></li>
                        <li><?php _e('早割: チェックイン30日以上前で自動適用', 'monthly-booking'); ?></li>
                        <li><?php _e('コミコミ10万円: 7-10日滞在で全込み10万円', 'monthly-booking'); ?></li>
                        <li><?php _e('最大1つのキャンペーンのみ適用されます', 'monthly-booking'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Campaign Modal -->
        <div id="campaign-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 5px; width: 500px; max-width: 90%;">
                <h3 id="modal-title"><?php _e('新規キャンペーン作成', 'monthly-booking'); ?></h3>
                
                <form method="post" id="campaign-form">
                    <input type="hidden" name="action" value="create_campaign" id="form-action">
                    <input type="hidden" name="campaign_id" value="" id="campaign-id">
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="campaign_name"><?php _e('キャンペーン名', 'monthly-booking'); ?></label></th>
                            <td><input type="text" name="campaign_name" id="campaign_name" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="campaign_type"><?php _e('タイプ', 'monthly-booking'); ?></label></th>
                            <td>
                                <select name="campaign_type" id="campaign_type" required>
                                    <option value=""><?php _e('選択してください', 'monthly-booking'); ?></option>
                                    <option value="immediate"><?php _e('即入居割', 'monthly-booking'); ?></option>
                                    <option value="earlybird"><?php _e('早割', 'monthly-booking'); ?></option>
                                    <option value="flatrate"><?php _e('コミコミ10万円', 'monthly-booking'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="discount_type"><?php _e('割引タイプ', 'monthly-booking'); ?></label></th>
                            <td>
                                <select name="discount_type" id="discount_type" required>
                                    <option value=""><?php _e('選択してください', 'monthly-booking'); ?></option>
                                    <option value="percentage"><?php _e('パーセンテージ', 'monthly-booking'); ?></option>
                                    <option value="fixed"><?php _e('固定金額', 'monthly-booking'); ?></option>
                                    <option value="flatrate"><?php _e('定額料金', 'monthly-booking'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="discount_value"><?php _e('割引値', 'monthly-booking'); ?></label></th>
                            <td><input type="number" name="discount_value" id="discount_value" class="regular-text" min="0" step="0.01" required></td>
                        </tr>
                        <tr>
                            <th><label for="start_date"><?php _e('開始日', 'monthly-booking'); ?></label></th>
                            <td><input type="date" name="start_date" id="start_date" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="end_date"><?php _e('終了日', 'monthly-booking'); ?></label></th>
                            <td><input type="date" name="end_date" id="end_date" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="target_plan"><?php _e('対象プラン', 'monthly-booking'); ?></label></th>
                            <td>
                                <select name="target_plan" id="target_plan" required>
                                    <option value="ALL"><?php _e('全プラン', 'monthly-booking'); ?></option>
                                    <option value="SS"><?php _e('SSプラン', 'monthly-booking'); ?></option>
                                    <option value="S"><?php _e('Sプラン', 'monthly-booking'); ?></option>
                                    <option value="M"><?php _e('Mプラン', 'monthly-booking'); ?></option>
                                    <option value="L"><?php _e('Lプラン', 'monthly-booking'); ?></option>
                                    <option value="S,M,L"><?php _e('S/M/Lプラン', 'monthly-booking'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="is_active"><?php _e('ステータス', 'monthly-booking'); ?></label></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                                    <?php _e('有効', 'monthly-booking'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                    
                    <div style="margin-top: 20px; text-align: right;">
                        <button type="button" class="button" onclick="hideCampaignModal()"><?php _e('キャンセル', 'monthly-booking'); ?></button>
                        <button type="submit" class="button button-primary" style="margin-left: 10px;"><?php _e('保存（仮）', 'monthly-booking'); ?></button>
                    </div>
                </form>
            </div>
        </div>
        
        <style>
        .monthly-booking-table {
            border-collapse: collapse;
            width: 100%;
        }
        .monthly-booking-table th,
        .monthly-booking-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .monthly-booking-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .campaign-status.active {
            color: #46b450;
            font-weight: bold;
        }
        .campaign-status.inactive {
            color: #dc3232;
            font-weight: bold;
        }
        </style>
        
        <script>
        function showCampaignModal() {
            document.getElementById('modal-title').textContent = '<?php _e('新規キャンペーン作成', 'monthly-booking'); ?>';
            document.getElementById('form-action').value = 'create_campaign';
            document.getElementById('campaign-id').value = '';
            document.getElementById('campaign-form').reset();
            document.getElementById('is_active').checked = true;
            document.getElementById('campaign-modal').style.display = 'block';
        }
        
        function editCampaign(campaignId) {
            document.getElementById('modal-title').textContent = '<?php _e('キャンペーン編集', 'monthly-booking'); ?>';
            document.getElementById('form-action').value = 'edit_campaign';
            document.getElementById('campaign-id').value = campaignId;
            
            document.getElementById('campaign_name').value = 'サンプルキャンペーン';
            document.getElementById('campaign_type').value = 'immediate';
            document.getElementById('discount_type').value = 'percentage';
            document.getElementById('discount_value').value = '20';
            document.getElementById('start_date').value = '2025-01-01';
            document.getElementById('end_date').value = '2099-12-31';
            document.getElementById('target_plan').value = 'ALL';
            document.getElementById('is_active').checked = true;
            
            document.getElementById('campaign-modal').style.display = 'block';
        }
        
        function hideCampaignModal() {
            document.getElementById('campaign-modal').style.display = 'none';
        }
        
        document.getElementById('campaign-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideCampaignModal();
            }
        });
        </script>
        <?php
    }
    
    /**
     * Admin page: オプション管理 (Options Management)
     */
    public function admin_page_options_management() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'monthly-booking'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'monthly_options';
        
        if (isset($_POST['action']) && $_POST['action'] === 'update_option') {
            check_admin_referer('monthly_booking_update_option');
            
            $option_id = intval($_POST['option_id']);
            $option_name = sanitize_text_field($_POST['option_name']);
            $price = floatval($_POST['price']);
            $is_discount_target = isset($_POST['is_discount_target']) ? 1 : 0;
            $display_order = intval($_POST['display_order']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            $wpdb->update(
                $table_name,
                array(
                    'option_name' => $option_name,
                    'price' => $price,
                    'is_discount_target' => $is_discount_target,
                    'display_order' => $display_order,
                    'is_active' => $is_active
                ),
                array('id' => $option_id),
                array('%s', '%f', '%d', '%d', '%d'),
                array('%d')
            );
            
            echo '<div class="notice notice-success"><p>' . __('オプションが更新されました。', 'monthly-booking') . '</p></div>';
        }
        
        $options = $wpdb->get_results("SELECT * FROM $table_name ORDER BY display_order ASC");
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="monthly-booking-admin-content">
                <h2><?php _e('オプション管理', 'monthly-booking'); ?></h2>
                <p><?php _e('月額予約のオプションセットを管理します。', 'monthly-booking'); ?></p>
                
                <table class="monthly-booking-table">
                    <thead>
                        <tr>
                            <th><?php _e('表示順', 'monthly-booking'); ?></th>
                            <th><?php _e('オプション名', 'monthly-booking'); ?></th>
                            <th><?php _e('価格', 'monthly-booking'); ?></th>
                            <th><?php _e('割引対象', 'monthly-booking'); ?></th>
                            <th><?php _e('ステータス', 'monthly-booking'); ?></th>
                            <th><?php _e('操作', 'monthly-booking'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($options as $option): ?>
                        <tr>
                            <form method="post" style="display: contents;">
                                <?php wp_nonce_field('monthly_booking_update_option'); ?>
                                <input type="hidden" name="action" value="update_option">
                                <input type="hidden" name="option_id" value="<?php echo esc_attr($option->id); ?>">
                                
                                <td>
                                    <input type="number" name="display_order" value="<?php echo esc_attr($option->display_order); ?>" min="1" max="99" style="width: 60px;">
                                </td>
                                <td>
                                    <input type="text" name="option_name" value="<?php echo esc_attr($option->option_name); ?>" style="width: 200px;" required>
                                </td>
                                <td>
                                    ¥<input type="number" name="price" value="<?php echo esc_attr($option->price); ?>" min="0" step="10" style="width: 100px;" required>
                                </td>
                                <td>
                                    <label>
                                        <input type="checkbox" name="is_discount_target" value="1" <?php checked($option->is_discount_target, 1); ?>>
                                        <?php _e('割引対象', 'monthly-booking'); ?>
                                    </label>
                                </td>
                                <td>
                                    <label>
                                        <input type="checkbox" name="is_active" value="1" <?php checked($option->is_active, 1); ?>>
                                        <?php _e('有効', 'monthly-booking'); ?>
                                    </label>
                                </td>
                                <td>
                                    <button type="submit" class="button button-primary"><?php _e('更新', 'monthly-booking'); ?></button>
                                </td>
                            </form>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="notice notice-info">
                    <p><strong><?php _e('割引ルール:', 'monthly-booking'); ?></strong></p>
                    <ul>
                        <li><?php _e('2つのオプション選択で500円割引', 'monthly-booking'); ?></li>
                        <li><?php _e('3つ以上のオプション選択で、3つ目以降1つにつき300円追加割引', 'monthly-booking'); ?></li>
                        <li><?php _e('最大割引額は2,000円まで', 'monthly-booking'); ?></li>
                        <li><?php _e('割引は「割引対象」にチェックが入っているオプションのみに適用されます', 'monthly-booking'); ?></li>
                    </ul>
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
