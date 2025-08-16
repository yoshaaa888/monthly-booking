const { test, expect } = require('@playwright/test');

const base = process.env.BASE_URL || 'http://127.0.0.1:8888';

async function loginAsAdmin(page) {
  const user = 'mb_admin';
  const pass = 'mbpass';
  await page.goto(`${base}/wp-login.php?redirect_to=${encodeURIComponent(base + '/wp-admin/')}`, { waitUntil: 'domcontentloaded' });
  await page.fill('#user_login', user);
  await page.fill('#user_pass', pass);
  await page.click('#wp-submit');
  await page.waitForURL(/\/wp-admin\/?$/, { timeout: 15000 });
}

async function adminAjax(page, data) {
  const params = new URLSearchParams(data);
  const res = await page.request.post(`${base}/wp-admin/admin-ajax.php`, {
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    data: params.toString(),
  });
  return res;
}

test.describe.configure({ mode: 'serial' });

test('Campaigns: success create/update/delete', async ({ page }) => {
  await loginAsAdmin(page);
  const res = await page.request.get(`${base}/wp-admin/admin.php?page=monthly-room-booking-campaigns`);
  expect(res.status()).toBeLessThan(500);
  const html = await res.text();
  const nonceMatch = html.match(/name="nonce"\s+value="([^"]+)"/);
  expect(nonceMatch).toBeTruthy();
  const nonce = nonceMatch[1];

  let r = await adminAjax(page, {
    action: 'create_campaign',
    nonce,
    name: 'PlaywrightTestCampaign',
    discount_type: 'percentage',
    discount_value: '10',
    start_date: '2025-01-01',
    end_date: '2025-12-31',
  });
  expect(r.status()).toBe(200);
  const j = await r.json();
  expect(j.success).toBeTruthy();
  const id = j.data.campaign_id;
  expect(id).toBeTruthy();

  r = await adminAjax(page, {
    action: 'update_campaign',
    nonce,
    campaign_id: id,
    name: 'PlaywrightTestCampaignUpdated',
    discount_type: 'fixed',
    discount_value: '500',
    start_date: '2025-01-01',
    end_date: '2025-12-31',
  });
  expect(r.status()).toBe(200);
  const j2 = await r.json();
  expect(j2.success).toBeTruthy();

  r = await adminAjax(page, { action: 'delete_campaign', nonce, campaign_id: id });
  expect(r.status()).toBe(200);
  const j3 = await r.json();
  expect(j3.success).toBeTruthy();
});

test('Campaigns: missing nonce → 4xx', async ({ page }) => {
  await loginAsAdmin(page);
  const r = await adminAjax(page, {
    action: 'create_campaign',
    name: 'ShouldFail',
    discount_type: 'percentage',
    discount_value: '10',
    start_date: '2025-01-01',
    end_date: '2025-12-31',
  });
  expect(r.status()).toBeGreaterThanOrEqual(400);
});

test('Campaigns: invalid input → 4xx', async ({ page }) => {
  await loginAsAdmin(page);
  const res = await page.request.get(`${base}/wp-admin/admin.php?page=monthly-room-booking-campaigns`);
  const html = await res.text();
  const nonceMatch = html.match(/name="nonce"\s+value="([^"]+)"/);
  const nonce = nonceMatch ? nonceMatch[1] : '';
  const r = await adminAjax(page, {
    action: 'create_campaign',
    nonce,
    name: '', // invalid
    discount_type: 'percentage',
    discount_value: '-5',
    start_date: '',
    end_date: '',
  });
  expect(r.status()).toBeGreaterThanOrEqual(400);
});
