# 成果物2：変更箇所の差分（Diff）レポート

## 修正ファイル別差分レポート

### 1. monthly-booking.php の変更内容

```diff
--- a/monthly-booking.php
+++ b/monthly-booking.php
@@ -638,42 +638,6 @@ class MonthlyBooking {
                 'description' => '追加子ども1名あたりの1日光熱費',
                 'display_order' => 9
             ),
-            array(
-                'setting_key' => 'default_rent_ss',
-                'setting_name' => 'デフォルト日額賃料（SSプラン）',
-                'setting_value' => 2500.00,
-                'unit_type' => 'daily',
-                'category' => 'default_rates',
-                'description' => 'SSプランのデフォルト日額賃料',
-                'display_order' => 10
-            ),
-            array(
-                'setting_key' => 'default_rent_s',
-                'setting_name' => 'デフォルト日額賃料（Sプラン）',
-                'setting_value' => 2000.00,
-                'unit_type' => 'daily',
-                'category' => 'default_rates',
-                'description' => 'Sプランのデフォルト日額賃料',
-                'display_order' => 11
-            ),
-            array(
-                'setting_key' => 'default_rent_m',
-                'setting_name' => 'デフォルト日額賃料（Mプラン）',
-                'setting_value' => 1900.00,
-                'unit_type' => 'daily',
-                'category' => 'default_rates',
-                'description' => 'Mプランのデフォルト日額賃料',
-                'display_order' => 12
-            ),
-            array(
-                'setting_key' => 'default_rent_l',
-                'setting_name' => 'デフォルト日額賃料（Lプラン）',
-                'setting_value' => 1800.00,
-                'unit_type' => 'daily',
-                'category' => 'default_rates',
-                'description' => 'Lプランのデフォルト日額賃料',
-                'display_order' => 13
-            ),
             array(
                 'setting_key' => 'option_discount_max',
                 'setting_name' => 'オプション割引上限額',
```

**変更概要**: 
- 4つのデフォルト日額賃料エントリ（SS/S/M/Lプラン）を完全削除
- 合計36行のコードを削除
- `default_rates` カテゴリが完全に除去され、料金設定ページに表示されなくなる

### 2. includes/campaign-manager.php の変更内容

```diff
--- a/includes/campaign-manager.php
+++ b/includes/campaign-manager.php
@@ -173,6 +173,18 @@ class MonthlyBooking_Campaign_Manager {
             return new WP_Error('invalid_name', __('Campaign name is required.', 'monthly-booking'));
         }
         
+        global $wpdb;
+        $campaigns_table = $wpdb->prefix . 'monthly_campaigns';
+        $existing_campaign = $wpdb->get_var($wpdb->prepare(
+            "SELECT id FROM $campaigns_table WHERE name = %s AND id != %d",
+            $data['name'],
+            isset($data['campaign_id']) ? intval($data['campaign_id']) : 0
+        ));
+        
+        if ($existing_campaign) {
+            return new WP_Error('duplicate_name', __('キャンペーン名が既に存在します。別の名前を選択してください。', 'monthly-booking'));
+        }
+        
         if (!in_array($data['discount_type'], array('percentage', 'fixed'))) {
             return new WP_Error('invalid_discount_type', __('Invalid discount type.', 'monthly-booking'));
         }
@@ -208,6 +220,13 @@ class MonthlyBooking_Campaign_Manager {
             return new WP_Error('invalid_date_range', __('End date must be after start date.', 'monthly-booking'));
         }
         
+        $max_date = new DateTime();
+        $max_date->add(new DateInterval('P180D'));
+        
+        if ($end > $max_date) {
+            return new WP_Error('invalid_end_date', __('終了日は今日から180日以内に設定してください。', 'monthly-booking'));
+        }
+        
         return true;
     }
```

**変更概要**:
- キャンペーン名の重複チェック機能を追加（12行追加）
- 180日制限チェック機能を追加（7行追加）
- 合計19行のコードを追加

### 3. includes/admin-ui.php の変更内容

```diff
--- a/includes/admin-ui.php
+++ b/includes/admin-ui.php
@@ -14,7 +14,6 @@ class MonthlyBooking_Admin_UI {
     public function __construct() {
         add_action('admin_menu', array($this, 'add_admin_menu'));
         add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
-        add_action('admin_init', array($this, 'register_settings'));
     }
```

**変更概要**:
- `register_settings` アクションフックを削除（1行削除）
- プラグイン設定ページが管理メニューから完全に除去される

## 修正の影響範囲

### 1. 料金設定ページ（default_rates削除）
- **影響**: 料金設定ページから「デフォルト日額賃料」セクションが完全に消える
- **確認方法**: WordPress管理画面 → Monthly Room Booking → 料金設定

### 2. キャンペーン設定（重複チェック・日付制限）
- **影響**: 同じ名前のキャンペーン作成時にエラー表示、180日超過時にエラー表示
- **確認方法**: WordPress管理画面 → Monthly Room Booking → キャンペーン設定

### 3. プラグイン設定ページ（完全削除）
- **影響**: 管理メニューから「プラグイン設定」項目が完全に消える
- **確認方法**: WordPress管理画面左側メニューの確認

## 総変更行数
- **削除**: 37行
- **追加**: 19行
- **正味変更**: -18行（コードの簡素化）
