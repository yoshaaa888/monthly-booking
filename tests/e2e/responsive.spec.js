const { test, expect } = require('@playwright/test');

test.describe('Calendar Responsive Design Tests', () => {
  
  const viewports = [
    { name: 'Mobile', width: 375, height: 667 },
    { name: 'Tablet', width: 768, height: 1024 },
    { name: 'Desktop', width: 1280, height: 720 }
  ];

  viewports.forEach(viewport => {
    test(`Calendar layout at ${viewport.name} (${viewport.width}x${viewport.height})`, async ({ page }) => {
      await page.setViewportSize({ width: viewport.width, height: viewport.height });
      
      await page.goto('/monthly-calendar/');
      await page.waitForLoadState('networkidle');
      
      const roomSelector = page.locator('#room-selector');
      
      if (await roomSelector.isVisible()) {
        await roomSelector.selectOption({ index: 1 });
        await page.waitForSelector('.calendar-month');
      }
      
      const calendarContainer = page.locator('.monthly-booking-calendar-container');
      await expect(calendarContainer).toBeVisible();
      
      const containerBox = await calendarContainer.boundingBox();
      expect(containerBox.width).toBeLessThanOrEqual(viewport.width);
      
      const bodyScrollWidth = await page.evaluate(() => document.body.scrollWidth);
      expect(bodyScrollWidth).toBeLessThanOrEqual(viewport.width + 20);
    });
  });

  test('Touch interaction on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    
    await page.goto('/monthly-calendar/');
    await page.waitForLoadState('networkidle');
    
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
    }
    
    const campaignDays = page.locator('.calendar-day.campaign');
    const campaignCount = await campaignDays.count();
    
    if (campaignCount > 0) {
      await campaignDays.first().tap();
      
      const tooltip = page.locator('.campaign-tooltip');
      if (await tooltip.count() > 0) {
        await expect(tooltip.first()).toBeVisible();
      }
    }
  });

});
