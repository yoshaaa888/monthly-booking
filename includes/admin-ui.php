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
                                    <a href="<?php echo admin_url('admin.php?page=monthly-room-booking&action=edit&id=' . $property->id); ?>" class="button button-small"><?php _e('Edit', 'monthly-booking'); ?></a>
                                    <a href="<?php echo admin_url('admin.php?page=monthly-room-booking&action=delete&id=' . $property->id); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php _e('Are you sure you want to delete this property?', 'monthly-booking'); ?>')"><?php _e('Delete', 'monthly-booking'); ?></a>
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
            $property = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $property_id));
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
            $result = $wpdb->update($table_name, $data, array('id' => $property_db_id));
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
        $result = $wpdb->delete($table_name, array('id' => $property_id), array('%d'));
        
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
        
        $current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
        $current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
        
        if ($current_month < 1) {
            $current_month = 12;
            $current_year--;
        } elseif ($current_month > 12) {
            $current_month = 1;
            $current_year++;
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="monthly-booking-admin-content">
                <div class="calendar-header">
                    <div class="calendar-navigation">
                        <a href="<?php echo admin_url('admin.php?page=monthly-room-booking-calendar&year=' . ($current_month == 1 ? $current_year - 1 : $current_year) . '&month=' . ($current_month == 1 ? 12 : $current_month - 1)); ?>" class="button">
                            ← <?php _e('Previous Month', 'monthly-booking'); ?>
                        </a>
                        <h2 class="calendar-title">
                            <?php echo sprintf(__('%s年 %s月', 'monthly-booking'), $current_year, $current_month); ?>
                        </h2>
                        <a href="<?php echo admin_url('admin.php?page=monthly-room-booking-calendar&year=' . ($current_month == 12 ? $current_year + 1 : $current_year) . '&month=' . ($current_month == 12 ? 1 : $current_month + 1)); ?>" class="button">
                            <?php _e('Next Month', 'monthly-booking'); ?> →
                        </a>
                    </div>
                </div>
                
                <?php $this->render_booking_calendar($current_year, $current_month); ?>
                
                <div class="calendar-legend">
                    <h3><?php _e('Legend', 'monthly-booking'); ?></h3>
                    <div class="legend-items">
                        <div class="legend-item">
                            <span class="legend-color available"></span>
                            <span><?php _e('Available', 'monthly-booking'); ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color booked"></span>
                            <span><?php _e('Booked', 'monthly-booking'); ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color cleaning"></span>
                            <span><?php _e('Cleaning Period', 'monthly-booking'); ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color today"></span>
                            <span><?php _e('Today', 'monthly-booking'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .monthly-booking-admin-content {
            max-width: 1200px;
        }
        .calendar-header {
            margin-bottom: 20px;
            padding: 20px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .calendar-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .calendar-title {
            margin: 0;
            font-size: 24px;
            color: #0073aa;
        }
        .booking-calendar {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #ddd;
        }
        .calendar-day-header {
            background: #0073aa;
            color: #fff;
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            font-size: 14px;
        }
        .calendar-day {
            background: #fff;
            min-height: 100px;
            padding: 8px;
            position: relative;
            border: 1px solid transparent;
        }
        .calendar-day.other-month {
            background: #f5f5f5;
            color: #999;
        }
        .calendar-day.today {
            background: #e3f2fd;
            border-color: #2196f3;
        }
        .calendar-day.available {
            background: #e8f5e8;
        }
        .calendar-day.booked {
            background: #ffebee;
        }
        .calendar-day.cleaning {
            background: #fff3e0;
        }
        .day-number {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 4px;
        }
        .day-bookings {
            font-size: 11px;
            margin-top: 4px;
        }
        .booking-item {
            background: #0073aa;
            color: #fff;
            padding: 2px 4px;
            border-radius: 2px;
            margin-bottom: 2px;
            font-size: 10px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .booking-item.cleaning {
            background: #ff9800;
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
            gap: 20px;
            flex-wrap: wrap;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .legend-color.available { background: #e8f5e8; }
        .legend-color.booked { background: #ffebee; }
        .legend-color.cleaning { background: #fff3e0; }
        .legend-color.today { background: #e3f2fd; border-color: #2196f3; }
        </style>
        <?php
    }
    
    /**
     * Render the booking calendar for a specific month
     */
    private function render_booking_calendar($year, $month) {
        $bookings = $this->get_month_bookings($year, $month);
        $cleaning_days = get_option('monthly_booking_options')['cleaning_days'] ?? 3;
        
        $first_day = mktime(0, 0, 0, $month, 1, $year);
        $days_in_month = date('t', $first_day);
        $start_day_of_week = date('w', $first_day);
        $today = date('Y-m-d');
        
        ?>
        <div class="booking-calendar">
            <div class="calendar-grid">
                <div class="calendar-day-header"><?php _e('Sun', 'monthly-booking'); ?></div>
                <div class="calendar-day-header"><?php _e('Mon', 'monthly-booking'); ?></div>
                <div class="calendar-day-header"><?php _e('Tue', 'monthly-booking'); ?></div>
                <div class="calendar-day-header"><?php _e('Wed', 'monthly-booking'); ?></div>
                <div class="calendar-day-header"><?php _e('Thu', 'monthly-booking'); ?></div>
                <div class="calendar-day-header"><?php _e('Fri', 'monthly-booking'); ?></div>
                <div class="calendar-day-header"><?php _e('Sat', 'monthly-booking'); ?></div>
                
                <?php
                for ($i = 0; $i < $start_day_of_week; $i++) {
                    $prev_month_day = date('j', mktime(0, 0, 0, $month, -$start_day_of_week + $i + 1, $year));
                    echo '<div class="calendar-day other-month">';
                    echo '<div class="day-number">' . $prev_month_day . '</div>';
                    echo '</div>';
                }
                
                for ($day = 1; $day <= $days_in_month; $day++) {
                    $current_date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $day_status = $this->get_day_status($current_date, $bookings, $cleaning_days);
                    $is_today = ($current_date === $today);
                    
                    $classes = array('calendar-day');
                    if ($is_today) $classes[] = 'today';
                    if ($day_status['status'] !== 'mixed') $classes[] = $day_status['status'];
                    
                    echo '<div class="' . implode(' ', $classes) . '">';
                    echo '<div class="day-number">' . $day . '</div>';
                    
                    if (!empty($day_status['bookings'])) {
                        echo '<div class="day-bookings">';
                        foreach ($day_status['bookings'] as $booking) {
                            $booking_class = $booking['type'] === 'cleaning' ? 'booking-item cleaning' : 'booking-item';
                            echo '<div class="' . $booking_class . '">';
                            if ($booking['type'] === 'cleaning') {
                                echo __('Cleaning', 'monthly-booking');
                            } else {
                                echo esc_html($booking['room_name'] ?? 'Room ' . $booking['room_id']);
                            }
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                    
                    echo '</div>';
                }
                
                $remaining_cells = 42 - ($start_day_of_week + $days_in_month);
                for ($i = 1; $i <= $remaining_cells; $i++) {
                    echo '<div class="calendar-day other-month">';
                    echo '<div class="day-number">' . $i . '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get bookings for a specific month
     */
    private function get_month_bookings($year, $month) {
        global $wpdb;
        
        $bookings_table = $wpdb->prefix . 'monthly_bookings';
        $rooms_table = $wpdb->prefix . 'monthly_rooms';
        
        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
        
        $sql = "SELECT b.*, r.room_name, r.display_name 
                FROM $bookings_table b 
                LEFT JOIN $rooms_table r ON b.room_id = r.id 
                WHERE (b.start_date <= %s AND b.end_date >= %s) 
                AND b.status != 'cancelled'
                ORDER BY b.start_date";
        
        return $wpdb->get_results($wpdb->prepare($sql, $end_date, $start_date));
    }
    
    /**
     * Get the status of a specific day
     */
    private function get_day_status($date, $bookings, $cleaning_days) {
        $day_bookings = array();
        $statuses = array();
        
        foreach ($bookings as $booking) {
            if ($date >= $booking->start_date && $date <= $booking->end_date) {
                $day_bookings[] = array(
                    'type' => 'booking',
                    'room_id' => $booking->room_id,
                    'room_name' => $booking->room_name,
                    'status' => $booking->status
                );
                $statuses[] = 'booked';
            }
            
            $cleaning_start = date('Y-m-d', strtotime($booking->end_date . ' +1 day'));
            $cleaning_end = date('Y-m-d', strtotime($booking->end_date . ' +' . $cleaning_days . ' days'));
            
            if ($date >= $cleaning_start && $date <= $cleaning_end) {
                $day_bookings[] = array(
                    'type' => 'cleaning',
                    'room_id' => $booking->room_id,
                    'room_name' => $booking->room_name
                );
                $statuses[] = 'cleaning';
            }
        }
        
        if (empty($statuses)) {
            $status = 'available';
        } elseif (count(array_unique($statuses)) > 1) {
            $status = 'mixed';
        } else {
            $status = $statuses[0];
        }
        
        return array(
            'status' => $status,
            'bookings' => $day_bookings
        );
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
