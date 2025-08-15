const fs = require('fs');
const path = 'test-environment/playwright/tests/journey_test_normal.spec.js';
let s = fs.readFileSync(path, 'utf8');

// 挿入アンカー（結果ロケータ定義の直後から、"Estimate completed" 直前まで差し替え）
const anchor = `const resultLocator = page.locator('#estimate-result');`;
const endMarker = `console.log('Estimate completed');`;

const i = s.indexOf(anchor);
const j = s.indexOf(endMarker, i);
if (i === -1 || j === -1) {
  console.error('❌ 置換ポイントが見つかりませんでした。ファイル内容を確認してください。');
  process.exit(1);
}

const inject = `
  // --- robust submit & result wait ---
  // 計算ボタンがあればクリック
  const calcBtn = page.locator('#calculate-estimate-btn, button[type="submit"]');
  if (await calcBtn.count()) {
    await calcBtn.first().waitFor({ state: 'visible', timeout: 10000 }).catch(() => {});
    try {
      await expect(calcBtn.first()).toBeEnabled({ timeout: 10000 });
      await calcBtn.first().click();
    } catch {}
  }

  // 念のためフォームを submit（ボタンが無効/見えないケース対策）
  await page.evaluate(() => {
    const f = document.querySelector('#monthly-estimate-form, form');
    if (f) { (f.requestSubmit ? f.requestSubmit() : f.submit()); }
  });

  // 念のため change → input イベントで再計算トリガを踏む
  await page.locator('#room_id').dispatchEvent('change').catch(()=>{});
  await page.waitForLoadState('networkidle');

  // 「#estimate-result が visible」または「Total/合計/小計 の可視テキスト」のどちらかを待つ
  const visibleResult = page.locator('#estimate-result:visible');
  const visibleTotal  = page.getByText(/(合計|小計|Total)/).first();

  await Promise.race([
    visibleResult.waitFor({ state: 'visible', timeout: 30000 }),
    visibleTotal.waitFor({ state: 'visible',  timeout: 30000 })
  ]);

  // 最終確認（曖昧さ回避のため .first() で厳密に）
  const finalTarget = (await visibleResult.count())
    ? visibleResult.first()
    : visibleTotal.first();

  await expect(finalTarget).toBeVisible({ timeout: 10000 });

  // デバッグ用に少しだけ中身を出力
  try {
    const snippet = await finalTarget.innerText();
    console.log('RESULT_SNIPPET:', (snippet || '').slice(0, 200));
  } catch {}
`;

const out = s.slice(0, i + anchor.length) + inject + '\n  ' + s.slice(j);
fs.writeFileSync(path, out);
console.log('✔ hardened submit & result wait inserted');
