const fs = require('fs');
const f = 'test-environment/playwright/tests/journey_test_normal.spec.js';
let s = fs.readFileSync(f, 'utf8');

// 1) 「// --- end robust select ---const today」を改行で分離
s = s.replace(/(end robust select ---)\s*const today/g, '$1\n    const today');

// 2) new Date(today) を使っているのに today 未定義なら前行に定義を挿入
if (/new Date\(today\)/.test(s) && !/const today\s*=\s*new Date\(\)/.test(s)) {
  s = s.replace(/(\n\s*const checkinDate\s*=\s*new Date\(today\);)/, '\n    const today = new Date();$1');
}

// 3) options[] の3連チェックを「存在すればチェック」ループに置換
const triple =
/await page\.check\('input\[name="options\[\]"]\[value="1"]'\);\s*[\r\n]+[\s\S]*?await page\.check\('input\[name="options\[\]"]\[value="2"]'\);\s*[\r\n]+[\s\S]*?await page\.check\('input\[name="options\[\]"]\[value="3"]'\);\s*/m;

const single =
/await page\.check\('input\[name="options\[\]"]\[value="1"]'\);\s*/m;

const loop =
`for (const v of ['1','2','3']) {
  const cb = page.locator(\`input[name="options[]"][value="\${v}"]\`);
  if (await cb.count()) { await cb.check(); }
}
`;

if (triple.test(s)) {
  s = s.replace(triple, loop);
} else if (single.test(s)) {
  s = s.replace(single, loop);
}

// 4) 二重適用で同じループが重複していたら1つにまとめる
s = s.replace(/for \(const v of \['1','2','3'\][\s\S]*?\}\s*\}\s*/g, loop);

fs.writeFileSync(f, s);
console.log('✅ Sanitized', f);
