const { test, expect } = require('@playwright/test');

test.describe('Calendar Accessibility Tests', () => {
  
  test.beforeEach(async ({ page }) => {
    await page.goto('/monthly-calendar/');
    await page.waitForLoadState('networkidle');
  });

  test('Calendar has proper ARIA labels and roles', async ({ page }) => {
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
      expect(ariaLabel).toMatch(/(予約可能|予約済み|キャンペーン|清掃期間)/);
    }
  });

  test('Keyboard navigation works correctly', async ({ page }) => {
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
        const secondDay = focusableDays.nth(1);
        if (await secondDay.isVisible()) {
          await expect(secondDay).toBeFocused();
        }
      }
    }
  });

  test('Screen reader compatibility', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
    }
    
    const calendarContainer = page.locator('.monthly-booking-calendar-container');
    const role = await calendarContainer.getAttribute('role');
    
    if (role) {
      expect(['application', 'grid', 'table']).toContain(role);
    }
    
    const monthHeaders = page.locator('.month-header h4');
    const headerCount = await monthHeaders.count();
    
    if (headerCount > 0) {
      const firstHeader = monthHeaders.first();
      const headerText = await firstHeader.textContent();
      expect(headerText).toMatch(/\d{4}年\d{1,2}月/);
    }
  });

});
