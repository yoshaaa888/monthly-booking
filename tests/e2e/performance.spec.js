const { test, expect } = require('@playwright/test');

test.describe('Calendar Performance Tests', () => {
  
  test('Initial page load performance', async ({ page }) => {
    const startTime = Date.now();
    
    await page.goto('/monthly-calendar/');
    await page.waitForSelector('.monthly-booking-calendar-container');
    
    const loadTime = Date.now() - startTime;
    expect(loadTime).toBeLessThan(5000);
    
    console.log(`Page load time: ${loadTime}ms`);
  });

  test('AJAX room selection performance', async ({ page }) => {
    await page.goto('/monthly-calendar/');
    await page.waitForLoadState('networkidle');
    
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      const startTime = Date.now();
      
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
      
      const ajaxTime = Date.now() - startTime;
      expect(ajaxTime).toBeLessThan(1000);
      
      console.log(`AJAX response time: ${ajaxTime}ms`);
    }
  });

  test('Memory usage and DOM performance', async ({ page }) => {
    await page.goto('/monthly-calendar/');
    await page.waitForLoadState('networkidle');
    
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
    }
    
    const calendarDays = page.locator('.calendar-day');
    const dayCount = await calendarDays.count();
    
    expect(dayCount).toBeLessThan(200);
    
    const jsHeapSize = await page.evaluate(() => {
      return performance.memory ? performance.memory.usedJSHeapSize : 0;
    });
    
    if (jsHeapSize > 0) {
      expect(jsHeapSize).toBeLessThan(50 * 1024 * 1024);
    }
  });

});
