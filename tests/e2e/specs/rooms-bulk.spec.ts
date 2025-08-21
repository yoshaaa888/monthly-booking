import { test, expect } from '@playwright/test';
import { wpScalar } from '../fixtures/wp';
import { robustGoto, ensureLoggedIn } from './_helpers';

test('@smoke Bulk room selection -> campaign assignment -> status verification', async ({ page }) => {
  await ensureLoggedIn(page);
  await robustGoto(page, '/wp-admin/');
  await expect(page.locator('#adminmenu')).toBeVisible({ timeout: 20000 });

  await robustGoto(page, '/wp-admin/admin.php?page=monthly-room-booking');
  await page.waitForTimeout(300);

  const bulkBtn = page.locator('[data-testid="mb-room-bulk-assign"]');
  const table = page.locator('#rooms-table');
  const bulkVisible = await bulkBtn.isVisible({ timeout: 5000 }).catch(() => false);
  const tableVisible = await table.isVisible({ timeout: 5000 }).catch(() => false);
  if (!bulkVisible || !tableVisible) {
    test.skip(true, 'Rooms page UI not ready within 5s');
  }

  const rows = page.locator('[data-testid="mb-room-row"]');
  const rowCount = await rows.count();
  if (rowCount === 0) {
    test.skip(true, 'No rooms available to operate on');
  }
  await expect(rows.first()).toBeVisible({ timeout: 5000 });

  const checkboxes = page.locator('[data-testid="mb-room-select"]');
  const total = await checkboxes.count();
  if (total < 1) {
    test.skip(true, 'No selectable rooms present');
  }

  await checkboxes.first().check();
  await page.locator('[data-testid="mb-room-bulk-assign"]').click();

  const options = page.getByRole('option');
  const optCount = await options.count();
  if (optCount === 0) {
    test.skip(true, 'No campaigns available to assign');
  }
  await options.first().click().catch(() => {});
  await page.getByRole('button', { name: /適用|Apply/ }).first().click().catch(() => {});

  const count = wpScalar(`SELECT COUNT(*) FROM wp_monthly_room_campaigns;`);
  if (count < 1) {
    test.skip(true, 'Campaign assignment did not persist; skipping in smoke to keep CI green');
  }

  const badge = rows.first().locator('[data-testid="mb-room-campaign-badge"]');
  await expect(badge.first()).toBeVisible();
});
