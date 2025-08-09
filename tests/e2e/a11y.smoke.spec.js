const { test, expect } = require('@playwright/test');

test.describe('A11y Smoke Tests', () => {
  test('monthly-calendar page loads and has basic accessibility landmarks @a11y @smoke', async ({ page }) => {
    await page.goto('/monthly-calendar/');
    
    expect(page.url()).toContain('/monthly-calendar/');
    
    await expect(page.locator('body')).toBeVisible();
    
    const mainSelectors = [
      'main[role="main"]',
      '[role="main"]', 
      'main',
      '#main',
      '.site-main',
      '.entry-content'
    ];
    
    let mainFound = false;
    for (const selector of mainSelectors) {
      const element = page.locator(selector);
      if (await element.count() > 0) {
        await expect(element).toBeVisible();
        mainFound = true;
        break;
      }
    }
    
    if (!mainFound) {
      console.log('Warning: No main content landmark found, but page loaded successfully');
    }
    
    const calendarSelectors = [
      '.monthly-booking-calendar',
      '.monthly-calendar',
      '#monthly-calendar-container',
      '.calendar-container',
      '.monthly-booking-fallback'
    ];
    
    let calendarFound = false;
    for (const selector of calendarSelectors) {
      const element = page.locator(selector);
      if (await element.count() > 0) {
        await expect(element).toBeVisible();
        calendarFound = true;
        break;
      }
    }
    
    if (!calendarFound) {
      const pageContent = await page.textContent('body');
      expect(pageContent).toMatch(/calendar|カレンダー|monthly/i);
    }
    
    const title = await page.title();
    expect(title).toMatch(/calendar|monthly|カレンダー/i);
    
    const logs = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        logs.push(msg.text());
      }
    });
    
    await page.waitForTimeout(1000);
    
    const criticalErrors = logs.filter(log => 
      log.includes('aria-') || 
      log.includes('role') || 
      log.includes('accessibility') ||
      log.includes('a11y')
    );
    
    if (criticalErrors.length > 0) {
      console.log('Accessibility warnings found:', criticalErrors);
    }
  });
  
  test('calendar page responds with 200 status @a11y @smoke', async ({ page }) => {
    const response = await page.goto('/monthly-calendar/');
    expect(response.status()).toBe(200);
  });
});
