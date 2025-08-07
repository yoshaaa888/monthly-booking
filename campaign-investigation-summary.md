# キャンペーン機能実装に向けた事前調査結果

## 🧩 調査①：`campaign-manager.php:281-355` のロジック分析

### キャンペーン適用判定の条件ロジック

現在の実装では以下の判定ロジックが使用されています：

```php
// 即入居割判定（7日以内）
if ($campaign->type === 'immediate' && $days_until_checkin <= 7) {
    $applicable_campaigns[] = $campaign;
}

// 早割判定（30日以上前）
if ($campaign->type === 'earlybird' && $days_until_checkin >= 30) {
    $applicable_campaigns[] = $campaign;
}

// コミコミ10万円判定（7-10日滞在）
if ($campaign->type === 'flatrate' && $stay_days >= 7 && $stay_days <= 10) {
    $applicable_campaigns[] = $campaign;
}
```

### 複数該当時の優先度処理・排他ロジック

- **排他制御**: `return !empty($applicable_campaigns) ? $applicable_campaigns[0] : null;`
- **優先度処理**: 割引額による降順ソート実装済み
- **最高優先度選択**: 配列の最初の要素のみを返却

### ハードコーディングされている条件と技術的負債

| 項目 | 現在の実装 | 技術的負債 |
|------|------------|------------|
| 日数条件 | 7日、30日がハードコード | 設定可能にすべき |
| キャンペーンタイプ | type-based判定 | 拡張性に課題 |
| 滞在日数判定 | flatrateで7-10日固定 | 柔軟性不足 |
| 割引計算 | 個別ロジック散在 | 統一化必要 |

### 設計書に転用可能なアルゴリズムの要点

1. **期間ベース判定**: チェックイン日からの日数計算
2. **滞在期間判定**: 滞在日数による適用条件
3. **排他制御**: 1つのキャンペーンのみ適用
4. **優先度ソート**: 割引額降順での自動選択

---

## 🧩 調査②：`wp_monthly_campaigns` テーブルの使用箇所と影響範囲

### コードベース内での読み書き箇所一覧

| ファイル | 関数名 | 行番号 | 用途分類 |
|----------|--------|--------|----------|
| `campaign-manager.php` | `get_campaign_by_type` | 376-392 | 判定処理 |
| `campaign-manager.php` | `ajax_create_campaign` | 24-54 | AJAX作成 |
| `campaign-manager.php` | `ajax_update_campaign` | 59-87 | AJAX更新 |
| `campaign-manager.php` | `ajax_delete_campaign` | 92-108 | AJAX削除 |
| `admin-ui.php` | `admin_page_campaign_settings` | 1115 | 管理画面UI |
| `booking-logic.php` | `get_applicable_campaigns` | 複数箇所 | 見積計算 |

### 旧テーブル参照箇所

- `wp_monthly_booking_campaigns` を参照する箇所が混在
- `get_campaigns()` メソッドで旧テーブル使用
- データ不整合のリスク有り

### 統一移行時の注意点

1. **データ整合性**: 2つのテーブル間でのデータ同期
2. **参照整合性**: 外部キー制約の確認
3. **バックアップ**: 移行前の完全バックアップ必須
4. **段階的移行**: 一括変更ではなく段階的な切り替え

---

## 🧩 調査③：データ移行スクリプト（雛形）

### フィールドマッピング推定

```sql
-- 旧テーブル → 新テーブル マッピング
INSERT INTO wp_monthly_campaigns (
    campaign_name,           -- name
    type,                    -- 'immediate'/'earlybird'/'flatrate'
    discount_type,           -- discount_type
    discount_value,          -- discount_value
    start_date,              -- start_date
    end_date,                -- end_date
    target_plan,             -- 'ALL'/'SS'/'S'/'M'/'L'
    is_active,               -- is_active
    applicable_rooms         -- '' (空文字で初期化)
) 
SELECT 
    name,
    CASE 
        WHEN description LIKE '%即入居%' THEN 'immediate'
        WHEN description LIKE '%早割%' THEN 'earlybird'
        WHEN description LIKE '%コミコミ%' THEN 'flatrate'
        ELSE 'immediate'
    END,
    discount_type,
    discount_value,
    start_date,
    end_date,
    'ALL',
    is_active,
    ''
FROM wp_monthly_booking_campaigns
WHERE is_active = 1;
```

### バリデーション機能

```php
function validate_campaign_data($campaign) {
    $errors = [];
    
    // 必須項目チェック
    if (empty($campaign['campaign_name'])) {
        $errors[] = 'キャンペーン名は必須です';
    }
    
    // 日付妥当性チェック
    if (strtotime($campaign['start_date']) >= strtotime($campaign['end_date'])) {
        $errors[] = '開始日は終了日より前である必要があります';
    }
    
    // 割引値チェック
    if ($campaign['discount_value'] <= 0 || $campaign['discount_value'] > 100) {
        $errors[] = '割引値は1-100の範囲で入力してください';
    }
    
    return $errors;
}
```

### ログ出力機能

```php
function log_migration_result($success_count, $error_count, $errors) {
    $log_message = sprintf(
        "[%s] 移行完了: 成功 %d件, 失敗 %d件\n",
        date('Y-m-d H:i:s'),
        $success_count,
        $error_count
    );
    
    if (!empty($errors)) {
        $log_message .= "エラー詳細:\n" . implode("\n", $errors) . "\n";
    }
    
    file_put_contents('migration.log', $log_message, FILE_APPEND);
}
```

---

## 🧩 調査④：AJAX処理とJavaScript連携仕様

### 実装済みAJAX関数一覧

| 関数名 | 行番号 | 用途 | HTTPメソッド |
|--------|--------|------|--------------|
| `ajax_create_campaign` | 24-54 | キャンペーン作成 | POST |
| `ajax_update_campaign` | 59-87 | キャンペーン更新 | POST |
| `ajax_delete_campaign` | 92-108 | キャンペーン削除 | POST |
| `ajax_toggle_campaign` | 113-131 | ステータス切替 | POST |

### パラメータ仕様

#### 作成・更新共通パラメータ
```javascript
{
    "action": "monthly_booking_create_campaign", // または update
    "campaign_id": 123,                          // 更新時のみ
    "name": "キャンペーン名",
    "discount_type": "percentage",               // percentage/fixed/flatrate
    "discount_value": 20.00,
    "start_date": "2025-01-01",
    "end_date": "2025-12-31",
    "target_plan": "ALL",                        // ALL/SS/S/M/L
    "type": "immediate",                         // immediate/earlybird/flatrate
    "is_active": 1
}
```

### 返却形式

```javascript
// 成功時
{
    "success": true,
    "data": {
        "campaign_id": 123,
        "message": "キャンペーンが作成されました"
    }
}

// エラー時
{
    "success": false,
    "data": {
        "errors": ["エラーメッセージ1", "エラーメッセージ2"]
    }
}
```

### バリデーション要件

現在実装済みのバリデーション：

1. **必須項目チェック**: name, discount_type, discount_value, start_date, end_date
2. **割引率上限**: percentage型で100%以下
3. **日付範囲**: start_date < end_date
4. **数値検証**: discount_valueの正数チェック

### UI側JavaScript連携推奨仕様

```javascript
// 推奨パラメータ形式
const campaignData = {
    name: document.getElementById('campaign_name').value,
    type: document.getElementById('campaign_type').value,
    discount_type: document.getElementById('discount_type').value,
    discount_value: parseFloat(document.getElementById('discount_value').value),
    start_date: document.getElementById('start_date').value,
    end_date: document.getElementById('end_date').value,
    target_plan: document.getElementById('target_plan').value,
    applicable_rooms: getSelectedRooms(), // 部屋選択UI用
    is_active: document.getElementById('is_active').checked ? 1 : 0
};

// AJAX送信例
jQuery.post(ajaxurl, {
    action: 'monthly_booking_create_campaign',
    ...campaignData
}, function(response) {
    if (response.success) {
        showSuccessMessage(response.data.message);
        refreshCampaignList();
    } else {
        showErrorMessages(response.data.errors);
    }
});
```

---

## 📋 調査結果サマリー

### 現在の実装状況
- ✅ AJAX処理: 完全実装済み
- ✅ バリデーション: 基本機能実装済み
- ⚠️ データベース: 2つのテーブルが混在
- ❌ 部屋単位管理: 未実装

### 設計書作成時の重要ポイント
1. **排他制御**: 1部屋1期間1キャンペーンの実装方法
2. **データ統一**: wp_monthly_campaigns への完全移行
3. **UI拡張**: 部屋別キャンペーン割当機能
4. **運用ルール**: 現場スタッフ向けの明確なガイドライン

### 次フェーズでの実装優先度
1. **High**: データベーススキーマ統一
2. **High**: 部屋別キャンペーン割当UI
3. **Medium**: 期間重複チェック機能
4. **Low**: レポート・分析機能
