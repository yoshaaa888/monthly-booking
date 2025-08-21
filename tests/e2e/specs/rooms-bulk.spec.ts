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

  await page.locator('[data-testid="mb-room-select"]').nth(0).check();
  await page.locator('[data-testid="mb-room-select"]').nth(1).check();
  await page.locator('[data-testid="mb-room-bulk-assign"]').click();

  await page.getByRole('option', { name: /E2E Test 20%/ }).first().click().catch(() => {});
  await page.getByRole('button', { name: /適用|Apply/ }).first().click();

  const count = wpScalar(`SELECT COUNT(*) FROM wp_monthly_room_campaigns WHERE campaign_id=(SELECT id FROM wp_monthly_campaigns WHERE campaign_name='E2E Test 20%' ORDER BY id DESC LIMIT 1);`);
  expect(count).toBeGreaterThanOrEqual(2);

  const badge = rows.first().locator('[data-testid="mb-room-campaign-badge"]');
  await expect(badge.first()).toBeVisible();
});
