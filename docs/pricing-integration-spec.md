# 料金連携インテグレーション仕様（予約CRUD連携・単一ページ）

目的
- 「料金表・キャンペーン」と予約CRUDのI/Fぶれを防止するための、コピー＆ペースト可能な最小仕様を定義。

前提
- 時刻帯: Asia/Tokyo
- 日付型: 文字列 "YYYY-MM-DD"
- 期間の定義: 半開区間 [checkin, checkout)（checkout当日は未滞在）
- SQLite保存: TEXT（"YYYY-MM-DD" フォーマットであれば文字列比較がそのまま時系列比較に一致）

入力（予約オブジェクト）
```json
{
  "id": 123,                  // 新規時は省略可
  "room_id": 633,             // 必須
  "checkin": "YYYY-MM-DD",    // 必須（半開区間 [checkin, checkout)）
  "checkout": "YYYY-MM-DD",   // 必須
  "guests": 2,                // 必須（整数）
  "options": ["wifi","clean"],// 任意
  "campaign_codes": ["SUMMER25"], // 任意
  "base_daily_price": null    // 任意（未設定なら部屋定義から解決）
}
```
- 型:
  - id: number|null（create時は省略可）
  - room_id: number（必須）
  - checkin/checkout: string "YYYY-MM-DD"（必須）
  - guests: number（必須、0以上の整数）
  - options: string[]（任意）
  - campaign_codes: string[]（任意）
  - base_daily_price: number|null（任意。未指定時は部屋定義や料金表から解決）

出力（料金計算結果）
```json
{
  "subtotal": 180000,           // 税込JPY整数（小数なし）
  "discounts": [{"code":"SUMMER25","amount":-20000}],
  "total": 160000,              // = subtotal + Σdiscounts.amount
  "notes": "早割適用"
}
```
- 単位: 円（整数）
- 丸め: 端数が出る計算は「四捨五入→整数」
- discounts[].amount はマイナス値で表現
- total の定義: subtotal に discounts の合計（負値）を加えたもの

実行タイミング
- create / update の保存直前に計算し、その結果の total を予約に保存する
- delete は計算不要
- UI反映: 保存成功後に一覧・カレンダーをリロード（AJAX 200 を確認）

フックI/F（既存フックに準拠・例示）
- 保存後通知（PR #39 で追加済）
```php
do_action( 'monthly_booking_reservation_saved', $id, $data, $total );
```

料金計算の拡張ポイント（将来/例示）
```php
apply_filters( 'mbp/pricing/calc', $result, $reservation );
```
- $reservation は「入力（予約オブジェクト）」相当
- $result は「出力（料金計算結果）」の型（subtotal/discounts/total/notes）で返す契約
- 実装側はこのフィルタで差し替え可能（ダミー→本実装へ段階移行）

エラー扱い（重要）
- 計算に失敗しても予約保存は通す
- total は null で保存し、pricing_failed=1 をフラグ保存（または管理通知）
- 失敗時は error_log に予約IDと原因を1行出力（PIIは最小限）

競合判定 前提
- 半開区間 [checkin, checkout)
- 境界一致（前の予約のcheckout == 次の予約のcheckin）は非重複

SQLite用の推奨インデックス（既出）
- wp_monthly_reservations(room_id, checkin_date)
- wp_monthly_reservations(room_id, checkout_date)

トランザクション指針（最小）
- 予約CRUD＋料金計算の保存は同一リクエスト内で行う
- SQLite では可能なら BEGIN IMMEDIATE → 更新 → COMMIT（失敗時は ROLLBACK）
- WP標準の $wpdb->query() で明示実行（失敗時はフォールバック保存のみ）

Definition of Done（DoD）
- 本ドキュメントに「入力/出力/タイミング/フック/拡張ポイント/エラー/競合/SQLite注意/トランザクション」を全て収録
- JSONサンプル（割引なし／あり）を掲載

JSONサンプル（割引なし）
```json
{
  "subtotal": 180000,
  "discounts": [],
  "total": 180000,
  "notes": ""
}
```

JSONサンプル（割引あり）
```json
{
  "subtotal": 180000,
  "discounts": [{"code":"SUMMER25","amount":-20000}],
  "total": 160000,
  "notes": "早割適用"
}
```
