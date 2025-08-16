// @ts-check
const { test, expect } = require('@playwright/test');

const BASE = process.env.BASE_URL || 'http://127.0.0.1:8888';

test('AJAX request to admin-ajax fires and returns 2xx/3xx', async ({ page }) => {
  // MB_FIX: fire an AJAX by visiting estimate and clicking the button
  await page.goto(BASE + "/monthly-estimate/", { waitUntil: "domcontentloaded" });
  const calcBtn = page.locator(`#calculate-estimate-btn, button:has-text("見積"), button:has-text("Calculate")`).first();
  await calcBtn.waitFor({ state: "visible", timeout: 10000 }).catch(()=>{});
  await calcBtn.click().catch(()=>{});
  await page.waitForLoadState("networkidle").catch(()=>{});
  // admin-ajax.php への最初のリクエストを待つ
  const ajaxPromise = page.waitForRequest(req =>
    /admin-ajax\.php/.test(req.url()) && req.method() === 'POST'
  );

  // レスポンスも待つ
  const respPromise = page.waitForResponse(res =>
    /admin-ajax\.php/.test(res.url())
  );

  // 対象ページへ
  await page.goto(`${BASE}/monthly-estimate/`, { waitUntil: 'domcontentloaded' });

  // 部屋セレクト表示チェック
  const roomSelect = page.locator('#room_id, select[name="room_id"]');
  await expect(roomSelect).toBeVisible({ timeout: 10000 });

  // （存在する場合）見積ボタンでAJAXを発火
  const calcBtn = page.locator('#calculate-estimate-btn, button#calculate-estimate-btn');
  if (await calcBtn.count()) {
    await calcBtn.first().click();
  }

  // いずれかのAJAXを捕捉
  const ajaxReq = await ajaxPromise;
  const ajaxRes = await respPromise;

  // ステータス検証
  const status = ajaxRes.status();
  expect([200, 201, 202, 204, 301, 302, 304]).toContain(status);

  // デバッグ出力
  console.log('AJAX URL:', ajaxReq.url());
  console.log('AJAX Status:', status);
});
