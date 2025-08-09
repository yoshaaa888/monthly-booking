# [a11y] Critical roving tabindex violation - all 180 date cells have tabindex="0"

**Labels**: `accessibility`, `a11y`, `bug`, `critical`, `keyboard-navigation`

## Description
The calendar grid has a critical accessibility violation where all 180 date cells have `tabindex="0"` simultaneously instead of proper roving tabindex implementation. This completely breaks keyboard navigation and violates WCAG 2.1 guidelines, making the calendar inaccessible to keyboard and screen reader users.

## Environment
- **Browser**: Chrome (mobile and desktop modes tested)
- **WordPress**: localhost:8888
- **Plugin Version**: monthly-booking v1.6.0
- **URL**: http://localhost:8888/monthly-calendar/
- **Test Date**: August 9, 2025
- **Viewport**: Tested on 375px mobile and 1280px desktop

## WCAG 2.1 Criteria
- **Level**: AA
- **Success Criterion**: 2.1.1 Keyboard, 2.4.3 Focus Order, 2.4.7 Focus Visible
- **Guideline**: Operable
- **Impact**: Complete keyboard accessibility failure

## Reproduction Steps
1. Navigate to http://localhost:8888/monthly-calendar/
2. Open browser developer tools (F12)
3. Run in console: `document.querySelectorAll('[tabindex="0"]').length`
4. Observe result shows 180 elements
5. Press Tab key to enter calendar
6. Try using Arrow keys to navigate between dates
7. Observe that focus doesn't move properly within calendar grid

## Expected Behavior (Roving Tabindex)
- Only ONE date cell should have `tabindex="0"` at any given time
- All other date cells should have `tabindex="-1"`
- Arrow keys should move focus between date cells
- Tab key should enter/exit the calendar grid
- Focus should be clearly visible on current date cell
- As user navigates, tabindex="0" should move to the focused cell

## Actual Behavior
- ALL 180 date cells have `tabindex="0"` simultaneously
- Arrow key navigation doesn't work properly
- Focus management is completely broken
- Keyboard users cannot navigate the calendar effectively
- Screen readers receive confusing focus information

## Impact Assessment
- **User Groups Affected**: Keyboard users, screen reader users, motor-impaired users
- **Severity**: Critical - completely blocks keyboard accessibility
- **WCAG Compliance**: Fails WCAG 2.1 AA requirements
- **Workaround Available**: No accessible alternative

## Technical Analysis
```javascript
// Current implementation (WRONG):
// All cells have tabindex="0"
<div role="gridcell" tabindex="0" aria-label="...">

// Correct implementation should be:
// Only one cell has tabindex="0", others have tabindex="-1"
<div role="gridcell" tabindex="0" aria-label="...">  // Current focus
<div role="gridcell" tabindex="-1" aria-label="..."> // Other cells
<div role="gridcell" tabindex="-1" aria-label="..."> // Other cells
```

## Console Analysis Results
```javascript
// Found 180 date cells with campaign labels
// Found 180 cells with tabindex="0" ← CRITICAL VIOLATION
// Grid element with role="grid": [object HTMLDivElement] ← Present
// Found 39 elements with role="row" ← Present
// Found 224 elements with role="gridcell" ← Present
```

## ARIA Implementation Status
- ✅ `role="grid"` on calendar container
- ✅ `role="row"` on calendar rows  
- ✅ `role="gridcell"` on date cells
- ✅ `role="columnheader"` on weekday headers
- ✅ `aria-labelledby="calendar-title"` grid labeling
- ❌ **Roving tabindex implementation BROKEN**

## Screenshots
- Calendar structure: Available upon request
- Console analysis: Shows 180 cells with tabindex="0"
- Mobile viewport: Same violation present

## Code References
- **File**: `includes/calendar-render.php`
- **Issue**: All date cells rendered with `tabindex="0"`
- **File**: `assets/calendar.js`
- **Issue**: Missing roving tabindex management logic

## Related PRs
This issue persists despite PRs #13-16 being merged:
- PR #13: ARIA grid roles (merged)
- PR #14: Keyboard navigation (merged) ← **Implementation not working**
- PR #15: Live regions (merged)
- PR #16: Tooltip management (merged)

## Acceptance Criteria
- [ ] Only ONE date cell has `tabindex="0"` at any time
- [ ] All other date cells have `tabindex="-1"`
- [ ] Arrow keys navigate between date cells properly
- [ ] Tab/Shift+Tab enters/exits calendar grid correctly
- [ ] Focus is clearly visible on current cell
- [ ] Roving tabindex updates as user navigates with Arrow keys
- [ ] Home/End keys work for first/last date navigation
- [ ] PageUp/PageDown work for month navigation with focus restoration
- [ ] WCAG 2.1 AA compliance achieved for keyboard navigation

## Priority
**Critical** - This is a complete accessibility failure that blocks keyboard users from using the calendar. Must be fixed immediately for WCAG compliance.

## Implementation Notes
The roving tabindex pattern requires:
1. Initialize with only one cell having `tabindex="0"`
2. JavaScript event handlers for Arrow/Home/End/PageUp/PageDown keys
3. Dynamic tabindex management as focus moves
4. Proper focus restoration after month changes
