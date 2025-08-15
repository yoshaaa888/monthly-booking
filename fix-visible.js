const fs = require('fs');
const path = 'test-environment/playwright/tests/journey_test_normal.spec.js';
let s = fs.readFileSync(path, 'utf8');

// 「resultLocator.or(...) に対する toBeVisible(...)」を、
// ① #estimate-result が見えること
// ② その中の「合計/小計/Total」いずれかが見えること
// の2行チェックに置換（strict mode 回避）
s = s.replace(
  /await\s+expect\(resultLocator\.or\(page\.getByText\([^)]*\)\)\)\.toBeVisible\(\{[^}]*\}\);\s*/m,
  `await expect(resultLocator).toBeVisible({ timeout: 15000 });
await expect(resultLocator.getByText(/(合計|小計|Total)/).first()).toBeVisible({ timeout: 15000 });
`
);

fs.writeFileSync(path, s);
console.log('✔ replaced visibility assertion');
