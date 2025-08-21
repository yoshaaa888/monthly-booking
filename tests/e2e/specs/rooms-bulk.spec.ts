import { test, expect } from '@playwright/test';
import { wpScalar } from '../fixtures/wp';
import { robustGoto, ensureLoggedIn } from './_helpers';

test('@smoke Bulk room selection -> campaign assignment -> status verification', async ({ page }) => {
  await ensureLoggedIn(page);
  await robustGoto(page, '/wp-admin/');
  await expect(page.locator('#adminmenu')).toBeVisible({ timeout: 20000 });

  await robustGoto(page, '/wp-admin/admin.php?page=monthly-room-booking');
  await page.waitForTimeout(500);

  const rows = page.locator('[data-testid="mb-room-row"]');
  await expect(rows.first()).toBeVisible();

  const checkboxes = page.locator('[data-testid="mb-room-select"]');
  const total = await checkboxes.count();
  expect(total).toBeGreaterThanOrEqual(1);

  await checkboxes.first().check();
  await page.locator('[data-testid="mb-room-bulk-assign"]').click();

  const anyOption = page.getByRole('option').first();
  if (await anyOption.count()) {
    await anyOption.click().catch(() => {});
  }
  await page.getByRole('button', { name: /適用|Apply/ }).first().click().catch(() => {});

  const count = wpScalar(`SELECT COUNT(*) FROM wp_monthly_room_campaigns;`);
  expect(count).toBeGreaterThanOrEqual(1);

  const badge = rows.first().locator('[data-testid="mb-room-campaign-badge"]');
  await expect(badge.first()).toBeVisible();
});
