import { Page } from '@playwright/test';

export async function robustGoto(page: Page, target: string, attempts = 5) {
  for (let i = 0; i < attempts; i++) {
    try {
      await page.goto(target, { waitUntil: 'domcontentloaded' });
      await page.waitForLoadState('networkidle').catch(() => {});
      await page.waitForTimeout(200);
      await handleWpInterstials(page);
      return;
    } catch {
      await page.waitForTimeout(400);
    }
  }
  try {
    await page.goto('/wp-admin/', { waitUntil: 'domcontentloaded' });
    await page.waitForLoadState('networkidle').catch(() => {});
    await page.waitForTimeout(200);
    await handleWpInterstials(page);
  } catch {}
}

async function handleWpInterstials(page: Page) {
  const url = page.url();
  if (url.includes('/wp-admin/upgrade.php')) {
    const btn = page.locator('input[type="submit"], button[type="submit"]');
    if (await btn.count()) {
      await btn.first().click().catch(() => {});
      await page.waitForLoadState('load').catch(() => {});
      await page.waitForTimeout(300);
    }
  }
  if (url.includes('/wp-login.php') || url.includes('action=postpass')) {
    return;
  }
  const maintain = page.locator('text=Briefly unavailable for scheduled maintenance');
  if (await maintain.count()) {
    await page.waitForTimeout(1000);
  }
}

import { Page } from '@playwright/test';

export async function robustGoto(page: Page, url: string) {
  for (let i = 0; i < 5; i++) {
    try {
      await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 45000 });
      return;
    } catch {
      await page.waitForTimeout(1500);
      try {
        await page.goto('/wp-admin/', { waitUntil: 'domcontentloaded', timeout: 45000 });
      } catch {}
      await page.waitForTimeout(1000);
    }
  }
  await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 45000 });
}
