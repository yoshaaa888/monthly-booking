const { test, expect } = require('@playwright/test');

test.describe('Monthly Booking Calendar E2E Tests', () => {
  
  test.beforeEach(async ({ page }) => {
    await page.goto('http://t-monthlycampaign.local/calendar-test-page/');
    await page.waitForLoadState('networkidle');
  });

  test('Calendar displays 6-month view with proper structure', async ({ page }) => {
    await expect(page.locator('.monthly-booking-calendar-container')).toBeVisible();
    await expect(page.locator('h3')).toContainText('予約カレンダー');
    
    const months = await page.locator('.calendar-month').count();
    expect(months).toBeGreaterThanOrEqual(6);
    
    await expect(page.locator('.calendar-legend')).toBeVisible();
    await expect(page.locator('.legend-symbol')).toHaveCount(3);
  });

  test('Room selection dropdown functionality', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    await expect(roomSelector).toBeVisible();
    
    const options = await roomSelector.locator('option').count();
    expect(options).toBeGreaterThan(1);
    
    await roomSelector.selectOption({ index: 1 });
    
    await page.waitForSelector('.loading', { state: 'visible' });
    await page.waitForSelector('.loading', { state: 'hidden' });
    
    await expect(page.locator('.calendar-month')).toBeVisible();
  });

  test('Calendar day status indicators', async ({ page }) => {
    await page.locator('#room-selector').selectOption({ index: 1 });
    await page.waitForSelector('.calendar-month');
    
    const availableDays = page.locator('.calendar-day.available');
    const bookedDays = page.locator('.calendar-day.booked');
    const campaignDays = page.locator('.calendar-day.campaign');
    
    await expect(availableDays.first()).toContainText('〇');
    
    if (await bookedDays.count() > 0) {
      await expect(bookedDays.first()).toContainText('×');
    }
    
    if (await campaignDays.count() > 0) {
      await expect(campaignDays.first()).toContainText('△');
    }
  });

  test('Campaign tooltips functionality', async ({ page }) => {
    await page.locator('#room-selector').selectOption({ index: 1 });
    await page.waitForSelector('.calendar-month');
    
    const campaignDays = page.locator('.calendar-day.campaign');
    const campaignCount = await campaignDays.count();
    
    if (campaignCount > 0) {
      await campaignDays.first().hover();
      await expect(page.locator('.campaign-tooltip')).toBeVisible();
    }
  });

  test('Keyboard navigation accessibility', async ({ page }) => {
    await page.locator('#room-selector').selectOption({ index: 1 });
    await page.waitForSelector('.calendar-month');
    
    const firstDay = page.locator('.calendar-day').first();
    await firstDay.focus();
    
    await expect(firstDay).toBeFocused();
    
    await page.keyboard.press('Tab');
    const secondDay = page.locator('.calendar-day').nth(1);
    await expect(secondDay).toBeFocused();
  });

  test('Responsive design at mobile breakpoint', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 });
    
    await page.locator('#room-selector').selectOption({ index: 1 });
    await page.waitForSelector('.calendar-month');
    
    const calendarContainer = page.locator('.monthly-booking-calendar-container');
    await expect(calendarContainer).toBeVisible();
    
    const calendarDays = page.locator('.calendar-day');
    const dayHeight = await calendarDays.first().boundingBox();
    expect(dayHeight.height).toBeLessThan(80);
  });

  test('AJAX error handling', async ({ page }) => {
    await page.route('**/admin-ajax.php', route => {
      route.fulfill({ status: 500, body: 'Server Error' });
    });
    
    await page.locator('#room-selector').selectOption({ index: 1 });
    
    await expect(page.locator('.error')).toBeVisible();
    await expect(page.locator('.error')).toContainText('エラーが発生しました');
  });

  test('Month boundaries and date transitions', async ({ page }) => {
    await page.locator('#room-selector').selectOption({ index: 1 });
    await page.waitForSelector('.calendar-month');
    
    const monthHeaders = page.locator('.month-header h4');
    const monthCount = await monthHeaders.count();
    expect(monthCount).toBeGreaterThanOrEqual(6);
    
    const firstMonth = await monthHeaders.first().textContent();
    const lastMonth = await monthHeaders.last().textContent();
    
    expect(firstMonth).toMatch(/\d{4}年\d{1,2}月/);
    expect(lastMonth).toMatch(/\d{4}年\d{1,2}月/);
  });

  test('Performance and load times', async ({ page }) => {
    const startTime = Date.now();
    
    await page.goto('http://t-monthlycampaign.local/calendar-test-page/');
    await page.waitForSelector('.monthly-booking-calendar-container');
    
    const loadTime = Date.now() - startTime;
    expect(loadTime).toBeLessThan(3000);
    
    const ajaxStartTime = Date.now();
    await page.locator('#room-selector').selectOption({ index: 1 });
    await page.waitForSelector('.calendar-month');
    
    const ajaxTime = Date.now() - ajaxStartTime;
    expect(ajaxTime).toBeLessThan(2000);
  });

  test('Specific room_id shortcode attribute', async ({ page }) => {
    await page.goto('http://t-monthlycampaign.local/calendar-specific-room/');
    await page.waitForLoadState('networkidle');
    
    await expect(page.locator('#room-selector')).not.toBeVisible();
    await expect(page.locator('.calendar-month')).toBeVisible();
  });

});
