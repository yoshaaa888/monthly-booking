import { Page } from '@playwright/test';

export async function robustGoto(page: Page, target: string, attempts = 5) {
  for (let i = 0; i < attempts; i++) {
    try {
      await page.goto(target, { waitUntil: 'domcontentloaded', timeout: 45000 });
      await page.waitForLoadState('networkidle').catch(() => {});
      await page.waitForTimeout(200);
      await handleWpInterstitals(page);
      return;
    } catch {
      await page.waitForTimeout(500);
    }
  }
  try {
    await page.goto('/wp-admin/', { waitUntil: 'domcontentloaded', timeout: 45000 });
    await page.waitForLoadState('networkidle').catch(() => {});
    await page.waitForTimeout(200);
    await handleWpInterstitals(page);
  } catch {}
}

async function handleWpInterstitals(page: Page) {
  const url = page.url();

  if (url.includes('/wp-admin/upgrade.php')) {
    const btn = page.locator('input[type="submit"], button[type="submit"]');
    if (await btn.count()) {
      await btn.first().click().catch(() => {});
      await page.waitForLoadState('load').catch(() => {});
      await page.waitForTimeout(300);
    }
    return;
  }

  const maintenance = page.locator('text=Briefly unavailable for scheduled maintenance');
  if (await maintenance.count()) {
    await page.waitForTimeout(1000);
  }
}
