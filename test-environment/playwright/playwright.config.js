const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
  testDir: 'test-environment/playwright/tests',
  testIgnore: ['**/safe/**'],
  reporter: [
    ['list'],
    ['html', { outputFolder: 'test-environment/playwright/playwright-report', open: 'never' }],
  ],
  preserveOutput: 'always',
  use: {
    baseURL: process.env.BASE_URL || 'http://127.0.0.1:8888',
    trace: 'retain-on-failure',
    headless: true,
  },
  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
  ],
});
