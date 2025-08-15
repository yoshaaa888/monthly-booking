const fs = require('fs');
const path = 'test-environment/playwright/tests/journey_test_normal.spec.js';
let src = fs.readFileSync(path, 'utf8');

const pat = /await page\.selectOption\('#room_id',\s*'1'\);\s*(\/\/.*)?/;
if (!pat.test(src)) {
  console.error("置換対象の `await page.selectOption('#room_id', '1')` が見つかりません。既に変更済みか、記述が違うかも。");
  process.exit(1);
}

const repl = `
    await page.waitForSelector('#room_id');
    await page.waitForSelector('#room_id option');
    const firstVal = await page.$eval('#room_id', sel => {
      const opt = Array.from(sel.options).find(op => op.value && !op.disabled);
      return opt ? opt.value : '';
    });
    if (!firstVal) throw new Error('No selectable options in #room_id');
    await page.selectOption('#room_id', firstVal);
`.trim();

src = src.replace(pat, repl);
fs.writeFileSync(path, src);
console.log('✅ Patched:', path);
