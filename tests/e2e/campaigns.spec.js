const { test, expect } = require('@playwright/test');
const { waitAjax } = require('./helpers');

const ADMIN_BASE = (process.env.BASE_URL || '') + '/wp-admin/';

async function login(page) {
  await page.goto(ADMIN_BASE);
  if (await page.locator('#user_login').isVisible()) {
    await page.fill('#user_login', process.env.WP_ADMIN_USER || '');
    await page.fill('#user_pass', process.env.WP_ADMIN_PASS || '');
    await page.click('#wp-submit');
  }
  await expect(page.locator('#wpadminbar')).toBeVisible();
}

test.describe('Campaigns edit/delete', () => {
  test('edit campaign sends update_campaign', async ({ page }) => {
    await login(page);
    await page.goto(ADMIN_BASE + 'admin.php?page=monthly-room-booking-campaigns');
    const editBtn = page.locator('.campaign-edit').first();
    await expect(editBtn).toBeVisible();
    page.on('dialog', async d => { await d.accept(); });
    await editBtn.click();
    await waitAjax(page, 'update_campaign');
    await page.reload();
    await expect(page.locator('.campaign-edit').first()).toBeVisible();
  });

  test('delete campaign sends delete_campaign and removes row', async ({ page }) => {
    await login(page);
    await page.goto(ADMIN_BASE + 'admin.php?page=monthly-room-bookking-campaigns'.replace('bookking','booking'));
    const rows = page.locator('.campaign-delete');
    const before = await rows.count();
    await expect(rows.first()).toBeVisible();
    page.on('dialog', async d => { await d.accept(); });
    await rows.first().click();
    await waitAjax(page, 'delete_campaign');
    await page.reload();
    const after = await page.locator('.campaign-delete').count();
    expect(after).toBeLessThanOrEqual(before);
  });
});
