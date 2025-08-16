const fs = require('fs');
const path = 'test-environment/playwright/tests/journey_test_normal.spec.js';
let s = fs.readFileSync(path, 'utf8');

// アンカー：結果ロケータの直後から、"Estimate completed" の手前までを置換
const anchor = `const resultLocator = page.locator('#estimate-result');`;
const endMarker = `console.log('Estimate completed');`;

const i = s.indexOf(anchor);
const j = s.indexOf(endMarker, i);
if (i === -1 || j === -1) {
  console.error('❌ 置換アンカーを見つけられませんでした。ファイル構成を確認してください。');
  process.exit(1);
}

const inject = `
  // --- robust result wait & click ---
  // 計算ボタン（あれば）を押す
  const calcBtn = page.locator('#calculate-estimate-btn, button[type="submit"]');
  if (await calcBtn.count()) {
    await expect(calcBtn).toBeEnabled({ timeout: 10000 });
    await calcBtn.click();
  }

  // ネットワーク静穏 + DOM が可視になるまで待つ
  await page.waitForLoadState('networkidle');

  await page.waitForFunction(() => {
    const el = document.querySelector('#estimate-result');
    if (!el) return false;
    const cs = getComputedStyle(el);
    const visible = cs.display !== 'none' && cs.visibility !== 'hidden' && (parseFloat(cs.opacity || '1') > 0);
    const hasText = /(合計|小計|Total)/i.test((el.textContent || '').trim());
    return visible && hasText;
  }, { timeout: 30000 });

  // 最終確認
  await expect(page.locator('#estimate-result')).toBeVisible({ timeout: 30000 });
  await expect(page.locator('#estimate-result').getByText(/(合計|小計|Total)/)).toBeVisible({ timeout: 30000 });
`;

const out = s.slice(0, i + anchor.length) + inject + '\n  ' + s.slice(j);
fs.writeFileSync(path, out);
console.log('✔ result wait block inserted');
