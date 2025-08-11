const { test, expect } = require('@playwright/test');

test.describe('Calendar Accessibility Tests', () => {
  
  test.beforeEach(async ({ page }) => {
    await page.goto('/monthly-calendar/');
    await page.waitForLoadState('networkidle');
    
    await page.waitForSelector('.monthly-booking-calendar-container', { 
      state: 'visible', 
      timeout: 15000 
    });
    
    await page.waitForTimeout(2000);
  });

  test('Calendar has proper ARIA labels and roles', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month', { timeout: 10000 });
      await page.waitForTimeout(1000); // Allow AJAX to complete
    }
    
    const calendarDays = page.locator('.calendar-day:not(.empty), .calendar-cell:not(.empty)');
    await expect(calendarDays.first()).toBeVisible({ timeout: 10000 });
    
    const dayCount = await calendarDays.count();
    expect(dayCount).toBeGreaterThan(0);
    
    const firstDay = calendarDays.first();
    const ariaLabel = await firstDay.getAttribute('aria-label');
    
    expect(ariaLabel).toBeTruthy();
    expect(ariaLabel).toMatch(/\d+月\d+日/);
    expect(ariaLabel).toMatch(/(予約可能|予約済み|キャンペーン|清掃期間)/);
  }, { timeout: 30000 });

  test('Keyboard navigation works correctly', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month', { timeout: 10000 });
      await page.waitForTimeout(1000); // Allow AJAX to complete
    }
    
    const focusableDays = page.locator('.calendar-day[tabindex="0"], .calendar-cell[tabindex="0"]');
    await expect(focusableDays.first()).toBeVisible({ timeout: 10000 });
    
    const focusableCount = await focusableDays.count();
    expect(focusableCount).toBeGreaterThan(0);
    
    await focusableDays.first().focus();
    await expect(focusableDays.first()).toBeFocused();
    
    await page.keyboard.press('Tab');
    
    if (focusableCount > 1) {
      const secondDay = focusableDays.nth(1);
      if (await secondDay.isVisible()) {
        await expect(secondDay).toBeFocused();
      }
    }
  }, { timeout: 30000 });

  test('Screen reader compatibility', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month', { timeout: 10000 });
      await page.waitForTimeout(1000); // Allow AJAX to complete
    }
    
    const calendarContainer = page.locator('.monthly-booking-calendar-container');
    await expect(calendarContainer).toBeVisible({ timeout: 10000 });
    
    const role = await calendarContainer.getAttribute('role');
    if (role) {
      expect(['application', 'grid', 'table']).toContain(role);
    }
    
    const monthHeaders = page.locator('.month-header h4, .calendar-header h4, .calendar-month h4');
    await expect(monthHeaders.first()).toBeVisible({ timeout: 10000 });
    
    const headerCount = await monthHeaders.count();
    expect(headerCount).toBeGreaterThan(0);
    
    const firstHeader = monthHeaders.first();
    const headerText = await firstHeader.textContent();
    expect(headerText).toMatch(/\d{4}年\d{1,2}月/);
  }, { timeout: 30000 });

});
