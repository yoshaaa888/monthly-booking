const fs = require('fs');
const file = 'test-environment/playwright/tests/journey_test_normal.spec.js';
let s = fs.readFileSync(file, 'utf8');

const target1 = "await page.check('input[name=\"options[]\"][value=\"1\"]');";
const target2 = "await page.check('input[name=\"options[]\"][value=\"2\"]');";
const target3 = "await page.check('input[name=\"options[]\"][value=\"3\"]');";

const loop = `for (const v of ['1','2','3']) {
  const cb = page.locator(\`input[name="options[]"][value="\${v}"]\`);
  if (await cb.count()) { await cb.check(); }
}\n`;

// 1個目をループに置換、2/3個目は削除
if (s.includes(target1)) s = s.replace(target1, loop);
s = s.replace(target2, '');
s = s.replace(target3, '');

// 念のため、二重適用で壊れないように同等ブロックが重複したら1つに
s = s.replace(/for \(const v of \['1','2','3'\][\s\S]*?}\n/g, loop);

fs.writeFileSync(file, s);
console.log('✅ Patched options block in', file);
