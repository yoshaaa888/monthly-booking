# Production Deployment Notes

## ステージング→本番移行記録

### 実行日時
- **移行日**: 2025-08-04
- **担当**: Devin AI
- **セッション**: https://app.devin.ai/sessions/fda561ef6a2c41f98ec2bbb426175f6b

### マージ完了PR
- **PR #4**: 統合キャンペーン機能 (Merged)
- **PR #5**: typeカラム追加とコミコミ10万円キャンペーン基盤 (Open - 要マージ)

### データベース変更点

#### wp_monthly_campaigns テーブル拡張
```sql
-- 新規追加カラム
ALTER TABLE wp_monthly_campaigns ADD COLUMN type VARCHAR(20) DEFAULT NULL;
ALTER TABLE wp_monthly_campaigns ADD COLUMN max_stay_days INT(3) DEFAULT NULL;
```

#### 必須キャンペーンデータ挿入
```sql
-- 即入居割キャンペーン
INSERT INTO wp_monthly_campaigns (
    campaign_name, campaign_description, type, discount_type, discount_value, 
    min_stay_days, earlybird_days, max_discount_amount, max_discount_days, 
    tax_type, target_plan, start_date, end_date, is_active
) VALUES (
    '即入居割20%', '入居7日以内のご予約で賃料・共益費20%OFF', 'immediate', 
    'percentage', 20.00, 1, 0, 80000.00, 30, 'taxable', 'ALL', 
    '2025-01-01', '2099-12-31', 1
);

-- 早割キャンペーン
INSERT INTO wp_monthly_campaigns (
    campaign_name, campaign_description, type, discount_type, discount_value, 
    min_stay_days, earlybird_days, max_discount_amount, max_discount_days, 
    tax_type, target_plan, start_date, end_date, is_active
) VALUES (
    '早割10%', '入居30日以上前のご予約で賃料・共益費10%OFF', 'earlybird', 
    'percentage', 10.00, 7, 30, 50000.00, 30, 'taxable', 'S,M,L', 
    '2025-01-01', '2099-12-31', 1
);
```

### ステージング環境との差異

#### スキーマ差異
- **ステージング**: 旧スキーマ（`type`列なし）で説明文ベースマッチング
- **本番**: 新スキーマ（`type`列あり）でtype-basedマッチング

#### 動作確認項目
- [ ] 即入居割（≤7日）: 20%割引適用確認
- [ ] 早割（≥30日）: 10%割引適用確認
- [ ] ギャップ期間（8-29日）: キャンペーン適用なし確認
- [ ] 見積もり画面でのキャンペーンバッジ表示
- [ ] PDF出力でのキャンペーン情報反映

### 留意点

#### 1. スキーマ移行順序
1. プラグインファイル更新
2. データベーススキーマ更新（ALTER TABLE）
3. キャンペーンデータ挿入
4. 動作確認

#### 2. 後方互換性
- 旧説明文ベースマッチングも併用可能
- 既存予約データへの影響なし
- 段階的移行が可能

#### 3. 次フェーズ準備完了
- コミコミ10万円キャンペーン基盤実装済み
- `type="flatrate"`対応完了
- 7-10日滞在条件ロジック実装済み

### トラブルシューティング

#### よくある問題
1. **`type`列不存在エラー**: ALTER TABLE実行確認
2. **キャンペーン適用されない**: is_active=1, 日付範囲確認
3. **重複キャンペーン**: 最大1キャンペーンルール動作確認

#### ログ確認箇所
- WordPress: `wp-content/debug.log`
- キャンペーンマネージャー: `includes/campaign-manager.php`
- 見積もり計算: `includes/booking-logic.php`

### 検証済み機能
- ✅ 統合キャンペーンシステム
- ✅ データベース駆動型割引計算
- ✅ 最大1キャンペーンルール
- ✅ 境界値条件（7日、30日）
- ✅ WordPress環境統合

### 次のアクション
1. PR #5のマージ
2. 本番環境動作確認
3. コミコミ10万円キャンペーンフロント対応
4. 見積もり画面UI更新
5. PDF出力対応
