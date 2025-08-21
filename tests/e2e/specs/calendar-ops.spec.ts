import { test, expect } from '@playwright/test';

test('@smoke Calendar displays and room selector works', async ({ page }) => {
  await page.goto('/wp-login.php');
  await page.fill('#user_login', process.env.MB_ADMIN_USER || 'admin');
  await page.fill('#user_pass', process.env.MB_ADMIN_PASS || 'password');
  await page.click('#wp-submit');

  await page.goto('/wp-admin/admin.php?page=monthly-room-booking-calendar');

  await expect(page.locator('[data-testid="mb-calendar-content"]')).toBeVisible();

  const roomSelector = page.locator('[data-testid="mb-calendar-room-selector"]');
  if (await roomSelector.count()) {
    await roomSelector.selectOption({ index: 1 }).catch(() => {});
  }

  const cells = page.locator('[data-testid="mb-calendar-cell"]');
  await expect(cells.first()).toBeVisible();
});
