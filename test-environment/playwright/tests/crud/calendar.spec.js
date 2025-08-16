const { test, expect, request } = require('@playwright/test');

const BASE = process.env.BASE_URL || 'http://127.0.0.1:8888';

async function getNonce(page) {
  await page.goto(BASE + '/', { waitUntil: 'domcontentloaded' });
  const candidates = [
    BASE + '/monthly-estimate/',
    BASE + '/?pagename=monthly-estimate',
  ];
  for (const url of candidates) {
    await page.goto(url, { waitUntil: 'domcontentloaded' });
    const has = await page.evaluate(() => typeof window.monthlyBookingAjax !== 'undefined' && !!window.monthlyBookingAjax.nonce);
    if (has) {
      return page.evaluate(() => window.monthlyBookingAjax.nonce);
    }
  }
  return null;
}

test.describe('Calendar AJAX get_calendar_bookings', () => {
  test.setTimeout(120000);

  test('success with valid nonce returns rooms/bookings structure', async ({ page, context }) => {
    const nonce = await getNonce(page);
    expect(nonce, 'nonce should be available via monthlyBookingAjax').toBeTruthy();

    const req = await request.newContext();
    const resp = await req.post(BASE + '/wp-admin/admin-ajax.php', {
      form: {
        action: 'get_calendar_bookings',
        month: '1',
        year: '2025',
        nonce,
      },
    });
    expect(resp.status()).toBe(200);
    const json = await resp.json();
    expect(json.success).toBeTruthy();
    expect(json.data).toHaveProperty('rooms');
    expect(Array.isArray(json.data.rooms)).toBeTruthy();
    expect(json.data).toHaveProperty('bookings');
    expect(json.data).toHaveProperty('campaignDays');
  });

  test('missing nonce is rejected', async () => {
    const req = await request.newContext();
    const resp = await req.post(BASE + '/wp-admin/admin-ajax.php', {
      form: {
        action: 'get_calendar_bookings',
        month: '1',
        year: '2025',
      },
    });
    expect([200, 400, 403]).toContain(resp.status());
    const body = await resp.text();
    try {
      const json = JSON.parse(body);
      expect(json.success).toBeFalsy();
    } catch {
      expect(resp.status()).toBeGreaterThanOrEqual(400);
    }
  });

  test('invalid month/year is rejected', async ({ page }) => {
    const nonce = await getNonce(page);
    expect(nonce).toBeTruthy();

    const req = await request.newContext();
    const resp = await req.post(BASE + '/wp-admin/admin-ajax.php', {
      form: {
        action: 'get_calendar_bookings',
        month: '13',
        year: '1900',
        nonce,
      },
    });
    expect([200, 400]).toContain(resp.status());
    const json = await resp.json();
    expect(json.success).toBeFalsy();
  });
});
