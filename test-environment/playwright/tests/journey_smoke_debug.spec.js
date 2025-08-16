const { test, expect } = require('@playwright/test');

test.describe('Monthly Booking - Smoke Debug', () => {
  test('autofill + click calculate (AJAX) + wait result', async ({ page }) => {
    page.on('console', msg => console.log('[console]', msg.type(), msg.text()));

    console.log('>> open');
    await page.goto('http://127.0.0.1:8888/monthly-estimate/', { waitUntil: 'domcontentloaded' });
    await page.waitForSelector(`#calculate-estimate-btn, button:has-text("見積"), button:has-text("Calculate")`, { timeout: 20000, state: "attached" });
    await page.locator(`#calculate-estimate-btn, button:has-text("見積"), button:has-text("Calculate")`).first().waitFor({ state: "visible", timeout: 5000 }).catch(()=>{});
    await page.waitForLoadState('networkidle');

    // 部屋：index=1 を選択（0はプレースホルダ）
    const room = page.locator('#room_id');
    await expect(room).toBeVisible({ timeout: 10000 });
    const count = await room.locator('option').count();
    console.log('room options:', count);
    if (count < 2) throw new Error(`room options too few: ${count}`);
    await room.selectOption({ index: 1 });

    // 日付（存在するフィールドへ）
    const today = new Date();
    const ci = new Date(today); ci.setDate(today.getDate() + 35);
    const co = new Date(ci);    co.setDate(ci.getDate() + 60);
    const y2 = n => String(n).padStart(2,'0');
    const ymd = d => `${d.getFullYear()}-${y2(d.getMonth()+1)}-${y2(d.getDate())}`;

    for (const sel of ['#checkin_date','#check_in_date','#start_date']) {
      if (await page.locator(sel).count()) { await page.fill(sel, ymd(ci)); break; }
    }
    for (const sel of ['#checkout_date','#check_out_date','#end_date']) {
      if (await page.locator(sel).count()) { await page.fill(sel, ymd(co)); break; }
    }

    // 任意の人数系
    if (await page.locator('#num_adults').count())   await page.selectOption('#num_adults','1');
    if (await page.locator('#num_children').count()) await page.selectOption('#num_children','0');

    // 備品などは存在すれば軽く1つ
    if (await page.locator('input[name="options[]"][value="1"]').count()) {
      await page.check('input[name="options[]"][value="1"]');
    }

    // --- ここ重要：submitではなく「計算ボタン」を押す ---
    const calcBtn = page.locator(`#calculate-estimate-btn, button:has-text("見積"), button:has-text("Calculate")`);
    await expect(calcBtn).toBeVisible({ timeout: 10000 });
    const ajaxWait = page.waitForResponse(resp =>
      resp.url().includes('/wp-admin/admin-ajax.php') &&
      resp.request().method() === 'POST'
    , { timeout: 20000 });
    await calcBtn.click();

    // AJAX応答を確認
    const ajaxResp = await ajaxWait;
    console.log('ajax status:', ajaxResp.status(), 'url:', ajaxResp.url());
    const ctype = ajaxResp.headers()['content-type'] || '';
    let bodyText = '';
    try {
      if (ctype.includes('application/json')) {
        const j = await ajaxResp.json();
        console.log('ajax json keys:', Object.keys(j||{}));
        bodyText = JSON.stringify(j).slice(0, 200);
      } else {
        bodyText = (await ajaxResp.text()).slice(0, 200);
      }
    } catch(e) { console.log('ajax parse error:', String(e)); }

    console.log('ajax body snippet:', bodyText);

    // 結果の可視化待ち
    const visibleResult = page.locator('#estimate-result:visible, .estimate-result:visible');
    await expect(visibleResult.first()).toBeVisible({ timeout: 20000 });

    const snippet = await visibleResult.first().innerText();
    console.log('RESULT_SNIPPET:', snippet.slice(0, 200));

    // スクショ保存
    await page.screenshot({ path: 'test-results/estimate-page.png', fullPage: true });
  });
});
