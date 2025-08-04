# Monthly Booking WordPress Plugin - 現在の仕様書
**Version: 1.5.7 (リファクタリング後)**  
**最終更新: 2025年8月3日**

---

## 📋 プラグイン概要

### 基本情報
- **プラグイン名**: Monthly Room Booking
- **バージョン**: 1.5.7
- **作成者**: Yoshi (@yoshaaa888)
- **ライセンス**: GPL v2 or later
- **テキストドメイン**: monthly-booking
- **GitHub**: https://github.com/yoshaaa888/monthly-booking

### 目的
月単位の物件予約管理を行うWordPressプラグイン。物件マスタ管理、予約カレンダー、料金計算、キャンペーン管理、予約申し込み機能を統合的に提供。

---

## 🔄 リファクタリング履歴

### 主要な変更点
1. **期間計算システムの全面リファクタリング**
   - 30日近似から**カレンダーベース月計算**に変更
   - **排他的チェックアウト**定義の実装（チェックアウト日は含まない）
   - 全プラン境界（SS/S、S/M、M/L）で統一された月計算ロジック

2. **UI改善**
   - `stay_months`ドロップダウンの削除
   - 自動プラン判定システムの実装
   - 部屋選択ドロップダウンの修正

3. **データベース修正**
   - SQLカラム名エラーの修正（`id` → `room_id`、`option_id`）
   - 管理画面部屋選択機能の修正

4. **プラン名の日本語化**
   - 英語プラン名から日本語プラン名に変更

---

## 🗄️ データベース設計

### テーブル構成（7テーブル）

#### 1. wp_monthly_rooms（物件・部屋マスタ）
**主キー**: `room_id` (UNIQUE)
```sql
CREATE TABLE wp_monthly_rooms (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    room_id int(11) UNIQUE,           -- 実際の主キー
    property_id int(11),
    mor_g char(1) DEFAULT 'M',
    property_name text,
    display_name text,
    room_name varchar(100) NOT NULL,
    daily_rent int(11),               -- 日割り賃料
    max_occupants int(3) DEFAULT 1,
    address text,
    line1/station1/access1_type/access1_time,  -- 最大3路線のアクセス情報
    line2/station2/access2_type/access2_time,
    line3/station3/access3_type/access3_time,
    room_amenities text,
    is_active tinyint(1) DEFAULT 1,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY room_id (room_id)
);
```

#### 2. wp_monthly_bookings（予約データ）
```sql
CREATE TABLE wp_monthly_bookings (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    room_id mediumint(9) NOT NULL,
    customer_id mediumint(9) NOT NULL,
    start_date date NOT NULL,         -- チェックイン日
    end_date date NOT NULL,           -- チェックアウト日（排他的）
    num_adults int(2) DEFAULT 1,
    num_children int(2) DEFAULT 0,
    plan_type varchar(10) DEFAULT 'M', -- SS/S/M/L
    base_rent decimal(10,2) NOT NULL,
    utilities_fee decimal(10,2) NOT NULL,
    initial_costs decimal(10,2) NOT NULL,
    person_additional_fee decimal(10,2) DEFAULT 0,
    options_total decimal(10,2) DEFAULT 0,
    options_discount decimal(10,2) DEFAULT 0,
    total_price decimal(10,2) NOT NULL,
    discount_amount decimal(10,2) DEFAULT 0, -- キャンペーン割引
    final_price decimal(10,2) NOT NULL,
    status varchar(20) DEFAULT 'pending',
    payment_status varchar(20) DEFAULT 'unpaid',
    PRIMARY KEY (id)
);
```

#### 3. wp_monthly_customers（顧客データ）
```sql
CREATE TABLE wp_monthly_customers (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    first_name varchar(50) NOT NULL,
    last_name varchar(50) NOT NULL,
    email varchar(100) NOT NULL,
    phone varchar(20),
    address varchar(255),
    emergency_contact_name varchar(100),
    emergency_contact_phone varchar(20),
    PRIMARY KEY (id),
    UNIQUE KEY email (email)
);
```

#### 4. wp_monthly_options（オプションマスタ）
**主キー**: `option_id`
```sql
CREATE TABLE wp_monthly_options (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    option_name varchar(100) NOT NULL,
    price decimal(10,2) NOT NULL,
    is_discount_target tinyint(1) DEFAULT 1, -- セット割引対象
    display_order int(3) DEFAULT 0,
    is_active tinyint(1) DEFAULT 1,
    PRIMARY KEY (id)
);
```

#### 5. wp_monthly_campaigns（キャンペーンマスタ）
```sql
CREATE TABLE wp_monthly_campaigns (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    campaign_name varchar(100) NOT NULL,
    campaign_description text,
    discount_type varchar(20) NOT NULL,    -- 'percentage' or 'fixed'
    discount_value decimal(10,2) NOT NULL,
    min_stay_days int(3) DEFAULT 1,
    start_date date NOT NULL,
    end_date date NOT NULL,
    is_active tinyint(1) DEFAULT 1,
    PRIMARY KEY (id)
);
```

#### 6. wp_monthly_booking_options（予約オプション関連）
```sql
CREATE TABLE wp_monthly_booking_options (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    booking_id mediumint(9) NOT NULL,
    option_id mediumint(9) NOT NULL,
    quantity int(2) DEFAULT 1,
    unit_price decimal(10,2) NOT NULL,
    total_price decimal(10,2) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY booking_option (booking_id, option_id)
);
```

#### 7. wp_monthly_rates（料金マスタ）
```sql
CREATE TABLE wp_monthly_rates (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    room_id mediumint(9) NOT NULL,
    rate_type varchar(20) DEFAULT 'monthly',
    base_price decimal(10,2) NOT NULL,
    cleaning_fee decimal(10,2) DEFAULT 0,
    service_fee decimal(10,2) DEFAULT 0,
    valid_from date NOT NULL,
    valid_to date,
    is_active tinyint(1) DEFAULT 1,
    PRIMARY KEY (id)
);
```

---

## 🎯 機能仕様

### 1. プラン自動判定システム

#### プラン種別（リファクタリング後）
| プラン | 日本語名 | 滞在期間 | 判定ロジック |
|--------|----------|----------|--------------|
| SS | スーパーショートプラン | 7-29日 | `stay_days >= 7 && stay_months < 1` |
| S | ショートプラン | 30日-3ヶ月未満 | `stay_months >= 1 && stay_months < 3` |
| M | ミドルプラン | 3-6ヶ月未満 | `stay_months >= 3 && stay_months < 6` |
| L | ロングプラン | 6ヶ月以上 | `stay_months >= 6` |

#### カレンダーベース月計算
```javascript
// JavaScript実装
function calculateStayMonths(moveInDate, moveOutDate) {
    const checkIn = new Date(moveInDate);
    const checkOut = new Date(moveOutDate);
    
    let months = 0;
    let currentDate = new Date(checkIn);
    
    while (currentDate < checkOut) {
        const originalDay = currentDate.getDate();
        const nextMonth = new Date(currentDate);
        nextMonth.setMonth(nextMonth.getMonth() + 1);
        
        // 月末処理：元の日付が存在しない場合は月末に調整
        if (nextMonth.getDate() !== originalDay) {
            nextMonth.setDate(0); // 前月の最終日
        }
        
        if (nextMonth <= checkOut) {
            months++;
            currentDate = new Date(nextMonth);
        } else {
            // 残り日数が30日以上の場合のみ1ヶ月とカウント
            const daysRemaining = Math.floor((checkOut - currentDate) / (1000 * 60 * 60 * 24));
            if (daysRemaining >= 30) {
                months++;
            }
            break;
        }
    }
    
    return months;
}
```

```php
// PHP実装
private function calculate_stay_months($move_in_date, $move_out_date) {
    $check_in = new DateTime($move_in_date);
    $check_out = new DateTime($move_out_date);
    
    $months = 0;
    $current_date = clone $check_in;
    $original_day = (int)$check_in->format('d');
    
    while ($current_date < $check_out) {
        $next_month = clone $current_date;
        $next_month->modify('+1 month');
        
        // 月末処理
        if ((int)$next_month->format('d') !== $original_day) {
            $next_month->modify('last day of previous month');
        }
        
        if ($next_month <= $check_out) {
            $months++;
            $current_date = clone $next_month;
        } else {
            $days_remaining = $current_date->diff($check_out)->days;
            if ($days_remaining >= 30) {
                $months++;
            }
            break;
        }
    }
    
    return $months;
}
```

### 2. 料金計算システム

#### 基本料金構成
```php
// 日割り賃料
$total_rent = $daily_rent * $stay_days;

// 共益費（プラン別）
$daily_utilities = ($plan === 'SS') ? 2500 : 2000;
$total_utilities = $daily_utilities * $stay_days;

// 初期費用
$cleaning_fee = 38500;  // 清掃費
$key_fee = 11000;       // 鍵交換費
$bedding_fee = 11000;   // 寝具費
$initial_costs = $cleaning_fee + $key_fee + $bedding_fee;

// 人数追加料金
$person_additional_fee = 0;
if ($num_adults > 1) {
    $person_additional_fee += ($num_adults - 1) * 1000 * $stay_days;
}
if ($num_children > 0) {
    $person_additional_fee += $num_children * 500 * $stay_days;
}
```

#### オプション割引システム
```php
function calculateOptionDiscount($selectedOptions) {
    $discountEligibleCount = 0;
    
    foreach ($selectedOptions as $optionId) {
        $option = getOption($optionId);
        if ($option && $option->is_discount_target == 1) {
            $discountEligibleCount++;
        }
    }
    
    if ($discountEligibleCount == 2) {
        return 500;  // 2つで¥500割引
    } elseif ($discountEligibleCount >= 3) {
        $extraOptions = $discountEligibleCount - 2;
        $discount = 500 + ($extraOptions * 300);
        return min($discount, 2000);  // 最大¥2,000
    }
    
    return 0;
}
```

### 3. キャンペーン管理システム

#### 自動キャンペーン適用
```php
public function get_applicable_campaigns($checkin_date) {
    $today = new DateTime();
    $checkin = new DateTime($checkin_date);
    $days_until_checkin = $today->diff($checkin)->days;
    
    $applicable_campaigns = array();
    
    // 即入居割（7日以内）
    if ($days_until_checkin <= 7) {
        $campaign = $this->get_campaign_by_type('last_minute');
        if ($campaign) {
            $applicable_campaigns[] = array(
                'name' => $campaign->campaign_name,
                'type' => 'last_minute',
                'discount_value' => $campaign->discount_value,
                'badge' => '即入居',
                'description' => '入居7日以内の即入居キャンペーン'
            );
        }
    }
    
    // 早割（30日以上前）
    if ($days_until_checkin >= 30) {
        $campaign = $this->get_campaign_by_type('early');
        if ($campaign) {
            $applicable_campaigns[] = array(
                'name' => $campaign->campaign_name,
                'type' => 'early',
                'discount_value' => $campaign->discount_value,
                'badge' => '早割',
                'description' => '入居30日以上前の早期予約キャンペーン'
            );
        }
    }
    
    return !empty($applicable_campaigns) ? $applicable_campaigns : null;
}
```

#### キャンペーン種別
| キャンペーン | 条件 | 割引率 | バッジ | 適用対象 |
|--------------|------|--------|--------|----------|
| 早割 | 30日以上前予約 | 10%OFF | △ 早割 | 賃料・共益費 |
| 即入居割 | 7日以内予約 | 20%OFF | △ 即入居 | 賃料・共益費 |

---

## 🖥️ フロントエンド機能

### 1. 見積もりフォーム（ショートコード: `[monthly_booking_estimate]`）

#### 主要機能
- **部屋選択**: AJAXによる動的部屋一覧取得
- **日付選択**: チェックイン・チェックアウト日付入力
- **自動プラン判定**: 日付入力時の即座プラン表示
- **人数選択**: 大人・子供人数選択
- **オプション選択**: チェックボックス形式、セット割引表示
- **見積計算**: リアルタイム料金計算・表示
- **予約申し込み**: ワンクリック予約申し込み

#### JavaScript主要関数
```javascript
// プラン自動判定
function determinePlanByDuration(moveInDate, moveOutDate) {
    const stayDays = calculateStayDuration(moveInDate, moveOutDate);
    const stayMonths = calculateStayMonths(moveInDate, moveOutDate);
    
    if (stayDays >= 7 && stayMonths < 1) {
        return { code: 'SS', name: 'SS Plan - スーパーショートプラン' };
    } else if (stayMonths >= 1 && stayMonths < 3) {
        return { code: 'S', name: 'S Plan - ショートプラン' };
    } else if (stayMonths >= 3 && stayMonths < 6) {
        return { code: 'M', name: 'M Plan - ミドルプラン' };
    } else if (stayMonths >= 6) {
        return { code: 'L', name: 'L Plan - ロングプラン' };
    }
}

// 見積計算
function calculateEstimate() {
    const formData = {
        action: 'calculate_estimate',
        nonce: monthlyBookingAjax.nonce,
        room_id: $('#room_id').val(),
        move_in_date: $('#move_in_date').val(),
        move_out_date: $('#move_out_date').val(),
        num_adults: $('#num_adults').val(),
        num_children: $('#num_children').val(),
        selected_options: getSelectedOptions()
    };
    
    $.ajax({
        url: monthlyBookingAjax.ajaxurl,
        type: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                displayResults(response.data);
            }
        }
    });
}
```

### 2. カレンダー表示（ショートコード: `[monthly_booking_calendar]`）

#### 機能
- **180日間カレンダー**: 6ヶ月先までの予約状況表示
- **予約状況表示**: 〇（空室）、×（予約済み）、△（キャンペーン対象）
- **キャンペーンバッジ**: 早割・即入居割の視覚的表示
- **クリック予約**: カレンダーから直接予約フォームへ遷移

---

## 🔧 バックエンド機能

### 1. 管理画面構成

#### メニュー構造
```
Monthly Room Booking
├── 物件マスタ管理 (monthly-room-booking)
├── 予約カレンダー (monthly-room-booking-calendar)
├── 予約管理 (monthly-room-booking-bookings)
├── キャンペーン管理 (monthly-room-booking-campaigns)
└── オプション管理 (monthly-room-booking-options)
```

#### 主要クラス
```php
class MonthlyBooking_Admin_UI {
    // 物件マスタ管理
    public function admin_page_property_management()
    
    // 予約カレンダー表示
    public function admin_page_booking_calendar()
    
    // 予約一覧管理
    public function admin_page_booking_management()
    
    // 全部屋取得（修正済み）
    private function get_all_rooms() {
        // SELECT room_id, display_name, ... FROM wp_monthly_rooms
    }
}

class MonthlyBooking_Booking_Logic {
    // 見積計算
    public function calculate_plan_estimate($plan, $move_in_date, $move_out_date, ...)
    
    // 予約作成
    public function create_step4_booking($booking_data)
    
    // カレンダーベース月計算
    private function calculate_stay_months($move_in_date, $move_out_date)
}

class MonthlyBooking_Campaign_Manager {
    // 適用可能キャンペーン取得
    public function get_applicable_campaigns($checkin_date)
    
    // キャンペーン割引計算
    public function calculate_campaign_discount($checkin_date, $base_total, $total_amount)
}
```

### 2. AJAX エンドポイント

#### フロントエンド用
```php
// 見積計算
add_action('wp_ajax_calculate_estimate', array($this, 'ajax_calculate_estimate'));
add_action('wp_ajax_nopriv_calculate_estimate', array($this, 'ajax_calculate_estimate'));

// 予約申し込み
add_action('wp_ajax_submit_booking', array($this, 'ajax_submit_booking'));
add_action('wp_ajax_nopriv_submit_booking', array($this, 'ajax_submit_booking'));

// オプション取得
add_action('wp_ajax_get_booking_options', array($this, 'ajax_get_options'));
add_action('wp_ajax_nopriv_get_booking_options', array($this, 'ajax_get_options'));

// 物件検索
add_action('wp_ajax_search_properties', array($this, 'ajax_search_properties'));
add_action('wp_ajax_nopriv_search_properties', array($this, 'ajax_search_properties'));
```

#### 管理画面用
```php
// キャンペーン管理
add_action('wp_ajax_create_campaign', array($this, 'ajax_create_campaign'));
add_action('wp_ajax_update_campaign', array($this, 'ajax_update_campaign'));
add_action('wp_ajax_delete_campaign', array($this, 'ajax_delete_campaign'));
add_action('wp_ajax_toggle_campaign', array($this, 'ajax_toggle_campaign'));
```

---

## 🔍 重要な修正点

### 1. SQLカラム名修正
**修正前**: 存在しない`id`カラムを参照
```sql
-- 修正前（エラー）
SELECT id, room_id, display_name FROM wp_monthly_rooms WHERE id = %d
```

**修正後**: 正しい主キー`room_id`を使用
```sql
-- 修正後（正常）
SELECT room_id, display_name FROM wp_monthly_rooms WHERE room_id = %d
```

### 2. 排他的チェックアウト実装
**修正前**: チェックアウト日を含む計算
```php
// 修正前
$stay_days = $checkout->diff($checkin)->days + 1; // +1が問題
```

**修正後**: チェックアウト日を含まない計算
```php
// 修正後
$stay_days = $checkout->diff($checkin)->days; // 排他的
```

### 3. stay_monthsドロップダウン削除
**修正前**: 手動月数選択
```html
<!-- 修正前 -->
<select name="stay_months">
    <option value="1">1ヶ月</option>
    <option value="2">2ヶ月</option>
</select>
```

**修正後**: 自動プラン判定
```html
<!-- 修正後 -->
<div id="selected-plan-display">
    <span id="auto-selected-plan">自動判定されたプラン</span>
</div>
```

---

## 🧪 テスト環境

### テストデータ
```sql
-- 物件データ（5件）
INSERT INTO wp_monthly_rooms (room_id, display_name, daily_rent, is_active) VALUES
(1, '立川シェアハウス', 3500, 1),
(2, '新宿マンスリー', 4200, 1),
(3, '渋谷アパート', 4800, 1),
(4, '池袋レジデンス', 3800, 1),
(5, '品川ハウス', 4500, 1);

-- オプションデータ（9件）
-- 割引対象（7件）: 調理器具、食器、洗剤、タオル、アメニティ、寝具、毛布
-- 割引対象外（2件）: アイロン、炊飯器

-- キャンペーンデータ（2件）
INSERT INTO wp_monthly_campaigns VALUES
(1, '早割キャンペーン', '入居30日以上前のご予約で賃料・共益費10%OFF', 'percentage', 10.00, 7, '2025-01-01', '2025-12-31', 1),
(2, '即入居割', '入居7日以内のご予約で賃料・共益費20%OFF', 'percentage', 20.00, 7, '2025-01-01', '2025-12-31', 1);
```

### テストシナリオ
1. **早割キャンペーン**: チェックイン35日後、60日滞在（Sプラン）、大人2名
2. **即入居割**: チェックイン3日後、10日滞在（SSプラン）、大人1名・子供1名
3. **通常料金**: チェックイン15日後、40日滞在（Sプラン）、大人1名

---

## 📁 ファイル構成

```
monthly-booking/
├── monthly-booking.php              # メインプラグインファイル
├── includes/
│   ├── admin-ui.php                 # 管理画面UI
│   ├── booking-logic.php            # 予約ロジック・料金計算
│   ├── calendar-render.php          # カレンダー表示・ショートコード
│   └── campaign-manager.php         # キャンペーン管理
├── assets/
│   ├── estimate.js                  # フロントエンド見積もりJS
│   ├── calendar.js                  # カレンダー表示JS
│   ├── admin.js                     # 管理画面JS
│   ├── calendar.css                 # カレンダーCSS
│   └── admin.css                    # 管理画面CSS
└── test-environment/                # テスト環境一式
    ├── plugin/                      # プラグインファイル
    ├── database/seed_data.sql       # テストデータ
    ├── playwright/                  # 自動テスト
    └── manuals/                     # 手動テストマニュアル
```

---

## 🚀 デプロイメント

### WordPress要件
- **WordPress**: 5.0以上
- **PHP**: 7.4以上
- **MySQL**: 5.6以上

### インストール手順
1. プラグインZIPファイルをWordPress管理画面からアップロード
2. プラグインを有効化（データベーステーブル自動作成）
3. テストデータをAdminerで投入（オプション）
4. ページ作成：
   - 見積もりページ: `[monthly_booking_estimate]`
   - カレンダーページ: `[monthly_booking_calendar]`

### 設定確認
- 管理画面「Monthly Room Booking」メニューの表示
- 物件マスタに部屋データの登録
- オプション・キャンペーンの設定

---

## 🔧 開発者向け情報

### 主要フック
```php
// カスタムフック例
do_action('monthly_booking_after_estimate', $estimate_data);
do_action('monthly_booking_before_booking_save', $booking_data);
apply_filters('monthly_booking_campaign_discount', $discount, $campaign);
```

### 拡張ポイント
- **新プラン追加**: `determine_plan_by_duration()`関数の拡張
- **新キャンペーン**: `get_applicable_campaigns()`の条件追加
- **新オプション**: オプションマスタテーブルへの追加
- **多言語対応**: `__()`, `_e()`関数使用済み

### デバッグ情報
```php
// デバッグログ有効化
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// ログ確認場所
// wp-content/debug.log
```

---

## 📊 パフォーマンス

### データベース最適化
- 主要カラムにインデックス設定済み
- `room_id`, `is_active`, `start_date`, `end_date`等
- 外部キー制約による整合性保証

### キャッシュ対応
- WordPressオブジェクトキャッシュ対応
- 静的ファイル（CSS/JS）のバージョニング

---

## 🔒 セキュリティ

### 実装済み対策
- **CSRF保護**: `wp_nonce_field()`, `check_ajax_referer()`
- **データサニタイズ**: `sanitize_text_field()`, `sanitize_email()`
- **SQLインジェクション対策**: `$wpdb->prepare()`
- **権限チェック**: `current_user_can('manage_options')`
- **XSS対策**: `esc_html()`, `esc_attr()`

---

## 📈 今後の拡張予定

### 機能拡張
- [ ] 複数物件対応
- [ ] 予約承認フロー
- [ ] 支払い連携（Stripe/PayPal）
- [ ] メール通知システム
- [ ] レポート・分析機能

### 技術改善
- [ ] REST API対応
- [ ] React/Vue.js フロントエンド
- [ ] 多言語対応強化
- [ ] モバイルアプリ対応

---

**最終更新**: 2025年8月3日  
**作成者**: Devin AI (@yoshaaa888)  
**GitHub**: https://github.com/yoshaaa888/monthly-booking
