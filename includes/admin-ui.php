<?php
/**
 * Admin UI functionality for Monthly Room Booking plugin
 * 
 * @package MonthlyRoomBooking
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'i18n.php';

class MonthlyBooking_Admin_UI {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    private function is_cpt_mode() {
        return defined('MB_USE_CPTS') && MB_USE_CPTS && post_type_exists('mrb_booking') && post_type_exists('mrb_campaign');
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
            __('料金設定', 'monthly-booking'),
            __('料金設定', 'monthly-booking'),
            'manage_options',
            'monthly-booking-fee-settings',
            array($this, 'render_fee_settings_page')
        );
        
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        $should_enqueue = false;

        if (strpos($hook, 'monthly-booking') !== false) {
            $should_enqueue = true;
        }

        if (isset($_GET['page'])) {
            $page = sanitize_text_field($_GET['page']);
            $targets = array(
                'monthly-room-booking-campaigns',
                'monthly-room-booking-registration',
            );
            if (in_array($page, $targets, true)) {
                $should_enqueue = true;
            }
        }

        if (!$should_enqueue) {
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

        wp_localize_script(
            'monthly-booking-admin',
            'monthlyBookingAdmin',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('monthly_booking_admin'),
                'reservationsNonce' => wp_create_nonce('mbp_reservations_nonce')
            )
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
            <h1><?php echo esc_html(mb_t('rooms.title')); ?></h1>
            
            <form method="get" action="">
                <input type="hidden" name="page" value="monthly-room-booking">
                <div class="tablenav top">
                    <div class="alignleft actions">
                        <a href="<?php echo admin_url('admin.php?page=monthly-room-booking&action=add'); ?>" class="button button-primary"><?php echo esc_html(mb_t('rooms.add_new')); ?></a>
                    </div>
                    <div class="alignleft actions">
                        <label style="margin-right:8px;">
                            <?php echo esc_html(mb_t('rooms.filters.status')); ?>:
                            <select name="filter_status">
                                <?php
                                $fs = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '';
                                $opts = array(
                                    '' => mb_t('rooms.filters.any'),
                                    'active' => mb_t('status.active'),
                                    'inactive' => mb_t('status.inactive')
                                );
                                foreach ($opts as $val => $label) {
                                    echo '<option value="' . esc_attr($val) . '"' . selected($fs, $val, false) . '>' . esc_html($label) . '</option>';
                                }
                                ?>
                            </select>
                        </label>
                        <label style="margin-right:8px;">
                            <?php echo esc_html(mb_t('rooms.filters.campaign')); ?>:
                            <select name="filter_campaign">
                                <?php
                                $fc = isset($_GET['filter_campaign']) ? sanitize_text_field($_GET['filter_campaign']) : '';
                                $opts2 = array(
                                    '' => mb_t('rooms.filters.any'),
                                    'has' => mb_t('rooms.filters.campaign_has'),
                                    'none' => mb_t('rooms.filters.campaign_none')
                                );
                                foreach ($opts2 as $val => $label) {
                                    echo '<option value="' . esc_attr($val) . '"' . selected($fc, $val, false) . '>' . esc_html($label) . '</option>';
                                }
                                ?>
                            </select>
                        </label>
                        <button class="button" type="submit"><?php echo esc_html(mb_t('rooms.filters.apply')); ?></button>
                        <a class="button" href="<?php echo admin_url('admin.php?page=monthly-room-booking'); ?>"><?php echo esc_html(mb_t('rooms.filters.reset')); ?></a>
                    </div>
                </div>
            </form>

            <?php
            $fs = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '';
            $fc = isset($_GET['filter_campaign']) ? sanitize_text_field($_GET['filter_campaign']) : '';
            $table_rooms = $wpdb->prefix . 'monthly_rooms';
            $where = array();
            if ($fs === 'active') $where[] = "is_active=1";
            if ($fs === 'inactive') $where[] = "is_active=0";
            $sql = "SELECT * FROM $table_rooms";
            if ($where) $sql .= " WHERE " . implode(' AND ', $where);
            $sql .= " ORDER BY property_id, room_name";
            $properties = $wpdb->get_results($sql);

            $roomCampaignBadges = array();
            $hasCampaignRoomIds = array();
            if (!empty($properties)) {
                $room_ids = array_map(function($p){ return (int)$p->room_id; }, $properties);
                $in = implode(',', array_fill(0, count($room_ids), '%d'));
                $today = current_time('Y-m-d');
                $ta = $wpdb->prefix . 'monthly_room_campaigns';
                $tc = $wpdb->prefix . 'monthly_campaigns';
                $rows = $wpdb->get_results($wpdb->prepare("
                    SELECT a.room_id, c.discount_type, c.discount_value, c.campaign_name
                    FROM {$ta} a
                    JOIN {$tc} c ON a.campaign_id=c.id
                    WHERE a.room_id IN ($in)
                      AND a.is_active=1
                      AND a.start_date <= %s
                      AND a.end_date > %s
                ", array_merge($room_ids, array($today, $today))));
                if ($rows) {
                    foreach ($rows as $r) {
                        $icon = ($r->discount_type === 'percentage') ? '％' : '¥';
                        $val = ($r->discount_type === 'percentage')
                            ? number_format_i18n((float)$r->discount_value, 0) . '%'
                            : '¥' . number_format_i18n((float)$r->discount_value, 0);
                        $label = $icon . ' ' . $val;
                        $roomCampaignBadges[(int)$r->room_id][] = $label;
                        $hasCampaignRoomIds[(int)$r->room_id] = true;
                    }
                }
            }
            if ($fc === 'has' || $fc === 'none') {
                $properties = array_values(array_filter($properties, function($p) use ($fc, $hasCampaignRoomIds){
                    $has = !empty($hasCampaignRoomIds[(int)$p->room_id]);
                    return ($fc === 'has') ? $has : !$has;
                }));
            }

            $occupiedToday = array();
            if (!empty($properties)) {
                $room_ids = array_map(function($p){ return (int)$p->room_id; }, $properties);
                $in = implode(',', array_fill(0, count($room_ids), '%d'));
                $today = current_time('Y-m-d');
                $tb = $wpdb->prefix . 'monthly_bookings';
                $sqlOcc = $wpdb->prepare("
                    SELECT room_id FROM {$tb}
                    WHERE room_id IN ($in)
                      AND %s >= start_date
                      AND %s < DATE_ADD(end_date, INTERVAL 5 DAY)
                      AND status <> 'cancelled'
                    GROUP BY room_id
                ", array_merge($room_ids, array($today, $today)));
                $occRows = $wpdb->get_results($sqlOcc);
                if ($occRows) {
                    foreach ($occRows as $o) $occupiedToday[(int)$o->room_id] = true;
                }
            }
            ?>
            <div class="tablenav top" style="margin:10px 0;">
                <div class="alignleft actions bulkactions">
                    <button type="button" class="button" id="rooms-bulk-assign"><?php echo esc_html(mb_t('rooms.bulk.assign')); ?></button>
                    <button type="button" class="button" id="rooms-bulk-unassign"><?php echo esc_html(mb_t('rooms.bulk.unassign')); ?></button>
                </div>
            </div>
            <table class="wp-list-table widefat fixed striped" id="rooms-table">
                <thead>
                    <tr>
                        <th style="width:28px;"><input type="checkbox" id="rooms-select-all"></th>
                        <th><?php echo esc_html(mb_t('rooms.table.header.property_id')); ?></th>
                        <th><?php echo esc_html(mb_t('rooms.table.header.room_id')); ?></th>
                        <th><?php echo esc_html(mb_t('rooms.table.header.display_name')); ?></th>
                        <th><?php echo esc_html(mb_t('rooms.table.header.room')); ?></th>
                        <th><?php echo esc_html(mb_t('rooms.table.header.daily_rent')); ?></th>
                        <th><?php echo esc_html(mb_t('rooms.table.header.max_occupants')); ?></th>
                        <th><?php echo esc_html(mb_t('rooms.table.header.campaigns')); ?></th>
                        <th><?php echo esc_html(mb_t('rooms.table.header.vacancy')); ?></th>
                        <th><?php echo esc_html(mb_t('rooms.table.header.cleaning')); ?></th>
                        <th><?php echo esc_html(mb_t('rooms.table.header.station_access')); ?></th>
                        <th><?php echo esc_html(mb_t('rooms.form.campaign.table.header.status')); ?></th>
                        <th><?php echo esc_html(mb_t('rooms.form.campaign.table.header.actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($properties)): ?>
                        <tr>
                            <td colspan="13"><?php echo esc_html(mb_t('rooms.empty')); ?></td>
                        </tr>
                    <?php else: ?>
                    <?php foreach ($properties as $property): ?>
                        <?php
                        $badges = isset($roomCampaignBadges[(int)$property->room_id]) ? $roomCampaignBadges[(int)$property->room_id] : array();
                        $vacant = empty($occupiedToday[(int)$property->room_id]);
                        $clean_opt_key = 'mb_room_cleaned_' . (int)$property->room_id;
                        $is_cleaned = get_option($clean_opt_key, '1') === '1';
                        ?>
                        <tr>
                            <td><input type="checkbox" class="room-select" value="<?php echo esc_attr($property->room_id); ?>"></td>
                            <td><?php echo esc_html($property->property_id); ?></td>
                            <td><?php echo esc_html($property->room_id); ?></td>
                            <td><?php echo esc_html($property->display_name); ?></td>
                            <td><?php echo esc_html($property->room_name); ?></td>
                            <td>¥<?php echo number_format($property->daily_rent); ?></td>
                            <td><?php echo esc_html($property->max_occupants); ?></td>
                            <td>
                                <?php if ($badges): ?>
                                    <?php foreach ($badges as $b): ?>
                                        <span class="badge" style="display:inline-block;background:#fff3cd;border:1px solid #ffc107;border-radius:3px;padding:2px 6px;margin-right:4px;"><?php echo esc_html($b); ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="<?php echo $vacant ? 'vacant-yes' : 'vacant-no'; ?>" style="font-weight:bold;<?php echo $vacant ? 'color:#4caf50' : 'color:#f44336'; ?>">
                                    <?php echo $vacant ? '〇' : '×'; ?>
                                </span>
                            </td>
                            <td>
                                <label>
                                    <input type="checkbox" class="cleaning-toggle" data-room-id="<?php echo esc_attr($property->room_id); ?>" <?php checked($is_cleaned, true); ?>>
                                    <span><?php echo esc_html($is_cleaned ? mb_t('rooms.cleaning.cleaned') : mb_t('rooms.cleaning.not_cleaned')); ?></span>
                                </label>
                            </td>
                            <td>
                                <?php if ($property->station1): ?>
                                    <?php echo esc_html($property->line1 . ' ' . $property->station1 . ' ' . $property->access1_type . $property->access1_time . '分'); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-<?php echo $property->is_active ? 'active' : 'inactive'; ?>">
                                    <?php echo $property->is_active ? esc_html(mb_t('status.active')) : esc_html(mb_t('status.inactive')); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=monthly-room-booking&action=edit&id=' . $property->room_id); ?>" class="button button-small"><?php echo esc_html(mb_t('rooms.action.edit')); ?></a>
                                <a href="<?php echo admin_url('admin.php?page=monthly-room-booking&action=delete&id=' . $property->room_id); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php echo esc_js(mb_t('rooms.confirm.delete')); ?>')"><?php echo esc_html(mb_t('rooms.action.delete')); ?></a>
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
        $page_title = $is_edit ? mb_t('rooms.form.title.edit') : mb_t('rooms.form.title.add');
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html($page_title); ?></h1>
            
            <form method="post" action="<?php echo admin_url('admin.php?page=monthly-room-booking'); ?>">
                <?php wp_nonce_field('monthly_booking_property_save', 'monthly_booking_property_nonce'); ?>
                <input type="hidden" name="property_db_id" value="<?php echo $property_id; ?>">
                
                <div class="form-section">
                    <h3><?php echo esc_html(mb_t('rooms.form.section.basic')); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><label for="property_id"><?php echo esc_html(mb_t('rooms.form.field.property_id')); ?></label></th>
                            <td><input type="number" id="property_id" name="property_id" value="<?php echo $property ? esc_attr($property->property_id) : ''; ?>" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="room_id"><?php echo esc_html(mb_t('rooms.form.field.room_id')); ?></label></th>
                            <td><input type="number" id="room_id" name="room_id" value="<?php echo $property ? esc_attr($property->room_id) : ''; ?>" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="mor_g"><?php echo esc_html(mb_t('rooms.form.field.classification')); ?></label></th>
                            <td>
                                <select id="mor_g" name="mor_g" required>
                                    <option value="M" <?php selected($property ? $property->mor_g : '', 'M'); ?>>M (マンスリー)</option>
                                    <option value="G" <?php selected($property ? $property->mor_g : '', 'G'); ?>>G (ゲストハウス)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="property_name"><?php echo esc_html(mb_t('rooms.form.field.property_name_internal')); ?></label></th>
                            <td><input type="text" id="property_name" name="property_name" value="<?php echo $property ? esc_attr($property->property_name) : ''; ?>" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="display_name"><?php echo esc_html(mb_t('rooms.form.field.display_name')); ?></label></th>
                            <td><input type="text" id="display_name" name="display_name" value="<?php echo $property ? esc_attr($property->display_name) : ''; ?>" class="large-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="room_name"><?php echo esc_html(mb_t('rooms.form.field.room_name')); ?></label></th>
                            <td><input type="text" id="room_name" name="room_name" value="<?php echo $property ? esc_attr($property->room_name) : ''; ?>" class="regular-text" required></td>
                        </tr>
                    </table>
                </div>
                
                <div class="form-section">
                    <h3><?php echo esc_html(mb_t('rooms.form.section.occupancy_pricing')); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><label for="min_stay_days"><?php echo esc_html(mb_t('rooms.form.field.minimum_stay')); ?></label></th>
                            <td>
                                <input type="number" id="min_stay_days" name="min_stay_days" value="<?php echo $property ? esc_attr($property->min_stay_days) : '1'; ?>" class="small-text" min="1">
                                <select name="min_stay_unit">
                                    <option value="日" <?php selected($property ? $property->min_stay_unit : '', '日'); ?>>日</option>
                                    <option value="月" <?php selected($property ? $property->min_stay_unit : '', '月'); ?>>月</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="max_occupants"><?php echo esc_html(mb_t('rooms.form.field.maximum_occupants')); ?></label></th>
                            <td><input type="number" id="max_occupants" name="max_occupants" value="<?php echo $property ? esc_attr($property->max_occupants) : '1'; ?>" class="small-text" min="1" max="10" required></td>
                        </tr>
                        <tr>
                            <th><label for="daily_rent"><?php echo esc_html(mb_t('rooms.form.field.daily_rent')); ?></label></th>
                            <td><input type="number" id="daily_rent" name="daily_rent" value="<?php echo $property ? esc_attr($property->daily_rent) : ''; ?>" class="regular-text" min="0" required></td>
                        </tr>
                    </table>
                </div>
                
                <div class="form-section">
                    <h3><?php echo esc_html(mb_t('rooms.form.section.location_details')); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><label for="address"><?php echo esc_html(mb_t('rooms.form.field.address')); ?></label></th>
                            <td><textarea id="address" name="address" class="large-text" rows="3"><?php echo $property ? esc_textarea($property->address) : ''; ?></textarea></td>
                        </tr>
                        <tr>
                            <th><label for="layout"><?php echo esc_html(mb_t('rooms.form.field.layout')); ?></label></th>
                            <td><input type="text" id="layout" name="layout" value="<?php echo $property ? esc_attr($property->layout) : ''; ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th><label for="floor_area"><?php echo esc_html(mb_t('rooms.form.field.floor_area')); ?></label></th>
                            <td><input type="number" id="floor_area" name="floor_area" value="<?php echo $property ? esc_attr($property->floor_area) : ''; ?>" class="regular-text" step="0.1" min="0"></td>
                        </tr>
                        <tr>
                            <th><label for="structure"><?php echo esc_html(mb_t('rooms.form.field.structure')); ?></label></th>
                            <td><input type="text" id="structure" name="structure" value="<?php echo $property ? esc_attr($property->structure) : ''; ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th><label for="built_year"><?php echo esc_html(mb_t('rooms.form.field.built_year')); ?></label></th>
                            <td><input type="text" id="built_year" name="built_year" value="<?php echo $property ? esc_attr($property->built_year) : ''; ?>" class="regular-text"></td>
                        </tr>
                    </table>
                </div>
                
                <div class="form-section">
                    <h3><?php echo esc_html(mb_t('rooms.form.section.station_access')); ?></h3>
                    
                    <div class="station-group">
                        <h4><?php echo esc_html(mb_t('rooms.form.station.1')); ?></h4>
                        <table class="form-table">
                            <tr>
                                <th><label for="line1"><?php echo esc_html(mb_t('rooms.form.field.line')); ?></label></th>
                                <td><input type="text" id="line1" name="line1" value="<?php echo $property ? esc_attr($property->line1) : ''; ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="station1"><?php echo esc_html(mb_t('rooms.form.field.station')); ?></label></th>
                                <td><input type="text" id="station1" name="station1" value="<?php echo $property ? esc_attr($property->station1) : ''; ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="access1_type"><?php echo esc_html(mb_t('rooms.form.field.access_type')); ?></label></th>
                                <td>
                                    <select id="access1_type" name="access1_type">
                                        <option value=""><?php echo esc_html(mb_t('rooms.form.field.select_placeholder')); ?></option>
                                        <option value="徒歩" <?php selected($property ? $property->access1_type : '', '徒歩'); ?>>徒歩</option>
                                        <option value="バス" <?php selected($property ? $property->access1_type : '', 'バス'); ?>>バス</option>
                                        <option value="自転車" <?php selected($property ? $property->access1_type : '', '自転車'); ?>>自転車</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="access1_time"><?php echo esc_html(mb_t('rooms.form.field.access_time')); ?></label></th>
                                <td><input type="number" id="access1_time" name="access1_time" value="<?php echo $property ? esc_attr($property->access1_time) : ''; ?>" class="small-text" min="0"></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="station-group">
                        <h4><?php echo esc_html(mb_t('rooms.form.station.2')); ?></h4>
                        <table class="form-table">
                            <tr>
                                <th><label for="line2"><?php echo esc_html(mb_t('rooms.form.field.line')); ?></label></th>
                                <td><input type="text" id="line2" name="line2" value="<?php echo $property ? esc_attr($property->line2) : ''; ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="station2"><?php echo esc_html(mb_t('rooms.form.field.station')); ?></label></th>
                                <td><input type="text" id="station2" name="station2" value="<?php echo $property ? esc_attr($property->station2) : ''; ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="access2_type"><?php echo esc_html(mb_t('rooms.form.field.access_type')); ?></label></th>
                                <td>
                                    <select id="access2_type" name="access2_type">
                                        <option value=""><?php echo esc_html(mb_t('rooms.form.field.select_placeholder')); ?></option>
                                        <option value="徒歩" <?php selected($property ? $property->access2_type : '', '徒歩'); ?>>徒歩</option>
                                        <option value="バス" <?php selected($property ? $property->access2_type : '', 'バス'); ?>>バス</option>
                                        <option value="自転車" <?php selected($property ? $property->access2_type : '', '自転車'); ?>>自転車</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="access2_time"><?php echo esc_html(mb_t('rooms.form.field.access_time')); ?></label></th>
                                <td><input type="number" id="access2_time" name="access2_time" value="<?php echo $property ? esc_attr($property->access2_time) : ''; ?>" class="small-text" min="0"></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="station-group">
                        <h4><?php echo esc_html(mb_t('rooms.form.station.3')); ?></h4>
                        <table class="form-table">
                            <tr>
                                <th><label for="line3"><?php echo esc_html(mb_t('rooms.form.field.line')); ?></label></th>
                                <td><input type="text" id="line3" name="line3" value="<?php echo $property ? esc_attr($property->line3) : ''; ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="station3"><?php echo esc_html(mb_t('rooms.form.field.station')); ?></label></th>
                                <td><input type="text" id="station3" name="station3" value="<?php echo $property ? esc_attr($property->station3) : ''; ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="access3_type"><?php echo esc_html(mb_t('rooms.form.field.access_type')); ?></label></th>
                                <td>
                                    <select id="access3_type" name="access3_type">
                                        <option value=""><?php echo esc_html(mb_t('rooms.form.field.select_placeholder')); ?></option>
                                        <option value="徒歩" <?php selected($property ? $property->access3_type : '', '徒歩'); ?>>徒歩</option>
                                        <option value="バス" <?php selected($property ? $property->access3_type : '', 'バス'); ?>>バス</option>
                                        <option value="自転車" <?php selected($property ? $property->access3_type : '', '自転車'); ?>>自転車</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="access3_time"><?php echo esc_html(mb_t('rooms.form.field.access_time')); ?></label></th>
                                <td><input type="number" id="access3_time" name="access3_time" value="<?php echo $property ? esc_attr($property->access3_time) : ''; ?>" class="small-text" min="0"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3><?php echo esc_html(mb_t('rooms.form.section.additional')); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><label for="room_amenities"><?php echo esc_html(mb_t('rooms.form.field.room_amenities')); ?></label></th>
                            <td><textarea id="room_amenities" name="room_amenities" class="large-text" rows="3"><?php echo $property ? esc_textarea($property->room_amenities) : ''; ?></textarea></td>
                        </tr>
                        <tr>
                            <th><label for="is_active"><?php echo esc_html(mb_t('rooms.form.field.status')); ?></label></th>
                            <td>
                                <label>
                                    <input type="checkbox" id="is_active" name="is_active" value="1" <?php checked($property ? $property->is_active : 1, 1); ?>>
                                    <?php echo esc_html(mb_t('status.active')); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Campaign Assignment Section -->
                <div class="form-section">
                    <h3><?php echo esc_html(mb_t('rooms.form.section.campaign_assignment')); ?></h3>
                    
                    <!-- Campaign Assignment Table -->
                    <div id="campaign-assignments-container">
                        <table class="wp-list-table widefat fixed striped" id="campaign-assignments-table">
                            <thead>
                                <tr>
                                    <th scope="col"><?php echo esc_html(mb_t('rooms.form.campaign.table.header.campaign')); ?></th>
                                    <th scope="col"><?php echo esc_html(mb_t('rooms.form.campaign.table.header.period')); ?></th>
                                    <th scope="col"><?php echo esc_html(mb_t('rooms.form.campaign.table.header.duration')); ?></th>
                                    <th scope="col"><?php echo esc_html(mb_t('rooms.form.campaign.table.header.status')); ?></th>
                                    <th scope="col"><?php echo esc_html(mb_t('rooms.form.campaign.table.header.actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody id="campaign-assignments-tbody">
                                <!-- Assignments will be loaded via AJAX -->
                            </tbody>
                        </table>
                        
                        <div id="no-assignments-message" style="display: none;">
                            <p><?php echo esc_html(mb_t('rooms.form.campaign.empty')); ?></p>
                        </div>
                    </div>
                    
                    <!-- Add Assignment Button -->
                    <p class="submit">
                        <button type="button" id="add-campaign-assignment" class="button button-secondary">
                            <?php echo esc_html(mb_t('rooms.form.campaign.add_button')); ?>
                        </button>
                    </p>
                </div>
                
                <!-- Campaign Assignment Modal -->
                <div id="campaign-assignment-modal" class="monthly-booking-modal" style="display: none;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 id="modal-title"><?php echo esc_html(mb_t('rooms.form.campaign.modal.title')); ?></h2>
                            <span class="close-modal">&times;</span>
                        </div>
                        
                        <div class="modal-body">
                            <form id="campaign-assignment-form">
                                <input type="hidden" id="assignment-id" name="assignment_id" value="">
                                <input type="hidden" id="room-id" name="room_id" value="<?php echo esc_attr($property_id); ?>">
                                
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="campaign-select"><?php echo esc_html(mb_t('rooms.form.campaign.table.header.campaign')); ?></label>
                                        </th>
                                        <td>
                                            <select id="campaign-select" name="campaign_id" required>
                                                <option value=""><?php echo esc_html(mb_t('rooms.form.campaign.field.select_campaign')); ?></option>
                                                <!-- Options loaded via AJAX -->
                                            </select>
                                            <p class="description"><?php echo esc_html(mb_t('rooms.form.campaign.field.select_campaign_desc')); ?></p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row">
                                            <label for="start-date"><?php echo esc_html(mb_t('rooms.form.campaign.field.start_date')); ?></label>
                                        </th>
                                        <td>
                                            <input type="date" id="start-date" name="checkin_date" required>
                                            <p class="description"><?php echo esc_html(mb_t('rooms.form.campaign.field.start_date_desc')); ?></p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row">
                                            <label for="end-date"><?php echo esc_html(mb_t('rooms.form.campaign.field.end_date')); ?></label>
                                        </th>
                                        <td>
                                            <input type="date" id="end-date" name="checkout_date" required>
                                            <p class="description"><?php echo esc_html(mb_t('rooms.form.campaign.field.end_date_desc')); ?></p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row">
                                            <label for="is-active"><?php echo esc_html(mb_t('rooms.form.campaign.field.status')); ?></label>
                                        </th>
                                        <td>
                                            <label class="toggle-switch">
                                                <input type="checkbox" id="is-active" name="is_active" value="1" checked>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <span class="toggle-label"><?php echo esc_html(mb_t('rooms.form.campaign.toggle.active')); ?></span>
                                            <p class="description"><?php echo esc_html(mb_t('rooms.form.campaign.toggle.desc')); ?></p>
                                        </td>
                                    </tr>
                                </table>
                                
                                <div id="validation-errors" class="notice notice-error" style="display: none;">
                                    <p></p>
                                </div>
                            </form>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" id="save-assignment" class="button button-primary">
                                <?php echo esc_html(mb_t('rooms.form.campaign.save')); ?>
                            </button>
                            <button type="button" class="button cancel-modal">
                                <?php echo esc_html(mb_t('action.cancel')); ?>
                            </button>
                        </div>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit_property" class="button-primary" value="<?php echo $is_edit ? esc_attr(mb_t('rooms.form.submit.update')) : esc_attr(mb_t('rooms.form.submit.add')); ?>">
                    <a href="<?php echo admin_url('admin.php?page=monthly-room-booking'); ?>" class="button"><?php echo esc_html(mb_t('action.cancel_nav')); ?></a>
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
        
        $rooms = $this->get_all_rooms();
        
        if (empty($rooms)) {
            global $wpdb;
            $rooms_table = $wpdb->prefix . 'monthly_rooms';
            $rooms = $wpdb->get_results("SELECT id, room_id, display_name, room_name, property_name FROM $rooms_table WHERE is_active = 1 ORDER BY property_name, room_name");
            
            if (empty($rooms)) {
                echo '<div class="notice notice-error"><p>' . __('表示できる部屋がありません。先に部屋を登録してください。', 'monthly-booking') . '</p></div>';
                return;
            }
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="monthly-booking-admin-content">
                <div class="calendar-controls">
                    <div class="room-selector">
                        <label for="room_select"><?php _e('部屋選択', 'monthly-booking'); ?>:</label>
                        <select id="room_select" name="room_id" onchange="try { var url = '<?php echo admin_url('admin.php?page=monthly-room-booking-calendar&room_id='); ?>' + this.value; window.location.href = url; } catch(e) { alert('<?php _e('Error selecting room: ', 'monthly-booking'); ?>' + e.message); }">
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
add_action('wp_ajax_mb_get_rooms', function () {
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    check_ajax_referer('monthly_booking_admin', 'nonce');
    global $wpdb;
    $q = isset($_POST['q']) ? sanitize_text_field($_POST['q']) : '';
    $table = $wpdb->prefix . 'monthly_rooms';
    if ($q) {
        $like = '%' . $wpdb->esc_like($q) . '%';
        $rows = $wpdb->get_results($wpdb->prepare("SELECT id, display_name AS name FROM {$table} WHERE display_name LIKE %s OR room_id LIKE %s LIMIT 50", $like, $like), ARRAY_A);
    } else {
        $rows = $wpdb->get_results("SELECT id, display_name AS name FROM {$table} ORDER BY id DESC LIMIT 50", ARRAY_A);
    }
    if (!$rows) $rows = [];
    wp_send_json_success($rows);
});

add_action('wp_ajax_mb_get_campaigns', function () {
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    check_ajax_referer('monthly_booking_admin', 'nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'monthly_campaigns';
    $rows = $wpdb->get_results("SELECT id, campaign_name AS name FROM {$table} WHERE is_active=1 ORDER BY id DESC LIMIT 100", ARRAY_A);
    if (!$rows) $rows = [];
    wp_send_json_success($rows);
add_action('wp_ajax_create_campaign', function () {
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    check_ajax_referer('monthly_booking_admin', 'nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'monthly_campaigns';
    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $discount_type = isset($_POST['discount_type']) ? sanitize_text_field($_POST['discount_type']) : '';
    $discount_value = isset($_POST['discount_value']) ? floatval($_POST['discount_value']) : 0;
    $period_type = isset($_POST['period_type']) ? sanitize_text_field($_POST['period_type']) : 'fixed';
    $relative_days = null;
    if ($period_type === 'first_month_30d') { $period_type = 'checkin_relative'; $relative_days = 30; }
    elseif ($period_type === 'checkin_relative') {
        $rd = isset($_POST['relative_days']) ? intval($_POST['relative_days']) : 0;
        if ($rd >= 1 && $rd <= 30) $relative_days = $rd;
    }
    $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : null;
    $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : null;
    $contracts = isset($_POST['contract_types']) ? (array) $_POST['contract_types'] : (isset($_POST['contract_types']) ? (array) $_POST['contract_types'] : array());
    if (empty($contracts) && isset($_POST['contract_types'])) $contracts = (array) $_POST['contract_types'];
    if (empty($contracts) && isset($_POST['contract_types'])) $contracts = array_map('sanitize_text_field', (array)$_POST['contract_types']);
    $contracts = isset($_POST['contract_types']) ? array_map('sanitize_text_field', (array)$_POST['contract_types']) : array();
    $allowed_contracts = array('SS','S','M','L','ALL');
    $contracts = array_values(array_intersect(array_map('strtoupper', $contracts), $allowed_contracts));
    $target_plan = empty($contracts) ? 'ALL' : implode(',', $contracts);
    $is_active = isset($_POST['is_active']) ? 1 : 1;

    if ($name === '' || !in_array($discount_type, array('percentage','fixed','flatrate'), true)) {
        wp_send_json_error('invalid parameters', 400);
    }
    if ($period_type === 'fixed') {
        if (empty($start_date) || empty($end_date) || $start_date >= $end_date) {
            wp_send_json_error('invalid fixed period', 400);
        }
    }
    if ($period_type !== 'fixed') {
        $start_date = $start_date ?: current_time('Y-m-d');
        $end_date = $end_date ?: '2099-12-31';
    }
    $data = array(
        'campaign_name' => $name,
        'discount_type' => $discount_type,
        'discount_value' => $discount_value,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'is_active' => $is_active,
        'target_plan' => $target_plan,
        'period_type' => $period_type,
        'relative_days' => $relative_days,
        'created_at' => current_time('mysql'),
        'updated_at' => current_time('mysql'),
    );
    $formats = array('%s','%s','%f','%s','%s','%d','%s','%s','%d','%s','%s');
    $ok = $wpdb->insert($table, $data);
    if (!$ok) wp_send_json_error($wpdb->last_error ?: 'db error', 500);
    wp_send_json_success(array('id' => $wpdb->insert_id, 'message' => 'キャンペーンを作成しました'));
});

add_action('wp_ajax_update_campaign', function () {
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    check_ajax_referer('monthly_booking_admin', 'nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'monthly_campaigns';
    $cid = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 0;
    if ($cid <= 0) wp_send_json_error('invalid id', 400);

    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $discount_type = isset($_POST['discount_type']) ? sanitize_text_field($_POST['discount_type']) : '';
    $discount_value = isset($_POST['discount_value']) ? floatval($_POST['discount_value']) : 0;
    $period_type = isset($_POST['period_type']) ? sanitize_text_field($_POST['period_type']) : 'fixed';
    $relative_days = null;
    if ($period_type === 'first_month_30d') { $period_type = 'checkin_relative'; $relative_days = 30; }
    elseif ($period_type === 'checkin_relative') {
        $rd = isset($_POST['relative_days']) ? intval($_POST['relative_days']) : 0;
        if ($rd >= 1 && $rd <= 30) $relative_days = $rd;
    }
    $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : null;
    $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : null;
    $contracts = isset($_POST['contract_types']) ? array_map('sanitize_text_field', (array)$_POST['contract_types']) : array();
    $allowed_contracts = array('SS','S','M','L','ALL');
    $contracts = array_values(array_intersect(array_map('strtoupper', $contracts), $allowed_contracts));
    $target_plan = empty($contracts) ? 'ALL' : implode(',', $contracts);
    $is_active = isset($_POST['is_active']) ? 1 : 1;

    if ($name === '' || !in_array($discount_type, array('percentage','fixed','flatrate'), true)) {
        wp_send_json_error('invalid parameters', 400);
    }
    if ($period_type === 'fixed') {
        if (empty($start_date) || empty($end_date) || $start_date >= $end_date) {
            wp_send_json_error('invalid fixed period', 400);
        }
    }
    if ($period_type !== 'fixed') {
        $start_date = $start_date ?: current_time('Y-m-d');
        $end_date = $end_date ?: '2099-12-31';
    }
    $data = array(
        'campaign_name' => $name,
        'discount_type' => $discount_type,
        'discount_value' => $discount_value,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'is_active' => $is_active,


        'target_plan' => $target_plan,


        'period_type' => $period_type,
        'relative_days' => $relative_days,
        'updated_at' => current_time('mysql'),
    );
    $ok = $wpdb->update($table, $data, array('id' => $cid), null, array('%d'));
    if ($ok === false) wp_send_json_error($wpdb->last_error ?: 'db error', 500);
    wp_send_json_success(array('id' => $cid, 'message' => 'キャンペーンを更新しました'));
});

add_action('wp_ajax_delete_campaign', function () {
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    check_ajax_referer('monthly_booking_admin', 'nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'monthly_campaigns';
    $cid = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 0;
    if ($cid <= 0) wp_send_json_error('invalid id', 400);
    $ok = $wpdb->delete($table, array('id' => $cid), array('%d'));
    if ($ok === false) wp_send_json_error($wpdb->last_error ?: 'db error', 500);
    wp_send_json_success(array('id' => $cid, 'message' => 'キャンペーンを削除しました'));
});

add_action('wp_ajax_toggle_campaign_status', function () {
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    check_ajax_referer('monthly_booking_admin', 'nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'monthly_campaigns';
    $cid = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 0;
    $is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 0;
    if ($cid <= 0) wp_send_json_error('invalid id', 400);
    $ok = $wpdb->update($table, array('is_active' => $is_active ? 0 : 1, 'updated_at' => current_time('mysql')), array('id' => $cid));
    if ($ok === false) wp_send_json_error($wpdb->last_error ?: 'db error', 500);
    wp_send_json_success(array('id' => $cid, 'message' => 'ステータスを更新しました', 'is_active' => $is_active ? 0 : 1));
});add_action('wp_ajax_toggle_campaign', function () {
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    check_ajax_referer('monthly_booking_admin', 'nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'monthly_campaigns';
    $cid = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 0;
    $is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 0;
    if ($cid <= 0) wp_send_json_error('invalid id', 400);
    $ok = $wpdb->update($table, array('is_active' => $is_active ? 1 : 0, 'updated_at' => current_time('mysql')), array('id' => $cid));
    if ($ok === false) wp_send_json_error($wpdb->last_error ?: 'db error', 500);
    wp_send_json_success(array('id' => $cid, 'message' => 'ステータスを更新しました', 'is_active' => $is_active ? 1 : 0));
});

});
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
        
        $sql = "SELECT room_id, display_name, room_name, property_name 
                FROM $rooms_table 
                WHERE is_active = 1 
                ORDER BY property_name, room_name";
        
        $results = $wpdb->get_results($sql);
        
        if ($wpdb->last_error) {
            return array();
        }
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
        error_log('[mb-admin] reached admin_page_booking_registration');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'monthly-booking'));
        }
        
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        if ($this->is_cpt_mode()) {
            if ($action === 'delete') {
                $post_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
                if ($post_id) {
                    check_admin_referer('mrb_booking_delete_' . $post_id);
                    wp_trash_post($post_id);
                }
                wp_redirect(admin_url('admin.php?page=monthly-room-booking-registration&message=deleted'));
                exit;
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mrb_booking_submit'])) {
                check_admin_referer('mrb_booking_save');
                $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
                $title = isset($_POST['guest_name']) ? sanitize_text_field($_POST['guest_name']) : '';
                if ($post_id) {
                    wp_update_post(array(
                        'ID' => $post_id,
                        'post_title' => $title
                    ));
                } else {
                    $post_id = wp_insert_post(array(
                        'post_type' => 'mrb_booking',
                        'post_status' => 'publish',
                        'post_title' => $title
                    ), true);
                }
                if (!is_wp_error($post_id)) {
                    $meta_keys = array(
                        'room_id','user_id','guest_name','guest_email','guest_phone',
                        'checkin_date','checkout_date','nights','guests','rate_id','campaign_id',
                        'options_json','subtotal','discount','total','status','notes'
                    );
                    foreach ($meta_keys as $k) {
                        if (isset($_POST[$k])) {
                            update_post_meta($post_id, $k, $_POST[$k]);
                        }
                    }
                }
                wp_redirect(admin_url('admin.php?page=monthly-room-booking-registration&message=saved'));
                exit;
            }
            if ($action === 'add' || ($action === 'edit' && isset($_GET['id']))) {
                $edit_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
                $guest_name = $edit_id ? get_post_meta($edit_id, 'guest_name', true) : '';
                $guest_email = $edit_id ? get_post_meta($edit_id, 'guest_email', true) : '';
                $guest_phone = $edit_id ? get_post_meta($edit_id, 'guest_phone', true) : '';
                $room_id = $edit_id ? get_post_meta($edit_id, 'room_id', true) : '';
                $checkin_date = $edit_id ? get_post_meta($edit_id, 'checkin_date', true) : '';
                $checkout_date = $edit_id ? get_post_meta($edit_id, 'checkout_date', true) : '';
                $nights = $edit_id ? get_post_meta($edit_id, 'nights', true) : '';
                $guests = $edit_id ? get_post_meta($edit_id, 'guests', true) : '';
                $rate_id = $edit_id ? get_post_meta($edit_id, 'rate_id', true) : '';
                $campaign_id = $edit_id ? get_post_meta($edit_id, 'campaign_id', true) : '';
                $subtotal = $edit_id ? get_post_meta($edit_id, 'subtotal', true) : '';
                $discount = $edit_id ? get_post_meta($edit_id, 'discount', true) : '';
                $total = $edit_id ? get_post_meta($edit_id, 'total', true) : '';
                $status = $edit_id ? get_post_meta($edit_id, 'status', true) : 'pending';
                $notes = $edit_id ? get_post_meta($edit_id, 'notes', true) : '';
                ?>
                <div class="wrap">
                    <h1><?php echo $action === 'edit' ? __('予約編集', 'monthly-booking') : __('新規予約追加', 'monthly-booking'); ?></h1>
                    <form method="post" action="">
                        <?php wp_nonce_field('mrb_booking_save'); ?>
                        <input type="hidden" name="post_id" value="<?php echo esc_attr($edit_id); ?>">
                        <table class="form-table">
                            <tr><th><?php _e('部屋ID', 'monthly-booking'); ?></th><td><input type="number" name="room_id" value="<?php echo esc_attr($room_id); ?>" class="regular-text"></td></tr>
                            <tr><th><?php _e('顧客名', 'monthly-booking'); ?></th><td><input type="text" name="guest_name" value="<?php echo esc_attr($guest_name); ?>" class="regular-text"></td></tr>
                            <tr><th><?php _e('メール', 'monthly-booking'); ?></th><td><input type="email" name="guest_email" value="<?php echo esc_attr($guest_email); ?>" class="regular-text"></td></tr>
                            <tr><th><?php _e('電話', 'monthly-booking'); ?></th><td><input type="text" name="guest_phone" value="<?php echo esc_attr($guest_phone); ?>" class="regular-text"></td></tr>
                            <tr><th><?php _e('チェックイン', 'monthly-booking'); ?></th><td><input type="date" name="checkin_date" value="<?php echo esc_attr($checkin_date); ?>"></td></tr>
                            <tr><th><?php _e('チェックアウト', 'monthly-booking'); ?></th><td><input type="date" name="checkout_date" value="<?php echo esc_attr($checkout_date); ?>"></td></tr>
                            <tr><th><?php _e('泊数', 'monthly-booking'); ?></th><td><input type="number" name="nights" value="<?php echo esc_attr($nights); ?>" class="small-text"></td></tr>
                            <tr><th><?php _e('人数', 'monthly-booking'); ?></th><td><input type="number" name="guests" value="<?php echo esc_attr($guests); ?>" class="small-text"></td></tr>
                            <tr><th><?php _e('レートID', 'monthly-booking'); ?></th><td><input type="number" name="rate_id" value="<?php echo esc_attr($rate_id); ?>" class="regular-text"></td></tr>
                            <tr><th><?php _e('キャンペーンID', 'monthly-booking'); ?></th><td><input type="number" name="campaign_id" value="<?php echo esc_attr($campaign_id); ?>" class="regular-text"></td></tr>
                            <tr><th><?php _e('小計(¥)', 'monthly-booking'); ?></th><td><input type="number" name="subtotal" value="<?php echo esc_attr($subtotal); ?>" class="regular-text"></td></tr>
                            <tr><th><?php _e('割引(¥)', 'monthly-booking'); ?></th><td><input type="number" name="discount" value="<?php echo esc_attr($discount); ?>" class="regular-text"></td></tr>
                            <tr><th><?php _e('合計(¥)', 'monthly-booking'); ?></th><td><input type="number" name="total" value="<?php echo esc_attr($total); ?>" class="regular-text"></td></tr>
                            <tr><th><?php _e('ステータス', 'monthly-booking'); ?></th>
                                <td>
                                    <select name="status">
                                        <option value="pending" <?php selected($status, 'pending'); ?>>pending</option>
                                        <option value="confirmed" <?php selected($status, 'confirmed'); ?>>confirmed</option>
                                        <option value="canceled" <?php selected($status, 'canceled'); ?>>canceled</option>
                                    </select>
                                </td>
                            </tr>
                            <tr><th><?php _e('メモ', 'monthly-booking'); ?></th><td><textarea name="notes" class="large-text" rows="3"><?php echo esc_textarea($notes); ?></textarea></td></tr>
                        </table>
                        <p class="submit">
                            <button type="submit" name="mrb_booking_submit" class="button button-primary"><?php _e('保存', 'monthly-booking'); ?></button>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=monthly-room-booking-registration')); ?>" class="button"><?php _e('キャンセル', 'monthly-booking'); ?></a>
                        </p>
                    </form>
                </div>
                <?php
                return;
            }
            $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
            $q = new WP_Query(array(
                'post_type' => 'mrb_booking',
                'posts_per_page' => 20,
                'paged' => $paged,
                'orderby' => 'date',
                'order' => 'DESC',
            ));
            ?>
            <div class="wrap">
                <?php if (isset($_GET['message'])) { $msg = sanitize_text_field($_GET['message']); if ($msg === 'saved' || $msg === 'updated' || $msg === 'deleted') { echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $msg === 'deleted' ? __('予約を削除しました。','monthly-booking') : ($msg === 'updated' ? __('予約を更新しました。','monthly-booking') : __('予約を保存しました。','monthly-booking') ) ) . '</p></div>'; } } ?>

                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                <div class="monthly-booking-admin-content">
                    <div class="reservation-header" style="margin-bottom: 20px;">
                        <a href="<?php echo admin_url('admin.php?page=monthly-room-booking-registration&action=add'); ?>" class="button button-primary"><?php _e('新規予約追加', 'monthly-booking'); ?></a>
                    </div>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('ID', 'monthly-booking'); ?></th>
                                <th><?php _e('部屋', 'monthly-booking'); ?></th>
                                <th><?php _e('顧客名', 'monthly-booking'); ?></th>
                                <th><?php _e('チェックイン', 'monthly-booking'); ?></th>
                                <th><?php _e('チェックアウト', 'monthly-booking'); ?></th>
                                <th><?php _e('ステータス', 'monthly-booking'); ?></th>
                                <th><?php _e('合計', 'monthly-booking'); ?></th>
                                <th><?php _e('操作', 'monthly-booking'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($q->have_posts()): ?>
                            <?php while ($q->have_posts()): $q->the_post(); $pid = get_the_ID(); ?>
                                <tr>
                                    <td><?php echo esc_html($pid); ?></td>
                                    <td><?php echo esc_html(get_post_meta($pid, 'room_id', true)); ?></td>
                                    <td><?php echo esc_html(get_post_meta($pid, 'guest_name', true)); ?></td>
                                    <td><?php echo esc_html(get_post_meta($pid, 'checkin_date', true)); ?></td>
                                    <td><?php echo esc_html(get_post_meta($pid, 'checkout_date', true)); ?></td>
                                    <td><?php echo esc_html(get_post_meta($pid, 'status', true)); ?></td>
                                    <td><?php $t = absint(get_post_meta($pid, 'total', true)); echo $t ? '¥' . number_format_i18n($t) : '¥0'; ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=monthly-room-booking-registration&action=edit&id=' . $pid); ?>"><?php _e('編集', 'monthly-booking'); ?></a> |
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=monthly-room-booking-registration&action=delete&id=' . $pid), 'mrb_booking_delete_' . $pid); ?>" onclick="return confirm('<?php _e('本当に削除しますか？', 'monthly-booking'); ?>')"><?php _e('削除', 'monthly-booking'); ?></a>
                                    </td>
                                </tr>
                            <?php endwhile; wp_reset_postdata(); ?>
                        <?php else: ?>
                            <tr><td colspan="8" style="text-align:center;"><?php _e('予約がありません。', 'monthly-booking'); ?></td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php
            return;
        }

        
        global $wpdb;
        $table_name = $wpdb->prefix . 'monthly_reservations';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            error_log('[mb-admin] Creating reservations table');
            $this->create_reservations_table();
        }
        
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        $reservation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        error_log('[mb-admin] Action: ' . $action . ', calling render_working_reservation_list');
        
        switch ($action) {
            case 'add':
                $this->render_reservation_form();
                break;
            case 'edit':
                $this->render_reservation_form($reservation_id);
                break;
            case 'delete':
                $this->handle_reservation_delete($reservation_id);
                break;
            default:
                $this->render_reservation_list();
                break;
        }
    }
    
    private function create_reservations_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_reservations';
        $charset_collate = $wpdb->get_charset_collate();
        
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
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    private function render_working_reservation_list() {
        error_log('[mb-admin] render_working_reservation_list called');
        
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_reservations';
        $reservations = $wpdb->get_results(
            "SELECT r.*, rm.room_name, rm.property_name 
             FROM $table_name r 
             LEFT JOIN {$wpdb->prefix}monthly_rooms rm ON r.room_id = rm.room_id 
             ORDER BY r.created_at DESC"
        );
        
        error_log('[mb-admin] Found ' . count($reservations) . ' reservations');
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="monthly-booking-admin-content">
                <div class="reservation-header" style="margin-bottom: 20px;">
                    <a href="<?php echo admin_url('admin.php?page=monthly-room-booking-registration&action=add'); ?>" 
                       class="button button-primary"><?php _e('新規予約追加', 'monthly-booking'); ?></a>
                </div>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('ID', 'monthly-booking'); ?></th>
                            <th><?php _e('部屋', 'monthly-booking'); ?></th>
                            <th><?php _e('顧客名', 'monthly-booking'); ?></th>
                            <th><?php _e('チェックイン', 'monthly-booking'); ?></th>
                            <th><?php _e('チェックアウト', 'monthly-booking'); ?></th>
                            <th><?php _e('ステータス', 'monthly-booking'); ?></th>
                            <th><?php _e('操作', 'monthly-booking'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reservations)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">
                                <?php _e('予約がありません。新規予約を追加してください。', 'monthly-booking'); ?>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?php echo esc_html($reservation->id); ?></td>
                            <td><?php echo esc_html($reservation->room_name ?: 'N/A'); ?></td>
                            <td><?php echo esc_html($reservation->guest_name); ?></td>
                            <td><?php echo esc_html($reservation->checkin_date); ?></td>
                            <td><?php echo esc_html($reservation->checkout_date); ?></td>
                            <td><?php echo esc_html($reservation->status); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=monthly-room-booking-registration&action=edit&id=' . $reservation->id); ?>"><?php _e('編集', 'monthly-booking'); ?></a> |
                                <a href="<?php echo admin_url('admin.php?page=monthly-room-booking-registration&action=delete&id=' . $reservation->id); ?>" 
                                   onclick="return confirm('<?php _e('本当に削除しますか？', 'monthly-booking'); ?>')"><?php _e('削除', 'monthly-booking'); ?></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 20px; padding: 15px; background: #f0f8ff; border-left: 4px solid #0073aa;">
                    <h3 style="margin-top: 0;">🚀 予約登録MVP v1.7.0-alpha</h3>
                    <p><strong>機能確認:</strong> 予約CRUD機能が正常に動作しています。</p>
                    <p><strong>テーブル:</strong> <?php echo $table_name; ?> が作成されました。</p>
                    <p><strong>次のステップ:</strong> 新規予約を追加してカレンダー連携をテストしてください。</p>
                </div>
            </div>
        </div>
        <?php
    }
    
    
    private function render_feature_disabled_notice() {
        $this->render_reservation_list();
    }
    
    private function render_reservation_list() {
        if (!class_exists('MonthlyBooking_Reservation_Service')) {
            require_once plugin_dir_path(__FILE__) . 'reservation-service.php';
        }
        
        $service = new MonthlyBooking_Reservation_Service();
        $page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
        $result = $service->get_reservations($page, 20);
        
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="monthly-booking-admin-content">
                <div class="reservation-header" style="margin-bottom: 20px;">
                    <a href="<?php echo admin_url('admin.php?page=monthly-room-booking-registration&action=add'); ?>" 
                       class="button button-primary"><?php _e('新規予約追加', 'monthly-booking'); ?></a>
                </div>
                
                <?php if (empty($result['reservations'])): ?>
                    <div class="notice notice-info">
                        <p><?php _e('予約がありません。新規予約を追加してください。', 'monthly-booking'); ?></p>
                    </div>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th scope="col"><?php _e('ID', 'monthly-booking'); ?></th>
                                <th scope="col"><?php _e('部屋', 'monthly-booking'); ?></th>
                                <th scope="col"><?php _e('顧客名', 'monthly-booking'); ?></th>
                                <th scope="col"><?php _e('メール', 'monthly-booking'); ?></th>
                                <th scope="col"><?php _e('チェックイン', 'monthly-booking'); ?></th>
                                <th scope="col"><?php _e('チェックアウト', 'monthly-booking'); ?></th>
                                <th scope="col"><?php _e('料金', 'monthly-booking'); ?></th>
                                <th scope="col"><?php _e('ステータス', 'monthly-booking'); ?></th>
                                <th scope="col"><?php _e('操作', 'monthly-booking'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="mbp-reservations-body">
                            <?php foreach ($result['reservations'] as $reservation): ?>
                            <tr>
                                <td><?php echo esc_html($reservation->id); ?></td>
                                <td><?php echo esc_html($reservation->property_name . ' - ' . $reservation->room_name); ?></td>
                                <td><?php echo esc_html($reservation->guest_name); ?></td>
                                <td><?php echo esc_html($reservation->guest_email); ?></td>
                                <td><?php echo esc_html($reservation->checkin_date); ?></td>
                                <td><?php echo esc_html($reservation->checkout_date); ?></td>
                                <td><?php echo esc_html('¥' . number_format($reservation->total_price)); ?></td>
                                <td>
                                    <span class="status-<?php echo esc_attr($reservation->status); ?>">
                                        <?php echo esc_html($this->get_status_label($reservation->status)); ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button"
                                            class="button button-small mbp-reservation-edit"
                                            data-id="<?php echo esc_attr($reservation->id); ?>"
                                            data-reservation-id="<?php echo esc_attr($reservation->id); ?>"
                                            <?php if (isset($reservation->room_id)) : ?>data-room-id="<?php echo esc_attr($reservation->room_id); ?>"<?php endif; ?>
                                            data-start="<?php echo esc_attr($reservation->checkin_date); ?>"
                                            data-end="<?php echo esc_attr($reservation->checkout_date); ?>"
                                            data-guest-name="<?php echo esc_attr($reservation->guest_name); ?>"
                                            data-guest-email="<?php echo esc_attr($reservation->guest_email); ?>"
                                            data-guest-phone="<?php echo isset($reservation->guest_phone) ? esc_attr($reservation->guest_phone) : ''; ?>"
                                            data-status="<?php echo isset($reservation->status) ? esc_attr($reservation->status) : ''; ?>"
                                            data-notes="<?php echo isset($reservation->notes) ? esc_attr($reservation->notes) : ''; ?>">
                                        <?php _e('編集', 'monthly-booking'); ?>
                                    </button>
                                    <button type="button"
                                            class="button button-small button-link-delete mbp-reservation-delete"
                                            data-id="<?php echo esc_attr($reservation->id); ?>"
                                            data-reservation-id="<?php echo esc_attr($reservation->id); ?>">
                                        <?php _e('削除', 'monthly-booking'); ?>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if ($result['total_pages'] > 1): ?>
                        <div class="tablenav">
                            <div class="tablenav-pages">
                                <?php
                                $page_links = paginate_links(array(
                                    'base' => add_query_arg('paged', '%#%'),
                                    'format' => '',
                                    'prev_text' => __('&laquo;'),
                                    'next_text' => __('&raquo;'),
                                    'total' => $result['total_pages'],
                                    'current' => $page
                                ));
                                echo $page_links;
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    private function render_reservation_form($reservation_id = 0) {
        $reservation = null;
        if ($reservation_id) {
            if (!class_exists('MonthlyBooking_Reservation_Service')) {
                require_once plugin_dir_path(__FILE__) . 'reservation-service.php';
            }
            $service = new MonthlyBooking_Reservation_Service();
            $reservation = $service->get_reservation($reservation_id);
            
            if (!$reservation) {
                wp_die(__('予約が見つかりません。', 'monthly-booking'));
            }
        }
        
        wp_enqueue_script('monthly-booking-admin-form', plugin_dir_url(__FILE__) . '../assets/admin-form.js', array('jquery'), '1.7.0', true);
        wp_localize_script('monthly-booking-admin-form', 'monthlyBookingAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'reservationsNonce' => wp_create_nonce('mbp_reservations_nonce'),
            'strings' => array(
                'saving' => __('保存中...', 'monthly-booking'),
                'saveSuccess' => __('予約が保存されました。', 'monthly-booking'),
                'saveError' => __('保存に失敗しました。', 'monthly-booking')
            )
        ));
        wp_localize_script('monthly-booking-admin-form', 'monthlyBookingAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('monthly_booking_nonce')
        ));
        
        ?>
        <div class="wrap">
            <h1><?php echo $reservation_id ? __('予約編集', 'monthly-booking') : __('新規予約追加', 'monthly-booking'); ?></h1>
            
            <form id="reservation-form" method="post" novalidate>
                <?php wp_nonce_field('mbp_reservations_form', 'reservation_nonce'); ?>
                <input type="hidden" name="reservation_id" value="<?php echo esc_attr($reservation_id); ?>">
                
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="room_id"><?php _e('部屋', 'monthly-booking'); ?> <span class="required" style="color: red;">*</span></label>
                        </th>
                        <td>
                            <select id="room_id" name="room_id" required aria-describedby="room_id_error" class="regular-text">
                                <option value=""><?php _e('部屋を選択', 'monthly-booking'); ?></option>
                                <?php
                                global $wpdb;
                                $rooms = $wpdb->get_results("SELECT room_id, room_name, property_name FROM {$wpdb->prefix}monthly_rooms WHERE is_active = 1 ORDER BY property_name, room_name");
                                foreach ($rooms as $room):
                                ?>
                                <option value="<?php echo esc_attr($room->room_id); ?>" 
                                        <?php selected($reservation ? $reservation->room_id : '', $room->room_id); ?>>
                                    <?php echo esc_html($room->property_name . ' - ' . $room->room_name); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="room_id_error" class="error-message" aria-live="polite" style="color: red; margin-top: 5px;"></div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="guest_name"><?php _e('ゲスト名', 'monthly-booking'); ?> <span class="required" style="color: red;">*</span></label>
                        </th>
                        <td>
                            <input type="text" id="guest_name" name="guest_name" 
                                   value="<?php echo esc_attr($reservation ? $reservation->guest_name : ''); ?>" 
                                   required aria-describedby="guest_name_error" class="regular-text">
                            <div id="guest_name_error" class="error-message" aria-live="polite" style="color: red; margin-top: 5px;"></div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="guest_email"><?php _e('メールアドレス', 'monthly-booking'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="guest_email" name="guest_email" 
                                   value="<?php echo esc_attr($reservation ? $reservation->guest_email : ''); ?>" 
                                   aria-describedby="guest_email_error" class="regular-text">
                            <div id="guest_email_error" class="error-message" aria-live="polite" style="color: red; margin-top: 5px;"></div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="checkin_date"><?php _e('チェックイン日', 'monthly-booking'); ?> <span class="required" style="color: red;">*</span></label>
                        </th>
                        <td>
                            <input type="date" id="checkin_date" name="checkin_date" 
                                   value="<?php echo esc_attr($reservation ? $reservation->checkin_date : ''); ?>" 
                                   required aria-describedby="checkin_date_error">
                            <div id="checkin_date_error" class="error-message" aria-live="polite" style="color: red; margin-top: 5px;"></div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="checkout_date"><?php _e('チェックアウト日', 'monthly-booking'); ?> <span class="required" style="color: red;">*</span></label>
                        </th>
                        <td>
                            <input type="date" id="checkout_date" name="checkout_date" 
                                   value="<?php echo esc_attr($reservation ? $reservation->checkout_date : ''); ?>" 
                                   required aria-describedby="checkout_date_error">
                            <div id="checkout_date_error" class="error-message" aria-live="polite" style="color: red; margin-top: 5px;"></div>
                        </td>
                    </tr>
                    
                    
                    <?php if ($reservation_id): ?>
                    <tr>
                        <th scope="row">
                            <label for="status"><?php _e('ステータス', 'monthly-booking'); ?></label>
                        </th>
                        <td>
                            <select id="status" name="status" class="regular-text">
                                <option value="draft" <?php selected($reservation->status, 'draft'); ?>><?php _e('下書き', 'monthly-booking'); ?></option>
                                <option value="confirmed" <?php selected($reservation->status, 'confirmed'); ?>><?php _e('確定', 'monthly-booking'); ?></option>
                                <option value="canceled" <?php selected($reservation->status, 'canceled'); ?>><?php _e('キャンセル', 'monthly-booking'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <?php endif; ?>
                    
                    <tr>
                        <th scope="row">
                            <label for="notes"><?php _e('備考', 'monthly-booking'); ?></label>
                        </th>
                        <td>
                            <textarea id="notes" name="notes" rows="4" class="large-text"><?php echo esc_textarea($reservation ? $reservation->notes : ''); ?></textarea>
                        </td>
                    </tr>
                </table>
                
                <div style="margin-top:20px; padding-top:10px; border-top:1px solid #ddd;">
                    <button type="button" id="recalculate_estimate" class="button"><?php _e('料金再計算', 'monthly-booking'); ?></button>
                    <div id="estimate-result-admin" style="margin-top:12px; display:none;"></div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button button-primary" 
                           value="<?php echo $reservation_id ? __('更新', 'monthly-booking') : __('追加', 'monthly-booking'); ?>">
                    <a href="<?php echo admin_url('admin.php?page=monthly-room-booking-registration'); ?>" 
                       class="button"><?php _e('キャンセル', 'monthly-booking'); ?></a>
                </p>
            </form>
        </div>
        
        <style>
        .error-message:empty {
            display: none;
        }
        .required {
            color: red;
        }
        .status-draft {
            color: #d63638;
        }
        .status-confirmed {
            color: #00a32a;
        }
        .status-canceled {
            color: #646970;
        }
        </style>
        <?php
    }
    
    private function handle_reservation_delete($reservation_id) {
        if (!$reservation_id) {
            wp_die(__('無効な予約IDです。', 'monthly-booking'));
        }
        
        if (!wp_verify_nonce($_GET['_wpnonce'], 'delete_reservation_' . $reservation_id)) {
            wp_die(__('セキュリティチェックに失敗しました。', 'monthly-booking'));
        }
        
        if (!class_exists('MonthlyBooking_Reservation_Service')) {
            require_once plugin_dir_path(__FILE__) . 'reservation-service.php';
        }
        
        $service = new MonthlyBooking_Reservation_Service();
        $result = $service->delete_reservation($reservation_id);
        
        if (is_wp_error($result)) {
            wp_die($result->get_error_message());
        }
        
        wp_redirect(admin_url('admin.php?page=monthly-room-booking-registration&deleted=1'));
        exit;
    }
    
    private function get_status_label($status) {
        $labels = array(
            'draft' => __('下書き', 'monthly-booking'),
            'confirmed' => __('確定', 'monthly-booking'),
            'canceled' => __('キャンセル', 'monthly-booking')
        );
        
        return isset($labels[$status]) ? $labels[$status] : $status;
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
        if ($this->is_cpt_mode()) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], array('create_campaign','edit_campaign'), true)) {
                check_admin_referer('mrb_campaign_save');
                $post_id = isset($_POST['campaign_id']) ? absint($_POST['campaign_id']) : 0;
                $title = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
                if ($post_id) {
                    wp_update_post(array('ID' => $post_id, 'post_title' => $title));
                } else {
                    $post_id = wp_insert_post(array(
                        'post_type' => 'mrb_campaign',
                        'post_status' => 'publish',
                        'post_title' => $title
                    ), true);
                }
                if (!is_wp_error($post_id)) {
                    $meta = array(
                        'type' => isset($_POST['campaign_type']) ? sanitize_text_field($_POST['campaign_type']) : '',
                        'amount' => isset($_POST['discount_value']) ? absint($_POST['discount_value']) : 0,
                        'start_date' => isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '',
                        'end_date' => isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '',
                        'room_ids_json' => isset($_POST['room_ids_json']) ? sanitize_text_field($_POST['room_ids_json']) : '',
                        'is_active' => isset($_POST['is_active']) ? 1 : 0,
                        'priority' => isset($_POST['priority']) ? absint($_POST['priority']) : 0
                    );
                    foreach ($meta as $k => $v) {
                        update_post_meta($post_id, $k, $v);
                    }
                }
                echo '<div class="notice notice-success"><p>' . esc_html__('キャンペーンを保存しました', 'monthly-booking') . '</p></div>';
            }
            $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
            $q = new WP_Query(array(
                'post_type' => 'mrb_campaign',
                'posts_per_page' => 20,
                'paged' => $paged,
                'orderby' => 'date',
                'order' => 'DESC',
            ));
            ?>
            <div class="wrap">
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                <div class="monthly-booking-admin-content">
                    <div style="margin-bottom: 20px;">
                        <a href="#" class="button button-primary" onclick="document.getElementById('mrb-campaign-form').style.display='block';return false;"><?php _e('新規作成', 'monthly-booking'); ?></a>
                    </div>
                    <form id="mrb-campaign-form" method="post" action="" style="display:none; margin-bottom:20px;">
                        <?php wp_nonce_field('mrb_campaign_save'); ?>
                        <input type="hidden" name="action" value="create_campaign">
                        <table class="form-table">
                            <tr><th><?php _e('キャンペーン名', 'monthly-booking'); ?></th><td><input type="text" name="name" class="regular-text" required></td></tr>
                            <tr><th><?php _e('タイプ', 'monthly-booking'); ?></th>
                                <td>
                                    <select name="campaign_type" required>
                                        <option value="immediate"><?php _e('即入居割', 'monthly-booking'); ?></option>
                                        <option value="earlybird"><?php _e('早割', 'monthly-booking'); ?></option>
                                        <option value="flatrate"><?php _e('定額', 'monthly-booking'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr><th><?php _e('割引値', 'monthly-booking'); ?></th><td><input type="number" name="discount_value" class="regular-text" min="0"></td></tr>
                            <tr><th><?php _e('開始日', 'monthly-booking'); ?></th><td><input type="date" name="start_date" required></td></tr>
                            <tr><th><?php _e('終了日', 'monthly-booking'); ?></th><td><input type="date" name="end_date" required></td></tr>
                            <tr><th><?php _e('有効', 'monthly-booking'); ?></th><td><label><input type="checkbox" name="is_active" value="1" checked> <?php _e('有効にする', 'monthly-booking'); ?></label></td></tr>
                        </table>
                        <p class="submit">
                            <button type="submit" class="button button-primary"><?php _e('保存', 'monthly-booking'); ?></button>
                        </p>
                    </form>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('ID', 'monthly-booking'); ?></th>
                                <th><?php _e('名称', 'monthly-booking'); ?></th>
                                <th><?php _e('タイプ', 'monthly-booking'); ?></th>
                                <th><?php _e('割引', 'monthly-booking'); ?></th>
                                <th><?php _e('期間', 'monthly-booking'); ?></th>
                                <th><?php _e('ステータス', 'monthly-booking'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($q->have_posts()): ?>
                                <?php while ($q->have_posts()): $q->the_post(); $pid = get_the_ID(); ?>
                                    <tr>
                                        <td><?php echo esc_html($pid); ?></td>
                                        <td><?php echo esc_html(get_the_title()); ?></td>
                                        <td><?php echo esc_html(get_post_meta($pid, 'type', true)); ?></td>
                                        <td><?php $type = get_post_meta($pid, 'type', true); $amt = absint(get_post_meta($pid, 'amount', true)); if (!$amt) { echo '—'; } else { if ($type === 'percent') { echo esc_html(number_format_i18n($amt)) . '%'; } else { echo '¥' . esc_html(number_format_i18n($amt)); } } ?></td>
                                        <td><?php echo esc_html(get_post_meta($pid, 'start_date', true) . ' — ' . get_post_meta($pid, 'end_date', true)); ?></td>
                                        <td><?php echo get_post_meta($pid, 'is_active', true) ? __('有効', 'monthly-booking') : __('無効', 'monthly-booking'); ?></td>
                                    </tr>
                                <?php endwhile; wp_reset_postdata(); ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center;"><?php _e('キャンペーンが登録されていません。', 'monthly-booking'); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php
            return;
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
                <h2><?php echo esc_html(mb_t('campaigns.title')); ?></h2>
                <p><?php echo esc_html(mb_t('campaigns.description')); ?></p>
                
                <!-- New Campaign Button -->
                <div style="margin-bottom: 20px;">
                    <button type="button" class="button button-primary" onclick="showCampaignModal()"><?php echo esc_html(mb_t('action.create')); ?></button>
                </div>
                
                <!-- Campaign List Table -->
                <?php
                    $counts = array();
                    $table_room_campaigns = $wpdb->prefix . 'monthly_room_campaigns';
                    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_room_campaigns))) {
                        $today = current_time('Y-m-d');
                        $count_rows = $wpdb->get_results($wpdb->prepare("
                            SELECT campaign_id, COUNT(*) AS cnt
                            FROM {$table_room_campaigns}
                            WHERE is_active = 1
                              AND start_date <= %s
                              AND end_date > %s
                            GROUP BY campaign_id
                        ", $today, $today));
                        if ($count_rows) {
                            foreach ($count_rows as $r) {
                                $counts[intval($r->campaign_id)] = intval($r->cnt);
                            }
                        }
                    }
                ?>
                <table class="monthly-booking-table widefat">
                    <thead>
                        <tr>
                            <th><?php echo esc_html(mb_t('campaigns.list.headers.name')); ?></th>
                            <th><?php echo esc_html(mb_t('campaigns.list.headers.type')); ?></th>
                            <th><?php echo esc_html(mb_t('campaigns.list.headers.value')); ?></th>
                            <th><?php echo esc_html(mb_t('campaigns.list.headers.period_type')); ?></th>
                            <th><?php echo esc_html(mb_t('campaigns.list.headers.contract_types')); ?></th>
                            <th><?php echo esc_html(mb_t('campaigns.list.headers.linked_count')); ?></th>
                            <th><?php echo esc_html(mb_t('campaigns.list.headers.status')); ?></th>
                            <th><?php echo esc_html(mb_t('campaigns.list.headers.created_by')); ?></th>
                            <th><?php echo esc_html(mb_t('campaigns.list.headers.updated_at')); ?></th>
                            <th><?php echo esc_html(mb_t('campaigns.list.headers.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($campaigns)): ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 20px;">
                                <?php echo esc_html(mb_t('campaigns.empty')); ?>
                                <br>
                                <small><?php echo esc_html(mb_t('campaigns.empty.hint')); ?></small>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($campaigns as $campaign): ?>
                        <tr>
                            <td><strong><?php echo esc_html($campaign->campaign_name); ?></strong></td>
                            <td>
                                <?php
                                    $mode_label = '';
                                    $mode_icon = '';
                                    if ($campaign->discount_type === 'percentage') {
                                        $mode_label = mb_t('discount.mode.rate');
                                        $mode_icon = '％';
                                    } else {
                                        $mode_label = mb_t('discount.mode.fixed');
                                        $mode_icon = '¥';
                                    }
                                    echo '<span class="type-icon" style="margin-right:6px;">' . esc_html($mode_icon) . '</span>' . esc_html($mode_label);
                                ?>
                            </td>
                            <td>
                                <?php
                                    if ($campaign->discount_type === 'percentage') {
                                        echo esc_html(number_format_i18n((float)$campaign->discount_value, 0)) . '%';
                                    } else {
                                        echo '¥' . esc_html(number_format_i18n((float)$campaign->discount_value, 0));
                                    }
                                ?>
                            </td>
                            <td>
                                <?php
                                    $pt = isset($campaign->period_type) ? $campaign->period_type : null;
                                    if ($pt === 'checkin_relative') {
                                        $days = isset($campaign->relative_days) ? intval($campaign->relative_days) : 0;
                                        echo esc_html($days > 0 ? ('入居日から' . $days . '日間') : mb_t('period.type.movein'));
                                    } elseif ($pt === 'unlimited') {
                                        echo esc_html(mb_t('period.type.unlimited'));
                                    } else {
                                        echo esc_html(mb_t('period.type.fixed')) . ' ' . esc_html($campaign->start_date) . ' — ' . esc_html($campaign->end_date);
                                    }
                                ?>
                            </td>
                            <td>
                                <?php
                                    $codes = array();
                                    if (!empty($campaign->target_plan)) {
                                        $raw = explode(',', $campaign->target_plan);
                                        foreach ($raw as $code) {
                                            $code = strtoupper(trim($code));
                                            if (in_array($code, array('SS','S','M','L','ALL'), true)) {
                                                $codes[] = $code;
                                            }
                                        }
                                    }
                                    echo esc_html(implode('/', $codes));
                                ?>
                            </td>
                            <td>
                                <?php
                                    $linked = isset($counts[intval($campaign->id)]) ? intval($counts[intval($campaign->id)]) : 0;
                                    echo esc_html(number_format_i18n($linked, 0));
                                ?>
                            </td>
                            <td>
                                <span class="campaign-status <?php echo $campaign->is_active ? 'active' : 'inactive'; ?>">
                                    <?php echo $campaign->is_active ? esc_html(mb_t('status.active')) : esc_html(mb_t('status.inactive')); ?>
                                </span>
                            </td>
                            <td>-</td>
                            <td>-</td>
                            <td>
                                <a href="#" class="button button-small campaign-edit"
                                   onclick="editCampaign(<?php echo esc_attr($campaign->id); ?>);return false;">
                                   <?php echo esc_html(mb_t('campaigns.actions.edit')); ?>
                                </a>
                                <a href="#" class="button button-small campaign-duplicate"
                                   data-campaign-id="<?php echo esc_attr($campaign->id); ?>"
                                   data-campaign-name="<?php echo esc_attr($campaign->campaign_name); ?>"
                                   data-discount-type="<?php echo esc_attr($campaign->discount_type); ?>"
                                   data-discount-value="<?php echo esc_attr($campaign->discount_value); ?>"
                                   data-start-date="<?php echo esc_attr($campaign->start_date); ?>"
                                   data-end-date="<?php echo esc_attr($campaign->end_date); ?>"
                                   data-target-plan="<?php echo isset($campaign->target_plan) ? esc_attr($campaign->target_plan) : ''; ?>"
                                   data-is-active="<?php echo esc_attr($campaign->is_active); ?>">
                                   <?php echo esc_html(mb_t('campaigns.actions.duplicate')); ?>
                                </a>
                                <a href="#" class="button button-small campaign-assign"
                                   data-campaign-id="<?php echo esc_attr($campaign->id); ?>">
                                   <?php echo esc_html(mb_t('campaigns.actions.assign_to_rooms')); ?>
                                </a>
                                <a href="#" class="button button-small toggle-campaign-status"
                                   data-campaign-id="<?php echo esc_attr($campaign->id); ?>"
                                   data-is-active="<?php echo esc_attr($campaign->is_active); ?>">
                                   <?php echo $campaign->is_active ? esc_html(mb_t('campaigns.actions.disable')) : esc_html(mb_t('campaigns.actions.enable')); ?>
                                </a>
                                <button type="button" class="button button-small button-link-delete campaign-delete"
                                    data-campaign-id="<?php echo esc_attr($campaign->id); ?>"
                                    onclick="if(!confirm('<?php echo esc_js(mb_t('campaigns.confirm.delete')); ?>')){return false;}">
                                    <?php echo esc_html(mb_t('campaigns.actions.delete')); ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <div class="notice notice-info" style="margin-top: 20px;">
                    <p><strong><?php echo esc_html(mb_t('campaigns.rules.title')); ?></strong></p>
                    <ul>
                        <li><?php echo esc_html(mb_t('campaigns.rules.immediate')); ?></li>
                        <li><?php echo esc_html(mb_t('campaigns.rules.earlybird')); ?></li>
                        <li><?php echo esc_html(mb_t('campaigns.rules.flatrate')); ?></li>
                        <li><?php echo esc_html(mb_t('campaigns.rules.only_one')); ?></li>
                    </ul>
                </div>

                <?php
                global $wpdb;
                $table_campaigns = $wpdb->prefix . 'monthly_campaigns';
                $campaigns = $wpdb->get_results("SELECT id, campaign_name AS name, discount_type, discount_value, start_date, end_date, is_active FROM $table_campaigns ORDER BY created_at DESC");
                ?>

                <table class="monthly-booking-table">
                    <thead>
                        <tr>
                            <th><?php echo esc_html(mb_t('common.name')); ?></th>
                            <th><?php echo esc_html(mb_t('common.discount')); ?></th>
                            <th><?php echo esc_html(mb_t('common.period')); ?></th>
                            <th><?php echo esc_html(mb_t('common.status')); ?></th>
                            <th><?php echo esc_html(mb_t('common.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($campaigns)): ?>
                            <?php foreach ($campaigns as $c): ?>
                                <tr>
                                    <td><?php echo esc_html($c->name); ?></td>
                                    <td>
                                        <?php
                                        if ($c->discount_type === 'percentage') {
                                            echo esc_html(number_format((float)$c->discount_value, 0)) . '%';
                                        } else {
                                            echo '¥' . esc_html(number_format((float)$c->discount_value, 0));
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo esc_html($c->start_date . ' — ' . $c->end_date); ?></td>
                                    <td><?php echo $c->is_active ? esc_html(mb_t('status.active')) : esc_html(mb_t('status.inactive')); ?></td>
                                    <td>
                                        <a href="#" class="button campaign-assign"
                                           data-campaign-id="<?php echo esc_attr($c->id); ?>">
                                           <?php echo esc_html(mb_t('campaigns.actions.assign_to_rooms')); ?>
                                        </a>
                                        <a href="#" class="button toggle-campaign-status" data-campaign-id="<?php echo esc_attr($c->id); ?>" data-is-active="<?php echo esc_attr($c->is_active); ?>">
                                           <?php echo $c->is_active ? esc_html(mb_t('action.disable')) : esc_html(mb_t('action.enable')); ?>
                                        </a>
                                        <a href="#" class="button button-small campaign-duplicate"
                                           data-campaign-id="<?php echo esc_attr($c->id); ?>"
                                           data-campaign-name="<?php echo esc_attr($c->name); ?>"
                                           data-discount-type="<?php echo esc_attr($c->discount_type); ?>"
                                           data-discount-value="<?php echo esc_attr($c->discount_value); ?>"
                                           data-start-date="<?php echo esc_attr($c->start_date); ?>"
                                           data-end-date="<?php echo esc_attr($c->end_date); ?>"
                                           data-target-plan="">
                                           <?php echo esc_html(mb_t('campaigns.actions.duplicate')); ?>
                                        </a>
                                        <button type="button" class="button button-small button-link-delete campaign-delete"
                                            data-campaign-id="<?php echo esc_attr($c->id); ?>">
                                            <?php echo esc_html(mb_t('action.delete')); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5"><?php echo esc_html(mb_t('campaigns.not_found')); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Campaign Modal -->
        <div id="campaign-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; width: 600px; max-width: 90%; max-height: 90%; overflow-y: auto;">
                <h3 id="modal-title" style="margin-top: 0; border-bottom: 2px solid #0073aa; padding-bottom: 10px; color: #0073aa;"><?php echo esc_html(mb_t('campaigns.form.title.add')); ?></h3>
                
                <form method="post" id="campaign-form">
                    <input type="hidden" name="action" value="create_campaign" id="form-action">
                    <input type="hidden" name="campaign_id" value="" id="campaign-id">
                    <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce('monthly_booking_admin') ); ?>">
                    <div id="campaign-form-message" class="notice" style="display:none"></div>
                    
                    <!-- 基本情報セクション -->
                    <div class="campaign-section">
                        <h4 class="section-title"><?php echo esc_html(mb_t('campaigns.form.sections.basic')); ?></h4>
                        <table class="form-table">
                            <tr>
                                <th><label for="name"><?php echo esc_html(mb_t('campaigns.form.fields.name')); ?></label></th>
                                <td><input type="text" name="name" id="name" class="regular-text" required placeholder="<?php echo esc_attr(mb_t('campaigns.form.fields.name_placeholder')); ?>"></td>
                            </tr>
                            <tr>
                                <th><label for="campaign_type"><?php _e('キャンペーンタイプ', 'monthly-booking'); ?></label></th>
                                <td>
                                    <select name="campaign_type" id="campaign_type" required>
                                        <option value=""><?php _e('選択してください', 'monthly-booking'); ?></option>
                                        <option value="immediate"><?php _e('即入居割（7日以内チェックイン）', 'monthly-booking'); ?></option>
                                        <option value="earlybird"><?php _e('早割（30日以上前予約）', 'monthly-booking'); ?></option>
                                        <option value="flatrate"><?php _e('コミコミ10万円（7-10日滞在）', 'monthly-booking'); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- 割引設定セクション -->
                    <div class="campaign-section">
                        <h4 class="section-title"><?php echo esc_html(mb_t('campaigns.form.sections.discount')); ?></h4>
                        <table class="form-table">

                            <tr>
                                <th><label for="discount_type"><?php echo esc_html(mb_t('campaigns.form.fields.discount_mode')); ?></label></th>
                                <td>
                                    <select name="discount_type" id="discount_type" required>
                                        <option value=""><?php echo esc_html(mb_t('common.select_placeholder')); ?></option>
                                        <option value="percentage"><?php echo esc_html(mb_t('discount.type.percentage')); ?></option>
                                        <option value="fixed"><?php echo esc_html(mb_t('discount.type.fixed')); ?></option>
                                        <option value="flatrate"><?php echo esc_html(mb_t('discount.type.flatrate')); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="discount_value"><?php echo esc_html(mb_t('campaigns.form.fields.discount_value')); ?></label></th>
                                <td>
                                    <input type="number" name="discount_value" id="discount_value" class="regular-text" min="0" step="0.01" required>
                                    <span id="discount-unit" class="description"></span>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="discount_scope"><?php echo esc_html(mb_t('campaigns.form.fields.scope')); ?></label></th>
                                <td>
                                    <label style="margin-right:12px;">
                                        <input type="radio" name="discount_scope" id="discount_scope_first" value="first_month" checked>
                                        <?php echo esc_html(mb_t('scope.first_month')); ?>
                                    </label>
                                    <label>
                                        <input type="radio" name="discount_scope" id="discount_scope_entire" value="entire_period">
                                        <?php echo esc_html(mb_t('scope.entire_period')); ?>
                                    </label>
                                </td>
                            </tr>

                        </table>
                    </div>
                    
                    <!-- 期間タイプセクション -->
                    <div class="campaign-section">
                        <h4 class="section-title"><?php echo esc_html(mb_t('campaigns.form.sections.period')); ?></h4>
                        <table class="form-table">
                            <tr>
                                <th><label><?php echo esc_html(mb_t('campaigns.form.fields.period_type')); ?></label></th>
                                <td>
                                    <label style="margin-right:12px;">
                                        <input type="radio" name="period_type" value="fixed" checked>
                                        <?php echo esc_html(mb_t('period.type.fixed')); ?>
                                    </label>
                                    <label style="margin-right:12px;">
                                        <input type="radio" name="period_type" value="checkin_relative">
                                        <?php echo esc_html(mb_t('period.type.movein')); ?>
                                    </label>
                                    <label style="margin-right:12px;">
                                        <input type="radio" name="period_type" value="first_month_30d">
                                        <?php echo esc_html(mb_t('period.type.first_month_30d')); ?>
                                    </label>
                                    <label>
                                        <input type="radio" name="period_type" value="unlimited">
                                        <?php echo esc_html(mb_t('period.type.unlimited')); ?>
                                    </label>
                                    <p class="description" id="unlimited-warning" style="display:none;color:#d63638;"><?php echo esc_html(mb_t('campaigns.validation.unlimited_warning')); ?></p>
                                </td>
                            </tr>
                            <tr id="relative-days-row" style="display:none;">
                                <th><label for="relative_days"><?php echo esc_html(mb_t('campaigns.form.fields.relative_days')); ?></label></th>
                                <td>
                                    <input type="number" name="relative_days" id="relative_days" class="small-text" min="1" max="30" value="30">
                                    <span class="description"><?php echo esc_html(mb_t('campaigns.form.help.relative_days')); ?></span>
                                </td>
                            </tr>
                            <tr class="fixed-period-row">
                                <th><label for="start_date"><?php echo esc_html(mb_t('campaigns.form.fields.start_date')); ?></label></th>
                                <td>
                                    <input type="date" name="start_date" id="start_date" class="regular-text" required min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+180 days')); ?>">

                                </td>
                            </tr>
                            <tr class="fixed-period-row">
                                <th><label for="end_date"><?php echo esc_html(mb_t('campaigns.form.fields.end_date')); ?></label></th>
                                <td>
                                    <input type="date" name="end_date" id="end_date" class="regular-text" required max="<?php echo date('Y-m-d', strtotime('+180 days')); ?>">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- 対象契約タイプ -->
                    <div class="campaign-section">
                        <h4 class="section-title"><?php echo esc_html(mb_t('campaigns.form.sections.contract_types')); ?></h4>
                        <table class="form-table">
                            <tr>
                                <th><label><?php echo esc_html(mb_t('campaigns.form.fields.contract_types')); ?></label></th>
                                <td>
                                    <label style="margin-right:12px;"><input type="checkbox" name="contract_types[]" value="SS"> SS</label>
                                    <label style="margin-right:12px;"><input type="checkbox" name="contract_types[]" value="S" checked> S</label>
                                    <label style="margin-right:12px;"><input type="checkbox" name="contract_types[]" value="M"> M</label>
                                    <label><input type="checkbox" name="contract_types[]" value="L"> L</label>
                                    <p class="description"><?php echo esc_html(mb_t('campaigns.form.help.contract_types')); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- プレビュー -->
                    <div class="campaign-section">
                        <h4 class="section-title"><?php echo esc_html(mb_t('campaigns.preview.title')); ?></h4>
                        <table class="form-table">
                            <tr>
                                <th><label for="preview_rent"><?php echo esc_html(mb_t('campaigns.preview.rent_sample')); ?></label></th>
                                <td>
                                    <input type="number" id="preview_rent" class="regular-text" min="0" placeholder="100000">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="preview_contract"><?php echo esc_html(mb_t('campaigns.preview.contract_type')); ?></label></th>
                                <td>
                                    <select id="preview_contract">
                                        <option value="S">S</option>
                                        <option value="SS">SS</option>
                                        <option value="M">M</option>
                                        <option value="L">L</option>
                                    </select>
                                    <button type="button" id="btn-preview" class="button" style="margin-left:8px;"><?php echo esc_html(mb_t('campaigns.preview.open')); ?></button>
                                </td>
                            </tr>
                        </table>
                    </div>

</open_file>
<insert path="/home/ubuntu/repos/monthly-booking/includes/admin-ui.php" insert_line="2319">
        </style>
        
        <!-- プレビューモーダル -->
        <div id="campaign-preview-modal" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index:10000;">
            <div style="position:absolute; top:50%; left:50%; transform: translate(-50%, -50%); background:#fff; padding:24px; width:520px; max-width:90%; border-radius:6px;">
                <h3 style="margin-top:0;"><?php echo esc_html(mb_t('campaigns.preview.title')); ?></h3>
                <div id="preview-result" style="font-size:18px; font-weight:600; margin:12px 0;"></div>
                <p id="preview-note" class="description" style="margin:8px 0 16px;"></p>
                <div style="text-align:right;">
                    <button type="button" class="button" id="preview-close"><?php echo esc_html(mb_t('campaigns.preview.close')); ?></button>
                </div>
            </div>
        </div>

        <script>

                    </div>
                    
                    <!-- 適用条件セクション -->
                    <div class="campaign-section">
                        <h4 class="section-title"><?php _e('適用条件', 'monthly-booking'); ?></h4>
                        <table class="form-table">
                            <tr>
                                <th><label for="start_date"><?php _e('開始日', 'monthly-booking'); ?></label></th>
                                <td>
                                    <input type="date" name="start_date" id="start_date" class="regular-text" required min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+180 days')); ?>">
                                    <p class="description"><?php _e('本日から180日後まで設定可能です', 'monthly-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="end_date"><?php _e('終了日', 'monthly-booking'); ?></label></th>
                                <td>
                                    <input type="date" name="end_date" id="end_date" class="regular-text" required max="<?php echo date('Y-m-d', strtotime('+180 days')); ?>">
                                    <p class="description"><?php _e('最大180日後まで設定可能です', 'monthly-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="target_plan"><?php _e('対象プラン', 'monthly-booking'); ?></label></th>
                                <td>
                                    <select name="target_plan" id="target_plan" required>
                                        <option value="ALL"><?php _e('全プラン対象', 'monthly-booking'); ?></option>
                                        <option value="SS"><?php _e('SSプラン（7-29日）', 'monthly-booking'); ?></option>
                                        <option value="S"><?php _e('Sプラン（30-89日）', 'monthly-booking'); ?></option>
                                        <option value="M"><?php _e('Mプラン（90-179日）', 'monthly-booking'); ?></option>
                                        <option value="L"><?php _e('Lプラン（180日以上）', 'monthly-booking'); ?></option>
                                        <option value="S,M,L"><?php _e('S/M/Lプラン（30日以上）', 'monthly-booking'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="is_active"><?php _e('ステータス', 'monthly-booking'); ?></label></th>
                                <td>
                                    <label style="display: flex; align-items: center;">
                                        <input type="checkbox" name="is_active" id="is_active" value="1" checked style="margin-right: 8px;">
                                        <span><?php _e('キャンペーンを有効にする', 'monthly-booking'); ?></span>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
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
        
        .campaign-section {
            background: #f9f9f9;
            border: 1px solid #e1e1e1;
            border-radius: 4px;
            margin: 15px 0;
            padding: 15px;
        }
        
        .campaign-section .section-title {
            margin: 0 0 10px 0;
            padding: 0 0 8px 0;
            border-bottom: 1px solid #ddd;
            color: #23282d;
            font-size: 14px;
            font-weight: 600;
        }
        
        .campaign-section .form-table {
            margin-bottom: 0;
        }
        
        .campaign-section .form-table th {
            width: 150px;
            padding: 10px 0;
        }
        
        .campaign-section .form-table td {
            padding: 10px 0;
        }
        
        #discount-unit {
            margin-left: 8px;
            font-weight: bold;
            color: #0073aa;
        }
        </style>
        
        <script>
        function showCampaignModal() {
            document.getElementById('modal-title').textContent = '<?php _e('新規キャンペーン作成', 'monthly-booking'); ?>';
            document.getElementById('form-action').value = 'create_campaign';
            document.getElementById('campaign-id').value = '';
            document.getElementById('campaign-form').reset();
            document.getElementById('is_active').checked = true;
            updateDiscountUnit();
            document.getElementById('campaign-modal').style.display = 'block';
        }
        
        function editCampaign(campaignId) {
            document.getElementById('modal-title').textContent = '<?php _e('キャンペーン編集', 'monthly-booking'); ?>';
            document.getElementById('form-action').value = 'update_campaign';
            document.getElementById('campaign-id').value = campaignId;
            
            document.getElementById('name').value = 'サンプルキャンペーン';
            document.getElementById('campaign_type').value = 'immediate';
            document.getElementById('discount_type').value = 'percentage';
            document.getElementById('discount_value').value = '20';
            document.getElementById('start_date').value = '<?php echo date('Y-m-d'); ?>';
            document.getElementById('end_date').value = '<?php echo date('Y-m-d', strtotime('+30 days')); ?>';
            document.getElementById('target_plan').value = 'ALL';
            document.getElementById('is_active').checked = true;
            
            document.getElementById('campaign-modal').style.display = 'block';
        }
        
        function hideCampaignModal() {
            document.getElementById('campaign-modal').style.display = 'none';
        }
        
        function updateDiscountUnit() {
            var discountType = document.getElementById('discount_type').value;
            var unitSpan = document.getElementById('discount-unit');
            
            switch(discountType) {
                case 'percentage':
                    unitSpan.textContent = '<?php _e('（例：20 = 20%割引）', 'monthly-booking'); ?>';
                    break;
                case 'fixed':
                    unitSpan.textContent = '<?php _e('（例：5000 = 5,000円割引）', 'monthly-booking'); ?>';
                    break;
                case 'flatrate':
                    unitSpan.textContent = '<?php _e('（例：100000 = 10万円定額）', 'monthly-booking'); ?>';
                    break;
                default:
                    unitSpan.textContent = '';
            }
        }
        
        (function(){
            function $(id){ return document.getElementById(id); }
            function qs(sel){ return document.querySelector(sel); }
            function qsa(sel){ return Array.prototype.slice.call(document.querySelectorAll(sel)); }

            function getDiscountMode(){
                var v = document.getElementById('discount_type') ? document.getElementById('discount_type').value : 'percentage';
                return v === 'fixed' ? 'fixed' : 'percent';
            }

            function recomputeVisibility(){
                var pt = (qsa('input[name="period_type"]:checked')[0] || {}).value || 'fixed';
                var fixedRows = document.querySelectorAll('.fixed-period-row');
                var relRow = $('relative-days-row');
                var unlimWarn = $('unlimited-warning');

                if (pt === 'fixed') {
                    fixedRows.forEach(function(el){ el.style.display = ''; });
                    if (relRow) relRow.style.display = 'none';
                    if (unlimWarn) unlimWarn.style.display = 'none';
                } else if (pt === 'checkin_relative') {
                    fixedRows.forEach(function(el){ el.style.display = 'none'; });
                    if (relRow) relRow.style.display = '';
                    if (unlimWarn) unlimWarn.style.display = 'none';
                } else if (pt === 'first_month_30d') {
                    fixedRows.forEach(function(el){ el.style.display = 'none'; });
                    if (relRow) relRow.style.display = 'none';
                    if (unlimWarn) unlimWarn.style.display = 'none';
                } else if (pt === 'unlimited') {
                    fixedRows.forEach(function(el){ el.style.display = 'none'; });
                    if (relRow) relRow.style.display = 'none';
                    if (unlimWarn) unlimWarn.style.display = '';
                }
            }

            qsa('input[name="period_type"]').forEach(function(r){
                r.addEventListener('change', recomputeVisibility);
            });
            recomputeVisibility();

            function showFormMessage(type, text){
                var box = document.getElementById('campaign-form-message');
                if (!box) return;
                box.className = 'notice ' + (type === 'error' ? 'notice-error' : 'notice-success');
                box.textContent = text || '';
                box.style.display = text ? '' : 'none';
            }

            function validateCampaignForm(){
                var ok = true;
                var msgs = [];

                var name = (document.getElementById('name') || {}).value || '';
                if (!name.trim()) { ok = false; msgs.push('<?php echo esc_js(mb_t('campaigns.validation.name_required')); ?>'); }

                var mode = getDiscountMode();
                var valStr = (document.getElementById('discount_value') || {}).value || '';
                var val = parseFloat(valStr);
                if (isNaN(val)) val = 0;

                if (mode === 'percent') {
                    if (!(val > 0 && val < 50)) { ok = false; msgs.push('<?php echo esc_js(mb_t('campaigns.validation.discount_percent_range')); ?>'); }
                } else {
                    if (val < 0) { ok = false; msgs.push('<?php echo esc_js(mb_t('campaigns.validation.discount_fixed_range')); ?>'); }
                    if (val >= 0) { msgs.push('<?php echo esc_js(mb_t('campaigns.validation.profit_warning')); ?>'); }
                }

                var pt = (qsa('input[name="period_type"]:checked')[0] || {}).value || 'fixed';
                if (pt === 'checkin_relative') {
                    var rd = parseInt((document.getElementById('relative_days') || {}).value || '0', 10);
                    if (!(rd >= 1 && rd <= 30)) { ok = false; msgs.push('<?php echo esc_js(mb_t('campaigns.validation.relative_days_range')); ?>'); }
                } else if (pt === 'fixed') {
                    var sd = (document.getElementById('start_date') || {}).value;
                    var ed = (document.getElementById('end_date') || {}).value;
                    if (!sd || !ed) { ok = false; msgs.push('<?php echo esc_js(mb_t('campaigns.validation.fixed_dates_required')); ?>'); }
                    if (sd && ed && sd >= ed) { ok = false; msgs.push('<?php echo esc_js(mb_t('campaigns.validation.date_order')); ?>'); }
                }

                var anyContract = qsa('input[name="contract_types[]"]:checked').length > 0;
                if (!anyContract) { ok = false; msgs.push('<?php echo esc_js(mb_t('campaigns.validation.contract_types_required')); ?>'); }

                showFormMessage(ok ? 'success' : 'error', msgs.join(' / '));
                return ok;
            }

            var form = document.getElementById('campaign-form');
            if (form) {
                form.addEventListener('submit', function(e){
                    if (!validateCampaignForm()) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                });
            }

            function computePreview(rent, mode, value, scope){
                var discounted = rent;
                if (mode === 'percent') {
                    discounted = Math.round(rent * (1 - value / 100));
                } else {
                    discounted = Math.max(0, rent - value);
                }
                return discounted;
            }

            var btnPrev = document.getElementById('btn-preview');
            if (btnPrev) {
                btnPrev.addEventListener('click', function(){
                    var rent = parseInt((document.getElementById('preview_rent') || {}).value || '0', 10);
                    if (!(rent >= 0)) rent = 0;
                    var mode = getDiscountMode();
                    var val = parseFloat((document.getElementById('discount_value') || {}).value || '0');
                    if (isNaN(val)) val = 0;
                    var scope = (document.getElementById('discount_scope_entire') || {}).checked ? 'entire_period' : 'first_month';
                    var result = computePreview(rent, mode, val, scope);

                    var resultBox = document.getElementById('preview-result');
                    var note = document.getElementById('preview-note');
                    if (resultBox) resultBox.textContent = '<?php echo esc_js(mb_t('campaigns.preview.result')); ?>: ¥' + result.toLocaleString();
                    if (note) {
                        note.textContent = scope === 'first_month'
                          ? '<?php echo esc_js(mb_t('campaigns.preview.note.first_month')); ?>'
                          : '';
                    }
                    var modal = document.getElementById('campaign-preview-modal');
                    if (modal) modal.style.display = 'block';
                });
            }
            var btnClose = document.getElementById('preview-close');
            if (btnClose) {
                btnClose.addEventListener('click', function(){
                    var modal = document.getElementById('campaign-preview-modal');
                    if (modal) modal.style.display = 'none';
                });
            }
        })();

        document.getElementById('discount_type').addEventListener('change', updateDiscountUnit);
        
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
    
    
    public function render_fee_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('権限がありません。', 'monthly-booking'));
        }
        
        require_once(plugin_dir_path(__FILE__) . 'fee-manager.php');
        $fee_manager = Monthly_Booking_Fee_Manager::get_instance();
        
        if (isset($_POST['submit'])) {
            check_admin_referer('monthly_booking_fee_settings', 'monthly_booking_fee_nonce');
            
            if (isset($_POST['monthly_booking_fees'])) {
                $sanitized_fees = array();
                foreach ($_POST['monthly_booking_fees'] as $key => $value) {
                    $sanitized_value = floatval($value);
                    if ($sanitized_value >= 0 && $sanitized_value <= 9999999) {
                        $sanitized_fees[sanitize_key($key)] = $sanitized_value;
                    }
                }
                $updated_count = $fee_manager->update_fees($sanitized_fees);
                
                if ($updated_count > 0) {
                    echo '<div class="notice notice-success"><p>' . 
                         sprintf(__('%d件の料金設定を保存しました。', 'monthly-booking'), $updated_count) . 
                         '</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>' . 
                         __('料金設定の保存に失敗しました。', 'monthly-booking') . 
                         '</p></div>';
                }
            }
        }
        
        $all_fees = $fee_manager->get_all_fees();
        $fees_by_category = array();
        
        foreach ($all_fees as $fee) {
            $fees_by_category[$fee->category][] = $fee;
        }
        
        $category_labels = array(
            'basic_fees' => __('基本料金', 'monthly-booking'),
            'utilities' => __('光熱費', 'monthly-booking'),
            'person_fees' => __('追加人数料金', 'monthly-booking'),
            'discount_limits' => __('オプション割引設定', 'monthly-booking')
        );
        
        $unit_labels = array(
            'fixed' => __('円（一括）', 'monthly-booking'),
            'daily' => __('円/日', 'monthly-booking'),
            'monthly' => __('円/月', 'monthly-booking')
        );
        
        ?>
        <div class="wrap">
            <h1><?php _e('料金設定', 'monthly-booking'); ?></h1>
            
            <div class="monthly-booking-fee-settings">
                <form method="post" action="">
                    <?php wp_nonce_field('monthly_booking_fee_settings', 'monthly_booking_fee_nonce'); ?>
                    
                    <?php foreach ($fees_by_category as $category => $fees): ?>
                    <div class="fee-category-section">
                        <h2><?php echo isset($category_labels[$category]) ? $category_labels[$category] : esc_html($category); ?></h2>
                        <table class="form-table">
                            <tbody>
                                <?php foreach ($fees as $fee): ?>
                                <tr>
                                    <th scope="row">
                                        <label for="<?php echo esc_attr($fee->setting_key); ?>">
                                            <?php echo esc_html($fee->setting_name); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input type="number" 
                                               id="<?php echo esc_attr($fee->setting_key); ?>" 
                                               name="monthly_booking_fees[<?php echo esc_attr($fee->setting_key); ?>]" 
                                               value="<?php echo esc_attr($fee->setting_value); ?>" 
                                               step="1" 
                                               min="0" 
                                               max="9999999"
                                               class="regular-text" />
                                        <span class="unit-label">
                                            <?php echo isset($unit_labels[$fee->unit_type]) ? $unit_labels[$fee->unit_type] : esc_html($fee->unit_type); ?>
                                        </span>
                                        <?php if (!empty($fee->description)): ?>
                                        <p class="description"><?php echo esc_html($fee->description); ?></p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="fee-settings-actions">
                        <?php submit_button(__('設定を保存', 'monthly-booking'), 'primary', 'submit', false); ?>
                        <button type="button" class="button button-secondary" id="reset-defaults">
                            <?php _e('デフォルト値に戻す', 'monthly-booking'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <style>
        .fee-category-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            margin: 20px 0;
            padding: 20px;
        }

        .fee-category-section h2 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .unit-label {
            margin-left: 10px;
            color: #666;
            font-style: italic;
        }

        .fee-settings-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .fee-settings-actions .button {
            margin-right: 10px;
        }
        </style>
        
        <script>
        document.getElementById('reset-defaults').addEventListener('click', function() {
            if (confirm('<?php _e('デフォルト値に戻しますか？この操作は元に戻せません。', 'monthly-booking'); ?>')) {
                var inputs = document.querySelectorAll('input[type="number"]');
                var defaults = {
                    'cleaning_fee': 38500,
                    'key_fee': 11000,
                    'bedding_fee_daily': 1100,
                    'utilities_ss_daily': 2500,
                    'utilities_other_daily': 2000,
                    'additional_adult_rent': 900,
                    'additional_adult_utilities': 200,
                    'additional_child_rent': 450,
                    'additional_child_utilities': 100,
                    'option_discount_max': 2000,
                    'option_discount_base': 500,
                    'option_discount_additional': 300
                };
                
                inputs.forEach(function(input) {
                    var key = input.name.replace('monthly_booking_fees[', '').replace(']', '');
                    if (defaults[key]) {
                        input.value = defaults[key];
                    }
                });
            }
        });
        </script>
        <?php
    }
}
add_action('wp_ajax_mb_get_room_assignments', function () {
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    check_ajax_referer('monthly_booking_admin', 'nonce');
    global $wpdb;
    $room_id = isset($_POST['room_id']) ? absint($_POST['room_id']) : 0;
    if (!$room_id) wp_send_json_error('invalid_room');
    $table = $wpdb->prefix . 'monthly_room_campaigns';
    $rows = $wpdb->get_results($wpdb->prepare("SELECT id, room_id, campaign_id, start_date, end_date, is_active FROM {$table} WHERE room_id=%d ORDER BY start_date ASC", $room_id), ARRAY_A);
    if (!$rows) $rows = [];
    $c_table = $wpdb->prefix . 'monthly_campaigns';
    $ids = array_map(function($r){ return (int)$r['campaign_id']; }, $rows);
    $names = [];
add_action('wp_ajax_mb_toggle_cleaning', function () {
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    check_ajax_referer('monthly_booking_admin', 'nonce');
    $room_id = isset($_POST['room_id']) ? absint($_POST['room_id']) : 0;
    $is_cleaned = isset($_POST['is_cleaned']) ? (int)$_POST['is_cleaned'] : 0;
    if (!$room_id) wp_send_json_error('invalid', 400);
    $key = 'mb_room_cleaned_' . $room_id;
    update_option($key, $is_cleaned ? '1' : '0');
    wp_send_json_success(true);
});
add_action('wp_ajax_mb_bulk_unassign_campaigns', function () {
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    check_ajax_referer('monthly_booking_admin', 'nonce');
    global $wpdb;
    $room_ids = isset($_POST['room_ids']) ? (array) $_POST['room_ids'] : array();
    $room_ids = array_values(array_filter(array_map('absint', $room_ids)));
    $campaign_id = isset($_POST['campaign_id']) && $_POST['campaign_id'] !== '' ? absint($_POST['campaign_id']) : 0;
    if (!$room_ids) wp_send_json_error('invalid', 400);
    $table = $wpdb->prefix . 'monthly_room_campaigns';
    $in = implode(',', array_fill(0, count($room_ids), '%d'));
    if ($campaign_id > 0) {
        $sql = $wpdb->prepare("DELETE FROM {$table} WHERE room_id IN ($in) AND campaign_id=%d", array_merge($room_ids, array($campaign_id)));
    } else {
        $sql = $wpdb->prepare("DELETE FROM {$table} WHERE room_id IN ($in)", $room_ids);
    }
    $ok = $wpdb->query($sql);
    if ($ok === false) wp_send_json_error($wpdb->last_error ?: 'db error', 500);
    wp_send_json_success(array('removed' => (int)$ok));
});
    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $sql = "SELECT id, name FROM {$c_table} WHERE id IN ($placeholders)";
        $prepared = $wpdb->prepare($sql, $ids);
        $res = $wpdb->get_results($prepared, ARRAY_A);
        foreach ($res as $r) $names[$r['id']] = $r['name'];
    }
    foreach ($rows as &$r) {
        $cid = (int)$r['campaign_id'];
        $r['campaign_name'] = isset($names[$cid]) ? $names[$cid] : '';
    }
    wp_send_json_success($rows);
});

add_action('wp_ajax_mb_check_overlap', function () {
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    check_ajax_referer('monthly_booking_admin', 'nonce');
    global $wpdb;
    $room_id = isset($_POST['room_id']) ? absint($_POST['room_id']) : 0;
    $assignment_id = isset($_POST['assignment_id']) ? absint($_POST['assignment_id']) : 0;
    $sd = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
    $ed = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
    if (!$room_id || !$sd || !$ed) wp_send_json_error('invalid');
    $table = $wpdb->prefix . 'monthly_room_campaigns';
    $query = "SELECT id, start_date, end_date FROM {$table} WHERE room_id=%d AND is_active=1";
    $params = [$room_id];
    if ($assignment_id) {
        $query .= " AND id<>%d";
        $params[] = $assignment_id;
    }
    $rows = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);
    $overlap = false;
    $conflict = null;
    $sd1 = $sd;
    $ed1 = $ed;
    foreach ($rows as $row) {
        $sd2 = $row['start_date'];
        $ed2 = $row['end_date'];
        if ($sd1 < $ed2 && $sd2 < $ed1) {
            $overlap = true;
            $conflict = $row;
            break;
        }
    }
    if ($overlap) wp_send_json_success(['overlap' => true, 'conflict' => $conflict]);
    wp_send_json_success(['overlap' => false]);
});

add_action('wp_ajax_mb_save_room_assignment', function () {
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    check_ajax_referer('monthly_booking_admin', 'nonce');
    global $wpdb, $current_user;
    $room_id = isset($_POST['room_id']) ? absint($_POST['room_id']) : 0;
    $campaign_id = isset($_POST['campaign_id']) ? absint($_POST['campaign_id']) : 0;
    $assignment_id = isset($_POST['assignment_id']) ? absint($_POST['assignment_id']) : 0;
    $sd = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
    $ed = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
    $is_active = isset($_POST['is_active']) ? (int)($_POST['is_active'] ? 1 : 0) : 1;
    if (!$room_id || !$campaign_id || !$sd || !$ed) wp_send_json_error('invalid');
    $table = $wpdb->prefix . 'monthly_room_campaigns';
    $check = $wpdb->get_results($wpdb->prepare("SELECT id, start_date, end_date FROM {$table} WHERE room_id=%d AND is_active=1" . ($assignment_id ? " AND id<>%d" : ""), $assignment_id ? [$room_id, $assignment_id] : [$room_id]), ARRAY_A);
    foreach ($check as $row) {
        if ($sd < $row['end_date'] && $row['start_date'] < $ed) {
            wp_send_json_error(['code' => 'overlap', 'conflict' => $row]);
        }
    }
    $data = [
        'room_id' => $room_id,
        'campaign_id' => $campaign_id,
        'start_date' => $sd,
        'end_date' => $ed,
        'is_active' => $is_active,
    ];
    $fmt = ['%d','%d','%s','%s','%d'];
    if ($assignment_id) {
        $prev = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $assignment_id), ARRAY_A);
        $wpdb->update($table, $data, ['id' => $assignment_id], $fmt, ['%d']);
        $id = $assignment_id;
        $action = 'update';
        $prev_values = $prev ? wp_json_encode($prev) : '';
    } else {
        $wpdb->insert($table, $data, $fmt);
        $id = (int)$wpdb->insert_id;
        $action = 'assign';
        $prev_values = '';
    }
    $post_id = wp_insert_post([
        'post_type' => 'mb_campaign_audit',
        'post_status' => 'private',
        'post_title' => 'Campaign Assignment Audit'
    ]);
    if ($post_id) {
        add_post_meta($post_id, 'room_id', $room_id);
        add_post_meta($post_id, 'campaign_id', $campaign_id);
        add_post_meta($post_id, 'action', $action);
        add_post_meta($post_id, 'user_id', get_current_user_id());
        add_post_meta($post_id, 'start_date', $sd);
        add_post_meta($post_id, 'end_date', $ed);
        if ($prev_values) add_post_meta($post_id, 'prev_values', $prev_values);
        add_post_meta($post_id, 'assignment_id', $id);
    }
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id), ARRAY_A);
    wp_send_json_success($row);
});

add_action('wp_ajax_mb_delete_room_assignment', function () {
    if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
    check_ajax_referer('monthly_booking_admin', 'nonce');
    global $wpdb;
    $id = isset($_POST['assignment_id']) ? absint($_POST['assignment_id']) : 0;
    if (!$id) wp_send_json_error('invalid');
    $table = $wpdb->prefix . 'monthly_room_campaigns';
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id), ARRAY_A);
    $wpdb->delete($table, ['id' => $id], ['%d']);
    $post_id = wp_insert_post([
        'post_type' => 'mb_campaign_audit',
        'post_status' => 'private',
        'post_title' => 'Campaign Assignment Audit'
    ]);
    if ($post_id && $row) {
        add_post_meta($post_id, 'room_id', $row['room_id']);
        add_post_meta($post_id, 'campaign_id', $row['campaign_id']);
        add_post_meta($post_id, 'action', 'unassign');
        add_post_meta($post_id, 'user_id', get_current_user_id());
        add_post_meta($post_id, 'start_date', $row['start_date']);
        add_post_meta($post_id, 'end_date', $row['end_date']);
        add_post_meta($post_id, 'assignment_id', $id);
    }
    wp_send_json_success(true);
});

add_action('init', function () {
    register_post_type('mb_campaign_audit', [
        'labels' => ['name' => 'Campaign Audit'],
        'public' => false,
        'show_ui' => false,
        'show_in_menu' => false,
































































        'supports' => ['title']
    ]);
});
add_action('admin_footer', function () {
    if (!current_user_can('manage_options')) return;
    ?>
<div id="assignment-modal" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index:10000;">
    <div role="dialog" aria-modal="true" aria-labelledby="assignment-modal-title" style="position:absolute; top:50%; left:50%; transform: translate(-50%, -50%); background:#fff; padding:24px; width:560px; max-width:90%; border-radius:6px;">
        <h3 id="assignment-modal-title" style="margin-top:0;"><?php echo esc_html(mb_t('campaigns.assign.modal.title')); ?></h3>
        <div id="assignment-message" class="notice" role="status" aria-live="polite" style="display:none;"></div>
        <table class="form-table">
            <tr>
                <th><label for="assignment_room"><?php echo esc_html(mb_t('campaigns.assign.fields.room')); ?></label></th>
                <td>
                    <input type="text" id="assignment_room_search" class="regular-text" style="margin-bottom:8px; width:100%" placeholder="<?php echo esc_attr(mb_t('common.search')); ?>" />
                    <select id="assignment_room"></select>
                </td>
            </tr>
            <tr>
                <th><label for="assignment_campaign"><?php echo esc_html(mb_t('campaigns.assign.fields.campaign')); ?></label></th>
                <td>
                    <select id="assignment_campaign"></select>
                </td>
            </tr>
            <tr>
                <th><label for="assignment_start"><?php echo esc_html(mb_t('campaigns.assign.fields.start_date')); ?></label></th>
                <td><input type="date" id="assignment_start"></td>
            </tr>
            <tr>
                <th><label for="assignment_end"><?php echo esc_html(mb_t('campaigns.assign.fields.end_date')); ?></label></th>
                <td><input type="date" id="assignment_end"></td>
            </tr>
            <tr>
                <th><label for="assignment_active"><?php echo esc_html(mb_t('campaigns.assign.fields.status')); ?></label></th>
                <td><label><input type="checkbox" id="assignment_active" checked> <?php echo esc_html(mb_t('rooms.form.campaign.toggle.active')); ?></label></td>
            </tr>
        </table>
        <div style="text-align:right;">
            <button type="button" class="button" id="assignment-cancel"><?php echo esc_html(mb_t('action.cancel')); ?></button>
            <button type="button" class="button button-primary" id="assignment-save"><?php echo esc_html(mb_t('campaigns.assign.actions.assign')); ?></button>
        </div>
    </div>
</div>
<?php
});
