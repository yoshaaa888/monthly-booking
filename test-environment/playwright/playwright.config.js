const { defineConfig, devices } = require('@playwright/test');
module.exports = defineConfig({
  use: { baseURL: 'http://127.0.0.1:8888', trace: 'retain-on-failure' },,
  testIgnore: ['**/safe/**']
  projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
});
