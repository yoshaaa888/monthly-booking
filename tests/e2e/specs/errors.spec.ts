import { test, expect } from '@playwright/test';

test.describe('Error scenarios', () => {
  test('invalid login blocks admin access', async ({ page }) => {
    await page.goto('/wp-login.php');
    await page.fill('#user_login', 'invalid_user');
    await page.fill('#user_pass', 'invalid_pass');
    await page.click('#wp-submit');

    await expect(page).toHaveURL(/wp-login\.php/);
    await expect(page.locator('#login_error')).toBeVisible();
  });
});
