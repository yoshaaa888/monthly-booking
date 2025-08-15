const { test, expect } = require('@playwright/test');

test('Probe: click calculate and observe admin-ajax', async ({ page }) => {
  page.on('console', m => console.log('[console]', m.type(), m.text()));

  await page.goto('http://127.0.0.1:8888/monthly-estimate/', { waitUntil: 'domcontentloaded' });
  await page.waitForLoadState('networkidle');

  // room select を確認して選択
  const room = page.locator('#room_id');
  await expect(room).toBeVisible({ timeout: 15000 });
  const optCount = await room.locator('option').count();
  console.log('room option count:', optCount);
  await room.selectOption({ index: Math.min(1, optCount - 1) });

  // ざっくり必須ぽい項目を自動入力（型を見て無難な値を投入）
  const filled = await page.evaluate(() => {
    const f = document.querySelector('#monthly-estimate-form') || document.querySelector('form');
    if (!f) return 'NO_FORM';
    const today = new Date();
    const addDays = (d, n) => { const x = new Date(d); x.setDate(x.getDate() + n); return x; };
    const z = n => String(n).padStart(2, '0');
    const fmt = d => `${d.getFullYear()}-${z(d.getMonth()+1)}-${z(d.getDate())}`;
    const date1 = fmt(addDays(today, 35));
    const date2 = fmt(addDays(today, 95));

    const controls = Array.from(f.querySelectorAll('input, select, textarea'));
    const done = [];
    for (const el of controls) {
      if (el.disabled || el.readOnly) continue;
      if (!el.name && !el.id) continue;
      try {
        if (el.matches('select')) {
          const o = Array.from(el.options).find(o => o.value && !o.disabled && o.value !== '0');
          if (o) { el.value = o.value; el.dispatchEvent(new Event('change',{bubbles:true})); done.push(el.name||el.id); }
        } else if (el.type === 'date') {
          if (!el.value) { el.value = done.includes('date1') ? date2 : date1; el.dispatchEvent(new Event('input',{bubbles:true})); done.push(el.name||el.id); }
        } else if (el.type === 'number') {
          if (!el.value) { el.value = 1; el.dispatchEvent(new Event('input',{bubbles:true})); done.push(el.name||el.id); }
        } else if (el.type === 'checkbox' || el.type === 'radio') {
          if (el.required && !el.checked) { el.checked = true; el.dispatchEvent(new Event('change',{bubbles:true})); done.push(el.name||el.id); }
        } else if (el.type !== 'hidden') {
          if (!el.value) { el.value = 'test'; el.dispatchEvent(new Event('input',{bubbles:true})); done.push(el.name||el.id); }
        }
      } catch {}
    }
    return done;
  });
  console.log('filled fields:', Array.isArray(filled) ? filled.slice(0,20) : filled);

  // 計算ボタン候補を探す（idが無ければフォーム内の最初のボタン）
  const calc = page.locator('#calculate-estimate-btn');
  let target = calc;
  if (!(await calc.count())) {
    const formBtn = page.locator('form button:visible, form input[type=submit]:visible, form input[type=button]:visible').first();
    if (await formBtn.count()) target = formBtn;
  }
  const hasTarget = await target.count();
  console.log('calc target found:', hasTarget);

  // admin-ajax POST を待つ（最大15s）
  const ajaxPromise = page.waitForResponse(
    r => r.url().includes('/wp-admin/admin-ajax.php') && r.request().method() === 'POST',
    { timeout: 15000 }
  ).catch(() => null);

  if (hasTarget) {
    await target.click({ force: true });
  } else {
    console.log('No button found to click.');
  }

  const ajaxResp = await ajaxPromise;
  if (ajaxResp) {
    const status = ajaxResp.status();
    let body = '';
    try { body = await ajaxResp.text(); } catch {}
    console.log('AJAX_STATUS:', status);
    console.log('AJAX_BODY_HEAD:', (body || '').slice(0, 300));
  } else {
    console.log('NO_ADMIN_AJAX_POST_DETECTED');
  }

  await page.screenshot({ path: 'test-results/probe.png', fullPage: true }).catch(() => {});
});
