const { test, expect } = require('@playwright/test');

test.describe('Monthly Booking Calendar E2E Tests', () => {
  
  test.beforeEach(async ({ page }) => {
    await page.goto('/monthly-calendar/');
    await page.waitForLoadState('networkidle');
  });

  test('Calendar displays 6-month view with proper structure', async ({ page }) => {
    await expect(page.locator('.monthly-booking-calendar-container')).toBeVisible();
    await expect(page.locator('h3')).toContainText('予約カレンダー');
    
    const months = await page.locator('.calendar-month').count();
    expect(months).toBeGreaterThanOrEqual(6);
    
    await expect(page.locator('.calendar-legend')).toBeVisible();
    
    const legendItems = await page.locator('.legend-item').count();
    expect(legendItems).toBeGreaterThanOrEqual(3);
  });

  test('Room selection dropdown functionality', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      const options = await roomSelector.locator('option').count();
      expect(options).toBeGreaterThan(1);
      
      await roomSelector.selectOption({ index: 1 });
      
      await page.waitForSelector('.loading', { state: 'visible', timeout: 1000 }).catch(() => {});
      await page.waitForSelector('.calendar-month', { timeout: 5000 });
      
      await expect(page.locator('.calendar-month')).toBeVisible();
    }
  });

  test('Calendar day status indicators display correctly', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
    }
    
    const calendarDays = page.locator('.calendar-day:not(.empty)');
    const dayCount = await calendarDays.count();
    expect(dayCount).toBeGreaterThan(0);
    
    const availableDays = page.locator('.calendar-day.available');
    const bookedDays = page.locator('.calendar-day.booked');
    const campaignDays = page.locator('.calendar-day.campaign');
    
    if (await availableDays.count() > 0) {
      await expect(availableDays.first().locator('.day-status')).toContainText('〇');
    }
    
    if (await bookedDays.count() > 0) {
      await expect(bookedDays.first().locator('.day-status')).toContainText('×');
    }
    
    if (await campaignDays.count() > 0) {
      await expect(campaignDays.first().locator('.day-status')).toContainText('△');
    }
  });

  test('Campaign tooltips functionality', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
    }
    
    const campaignDays = page.locator('.calendar-day.campaign');
    const campaignCount = await campaignDays.count();
    
    if (campaignCount > 0) {
      await campaignDays.first().hover();
      
      const tooltip = page.locator('.campaign-tooltip');
      if (await tooltip.count() > 0) {
        await expect(tooltip.first()).toBeVisible();
      }
    }
  });

  test('Keyboard navigation accessibility', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
    }
    
    const focusableDays = page.locator('.calendar-day[tabindex="0"]');
    const focusableCount = await focusableDays.count();
    
    if (focusableCount > 0) {
      await focusableDays.first().focus();
      await expect(focusableDays.first()).toBeFocused();
      
      await page.keyboard.press('Tab');
      
      if (focusableCount > 1) {
        await expect(focusableDays.nth(1)).toBeFocused();
      }
    }
  });

  test('Responsive design at mobile breakpoint', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
    }
    
    const calendarContainer = page.locator('.monthly-booking-calendar-container');
    await expect(calendarContainer).toBeVisible();
    
    const containerBox = await calendarContainer.boundingBox();
    expect(containerBox.width).toBeLessThanOrEqual(375);
    
    const calendarDays = page.locator('.calendar-day');
    if (await calendarDays.count() > 0) {
      const dayBox = await calendarDays.first().boundingBox();
      expect(dayBox.height).toBeGreaterThan(0);
    }
  });

  test('Responsive design at tablet breakpoint', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 });
    
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
    }
    
    const calendarContainer = page.locator('.monthly-booking-calendar-container');
    await expect(calendarContainer).toBeVisible();
    
    const containerBox = await calendarContainer.boundingBox();
    expect(containerBox.width).toBeLessThanOrEqual(768);
  });

  test('AJAX error handling', async ({ page }) => {
    await page.route('**/admin-ajax.php', route => {
      if (route.request().postData()?.includes('mbp_load_calendar')) {
        route.fulfill({ status: 500, body: 'Server Error' });
      } else {
        route.continue();
      }
    });
    
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      
      const errorMessage = page.locator('.error');
      await expect(errorMessage).toBeVisible({ timeout: 5000 });
      await expect(errorMessage).toContainText('エラー');
    }
  });

  test('Month boundaries and date transitions', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
    }
    
    const monthHeaders = page.locator('.month-header h4');
    const monthCount = await monthHeaders.count();
    expect(monthCount).toBeGreaterThanOrEqual(6);
    
    if (monthCount > 0) {
      const firstMonth = await monthHeaders.first().textContent();
      const lastMonth = await monthHeaders.last().textContent();
      
      expect(firstMonth).toMatch(/\d{4}年\d{1,2}月/);
      expect(lastMonth).toMatch(/\d{4}年\d{1,2}月/);
    }
  });

  test('Performance and load times', async ({ page }) => {
    const startTime = Date.now();
    
    await page.goto('/monthly-calendar/');
    await page.waitForSelector('.monthly-booking-calendar-container');
    
    const loadTime = Date.now() - startTime;
    expect(loadTime).toBeLessThan(5000);
    
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      const ajaxStartTime = Date.now();
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
      
      const ajaxTime = Date.now() - ajaxStartTime;
      expect(ajaxTime).toBeLessThan(3000);
    }
  });

  test('Specific room_id shortcode attribute', async ({ page }) => {
    await page.goto('/monthly-calendar-room-633/');
    await page.waitForLoadState('networkidle');
    
    await expect(page.locator('#room-selector')).not.toBeVisible();
    await expect(page.locator('.calendar-month')).toBeVisible();
  });

  test('Cleaning buffer logic verification', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
    }
    
    const bookedDays = page.locator('.calendar-day.booked');
    const bookedCount = await bookedDays.count();
    
    if (bookedCount > 0) {
      const firstBookedDay = bookedDays.first();
      await expect(firstBookedDay.locator('.day-status')).toContainText('×');
      
      const ariaLabel = await firstBookedDay.getAttribute('aria-label');
      expect(ariaLabel).toMatch(/(予約済み|清掃期間)/);
    }
  });

  test('Campaign priority over availability', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
    }
    
    const campaignDays = page.locator('.calendar-day.campaign');
    const campaignCount = await campaignDays.count();
    
    if (campaignCount > 0) {
      const firstCampaignDay = campaignDays.first();
      await expect(firstCampaignDay.locator('.day-status')).toContainText('△');
      
      const ariaLabel = await firstCampaignDay.getAttribute('aria-label');
      expect(ariaLabel).toContain('キャンペーン');
    }
  });

  test('Accessibility ARIA labels', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
    }
    
    const calendarDays = page.locator('.calendar-day:not(.empty)');
    const dayCount = await calendarDays.count();
    
    if (dayCount > 0) {
      const firstDay = calendarDays.first();
      const ariaLabel = await firstDay.getAttribute('aria-label');
      
      expect(ariaLabel).toBeTruthy();
      expect(ariaLabel).toMatch(/\d+月\d+日/);
      expect(ariaLabel).toMatch(/(予約可能|予約済み|キャンペーン)/);
    }
  });

});
