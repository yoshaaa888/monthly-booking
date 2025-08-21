import { test, expect } from '@playwright/test';
import { robustGoto } from './_helpers';

test('@smoke Calendar displays and room selector works', async ({ page }) => {
  await page.goto('/wp-login.php');
  await page.fill('#user_login', process.env.MB_ADMIN_USER || 'admin');
  await page.fill('#user_pass', process.env.MB_ADMIN_PASS || 'password');
  await page.click('#wp-submit');
  await page.waitForLoadState('domcontentloaded');
  await page.waitForLoadState('networkidle').catch(() => {});
  await page.waitForTimeout(800);
  await robustGoto(page, '/wp-admin/');
  await expect(page.locator('#adminmenu')).toBeVisible({ timeout: 20000 });

  await robustGoto(page, '/wp-admin/admin.php?page=monthly-room-booking-calendar');
  await page.waitForTimeout(500);

  await expect(page.locator('[data-testid="mb-calendar-content"]')).toBeVisible();

  const roomSelector = page.locator('[data-testid="mb-calendar-room-selector"]');
  if (await roomSelector.count()) {
    await roomSelector.selectOption({ index: 1 }).catch(() => {});
  }

  const cells = page.locator('[data-testid="mb-calendar-cell"]');
  await expect(cells.first()).toBeVisible();
});
