/* ci-trigger: noop at 2025-08-13T11:59Z to trigger E2E workflow */

/* ci-trigger: noop to retrigger E2E at 2025-08-13T11:48Z */

/* ci-trigger: noop commit to trigger workflow */
/* ci-trigger: noop at ${Date.now?.() || 'ts'} */

/* ci-trigger: noop */
/* trigger CI: noop comment */
const { expect } = require('@playwright/test');

async function waitAjax(page, action, timeout = 15000) {
  const response = await page.waitForResponse(res => {
    try {
      const url = new URL(res.url());
      if (!url.pathname.endsWith('/admin-ajax.php')) return false;
      const pd = res.request().postData() || '';
      return pd.includes(`action=${action}`) && res.status() === 200;
    } catch {
      return false;
    }
  }, { timeout });
  expect(response.status()).toBe(200);
  return response;
}

module.exports = { waitAjax };
exports.waitAjax = waitAjax;
/* ci-trigger: second green attempt 2025-08-13T12:03:27Z */
