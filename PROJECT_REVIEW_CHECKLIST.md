# Project Review Checklist - やり残し事項リスト

## 優先度: 高 (High Priority)

- [ ] **布団の追加費用を管理画面から設定できるようにする**
  - 現在: ¥1,100/日がハードコード
  - 場所: `includes/booking-logic.php` line 566, 571
  - 提案: 管理画面で設定可能な料金マスタテーブル作成

- [ ] **清掃費・鍵手数料を管理画面から設定できるようにする**
  - 現在: 清掃費¥38,500、鍵手数料¥11,000がハードコード
  - 場所: `includes/booking-logic.php` line 556-557
  - 提案: 初期費用設定画面の追加

- [ ] **日割料金レートを管理画面から設定できるようにする**
  - 現在: 大人¥900/日、子ども¥450/日がハードコード
  - 場所: `includes/booking-logic.php` line 564, 569
  - 提案: 人数追加料金設定機能

## 優先度: 中 (Medium Priority)

- [ ] **共益費レートを管理画面から設定できるようにする**
  - 現在: SS=¥2,500、その他=¥2,000がハードコード
  - 場所: `includes/booking-logic.php` line 553
  - 提案: プラン別料金設定画面

- [ ] **オプション割引上限額を設定可能にする**
  - 現在: 最大¥2,000割引がハードコード
  - 場所: `includes/booking-logic.php` (オプション計算部分)
  - 提案: オプション割引設定画面

- [ ] **見積もり計算中のローディング表示改善**
  - 現在: 基本的なローディング表示は実装済み
  - 場所: `assets/estimate.js` line 449
  - 提案: より詳細な進捗表示やスピナーアニメーション

- [ ] **管理画面でのキャンペーン割り当て時のUX改善**
  - 現在: 基本機能は実装済み
  - 場所: `assets/admin.js`, `includes/admin-ui.php`
  - 提案: ドラッグ&ドロップ、一括操作機能

## 優先度: 低 (Low Priority)

- [ ] **デバッグコードの完全削除**
  - 残存箇所: テストファイル内のconsole.log
  - 場所: `test-environment/playwright/tests/` 配下
  - 提案: 本番環境では不要なログ出力を削除

- [ ] **エラーメッセージの日本語統一**
  - 現在: 一部英語メッセージが残存
  - 場所: `assets/admin.js` (修正済み)
  - 提案: 全エラーメッセージの日本語化確認

- [ ] **コード内コメントの充実**
  - 現在: 基本的なコメントは存在
  - 場所: 複雑な計算ロジック部分
  - 提案: 料金計算ロジックの詳細コメント追加

- [ ] **PHPUnitテストカバレッジの拡張**
  - 現在: キャンペーン自動適用ロジックのテストは完備
  - 場所: `tests/test-campaign-logic.php`
  - 提案: 料金計算全般のテストケース追加

## 設定可能化の実装提案

### 料金マスタテーブル設計案
```sql
CREATE TABLE wp_monthly_fee_settings (
    id int(11) NOT NULL AUTO_INCREMENT,
    fee_type varchar(50) NOT NULL,
    fee_name varchar(100) NOT NULL,
    amount decimal(10,2) NOT NULL,
    unit varchar(20) DEFAULT NULL,
    is_active tinyint(1) DEFAULT 1,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_fee_type (fee_type)
);
```

### 設定項目例
- `cleaning_fee`: 清掃費 (¥38,500)
- `key_fee`: 鍵手数料 (¥11,000)
- `bedding_fee_daily`: 布団代日額 (¥1,100)
- `adult_additional_rent`: 大人追加賃料日額 (¥900)
- `children_additional_rent`: 子ども追加賃料日額 (¥450)
- `adult_additional_utilities`: 大人追加共益費日額 (¥200)
- `children_additional_utilities`: 子ども追加共益費日額 (¥100)
- `ss_utilities_daily`: SSプラン共益費日額 (¥2,500)
- `standard_utilities_daily`: 標準共益費日額 (¥2,000)
- `option_discount_max`: オプション割引上限額 (¥2,000)

## UX改善提案

### 管理画面改善
- キャンペーン割り当て画面でのカレンダー表示
- 料金設定画面での即座プレビュー機能
- 一括データインポート/エクスポート機能

### フロントエンド改善
- 見積もり計算の段階的表示
- リアルタイム料金計算
- モバイル対応の改善

## ドキュメント改善提案

### 追加すべきドキュメント
- [ ] **API仕様書**: AJAX エンドポイントの詳細
- [ ] **データベース設計書**: テーブル関係図と制約
- [ ] **運用マニュアル**: 管理者向け操作手順
- [ ] **トラブルシューティングガイド**: よくある問題と解決方法

## 実装優先順位の根拠

**高優先度**: 現在ハードコードされている値は運用中に変更される可能性が高く、コード修正なしで設定変更できることが重要

**中優先度**: 機能的には動作するが、管理性やユーザビリティの向上に寄与

**低優先度**: 品質向上や保守性向上に寄与するが、機能的な影響は限定的
