const { test, expect } = require('@playwright/test');

test.describe('Calendar Smoke Test', () => {
  
  test('Basic calendar page loads and shows month headers', async ({ page }) => {
    console.info('CAL_URL=', process.env.CAL_URL || 'not set');
    console.info('baseURL=', page.context()._options.baseURL);
    
    try {
      await page.goto('/monthly-calendar/');
      await page.waitForLoadState('networkidle', { timeout: 10000 });
      
      const calendarContainer = page.locator('.monthly-booking-calendar-container');
      await expect(calendarContainer).toBeVisible({ timeout: 5000 });
      
      const monthHeaders = page.locator('.month-header h4');
      const monthCount = await monthHeaders.count();
      
      console.info('Found month headers:', monthCount);
      expect(monthCount).toBeGreaterThan(0);
      
      console.info('✅ Smoke test passed: Calendar page loads with', monthCount, 'month headers');
      
    } catch (error) {
      console.error('❌ Smoke test failed:', error.message);
      throw error;
    }
  });

});
