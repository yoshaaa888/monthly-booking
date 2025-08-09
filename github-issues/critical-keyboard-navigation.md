# [a11y] Critical keyboard navigation failure - roving tabindex not implemented

**Labels**: `accessibility`, `a11y`, `bug`, `critical`

## Description
The calendar grid has a critical accessibility violation where all date cells have `tabindex="0"` instead of proper roving tabindex implementation. This prevents keyboard navigation from working correctly and violates WCAG 2.1 guidelines.

## Environment
- **Browser**: Chrome (mobile mode)
- **Device**: Mobile viewport (375px width)
- **WordPress**: localhost:8888
- **Plugin Version**: monthly-booking v1.6.0
- **URL**: http://localhost:8888/monthly-calendar/

## WCAG 2.1 Criteria
- **Level**: AA
- **Success Criterion**: 2.1.1 Keyboard, 2.4.3 Focus Order
- **Guideline**: Operable

## Reproduction Steps
1. Navigate to http://localhost:8888/monthly-calendar/
2. Press Tab key to enter calendar
3. Try using Arrow keys to navigate between dates
4. Observe that focus doesn't move properly within calendar grid
5. Inspect HTML - all date cells have `tabindex="0"`

## Expected Behavior
- Only one date cell should have `tabindex="0"` at a time (roving tabindex)
- Arrow keys should move focus between date cells
- Tab key should enter/exit the calendar grid
- Focus should be clearly visible on current date cell

## Actual Behavior
- All 224 date cells have `tabindex="0"` simultaneously
- Arrow key navigation doesn't work properly
- Focus management is broken
- Keyboard users cannot navigate the calendar effectively

## Impact Assessment
- **User Groups Affected**: Keyboard users, screen reader users
- **Severity**: Critical - completely blocks keyboard accessibility
- **Workaround Available**: No

## Technical Details
- **ARIA Implementation**: Calendar has `role="grid"` but improper tabindex
- **HTML Structure**: All div elements with `role="gridcell"` have `tabindex="0"`
- **JavaScript**: Roving tabindex logic not implemented
- **Focus Management**: No proper focus control

## Screenshots
Calendar structure screenshot: /home/ubuntu/screenshots/localhost_8888_062435.png

## Code References
- **File**: includes/calendar-render.php
- **Issue**: All date cells rendered with `tabindex="0"`
- **File**: assets/calendar.js
- **Issue**: Missing roving tabindex implementation

## Automated Testing Results
- **Console Analysis**: 224 gridcell elements found, all with tabindex="0"
- **Expected**: Only 1 element should have tabindex="0" at a time

## Acceptance Criteria
- [ ] Only one date cell has `tabindex="0"` at any time
- [ ] Arrow keys navigate between date cells properly
- [ ] Tab/Shift+Tab enters/exits calendar grid
- [ ] Focus is clearly visible on current cell
- [ ] Roving tabindex updates as user navigates
- [ ] WCAG 2.1 AA compliance achieved
