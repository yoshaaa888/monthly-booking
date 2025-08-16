// @ts-check
const { test, expect } = require("@playwright/test");
const BASE = process.env.BASE_URL || "http://127.0.0.1:8888";

test("MU marker and REST ping", async ({ page, request }) => {
  const res = await request.get(BASE + "/wp-json/mb-qa/v1/ping");
  expect(res.ok()).toBeTruthy();
  const json = await res.json();
  expect(json.ok).toBe(true);
  await page.goto(BASE + "/");
  const html = await page.content();
  expect(html).toContain("MB_FIXER_ACTIVE");
});

test("admin-ajax responds", async ({ request }) => {
  const res = await request.post(BASE + "/wp-admin/admin-ajax.php", { form: { action: "mb_qa_echo" } });
  expect(res.ok()).toBeTruthy();
  const json = await res.json();
  expect(json.success).toBe(true);
});
