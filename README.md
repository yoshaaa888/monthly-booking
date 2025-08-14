# Monthly Booking

## Data integrity and seeding (WP-CLI)
Use these commands with wp-env to backfill room_id and seed minimal data.

```
npx wp-env start
npx wp-env run cli -- wp plugin activate monthly-booking || true
npx wp-env run cli -- wp mb backfill_room_id
npx wp-env run cli -- wp mb seed
npx wp-env run cli -- wp mb migrate
```

Re-running seed should result in inserted=0 and skipped increasing.


# Monthly Booking WordPress Plugin

A comprehensive WordPress plugin for managing monthly property bookings with advanced campaign management, pricing calculations, and administrative interfaces.

## Features

### Core Booking System
- **Monthly booking calendar** with real-time availability display
- **Multi-tier pricing calculation** (SS/S/M/L plans based on stay duration)
- **Person-based fee calculations** with separate adult/children rates
- **Tax-separated billing** with proper consumption tax handling
- **Option bundle discounts** with tiered pricing (2 options: -¥500, 3+ options: +¥300 each)

### Campaign Management System
- **Room-based campaign assignments** with flexible date ranges
- **Automatic campaign application** based on booking conditions:
  - Early booking discounts (30+ days advance)
  - Immediate move-in discounts (within 7 days)
  - Flatrate campaigns (fixed pricing override)
- **Priority-based selection** (flatrate > highest percentage discount)
- **Campaign exclusivity** (one campaign per room per period)
- **Admin interface** for campaign assignment and management

### Administrative Features
- **WordPress-standard admin interface** with proper capability checks
- **Room management** with property associations
- **Campaign assignment UI** with modal dialogs and validation
- **Calendar view** with booking status indicators
- **AJAX-powered** real-time updates and validation

## File Structure
```
monthly-booking/
├── monthly-booking.php          # Main plugin file with activation hooks
├── includes/
│   ├── booking-logic.php        # Core pricing and calculation logic
│   ├── campaign-manager.php     # Campaign selection and application
│   ├── admin-ui.php            # WordPress admin interface
│   └── calendar-render.php     # Calendar display functionality
├── assets/
│   ├── estimate.js             # Frontend booking estimate logic
│   ├── admin.js               # Admin interface JavaScript
│   └── calendar.js            # Calendar interaction handling
├── tests/
│   └── test-campaign-logic.php # PHPUnit test suite
└── templates/                  # Frontend display templates
```

## Campaign System Architecture

### Campaign Types
1. **Percentage Discounts** - Apply percentage reduction to daily rent
2. **Fixed Amount Discounts** - Apply fixed yen amount reduction
3. **Flatrate Campaigns** - Override all pricing with fixed total amount

### Selection Logic
```php
// Priority order for campaign selection:
1. Flatrate campaigns (complete pricing override)
2. Highest percentage discount available
3. Highest fixed amount discount available
4. No campaign (standard pricing)
```

### Room Assignment Rules
- Each room can have multiple campaign assignments with different date ranges
- No overlapping periods allowed for the same room
- Campaigns must be active (`is_active = 1`) to be considered
- Date validation prevents conflicts during assignment

## Database Schema

### Core Tables
- `wp_monthly_rooms` - Room and property information
- `wp_monthly_campaigns` - Campaign master data
- `wp_monthly_room_campaigns` - Room-campaign assignments
- `wp_monthly_bookings` - Booking records
- `wp_monthly_customers` - Customer information
- `wp_monthly_options` - Available booking options

## Testing Infrastructure

### PHPUnit Test Suite
Run comprehensive campaign logic tests:
```bash
php run_campaign_tests.php
```

### Test Coverage
- Campaign selection algorithm validation
- Date boundary condition testing
- Priority-based selection verification
- Edge case handling (same-day bookings, long-term stays)
- Integration testing with booking logic

## Development Guidelines

### WordPress Standards
- Follows WordPress Coding Standards
- Uses proper sanitization and validation
- Implements capability-based access control
- Supports internationalization (i18n)

### Code Organization
- Separation of concerns between booking logic and UI
- AJAX endpoints with proper nonce verification
- Error handling with user-friendly messages
- Comprehensive logging for debugging

## Installation & Setup

1. Upload plugin files to `/wp-content/plugins/monthly-booking/`
2. Activate plugin through WordPress admin
3. Configure room and property data
4. Set up campaigns and assignments
5. Add booking forms to frontend pages using shortcodes

## Shortcodes
- `[monthly_booking_estimate]` - Booking estimate form
- `[monthly_booking_calendar]` - Availability calendar display

## Configuration

### Hardcoded Values (Recommended for Admin Configuration)
Current hardcoded pricing values that should be made configurable:
- Cleaning fee: ¥38,500
- Key fee: ¥11,000  
- Daily utilities: SS=¥2,500, others=¥2,000
- Person additional rates: adults ¥900/day, children ¥450/day
- Person utilities: adults ¥200/day, children ¥100/day

## Support & Maintenance

### Logging
- WordPress debug.log integration
- Campaign selection decision logging
- Booking calculation audit trail

### Performance
- Optimized database queries with proper indexing
- AJAX operations with loading states
- Efficient campaign selection algorithms

---

**Version**: 1.5.8+  
**WordPress Compatibility**: 5.0+  
**PHP Compatibility**: 7.4+  
**Testing**: Comprehensive PHPUnit suite included

# GitHub Codespaces での一発起動ガイド（非エンジニア向け）

このリポジトリは `.devcontainer` 構成を備えており、**GitHub Codespaces** を使うと、ローカルに何も入れなくても **WordPress + Playwright** の環境が自動で用意されます。  
はじめての方でも、下の順番どおりに進めれば “初回から一発” で起動できます。

---

## 1. Codespaces を作成する
1. GitHub でこのリポジトリを開きます。
2. 緑の **`<> Code`** ボタン → **`Codespaces`** タブを選びます。
3. **`Create codespace on main`** をクリックします。

> 💡 **毎回新規作成がおすすめ**  
> 既存の Codespace を再利用すると古いキャッシュが残ることがあります。初回検証やトラブル時は新規で作ると確実です。

---

## 2. 起動中に自動で行われること（待つだけでOK）
- Dev Container のビルド（Ubuntu ベース）
- Node.js 18 のセットアップ（devcontainer の Node Feature）
- PHP / WP-CLI（`@wordpress/env`）の準備
- Playwright（ブラウザ依存含む）のインストール
- `wp-env start` による WordPress ローカル環境の起動
- VS Code 拡張（Docker / ESLint / PHP IntelliSense / Prettier）の導入

> ⏳ 初回は数分かかることがあります（特に Playwright のブラウザ導入）。

---

## 3. WordPress を開く（ポート 8888）
起動が完了すると、VS Code 画面下部の **PORTS** パネルに **`8888`** が表示されます。行を右クリックして **Open in Browser** を選ぶと、WordPress サイトが開きます。

---

## 4. Playwright テストを動かす
メニューの **Terminal** → **New Terminal** を開き、次を実行します。

```bash
cd test-environment/playwright
npx playwright test --reporter=list
```

レポートを開く場合は:
```bash
npx playwright show-report
```

---

## 5. よくあるつまづきと対処

### ✅ ビルドが失敗した / 途中で止まった
- **Codespace を削除して、もう一度「新規作成」**してください（キャッシュ起因の不整合が解消されます）。

### ✅ WordPress（ポート 8888）が開かない
```bash
wp-env start
wp-env list
```
で起動状態を確認し、PORTS パネルから 8888 をブラウザで開きます。

### ✅ Playwright の導入でコケる / テストが走らない
```bash
npx playwright install --with-deps
```
を一度実行してから再度テストを実行します。

---

## 6. 補足：CI（Devcontainer Validate）について
GitHub Actions の CI では **Dev Container の「ビルドだけ」を検証**しています（`postCreate` の処理は Codespaces 側で実行）。  
これにより、CI は安定して速く、開発者の Codespaces 起動時には完全なセットアップ（`wp-env start` など）が自動で行われます。

---

## 7. 参考（内部構成）
- **`.devcontainer/devcontainer.json`** … Dev Container 設定（`dockerFile` は `.devcontainer/Dockerfile` を指す）
- **`.devcontainer/Dockerfile`** … ベースイメージと必要パッケージ
- **`test-environment/playwright/`** … E2E テスト用ワークスペース

> いずれも Codespaces での “初回から一発起動” を前提に配置済みです。

