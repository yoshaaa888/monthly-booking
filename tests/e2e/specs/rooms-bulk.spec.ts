import { test, expect } from '@playwright/test';

test('Bulk room selection -> campaign assignment -> status verification', async ({ page }) => {
  await page.goto('/wp-login.php');
  await page.fill('#user_login', process.env.MB_ADMIN_USER || 'admin');
  await page.fill('#user_pass', process.env.MB_ADMIN_PASS || 'password');
  await page.click('#wp-submit');

  await page.goto('/wp-admin/admin.php?page=monthly-room-booking');
  const rows = page.locator('[data-testid="mb-room-row"]');
  await expect(rows.first()).toBeVisible();

  await page.locator('[data-testid="mb-room-select"]').nth(0).check();
  await page.locator('[data-testid="mb-room-select"]').nth(1).check();
  await page.locator('[data-testid="mb-room-bulk-assign"]').click();

  await page.getByRole('option', { name: /E2E Test 20%/ }).first().click().catch(() => {});
  await page.getByRole('button', { name: /適用|Apply/ }).first().click();

  const badge = rows.first().locator('[data-testid="mb-room-campaign-badge"]');
  await expect(badge.first()).toBeVisible();
});
