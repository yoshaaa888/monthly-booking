const fs = require('fs');
const p = 'test-environment/playwright/tests/journey_test_normal.spec.js';
let s = fs.readFileSync(p,'utf8');

const anchor = `const resultLocator = page.locator('#estimate-result');`;
const endMark = `console.log('Estimate completed');`;
const i = s.indexOf(anchor);
const j = s.indexOf(endMark, i);
if (i<0 || j<0) { console.error('置換位置が見つかりません'); process.exit(1); }

const inject = `
  // --- DEBUG: robust submit + invalid dump + screenshot ---
  const form = page.locator('#monthly-estimate-form, form').first();

  // ボタンがあればクリック、なくても requestSubmit で送信を試みる
  const calcBtn = page.locator('#calculate-estimate-btn, button[type="submit"], input[type="submit"]');
  if (await calcBtn.count()) {
    try { await calcBtn.first().click({ force: true, trial: false }); } catch {}
  }
  await page.evaluate(() => {
    const f = document.querySelector('#monthly-estimate-form, form');
    if (f) { (f.requestSubmit ? f.requestSubmit() : f.submit()); }
  });

  // change/input を明示的に発火（jQuery系トリガ対策）
  try {
    const r = document.querySelector('#room_id'); if (r) r.dispatchEvent(new Event('change',{bubbles:true}));
    const ci = document.querySelector('#start_date,#check_in_date'); if (ci) ci.dispatchEvent(new Event('change',{bubbles:true}));
    const co = document.querySelector('#end_date,#check_out_date'); if (co) co.dispatchEvent(new Event('change',{bubbles:true}));
  } catch {}

  // フォームの妥当性をチェックし、不備項目をログする
  const invalids = await page.evaluate(() => {
    const f=document.querySelector('#monthly-estimate-form, form'); if(!f) return ['NO_FORM'];
    const arr=[...f.querySelectorAll('input,select,textarea')];
    return arr.filter(el => !el.checkValidity()).map(el => (el.name||el.id||el.tagName)+': '+(el.validationMessage||'invalid'));
  });
  console.log('INVALID_FIELDS:', invalids);

  // ページ全体のスクショ
  try { await page.screenshot({ path: 'test-results/estimate-page.png', fullPage: true }); } catch {}

  // 結果の待機（可視 or テキスト）
  const visibleResult = page.locator('#estimate-result:visible').first();
  const visibleText   = page.locator('#estimate-result').getByText(/(合計|小計|Total|円)/).first();
  await Promise.race([
    visibleResult.waitFor({ state: 'visible', timeout: 60000 }),
    visibleText.waitFor({ state: 'visible',  timeout: 60000 })
  ]);

  // 最終確認とスニペット
  const finalTgt = (await visibleResult.count()) ? visibleResult : visibleText;
  await expect(finalTgt).toBeVisible({ timeout: 10000 });
  try { console.log('RESULT_SNIPPET:', (await finalTgt.innerText()).slice(0,200)); } catch {}
  // --- DEBUG end ---
`;

s = s.slice(0, i + anchor.length) + inject + '\n  ' + s.slice(j);
fs.writeFileSync(p, s);
console.log('✔ debug block inserted');
