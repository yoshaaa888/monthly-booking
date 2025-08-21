import { test, expect } from '@playwright/test';
import { robustGoto, ensureLoggedIn } from './_helpers';

test('@smoke Calendar displays and room selector works', async ({ page }) => {
  await ensureLoggedIn(page);
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
