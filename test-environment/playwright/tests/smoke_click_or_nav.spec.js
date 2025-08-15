const { test, expect } = require('@playwright/test');

test('Click calc -> detect AJAX or navigation', async ({ page }) => {
  page.on('console', m => console.log('[console]', m.type(), m.text()));

  // MU がなくても拾えるようクライアント側でも data-qa-calc を付与
  await page.addInitScript(() => {
    const tag = () => {
      const b = document.getElementById('calculate-estimate')
            || document.querySelector('button.estimate-button')
            || document.getElementById('calculate-estimate-btn');
      if (b && !b.getAttribute('data-qa-calc')) {
        try { b.setAttribute('data-qa-calc','1'); } catch(e){}
      }
      return !!(b && b.getAttribute('data-qa-calc'));
    };
    if (!tag()) {
      const obs = new MutationObserver(() => { if (tag()) { try { obs.disconnect(); } catch(_){} } });
      obs.observe(document.documentElement, { childList:true, subtree:true });
    }
  });

  await page.goto('http://127.0.0.1:8888/monthly-estimate/', { waitUntil: 'domcontentloaded' });
  await page.waitForLoadState('networkidle');

  // ===== フォーム入力 =====
  // room は <select> なので selectOption
  await page.selectOption('#room_id', { index: 1 });

  const today = new Date();
  const moveIn  = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 7);
  const moveOut = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 14);
  const fmt = d => d.toISOString().slice(0,10);
  await page.fill('#move_in_date', fmt(moveIn));
  await page.fill('#move_out_date', fmt(moveOut));

  // これも <select> 想定なので selectOption を利用
  if (await page.locator('#num_adults').count())   await page.selectOption('#num_adults', '1').catch(()=>{});
  if (await page.locator('#num_children').count()) await page.selectOption('#num_children', '0').catch(()=>{});

  // ===== ボタン取得（ID/クラス/データ属性のいずれでもOK）=====
  const calcBtn = page.locator('[data-qa-calc], #calculate-estimate, #calculate-estimate-btn, button.estimate-button').first();
  await calcBtn.waitFor({ state: 'visible', timeout: 15000 });

  // ===== レース：AJAX / ナビゲーション / DOM =====
  const ajaxWait = page.waitForResponse(r =>
    r.url().includes('/wp-admin/admin-ajax.php') && r.request().method() === 'POST',
    { timeout: 20000 }
  ).then(r => `AJAX:${r.status()}`).catch(() => null);

  const navWait = page.waitForNavigation({ timeout: 20000 })
    .then(() => 'NAV').catch(() => null);

  const domWait = page.locator('#estimate-result:visible, #estimate-total:visible')
    .first().waitFor({ timeout: 20000 }).then(() => 'DOM').catch(() => null);

  await calcBtn.click({ force: true });

  const winner = await Promise.race([ajaxWait, navWait, domWait]);
  console.log('RACE_WINNER:', winner || 'NONE');
});
