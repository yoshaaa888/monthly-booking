const { test, expect } = require('@playwright/test');

test.describe('Calendar Smoke Test @smoke', () => {
  
  test('Basic calendar page loads and shows month headers @smoke', async ({ page }) => {
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
      
      const pageHTML = await page.content();
      if (pageHTML.toLowerCase().includes('wpdberror')) {
        console.error('❌ WordPress database error detected');
        const fs = require('fs');
        const path = require('path');
        const artifactsDir = path.join(process.cwd(), 'test-results');
        if (!fs.existsSync(artifactsDir)) {
          fs.mkdirSync(artifactsDir, { recursive: true });
        }
        fs.writeFileSync(path.join(artifactsDir, 'wpdberror-page.html'), pageHTML);
        throw new Error('WordPress database error detected on /monthly-calendar/ page');
      }
      
      let calendarFound = false;
      let monthCount = 0;
      
      await page.waitForLoadState('networkidle', { timeout: 15000 });
      
      const t1 = page.locator('.calendar-month .month-header');
      monthCount = await t1.count();
      if (monthCount > 0) {
        console.info('✅ Found month headers (.calendar-month .month-header):', monthCount);
        calendarFound = true;
      }

      if (!calendarFound) {
        const t2 = page.locator('.monthly-booking-calendar-container .calendar-month');
        monthCount = await t2.count();
        if (monthCount > 0) {
          console.info('✅ Found calendar months (container .calendar-month):', monthCount);
          calendarFound = true;
        }
      }

      if (!calendarFound) {
        const t3 = page.locator('.calendar-month');
        monthCount = await t3.count();
        if (monthCount > 0) {
          console.info('✅ Found calendar months (.calendar-month):', monthCount);
          calendarFound = true;
        }
      }

      if (!calendarFound) {
        const t4 = page.locator('.calendar-grid .calendar-day-header');
        const headerCount = await t4.count();
        if (headerCount >= 7) {
          console.info('✅ Found calendar grid day headers:', headerCount);
          calendarFound = true;
          monthCount = 1;
        }
      }
      
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
