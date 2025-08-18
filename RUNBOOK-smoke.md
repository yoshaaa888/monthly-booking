# RUNBOOK: E2E Smoke（最短リカバリ）

## 日常運用
- 再実行: `git commit --allow-empty -m "ci: re-run smoke" && git push`

## 赤になったら（順に実行）
- STEP 25: 先置きインストール確認/追加
- STEP 26: wp-now 起動ライン/BASE_URL 正規化
- STEP 27: Playwright タイムアウト緩和（必要時）
- STEP 28-29: commit & trigger
- STEP 30: 失敗 run の末尾50行（Run smoke / wp-now.log / Listening ports）を貼る

## 成功構成（チェックリスト）
- start = `@wp-now/wp-now@latest`、`--skip-browser`、`--host`なし
- job-level env: NPX_YES / npm_config_legacy_peer_deps / CI / ADBLOCK
- HealthCheck: 360s / 3URL
- Show tails on failure あり
