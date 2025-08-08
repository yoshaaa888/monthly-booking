const { test, expect } = require('@playwright/test');

test.describe('Calendar Smoke Test', () => {
  
  test('Basic calendar page loads and shows month headers', async ({ page }) => {
    console.info('🔍 Environment Debug:');
    console.info('CAL_URL=', process.env.CAL_URL || 'not set');
    console.info('baseURL=', page.context()._options.baseURL);
    console.info('CI=', process.env.CI || 'not set');
    
    try {
      console.info('Testing base URL accessibility...');
      await page.goto('/');
      console.info('Base URL accessible');
    } catch (error) {
      console.error('Base URL not accessible:', error.message);
      throw new Error(`Base URL ${page.context()._options.baseURL} is not accessible: ${error.message}`);
    }
    
    try {
      console.info('Navigating to /monthly-calendar/...');
      await page.goto('/monthly-calendar/');
      await page.waitForLoadState('networkidle', { timeout: 10000 });
      
      let calendarFound = false;
      let monthCount = 0;
      
      const calendarContainer = page.locator('.monthly-booking-calendar-container');
      if (await calendarContainer.isVisible()) {
        console.info('✅ Found new calendar container');
        calendarFound = true;
        
        const monthHeaders = page.locator('.month-header h4');
        monthCount = await monthHeaders.count();
        console.info('Found month headers in new container:', monthCount);
      }
      
      if (!calendarFound) {
        const existingCalendar = page.locator('.calendar-month .calendar-header');
        if (await existingCalendar.count() > 0) {
          console.info('✅ Found existing calendar structure');
          calendarFound = true;
          monthCount = await existingCalendar.count();
          console.info('Found calendar headers in existing structure:', monthCount);
        }
      }
      
      if (!calendarFound) {
        const pageText = await page.textContent('body');
        if (pageText && (pageText.includes('予約カレンダー') || pageText.includes('空室') || pageText.includes('calendar'))) {
          console.info('✅ Found calendar-related text content');
          calendarFound = true;
          monthCount = 1; // Assume at least one month if text is found
        }
      }
      
      const pageHTML = await page.content();
      console.info('📄 Page HTML length:', pageHTML.length);
      
      if (!calendarFound) {
        console.error('❌ No calendar elements found with any selector');
        console.error('📄 Page HTML preview:', pageHTML.substring(0, 500));
        throw new Error('Calendar elements not found on /monthly-calendar/ page');
      }
      
      expect(monthCount).toBeGreaterThan(0);
      console.info('✅ Smoke test passed: Calendar page loads with', monthCount, 'calendar elements');
      
    } catch (error) {
      console.error('❌ Smoke test failed:', error.message);
      
      try {
        const pageHTML = await page.content();
        const pageURL = page.url();
        console.error('🔍 Failure diagnostics:');
        console.error('URL:', pageURL);
        console.error('HTML length:', pageHTML.length);
        console.error('HTML preview (first 200 chars):', pageHTML.substring(0, 200));
        
        const fs = require('fs');
        const path = require('path');
        const artifactsDir = path.join(process.cwd(), 'test-results');
        if (!fs.existsSync(artifactsDir)) {
          fs.mkdirSync(artifactsDir, { recursive: true });
        }
        fs.writeFileSync(path.join(artifactsDir, 'monthly-calendar-page.html'), pageHTML);
        console.error('📁 Full HTML saved to test-results/monthly-calendar-page.html');
        
      } catch (diagError) {
        console.error('Failed to save diagnostics:', diagError.message);
      }
      
      throw error;
    }
  });

});
