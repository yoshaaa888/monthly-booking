# 次フェーズ: typeカラム追加とコミコミ10万円キャンペーン設計

## 🎯 実装目標

### 1. wp_monthly_campaigns テーブルにtypeカラム追加
- 新カラム: `type` VARCHAR(20) DEFAULT NULL
- 対応値: "earlybird", "immediate", "season", "flatrate"
- 既存データの移行: 説明文ベースから type 値への変換

### 2. コミコミ10万円キャンペーン設計
- **キャンペーン名**: コミコミ10万円キャンペーン
- **type**: "flatrate"
- **料金体系**: 固定価格 ¥100,000（税込）
- **適用条件**: 7〜10日以内の滞在
- **対象プラン**: SS, S プラン
- **割引方式**: 固定価格設定（通常料金との差額を割引として計算）

## 📋 実装計画

### Phase 1: データベーススキーマ拡張
1. `wp_monthly_campaigns` テーブルにtypeカラム追加
2. 既存キャンペーンデータの type 値設定
   - 即入居割 → type="immediate"
   - 早割 → type="earlybird"
3. campaign-manager.php の判定ロジック更新

### Phase 2: コミコミ10万円キャンペーン実装
1. 新キャンペーンデータ挿入
2. flatrate タイプの料金計算ロジック追加
3. 7-10日条件の判定ロジック実装
4. 見積もり画面での表示対応

### Phase 3: 管理画面対応
1. typeカラムの管理画面表示
2. flatrateキャンペーンの作成・編集機能
3. 固定価格設定のUI実装

## 🔧 技術仕様

### typeカラム追加SQL
```sql
ALTER TABLE wp_monthly_campaigns 
ADD COLUMN type VARCHAR(20) DEFAULT NULL 
AFTER campaign_description;

-- 既存データ更新
UPDATE wp_monthly_campaigns 
SET type = 'immediate' 
WHERE campaign_name LIKE '%即入居%' OR campaign_description LIKE '%即入居%';

UPDATE wp_monthly_campaigns 
SET type = 'earlybird' 
WHERE campaign_name LIKE '%早割%' OR campaign_description LIKE '%早割%';
```

### コミコミ10万円キャンペーンデータ
```sql
INSERT INTO wp_monthly_campaigns (
    campaign_name, 
    campaign_description, 
    type,
    discount_type, 
    discount_value, 
    min_stay_days,
    max_stay_days,
    start_date, 
    end_date, 
    target_plan,
    is_active
) VALUES (
    'コミコミ10万円キャンペーン',
    '7〜10日滞在で全込み10万円の特別料金',
    'flatrate',
    'flatrate',
    100000.00,
    7,
    10,
    '2025-01-01',
    '2099-12-31',
    'SS,S',
    1
);
```

### 判定ロジック更新
```php
// campaign-manager.php 更新案
private function get_campaign_by_type($type) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'monthly_campaigns';
    $today = date('Y-m-d');
    
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name 
         WHERE is_active = 1 
         AND start_date <= %s 
         AND end_date >= %s 
         AND type = %s
         ORDER BY discount_value DESC 
         LIMIT 1",
        $today,
        $today,
        $type
    );
    
    return $wpdb->get_row($sql);
}
```

## 🧪 テスト計画

### テストケース
1. **7-10日滞在**: コミコミ10万円適用確認
2. **境界値テスト**: 7日、10日ちょうどの場合
3. **プラン制限**: SS/Sプランのみ適用確認
4. **優先度テスト**: 他キャンペーンとの競合時の選択ロジック
5. **固定価格計算**: 通常料金との差額計算確認

### 検証項目
- [ ] typeカラム追加とデータ移行
- [ ] 既存キャンペーン機能の継続動作
- [ ] コミコミ10万円キャンペーンの正確な適用
- [ ] 見積もり画面での表示確認
- [ ] 最大1キャンペーンルールの維持

## 📅 実装スケジュール

1. **Phase 1**: スキーマ拡張（30分）
2. **Phase 2**: コミコミキャンペーン実装（60分）
3. **Phase 3**: テスト・検証（30分）
4. **Phase 4**: ドキュメント更新・PR作成（15分）

**合計予定時間**: 約2時間15分
