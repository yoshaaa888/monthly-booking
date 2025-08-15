const { test, expect } = require('@playwright/test');
const BASE = process.env.BASE_URL || 'http://127.0.0.1:8888';

test('Smoke: estimate page opens and room select visible', async ({ page }) => {
  const candidates = [
    BASE + '/monthly-estimate/',
    BASE + '/?pagename=monthly-estimate', // fallback for plain permalinks
  ];
  let ok = false;
  for (const url of candidates) {
    await page.goto(url, { waitUntil: 'domcontentloaded' });
    const room = page.locator('#room_id');
    if (await room.count()) {
      await expect(room).toBeVisible({ timeout: 20000 });
      ok = true;
      break;
    }
  }
  expect(ok).toBeTruthy();
});
