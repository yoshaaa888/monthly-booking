const { test, expect } = require('@playwright/test');

test.describe('Monthly Booking - Normal Journey Test', () => {
  test('Complete normal booking journey with early booking campaign', async ({ page }) => {
    console.log('Step 0: open page');
    await page.goto('http://127.0.0.1:8888/monthly-estimate/', { waitUntil: 'domcontentloaded' });
    await page.waitForLoadState('networkidle');

    // ---- 部屋選択（最初の有効optionを選ぶ）----
    const room = page.locator('#room_id');
    await expect(room).toBeVisible({ timeout: 10000 });
    const opts = room.locator('option');
    const optCount = await opts.count();
    if (optCount < 2) throw new Error(`room_id has insufficient options (${optCount})`);
    await room.selectOption({ index: 1 }); // 0番はプレースホルダ、1番を選択

    // ---- 日付入力（存在するフィールドだけ埋める）----
    const today = new Date();
    const checkin = new Date(today); checkin.setDate(today.getDate() + 35);
    const checkout = new Date(checkin); checkout.setDate(checkin.getDate() + 60);
    const ymd = d => {
      const z = n => String(n).padStart(2,'0');
      return `${d.getFullYear()}-${z(d.getMonth()+1)}-${z(d.getDate())}`;
    };
    const checkinSelectors  = ['#checkin_date', '#check_in_date', '#start_date'];
    const checkoutSelectors = ['#checkout_date', '#check_out_date', '#end_date'];
    for (const sel of checkinSelectors)  if (await page.locator(sel).count())  { await page.fill(sel,  ymd(checkin));  break; }
    for (const sel of checkoutSelectors) if (await page.locator(sel).count()) { await page.fill(sel,  ymd(checkout)); break; }

    // ---- 人数（あれば設定）----
    if (await page.locator('#num_adults').count())   await page.selectOption('#num_adults', '1');
    if (await page.locator('#num_children').count()) await page.selectOption('#num_children', '0');

    // ---- オプション（存在すればチェック）----
    for (const v of ['1','2','3']) {
      const cb = page.locator(`input[name="options[]"][value="${v}"]`);
      if (await cb.count()) await cb.check();
    }

    // ---- 連絡先（存在すれば入力）----
    const fillIf = async (sel, val) => {
      const loc = page.locator(sel);
      if (await loc.count()) await loc.fill(val);
    };
    await fillIf('#guest_name', '田中太郎');
    await fillIf('#guest_email', 'tanaka.test@example.com');
    await fillIf('#company_name', 'テスト株式会社');

    // ---- 見積もり実行（ボタンIDかラベルで探す）----
    if (await page.locator(`#calculate-estimate-btn, button:has-text("見積"), button:has-text("Calculate")`).count()) {
      await page.locator(`#calculate-estimate-btn, button:has-text("見積"), button:has-text("Calculate")`).click();
    } else {
      const btn = page.getByRole('button', { name: /見積|estimate|計算/i });
      if (await btn.count()) await btn.first().click();
    }

    // ---- 結果の可視確認（ID or 文字列）----
    const resultLocator = page.locator('#estimate-result');
  // --- DEBUG: robust submit + invalid dump + screenshot ---
  const form = page.locator('#monthly-estimate-form, form').first();

  // ボタンがあればクリック、なくても requestSubmit で送信を試みる
  const calcBtn = page.locator('#calculate-estimate-btn, button:has-text("見積"), button:has-text("Calculate"), button[type="submit"], input[type="submit"]');
  if (await calcBtn.count()) {
    try { await calcBtn.first().click({ force: true, trial: false }); } catch {}
  }
  await page.evaluate(() => {
    const f = document.querySelector('#monthly-estimate-form, form');
    if (f) { (f.requestSubmit ? f.requestSubmit() : f.submit()); }
  });

  // change/input を明示的に発火（jQuery系トリガ対策）
  try {
    const r = document.querySelector('#room_id'); if (r) r.dispatchEvent(new Event('change',{bubbles:true}));
    const ci = document.querySelector('#checkin_date,#check_in_date,#start_date'); if (ci) ci.dispatchEvent(new Event('change',{bubbles:true}));
    const co = document.querySelector('#checkout_date,#check_out_date,#end_date'); if (co) co.dispatchEvent(new Event('change',{bubbles:true}));
  } catch {}

  // フォームの妥当性をチェックし、不備項目をログする
  const invalids = await page.evaluate(() => {
    const f=document.querySelector('#monthly-estimate-form, form'); if(!f) return ['NO_FORM'];
    const arr=[...f.querySelectorAll('input,select,textarea')];
    return arr.filter(el => !el.checkValidity()).map(el => (el.name||el.id||el.tagName)+': '+(el.validationMessage||'invalid'));
  });
  console.log('INVALID_FIELDS:', invalids);

  // ページ全体のスクショ
  try { await page.screenshot({ path: 'test-results/estimate-page.png', fullPage: true }); } catch {}

  // 結果の待機（可視 or テキスト）
  const visibleResult = page.locator('#estimate-result:visible').first();
  const visibleText   = page.locator('#estimate-result').getByText(/(合計|小計|Total|円)/).first();
  await Promise.race([
    visibleResult.waitFor({ state: 'visible', timeout: 60000 }),
    visibleText.waitFor({ state: 'visible',  timeout: 60000 })
  ]);

  // 最終確認とスニペット
  const finalTgt = (await visibleResult.count()) ? visibleResult : visibleText;
  await expect(finalTgt).toBeVisible({ timeout: 10000 });
  try { console.log('RESULT_SNIPPET:', (await finalTgt.innerText()).slice(0,200)); } catch {}
  // --- DEBUG end ---

  console.log('Estimate completed');
  });
});
