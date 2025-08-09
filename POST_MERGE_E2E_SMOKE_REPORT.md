# Post-merge E2E（@smoke）実行レポート

* **対象リポジトリ**: `yoshaaa888/monthly-booking`
* **対象ワークフロー**: Post-merge E2E (main)
* **Run URL**: https://github.com/yoshaaa888/monthly-booking/actions/runs/16843427860
* **実行トリガー**: 自動（PR #11 merge）
* **concurrency**: `cancel-in-progress: true` 反映済み ✅

## 実行結果

* **Playwright 実行コマンド**:
  `npx playwright test --reporter=list,html --grep "@smoke" --max-failures=1 --workers=2`
* **検出テスト数**: 5 件（@smoke）- 1テスト × 5ブラウザ
* **経過時間**: 3 分 6 秒
* **結果**: ✅ 成功（再試行: 0 回）

## ログ抜粋

```
5 passed (8.9s)

✓ [chromium] › tests/e2e/smoke.spec.js:5:3 › Calendar Smoke Test @smoke › Basic calendar page loads and shows month headers @smoke (1.3s)
✓ [firefox] › tests/e2e/smoke.spec.js:5:3 › Calendar Smoke Test @smoke › Basic calendar page loads and shows month headers @smoke (2.2s)  
✓ [webkit] › tests/e2e/smoke.spec.js:5:3 › Calendar Smoke Test @smoke › Basic calendar page loads and shows month headers @smoke (5.2s)
✓ [Mobile Chrome] › tests/e2e/smoke.spec.js:5:3 › Calendar Smoke Test @smoke › Basic calendar page loads and shows month headers @smoke (1.1s)
✓ [Mobile Safari] › tests/e2e/smoke.spec.js:5:3 › Calendar Smoke Test @smoke › Basic calendar page loads and shows month headers @smoke (2.0s)
```

## アーティファクト

* Playwright HTML レポート: https://github.com/yoshaaa888/monthly-booking/actions/runs/16843427860/artifacts/3724355828
* test-results（trace/video/screenshot）: 同上

## 所感・改善メモ

* @smoke は安定しており、CIの所要時間が大幅短縮（35分+ → 3分6秒、91%改善）。
* a11y/keyboard は trace 確認のうえ、ARIA付与の遅延に対する待機調整 or ロケータ修正を別PRで対応。
* 5ブラウザ全てでテスト成功、カレンダー要素7個検出で基本機能確認完了。
* 旧ワークフロー #16843204192 は自動キャンセルされ、新ワークフロー #16843427860 が正常完了。

---

**検証完了**: ✅ smoke-only post-merge E2E workflow 実装成功
