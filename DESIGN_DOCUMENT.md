# Monthly Booking WordPress Plugin - 設計書

## 📋 プラグイン概要

### 基本情報
- **プラグイン名**: Monthly Room Booking
- **バージョン**: 1.5.7
- **作成者**: Yoshi
- **ライセンス**: GPL v2 or later
- **テキストドメイン**: monthly-booking

### 目的
月単位の物件予約管理を行うWordPressプラグイン。物件マスタ管理、予約カレンダー、料金計算、キャンペーン管理、予約申し込み機能を統合的に提供。

---

## 🗄️ データベース設計

### テーブル構成（7テーブル）

#### 1. monthly_rooms（物件・部屋マスタ）
```sql
CREATE TABLE monthly_rooms (
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
    PRIMARY KEY (id)
);
```

**特徴**:
- 最大3路線のアクセス情報を格納
- 日割り賃料（daily_rent）を基準とした料金体系
- 物件設備・画像情報の管理

#### 2. monthly_bookings（予約データ）
```sql
CREATE TABLE monthly_bookings (
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
    PRIMARY KEY (id)
);
```

**特徴**:
- 詳細な料金内訳を保存
- プラン種別（SS/S/M/L）の管理
- 予約・支払いステータスの追跡

#### 3. monthly_customers（顧客情報）
```sql
CREATE TABLE monthly_customers (
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
    UNIQUE KEY email (email)
);
```

**特徴**:
- 緊急連絡先情報の管理
- 身分証明書情報の保存
- メールアドレスによる重複防止

#### 4. monthly_campaigns（キャンペーン管理）
```sql
CREATE TABLE monthly_campaigns (
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
    PRIMARY KEY (id)
);
```

**特徴**:
- パーセンテージ・固定額割引の対応
- 適用期間・予約期間の管理
- 利用回数制限機能

#### 5. monthly_options（オプション商品マスタ）
```sql
CREATE TABLE monthly_options (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    option_name varchar(100) NOT NULL,
    option_description text,
    price decimal(10,2) NOT NULL,
    is_discount_target tinyint(1) DEFAULT 1,
    display_order int(3) DEFAULT 0,
    is_active tinyint(1) DEFAULT 1,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

**特徴**:
- セット割引対象フラグ（is_discount_target）
- 表示順序の管理

#### 6. monthly_booking_options（予約オプション関連）
```sql
CREATE TABLE monthly_booking_options (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    booking_id mediumint(9) NOT NULL,
    option_id mediumint(9) NOT NULL,
    quantity int(2) DEFAULT 1,
    unit_price decimal(10,2) NOT NULL,
    total_price decimal(10,2) NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY booking_option (booking_id, option_id)
);
```

#### 7. monthly_rates（料金体系テーブル）
```sql
CREATE TABLE monthly_rates (
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
    PRIMARY KEY (id)
);
```

**特徴**:
- 将来の料金体系拡張に対応
- 期間限定料金の管理

---

## 🏗️ アーキテクチャ設計

### ファイル構成
```
monthly-booking/
├── monthly-booking.php          # メインプラグインファイル
├── includes/
│   ├── admin-ui.php            # 管理画面UI
│   ├── booking-logic.php       # 予約ロジック・AJAX処理
│   ├── calendar-render.php     # カレンダー・見積フォーム表示
│   └── campaign-manager.php    # キャンペーン管理
├── assets/
│   ├── admin.css              # 管理画面スタイル
│   ├── admin.js               # 管理画面JavaScript
│   ├── calendar.css           # カレンダースタイル
│   ├── calendar.js            # カレンダーJavaScript
│   └── estimate.js            # 見積フォームJavaScript
└── README.md
```

### クラス構成
- **MonthlyBooking**: メインプラグインクラス
- **MonthlyBooking_Admin_UI**: 管理画面機能
- **MonthlyBooking_Booking_Logic**: 予約・料金計算ロジック
- **MonthlyBooking_Calendar_Render**: フロントエンド表示
- **MonthlyBooking_Campaign_Manager**: キャンペーン管理

---

## 🎯 機能仕様

### 1. プラン自動判定システム

#### プラン種別
| プラン | 滞在期間 | 日割賃料 | 共益費/日 |
|--------|----------|----------|-----------|
| SS     | 7-29日   | 物件設定値 | ¥2,500   |
| S      | 30-89日  | 物件設定値 | ¥2,000   |
| M      | 90-179日 | 物件設定値 | ¥2,000   |
| L      | 180日+   | 物件設定値 | ¥2,000   |

#### 自動判定ロジック
```javascript
function determinePlanByDuration(days) {
    if (days >= 7 && days <= 29) return 'SS';
    if (days >= 30 && days <= 89) return 'S';
    if (days >= 90 && days <= 179) return 'M';
    if (days >= 180) return 'L';
    return null;
}
```

### 2. 料金計算システム

#### 料金構成要素
1. **基本賃料**: 日割賃料 × 滞在日数
2. **共益費**: プラン別日額 × 滞在日数
3. **初期費用**: 固定額（清掃費¥38,500 + 鍵手数料¥11,000 + 布団代¥11,000）
4. **人数追加料金**: 
   - 大人: 2人目以降 ¥1,000/日
   - 子供: ¥500/日
5. **オプション費用**: セット価格 - セット割引
6. **キャンペーン割引**: 自動適用

#### オプションセット割引
- 2つ選択: -¥500
- 3つ以上: -¥500 + (追加数 × ¥300)
- 最大割引額: ¥2,000
- 対象: オプション1-7番のみ（8-9番は対象外）

### 3. キャンペーン自動適用システム

#### キャンペーン種別
1. **早割キャンペーン**
   - 条件: 入居30日以上前の予約
   - 割引: 賃料・共益費の10%OFF
   - バッジ: "早割"

2. **即入居割**
   - 条件: 入居7日以内の予約
   - 割引: 賃料・共益費の20%OFF
   - バッジ: "即入居"

#### 自動判定ロジック
```php
public function get_applicable_campaigns($checkin_date) {
    $today = new DateTime();
    $checkin = new DateTime($checkin_date);
    $days_until_checkin = $today->diff($checkin)->days;
    
    if ($days_until_checkin <= 7) {
        // 即入居割適用
    }
    if ($days_until_checkin >= 30) {
        // 早割適用
    }
}
```

---

## 🔌 API設計（AJAX エンドポイント）

### フロントエンド用エンドポイント（6個）
1. **calculate_estimate** - 見積計算
2. **submit_booking** - 予約申し込み
3. **get_booking_options** - オプション一覧取得
4. **search_properties** - 物件検索
5. **get_search_filters** - 検索フィルター取得
6. **calculate_booking_price** - 料金計算（レガシー）

### 管理画面用エンドポイント（4個）
1. **create_campaign** - キャンペーン作成
2. **update_campaign** - キャンペーン更新
3. **delete_campaign** - キャンペーン削除
4. **toggle_campaign** - キャンペーン有効/無効切り替え

### API仕様例

#### calculate_estimate
```javascript
// リクエスト
{
    action: 'calculate_estimate',
    nonce: 'security_nonce',
    room_id: 1,
    move_in_date: '2025-03-01',
    move_out_date: '2025-06-01',
    num_adults: 2,
    num_children: 1,
    selected_options: {1: 1, 2: 1, 3: 1},
    guest_name: '田中太郎',
    guest_email: 'tanaka@example.com'
}

// レスポンス
{
    success: true,
    data: {
        plan: 'M',
        stay_days: 92,
        total_rent: 220800,
        total_utilities: 184000,
        initial_costs: 60500,
        person_additional_fee: 92000,
        options_total: 14300,
        options_discount: 500,
        campaign_discount: 40480,
        final_total: 530620,
        campaign_badge: '早割',
        campaign_type: 'early'
    }
}
```

---

## 🖥️ UI/UX設計

### フロントエンド

#### 1. 見積フォーム（[monthly_booking_estimate]ショートコード）
```html
<form id="monthly-booking-estimate-form">
    <!-- 物件選択 -->
    <select id="room_id" name="room_id">
        <option value="">物件を選択してください</option>
    </select>
    
    <!-- 日程選択 -->
    <input type="date" id="move_in_date" name="move_in_date">
    <input type="date" id="move_out_date" name="move_out_date">
    
    <!-- 人数選択 -->
    <select id="num_adults" name="num_adults">
        <option value="1">大人1名</option>
        <option value="2">大人2名</option>
    </select>
    
    <!-- オプション選択 -->
    <div class="options-section">
        <input type="checkbox" name="options[]" value="1"> 調理器具セット ¥6,600
        <input type="checkbox" name="options[]" value="2"> 食器類 ¥3,900
        <!-- ... -->
    </div>
    
    <!-- 顧客情報 -->
    <input type="text" id="guest_name" name="guest_name" placeholder="お名前">
    <input type="email" id="guest_email" name="guest_email" placeholder="メールアドレス">
    
    <button type="button" id="calculate-estimate-btn">見積計算</button>
</form>
```

#### 2. 見積結果表示
```html
<div id="estimate-result">
    <div class="cost-breakdown">
        <div class="cost-item">
            <span>基本賃料（92日間）</span>
            <span>¥220,800</span>
        </div>
        <div class="cost-item">
            <span>共益費（92日間）</span>
            <span>¥184,000</span>
        </div>
        <div class="cost-item discount">
            <span>キャンペーン割引 <span class="campaign-badge early">早割</span></span>
            <span>-¥40,480</span>
        </div>
        <div class="cost-total">
            <span>合計金額</span>
            <span>¥530,620</span>
        </div>
    </div>
    
    <button id="submit-booking-btn">この内容で申し込む</button>
</div>
```

### 管理画面

#### メニュー構成
1. **物件マスタ管理** - 物件・部屋情報の管理
2. **予約カレンダー** - 180日間のカレンダー表示
3. **予約登録** - 手動予約登録
4. **売上サマリー** - 売上集計表示
5. **キャンペーン設定** - キャンペーン管理
6. **オプション管理** - オプション商品管理
7. **プラグイン設定** - 基本設定

#### カレンダー表示
```html
<div class="calendar-container">
    <div class="calendar-header">
        <select id="room-selector">
            <option value="1">立川マンスリー101号室</option>
        </select>
    </div>
    
    <table class="booking-calendar">
        <thead>
            <tr>
                <th>プラン</th>
                <th>1日</th>
                <th>2日</th>
                <!-- ... 31日まで -->
            </tr>
        </thead>
        <tbody>
            <tr class="plan-row" data-plan="SS">
                <td>SS</td>
                <td class="available">〇</td>
                <td class="unavailable">×</td>
                <td class="campaign">△ 早割</td>
                <!-- ... -->
            </tr>
            <!-- S, M, L プラン行 -->
        </tbody>
    </table>
</div>
```

---

## 🔄 ビジネスロジック

### 予約申し込みフロー
1. **見積計算**: フォーム入力 → AJAX計算 → 結果表示
2. **申し込み**: 「申し込む」ボタン → データ検証 → DB保存
3. **外部連携**: 経理システムへPOST送信
4. **完了通知**: 仮予約完了画面表示

### データ整合性
- **顧客重複防止**: メールアドレスによるユニーク制約
- **予約重複チェック**: 日程・部屋の重複検証
- **トランザクション管理**: 予約・顧客・オプションの一括処理

### セキュリティ対策
- **nonce検証**: 全AJAX処理でCSRF対策
- **権限チェック**: 管理機能でcapability確認
- **データサニタイズ**: 全入力値の無害化処理
- **SQLインジェクション対策**: prepared statement使用

---

## 🔧 技術仕様

### WordPress標準準拠
- **フック使用**: add_action, add_filter
- **国際化対応**: __(), _e()関数
- **設定API**: register_setting, add_settings_section
- **データベース**: $wpdb, dbDelta()使用

### フロントエンド技術
- **jQuery**: DOM操作・AJAX通信
- **CSS3**: レスポンシブデザイン
- **HTML5**: セマンティックマークアップ

### 外部システム連携
```php
// 経理システムPOST送信
$external_url = 'https://accounting-system.example.com/api/bookings';
$response = wp_remote_post($external_url, array(
    'body' => json_encode($booking_data),
    'headers' => array('Content-Type' => 'application/json')
));
```

### パフォーマンス最適化
- **インデックス設定**: 検索頻度の高いカラムにINDEX
- **キャッシュ対応**: WordPress標準キャッシュ機能
- **アセット最適化**: CSS/JS圧縮・結合

---

## 📊 運用・保守

### ログ機能
- **予約ログ**: 予約作成・更新・削除の記録
- **エラーログ**: AJAX処理・外部連携エラーの記録
- **キャンペーンログ**: 適用・利用状況の記録

### バックアップ対応
- **データベース**: 全テーブルのバックアップ推奨
- **設定情報**: WordPress options_table
- **アップロードファイル**: 物件画像等

### 拡張性
- **新プラン追加**: プラン判定ロジックの拡張可能
- **新キャンペーン**: キャンペーンマネージャーで柔軟対応
- **新オプション**: オプションマスタで簡単追加
- **多言語対応**: 国際化関数使用で翻訳ファイル対応

---

## 🚀 デプロイメント

### 必要環境
- **WordPress**: 5.0以上
- **PHP**: 7.4以上
- **MySQL/MariaDB**: 5.6以上

### インストール手順
1. プラグインファイルを`wp-content/plugins/monthly-booking/`に配置
2. WordPress管理画面でプラグインを有効化
3. データベーステーブルが自動作成される
4. サンプルデータが挿入される
5. 管理画面メニューが追加される

### 設定項目
- **外部経理システムURL**: POST送信先の設定
- **デフォルト料金**: プラン別基本料金
- **メール設定**: 通知メールの設定
- **表示設定**: カレンダー・フォームの表示オプション

---

## 📈 今後の拡張予定

### Phase 2 機能
- **メール通知機能**: 予約確認・リマインダーメール
- **決済連携**: オンライン決済システム統合
- **レポート機能**: 売上・稼働率分析
- **API公開**: 外部システム連携用REST API

### Phase 3 機能
- **モバイルアプリ**: 専用アプリ開発
- **多拠点対応**: 複数物件管理の強化
- **AI機能**: 需要予測・価格最適化
- **CRM連携**: 顧客管理システム統合

---

*このドキュメントは Monthly Booking Plugin v1.5.7 の設計仕様書です。*
*最終更新: 2025年8月2日*
