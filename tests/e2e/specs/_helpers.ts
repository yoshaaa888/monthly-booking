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
  let url = page.url();

  if (url.includes('/wp-admin/upgrade.php')) {
    const submit = page.locator('input[type="submit"], button[type="submit"]');
    if (await submit.count()) {
      await submit.first().click().catch(() => {});
      await page.waitForLoadState('load').catch(() => {});
      await page.waitForTimeout(300);
      const cont = page.locator('a:has-text("Continue"), a:has-text("継続"), a:has-text("続行")');
      if (await cont.count()) {
        await cont.first().click().catch(() => {});
        await page.waitForLoadState('domcontentloaded').catch(() => {});
        await page.waitForTimeout(200);
      }
    }
    try {
      await page.goto('/wp-admin/', { waitUntil: 'domcontentloaded', timeout: 45000 });
      await page.waitForLoadState('networkidle').catch(() => {});
    } catch {}
    return;
  }

  const maintenance = page.locator('text=Briefly unavailable for scheduled maintenance');
  if (await maintenance.count()) {
    await page.waitForTimeout(1000);
  }
}
