const { defineConfig, devices } = require('@playwright/test');

const isCI = !!process.env.CI;
const baseURL = process.env.PW_BASE_URL || 'http://localhost:8888';

module.exports = defineConfig({
  testDir: './tests',
  fullyParallel: true,
  forbidOnly: isCI,
  retries: isCI ? 2 : 0,
  workers: isCI ? 2 : undefined,
  reporter: [
    ['list'],
    ['html', { outputFolder: 'playwright-report' }],
    ['json', { outputFile: 'test-results/results.json' }]
  ],
  use: {
    baseURL: baseURL,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },
    {
      name: 'Mobile Chrome',
      use: { ...devices['Pixel 5'] },
    },
    {
      name: 'Mobile Safari',
      use: { ...devices['iPhone 12'] },
    },
  ],
  ...(process.env.CI ? {} : {
    webServer: {
      command: 'echo "WordPress Local environment should be running at http://t-monthlycampaign.local"',
      url: 'http://t-monthlycampaign.local',
      reuseExistingServer: true,
      timeout: 120000,
    }
  }),
});
