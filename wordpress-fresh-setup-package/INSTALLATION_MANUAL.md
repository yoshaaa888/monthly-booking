# Monthly Booking WordPress プラグイン - 完全インストールマニュアル

## 📋 概要

WordPress を一から立ち上げ直して、Monthly Booking プラグインを完全にセットアップするための詳細手順書です。

## 🎯 前提条件

### 必要な環境
- Windows 11
- Local WP (Local by Flywheel) インストール済み
- Chrome ブラウザ最新版
- テキストエディタ（メモ帳、VSCode等）

### 必要なファイル
- `monthly-booking-v2.2-final.zip` - プラグインファイル
- `seed_data.sql` - サンプルデータファイル

## 🚀 Step 1: Local WP サイト作成

### 1.1 Local WP アプリ起動
1. **Local WP アプリを起動**
2. **「+ Create a new site」をクリック**

### 1.2 サイト設定
1. **サイト名**: `t-monthlycampaign`
2. **ローカルドメイン**: `t-monthlycampaign.local`
3. **「Continue」をクリック**

### 1.3 環境設定
1. **Environment**: 「Preferred」を選択
2. **PHP Version**: 7.4以上を選択
3. **Web Server**: Nginx
4. **Database**: MySQL 5.7以上
5. **「Continue」をクリック**

### 1.4 WordPress設定
1. **WordPress Username**: `admin`
2. **WordPress Password**: `password`（任意）
3. **WordPress Email**: 任意のメールアドレス
4. **「Add Site」をクリック**

### 1.5 サイト起動確認
1. **サイト一覧で「Start site」をクリック**
2. **緑色の「Running」表示を確認**
3. **「WP Admin」をクリックしてWordPress管理画面にアクセス**

## 📦 Step 2: プラグインインストール

### 2.1 WordPress管理画面アクセス
1. **URL**: `http://t-monthlycampaign.local/wp-admin/`
2. **ユーザー名**: `admin`
3. **パスワード**: 設定したパスワード
4. **「ログイン」をクリック**

### 2.2 プラグインアップロード
1. **左側メニュー「プラグイン」をクリック**
2. **「新規追加」をクリック**
3. **「プラグインのアップロード」をクリック**
4. **「ファイルを選択」で `monthly-booking-v2.2-final.zip` を選択**
5. **「今すぐインストール」をクリック**

### 2.3 プラグイン有効化
1. **「プラグインを有効化」をクリック**
2. **成功メッセージを確認**
3. **左側メニューに「Monthly Room Booking」が表示されることを確認**

## 🗄️ Step 3: データベースセットアップ

### 3.1 Adminer アクセス
1. **Local WP アプリに戻る**
2. **「Database」タブをクリック**
3. **「Open Adminer」をクリック**
4. **ブラウザで Adminer が開くことを確認**

### 3.2 サンプルデータ投入
1. **Adminer で「SQL command」をクリック**
2. **`seed_data.sql` ファイルをテキストエディタで開く**
3. **SQLの内容をすべてコピー**
4. **Adminer のテキストエリアに貼り付け**
5. **「Execute」をクリック**

### 3.3 データ投入確認
実行後、以下のような結果が表示されることを確認：

```
Rooms inserted: 5
Options inserted: 9  
Campaigns inserted: 2
Customers inserted: 3
Sample bookings inserted: 2
```

## 📄 Step 4: WordPress ページ作成

### 4.1 見積もりページ作成

#### 基本設定
1. **WordPress管理画面で「固定ページ」→「新規追加」**
2. **タイトル**: `月額見積り`
3. **パーマリンク**: `monthly-estimate`

#### ページ内容
```html
<h2>🏠 月額マンスリー見積り</h2>
<p>ご希望の条件を入力して、料金を確認してください。</p>

[monthly_booking_estimate]

<div style="margin-top: 30px; padding: 20px; background-color: #f9f9f9; border-radius: 5px;">
<h3>📋 ご利用の流れ</h3>
<ol>
<li><strong>条件入力</strong> - 入居日・退去日・人数を選択</li>
<li><strong>見積り確認</strong> - 詳細な料金内訳を確認</li>
<li><strong>オプション選択</strong> - 必要なオプションを追加</li>
<li><strong>予約申し込み</strong> - 内容確認後にお申し込み</li>
</ol>
</div>

<div style="margin-top: 20px; padding: 15px; background-color: #e8f4fd; border-left: 4px solid #2196F3;">
<h4>💡 料金について</h4>
<ul>
<li><strong>非課税項目</strong>: 日額賃料・共益費</li>
<li><strong>課税項目</strong>: 清掃費・布団代・鍵交換代・オプション類</li>
<li><strong>割引制度</strong>: オプション2個以上で割引適用</li>
<li><strong>キャンペーン</strong>: 早割・即入居割を自動適用</li>
</ul>
</div>
```

#### 公開設定
1. **「公開」をクリック**
2. **「ページを表示」で動作確認**

### 4.2 カレンダーページ作成

#### 基本設定
1. **「固定ページ」→「新規追加」**
2. **タイトル**: `予約カレンダー`
3. **パーマリンク**: `monthly-calendar`

#### ページ内容
```html
<h2>📅 予約カレンダー・空室状況</h2>
<p>各物件の予約状況をカレンダーで確認できます。</p>

[monthly_booking_calendar]

<div style="margin-top: 30px; padding: 20px; background-color: #f0f8f0; border-radius: 5px;">
<h3>📊 カレンダーの見方</h3>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
<div style="display: flex; align-items: center; gap: 5px;">
<span style="width: 20px; height: 20px; background-color: #4CAF50; border-radius: 3px; display: inline-block;"></span>
<span>〇 空室・予約可能</span>
</div>
<div style="display: flex; align-items: center; gap: 5px;">
<span style="width: 20px; height: 20px; background-color: #f44336; border-radius: 3px; display: inline-block;"></span>
<span>× 予約済み・利用不可</span>
</div>
<div style="display: flex; align-items: center; gap: 5px;">
<span style="width: 20px; height: 20px; background-color: #FF9800; border-radius: 3px; display: inline-block;"></span>
<span>△ キャンペーン対象日</span>
</div>
</div>
</div>

<div style="margin-top: 20px; padding: 15px; background-color: #fff3cd; border-left: 4px solid #ffc107;">
<h4>⚠️ 注意事項</h4>
<ul>
<li>カレンダーは180日先まで表示されます</li>
<li>予約には清掃期間（5日間）が含まれます</li>
<li>キャンペーン適用条件は自動判定されます</li>
</ul>
</div>
```

#### 公開設定
1. **「公開」をクリック**
2. **「ページを表示」で動作確認**

### 4.3 メニューへの追加
1. **「外観」→「メニュー」**
2. **「新しいメニューを作成」**
3. **メニュー名**: `メインメニュー`
4. **作成したページを「メニューに追加」**
5. **「メニューを保存」**
6. **「メニューの位置」で「Primary Menu」に設定**

## ✅ Step 5: 動作確認

### 5.1 基本動作確認
1. **見積もりページアクセス**: `http://t-monthlycampaign.local/monthly-estimate/`
2. **フォームが表示されることを確認**
3. **部屋選択ドロップダウンに5つの物件が表示されることを確認**

### 5.2 見積計算テスト

#### テストデータ入力
- **物件**: 立川マンスリー101号室
- **チェックイン**: 今日から35日後（早割適用）
- **チェックアウト**: チェックインから60日後（Sプラン）
- **大人**: 2名
- **子供**: 0名
- **オプション**: 調理器具セット、食器類、洗剤類（3つ選択）

#### 期待される結果
- **プラン**: S（30-89日）
- **早割バッジ**: 表示される
- **10%割引**: 適用される
- **オプション割引**: -¥500（3つ選択）
- **最終金額**: 正確に計算される

### 5.3 管理画面確認
1. **WordPress管理画面で「Monthly Room Booking」をクリック**
2. **以下のメニューが表示されることを確認**:
   - 物件マスタ管理
   - 予約カレンダー
   - キャンペーン設定
   - 料金設定

### 5.4 データベース確認
1. **Adminer で以下のテーブルを確認**:
   - `wp_monthly_rooms` - 5件の物件データ
   - `wp_monthly_options` - 9件のオプションデータ
   - `wp_monthly_campaigns` - 2件のキャンペーンデータ

## 🚨 トラブルシューティング

### プラグイン有効化エラー
**症状**: プラグインアップロード時にエラー
**解決方法**:
- WordPressバージョン確認（5.0以上必要）
- PHPバージョン確認（7.4以上必要）
- ファイルサイズ制限確認

### ショートコードが表示されない
**症状**: `[monthly_booking_estimate]` がそのまま表示
**解決方法**:
- プラグインが有効化されているか確認
- ページを再読み込み
- ブラウザキャッシュをクリア

### データベースエラー
**症状**: SQLエラーが発生
**解決方法**:
- テーブルプレフィックス確認（`wp_`）
- データベース接続確認
- プラグイン再有効化

### 見積計算エラー
**症状**: 見積ボタンを押してもエラー
**解決方法**:
- ブラウザコンソールでJavaScriptエラー確認
- WordPressデバッグモード有効化
- 他のプラグインとの競合確認

## 📊 完了チェックリスト

### 環境セットアップ
- [ ] Local WP サイト作成完了
- [ ] WordPress管理画面アクセス可能
- [ ] プラグイン正常に有効化

### データベース
- [ ] Adminer アクセス可能
- [ ] サンプルデータ投入完了
- [ ] 5つのテーブルにデータ確認

### ページ作成
- [ ] 見積もりページ作成・公開
- [ ] カレンダーページ作成・公開
- [ ] メニューに追加完了

### 動作確認
- [ ] 見積もりフォーム表示
- [ ] 部屋選択ドロップダウン動作
- [ ] 見積計算実行成功
- [ ] キャンペーン自動適用確認
- [ ] 管理画面メニュー表示

## 🎉 セットアップ完了

すべてのチェックリストが完了したら、Monthly Booking プラグインのセットアップは完了です。

**テスト用URL**:
- 見積もり: `http://t-monthlycampaign.local/monthly-estimate/`
- カレンダー: `http://t-monthlycampaign.local/monthly-calendar/`
- 管理画面: `http://t-monthlycampaign.local/wp-admin/`

このマニュアルに従って設定することで、Monthly Booking プラグインの全機能をご利用いただけます。
