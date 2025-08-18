# RUNBOOK: E2E Smoke（最短リカバリ）

この文書は E2E Smoke を「すぐ再現 → すぐ復旧」するための最短手順です。

---

## 日常運用（よく使うもの）
- 再実行（CI だけ回す）  
  `git commit --allow-empty -m "ci: re-run smoke" && git push`
- 監視ポイント  
  Actions → E2E Smoke → 最新 1 本だけを見る（古い並列は Cancel）

---

## 成功構成（チェックリスト）

**scripts/smoke_local.sh**
- start 行（正規形）：  
  `npx -y @wp-now/wp-now@latest start --wp "$WP_VER" --port "$PORT" --skip-browser > wp-now.log 2>&1 &`
- `--host` は **使わない**
- `BASE_URL` 既定：`http://127.0.0.1:${PORT}`
- MU 注入＆自己回復ロジックあり

**.github/workflows/smoke.yml**
- Job-level env（1ブロック）：  
  `NPX_YES: "1"`, `npm_config_legacy_peer_deps: "true"`, `CI: "1"`, `ADBLOCK: "1"`
- 依存インストール（fallback あり）：  
  `npm ci --no-audit --no-fund --legacy-peer-deps || npm i --no-audit --no-fund --legacy-peer-deps`
- 先置き（プロンプト根絶用・任意）：`babel-loader@^8.0.0-beta @babel/core@^7 webpack@^5 webpack-cli@^5`
- ブラウザ準備：`npx playwright install --with-deps chromium`
- HealthCheck：**最大 360s / 3 URL**  
  `/`、`/wp-json`、`/wp-admin/admin-ajax.php`
- 失敗時の尻尾出力：**Show tails on failure**（`wp-now.log`/ports/`rest.json`/`ajax.json`）
- アーティファクト：`wp-now.log`, `test-results/`, `playwright-report/`, `rest.json`, `ajax.json`
- concurrency：`group: e2e-smoke-${{ github.ref }}, cancel-in-progress: true`

---

## 赤になったら（順に実行）

1. **STEP 25**｜先置きインストール確認/追加  
   webpack/babel ツールチェインを事前に入れて TTY プロンプトを根絶
2. **STEP 26**｜wp-now 起動ライン/BASE_URL 正規化  
   start 行を正規形へ、`--host` 残骸がないか grep、`BASE_URL` を固定
3. **STEP 27**｜Playwright タイムアウト緩和（必要時）
4. **STEP 28–29**｜commit & trigger（小改修を反映して単発で再実行）
5. **STEP 30**｜“尻尾”の収集・貼付  
   - Run smoke ステップの末尾 50 行  
   - `===== wp-now.log (tail) =====` の末尾 50 行  
   - Listening ports（`:8888` が LISTEN か）

---

## ローカル再現の最短動線
```
PORT=8888 WP_VER=6.8.2 bash scripts/smoke_local.sh
# 疎通:
curl -I http://127.0.0.1:8888/wp-json   # 200/204 なら OK
```

## メモ
- `NPX_YES` と **先置き** により、babel/webpack 系の対話プロンプトは抑止済み
- `/wp-json` が 404/タイムアウトの時は `wp-now.log` 先頭/末尾で原因特定
