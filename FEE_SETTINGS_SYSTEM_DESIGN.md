# 料金設定管理システム設計書

## 概要
現在 `booking-logic.php` にハードコードされている全ての料金や手数料を、WordPressの管理画面から設定・変更可能にするシステムの設計書です。

## 1. データベース設計

### テーブル: wp_monthly_fee_settings

```sql
CREATE TABLE wp_monthly_fee_settings (
    id int(11) NOT NULL AUTO_INCREMENT,
    setting_key varchar(50) NOT NULL UNIQUE,
    setting_name varchar(100) NOT NULL,
    setting_value decimal(10,2) NOT NULL,
    unit_type enum('fixed', 'daily', 'monthly') NOT NULL DEFAULT 'fixed',
    category varchar(30) NOT NULL,
    description text,
    is_active tinyint(1) NOT NULL DEFAULT 1,
    display_order int(11) NOT NULL DEFAULT 0,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_setting_key (setting_key),
    KEY idx_category (category),
    KEY idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 初期データ投入

```sql
INSERT INTO wp_monthly_fee_settings (setting_key, setting_name, setting_value, unit_type, category, description, display_order) VALUES
-- 基本料金
('cleaning_fee', '清掃費', 38500.00, 'fixed', 'basic_fees', 'チェックアウト時の清掃費用', 1),
('key_fee', '鍵手数料', 11000.00, 'fixed', 'basic_fees', '鍵の受け渡し手数料', 2),
('bedding_fee_daily', '布団代（1日あたり）', 1100.00, 'daily', 'basic_fees', '追加布団の1日あたり料金', 3),

-- 光熱費
('utilities_ss_daily', '光熱費（SSプラン・1日）', 2500.00, 'daily', 'utilities', 'SSプラン滞在時の1日あたり光熱費', 4),
('utilities_other_daily', '光熱費（S/M/Lプラン・1日）', 2000.00, 'daily', 'utilities', 'S/M/Lプラン滞在時の1日あたり光熱費', 5),

-- 追加人数料金
('additional_adult_rent', '追加大人・賃料（1日）', 900.00, 'daily', 'person_fees', '追加大人1名あたりの1日賃料', 6),
('additional_adult_utilities', '追加大人・光熱費（1日）', 200.00, 'daily', 'person_fees', '追加大人1名あたりの1日光熱費', 7),
('additional_child_rent', '追加子ども・賃料（1日）', 450.00, 'daily', 'person_fees', '追加子ども1名あたりの1日賃料', 8),
('additional_child_utilities', '追加子ども・光熱費（1日）', 100.00, 'daily', 'person_fees', '追加子ども1名あたりの1日光熱費', 9);
```

## 2. 管理画面UI設計

### HTMLモックアップ

```html
<div class="wrap">
    <h1><?php _e('料金設定', 'monthly-booking'); ?></h1>
    
    <div class="monthly-booking-fee-settings">
        <form method="post" action="options.php">
            <?php settings_fields('monthly_booking_fee_settings'); ?>
            
            <!-- 基本料金セクション -->
            <div class="fee-category-section">
                <h2><?php _e('基本料金', 'monthly-booking'); ?></h2>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="cleaning_fee"><?php _e('清掃費', 'monthly-booking'); ?></label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="cleaning_fee" 
                                       name="monthly_booking_fees[cleaning_fee]" 
                                       value="38500" 
                                       step="1" 
                                       min="0" 
                                       class="regular-text" />
                                <span class="unit-label"><?php _e('円（一括）', 'monthly-booking'); ?></span>
                                <p class="description"><?php _e('チェックアウト時の清掃費用', 'monthly-booking'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="key_fee"><?php _e('鍵手数料', 'monthly-booking'); ?></label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="key_fee" 
                                       name="monthly_booking_fees[key_fee]" 
                                       value="11000" 
                                       step="1" 
                                       min="0" 
                                       class="regular-text" />
                                <span class="unit-label"><?php _e('円（一括）', 'monthly-booking'); ?></span>
                                <p class="description"><?php _e('鍵の受け渡し手数料', 'monthly-booking'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="bedding_fee_daily"><?php _e('布団代', 'monthly-booking'); ?></label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="bedding_fee_daily" 
                                       name="monthly_booking_fees[bedding_fee_daily]" 
                                       value="1100" 
                                       step="1" 
                                       min="0" 
                                       class="regular-text" />
                                <span class="unit-label"><?php _e('円/日・人', 'monthly-booking'); ?></span>
                                <p class="description"><?php _e('追加布団の1日あたり料金', 'monthly-booking'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 光熱費セクション -->
            <div class="fee-category-section">
                <h2><?php _e('光熱費', 'monthly-booking'); ?></h2>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="utilities_ss_daily"><?php _e('SSプラン光熱費', 'monthly-booking'); ?></label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="utilities_ss_daily" 
                                       name="monthly_booking_fees[utilities_ss_daily]" 
                                       value="2500" 
                                       step="1" 
                                       min="0" 
                                       class="regular-text" />
                                <span class="unit-label"><?php _e('円/日', 'monthly-booking'); ?></span>
                                <p class="description"><?php _e('SSプラン滞在時の1日あたり光熱費', 'monthly-booking'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="utilities_other_daily"><?php _e('S/M/Lプラン光熱費', 'monthly-booking'); ?></label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="utilities_other_daily" 
                                       name="monthly_booking_fees[utilities_other_daily]" 
                                       value="2000" 
                                       step="1" 
                                       min="0" 
                                       class="regular-text" />
                                <span class="unit-label"><?php _e('円/日', 'monthly-booking'); ?></span>
                                <p class="description"><?php _e('S/M/Lプラン滞在時の1日あたり光熱費', 'monthly-booking'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 追加人数料金セクション -->
            <div class="fee-category-section">
                <h2><?php _e('追加人数料金', 'monthly-booking'); ?></h2>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="additional_adult_rent"><?php _e('追加大人・賃料', 'monthly-booking'); ?></label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="additional_adult_rent" 
                                       name="monthly_booking_fees[additional_adult_rent]" 
                                       value="900" 
                                       step="1" 
                                       min="0" 
                                       class="regular-text" />
                                <span class="unit-label"><?php _e('円/日・人', 'monthly-booking'); ?></span>
                                <p class="description"><?php _e('追加大人1名あたりの1日賃料', 'monthly-booking'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="additional_adult_utilities"><?php _e('追加大人・光熱費', 'monthly-booking'); ?></label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="additional_adult_utilities" 
                                       name="monthly_booking_fees[additional_adult_utilities]" 
                                       value="200" 
                                       step="1" 
                                       min="0" 
                                       class="regular-text" />
                                <span class="unit-label"><?php _e('円/日・人', 'monthly-booking'); ?></span>
                                <p class="description"><?php _e('追加大人1名あたりの1日光熱費', 'monthly-booking'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="additional_child_rent"><?php _e('追加子ども・賃料', 'monthly-booking'); ?></label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="additional_child_rent" 
                                       name="monthly_booking_fees[additional_child_rent]" 
                                       value="450" 
                                       step="1" 
                                       min="0" 
                                       class="regular-text" />
                                <span class="unit-label"><?php _e('円/日・人', 'monthly-booking'); ?></span>
                                <p class="description"><?php _e('追加子ども1名あたりの1日賃料', 'monthly-booking'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="additional_child_utilities"><?php _e('追加子ども・光熱費', 'monthly-booking'); ?></label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="additional_child_utilities" 
                                       name="monthly_booking_fees[additional_child_utilities]" 
                                       value="100" 
                                       step="1" 
                                       min="0" 
                                       class="regular-text" />
                                <span class="unit-label"><?php _e('円/日・人', 'monthly-booking'); ?></span>
                                <p class="description"><?php _e('追加子ども1名あたりの1日光熱費', 'monthly-booking'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

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
```

## 3. 既存ロジックの修正計画

### 3.1 料金取得関数の作成

新しいヘルパー関数を `includes/fee-manager.php` に作成：

```php
<?php
class Monthly_Booking_Fee_Manager {
    
    private static $instance = null;
    private $fee_cache = array();
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 料金設定値を取得
     */
    public function get_fee($setting_key, $default_value = 0) {
        if (isset($this->fee_cache[$setting_key])) {
            return $this->fee_cache[$setting_key];
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'monthly_fee_settings';
        
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM {$table_name} WHERE setting_key = %s AND is_active = 1",
            $setting_key
        ));
        
        $result = ($value !== null) ? floatval($value) : $default_value;
        $this->fee_cache[$setting_key] = $result;
        
        return $result;
    }
    
    /**
     * 複数の料金設定を一括取得
     */
    public function get_fees($setting_keys) {
        $fees = array();
        foreach ($setting_keys as $key) {
            $fees[$key] = $this->get_fee($key);
        }
        return $fees;
    }
    
    /**
     * キャッシュをクリア
     */
    public function clear_cache() {
        $this->fee_cache = array();
    }
}
```

### 3.2 booking-logic.php の修正

#### 修正前（現在のハードコード）

```php
// 清掃費・鍵手数料（固定）
$cleaning_fee = 38500;
$key_fee = 11000;

// 光熱費（日割り）
$utilities_daily = ($plan === 'SS') ? 2500 : 2000;

// 追加人数料金
$additional_adults = max(0, $num_adults - 1);
$additional_children = $num_children;

$adult_rent_fee = $additional_adults * 900 * $stay_days;
$adult_utilities_fee = $additional_adults * 200 * $stay_days;
$children_rent_fee = $additional_children * 450 * $stay_days;
$children_utilities_fee = $additional_children * 100 * $stay_days;

// 布団代
$adult_bedding_fee = $additional_adults * 1100 * $stay_days;
$children_bedding_fee = $additional_children * 1100 * $stay_days;
```

#### 修正後（データベース連携）

```php
// 料金管理クラスのインスタンス取得
$fee_manager = Monthly_Booking_Fee_Manager::get_instance();

// 基本料金（固定）
$cleaning_fee = $fee_manager->get_fee('cleaning_fee', 38500);
$key_fee = $fee_manager->get_fee('key_fee', 11000);

// 光熱費（日割り）
$utilities_daily = ($plan === 'SS') 
    ? $fee_manager->get_fee('utilities_ss_daily', 2500)
    : $fee_manager->get_fee('utilities_other_daily', 2000);

// 追加人数料金
$additional_adults = max(0, $num_adults - 1);
$additional_children = $num_children;

$adult_rent_daily = $fee_manager->get_fee('additional_adult_rent', 900);
$adult_utilities_daily = $fee_manager->get_fee('additional_adult_utilities', 200);
$children_rent_daily = $fee_manager->get_fee('additional_child_rent', 450);
$children_utilities_daily = $fee_manager->get_fee('additional_child_utilities', 100);

$adult_rent_fee = $additional_adults * $adult_rent_daily * $stay_days;
$adult_utilities_fee = $additional_adults * $adult_utilities_daily * $stay_days;
$children_rent_fee = $additional_children * $children_rent_daily * $stay_days;
$children_utilities_fee = $additional_children * $children_utilities_daily * $stay_days;

// 布団代
$bedding_daily = $fee_manager->get_fee('bedding_fee_daily', 1100);
$adult_bedding_fee = $additional_adults * $bedding_daily * $stay_days;
$children_bedding_fee = $additional_children * $bedding_daily * $stay_days;
```

### 3.3 管理画面の実装

`includes/admin-ui.php` に料金設定ページを追加：

```php
/**
 * 料金設定ページの追加
 */
public function add_fee_settings_page() {
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
 * 料金設定ページのレンダリング
 */
public function render_fee_settings_page() {
    if (isset($_POST['submit'])) {
        $this->save_fee_settings();
    }
    
    $fee_manager = Monthly_Booking_Fee_Manager::get_instance();
    $current_fees = $this->get_current_fee_settings();
    
    include_once plugin_dir_path(__FILE__) . '../templates/admin-fee-settings.php';
}

/**
 * 料金設定の保存
 */
private function save_fee_settings() {
    if (!current_user_can('manage_options')) {
        wp_die(__('権限がありません。', 'monthly-booking'));
    }
    
    check_admin_referer('monthly_booking_fee_settings-options');
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'monthly_fee_settings';
    
    if (isset($_POST['monthly_booking_fees'])) {
        foreach ($_POST['monthly_booking_fees'] as $key => $value) {
            $sanitized_value = floatval($value);
            
            $wpdb->replace(
                $table_name,
                array(
                    'setting_key' => sanitize_key($key),
                    'setting_value' => $sanitized_value,
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%f', '%s')
            );
        }
        
        // キャッシュクリア
        Monthly_Booking_Fee_Manager::get_instance()->clear_cache();
        
        add_settings_error(
            'monthly_booking_fee_settings',
            'settings_updated',
            __('料金設定を保存しました。', 'monthly-booking'),
            'updated'
        );
    }
}
```

## 4. 実装手順

### Phase 1: データベース準備
1. `wp_monthly_fee_settings` テーブル作成
2. 初期データ投入
3. 料金管理クラス (`Monthly_Booking_Fee_Manager`) 実装

### Phase 2: 管理画面実装
1. 料金設定ページの追加
2. フォーム処理とバリデーション
3. CSS スタイリング

### Phase 3: 既存ロジック修正
1. `booking-logic.php` のハードコード値を動的取得に変更
2. フロントエンド JavaScript の料金表示更新
3. テスト実行と動作確認

### Phase 4: 検証とドキュメント
1. 全料金パターンのテスト
2. 管理画面操作マニュアル作成
3. 本番環境移行手順書作成

## 5. 期待される効果

- **運用の柔軟性**: 料金変更時にコード修正が不要
- **保守性向上**: 料金ロジックの一元管理
- **ユーザビリティ**: 直感的な管理画面操作
- **拡張性**: 新しい料金項目の追加が容易

## 6. 注意事項

- 料金変更時は既存予約への影響を考慮
- データベースバックアップを必ず実施
- 段階的な実装とテストを推奨
- キャッシュ機能による性能最適化を実装
