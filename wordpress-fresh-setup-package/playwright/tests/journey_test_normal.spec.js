const { test, expect } = require('@playwright/test');

test.describe('Monthly Booking - Normal Journey Test', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/monthly-estimate/');
    await page.waitForLoadState('networkidle');
  });

  test('Complete normal booking journey with early booking campaign', async ({ page }) => {
    await page.screenshot({ path: 'test-results/01-initial-page.png', fullPage: true });

    console.log('Step 1: Filling out estimate form...');
    
    await page.selectOption('#room_id', '1'); // 立川マンスリー101号室
    
    const today = new Date();
    const checkinDate = new Date(today);
    checkinDate.setDate(today.getDate() + 35); // 35 days from now
    const checkoutDate = new Date(checkinDate);
    checkoutDate.setDate(checkinDate.getDate() + 60); // 60 days stay (S plan)
    
    const checkinStr = checkinDate.toISOString().split('T')[0];
    const checkoutStr = checkoutDate.toISOString().split('T')[0];
    
    await page.fill('#move_in_date', checkinStr);
    await page.fill('#move_out_date', checkoutStr);
    
    await page.selectOption('#num_adults', '2');
    await page.selectOption('#num_children', '0');
    
    await page.check('input[name="options[]"][value="1"]'); // 調理器具セット
    await page.check('input[name="options[]"][value="2"]'); // 食器類
    await page.check('input[name="options[]"][value="3"]'); // 洗剤類
    
    await page.fill('#guest_name', '田中太郎');
    await page.fill('#guest_email', 'tanaka.test@example.com');
    await page.fill('#company_name', 'テスト株式会社');
    
    await page.screenshot({ path: 'test-results/02-form-filled.png', fullPage: true });
    
    console.log('Step 2: Calculating estimate...');
    await page.click('#calculate-estimate-btn');
    
    await page.waitForSelector('#estimate-result', { timeout: 10000 });
    
    await page.screenshot({ path: 'test-results/03-estimate-result.png', fullPage: true });
    
    console.log('Step 3: Verifying estimate calculations...');
    
    const planText = await page.textContent('.plan-info');
    expect(planText).toContain('S');
    
    const campaignBadge = await page.locator('.campaign-badge.early');
    await expect(campaignBadge).toBeVisible();
    expect(await campaignBadge.textContent()).toBe('早割');
    
    const optionDiscount = await page.locator('.cost-item:has-text("オプション割引")');
    await expect(optionDiscount).toBeVisible();
    
    const finalTotal = await page.locator('.cost-total');
    await expect(finalTotal).toBeVisible();
    
    console.log('Step 4: Submitting booking...');
    await page.click('#submit-booking-btn');
    
    await page.waitForSelector('.booking-success', { timeout: 15000 });
    
    await page.screenshot({ path: 'test-results/04-booking-success.png', fullPage: true });
    
    const successMessage = await page.locator('.booking-success');
    await expect(successMessage).toBeVisible();
    
    const bookingId = await page.locator('.booking-id');
    await expect(bookingId).toBeVisible();
    
    console.log('✅ Normal booking journey test completed successfully');
  });

  test('Verify form validation and error handling', async ({ page }) => {
    console.log('Testing form validation...');
    
    await page.click('#calculate-estimate-btn');
    
    const errorMessages = await page.locator('.error-message, .validation-error');
    await expect(errorMessages.first()).toBeVisible();
    
    await page.screenshot({ path: 'test-results/05-validation-errors.png', fullPage: true });
    
    await page.selectOption('#room_id', '1');
    
    const today = new Date();
    const checkinDate = new Date(today);
    checkinDate.setDate(today.getDate() + 10);
    const checkoutDate = new Date(checkinDate);
    checkoutDate.setDate(checkinDate.getDate() - 5); // Invalid: checkout before checkin
    
    await page.fill('#move_in_date', checkinDate.toISOString().split('T')[0]);
    await page.fill('#move_out_date', checkoutDate.toISOString().split('T')[0]);
    
    await page.click('#calculate-estimate-btn');
    
    const dateError = await page.locator('.error-message:has-text("日付")');
    await expect(dateError).toBeVisible();
    
    console.log('✅ Form validation working correctly');
  });

  test('Verify option selection and discount calculation', async ({ page }) => {
    console.log('Testing option selection and discounts...');
    
    await page.selectOption('#room_id', '1');
    
    const today = new Date();
    const checkinDate = new Date(today);
    checkinDate.setDate(today.getDate() + 20);
    const checkoutDate = new Date(checkinDate);
    checkoutDate.setDate(checkinDate.getDate() + 30);
    
    await page.fill('#move_in_date', checkinDate.toISOString().split('T')[0]);
    await page.fill('#move_out_date', checkoutDate.toISOString().split('T')[0]);
    await page.selectOption('#num_adults', '1');
    await page.selectOption('#num_children', '0');
    await page.fill('#guest_name', 'オプションテスト');
    await page.fill('#guest_email', 'option.test@example.com');
    
    await page.check('input[name="options[]"][value="1"]');
    await page.check('input[name="options[]"][value="2"]');
    
    await page.click('#calculate-estimate-btn');
    await page.waitForSelector('#estimate-result', { timeout: 10000 });
    
    const twoOptionDiscount = await page.locator('.cost-item:has-text("オプション割引")');
    await expect(twoOptionDiscount).toBeVisible();
    
    await page.screenshot({ path: 'test-results/06-two-option-discount.png', fullPage: true });
    
    await page.check('input[name="options[]"][value="3"]');
    await page.check('input[name="options[]"][value="4"]');
    await page.check('input[name="options[]"][value="5"]');
    await page.check('input[name="options[]"][value="6"]');
    await page.check('input[name="options[]"][value="7"]');
    
    await page.click('#calculate-estimate-btn');
    await page.waitForSelector('#estimate-result', { timeout: 10000 });
    
    await page.screenshot({ path: 'test-results/07-max-option-discount.png', fullPage: true });
    
    console.log('✅ Option discount calculation working correctly');
  });
});
