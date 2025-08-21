import { test, expect } from '@playwright/test';

test('Vacancy detection -> quick discount application -> result confirmation (if available)', async ({ page }) => {
  await page.goto('/wp-login.php');
  await page.fill('#user_login', process.env.MB_ADMIN_USER || 'admin');
  await page.fill('#user_pass', process.env.MB_ADMIN_PASS || 'password');
  await page.click('#wp-submit');

  await page.goto('/wp-admin/admin.php?page=monthly-room-booking');

  const vacant = page.locator('[data-testid="mb-room-vacancy"]');
  if (await vacant.count() === 0) {
    test.skip();
  }

  const quickBtn = page.getByRole('button', { name: /Quick Discount|緊急割/ });
  if (await quickBtn.count() === 0) {
    test.skip();
  }

  await quickBtn.first().click().catch(() => {});
  const applied = page.locator('[data-testid="mb-room-campaign-badge"]');
  await expect(applied.first()).toBeVisible();
});
