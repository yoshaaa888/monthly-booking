# [a11y] Serious keyboard navigation failure - Arrow keys don't work in calendar grid

**Labels**: `accessibility`, `a11y`, `bug`, `serious`, `keyboard-navigation`

## Description
Despite ARIA grid structure being present, keyboard navigation within the calendar grid is completely non-functional. Arrow keys, Home/End keys, and PageUp/PageDown keys do not work for calendar navigation, making the interface inaccessible to keyboard users.

## Environment
- **Browser**: Chrome (mobile and desktop modes tested)
- **WordPress**: localhost:8888
- **Plugin Version**: monthly-booking v1.6.0
- **URL**: http://localhost:8888/monthly-calendar/
- **Test Date**: August 9, 2025

## WCAG 2.1 Criteria
- **Level**: AA
- **Success Criterion**: 2.1.1 Keyboard, 2.1.3 Keyboard (No Exception)
- **Guideline**: Operable
- **Impact**: Keyboard navigation completely broken

## Reproduction Steps
1. Navigate to http://localhost:8888/monthly-calendar/
2. Press Tab key to focus on calendar area
3. Try pressing Arrow keys (Up/Down/Left/Right) to navigate dates
4. Try pressing Home key to go to first date
5. Try pressing End key to go to last date
6. Try pressing PageUp/PageDown to change months
7. Observe that none of these keyboard interactions work

## Expected Behavior
According to ARIA grid pattern and WCAG guidelines:
- **Arrow Keys**: Navigate between date cells in grid
- **Home**: Move to first date in current month
- **End**: Move to last date in current month  
- **PageUp**: Navigate to previous month, maintain focus position
- **PageDown**: Navigate to next month, maintain focus position
- **Enter/Space**: Activate date selection
- **Escape**: Dismiss tooltips or exit interactions

## Actual Behavior
- Arrow keys do nothing when pressed in calendar
- Home/End keys do nothing
- PageUp/PageDown keys do nothing
- No keyboard navigation works within calendar grid
- Users must rely on mouse/touch interaction only

## Impact Assessment
- **User Groups Affected**: Keyboard users, screen reader users, motor-impaired users
- **Severity**: Serious - blocks keyboard accessibility completely
- **WCAG Compliance**: Fails WCAG 2.1 AA keyboard requirements
- **Workaround Available**: No keyboard alternative exists

## Technical Analysis
The calendar has proper ARIA structure but missing JavaScript implementation:

**Present (Good):**
- `role="grid"` on calendar container
- `role="gridcell"` on date cells
- `role="row"` on calendar rows
- `aria-labelledby` grid labeling

**Missing (Critical):**
- JavaScript event handlers for keyboard navigation
- Arrow key navigation logic
- Home/End key handling
- PageUp/PageDown month navigation
- Focus management and restoration

## Console Testing
```javascript
// Test keyboard event listeners
const grid = document.querySelector('[role="grid"]');
console.log('Grid element:', grid);
console.log('Event listeners:', getEventListeners(grid));
// Result: No keyboard event listeners found
```

## Code References
- **File**: `assets/calendar.js`
- **Missing**: Keyboard event handlers for grid navigation
- **File**: `includes/calendar-render.php`
- **Present**: ARIA structure exists but no keyboard support

## Related Issues
- Related to roving tabindex violation (all cells have tabindex="0")
- Connected to PR #14 implementation not working as expected
- May require JavaScript debugging and event handler implementation

## Screenshots
- Calendar ARIA structure: Shows proper roles but no keyboard functionality
- Console analysis: No keyboard event listeners detected

## Acceptance Criteria
- [ ] Arrow keys navigate between date cells in all directions
- [ ] Home key moves focus to first date in current month
- [ ] End key moves focus to last date in current month
- [ ] PageUp navigates to previous month with focus restoration
- [ ] PageDown navigates to next month with focus restoration
- [ ] Enter/Space keys activate date selection
- [ ] Escape key dismisses tooltips
- [ ] All keyboard navigation works smoothly without page scrolling
- [ ] Focus indicators are clearly visible during navigation

## Priority
**Serious** - This blocks keyboard accessibility and violates WCAG 2.1 requirements. Essential for accessibility compliance.

## Implementation Requirements
1. Add keyboard event listeners to calendar grid
2. Implement Arrow key navigation logic
3. Add Home/End key support for first/last date
4. Implement PageUp/PageDown month navigation
5. Ensure proper focus management and restoration
6. Prevent default browser scrolling behavior
7. Test with screen readers for proper announcements
