const { test, expect } = require('@playwright/test');

test.describe('Calendar Campaign Integration Tests', () => {
  
  test.beforeEach(async ({ page }) => {
    await page.goto('/monthly-calendar/');
    await page.waitForLoadState('networkidle');
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
        
        const tooltipText = await tooltip.first().textContent();
        expect(tooltipText).toBeTruthy();
        expect(tooltipText.length).toBeGreaterThan(0);
      }
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

  test('Room-specific vs global campaigns', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      const options = await roomSelector.locator('option').count();
      
      if (options > 2) {
        await roomSelector.selectOption({ index: 1 });
        await page.waitForSelector('.calendar-month');
        
        const room1CampaignDays = await page.locator('.calendar-day.campaign').count();
        
        await roomSelector.selectOption({ index: 2 });
        await page.waitForSelector('.calendar-month');
        
        const room2CampaignDays = await page.locator('.calendar-day.campaign').count();
        
        console.log(`Room 1 campaign days: ${room1CampaignDays}, Room 2 campaign days: ${room2CampaignDays}`);
      }
    }
  });

  test('Campaign date range accuracy', async ({ page }) => {
    const roomSelector = page.locator('#room-selector');
    
    if (await roomSelector.isVisible()) {
      await roomSelector.selectOption({ index: 1 });
      await page.waitForSelector('.calendar-month');
    }
    
    const campaignDays = page.locator('.calendar-day.campaign');
    const campaignCount = await campaignDays.count();
    
    if (campaignCount > 0) {
      for (let i = 0; i < Math.min(campaignCount, 5); i++) {
        const campaignDay = campaignDays.nth(i);
        const ariaLabel = await campaignDay.getAttribute('aria-label');
        
        expect(ariaLabel).toMatch(/\d+月\d+日/);
        expect(ariaLabel).toContain('キャンペーン');
      }
    }
  });

});
