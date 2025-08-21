import { test, expect } from '@playwright/test';
import { wpScalar } from '../fixtures/wp';

function fmtDate(d: Date) {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

test.describe('Campaign Management Flow', () => {
  test('create campaign -> assign to rooms -> verify calendar display + DB', async ({ page }) => {
    await page.goto('/wp-login.php');
    await page.fill('#user_login', process.env.MB_ADMIN_USER || 'admin');
    await page.fill('#user_pass', process.env.MB_ADMIN_PASS || 'password');
    await page.click('#wp-submit');

    await page.goto('/wp-admin/admin.php?page=monthly-room-booking-campaigns');

    await page.locator('[data-testid="mb-campaign-create"]').click();
    const form = page.locator('[data-testid="mb-campaign-form"]');
    await form.locator('[data-testid="mb-campaign-name"]').fill('E2E Test 20%');
    await form.locator('[data-testid="mb-campaign-discount-type"]').selectOption('percentage');
    await form.locator('[data-testid="mb-campaign-discount-value"]').fill('20');
    await form.locator('[data-testid="mb-campaign-period-fixed"]').check();

    const today = new Date();
    const start = fmtDate(today);
    const endDate = new Date(today.getTime());
    endDate.setDate(today.getDate() + 30);
    const end = fmtDate(endDate);

    await form.locator('[data-testid="mb-campaign-start-date"]').fill(start);
    await form.locator('[data-testid="mb-campaign-end-date"]').fill(end);
    await page.getByRole('button', { name: /保存|Save/ }).click();

    const count = wpScalar(`SELECT COUNT(*) FROM wp_monthly_campaigns WHERE campaign_name='E2E Test 20%';`);
    expect(count).toBeGreaterThan(0);

    await page.goto('/wp-admin/admin.php?page=monthly-room-booking');
    await page.locator('[data-testid="mb-room-select"]').nth(0).check();
    await page.locator('[data-testid="mb-room-select"]').nth(1).check();
    await page.locator('[data-testid="mb-room-bulk-assign"]').click();
    await page.getByRole('option', { name: /E2E Test 20%/ }).first().click().catch(() => {});
    await page.getByRole('button', { name: /適用|Apply/ }).first().click();

    const assignCount = wpScalar(`SELECT COUNT(*) FROM wp_monthly_room_campaigns WHERE campaign_id=(SELECT id FROM wp_monthly_campaigns WHERE campaign_name='E2E Test 20%' ORDER BY id DESC LIMIT 1);`);
    expect(assignCount).toBeGreaterThanOrEqual(2);

    await page.goto('/wp-admin/admin.php?page=monthly-room-booking-calendar');
    await expect(page.locator('[data-testid="mb-calendar-content"]')).toBeVisible();
    const anyBadge = page.locator('[data-testid="mb-calendar-cell"] .mb-cal-cmp, [data-testid="mb-calendar-cell"] .mb-cal-symbol');
    await expect(anyBadge.first()).toBeVisible();
  });
});
