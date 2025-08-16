const { test, expect } = require('@playwright/test');

test.describe('Monthly Booking - Campaign Journey Test', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/monthly-estimate/');
    await page.waitForLoadState('networkidle');
  });

  test('Last-minute booking with immediate move-in campaign', async ({ page }) => {
    await page.screenshot({ path: 'test-results/campaign-01-initial-page.png', fullPage: true });

    console.log('Testing last-minute booking with immediate move-in campaign...');
    
    await page.selectOption('#room_id', { index: 1 }); // 新宿レジデンス205号室
    
    const today = new Date();
    const checkinDate = new Date(today);
    checkinDate.setDate(today.getDate() + 3); // 3 days from now
    const checkoutDate = new Date(checkinDate);
    checkoutDate.setDate(checkinDate.getDate() + 10); // 10 days stay (SS plan)
    
    const checkinStr = checkinDate.toISOString().split('T')[0];
    const checkoutStr = checkoutDate.toISOString().split('T')[0];
    
    await page.fill('#move_in_date', checkinStr);
    await page.fill('#move_out_date', checkoutStr);
    
    await page.selectOption('#num_adults', '1');
    await page.selectOption('#num_children', '1');
    
    await page.check('input[name="options[]"][value="4"]'); // タオル類
    await page.check('input[name="options[]"][value="5"]'); // アメニティ類
    
    await page.fill('#guest_name', '佐藤花子');
    await page.fill('#guest_email', 'sato.campaign@example.com');
    await page.fill('#company_name', '即入居テスト会社');
    
    await page.screenshot({ path: 'test-results/campaign-02-form-filled.png', fullPage: true });
    
    await page.click('#calculate-estimate-btn');
    await page.waitForSelector('#estimate-result', { timeout: 10000 });
    
    await page.screenshot({ path: 'test-results/campaign-03-estimate-result.png', fullPage: true });
    
    console.log('Verifying immediate move-in campaign application...');
    
    const planText = await page.textContent('.plan-info');
    expect(planText).toContain('SS');
    
    const campaignBadge = await page.locator('.campaign-badge.last_minute');
    await expect(campaignBadge).toBeVisible();
    expect(await campaignBadge.textContent()).toBe('即入居');
    
    const campaignDiscount = await page.locator('.cost-item:has-text("キャンペーン割引")');
    await expect(campaignDiscount).toBeVisible();
    
    const optionDiscount = await page.locator('.cost-item:has-text("オプション割引")');
    await expect(optionDiscount).toBeVisible();
    
    await page.click('#submit-booking-btn');
    await page.waitForSelector('.booking-success', { timeout: 15000 });
    
    await page.screenshot({ path: 'test-results/campaign-04-booking-success.png', fullPage: true });
    
    const successMessage = await page.locator('.booking-success');
    await expect(successMessage).toBeVisible();
    
    const bookingId = await page.locator('.booking-id');
    await expect(bookingId).toBeVisible();
    
    console.log('✅ Last-minute campaign booking test completed successfully');
  });

  test('Early booking with advance reservation campaign', async ({ page }) => {
    console.log('Testing early booking with advance reservation campaign...');
    
    await page.selectOption('#room_id', { index: 2 }); // 渋谷アパートメント302号室
    
    const today = new Date();
    const checkinDate = new Date(today);
    checkinDate.setDate(today.getDate() + 45); // 45 days from now
    const checkoutDate = new Date(checkinDate);
    checkoutDate.setDate(checkinDate.getDate() + 120); // 120 days stay (M plan)
    
    const checkinStr = checkinDate.toISOString().split('T')[0];
    const checkoutStr = checkoutDate.toISOString().split('T')[0];
    
    await page.fill('#move_in_date', checkinStr);
    await page.fill('#move_out_date', checkoutStr);
    
    await page.selectOption('#num_adults', '2');
    await page.selectOption('#num_children', '0');
    
    await page.check('input[name="options[]"][value="1"]'); // 調理器具セット
    await page.check('input[name="options[]"][value="2"]'); // 食器類
    await page.check('input[name="options[]"][value="3"]'); // 洗剤類
    await page.check('input[name="options[]"][value="6"]'); // 寝具カバーセット
    
    await page.fill('#guest_name', '鈴木次郎');
    await page.fill('#guest_email', 'suzuki.early@example.com');
    await page.fill('#company_name', '早割テスト株式会社');
    
    await page.screenshot({ path: 'test-results/campaign-05-early-form-filled.png', fullPage: true });
    
    await page.click('#calculate-estimate-btn');
    await page.waitForSelector('#estimate-result', { timeout: 10000 });
    
    await page.screenshot({ path: 'test-results/campaign-06-early-estimate-result.png', fullPage: true });
    
    console.log('Verifying early booking campaign application...');
    
    const planText = await page.textContent('.plan-info');
    expect(planText).toContain('M');
    
    const campaignBadge = await page.locator('.campaign-badge.early');
    await expect(campaignBadge).toBeVisible();
    expect(await campaignBadge.textContent()).toBe('早割');
    
    const campaignDiscount = await page.locator('.cost-item:has-text("キャンペーン割引")');
    await expect(campaignDiscount).toBeVisible();
    
    const optionDiscount = await page.locator('.cost-item:has-text("オプション割引")');
    await expect(optionDiscount).toBeVisible();
    
    await page.click('#submit-booking-btn');
    await page.waitForSelector('.booking-success', { timeout: 15000 });
    
    await page.screenshot({ path: 'test-results/campaign-07-early-booking-success.png', fullPage: true });
    
    const successMessage = await page.locator('.booking-success');
    await expect(successMessage).toBeVisible();
    
    const bookingId = await page.locator('.booking-id');
    await expect(bookingId).toBeVisible();
    
    console.log('✅ Early booking campaign test completed successfully');
  });

  test('No campaign scenario (normal pricing)', async ({ page }) => {
    console.log('Testing booking without campaign eligibility...');
    
    await page.selectOption('#room_id', { index: 3 }); // 池袋ハイツ403号室
    
    const today = new Date();
    const checkinDate = new Date(today);
    checkinDate.setDate(today.getDate() + 15); // 15 days from now (no campaign)
    const checkoutDate = new Date(checkinDate);
    checkoutDate.setDate(checkinDate.getDate() + 40); // 40 days stay (S plan)
    
    const checkinStr = checkinDate.toISOString().split('T')[0];
    const checkoutStr = checkoutDate.toISOString().split('T')[0];
    
    await page.fill('#move_in_date', checkinStr);
    await page.fill('#move_out_date', checkoutStr);
    
    await page.selectOption('#num_adults', '1');
    await page.selectOption('#num_children', '0');
    
    await page.check('input[name="options[]"][value="7"]'); // 毛布
    
    await page.fill('#guest_name', '山田太郎');
    await page.fill('#guest_email', 'yamada.normal@example.com');
    
    await page.screenshot({ path: 'test-results/campaign-08-no-campaign-form.png', fullPage: true });
    
    await page.click('#calculate-estimate-btn');
    await page.waitForSelector('#estimate-result', { timeout: 10000 });
    
    await page.screenshot({ path: 'test-results/campaign-09-no-campaign-result.png', fullPage: true });
    
    console.log('Verifying no campaign is applied...');
    
    const planText = await page.textContent('.plan-info');
    expect(planText).toContain('S');
    
    const campaignBadges = await page.locator('.campaign-badge');
    await expect(campaignBadges).toHaveCount(0);
    
    const campaignDiscount = await page.locator('.cost-item:has-text("キャンペーン割引")');
    await expect(campaignDiscount).toHaveCount(0);
    
    const optionDiscount = await page.locator('.cost-item:has-text("オプション割引")');
    await expect(optionDiscount).toHaveCount(0);
    
    await page.click('#submit-booking-btn');
    await page.waitForSelector('.booking-success', { timeout: 15000 });
    
    await page.screenshot({ path: 'test-results/campaign-10-no-campaign-success.png', fullPage: true });
    
    const successMessage = await page.locator('.booking-success');
    await expect(successMessage).toBeVisible();
    
    console.log('✅ No campaign scenario test completed successfully');
  });
});
