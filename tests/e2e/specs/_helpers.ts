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
export async function ensureLoggedIn(page: Page, user?: string, pass?: string) {
  const u = user || process.env.MB_ADMIN_USER || 'admin';
  const p = pass || process.env.MB_ADMIN_PASS || 'password';

  for (let i = 0; i < 3; i++) {
    try {
      await page.goto('/wp-admin/', { waitUntil: 'domcontentloaded', timeout: 45000 });
      await page.waitForLoadState('networkidle').catch(() => {});
      await handleWpInterstitals(page);
      if (await page.locator('#adminmenu').first().isVisible().catch(() => false)) {
        return;
      }
    } catch {}

    await page.goto('/wp-login.php', { waitUntil: 'domcontentloaded', timeout: 45000 });
    const loginForm = page.locator('#loginform');
    if (await loginForm.count()) {
      await page.fill('#user_login', u);
      await page.fill('#user_pass', p);
      await page.click('#wp-submit');
    }
    await page.waitForLoadState('domcontentloaded').catch(() => {});
    await page.waitForLoadState('networkidle').catch(() => {});
    await page.waitForTimeout(600);
    await handleWpInterstitals(page);

    await page.goto('/wp-admin/', { waitUntil: 'domcontentloaded', timeout: 45000 });
    await page.waitForLoadState('networkidle').catch(() => {});
    await page.waitForTimeout(400);
    await handleWpInterstitals(page);

    if (await page.locator('#adminmenu').first().isVisible().catch(() => false)) {
      return;
    }
    const err = page.locator('#login_error');
    if (await err.count()) {
      await page.waitForTimeout(500);
    }
  }
}
