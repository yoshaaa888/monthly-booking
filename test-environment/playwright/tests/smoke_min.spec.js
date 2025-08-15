const { test, expect } = require('@playwright/test');

test('Smoke: estimate page opens and room select visible', async ({ page }) => {
  await page.goto('http://127.0.0.1:8888/monthly-estimate/', { waitUntil: 'domcontentloaded' });
  const room = page.locator('#room_id');
  await expect(room).toBeVisible({ timeout: 15000 });
  const opts = room.locator('option');
  const count = await opts.count();
  console.log('room option count:', count);
  expect(count).toBeGreaterThan(1); // プレースホルダ+1以上
});
