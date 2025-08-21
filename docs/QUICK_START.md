# Monthly Room Booking v1.0.0 — Quick Start Guide

This guide shows the “fresh food time‑sale” operational workflow using the Calendar, Campaigns, and Rooms screens.

What you’ll do
- Create a Campaign (割引方式、期間タイプ)
- Assign to Room(s) (紐づけ)
- Verify on Calendar (◎、○、◆、△、×)
- Run daily operations quickly with row actions and the right side panel

1) Create a Campaign
- Go to: 管理画面 → キャンペーン
- New Campaign
  - 名称: 任意（例: 10% OFF）
  - 割引方式: 定率（例: 10%）
  - 期間タイプ: 固定 または 入居日連動 または 無期限
  - ステータス: 有効
- Save

Tips
- 種別（immediate/earlybird/flatrate）アイコンと割引は一覧に表示
- 入居日連動（checkin-relative）や無期限は運用に応じて選択

2) Assign Campaign to Rooms
- Path A: 部屋一覧 → 複数選択 → [一括紐づけ]
- Path B: キャンペーン一覧 → 行アクション [部屋へ紐づけ]
- Path C: カレンダー → 行アクション [キャンペーン紐づけ] or 右パネルのショートカット

Assignment fields
- 対象部屋: 単一または複数
- 期間: キャンペーンの適用期間（固定/入居日連動/無期限に応じて）
- 有効/無効: 運用に応じて切替

3) Verify on Calendar
- Go to: カレンダー
- シンボル凡例
  - ◆: 予約中 [checkin, checkout)
  - △: 清掃バッファ [checkout, +5日)
  - ◎: 空室＋キャンペーン（今日対象の割引あり）
  - ○: 空室（キャンペーンなし）
  - ×: 利用不可（例: 部屋が非アクティブ）
- 操作
  - セルをクリック → 右パネルが更新（部屋・日付・状態・有効/今月開始予定キャンペーン）
  - 行アクション [キャンペーン紐づけ] / [清掃済み切替] で即操作

4) Daily Operation Procedures
- 朝の確認
  - カレンダーを開き、◆（占有）と△（清掃バッファ）を確認
  - ◎（空室＋キャンペーン）が十分に出ているかチェック
- 値下げ（キャンペーン）の調整
  - 右パネル / 行アクション → [キャンペーン紐づけ]
  - 空室が続く部屋へ期間・割引を素早く適用
- 清掃完了の反映
  - チェックアウト後の△期間を短縮/完了したら [清掃済み切替]
- 予約登録時
  - 予約登録画面の再計算で価格を確認（二重割引防止あり）

5) Notes and Best Practices
- 優先順位: ◆ > △ > ◎ > ○ > ×（最上位を表示）
- 期間ロジックは半開区間: [checkin, checkout)
- 清掃バッファは [checkout, checkout+5日)
- 450室規模の運用
  - 一覧/カレンダーのフィルタを活用
  - 将来的な最適化（仮想スクロール等）は実運用のフィードバック後に実施予定

Troubleshooting Cheatsheet
- 記号が意図通りでない
  - 予約・清掃・キャンペーンの期間を再確認
- 右パネルが更新されない
  - ブラウザのエラーコンソールを確認。管理者ログインの有効期限切れに注意
- 紐づけが保存できない
  - 期間重複や無効/日付誤りのチェックに引っかかっている可能性。エラー表示を確認
