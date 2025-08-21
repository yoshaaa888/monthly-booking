import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './specs',
  timeout: 90_000,
  expect: { timeout: 10_000 },
  retries: 1,
  fullyParallel: false,
  workers: process.env.CI ? 1 : undefined,
  use: {
    baseURL: process.env.MB_BASE_URL || 'http://127.0.0.1:8888',
    headless: true,
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    trace: 'retain-on-failure'
  },
  reporter: [
    ['list'],
    ['html', { outputFolder: 'playwright-report', open: 'never' }]
  ],
  projects: [
    { name: 'firefox', use: { browserName: 'firefox' } }
  ]
});
