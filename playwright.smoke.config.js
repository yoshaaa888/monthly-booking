const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  // 対象2ファイルだけを確実に拾う
  testDir: 'test-environment/playwright/tests',
  testMatch: ['mu_smoke.spec.js', 'smoke_min.spec.js'],

  use: {
    baseURL: process.env.BASE_URL || 'http://127.0.0.1:8888',
    trace: 'on',
  },

  projects: [
    { name: 'chromium', use: { browserName: 'chromium' } },
  ],
});
