# デモ準備ガイド - Monthly Booking Plugin

## 🎯 デモ目的
営業・経営層向けに Monthly Booking Plugin の主要機能を短時間で効果的にデモンストレーションする

## ⏱️ デモフロー（15分想定）

### Phase 1: 概要説明（2分）
**目的**: プラグインの価値提案を明確に伝える

#### 説明内容
```
「Monthly Booking Plugin は、月単位レンタル物件の予約管理を
WordPress で簡単に実現するプラグインです。

主な特徴:
✅ 6ヶ月先までの予約カレンダー表示
✅ キャンペーン割引の自動適用
✅ 見積もりPDF自動生成
✅ 清掃期間の自動管理
✅ レスポンシブ対応（PC/タブレット/スマホ）
```

### Phase 2: 管理画面デモ（8分）

#### 2-1: 物件マスタ管理（2分）
**URL**: `/wp-admin/admin.php?page=monthly-booking-properties`

**デモ手順**:
1. 物件一覧の表示確認
2. 新規物件の追加デモ
   - 物件名: "デモ物件A"
   - 日額賃料: 3000円
   - 最小滞在日数: 7日
3. 保存後の一覧更新確認

**強調ポイント**:
- 直感的な入力フォーム
- リアルタイムバリデーション
- 料金設定の柔軟性

#### 2-2: キャンペーン設定（3分）
**URL**: `/wp-admin/admin.php?page=monthly-booking-campaigns`

**デモ手順**:
1. 既存キャンペーン一覧の確認
2. 新規キャンペーン作成
   - キャンペーン名: "早期予約割引"
   - 割引率: 10%
   - 最小滞在日数: 14日
   - 有効期間: 30日間
3. 180日制限の説明（Priority 3修正）

**強調ポイント**:
- 柔軟な割引設定
- 期間・条件の細かい制御
- UI改善による操作性向上

#### 2-3: 予約カレンダー（3分）
**URL**: `/wp-admin/admin.php?page=monthly-booking-calendar`

**デモ手順**:
1. 6ヶ月カレンダーの表示
2. 部屋選択による表示切り替え
3. 予約状況の確認
4. 清掃期間の自動表示確認

**強調ポイント**:
- 視覚的に分かりやすいカレンダー
- 複数物件の一元管理
- 清掃期間の自動計算

### Phase 3: フロントエンド デモ（4分）

#### 3-1: 予約カレンダーページ（2分）
**URL**: `/monthly-calendar/`

**デモ手順**:
1. ユーザー視点でのカレンダー表示
2. 利用可能日の確認
3. キャンペーン期間の表示確認
4. レスポンシブ表示の確認（スマホ表示）

#### 3-2: 見積もりページ（2分）
**URL**: `/monthly-estimate/`

**デモ手順**:
1. 見積もりフォームの入力
   - チェックイン: 来月1日
   - チェックアウト: 来月15日
   - 部屋選択: デモ物件A
2. キャンペーン割引の自動適用確認
3. 見積もりPDF生成・ダウンロード

### Phase 4: 質疑応答（1分）

## 🗂️ デモ用テストデータ

### 物件データ
```sql
INSERT INTO wp_monthly_rooms (room_id, display_name, room_name, property_name, daily_rent, is_active) VALUES
(201, 'デモ物件A', 'Room A-101', 'Demo Building A', 3000, 1),
(202, 'デモ物件B', 'Room B-201', 'Demo Building B', 4500, 1),
(203, 'デモ物件C', 'Room C-301', 'Demo Building C', 2800, 1);
```

### キャンペーンデータ
```sql
INSERT INTO wp_monthly_campaigns (campaign_name, campaign_description, type, discount_type, discount_value, min_stay_days, start_date, end_date, is_active) VALUES
('早期予約割引', '30日前予約で10%OFF', 'advance', 'percentage', 10.00, 7, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 1),
('長期滞在割引', '30日以上で15%OFF', 'duration', 'percentage', 15.00, 30, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 90 DAY), 1),
('新規オープン記念', '新規物件20%OFF', 'immediate', 'percentage', 20.00, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1);
```

### 予約データ（デモ用）
```sql
INSERT INTO wp_monthly_bookings (room_id, start_date, end_date, status) VALUES
(201, DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 12 DAY), 'confirmed'),
(202, DATE_ADD(CURDATE(), INTERVAL 20 DAY), DATE_ADD(CURDATE(), INTERVAL 35 DAY), 'confirmed'),
(203, DATE_ADD(CURDATE(), INTERVAL 45 DAY), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 'pending');
```

## 🎬 デモ実行チェックリスト

### 事前準備
- [ ] WordPress 環境が正常に動作している
- [ ] Monthly Booking Plugin が有効化されている
- [ ] テストデータが投入されている
- [ ] 各ページが正常に表示される
- [ ] PDF生成機能が動作する

### デモ中確認事項
- [ ] 画面共有が正常に動作している
- [ ] 音声が明瞭に聞こえている
- [ ] 各機能がスムーズに動作している
- [ ] エラーが発生していない
- [ ] 時間配分が適切である

### デモ後フォロー
- [ ] 質問への回答準備
- [ ] 追加資料の提供
- [ ] 次回打ち合わせの調整
- [ ] フィードバックの収集

## 🚨 トラブルシューティング

### よくある問題と対処法

#### 問題1: カレンダーが表示されない
**原因**: JavaScript エラーまたはデータベース接続問題
**対処法**: 
1. ブラウザコンソールでエラー確認
2. プラグイン再有効化
3. キャッシュクリア

#### 問題2: PDF生成エラー
**原因**: ライブラリ不足またはパーミッション問題
**対処法**:
1. PHP拡張モジュール確認
2. 一時ディレクトリのパーミッション確認
3. メモリ制限の確認

#### 問題3: キャンペーン割引が適用されない
**原因**: 日付設定またはキャンペーン条件の問題
**対処法**:
1. キャンペーン有効期間の確認
2. 最小滞在日数の確認
3. キャンペーンタイプの確認

## 📋 デモ評価シート

### 機能評価
```
物件管理機能: □ 優秀 □ 良好 □ 改善要
キャンペーン機能: □ 優秀 □ 良好 □ 改善要
カレンダー表示: □ 優秀 □ 良好 □ 改善要
見積もり機能: □ 優秀 □ 良好 □ 改善要
操作性: □ 優秀 □ 良好 □ 改善要
```

### 総合評価
```
デモ満足度: □ 非常に満足 □ 満足 □ 普通 □ 不満足
導入検討度: □ 積極的 □ 前向き □ 検討中 □ 消極的
```

### フィードバック記録
```
良かった点:
[                                    ]

改善要望:
[                                    ]

追加質問:
[                                    ]
```

## 🎯 成功指標

### 定量指標
- デモ完了率: 100%
- 時間内完了: 15分以内
- エラー発生率: 0%
- 機能動作率: 100%

### 定性指標
- 参加者の理解度: 高
- 操作性の評価: 良好以上
- 導入意欲: 前向き以上
- 追加質問数: 適度

## 📞 サポート連絡先

### 技術的問題
- 開発チーム: [連絡先]
- システム管理者: [連絡先]

### 営業・商談関連
- 営業担当: [連絡先]
- プロジェクトマネージャー: [連絡先]
