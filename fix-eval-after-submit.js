const fs = require('fs');
const p = 'test-environment/playwright/tests/journey_test_normal.spec.js';
let s = fs.readFileSync(p,'utf8');

// 以前挿入した DEBUG ブロックの「invalid チェック」部分を safer 版に差し替え
s = s.replace(
  /\/\/ フォームの妥当性をチェック[\s\S]*?console\.log\('INVALID_FIELDS':[^\n]*\);\n\s*\}\);\n/,
  `// 送信後、ナビゲーションが来る/来ない双方に対応して一旦落ち着くのを待つ
  let navigated = false;
  await Promise.race([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }).then(() => { navigated = true; }).catch(() => {}),
    page.waitForTimeout(1500)
  ]);
  console.log('URL_AFTER_SUBMIT:', page.url(), 'navigated=', navigated);

  // invalid 要素の検出は try/catch で安全に（新しいページにいても OK）
  try {
    const invalids = await page.$$eval(
      '#monthly-estimate-form input:invalid, #monthly-estimate-form select:invalid, #monthly-estimate-form textarea:invalid, form input:invalid, form select:invalid, form textarea:invalid',
      els => els.map(el => (el.name||el.id||el.tagName)+': '+(el.validationMessage||'invalid'))
    );
    console.log('INVALID_FIELDS:', invalids);
  } catch (e) {
    console.log('INVALID_FIELDS:ERR', String(e));
  }
  `
);

fs.writeFileSync(p, s);
console.log('✔ eval-after-submit patched');
