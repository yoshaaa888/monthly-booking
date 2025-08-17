// @ts-check
const { test, expect } = require("@playwright/test");
const BASE = process.env.BASE_URL || "http://127.0.0.1:8888";

test("REST ping responds ok (smoke)", async ({ request }) => {
  const res = await request.get(BASE + "/wp-json/mb-qa/v1/ping");
  expect(res.ok()).toBeTruthy();
  const json = await res.json();
  expect(json.ok).toBe(true);
});

test("admin-ajax responds (smoke-2)", async ({ request }) => {
  const res = await request.post(BASE + "/wp-admin/admin-ajax.php", {
    form: { action: "mb_qa_echo" },
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
  });
  expect(res.ok()).toBeTruthy();
  const json = await res.json();
  const ok =
    json?.ok === true ||
    json?.data?.ok === true ||
    false;
  const success =
    json?.success === true ||
    json?.data?.success === true ||
    false;
  expect(ok || success).toBeTruthy();
});
