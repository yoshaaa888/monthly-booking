# Monthly Booking Calendar Testing Procedures

## Overview
This document outlines comprehensive testing procedures for the enhanced 6-month calendar feature implementation.

## Test Environment Setup

### Prerequisites
- WordPress Local environment: http://t-monthlycampaign.local/
- Monthly Booking Plugin v2.2-final activated
- Test data loaded in database tables
- Browser developer tools available

### Database Verification
```sql
-- Verify room data exists
SELECT COUNT(*) FROM wp_monthly_rooms WHERE is_active = 1;

-- Verify booking data exists
SELECT COUNT(*) FROM wp_monthly_bookings WHERE status != 'cancelled';

-- Verify campaign data exists
SELECT COUNT(*) FROM wp_monthly_campaigns WHERE is_active = 1;
```

## Test Cases

### TC001: Basic Calendar Display
**Objective**: Verify calendar renders with 6-month view

**Steps**:
1. Create WordPress page with `[monthly_booking_calendar]` shortcode
2. View page in browser
3. Verify 6 consecutive months are displayed
4. Check Japanese month formatting (年月)

**Expected Results**:
- 6 months displayed consecutively
- Each month shows proper grid layout
- Japanese date formatting correct
- No PHP errors in debug log

### TC002: Room Selection Dropdown
**Objective**: Test room selection functionality

**Steps**:
1. Use shortcode without room_id: `[monthly_booking_calendar]`
2. Verify dropdown appears with room options
3. Select different rooms from dropdown
4. Verify calendar updates via AJAX

**Expected Results**:
- Dropdown populated with active rooms
- Room names display correctly
- AJAX loading indicator appears
- Calendar updates without page refresh

### TC003: Specific Room Display
**Objective**: Test room_id attribute functionality

**Steps**:
1. Use shortcode with room_id: `[monthly_booking_calendar room_id="633"]`
2. Verify no dropdown appears
3. Verify calendar shows data for specified room only

**Expected Results**:
- No room selection dropdown
- Calendar displays specified room data
- Room-specific bookings and campaigns shown

### TC004: Color-Coded Availability
**Objective**: Verify availability status display

**Test Data Setup**:
- Room with existing bookings
- Room with active campaigns
- Room with overlapping booking/campaign dates

**Steps**:
1. View calendar for room with test data
2. Verify color coding:
   - Green (〇): Available dates
   - Red (×): Booked dates
   - Orange (△): Campaign dates

**Expected Results**:
- Correct symbols and colors displayed
- Priority order: booked > campaign > available
- Legend matches actual display

### TC005: Cleaning Buffer Logic
**Objective**: Test 5-day cleaning buffer implementation

**Test Data**:
- Booking: 2025-08-15 to 2025-08-20
- Expected buffer: 2025-08-10 to 2025-08-25

**Steps**:
1. Create booking with known dates
2. View calendar
3. Verify 5 days before/after booking show as unavailable (×)

**Expected Results**:
- Buffer dates display as booked (red/×)
- Buffer calculation accurate
- No overlap conflicts with available dates

### TC006: Campaign Tooltips
**Objective**: Test campaign tooltip functionality

**Steps**:
1. View calendar with campaign dates
2. Hover over campaign dates (△)
3. Verify tooltip appears with campaign name
4. Test on mobile (touch interaction)

**Expected Results**:
- Tooltips appear on hover
- Campaign names display correctly
- Mobile touch interaction works
- Tooltips positioned correctly

### TC007: Month Boundaries
**Objective**: Test date handling across month/year boundaries

**Test Scenarios**:
- December to January transition
- February leap year handling
- Month-end date calculations

**Steps**:
1. Set system date near month boundary
2. View 6-month calendar
3. Verify correct date progression
4. Check booking/campaign date handling

**Expected Results**:
- Smooth month transitions
- Correct date calculations
- No date skipping or duplication

### TC008: Responsive Design
**Objective**: Test mobile responsiveness at 768px breakpoint

**Steps**:
1. View calendar on desktop (>768px)
2. Resize browser to 768px and below
3. Verify layout adjustments
4. Test touch interactions on mobile

**Expected Results**:
- Layout adapts at 768px breakpoint
- Font sizes scale appropriately
- Touch targets remain accessible
- No horizontal scrolling

### TC009: Accessibility Features
**Objective**: Test keyboard navigation and screen reader support

**Steps**:
1. Navigate calendar using Tab key
2. Verify focus indicators visible
3. Test with screen reader (NVDA/JAWS)
4. Check ARIA labels

**Expected Results**:
- All calendar days focusable
- Focus indicators clearly visible
- Screen reader announces dates and status
- ARIA labels provide context

### TC010: AJAX Error Handling
**Objective**: Test error scenarios and fallbacks

**Steps**:
1. Disable network connection
2. Try changing room selection
3. Verify error message displays
4. Test with invalid room_id

**Expected Results**:
- Japanese error messages display
- Graceful degradation
- No JavaScript console errors
- User-friendly error handling

### TC011: Performance Testing
**Objective**: Verify calendar loads efficiently

**Steps**:
1. Use browser dev tools Performance tab
2. Load calendar page
3. Measure load times and resource usage
4. Test with large datasets (many bookings/campaigns)

**Expected Results**:
- Page loads within 3 seconds
- Database queries optimized
- No memory leaks
- Efficient DOM manipulation

### TC012: Edge Cases
**Objective**: Test unusual scenarios

**Test Cases**:
- No rooms in database
- No bookings for selected room
- No active campaigns
- Overlapping bookings
- Campaign dates outside 180-day window

**Expected Results**:
- Appropriate messages for empty states
- No PHP errors or warnings
- Graceful handling of edge cases

## Browser Compatibility Testing

### Desktop Browsers
- Chrome 70+ ✓
- Firefox 65+ ✓
- Safari 12+ ✓
- Edge 79+ ✓

### Mobile Browsers
- iOS Safari 12+ ✓
- Chrome Mobile 70+ ✓
- Samsung Internet ✓

## Automated Testing

### PHP Unit Tests
```php
// Test calendar API functions
public function test_mbp_get_rooms_returns_active_rooms()
public function test_mbp_get_bookings_filters_by_date_range()
public function test_cleaning_buffer_calculation()
```

### JavaScript Tests
```javascript
// Test AJAX functionality
describe('Room Selection', () => {
  it('should update calendar on room change');
  it('should handle AJAX errors gracefully');
});
```

## Performance Benchmarks

### Database Query Limits
- Room query: < 50ms
- Booking query: < 100ms
- Campaign query: < 75ms
- Total page load: < 3 seconds

### Memory Usage
- PHP memory: < 32MB per request
- JavaScript heap: < 10MB
- DOM nodes: < 1000 elements

## Security Testing

### Input Validation
- room_id parameter sanitization
- AJAX nonce verification
- SQL injection prevention
- XSS protection

### Access Control
- Public calendar access (wp_ajax_nopriv)
- Admin calendar management
- Data exposure limits

## Regression Testing

### Existing Functionality
- Estimate shortcode still works
- Admin calendar unchanged
- Booking process unaffected
- Campaign management intact

## Bug Report Template

```
**Bug ID**: BUG-YYYY-MM-DD-###
**Severity**: Critical/High/Medium/Low
**Browser**: Chrome 120.0.0.0
**Device**: Desktop/Mobile
**Steps to Reproduce**:
1. 
2. 
3. 

**Expected Result**:

**Actual Result**:

**Screenshots**:

**Console Errors**:

**Additional Notes**:
```

## Test Completion Checklist

- [ ] All test cases executed
- [ ] No critical bugs found
- [ ] Performance benchmarks met
- [ ] Accessibility requirements satisfied
- [ ] Browser compatibility verified
- [ ] Mobile responsiveness confirmed
- [ ] Security tests passed
- [ ] Regression tests completed
- [ ] Documentation updated
- [ ] Code review completed

## Sign-off

**Tester**: ________________  
**Date**: ________________  
**Status**: PASS / FAIL  
**Notes**: ________________

---

**Version**: 2.2-final  
**Last Updated**: August 8, 2025  
**Next Review**: Before production deployment
