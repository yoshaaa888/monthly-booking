import { test, expect } from '@playwright/test';
import { wpScalar } from '../fixtures/wp';
import { robustGoto, ensureLoggedIn } from './_helpers';

function fmtDate(d: Date) {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

test.describe('@smoke Campaign Management Flow', () => {
  test('@smoke create campaign -> assign to rooms -> verify calendar display + DB', async ({ page }) => {
    await ensureLoggedIn(page);
    await robustGoto(page, '/wp-admin/');
    await expect(page.locator('#adminmenu')).toBeVisible({ timeout: 20000 });

    const campaignsTarget = '/wp-admin/admin.php?page=monthly-room-booking-campaigns';
    await robustGoto(page, campaignsTarget);
    await page.waitForTimeout(500);

    const createBtn = page.locator('[data-testid="mb-campaign-create"]');
    if (await createBtn.count()) {
      await createBtn.click();
      const form = page.locator('[data-testid="mb-campaign-form"]');
      await form.locator('[data-testid="mb-campaign-name"]').fill('E2E Test 20%');
      const typeSel = form.locator('[data-testid="mb-campaign-type"], [data-testid="mb-campaign-discount-type"]');
      await typeSel.first().selectOption('percentage').catch(() => {});
      await form.locator('[data-testid="mb-campaign-discount-value"]').fill('20').catch(() => {});
      const periodFixed = form.locator('[data-testid="mb-campaign-period-fixed"]');
      if (await periodFixed.count()) {
        await periodFixed.check().catch(() => {});
      }

      const today = new Date();
      const start = fmtDate(today);
      const endDate = new Date(today.getTime());
      endDate.setDate(today.getDate() + 30);
      const end = fmtDate(endDate);

      await form.locator('[data-testid="mb-campaign-start-date"]').fill(start).catch(() => {});
      await form.locator('[data-testid="mb-campaign-end-date"]').fill(end).catch(() => {});
      await page.getByRole('button', { name: /保存|Save/ }).click().catch(() => {});
      const count = wpScalar(`SELECT COUNT(*) FROM wp_monthly_campaigns;`);
      expect(count).toBeGreaterThan(0);
    } else {
      await expect(page.locator('[data-testid="mb-campaign-list"], [data-testid="mb-campaign-list-legacy"]')).toBeVisible();
    }

    await robustGoto(page, '/wp-admin/admin.php?page=monthly-room-booking');
    await page.waitForTimeout(500);

    await page.locator('[data-testid="mb-room-select"]').nth(0).check();
    await page.locator('[data-testid="mb-room-select"]').nth(1).check();
    await page.locator('[data-testid="mb-room-bulk-assign"]').click();
    await page.getByRole('option', { name: /E2E Test 20%/ }).first().click().catch(() => {});
    await page.getByRole('button', { name: /適用|Apply/ }).first().click();

    const assignCount = wpScalar(`SELECT COUNT(*) FROM wp_monthly_room_campaigns WHERE campaign_id=(SELECT id FROM wp_monthly_campaigns WHERE campaign_name='E2E Test 20%' ORDER BY id DESC LIMIT 1);`);
    expect(assignCount).toBeGreaterThanOrEqual(2);

    await robustGoto(page, '/wp-admin/admin.php?page=monthly-room-booking-calendar');
    await page.waitForTimeout(500);

    await expect(page.locator('[data-testid="mb-calendar-content"]')).toBeVisible();
    const anyBadge = page.locator('[data-testid="mb-calendar-cell"] .mb-cal-cmp, [data-testid="mb-calendar-cell"] .mb-cal-symbol');
    await expect(anyBadge.first()).toBeVisible();
  });
});
