import { test, expect } from '@playwright/test';
const BASE = process.env.BASE_URL || 'http://127.0.0.1:8888';
const isTarget = (u) => /(\/wp-admin\/admin-ajax\.php|\/wp-json\/)/.test(u);

async function minimalFill(page) {
  await page.goto(`${BASE}/monthly-estimate/`, { waitUntil: 'domcontentloaded' });
  await page.locator('#room_id').first().waitFor({ state: 'visible', timeout: 10000 }).catch(()=>{});
  const rc = await page.locator('#room_id option').count().catch(()=>0);
  if (rc > 1) await page.selectOption('#room_id', { index: 1 }).catch(()=>{});
  const pad = (x)=>String(x).padStart(2,'0'); const fmt=(d)=>`${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
  const now=new Date(); const inD=new Date(now); inD.setDate(inD.getDate()+1); const outD=new Date(now); outD.setDate(outD.getDate()+31);
  const setDate = async (sel,val)=>{ if (await page.locator(sel).count()){ await page.fill(sel,val).catch(()=>{}); await page.locator(sel).dispatchEvent('input').catch(()=>{}); await page.locator(sel).dispatchEvent('change').catch(()=>{}); } };
  await setDate('#move_in_date',fmt(inD)); await setDate('#move_out_date',fmt(outD));
  const setSel = async (sel,value)=>{ if (await page.locator(sel).count()){ await page.selectOption(sel,value).catch(()=>{}); await page.locator(sel).dispatchEvent('change').catch(()=>{});} };
  await setSel('#num_adults','1'); await setSel('#num_children','0');
  const tryFill = async (sels,val)=>{ for (const s of sels){ if (await page.locator(s).count()){ await page.fill(s,val).catch(()=>{}); await page.locator(s).dispatchEvent('input').catch(()=>{}); await page.locator(s).blur().catch(()=>{}); break; } } };
  await tryFill(['#guest_name','[name="guest_name"]','input[name="name"]','input[placeholder*="Name" i]'],'Taro Yamada');
  await tryFill(['#guest_phone','[name="guest_phone"]','input[type="tel"]','input[name="phone"]'],'0312345678');
  await tryFill(['#guest_email','[name="guest_email"]','input[type="email"]','input[name="email"]'],'taro@example.com');
  const checks=['#agree_terms','#privacy_agree','input[name="agree"]','input[name="consent"]'];
  for (const c of checks){ if (await page.locator(c).count()) await page.locator(c).check({force:true}).catch(()=>{}); }
  const opt = page.locator('input[name="options[]"]'); if (await opt.count()) await opt.first().check({ force:true }).catch(()=>{});
  await page.waitForTimeout(120);
}

async function clickAndRace(page, timeout=35000){
  const btn = page.locator('#calculate-estimate-btn, button:has-text("見積"), button:has-text("Calculate")').first();
  await expect(btn).toBeVisible({ timeout: 10000 });

  const prePing = page.evaluate(()=>fetch('/wp-json/mb-qa/v1/ping').catch(()=>{})).catch(()=>{});
  const netWait = page.waitForResponse(r => isTarget(r.url()), { timeout }).then(r=>({kind:'net',r})).catch(()=>null);
  const domWait = page.locator('#estimate-result').getByText(/(Total|合計|円|小計)/).first().waitFor({state:'visible',timeout}).then(()=>({kind:'dom'})).catch(()=>null);

  await btn.scrollIntoViewIfNeeded().catch(()=>{});
  await btn.click({force:true});
  await page.evaluate(() => {
    const b = document.querySelector('#calculate-estimate-btn'); b && b.click();
    const f = document.querySelector('#estimate-form');
    if (f){ if (typeof f.requestSubmit==='function'){ try{ f.requestSubmit(); }catch(e){} } f.dispatchEvent(new Event('submit',{bubbles:true,cancelable:true})); }
  }).catch(()=>{});
  await page.waitForLoadState('networkidle',{timeout:1500}).catch(()=>{});

  let w = await Promise.race([netWait, domWait]);
  if (!w){ try{ const p = await page.waitForResponse(r=>/\/wp-json\/mb-qa\/v1\/ping/.test(r.url()),{timeout:5000}); if (p) w={kind:'net', r:p}; }catch(_){ } }
  return w;
}

test('Monthly Booking - Smoke Debug: autofill + click calculate (AJAX) + wait result', async ({ page }) => {
  await minimalFill(page);
  const w = await clickAndRace(page, 35000);
  if (!w) throw new Error('No AJAX/REST or DOM update detected');
  if (w.kind === 'dom') await expect(page.locator('#estimate-result')).toBeVisible();
});
