# AUDIT_LOG_USAGE

本ドキュメントは、軽量監査ログテーブル monthly_audit_log の仕様と運用方法をまとめたものです。

## テーブル仕様

- テーブル名: wp_monthly_audit_log
- カラム:
  - id bigint(20) AUTO_INCREMENT PRIMARY KEY
  - table_name varchar(64) NOT NULL
  - record_id bigint(20) NOT NULL
  - action varchar(32) NOT NULL
  - before_state json
  - after_state json
  - user_id bigint(20)
  - timestamp datetime DEFAULT CURRENT_TIMESTAMP
- インデックス:
  - KEY idx_table_record (table_name, record_id)
  - KEY idx_timestamp (timestamp)

プラグイン側では {$wpdb->prefix} を付与して作成・参照します。

## 書き込みタイミング

- fix_missing_rate_plans(room_id):
  - before_state: 対象部屋の既存レート行（SS/S/M/L、全件）
  - after_state: 直近の挿入結果（inserted_ids の配列など）
  - action: "fix_missing_rate_plans"
  - table_name: "{$prefix}monthly_rates"
  - record_id: room_id

- fix_duplicate_display_orders():
  - before_state: 影響を受ける id と元の display_order のリスト
  - after_state: 省略または変更後の取得結果
  - action: "fix_duplicate_display_orders"
  - table_name: "{$prefix}monthly_options"
  - record_id: 0（全体調整のため）

## 代表的なクエリ例

- 指定 room_id の料金プラン補完履歴
  SELECT id, action, before_state, after_state, user_id, timestamp
  FROM wp_monthly_audit_log
  WHERE table_name = 'wp_monthly_rates' AND record_id = 633
  ORDER BY id DESC;

- オプション表示順序修正の直近履歴
  SELECT id, before_state, timestamp
  FROM wp_monthly_audit_log
  WHERE action = 'fix_duplicate_display_orders'
  ORDER BY id DESC
  LIMIT 5;

- 期間で絞り込む
  SELECT *
  FROM wp_monthly_audit_log
  WHERE timestamp >= '2025-08-01 00:00:00' AND timestamp < '2025-09-01 00:00:00'
  ORDER BY timestamp DESC;

## ロールバックとの関係

- 既存の mb_audit_log は batch_id ベースのロールバックに使用します（rates / options）。
- monthly_audit_log は軽量な before/after 記録で、差分の確認・監査に利用します。
- 双方を併用することで、安全な変更履歴と復旧手段を提供します。

## 注意点

- JSON は wp_json_encode(JSON_UNESCAPED_UNICODE) で保存されます。
- テーブル作成は ensure_monthly_audit_table() により初回呼び出し時に自動実行されます。
- 監査対象の INSERT/UPDATE はすべて $wpdb の prepared statement / formats 指定で実行されます。
