import { test, expect } from '@playwright/test';

test('Calendar navigation -> room selection -> campaign assignment', async ({ page }) => {
  await page.goto('/wp-login.php');
  await page.fill('#user_login', process.env.MB_ADMIN_USER || 'admin');
  await page.fill('#user_pass', process.env.MB_ADMIN_PASS || 'password');
  await page.click('#wp-submit');

  await page.goto('/wp-admin/admin.php?page=monthly-booking-calendar');

  await page.click('[data-testid="calendar-next"]');
  await page.click('[data-testid="calendar-room-row"]:nth-of-type(1)');

  await page.click('button:has-text("キャンペーン紐づけ")');
  await page.click('[data-testid="campaign-option"]:has-text("E2E Test 20%")');
  await page.click('button:has-text("適用")');

  const indicator = page.locator('[data-testid="campaign-badge"]', { hasText: '20%' });
  await expect(indicator.first()).toBeVisible();
});
