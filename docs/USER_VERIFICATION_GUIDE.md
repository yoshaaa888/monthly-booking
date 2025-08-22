# ユーザー検証手順書（整合性ダッシュボード）

対象ブランチ: devin/1755817033-sql-and-checklists  
PR: #91

## 前提
- 管理者権限（manage_options）が必要です。

## 手順
1. WordPress 管理画面へログイン
2. メニュー: Monthly Room Booking → 「整合性ダッシュボード」をクリック（スラッグ: monthly-room-booking-consistency）
3. 画面上部のサマリーで重要度別件数（高/中/低）を確認
4. 下部の詳細テーブルで各行の「カテゴリ / 箇所 / 現在値 / 期待値 / 重要度 / 備考」を確認
5. CSV ダウンロード:
   - 「CSVダウンロード（全件）」
   - 「CSV（キャンペーン）/（料金参照）/（オプション）/（レート）」
   - ダウンロードは nonce により保護されています

## 期待結果
- 管理者でアクセス可。非管理者は「Sorry, you are not allowed to access this page.」となります。
- CSV は UTF-8、ヘッダー: category, location, current_value, expected_value, severity, note

## トラブルシュート
- 403/権限エラー: ロールと権限を確認
- 画面が空/エラー: PHP エラーログを確認
- CSV 文字化け: Excel では「UTF-8」指定で開くか、Google Sheets でインポート

## 自動修正の使い方

整合性ダッシュボード上部に「自動修正」セクションがあります。管理者のみ使用できます。全ての操作は nonce と権限で保護され、変更前に監査ログへスナップショットを保存します。

- キャンペーンテーブル統一
  - ボタンを押すとキャンペーン設定ページへ遷移します。コード側は参照テーブルを monthly_campaigns に統一済みです。

- 料金プラン補完
  - room_id を入力して実行します。対象部屋に SS/S/M/L のアクティブレートが存在しない場合、monthly_rates に不足分を作成します。
  - 生成値: base_price は部屋マスタ daily_rent、cleaning_fee=0、service_fee=0、valid_from=本日、valid_to=NULL、is_active=1

- 表示順序修正
  - monthly_options の display_order が重複している行を検出し、2件目以降を次に空いている連番へ繰り上げます。

### ロールバック

直前の実行はロールバック可能です。batch_id を入力するか、空のまま送信すると最後のバッチを利用します。

- 料金プラン ロールバック: 直前に自動作成されたレート行を削除します。
- 表示順序 ロールバック: 監査ログに保存した display_order の元の値へ戻します。

### 検証手順（例）

1) 整合性ダッシュボードのサマリーで高/中/低件数を確認  
2) room_id=633 を入力して「料金プラン補完」を実行 → 再表示して SS/S/M/L の欠損警告が解消していることを確認  
3) 「表示順序修正」を実行 → 再表示して display_order 重複が解消していることを確認  
4) 必要に応じてロールバックを実行し元に戻せることを確認
