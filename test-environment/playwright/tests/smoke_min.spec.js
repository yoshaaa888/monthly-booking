const { test, expect } = require('@playwright/test');
const BASE = process.env.BASE_URL || 'http://127.0.0.1:8888';

test.setTimeout(60000);

test('Smoke: estimate page opens and room select visible', async ({ page }) => {
  await page.goto(BASE + '/', { waitUntil: 'domcontentloaded' });
  await page.request.get(BASE + '/wp-json/mb-qa/v1/ping');

  const candidates = [
    BASE + '/monthly-estimate/',
    BASE + '/?pagename=monthly-estimate',
  ];
  let ok = false;

  for (const url of candidates) {
    const resp = await page.goto(url, { waitUntil: 'domcontentloaded' });
    if (resp && resp.status() === 404) continue;
    const room = page.locator('#room_id');
    try {
      await page.waitForSelector('#room_id', { timeout: 30000 });
      await expect(room).toBeVisible({ timeout: 30000 });
      ok = true;
      break;
    } catch (_) {}
  }
  expect(ok).toBeTruthy();
});
