const fs = require('fs');
const file = 'test-environment/playwright/tests/journey_test_normal.spec.js';
let s = fs.readFileSync(file, 'utf8');

const robust =
`// --- robust room select (no visibility wait) ---
await page.waitForSelector('#room_id', { state: 'attached' });
// 最初の有効 option（placeholderの次）を選択
await page.locator('#room_id').selectOption({ index: 1 });
// --- end robust select ---`;

// 1) 固定値 '1' を使う古い実装を置換
s = s.replace(/await\s+page\.selectOption\('#room_id',\s*'1'\);\s*(\/\/[^\n]*)?/g, robust);

// 2) 以前のパッチで入った firstVal 系の塊を置換
s = s.replace(/await\s+page\.waitForSelector\('#room_id'[\s\S]*?await\s+page\.selectOption\('#room_id'[\s\S]*?;\s*/m, robust);

// 3) $eval/$$eval を使って options を配列化していた塊を丸ごと置換
s = s.replace(/const\s+opts\s*=\s*await\s+page\.\$?\$eval\([\s\S]*?selectOption\(\{ index:\s*1 \}\);\s*/m, robust);

// 4) 可視性待ちで詰まる行を消す
s = s.replace(/^\s*await\s+page\.waitForSelector\('#room_id option'(?:,[^\)]*)?\);\s*$/gm, '');

// 念のため、重複した robust ブロックを 1 回に正規化
s = s.replace(/\/\/ --- robust room select[\s\S]*?end robust select ---/g, robust);

fs.writeFileSync(file, s);
console.log('✅ Repatched', file);
