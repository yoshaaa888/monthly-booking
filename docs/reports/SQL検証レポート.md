# SQL検証レポート

対象ファイル: docs/sql/料金設定確認用SQLクエリ集.sql

## 1. 安全性
- 全クエリは SELECT/SHOW のみ。DML/DDL（INSERT/UPDATE/DELETE/DROP/ALTER）は含まれません。
- テーブル接頭辞は {{PREFIX}} プレースホルダまたは MySQL ユーザー変数方式で対応可能。

## 2. 主なセクションと意図
- 料金設定確認（rooms / rates 結合）
- オプション確認（割引対象除外、並び順、負値検出）
- 整合性チェック（レート期間重複、必須レート欠損、オプションの非整合、キャンペーン重複）

## 3. 実行計画（EXPLAIN）推奨
- 実データがないため実行は行っていません。大規模データでは以下のインデックスを推奨:
  - monthly_rates: (room_id, rate_type, valid_from, valid_to), (is_active)
  - monthly_options: (display_order), (is_active)
  - monthly_campaigns: (is_active, start_date, end_date)
- クエリ例で ORDER/LIMIT を付与しており、確認作業時の負荷を抑制。

## 4. プレフィックス検証
- 実行前に {{PREFIX}} を wp_ 等に一括置換、または付録の PREPARE/EXECUTE 雛形を利用。

## 5. 追加の注意
- applicable_rooms の CSV 検索は FIND_IN_SET を使用（スモールデータ前提）。将来は正規化（中間テーブル）を推奨。
