# A11y-Nightly Workflow Failure Analysis Report

## 概要 (Summary)
a11y-nightlyワークフローが継続的に404エラーで失敗している問題の根本原因分析と恒久対策の提案。

## 根本原因 (Root Cause)
### 直接原因 (Direct Cause)
- `/monthly-calendar/` ページへのアクセスが404エラーを返す
- ヘルスチェックが60秒でタイムアウトし、ワークフロー全体が失敗

### 根本原因 (Root Cause)
1. **ページ未作成**: `monthly-calendar` 固定ページがWordPress環境で作成されていない
2. **パーマリンク未設定**: デフォルトのプレーン構造（`?p=123`）のため、スラッグURL（`/monthly-calendar/`）が機能しない
3. **プラグイン未有効化**: `monthly-booking`プラグインが自動有効化されていない
4. **機能フラグ未設定**: `MB_FEATURE_RESERVATIONS_MVP`等の機能フラグがテスト環境で無効

## 再現手順 (Reproduction Steps)
1. 新しいwp-env環境を起動: `npx wp-env start`
2. プラグイン未有効化の状態で `/monthly-calendar/` にアクセス
3. 404エラーが発生し、ヘルスチェックがタイムアウト

## 影響範囲 (Impact)
- **CI/CD**: a11y-nightlyワークフローが完全に機能停止
- **品質保証**: アクセシビリティテストが実行されず、品質低下リスク
- **開発効率**: 手動でのa11yテストが必要となり、開発速度低下

## 証拠 (Evidence)
### GitHub Actions履歴
- PR #27の複数回のa11y-nightly実行で404エラーが継続発生
- E2Eワークフローは1分で安定動作（CI最適化は成功）

### ワークフロー設定
```yaml
# 問題のあった従来のPre-flight設定
- name: WordPress sanity & prepare calendar page
  run: |
    # 複雑な設定手順が不安定
    npx wp-env run cli "wp plugin activate monthly-booking || true"
    # エラーハンドリングが不十分
```

## 暫定回避策 (Temporary Workaround)
1. **手動プラグイン有効化**: CI実行前に手動でプラグインを有効化
2. **ページ手動作成**: WordPress管理画面で`monthly-calendar`ページを作成
3. **パーマリンク手動設定**: 管理画面でパーマリンク構造を`/%postname%/`に変更

## 恒久対策 (Permanent Solution)
### A. WP-CLIブートストラップコマンド実装
**ファイル**: `includes/cli-bootstrap.php`
```php
<?php
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::add_command( 'mb bootstrap', 'MB_CLI_Bootstrap' );
}

class MB_CLI_Bootstrap {
    public function __invoke() {
        // 1. プラグイン有効化（冪等）
        // 2. 機能フラグ設定
        // 3. パーマリンク設定
        // 4. ページ作成
        // 5. ヘルスチェック
    }
}
```

### B. a11y-nightly.yml簡素化
```yaml
- name: Bootstrap WordPress environment
  run: npx wp-env run cli "wp mb bootstrap"

- name: Health check (simplified)
  run: |
    for i in {1..30}; do
      if curl -s -o /dev/null -w "%{http_code}" "$URL" | grep -q "200"; then
        exit 0
      fi
      sleep 2
    done
    exit 1
```

### C. ランタイムフォールバック（任意）
**ファイル**: `includes/fallback-calendar-endpoint.php`
- 仮想エンドポイント実装で固定ページ依存を回避
- `template_redirect`フックでカレンダー表示

## 検証手順 (Verification Steps)
1. **ローカルテスト**: `wp-env start` → `wp mb bootstrap` → `curl http://localhost:8888/monthly-calendar/`
2. **CI手動実行**: a11y-nightlyワークフローを手動トリガーして3回連続成功確認
3. **デバッグ確認**: 失敗時にa11y-debugアーティファクトが正常収集されることを確認

## 成功指標 (Success Metrics)
- [ ] `wp mb bootstrap`コマンドが単独で`/monthly-calendar/`を200にする
- [ ] a11y-nightlyワークフローが3回連続で成功
- [ ] 失敗時のデバッグアーティファクト収集が機能
- [ ] E2Eワークフローの1分実行時間が維持される

## 実装優先度
1. **高**: WP-CLIブートストラップ実装（必須）
2. **高**: a11y-nightly.yml更新（必須）
3. **中**: デバッグアーティファクト強化（推奨）
4. **低**: ランタイムフォールバック（任意）

---
**作成日時**: 2025-08-09 17:41 UTC  
**対象PR**: #27 (ci/add-pr-triggers)  
**ステータス**: 実装準備完了
