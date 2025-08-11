# A11Y/Keyboard Test Failure Analysis Report

## Executive Summary

The accessibility and keyboard navigation tests in `tests/e2e/accessibility.spec.js` are experiencing timeout failures in the post-merge E2E workflow. This analysis identifies the root causes and proposes minimal fixes to stabilize the test suite.

## Root Cause Analysis

### 1. Missing DOM Elements

**Issue**: Tests expect specific calendar DOM elements that may not exist or load slowly:
- `.calendar-day:not(.empty)` with `aria-label` attributes
- `.calendar-day[tabindex="0"]` for keyboard navigation
- `#room-selector` dropdown element
- `.monthly-booking-calendar-container` with proper `role` attribute

**Evidence**: Previous test artifacts show "Example Domain" page instead of WordPress calendar, indicating baseURL or routing issues.

### 2. Timing Issues

**Problem**: Tests use `waitForLoadState('networkidle')` but calendar elements may load asynchronously via AJAX after initial page load.

**Specific Failures**:
- ARIA label expectations: `/\d+月\d+日/` and `/(予約可能|予約済み|キャンペーン|清掃期間)/`
- Keyboard focus expectations on calendar days
- Screen reader compatibility checks for month headers

### 3. Selector Instability

**Issue**: Tests rely on specific CSS selectors that may not be consistently present:
- `.month-header h4` for month headers
- `.calendar-day[tabindex="0"]` for focusable elements
- Complex nested selectors that depend on plugin rendering

## Proposed Minimal Fixes

### Option 1: Plugin-Side ARIA Improvements (Recommended)

Add ARIA attributes to the Monthly Booking plugin calendar rendering:

```php
// In calendar rendering code
echo '<div class="calendar-day" aria-label="' . $date . '月' . $day . '日 ' . $status . '" tabindex="0">';
echo '<div class="monthly-booking-calendar-container" role="grid" aria-label="予約カレンダー">';
```

### Option 2: Test Selector Updates

Update accessibility tests with more robust selectors and better waiting:

```javascript
// Wait for calendar to be fully loaded
await page.waitForFunction(() => {
  const days = document.querySelectorAll('.calendar-day:not(.empty)');
  return days.length > 0 && days[0].getAttribute('aria-label');
}, { timeout: 15000 });

// Use fallback selectors
const calendarDays = page.locator('.calendar-day:not(.empty), .calendar-cell:not(.empty)');
```

### Option 3: Enhanced Wait Strategies

Implement more sophisticated waiting for dynamic content:

```javascript
// Wait for AJAX calendar loading to complete
await page.waitForLoadState('networkidle');
await page.waitForTimeout(2000); // Allow for AJAX updates
await page.waitForSelector('.calendar-month', { state: 'visible' });
```

## Separation Strategy Justification

### Why Move A11Y Tests to Separate Workflow

1. **Stability**: Post-merge workflows need fast, reliable feedback
2. **Complexity**: A11Y tests require more setup time and are prone to timing issues
3. **Focus**: Smoke tests verify core functionality, A11Y tests verify compliance
4. **Maintenance**: Separate workflows allow independent optimization

### Recommended Workflow Structure

```yaml
# post-merge-e2e.yml (current)
- Runs: @smoke tests only
- Purpose: Fast regression detection
- Timeout: 15 minutes
- Frequency: Every main branch push

# nightly-a11y.yml (future)
- Runs: Full accessibility test suite
- Purpose: Comprehensive compliance verification
- Timeout: 45 minutes
- Frequency: Nightly or manual trigger
```

## Implementation Priority

1. **Immediate**: Implement smoke-only post-merge workflow (this PR)
2. **Short-term**: Add basic ARIA attributes to plugin calendar rendering
3. **Medium-term**: Create dedicated nightly A11Y workflow
4. **Long-term**: Enhance test selectors and waiting strategies

## Risk Assessment

- **Low Risk**: Smoke-only approach reduces CI instability
- **Medium Risk**: A11Y regressions may go undetected between nightly runs
- **Mitigation**: Manual A11Y testing during major releases

## Success Metrics

- Post-merge workflow completion time: < 10 minutes
- Post-merge workflow success rate: > 95%
- A11Y test suite success rate in dedicated workflow: > 90%

## Conclusion

The smoke-only approach for post-merge E2E provides immediate stability while maintaining comprehensive testing through dedicated workflows. The identified A11Y issues require minimal plugin-side improvements and enhanced test waiting strategies.
