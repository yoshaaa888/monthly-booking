const { test as base } = require('@playwright/test');

const test = base.extend({
  testPages: async ({ page }, use) => {
    const testPages = {
      async createCalendarPage() {
        await page.goto('/wp-admin/post-new.php?post_type=page');
        await page.waitForSelector('#title');
        
        await page.fill('#title', 'Monthly Calendar Test');
        await page.fill('#post-name', 'monthly-calendar');
        
        await page.click('.wp-editor-tabs .switch-html');
        await page.fill('#content', '[monthly_booking_calendar]');
        
        await page.click('#publish');
        await page.waitForSelector('.notice-success');
      },

      async createSpecificRoomPage() {
        await page.goto('/wp-admin/post-new.php?post_type=page');
        await page.waitForSelector('#title');
        
        await page.fill('#title', 'Monthly Calendar Room 633');
        await page.fill('#post-name', 'monthly-calendar-room-633');
        
        await page.click('.wp-editor-tabs .switch-html');
        await page.fill('#content', '[monthly_booking_calendar room_id="633"]');
        
        await page.click('#publish');
        await page.waitForSelector('.notice-success');
      }
    };

    await use(testPages);
  }
});

module.exports = { test };
