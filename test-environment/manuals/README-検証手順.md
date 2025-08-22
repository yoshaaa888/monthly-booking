# 月次賃貸「Monthly Booking」検証手順（ローカル環境向け）

対象: Local WP（Local by Flywheel）または XAMPP 上の WordPress 6.x（日本語）

本手順は「インストール用テストデータ」パッケージ用の簡易版です。以下の順で実施してください。

## 1. 事前準備
- WordPress 6.x（日本語）をローカルに用意
- 管理者アカウントでログイン可能であること
- Local WP を使う場合は「Database → Open Adminer」からDB操作します（phpMyAdmin ではなく Adminer を推奨）

## 2. プラグインのインストール
1) WordPress 管理画面 → 「プラグイン」 → 「新規追加」 → 「プラグインのアップロード」
2) 「ファイルを選択」で monthly-booking.zip を選択して「今すぐインストール」
3) インストール完了後「有効化」をクリック

## 3. テストデータ（seed_data_compatible.sql）の投入
1) Local WP を使用する場合:
   - Local → 対象サイト → 「Database」 → 「Open Adminer」
   - 上部メニューの「SQL コマンド」をクリック
2) XAMPP 等で phpMyAdmin を使う場合:
   - 対象データベースを選択し「SQL」タブを開く
3) seed_data_compatible.sql をテキストエディタで開き、内容をすべてコピー
4) Adminer / phpMyAdmin の SQL 入力欄に貼り付けて「Execute（実行）」をクリック
5) エラーが出ないことを確認

補足:
- 本SQLは v4 で「必要テーブルの自動作成（CREATE TABLE IF NOT EXISTS）」を追加しました。wp_monthly_rooms が未作成でも自動で作成したうえで投入します。
- enum('日','月') と enum('day','month') のどちらの環境でも自動判定して投入します（information_schema 参照、準備済みステートメントで挿入）
- テーブル接頭辞（wp_）が異なる環境でも、現在のDB内の実テーブル名（%_monthly_*）を自動検出して投入します
- MySQL 9.4 対応: PREPARE は「1 文ずつ」のみ実行（マルチステートメント未使用）、TRUNCATE は各テーブル個別に PREPARE/EXECUTE/DEALLOCATE します
- 何度も流すと重複エラーになる場合があります。必要に応じてテーブルを空にしてから再投入してください
- CLI 例（docker-compose 環境）:
  cat seed_data_compatible.sql | docker-compose -f docker-compose.wordpress.yml exec -T db mysql -u USER -pPASS DBNAME

## 4. 動作確認用ページの作成
1) 管理画面 → 「固定ページ」 → 「新規追加」
2) 以下の2ページを作成し公開します
   - 見積ページ（スラッグ例: estimate）
     - 本文に以下のショートコードを貼り付け
       [monthly_booking_estimate]
   - カレンダーページ（スラッグ例: calendar）
     - 本文に以下のショートコードを貼り付け
       [monthly_booking_calendar]
3) 公開後、以下のURLで表示確認（サイトドメインはローカル環境に合わせて読み替え）
   - http://[site].local/estimate
   - http://[site].local/calendar

## 5. 確認観点（スモークテスト）
- カレンダー
  - 6か月分の表示が行われること
  - 在庫状況が配色（空き/予約/キャンペーン等）で区別されること
  - 直近/遠隔月に跨るデータが正しく反映されること
- 見積
  - 期間入力に応じて料金が再計算されること（税区分分離がある場合は内訳が妥当）
  - キャンペーン適用がある場合は割引が反映されること（適用は 1 つのみ）
- 予約整合性
  - 予約期間は半開区間 [checkin, checkout) で取り扱われ、重複しないこと
  - クリーニングバッファ（+5日 等）がある場合は利用不可が正しく表現されること

## 6. トラブルシュート
- ページが空白/ショートコード文字列のまま表示される:
  - プラグインが有効化されているか確認
  - ショートコードの表記揺れ（全角/半角スペース）を確認
- カレンダーが表示されない/崩れる:
  - テーマや他プラグインの CSS/JS 競合の可能性 → 一時的にデフォルトテーマ（Twenty Twenty-Five など）で確認
- データが反映されない:
  - seed_data.sql の投入エラーがないか、投入先DBが正しいかを Adminer/phpMyAdmin で確認
  - WP のタイムゾーン設定（一般 → サイトのタイムゾーン）が東京等に設定されているか確認

## 7. 参考
- 見積ショートコード: [monthly_booking_estimate]
- カレンダーショートコード: [monthly_booking_calendar]
- 仕様メモ:
  - 予約は半開区間 [checkin, checkout)
  - キャンペーンは同時に最大1つ適用
  - 料金はプラン種別（SS/S/M/L）や人数/オプションにより構成

以上でローカル検証が可能です。
