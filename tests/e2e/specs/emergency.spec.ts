import { test, expect } from '@playwright/test';

test('Vacancy detection -> quick discount application -> result confirmation', async ({ page }) => {
  await page.goto('/wp-login.php');
  await page.fill('#user_login', process.env.MB_ADMIN_USER || 'admin');
  await page.fill('#user_pass', process.env.MB_ADMIN_PASS || 'password');
  await page.click('#wp-submit');

  await page.goto('/wp-admin/admin.php?page=monthly-booking-rooms');

  await page.click('button:has-text("空室10日+")').catch(() => {});
  await page.click('[data-testid="room-row-actions"]:nth-of-type(1) >> text=クイック割引');
  await page.click('button:has-text("20%")');

  const badge = (await page.locator('[data-testid="assigned-campaign-badge"]').first()).first();
  await expect(badge).toContainText('20%');
});
