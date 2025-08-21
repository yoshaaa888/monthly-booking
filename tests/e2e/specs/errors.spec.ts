import { test, expect } from '@playwright/test';

test.describe('Error scenarios (nonce/capability/basic validation)', () => {
  test('invalid login should block admin access', async ({ page }) => {
    await page.goto('/wp-login.php');
    await page.fill('#user_login', 'not-admin');
    await page.fill('#user_pass', 'wrong');
    await page.click('#wp-submit');
    await expect(page).toHaveURL(/wp-login\.php/);
  });
});
