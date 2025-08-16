const fs=require('fs');
const path='test-environment/playwright/tests/journey_test_normal.spec.js';
let s=fs.readFileSync(path,'utf8');

// 1) '#room_id option' の待ちを削除
s=s.replace(/^\s*await\s+page\.waitForSelector\('#room_id option'(?:,[^\)]*)?\);\s*$/gm,'');

// 2) 部屋選択を index=1 に統一するブロック
const robust = `
    // --- robust room select (index-based) ---
    await page.waitForSelector('#room_id', { state: 'attached' });
    await page.locator('#room_id').selectOption({ index: 1 });
    // --- end robust select ---
`;

// 3) 旧実装や過去のパッチを robust に置換
s=s.replace(/await\s+page\.selectOption\('#room_id',\s*'[^']+'\);\s*(\/\/[^\n]*)?/g, robust);
s=s.replace(/\/\/ --- robust room select[\s\S]*?end robust select ---/g, robust);
s=s.replace(/await\s+page\.waitForSelector\('#room_id'\);\s*[\s\S]*?selectOption\([^)]+\);\s*/m, robust);

// 4) robust ブロックの直後に「改行して」続きのコードが来るように保険
s=s.replace(/end robust select ---\s*const today/g, 'end robust select ---\n    const today');

fs.writeFileSync(path,s);
console.log('✅ fixed',path);
