# UI/UX + Accessibility Testing Checklist

## Testing Environment
- **WordPress Version**: 6.5+
- **Plugin Version**: monthly-booking v1.6.0
- **Test URL**: http://localhost:8888/monthly-calendar/
- **Admin URL**: http://localhost:8888/wp-admin/
- **Sample Data**: 2 rooms loaded (E2Eデモ101, 東都マンスリー立川)

## 1. Display Testing (表示面)

### Layout Testing
- [ ] PC viewport (1280px+): Calendar grid alignment, header display
- [ ] Tablet viewport (768px): Responsive layout, touch targets
- [ ] Mobile viewport (375px): Mobile-optimized display, no horizontal scroll
- [ ] Calendar month headers display correctly
- [ ] Room selection dropdown renders properly
- [ ] Date cells align in proper grid format

### Visual Elements
- [ ] Font rendering consistency across devices
- [ ] Color contrast meets WCAG 2.1 AA standards
- [ ] Spacing and margins consistent
- [ ] Image loading (if any) with proper alt attributes
- [ ] Calendar legend/status indicators visible
- [ ] Tooltip display and positioning

### Cross-Browser Testing
- [ ] Chrome desktop/mobile behavior
- [ ] Firefox compatibility
- [ ] Safari compatibility (if available)

## 2. Operation Testing (操作面)

### Interactive Elements
- [ ] Room selection dropdown functionality
- [ ] Date cell clicking/selection behavior
- [ ] Calendar navigation (if present)
- [ ] Button hover states and feedback
- [ ] Touch interactions on mobile devices

### Form Validation
- [ ] Input field validation (if any)
- [ ] Error message display
- [ ] Required field handling
- [ ] AJAX request handling and error states

### Responsive Behavior
- [ ] Touch targets minimum 44px on mobile
- [ ] Swipe gestures (if implemented)
- [ ] Orientation change handling
- [ ] Viewport scaling behavior

## 3. Functionality Testing (機能面)

### Core Features
- [ ] Calendar data loading and display
- [ ] Room data retrieval and display
- [ ] Booking status indicators
- [ ] Campaign tooltip functionality
- [ ] Month transition handling

### Performance
- [ ] Initial page load time < 3 seconds
- [ ] AJAX response time < 2 seconds
- [ ] Calendar rendering performance
- [ ] Memory usage on mobile devices

### Navigation & Stability
- [ ] Browser back/forward button handling
- [ ] Page refresh behavior
- [ ] Deep linking functionality
- [ ] Error recovery mechanisms

## 4. Accessibility Testing (アクセシビリティ)

### ARIA Implementation
- [ ] role="grid" on calendar container
- [ ] role="row" on calendar rows
- [ ] role="gridcell" on date cells
- [ ] role="columnheader" on weekday headers
- [ ] aria-labelledby="calendar-title" on grid
- [ ] aria-describedby for tooltip relationships

### Keyboard Navigation
- [ ] Tab key enters/exits calendar grid
- [ ] Arrow keys navigate between dates
- [ ] Home/End keys jump to first/last date
- [ ] PageUp/PageDown navigate months
- [ ] Enter/Space activate date selection
- [ ] Escape dismisses tooltips
- [ ] Roving tabindex implementation (only one cell has tabindex="0")

### Screen Reader Support
- [ ] Live region announcements for month changes
- [ ] Proper date and status announcements
- [ ] Tooltip content accessible via aria-describedby
- [ ] Form labels and descriptions
- [ ] Error message announcements

### Visual Accessibility
- [ ] Color contrast ratios ≥ 4.5:1 for normal text
- [ ] Color contrast ratios ≥ 3:1 for large text
- [ ] Focus indicators visible and clear
- [ ] No information conveyed by color alone
- [ ] Text scaling up to 200% without horizontal scroll

### Motor Accessibility
- [ ] Click targets ≥ 44px on mobile
- [ ] Sufficient spacing between interactive elements
- [ ] No time-based interactions without alternatives
- [ ] Drag and drop alternatives (if applicable)

## 5. WordPress Admin Interface Testing

### Admin Pages
- [ ] Monthly Room Booking menu accessibility
- [ ] Property Master Management page functionality
- [ ] Booking Calendar admin page
- [ ] Campaign Settings page
- [ ] Fee Settings page
- [ ] Form submissions and data persistence
- [ ] Admin interface keyboard navigation

### Integration Testing
- [ ] Plugin activation/deactivation
- [ ] Database table creation and data integrity
- [ ] WordPress multisite compatibility (if applicable)
- [ ] Theme compatibility testing

## 6. Error Scenarios

### JavaScript Errors
- [ ] Console error monitoring
- [ ] AJAX failure handling
- [ ] Network connectivity issues
- [ ] Invalid data handling

### Edge Cases
- [ ] Empty room data scenarios
- [ ] Invalid date selections
- [ ] Concurrent user interactions
- [ ] Browser compatibility edge cases

## Testing Results Documentation

For each identified issue:
1. **Issue Title**: [UI/UX] or [a11y] prefix with clear description
2. **Severity**: critical, serious, minor
3. **Reproduction Steps**: Detailed step-by-step instructions
4. **Expected Behavior**: What should happen
5. **Actual Behavior**: What actually happens
6. **Environment**: Browser, device, viewport size
7. **Screenshots**: Visual evidence of the issue
8. **Code References**: Relevant file paths and line numbers

## Automated Testing Integration

### a11y-nightly Workflow
- [ ] Manual trigger via GitHub Actions UI
- [ ] Artifact download and analysis
- [ ] Critical/serious violation documentation
- [ ] Integration with manual testing results

### Test Coverage Verification
- [ ] All testing dimensions covered
- [ ] All identified issues documented
- [ ] Screenshots captured for visual issues
- [ ] Reproduction steps verified
