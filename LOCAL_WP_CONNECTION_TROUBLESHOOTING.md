# Local WP 環境接続トラブルシューティングガイド - 完全版

## 🔍 現在の状況分析
- **環境URL**: http://t-monthlycampaign.local/
- **管理画面URL**: http://t-monthlycampaign.local/wp-admin/
- **認証情報**: ユーザー名 `t-monthly-admin` / パスワード `t-monthly`
- **エラー**: `net::ERR_SOCKS_CONNECTION_FAILED`
- **影響**: ブラウザからの直接アクセスが不可能
- **目的**: Priority 1-4修正の動作確認とデモ準備

## 🛠️ 段階的接続確認手順

### Step 1: Local by Flywheel アプリケーション確認

#### 1.1 アプリケーション起動状態確認
```bash
# macOS/Linux の場合
ps aux | grep -i local

# Windows の場合 (PowerShell)
Get-Process | Where-Object {$_.ProcessName -like "*local*"}
```

#### 1.2 Local by Flywheel GUI 確認項目
1. **アプリケーション起動**
   - Local by Flywheel アプリが起動しているか
   - システムトレイまたはDockにアイコンが表示されているか

2. **サイト状態確認**
   - サイト一覧で "t-monthlycampaign" が表示されているか
   - サイトのステータスが "Running" (緑色) になっているか
   - "Stopped" の場合は "Start Site" ボタンをクリック

3. **環境設定確認**
   - "Site Details" → "Environment" タブを確認
   - PHP バージョン: 8.1以上
   - WordPress バージョン: 6.5以上
   - SSL証明書が有効になっているか

### Step 2: ネットワーク設定とDNS確認

#### 2.1 hosts ファイル確認
```bash
# macOS/Linux の場合
cat /etc/hosts | grep monthlycampaign

# Windows の場合
type C:\Windows\System32\drivers\etc\hosts | findstr monthlycampaign

# 期待される設定:
# 127.0.0.1 t-monthlycampaign.local
```

#### 2.2 DNS解決テスト
```bash
# DNS解決確認
nslookup t-monthlycampaign.local

# 期待される結果:
# Server: 127.0.0.1
# Address: 127.0.0.1

# 接続テスト
ping t-monthlycampaign.local

# 期待される結果: 127.0.0.1 への ping 応答
```

### Step 3: ポートとサービス確認

#### 3.1 使用ポート確認
```bash
# macOS/Linux の場合
netstat -tulpn | grep :80
netstat -tulpn | grep :443

# Windows の場合
netstat -an | findstr :80
netstat -an | findstr :443
```

#### 3.2 Local WP 固有ポート確認
- Local by Flywheel は通常 10000番台のポートを使用
- "Site Details" → "Environment" で実際のポート番号を確認
- 例: http://localhost:10004/ のような形式

## 🔄 代替アクセス方法（優先順位順）

### 方法1: Local by Flywheel の "Open Site" 機能 (推奨)
1. **Local by Flywheel アプリを開く**
2. **"t-monthlycampaign" サイトを選択**
3. **"Open Site" ボタンをクリック**
4. **自動的にブラウザが開き、正しいURLでアクセス**

### 方法2: 実際のポート番号での直接アクセス
1. **Local by Flywheel で実際のポート番号を確認**
   - "Site Details" → "Environment" → "Site Host"
2. **ブラウザで直接アクセス**
   ```
   http://localhost:[ポート番号]/
   例: http://localhost:10004/
   ```

### 方法3: IPアドレス直接アクセス
```
http://127.0.0.1:[ポート番号]/
http://127.0.0.1:[ポート番号]/wp-admin/
```

### 方法4: 管理画面直接アクセス (Local GUI経由)
1. **Local by Flywheel で "WP Admin" ボタンをクリック**
2. **自動ログインで管理画面にアクセス**

## 🚨 詳細トラブルシューティング

### エラー: net::ERR_SOCKS_CONNECTION_FAILED

#### 原因分析:
- プロキシ設定の競合
- VPN接続による干渉
- ファイアウォール設定
- ブラウザのネットワーク設定

#### 解決手順:
1. **ブラウザのプロキシ設定確認**
   ```
   Chrome: 設定 → 詳細設定 → システム → プロキシ設定を開く
   Firefox: 設定 → ネットワーク設定 → 設定
   ```
   - "プロキシを使用しない" または "システムのプロキシ設定を使用" を選択

2. **VPN接続の一時無効化**
   - VPN アプリケーションを終了
   - システムのVPN設定を無効化

3. **Local by Flywheel の再起動**
   - アプリケーションを完全終了
   - 再起動後、サイトを再開

4. **ブラウザキャッシュクリア**
   - Ctrl+Shift+Delete (Windows) または Cmd+Shift+Delete (Mac)
   - "すべての時間" を選択してクリア

### エラー: サイトが見つからない (404)

#### 解決手順:
1. **Local by Flywheel でサイト再起動**
   - サイトを選択 → "Stop Site" → "Start Site"

2. **データベース接続確認**
   - "Database" タブで接続情報確認
   - Adminer または phpMyAdmin でアクセステスト

3. **WordPress ファイル確認**
   - "Site Details" → "Go to site folder"
   - wp-config.php ファイルの存在確認

### エラー: SSL証明書エラー

#### 解決手順:
1. **HTTP (非SSL) でアクセス試行**
   ```
   http://t-monthlycampaign.local/ (https ではなく http)
   ```

2. **Local by Flywheel SSL設定確認**
   - "Site Details" → "SSL" タブ
   - "Trust" ボタンをクリックして証明書をインストール

3. **ブラウザでの証明書例外追加**
   - "詳細設定" → "安全でないサイトにアクセス"

## 📋 包括的環境確認チェックリスト

### Local by Flywheel アプリケーション:
- [ ] アプリケーションが起動している
- [ ] "t-monthlycampaign" サイトが一覧に表示される
- [ ] サイトステータスが "Running" (緑色)
- [ ] PHP 8.1以上、WordPress 6.5以上
- [ ] SSL証明書が設定されている

### ネットワーク設定:
- [ ] hosts ファイルに正しいエントリが存在
- [ ] DNS解決が正常に動作
- [ ] ポート80/443または指定ポートが利用可能
- [ ] ファイアウォールがアクセスを許可

### ブラウザ設定:
- [ ] プロキシ設定が無効または適切
- [ ] VPN接続が無効（必要に応じて）
- [ ] ブラウザキャッシュがクリア済み
- [ ] JavaScript が有効

### WordPress 環境:
- [ ] WordPress が正常にインストールされている
- [ ] Monthly Booking プラグインが有効化されている
- [ ] データベース接続が正常
- [ ] 管理者アカウント (t-monthly-admin) が存在

## 🎯 接続成功後の確認手順

### 接続が確立できた場合:

1. **WordPress管理画面アクセス**
   ```
   URL: http://t-monthlycampaign.local/wp-admin/
   ユーザー名: t-monthly-admin
   パスワード: t-monthly
   ```

2. **プラグイン有効化確認**
   - プラグイン → インストール済みプラグイン
   - "Monthly Room Booking" が有効化されているか確認

3. **Priority 1-4 修正の動作確認**
   - 管理メニューの構成確認 (Priority 4)
   - 料金設定ページの確認 (Priority 1)
   - キャンペーン設定の180日制限確認 (Priority 3)
   - カレンダーのJavaScript安定性確認 (Priority 2)

4. **テストデータ確認**
   - 物件マスタ管理で部屋データ確認
   - キャンペーン設定でサンプルキャンペーン確認

### 接続できない場合の代替手順:

1. **ドキュメントベース検証**
   - 提供された手動テスト手順書を使用
   - コード解析結果に基づく確認

2. **スクリーンショット付きレポート作成**
   - 接続試行の詳細記録
   - エラーメッセージのキャプチャ
   - 代替手順の提案

3. **リモート検証依頼**
   - ユーザーによる直接確認
   - 画面共有セッションの実施

## 🔧 高度なトラブルシューティング

### Local by Flywheel ログ確認:
```bash
# macOS の場合
~/Library/Logs/local-by-flywheel/main.log

# Windows の場合
%USERPROFILE%\AppData\Roaming\Local by Flywheel\logs\main.log
```

### WordPress デバッグモード有効化:
```php
// wp-config.php に追加
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### データベース直接アクセス:
1. **Local by Flywheel の Database タブを使用**
2. **Adminer または phpMyAdmin でアクセス**
3. **wp_options テーブルでプラグイン設定確認**

## 📞 エスカレーション手順

### 技術サポートへの連絡時に準備する情報:

1. **環境情報**
   - OS バージョン (Windows 10/11, macOS Big Sur/Monterey, etc.)
   - Local by Flywheel バージョン
   - ブラウザとバージョン

2. **エラー詳細**
   - 正確なエラーメッセージ
   - エラー発生タイミング
   - 試行した解決手順

3. **ログファイル**
   - Local by Flywheel ログ
   - WordPress debug.log
   - ブラウザコンソールエラー

4. **スクリーンショット**
   - エラー画面
   - Local by Flywheel 設定画面
   - ネットワーク設定

### 緊急時の代替案:

1. **別環境での検証**
   - XAMPP または MAMP での環境構築
   - Docker を使用したWordPress環境

2. **クラウド環境での一時検証**
   - WordPress.com での一時サイト作成
   - 無料ホスティングサービスの利用

3. **オフライン検証**
   - コードレビューによる確認
   - 静的解析ツールの使用

このトラブルシューティングガイドにより、Local WP環境への接続問題を体系的に解決し、Priority 1-4修正の検証を確実に実行できます。
