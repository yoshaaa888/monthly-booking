# wp_monthly_options 修正前後のSQL実行ログ

## 修正前の状態確認

### 1. 重複オプション名の確認
```sql
SELECT 
    option_name, 
    COUNT(*) as duplicate_count,
    GROUP_CONCAT(id ORDER BY id) as all_ids,
    GROUP_CONCAT(price ORDER BY id) as all_prices,
    GROUP_CONCAT(is_discount_target ORDER BY id) as all_targets,
    GROUP_CONCAT(display_order ORDER BY id) as all_orders
FROM wp_monthly_options 
GROUP BY option_name 
ORDER BY duplicate_count DESC, option_name;
```

**実行結果（修正前）:**
```
| option_name | duplicate_count | all_ids | all_prices | all_targets | all_orders |
|-------------|-----------------|---------|------------|-------------|------------|
| 食器類      | 3               | 2,10,11 | 3900,3900,4000 | 1,1,0    | 2,2,0      |
| アイロン    | 2               | 8,13    | 6860,6860  | 0,1         | 8,8        |
| 洗剤類      | 2               | 3,12    | 3800,3800  | 1,1         | 3,3        |
| 調理器具セット | 1            | 1       | 6600       | 1           | 1          |
| タオル類    | 1               | 4       | 2900       | 1           | 4          |
| アメニティ類 | 1              | 5       | 3500       | 1           | 5          |
| 寝具カバーセット | 1          | 6       | 4530       | 1           | 6          |
| 毛布        | 1               | 7       | 3950       | 1           | 7          |
| 炊飯器（4合炊き） | 1         | 9       | 6600       | 0           | 9          |
```

### 2. is_discount_target正確性チェック（修正前）
```sql
SELECT 
    id, option_name, 
    is_discount_target as current_target,
    CASE 
        WHEN id BETWEEN 1 AND 7 THEN 1
        WHEN id BETWEEN 8 AND 9 THEN 0
        ELSE -1
    END as expected_target,
    CASE 
        WHEN is_discount_target = CASE 
            WHEN id BETWEEN 1 AND 7 THEN 1
            WHEN id BETWEEN 8 AND 9 THEN 0
            ELSE -1
        END THEN '✅ 正確'
        ELSE '❌ 要修正'
    END as status,
    display_order, price
FROM wp_monthly_options 
ORDER BY id;
```

**実行結果（修正前）:**
```
| id | option_name | current_target | expected_target | status | display_order | price |
|----|-------------|----------------|-----------------|--------|---------------|-------|
| 1  | 調理器具セット | 1            | 1               | ✅ 正確 | 1             | 6600  |
| 2  | 食器類      | 1              | 1               | ✅ 正確 | 2             | 3900  |
| 3  | 洗剤類      | 1              | 1               | ✅ 正確 | 3             | 3800  |
| 4  | タオル類    | 1              | 1               | ✅ 正確 | 4             | 2900  |
| 5  | アメニティ類 | 1             | 1               | ✅ 正確 | 5             | 3500  |
| 6  | 寝具カバーセット | 1         | 1               | ✅ 正確 | 6             | 4530  |
| 7  | 毛布        | 1              | 1               | ✅ 正確 | 7             | 3950  |
| 8  | アイロン    | 0              | 0               | ✅ 正確 | 8             | 6860  |
| 9  | 炊飯器（4合炊き） | 0        | 0               | ✅ 正確 | 9             | 6600  |
| 10 | 食器類      | 1              | -1              | ❌ 要修正 | 2           | 3900  |
| 11 | 食器類      | 0              | -1              | ❌ 要修正 | 0           | 4000  |
| 12 | 洗剤類      | 1              | -1              | ❌ 要修正 | 3           | 3800  |
| 13 | アイロン    | 1              | -1              | ❌ 要修正 | 8           | 6860  |
```

### 3. 総レコード数確認（修正前）
```sql
SELECT COUNT(*) as total_records FROM wp_monthly_options;
```
**結果:** 13件（期待値: 9件）

## 修正実行

### Step 1: バックアップ作成
```sql
CREATE TABLE wp_monthly_options_backup_20250804 AS SELECT * FROM wp_monthly_options;
```
**結果:** バックアップテーブル作成完了（13件のレコードをバックアップ）

### Step 2: 重複削除（最古IDを保持）
```sql
DELETE o1 FROM wp_monthly_options o1
INNER JOIN wp_monthly_options o2
WHERE o1.option_name = o2.option_name AND o1.id > o2.id;
```
**結果:** 4件のレコードを削除（ID: 10, 11, 12, 13）

### Step 3: is_discount_target修正（IDベース）
```sql
UPDATE wp_monthly_options SET is_discount_target = 1 WHERE id BETWEEN 1 AND 7;
UPDATE wp_monthly_options SET is_discount_target = 0 WHERE id BETWEEN 8 AND 9;
```
**結果:** 
- 7件のレコードでis_discount_target = 1に設定
- 2件のレコードでis_discount_target = 0に設定

## 修正後の状態確認

### 1. 重複確認（修正後）
```sql
SELECT option_name, COUNT(*) as count FROM wp_monthly_options GROUP BY option_name HAVING COUNT(*) > 1;
```
**結果:** 0件（重複なし）

### 2. 総レコード数確認（修正後）
```sql
SELECT COUNT(*) as total_records FROM wp_monthly_options;
```
**結果:** 9件（期待値と一致）

### 3. 最終データ確認（修正後）
```sql
SELECT 
    id, option_name, price, is_discount_target, display_order,
    CASE 
        WHEN is_discount_target = CASE 
            WHEN id BETWEEN 1 AND 7 THEN 1
            WHEN id BETWEEN 8 AND 9 THEN 0
            ELSE -1
        END THEN '✅ 正確'
        ELSE '❌ 不正確'
    END as target_flag_status
FROM wp_monthly_options 
ORDER BY display_order;
```

**実行結果（修正後）:**
```
| id | option_name | price | is_discount_target | display_order | target_flag_status |
|----|-------------|-------|-------------------|---------------|-------------------|
| 1  | 調理器具セット | 6600  | 1                 | 1             | ✅ 正確           |
| 2  | 食器類      | 3900  | 1                 | 2             | ✅ 正確           |
| 3  | 洗剤類      | 3800  | 1                 | 3             | ✅ 正確           |
| 4  | タオル類    | 2900  | 1                 | 4             | ✅ 正確           |
| 5  | アメニティ類 | 3500  | 1                 | 5             | ✅ 正確           |
| 6  | 寝具カバーセット | 4530 | 1              | 6             | ✅ 正確           |
| 7  | 毛布        | 3950  | 1                 | 7             | ✅ 正確           |
| 8  | アイロン    | 6860  | 0                 | 8             | ✅ 正確           |
| 9  | 炊飯器（4合炊き） | 6600 | 0              | 9             | ✅ 正確           |
```

## 修正サマリー

### 変更内容
- **削除されたレコード:** 4件（ID: 10, 11, 12, 13）
- **保持されたレコード:** 9件（ID: 1-9）
- **is_discount_target修正:** 全レコードがIDベースルールに準拠

### 修正前後の比較
| 項目 | 修正前 | 修正後 | 改善 |
|------|--------|--------|------|
| 総レコード数 | 13件 | 9件 | ✅ 重複削除完了 |
| 重複オプション名 | 3件 | 0件 | ✅ 重複解消 |
| is_discount_target不正 | 4件 | 0件 | ✅ 全て正確 |
| display_order重複 | 3箇所 | 0箇所 | ✅ 重複解消 |

### 検証結果
- ✅ 重複削除: 完了
- ✅ IDベースルール適用: 完了
- ✅ データ整合性: 確認済み
- ✅ 割引計算テスト: 正常動作確認済み

**修正完了日時:** 2025年8月4日 12:40 UTC
**実行者:** Devin AI
**承認者:** yoshi@cocolomachi.co.jp
