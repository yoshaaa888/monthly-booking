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
