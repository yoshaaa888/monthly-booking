const { test, expect } = require('@playwright/test');

const base = process.env.BASE_URL || 'http://127.0.0.1:8888';

async function loginAsAdmin(page) {
  const user = 'mb_admin';
  const pass = 'mbpass';
  const adminUrl = `${base}/wp-admin/`;
  await page.goto(adminUrl, { waitUntil: 'domcontentloaded' });
  const onLogin = await page.$('#user_login');
  if (onLogin) {
    await page.fill('#user_login', user);
    await page.fill('#user_pass', pass);
    await page.click('#wp-submit');
  }
  await page.waitForURL(/\/wp-admin\/?$/, { timeout: 20000 });
}

async function getAdminNonce(page) {
  await page.goto(`${base}/wp-admin/tools.php?page=mb-test-nonce`, { waitUntil: 'domcontentloaded' });
  await page.waitForSelector('#nonce-dump', { timeout: 10000 });
  const txt = await page.textContent('#nonce-dump');
  let j = {};
  try { j = JSON.parse(txt || '{}'); } catch (e) {}
  return j.nonce;
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
  const nonce = await getAdminNonce(page);
  expect(nonce).toBeTruthy();

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

test('Campaigns: missing nonce → 4xx or success:false', async ({ page }) => {
  await loginAsAdmin(page);
  const r = await adminAjax(page, {
    action: 'create_campaign',
    name: 'ShouldFail',
    discount_type: 'percentage',
    discount_value: '10',
    start_date: '2025-01-01',
    end_date: '2025-12-31',
  });
  const status = r.status();
  if (status >= 200 && status < 300) {
    const j = await r.json();
    expect(j.success).toBeFalsy();
  } else {
    expect(status).toBeGreaterThanOrEqual(400);
  }
});

test('Campaigns: invalid input → 4xx or success:false', async ({ page }) => {
  await loginAsAdmin(page);
  const nonce = await getAdminNonce(page);
  const r = await adminAjax(page, {
    action: 'create_campaign',
    nonce,
    name: '',
    discount_type: 'percentage',
    discount_value: '-5',
    start_date: '',
    end_date: '',
  });
  const status = r.status();
  if (status >= 200 && status < 300) {
    const j = await r.json();
    expect(j.success).toBeFalsy();
  } else {
    expect(status).toBeGreaterThanOrEqual(400);
  }
});
