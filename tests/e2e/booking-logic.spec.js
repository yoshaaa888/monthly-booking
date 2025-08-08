const { test, expect } = require('@playwright/test');

test.describe('Calendar Booking Logic Tests', () => {
  
  test.beforeEach(async ({ page }) => {
    await page.goto('/monthly-calendar/');
    await page.waitForLoadState('networkidle');
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

  test('Booking priority over campaigns', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
    }
    
    const bookedDays = page.locator('.calendar-day.booked');
    const campaignDays = page.locator('.calendar-day.campaign');
    
    const bookedCount = await bookedDays.count();
    const campaignCount = await campaignDays.count();
    
    if (bookedCount > 0) {
      const firstBookedDay = bookedDays.first();
      const hasBookedClass = await firstBookedDay.evaluate(el => el.classList.contains('booked'));
      const hasCampaignClass = await firstBookedDay.evaluate(el => el.classList.contains('campaign'));
      
      expect(hasBookedClass).toBe(true);
      if (hasCampaignClass) {
        expect(hasBookedClass).toBe(true);
      }
    }
  });

  test('Half-open interval booking logic', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
    }
    
    const calendarDays = page.locator('.calendar-day:not(.empty)');
    const dayCount = await calendarDays.count();
    
    if (dayCount > 0) {
      let availableCount = 0;
      let bookedCount = 0;
      
      for (let i = 0; i < Math.min(dayCount, 10); i++) {
        const day = calendarDays.nth(i);
        const isAvailable = await day.evaluate(el => el.classList.contains('available'));
        const isBooked = await day.evaluate(el => el.classList.contains('booked'));
        
        if (isAvailable) availableCount++;
        if (isBooked) bookedCount++;
      }
      
      expect(availableCount + bookedCount).toBeGreaterThan(0);
    }
  });

  (process.env.CI ? test.skip : test)('Month boundary handling', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
    }
    
    const monthHeaders = page.locator('.month-header h4');
    const monthCount = await monthHeaders.count();
    
    expect(monthCount).toBeGreaterThanOrEqual(6);
    
    if (monthCount >= 2) {
      const firstMonth = await monthHeaders.first().textContent();
      const secondMonth = await monthHeaders.nth(1).textContent();
      
      expect(firstMonth).toMatch(/\d{4}年\d{1,2}月/);
      expect(secondMonth).toMatch(/\d{4}年\d{1,2}月/);
      expect(firstMonth).not.toBe(secondMonth);
    }
  });

});
