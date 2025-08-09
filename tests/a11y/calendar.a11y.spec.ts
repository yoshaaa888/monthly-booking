import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test('Calendar has no serious/critical a11y issues @axe', async ({ page }) => {
  await page.goto('http://localhost:8888/monthly-calendar/');
  await page.waitForLoadState('networkidle');
  
  const results = await new AxeBuilder({ page }).analyze();
  const bad = results.violations.filter(v => ['critical','serious'].includes(v.impact || ''));
  expect(bad, bad.map(v => `${v.id}: ${v.help}`).join('\n')).toHaveLength(0);
});

test('Calendar accessibility comprehensive check @a11y', async ({ page }) => {
  await page.goto('http://localhost:8888/monthly-calendar/');
  await page.waitForLoadState('networkidle');
  
  const results = await new AxeBuilder({ page }).analyze();
  
  if (results.violations.length > 0) {
    console.log('Accessibility violations found:', results.violations.map(v => ({
      id: v.id,
      impact: v.impact,
      help: v.help,
      nodes: v.nodes.length
    })));
  }
  
  const criticalViolations = results.violations.filter(v => ['critical','serious'].includes(v.impact || ''));
  expect(criticalViolations).toHaveLength(0);
});
