# Monthly Booking Plugin - Local Test Environment

## 📦 パッケージ内容

このパッケージには、Monthly Booking WordPress プラグイン（v1.5.7）のローカルテスト環境一式が含まれています。

### 📁 ディレクトリ構成

```
test-environment/
├── monthly-booking.zip          # プラグインファイル（WordPress用）
├── database/
│   └── seed_data.sql           # テストデータ（Adminer用）
├── manuals/
│   ├── test_pages.md           # WordPressページ作成マニュアル
│   └── test_manual.md          # 操作手順書
├── playwright/
│   ├── package.json            # Node.js依存関係
│   ├── playwright.config.js    # Playwright設定
│   └── tests/
│       ├── journey_test_normal.spec.js      # 通常予約テスト
│       ├── journey_test_campaign.spec.js    # キャンペーンテスト
│       └── db_verification.spec.js          # DB整合性テスト
└── README.md                   # このファイル
```

## 🚀 クイックスタート

### 1. 前提条件の確認
- ✅ Windows 11
- ✅ Local WP インストール済み
- ✅ `t-monthlycampaign.local` サイト作成済み・起動中
- ✅ Node.js 16+ インストール済み
- ✅ Chrome ブラウザ最新版

### 2. セットアップ手順（5分）

#### Step 1: プラグインインストール
1. `monthly-booking.zip` をWordPress管理画面からアップロード・有効化

#### Step 2: テストデータ投入
1. Local WP → Database → Open Adminer
2. `database/seed_data.sql` の内容をコピー&ペースト実行

#### Step 3: WordPressページ作成
1. `manuals/test_pages.md` の手順に従って3つのページを作成

#### Step 4: 動作確認
1. `http://t-monthlycampaign.local/monthly-estimate/` にアクセス
2. 見積フォームが表示されることを確認

### 3. テスト実行

#### 手動テスト
`manuals/test_manual.md` の手順に従って、ブラウザで以下をテスト：
- 見積計算（プラン自動判定）
- キャンペーン自動適用（早割・即入居割）
- オプション選択・割引計算
- 予約申し込み・データベース保存

#### 自動テスト（Playwright）
```bash
cd playwright
npm install
npx playwright install
npm run test:all
```

## 🎯 テスト対象機能

### ✅ 見積機能
- **プラン自動判定**: SS(7-29日)/S(30-89日)/M(90-179日)/L(180日+)
- **料金計算**: 基本賃料・共益費・初期費用・人数加算・オプション
- **キャンペーン自動適用**: 早割(30日前・10%OFF)・即入居割(7日以内・20%OFF)
- **オプション割引**: 2つで¥500、3つ以上で+¥300ずつ（最大¥2,000）

### ✅ 予約機能
- **顧客データ保存**: `wp_monthly_customers` テーブル
- **予約データ保存**: `wp_monthly_bookings` テーブル
- **オプション関連**: `wp_monthly_booking_options` テーブル
- **外部システム連携**: 経理システムへのPOST送信（モック）

### ✅ 管理機能
- **管理画面**: WordPress管理画面での予約・顧客・物件管理
- **カレンダー表示**: 予約状況の視覚的確認
- **データ整合性**: 関連テーブル間の整合性確認

## 📊 テストデータ

### 物件データ（5件）
1. 立川マンスリー101号室（¥2,400/日）
2. 新宿レジデンス205号室（¥3,200/日）
3. 渋谷アパートメント302号室（¥2,800/日）
4. 池袋ハイツ403号室（¥2,600/日）
5. 品川タワー1205号室（¥3,800/日）

### オプション（9種類）
1-7: 割引対象（調理器具、食器、洗剤、タオル、アメニティ、寝具、毛布）
8-9: 割引対象外（アイロン、炊飯器）

### キャンペーン（2種類）
- **早割**: 30日以上前予約で10%OFF
- **即入居割**: 7日以内予約で20%OFF

## 🔍 トラブルシューティング

### よくある問題

#### プラグインが有効化できない
- WordPressバージョン確認（5.0+必要）
- PHPバージョン確認（7.4+必要）

#### ショートコードが表示されない
- プラグイン有効化確認
- ページ再読み込み

#### データベースエラー
- テーブルプレフィックス確認（`wp_`）
- プラグイン再有効化

#### Playwrightテストエラー
```bash
npx playwright install --force
```

## 📞 サポート

### ログ確認
- **WordPress**: `wp-content/debug.log`
- **Local WP**: Localアプリの「Logs」タブ
- **Playwright**: `test-results/` フォルダ

### デバッグ情報
```bash
# WordPress接続確認
curl http://t-monthlycampaign.local

# データベース確認
mysql -h localhost -u root -p local
```

## 🎪 デモシナリオ

### シナリオ1: 早割キャンペーン
1. チェックイン: 35日後
2. 滞在期間: 60日（Sプラン）
3. 人数: 大人2名
4. オプション: 3つ選択（-¥500割引）
5. 結果: 早割10%OFF + オプション割引

### シナリオ2: 即入居割
1. チェックイン: 3日後
2. 滞在期間: 10日（SSプラン）
3. 人数: 大人1名・子供1名
4. オプション: 2つ選択（-¥500割引）
5. 結果: 即入居割20%OFF + オプション割引

### シナリオ3: 通常料金
1. チェックイン: 15日後
2. 滞在期間: 40日（Sプラン）
3. 人数: 大人1名
4. オプション: 1つ選択（割引なし）
5. 結果: キャンペーン適用なし

---

**🎯 目的**: 営業・経営層への機能説明・デモンストレーション用
**⏱️ 所要時間**: セットアップ5分、デモ10分
**👥 対象**: ノンプログラマ（ブラウザ操作のみ）

このテスト環境により、Monthly Bookingプラグインの全機能を体験・確認できます。
