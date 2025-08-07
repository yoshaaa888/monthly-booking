# 部屋別キャンペーン割当UI設計仕様書

## 🎯 概要
部屋編集画面内にキャンペーン割当機能を追加し、1部屋1キャンペーン制の排他制御を実現するUI設計。

## 📍 実装箇所
- **対象ファイル**: `includes/admin-ui.php`
- **統合箇所**: 部屋編集フォーム内の新セクション（line 474-476の間）
- **既存パターン活用**: form-section構造、WordPress標準UI

## 🗄️ データベース統合戦略

### wp_monthly_room_campaigns テーブル設計
```sql
CREATE TABLE {$wpdb->prefix}monthly_room_campaigns (
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
) $charset_collate;
```

### テーブル作成統合（monthly-booking.php）
```php
// monthly-booking.php の create_tables() メソッド内に追加
private function create_room_campaigns_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'monthly_room_campaigns';
    $charset_collate = $wpdb->get_charset_collate();
    
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
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
```

## 🔗 AJAX エンドポイント仕様

### campaign-manager.php への追加
```php
// 新規AJAXアクション（既存のコンストラクタに追加）
add_action('wp_ajax_save_campaign_assignment', array($this, 'ajax_save_campaign_assignment'));
add_action('wp_ajax_delete_campaign_assignment', array($this, 'ajax_delete_campaign_assignment'));
add_action('wp_ajax_check_campaign_period_overlap', array($this, 'ajax_check_campaign_period_overlap'));
add_action('wp_ajax_get_room_campaign_assignments', array($this, 'ajax_get_room_campaign_assignments'));
add_action('wp_ajax_get_active_campaigns', array($this, 'ajax_get_active_campaigns'));
add_action('wp_ajax_get_campaign_assignment', array($this, 'ajax_get_campaign_assignment'));
```

### エンドポイント実装仕様

#### 1. save_campaign_assignment
```php
public function ajax_save_campaign_assignment() {
    check_ajax_referer('monthly_booking_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Insufficient permissions.', 'monthly-booking'));
    }
    
    $assignment_id = sanitize_text_field($_POST['assignment_id']);
    $room_id = intval($_POST['room_id']);
    $campaign_id = intval($_POST['campaign_id']);
    $start_date = sanitize_text_field($_POST['start_date']);
    $end_date = sanitize_text_field($_POST['end_date']);
    $is_active = intval($_POST['is_active']);
    
    // バリデーション
    $validation_result = $this->validate_campaign_assignment($room_id, $campaign_id, $start_date, $end_date, $assignment_id);
    if (is_wp_error($validation_result)) {
        wp_send_json_error($validation_result->get_error_message());
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'monthly_room_campaigns';
    
    $data = array(
        'room_id' => $room_id,
        'campaign_id' => $campaign_id,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'is_active' => $is_active,
        'updated_at' => current_time('mysql')
    );
    
    if ($assignment_id) {
        // 更新
        $result = $wpdb->update($table_name, $data, array('id' => $assignment_id));
    } else {
        // 新規作成
        $data['created_at'] = current_time('mysql');
        $result = $wpdb->insert($table_name, $data);
        $assignment_id = $wpdb->insert_id;
    }
    
    if ($result !== false) {
        wp_send_json_success(array('assignment_id' => $assignment_id));
    } else {
        wp_send_json_error(__('Failed to save campaign assignment.', 'monthly-booking'));
    }
}
```

#### 2. check_campaign_period_overlap
```php
public function ajax_check_campaign_period_overlap() {
    check_ajax_referer('monthly_booking_nonce', 'nonce');
    
    $room_id = intval($_POST['room_id']);
    $start_date = sanitize_text_field($_POST['start_date']);
    $end_date = sanitize_text_field($_POST['end_date']);
    $exclude_assignment_id = intval($_POST['assignment_id']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'monthly_room_campaigns';
    
    $sql = "SELECT COUNT(*) FROM $table_name 
            WHERE room_id = %d 
            AND is_active = 1 
            AND (
                (%s BETWEEN start_date AND end_date) OR 
                (%s BETWEEN start_date AND end_date) OR 
                (start_date BETWEEN %s AND %s)
            )";
    
    $params = array($room_id, $start_date, $end_date, $start_date, $end_date);
    
    if ($exclude_assignment_id) {
        $sql .= " AND id != %d";
        $params[] = $exclude_assignment_id;
    }
    
    $overlap_count = $wpdb->get_var($wpdb->prepare($sql, $params));
    
    if ($overlap_count > 0) {
        wp_send_json_error(__('Period overlaps with existing campaign assignment.', 'monthly-booking'));
    } else {
        wp_send_json_success();
    }
}
```

#### 3. get_room_campaign_assignments
```php
public function ajax_get_room_campaign_assignments() {
    check_ajax_referer('monthly_booking_nonce', 'nonce');
    
    $room_id = intval($_POST['room_id']);
    
    global $wpdb;
    $assignments_table = $wpdb->prefix . 'monthly_room_campaigns';
    $campaigns_table = $wpdb->prefix . 'monthly_campaigns';
    
    $sql = "SELECT a.*, c.campaign_name, c.discount_type, c.discount_value 
            FROM $assignments_table a 
            LEFT JOIN $campaigns_table c ON a.campaign_id = c.id 
            WHERE a.room_id = %d 
            ORDER BY a.start_date DESC";
    
    $assignments = $wpdb->get_results($wpdb->prepare($sql, $room_id));
    
    wp_send_json_success($assignments);
}
```

#### 4. get_active_campaigns
```php
public function ajax_get_active_campaigns() {
    check_ajax_referer('monthly_booking_nonce', 'nonce');
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'monthly_campaigns';
    
    $campaigns = $wpdb->get_results(
        "SELECT id, campaign_name, discount_type, discount_value 
         FROM $table_name 
         WHERE is_active = 1 
         ORDER BY campaign_name"
    );
    
    wp_send_json_success($campaigns);
}
```

## 🎨 UIワイヤーフレーム

### 1. キャンペーン設定セクション（部屋編集画面内）

```html
<div class="form-section">
    <h3><?php _e('Campaign Assignment', 'monthly-booking'); ?></h3>
    
    <!-- キャンペーン割当一覧テーブル -->
    <div class="campaign-assignments-wrapper">
        <div class="campaign-assignments-header">
            <button type="button" id="add-campaign-assignment" class="button button-primary">
                <span class="dashicons dashicons-plus-alt"></span>
                <?php _e('Add Campaign Assignment', 'monthly-booking'); ?>
            </button>
        </div>
        
        <table class="wp-list-table widefat fixed striped campaign-assignments-table">
            <thead>
                <tr>
                    <th scope="col"><?php _e('Campaign', 'monthly-booking'); ?></th>
                    <th scope="col"><?php _e('Period', 'monthly-booking'); ?></th>
                    <th scope="col"><?php _e('Status', 'monthly-booking'); ?></th>
                    <th scope="col"><?php _e('Actions', 'monthly-booking'); ?></th>
                </tr>
            </thead>
            <tbody id="campaign-assignments-list">
                <!-- 既存の割当がここに表示される -->
                <tr class="campaign-assignment-row" data-assignment-id="1">
                    <td>
                        <strong>即入居割</strong><br>
                        <span class="description">20%割引</span>
                    </td>
                    <td>
                        2025-01-01 ～ 2025-12-31<br>
                        <span class="description">365日間</span>
                    </td>
                    <td>
                        <label class="toggle-switch">
                            <input type="checkbox" class="toggle-assignment-status" 
                                   data-assignment-id="1" checked>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="status-text">有効</span>
                    </td>
                    <td>
                        <button type="button" class="button edit-assignment" 
                                data-assignment-id="1">編集</button>
                        <button type="button" class="button delete-assignment" 
                                data-assignment-id="1">削除</button>
                    </td>
                </tr>
                
                <!-- 割当なしの場合 -->
                <tr id="no-assignments-row" style="display: none;">
                    <td colspan="4" class="no-assignments-message">
                        <?php _e('No campaign assignments found. Click "Add Campaign Assignment" to create one.', 'monthly-booking'); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
```

### 2. キャンペーン割当追加/編集モーダル

```html
<!-- モーダルオーバーレイ -->
<div id="campaign-assignment-modal" class="campaign-modal-overlay" style="display: none;">
    <div class="campaign-modal-content">
        <div class="campaign-modal-header">
            <h2 id="modal-title"><?php _e('Add Campaign Assignment', 'monthly-booking'); ?></h2>
            <button type="button" class="campaign-modal-close">&times;</button>
        </div>
        
        <form id="campaign-assignment-form" class="campaign-modal-body">
            <input type="hidden" id="assignment-id" name="assignment_id" value="">
            <input type="hidden" id="room-id" name="room_id" value="<?php echo $property_id; ?>">
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="campaign-select"><?php _e('Campaign', 'monthly-booking'); ?></label>
                    </th>
                    <td>
                        <select id="campaign-select" name="campaign_id" class="regular-text" required>
                            <option value=""><?php _e('Select Campaign', 'monthly-booking'); ?></option>
                            <!-- 動的に生成される -->
                            <option value="1">即入居割 (20%割引)</option>
                            <option value="2">早割 (10%割引)</option>
                            <option value="3">コミコミ10万円 (固定額)</option>
                        </select>
                        <p class="description">
                            <?php _e('Select from active campaigns in the system.', 'monthly-booking'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="start-date"><?php _e('Start Date', 'monthly-booking'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="start-date" name="start_date" 
                               class="regular-text date-picker" required>
                        <p class="description">
                            <?php _e('Campaign application start date.', 'monthly-booking'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="end-date"><?php _e('End Date', 'monthly-booking'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="end-date" name="end_date" 
                               class="regular-text date-picker" required>
                        <p class="description">
                            <?php _e('Campaign application end date.', 'monthly-booking'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="is-active"><?php _e('Status', 'monthly-booking'); ?></label>
                    </th>
                    <td>
                        <label class="toggle-switch">
                            <input type="checkbox" id="is-active" name="is_active" checked>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-label"><?php _e('Active', 'monthly-booking'); ?></span>
                        <p class="description">
                            <?php _e('Enable or disable this campaign assignment.', 'monthly-booking'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <!-- バリデーションエラー表示エリア -->
            <div id="validation-errors" class="notice notice-error" style="display: none;">
                <p></p>
            </div>
        </form>
        
        <div class="campaign-modal-footer">
            <button type="button" class="button button-secondary campaign-modal-close">
                <?php _e('Cancel', 'monthly-booking'); ?>
            </button>
            <button type="submit" form="campaign-assignment-form" class="button button-primary">
                <span id="save-button-text"><?php _e('Save Assignment', 'monthly-booking'); ?></span>
                <span id="save-spinner" class="spinner" style="display: none;"></span>
            </button>
        </div>
    </div>
</div>
```

## 🎨 CSS スタイリング

```css
/* キャンペーン割当テーブル */
.campaign-assignments-wrapper {
    margin-top: 15px;
}

.campaign-assignments-header {
    margin-bottom: 15px;
}

.campaign-assignments-table {
    margin-bottom: 0;
}

.campaign-assignment-row:hover {
    background-color: #f9f9f9;
}

.no-assignments-message {
    text-align: center;
    color: #666;
    font-style: italic;
    padding: 20px;
}

/* トグルスイッチ */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
    margin-right: 10px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: #0073aa;
}

input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

.status-text {
    vertical-align: middle;
    font-weight: 500;
}

/* モーダル */
.campaign-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.campaign-modal-content {
    background: white;
    border-radius: 4px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.campaign-modal-header {
    padding: 20px 20px 0;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.campaign-modal-header h2 {
    margin: 0;
    font-size: 18px;
}

.campaign-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.campaign-modal-close:hover {
    color: #000;
}

.campaign-modal-body {
    padding: 20px;
}

.campaign-modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #ddd;
    text-align: right;
}

.campaign-modal-footer .button {
    margin-left: 10px;
}

/* バリデーションエラー */
#validation-errors {
    margin: 15px 0;
    padding: 10px;
}

#validation-errors p {
    margin: 0;
}

/* 日付ピッカー */
.date-picker {
    background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>');
    background-repeat: no-repeat;
    background-position: right 8px center;
    background-size: 16px;
    padding-right: 35px;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    .campaign-modal-content {
        width: 95%;
        margin: 20px;
    }
    
    .campaign-assignments-table {
        font-size: 14px;
    }
    
    .campaign-assignments-table th,
    .campaign-assignments-table td {
        padding: 8px 4px;
    }
}
```

## 🔧 JavaScript バリデーションロジック

```javascript
jQuery(document).ready(function($) {
    'use strict';
    
    // モーダル表示/非表示
    $('#add-campaign-assignment').on('click', function() {
        openCampaignModal();
    });
    
    $('.campaign-modal-close').on('click', function() {
        closeCampaignModal();
    });
    
    // モーダル外クリックで閉じる
    $('#campaign-assignment-modal').on('click', function(e) {
        if (e.target === this) {
            closeCampaignModal();
        }
    });
    
    // フォーム送信処理
    $('#campaign-assignment-form').on('submit', function(e) {
        e.preventDefault();
        
        if (validateCampaignForm()) {
            saveCampaignAssignment();
        }
    });
    
    // 編集ボタン
    $(document).on('click', '.edit-assignment', function() {
        const assignmentId = $(this).data('assignment-id');
        loadCampaignAssignment(assignmentId);
    });
    
    // 削除ボタン
    $(document).on('click', '.delete-assignment', function() {
        const assignmentId = $(this).data('assignment-id');
        if (confirm('このキャンペーン割当を削除しますか？')) {
            deleteCampaignAssignment(assignmentId);
        }
    });
    
    // ステータストグル
    $(document).on('change', '.toggle-assignment-status', function() {
        const assignmentId = $(this).data('assignment-id');
        const isActive = $(this).is(':checked') ? 1 : 0;
        toggleAssignmentStatus(assignmentId, isActive);
    });
    
    // 日付ピッカー初期化
    if ($.fn.datepicker) {
        $('.date-picker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            minDate: 0 // 今日以降のみ選択可能
        });
    }
    
    /**
     * モーダルを開く
     */
    function openCampaignModal(assignmentId = null) {
        if (assignmentId) {
            $('#modal-title').text('Edit Campaign Assignment');
            $('#save-button-text').text('Update Assignment');
        } else {
            $('#modal-title').text('Add Campaign Assignment');
            $('#save-button-text').text('Save Assignment');
            $('#campaign-assignment-form')[0].reset();
            $('#assignment-id').val('');
        }
        
        $('#campaign-assignment-modal').fadeIn(300);
        $('#campaign-select').focus();
    }
    
    /**
     * モーダルを閉じる
     */
    function closeCampaignModal() {
        $('#campaign-assignment-modal').fadeOut(300);
        clearValidationErrors();
    }
    
    /**
     * フォームバリデーション
     */
    function validateCampaignForm() {
        clearValidationErrors();
        
        const campaignId = $('#campaign-select').val();
        const startDate = $('#start-date').val();
        const endDate = $('#end-date').val();
        const roomId = $('#room-id').val();
        const assignmentId = $('#assignment-id').val();
        
        let errors = [];
        
        // 必須項目チェック
        if (!campaignId) {
            errors.push('キャンペーンを選択してください。');
        }
        
        if (!startDate) {
            errors.push('開始日を入力してください。');
        }
        
        if (!endDate) {
            errors.push('終了日を入力してください。');
        }
        
        // 日付整合性チェック
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            if (start >= end) {
                errors.push('開始日は終了日より前である必要があります。');
            }
            
            // 過去日チェック
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (start < today) {
                errors.push('開始日は今日以降である必要があります。');
            }
        }
        
        if (errors.length > 0) {
            showValidationErrors(errors);
            return false;
        }
        
        // AJAX期間重複チェック
        return checkPeriodOverlap(roomId, startDate, endDate, assignmentId);
    }
    
    /**
     * 期間重複チェック（AJAX）
     */
    function checkPeriodOverlap(roomId, startDate, endDate, assignmentId) {
        let isValid = true;
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            async: false, // 同期処理
            data: {
                action: 'check_campaign_period_overlap',
                room_id: roomId,
                start_date: startDate,
                end_date: endDate,
                assignment_id: assignmentId || '',
                nonce: monthlyBookingAdmin.nonce
            },
            success: function(response) {
                if (!response.success) {
                    showValidationErrors([response.data]);
                    isValid = false;
                }
            },
            error: function() {
                showValidationErrors(['期間重複チェックでエラーが発生しました。']);
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    /**
     * キャンペーン割当保存
     */
    function saveCampaignAssignment() {
        const $form = $('#campaign-assignment-form');
        const $saveButton = $('#save-button-text');
        const $spinner = $('#save-spinner');
        
        $saveButton.hide();
        $spinner.show();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_campaign_assignment',
                ...$form.serializeObject(),
                nonce: monthlyBookingAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    closeCampaignModal();
                    refreshCampaignAssignmentsList();
                    showNotice('キャンペーン割当を保存しました。', 'success');
                } else {
                    showValidationErrors([response.data]);
                }
            },
            error: function() {
                showValidationErrors(['保存中にエラーが発生しました。']);
            },
            complete: function() {
                $saveButton.show();
                $spinner.hide();
            }
        });
    }
    
    /**
     * キャンペーン割当削除
     */
    function deleteCampaignAssignment(assignmentId) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_campaign_assignment',
                assignment_id: assignmentId,
                nonce: monthlyBookingAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    refreshCampaignAssignmentsList();
                    showNotice('キャンペーン割当を削除しました。', 'success');
                } else {
                    alert('削除に失敗しました: ' + response.data);
                }
            },
            error: function() {
                alert('削除中にエラーが発生しました。');
            }
        });
    }
    
    /**
     * ステータス切り替え
     */
    function toggleAssignmentStatus(assignmentId, isActive) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'toggle_assignment_status',
                assignment_id: assignmentId,
                is_active: isActive,
                nonce: monthlyBookingAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    const $row = $(`.campaign-assignment-row[data-assignment-id="${assignmentId}"]`);
                    const $statusText = $row.find('.status-text');
                    $statusText.text(isActive ? '有効' : '無効');
                    
                    showNotice('ステータスを更新しました。', 'success');
                } else {
                    // エラー時は元に戻す
                    const $toggle = $(`.toggle-assignment-status[data-assignment-id="${assignmentId}"]`);
                    $toggle.prop('checked', !isActive);
                    alert('ステータス更新に失敗しました: ' + response.data);
                }
            },
            error: function() {
                // エラー時は元に戻す
                const $toggle = $(`.toggle-assignment-status[data-assignment-id="${assignmentId}"]`);
                $toggle.prop('checked', !isActive);
                alert('ステータス更新中にエラーが発生しました。');
            }
        });
    }
    
    /**
     * キャンペーン割当一覧更新
     */
    function refreshCampaignAssignmentsList() {
        const roomId = $('#room-id').val();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_room_campaign_assignments',
                room_id: roomId,
                nonce: monthlyBookingAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#campaign-assignments-list').html(response.data);
                }
            }
        });
    }
    
    /**
     * バリデーションエラー表示
     */
    function showValidationErrors(errors) {
        const $errorDiv = $('#validation-errors');
        const $errorText = $errorDiv.find('p');
        
        $errorText.html(errors.join('<br>'));
        $errorDiv.show();
    }
    
    /**
     * バリデーションエラークリア
     */
    function clearValidationErrors() {
        $('#validation-errors').hide();
    }
    
    /**
     * 通知表示
     */
    function showNotice(message, type = 'info') {
        const $notice = $(`
            <div class="notice notice-${type} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">この通知を閉じる</span>
                </button>
            </div>
        `);
        
        $('.wrap h1').after($notice);
        
        // 自動削除
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    /**
     * フォームデータをオブジェクトに変換
     */
    $.fn.serializeObject = function() {
        const o = {};
        const a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };
});
```

## 🔌 AJAX エンドポイント仕様

### 1. 期間重複チェック
```php
// Action: check_campaign_period_overlap
// Parameters: room_id, start_date, end_date, assignment_id (optional)
// Response: success/error with message
```

### 2. キャンペーン割当保存
```php
// Action: save_campaign_assignment  
// Parameters: assignment_id (optional), room_id, campaign_id, start_date, end_date, is_active
// Response: success/error with assignment data
```

### 3. キャンペーン割当削除
```php
// Action: delete_campaign_assignment
// Parameters: assignment_id
// Response: success/error with message
```

### 4. ステータス切り替え
```php
// Action: toggle_assignment_status
// Parameters: assignment_id, is_active
// Response: success/error with message
```

### 5. 割当一覧取得
```php
// Action: get_room_campaign_assignments
// Parameters: room_id
// Response: success with HTML content
```

## 🛡️ バリデーション要件

### フロントエンド
1. **必須項目チェック**: キャンペーン、開始日、終了日
2. **日付整合性**: 開始日 < 終了日
3. **過去日チェック**: 開始日は今日以降
4. **期間重複チェック**: AJAX による既存割当との重複確認

### バックエンド
1. **権限チェック**: `current_user_can('manage_options')`
2. **ナンス検証**: `check_ajax_referer()`
3. **データサニタイズ**: `sanitize_text_field()`, `sanitize_key()`
4. **参照整合性**: room_id, campaign_id の存在確認
5. **期間重複防止**: データベースレベルでの重複チェック
6. **日付妥当性**: strtotime() による日付検証

## 🎯 ユーザビリティ考慮事項

1. **直感的操作**: ワンクリックでモーダル表示、トグルでステータス切り替え
2. **視覚的フィードバック**: ローディングスピナー、成功/エラー通知
3. **エラーハンドリング**: 分かりやすいエラーメッセージ、入力値保持
4. **レスポンシブ**: モバイル端末での操作性確保
5. **アクセシビリティ**: キーボード操作、スクリーンリーダー対応

この設計により、部屋単位でのキャンペーン割当管理が直感的かつ安全に行えるUIを実現します。
