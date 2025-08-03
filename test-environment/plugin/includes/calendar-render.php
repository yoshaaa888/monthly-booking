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
        add_shortcode('monthly_booking_estimate', array($this, 'render_estimate_shortcode'));
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
        
        wp_enqueue_script(
            'monthly-booking-estimate',
            MONTHLY_BOOKING_PLUGIN_URL . 'assets/estimate.js',
            array('jquery'),
            MONTHLY_BOOKING_VERSION,
            true
        );
        
        wp_localize_script('monthly-booking-estimate', 'monthlyBookingAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('monthly_booking_nonce'),
            'calculating' => __('Calculating...', 'monthly-booking'),
            'error' => __('An error occurred. Please try again.', 'monthly-booking'),
            'selectStation' => __('Select Station...', 'monthly-booking'),
            'selectStructure' => __('Select Structure...', 'monthly-booking'),
            'maxOccupants' => __('Max Occupants...', 'monthly-booking'),
            'selectDatesFirst' => __('Please select move-in date and stay duration first.', 'monthly-booking'),
            'searchError' => __('Error searching properties. Please try again.', 'monthly-booking'),
            'noPropertiesFound' => __('No properties found matching your criteria.', 'monthly-booking'),
            'availableProperties' => __('Available Properties', 'monthly-booking'),
            'room' => __('Room', 'monthly-booking'),
            'dailyRent' => __('Daily Rent', 'monthly-booking'),
            'access' => __('Access', 'monthly-booking'),
            'amenities' => __('Amenities', 'monthly-booking')
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
    
    /**
     * Render estimate form shortcode
     */
    public function render_estimate_shortcode($atts) {
        $atts = shortcode_atts(array(
            'default_plan' => 'M'
        ), $atts, 'monthly_booking_estimate');
        
        ob_start();
        
        if (isset($_POST['preview_form']) && $_POST['preview_form'] == '1') {
            ?>
            <div class="form-preview-container">
                <h3><?php _e('入力内容の確認', 'monthly-booking'); ?></h3>
                <div class="preview-content">
                    <h4><?php _e('送信されたデータ:', 'monthly-booking'); ?></h4>
                    <table class="preview-table">
                        <?php foreach ($_POST as $key => $value): ?>
                            <?php if ($key !== 'preview_form'): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($key); ?>:</strong></td>
                                    <td><?php echo esc_html(is_array($value) ? implode(', ', $value) : $value); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </table>
                </div>
                <div class="preview-actions">
                    <button onclick="window.location.reload()" class="button"><?php _e('戻る', 'monthly-booking'); ?></button>
                </div>
            </div>
            <style>
                .form-preview-container {
                    max-width: 800px;
                    margin: 20px auto;
                    padding: 20px;
                    background: #fff;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .preview-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 15px 0;
                }
                .preview-table td {
                    padding: 8px 12px;
                    border: 1px solid #ddd;
                    vertical-align: top;
                }
                .preview-table td:first-child {
                    background: #f8f9fa;
                    width: 200px;
                }
                .preview-actions {
                    margin-top: 20px;
                    text-align: center;
                }
            </style>
            <?php
            return ob_get_clean();
        }
        
        ?>
        <div class="monthly-booking-estimate-form">
            <div class="estimate-header">
                <h3><?php _e('月額賃貸見積もり', 'monthly-booking'); ?></h3>
                <p><?php _e('必要事項を入力して見積もりを取得してください。入居日と退去日から自動でプランを判定します。', 'monthly-booking'); ?></p>
            </div>
            
            <form id="monthly-estimate-form" class="estimate-form" method="post">
                <div class="form-section">
                    <h4><?php _e('対象部屋', 'monthly-booking'); ?></h4>
                    <div class="form-row">
                        <label for="room_id"><?php _e('部屋選択', 'monthly-booking'); ?> <span class="required">*</span></label>
                        <select id="room_id" name="room_id" required>
                            <option value=""><?php _e('部屋を選択してください...', 'monthly-booking'); ?></option>
                            <?php
                            global $wpdb;
                            $rooms = $wpdb->get_results("SELECT room_id, display_name, room_name FROM {$wpdb->prefix}monthly_rooms WHERE status = 'active' ORDER BY display_name");
                            foreach ($rooms as $room) {
                                echo '<option value="' . esc_attr($room->room_id) . '">' . esc_html($room->display_name . ' - ' . $room->room_name) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-section">
                    <h4><?php _e('自動プラン判定', 'monthly-booking'); ?></h4>
                    <div class="auto-plan-info">
                        <p><?php _e('滞在期間に基づいて最適なプランを自動選択します：', 'monthly-booking'); ?></p>
                        <ul class="plan-duration-list">
                            <li><strong>SS Plan:</strong> <?php _e('7-29日 - スーパーショートプラン', 'monthly-booking'); ?></li>
                            <li><strong>S Plan:</strong> <?php _e('30-89日 - ショートプラン', 'monthly-booking'); ?></li>
                            <li><strong>M Plan:</strong> <?php _e('90-179日 - ミドルプラン', 'monthly-booking'); ?></li>
                            <li><strong>L Plan:</strong> <?php _e('180日以上 - ロングプラン', 'monthly-booking'); ?></li>
                        </ul>
                        <div id="selected-plan-display" class="selected-plan-display" style="display: none;">
                            <strong><?php _e('選択されたプラン: ', 'monthly-booking'); ?><span id="auto-selected-plan"></span></strong>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h4><?php _e('滞在期間', 'monthly-booking'); ?></h4>
                    <div class="form-row">
                        <label for="move_in_date"><?php _e('入居日', 'monthly-booking'); ?> <span class="required">*</span></label>
                        <input type="date" id="move_in_date" name="move_in_date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-row">
                        <label for="move_out_date"><?php _e('退去日', 'monthly-booking'); ?> <span class="required">*</span></label>
                        <input type="date" id="move_out_date" name="move_out_date" required>
                    </div>
                    <div class="form-row">
                        <label for="stay_months"><?php _e('滞在期間（月数）', 'monthly-booking'); ?></label>
                        <select id="stay_months" name="stay_months">
                            <option value=""><?php _e('自動計算されます...', 'monthly-booking'); ?></option>
                            <option value="1">1 <?php _e('ヶ月', 'monthly-booking'); ?></option>
                            <option value="2">2 <?php _e('ヶ月', 'monthly-booking'); ?></option>
                            <option value="3">3 <?php _e('ヶ月', 'monthly-booking'); ?></option>
                            <option value="6">6 <?php _e('ヶ月', 'monthly-booking'); ?></option>
                            <option value="12">12 <?php _e('ヶ月', 'monthly-booking'); ?></option>
                        </select>
                        <small class="form-help"><?php _e('入居日・退去日から自動計算されます', 'monthly-booking'); ?></small>
                    </div>
                </div>
                
                <div class="form-section">
                    <h4><?php _e('利用人数', 'monthly-booking'); ?></h4>
                    <div class="form-row">
                        <label for="num_adults"><?php _e('大人の人数', 'monthly-booking'); ?> <span class="required">*</span></label>
                        <select id="num_adults" name="num_adults" required>
                            <option value="1" selected>1人</option>
                            <option value="2">2人</option>
                            <option value="3">3人</option>
                            <option value="4">4人</option>
                            <option value="5">5人</option>
                            <option value="6">6人</option>
                            <option value="7">7人</option>
                            <option value="8">8人</option>
                            <option value="9">9人</option>
                            <option value="10">10人</option>
                        </select>
                        <small class="form-help"><?php _e('基本料金には1名分が含まれています', 'monthly-booking'); ?></small>
                    </div>
                    <div class="form-row">
                        <label for="num_children"><?php _e('子供の人数（中学生以下）', 'monthly-booking'); ?></label>
                        <select id="num_children" name="num_children">
                            <option value="0" selected>0人</option>
                            <option value="1">1人</option>
                            <option value="2">2人</option>
                            <option value="3">3人</option>
                            <option value="4">4人</option>
                            <option value="5">5人</option>
                            <option value="6">6人</option>
                            <option value="7">7人</option>
                            <option value="8">8人</option>
                            <option value="9">9人</option>
                            <option value="10">10人</option>
                        </select>
                        <small class="form-help"><?php _e('追加料金: 大人 ¥1,000/日、子供 ¥500/日', 'monthly-booking'); ?></small>
                    </div>
                </div>
                
                <div class="form-section">
                    <h4><?php _e('オプションセット', 'monthly-booking'); ?></h4>
                    <div id="options-grid" class="options-grid">
                        <div class="options-loading"><?php _e('オプションを読み込み中...', 'monthly-booking'); ?></div>
                    </div>
                    <div class="options-discount-info">
                        <small><?php _e('2つ選択で500円割引、3つ以上で追加300円割引（最大2,000円まで）', 'monthly-booking'); ?></small>
                        <div id="options-discount-display" class="options-discount-display" style="display: none;">
                            <strong><?php _e('オプション割引: ', 'monthly-booking'); ?><span id="discount-amount">¥0</span></strong>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h4><?php _e('お客様情報', 'monthly-booking'); ?></h4>
                    <div class="form-row">
                        <label for="guest_name"><?php _e('お名前', 'monthly-booking'); ?> <span class="required">*</span></label>
                        <input type="text" id="guest_name" name="guest_name" required>
                    </div>
                    <div class="form-row">
                        <label for="guest_email"><?php _e('メールアドレス', 'monthly-booking'); ?> <span class="required">*</span></label>
                        <input type="email" id="guest_email" name="guest_email" required>
                    </div>
                    <div class="form-row">
                        <label for="guest_phone"><?php _e('電話番号', 'monthly-booking'); ?></label>
                        <input type="tel" id="guest_phone" name="guest_phone">
                    </div>
                    <div class="form-row">
                        <label for="company_name"><?php _e('法人名（任意）', 'monthly-booking'); ?></label>
                        <input type="text" id="company_name" name="company_name" placeholder="<?php _e('法人でのご利用の場合はご記入ください', 'monthly-booking'); ?>">
                    </div>
                    <div class="form-row">
                        <label for="special_requests"><?php _e('特別なご要望', 'monthly-booking'); ?></label>
                        <textarea id="special_requests" name="special_requests" rows="3" placeholder="<?php _e('ご要望がございましたらご記入ください', 'monthly-booking'); ?>"></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" id="calculate-estimate" class="estimate-button">
                        <?php _e('見積もりを計算', 'monthly-booking'); ?>
                    </button>
                    <button type="submit" name="preview_form" value="1" class="button button-secondary">
                        <?php _e('入力内容を確認', 'monthly-booking'); ?>
                    </button>
                </div>
            </form>
            
            <div id="estimate-result" class="estimate-result" style="display: none;">
                <div class="result-header">
                    <h4><?php _e('Estimate Result', 'monthly-booking'); ?></h4>
                </div>
                <div class="result-content">
                    <div class="estimate-loading" style="display: none;">
                        <p><?php _e('Calculating estimate...', 'monthly-booking'); ?></p>
                    </div>
                    <div class="estimate-details" style="display: none;">
                        <table class="estimate-table">
                            <tr>
                                <td><?php _e('Selected Plan', 'monthly-booking'); ?>:</td>
                                <td class="plan-display"></td>
                            </tr>
                            <tr>
                                <td><?php _e('Move-in Date', 'monthly-booking'); ?>:</td>
                                <td class="date-display"></td>
                            </tr>
                            <tr>
                                <td><?php _e('Stay Duration', 'monthly-booking'); ?>:</td>
                                <td class="duration-display"></td>
                            </tr>
                            <tr class="price-row">
                                <td><?php _e('Monthly Rent', 'monthly-booking'); ?>:</td>
                                <td class="rent-amount"></td>
                            </tr>
                            <tr class="price-row">
                                <td><?php _e('Utilities & Service Fee', 'monthly-booking'); ?>:</td>
                                <td class="utilities-amount"></td>
                            </tr>
                            <tr class="price-row">
                                <td><?php _e('Initial Costs', 'monthly-booking'); ?>:</td>
                                <td class="initial-costs-amount"></td>
                            </tr>
                            <tr class="price-row person-fee-row" style="display: none;">
                                <td><?php _e('追加人数料金', 'monthly-booking'); ?>:</td>
                                <td class="person-fee-amount"></td>
                            </tr>
                            <tr class="price-row options-row" style="display: none;">
                                <td><?php _e('オプション料金', 'monthly-booking'); ?>:</td>
                                <td class="options-amount"></td>
                            </tr>
                            <tr class="price-row options-discount-row" style="display: none;">
                                <td><?php _e('オプション割引', 'monthly-booking'); ?>:</td>
                                <td class="options-discount-amount"></td>
                            </tr>
                            <tr class="subtotal-row">
                                <td><?php _e('Subtotal', 'monthly-booking'); ?>:</td>
                                <td class="subtotal-amount"></td>
                            </tr>
                            <tr class="campaign-row" style="display: none;">
                                <td><?php _e('Campaign Discount', 'monthly-booking'); ?>:</td>
                                <td class="campaign-discount"></td>
                            </tr>
                            <tr class="total-row">
                                <td><strong><?php _e('Total Amount', 'monthly-booking'); ?>:</strong></td>
                                <td class="total-amount"></td>
                            </tr>
                        </table>
                        <div class="campaign-details" style="display: none;">
                            <h5><?php _e('Applied Campaigns', 'monthly-booking'); ?></h5>
                            <div class="campaign-list"></div>
                        </div>
                        <div class="options-details" style="display: none;">
                            <h5><?php _e('選択されたオプション', 'monthly-booking'); ?></h5>
                            <div class="options-list"></div>
                        </div>
                    </div>
                    <div class="estimate-error" style="display: none;">
                        <p class="error-message"></p>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .monthly-booking-estimate-form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fff;
        }
        .estimate-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .form-section:last-of-type {
            border-bottom: none;
        }
        .form-section h4 {
            margin-bottom: 15px;
            color: #333;
        }
        .plan-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .plan-option {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .plan-option:hover {
            border-color: #007cba;
        }
        .plan-option input[type="radio"] {
            margin-right: 10px;
        }
        .plan-option input[type="radio"]:checked + .plan-label {
            color: #007cba;
        }
        .plan-option:has(input[type="radio"]:checked) {
            border-color: #007cba;
            background-color: #f0f8ff;
        }
        .plan-label strong {
            display: block;
            margin-bottom: 5px;
        }
        .plan-label small {
            color: #666;
        }
        .form-row {
            margin-bottom: 15px;
        }
        .form-row label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .required {
            color: #d63638;
        }
        .form-row input,
        .form-row select,
        .form-row textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-row textarea {
            resize: vertical;
            min-height: 80px;
        }
        .button-secondary {
            background: #f0f0f0;
            color: #333;
            padding: 12px 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            margin-left: 10px;
            transition: background 0.3s ease;
        }
        .button-secondary:hover {
            background: #e0e0e0;
        }
        .form-actions {
            text-align: center;
            margin-top: 20px;
        }
        .estimate-button {
            background: #007cba;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .estimate-button:hover {
            background: #005a87;
        }
        .estimate-result {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #f9f9f9;
        }
        .estimate-table {
            width: 100%;
            border-collapse: collapse;
        }
        .estimate-table td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .estimate-table td:last-child {
            text-align: right;
            font-weight: 600;
        }
        .total-row td {
            font-size: 18px;
            padding-top: 15px;
            border-top: 2px solid #007cba;
            border-bottom: none;
        }
        .campaign-details {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        .campaign-details h5 {
            margin-bottom: 10px;
            color: #007cba;
        }
        .error-message {
            color: #d63638;
            font-weight: 600;
        }
        .form-help {
            display: block;
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }
        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }
        .option-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f9f9f9;
        }
        .option-item input[type="checkbox"] {
            margin-right: 10px;
        }
        .option-item.discount-eligible {
            border-color: #007cba;
            background: #f0f8ff;
        }
        .option-label {
            flex: 1;
        }
        .option-name {
            font-weight: 600;
            display: block;
        }
        .option-price {
            color: #007cba;
            font-size: 14px;
        }
        .options-discount-info {
            padding: 10px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            margin-top: 10px;
        }
        .options-discount-display {
            margin-top: 10px;
            padding: 8px;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            color: #155724;
        }
        .options-loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        .person-fee-row,
        .options-row,
        .options-discount-row {
            color: #007cba;
        }
        .options-details {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        .options-details h5 {
            margin-bottom: 10px;
            color: #007cba;
        }
        .option-detail-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .option-detail-item:last-child {
            border-bottom: none;
        }
        .auto-plan-info {
            background: #f0f8ff;
            padding: 15px;
            border: 1px solid #007cba;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .plan-duration-list {
            margin: 10px 0;
            padding-left: 20px;
        }
        .plan-duration-list li {
            margin-bottom: 5px;
        }
        .selected-plan-display {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            font-weight: 600;
        }
        .selected-plan-display.valid-plan {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .selected-plan-display.error-plan {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .cost-subitem {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            font-size: 0.9em;
            color: #666;
            border-bottom: none;
        }
        .cost-subitem span:first-child {
            padding-left: 10px;
        }
        .discount-eligible {
            font-size: 0.8em;
            color: #28a745;
            font-weight: bold;
        }
        .tax-note {
            text-align: center;
            font-size: 0.9em;
            color: #666;
            margin-top: 10px;
            font-style: italic;
        }
        .booking-action-section {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border: 2px solid #007cba;
            border-radius: 8px;
        }
        .booking-confirmation {
            text-align: center;
            margin-bottom: 20px;
        }
        .booking-notice {
            font-size: 1.1em;
            color: #333;
            margin-bottom: 10px;
        }
        .booking-details {
            color: #666;
            font-size: 0.9em;
        }
        .booking-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .booking-submit-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.1em;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .booking-submit-btn:hover:not(:disabled) {
            background: #218838;
        }
        .booking-submit-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        .booking-modify-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.1em;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .booking-modify-btn:hover:not(:disabled) {
            background: #5a6268;
        }
        .booking-success {
            text-align: center;
            padding: 30px;
        }
        .success-header {
            margin-bottom: 30px;
        }
        .success-header h3 {
            color: #28a745;
            font-size: 2em;
            margin-bottom: 10px;
        }
        .success-message {
            font-size: 1.2em;
            color: #333;
        }
        .booking-details-section, .next-steps-section, .contact-section {
            margin: 25px 0;
            padding: 20px;
            background: white;
            border-radius: 6px;
            text-align: left;
        }
        .booking-info p {
            margin: 8px 0;
            font-size: 1.1em;
        }
        .next-steps-list {
            padding-left: 20px;
        }
        .next-steps-list li {
            margin: 10px 0;
            line-height: 1.5;
        }
        .campaign-badge {
            background: #ff6b6b;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.8em;
            font-weight: bold;
            margin-left: 5px;
        }
        .campaign-badge.early {
            background: #4ecdc4;
        }
        .campaign-badge.last_minute {
            background: #ff6b6b;
        }
        </style>
        <?php
        return ob_get_clean();
    }
}
