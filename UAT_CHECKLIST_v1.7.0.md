# User Acceptance Testing Checklist - Reservations MVP v1.7.0

## Pre-Testing Setup
- [ ] WordPress admin access confirmed
- [ ] Monthly Booking Plugin v1.7.0-alpha installed and activated
- [ ] Feature flag `MB_FEATURE_RESERVATIONS_MVP` enabled
- [ ] Test room data available in system
- [ ] Browser developer tools available for accessibility testing

## Admin Interface Testing

### Navigation and Access
- [ ] Navigate to WordPress admin dashboard
- [ ] Locate "Monthly Room Booking" menu in admin sidebar
- [ ] Click on "予約登録" (Reservation Registration) submenu
- [ ] Verify page loads without errors
- [ ] Confirm page title displays correctly

### Reservation List View
- [ ] View empty reservation list (initial state)
- [ ] Verify "新規予約追加" (Add New Reservation) button is visible
- [ ] Check table headers are properly displayed
- [ ] Verify "予約がありません" (No reservations) message appears when empty

### Add New Reservation
- [ ] Click "新規予約追加" button
- [ ] Verify form loads with all required fields
- [ ] Check room dropdown populates with available rooms
- [ ] Confirm all form labels are properly associated
- [ ] Verify required field indicators (*) are visible

## Form Validation Testing

### Required Field Validation
- [ ] Submit empty form
- [ ] Verify error messages appear for required fields:
  - [ ] Room selection error
  - [ ] Customer name error
  - [ ] Email address error
  - [ ] Check-in date error
  - [ ] Check-out date error
  - [ ] Adult count error
- [ ] Confirm error messages are announced to screen readers
- [ ] Verify `aria-invalid="true"` is set on invalid fields

### Data Validation
- [ ] Enter invalid email format - verify validation error
- [ ] Select check-out date before check-in date - verify error
- [ ] Select past date for check-in - verify error
- [ ] Enter adult count outside 1-10 range - verify error
- [ ] Enter child count outside 0-10 range - verify error

### Successful Submission
- [ ] Fill all required fields with valid data:
  - [ ] Select room from dropdown
  - [ ] Enter customer name
  - [ ] Enter valid email address
  - [ ] Select future check-in date
  - [ ] Select check-out date after check-in
  - [ ] Set adult count (1-10)
  - [ ] Set child count (0-10)
  - [ ] Add optional phone number
  - [ ] Add optional notes
- [ ] Submit form
- [ ] Verify success message appears
- [ ] Confirm redirect to reservation list
- [ ] Verify new reservation appears in list

## Conflict Detection Testing

### Create Base Reservation
- [ ] Create reservation for Room A, dates 2025-09-01 to 2025-09-05
- [ ] Verify successful creation

### Test Overlap Scenarios
- [ ] Attempt to create reservation for same room, dates 2025-09-03 to 2025-09-07
- [ ] Verify conflict error message appears
- [ ] Test exact same dates - verify conflict detected
- [ ] Test check-in on existing check-out date - verify no conflict (half-open interval)
- [ ] Test check-out on existing check-in date - verify no conflict

## Edit Reservation Testing

### Access Edit Form
- [ ] From reservation list, click "編集" (Edit) button
- [ ] Verify form loads with existing data populated
- [ ] Confirm all fields show current values
- [ ] Verify status dropdown is available (pending/confirmed/cancelled)

### Update Reservation
- [ ] Modify customer name
- [ ] Change status to "confirmed"
- [ ] Update notes field
- [ ] Submit changes
- [ ] Verify success message
- [ ] Confirm changes appear in reservation list

### Edit Conflict Detection
- [ ] Edit reservation to overlap with another existing reservation
- [ ] Verify conflict error appears
- [ ] Confirm original reservation remains unchanged

## Delete Reservation Testing

### Delete Confirmation
- [ ] From reservation list, click "削除" (Delete) button
- [ ] Verify confirmation dialog appears
- [ ] Click "Cancel" - confirm reservation remains
- [ ] Click "削除" again and confirm deletion
- [ ] Verify success message appears
- [ ] Confirm reservation removed from list

## Calendar Integration Testing

### Create Reservation and Verify Calendar
- [ ] Create new reservation through admin interface
- [ ] Navigate to "予約カレンダー" (Booking Calendar) page
- [ ] Select the room used for reservation
- [ ] Navigate to the month containing reservation dates
- [ ] Verify booking appears on correct dates in calendar
- [ ] Confirm booking status is visually indicated

### Real-time Calendar Updates
- [ ] Keep calendar page open in one browser tab
- [ ] Open reservation admin in another tab
- [ ] Create new reservation
- [ ] Return to calendar tab
- [ ] Verify calendar automatically updates without page refresh
- [ ] Test edit and delete operations similarly

## Accessibility Testing

### Keyboard Navigation
- [ ] Navigate entire reservation form using only Tab key
- [ ] Verify logical tab order through all form elements
- [ ] Test form submission using Enter key
- [ ] Navigate reservation list using keyboard only
- [ ] Test edit/delete actions via keyboard

### Screen Reader Testing (if available)
- [ ] Use screen reader to navigate reservation form
- [ ] Verify all labels are properly announced
- [ ] Confirm error messages are announced when they appear
- [ ] Test form submission feedback via screen reader

### Focus Management
- [ ] Verify focus indicators are visible on all interactive elements
- [ ] Test focus movement after form submission
- [ ] Confirm focus returns appropriately after modal/dialog interactions

## Feature Flag Testing

### Disable Feature Flag
- [ ] Set `MB_FEATURE_RESERVATIONS_MVP` to false
- [ ] Navigate to reservation page
- [ ] Verify graceful degradation message appears
- [ ] Confirm "新規予約追加" button is not visible
- [ ] Verify existing functionality message is displayed

### Re-enable Feature Flag
- [ ] Set `MB_FEATURE_RESERVATIONS_MVP` to true
- [ ] Refresh reservation page
- [ ] Verify full functionality is restored
- [ ] Confirm all CRUD operations work normally

## Calendar Accessibility Regression Testing

### Existing v1.6.1 Features
- [ ] Navigate to booking calendar page
- [ ] Verify keyboard navigation still works:
  - [ ] Arrow keys move between dates
  - [ ] Home/End keys work for row navigation
  - [ ] PageUp/PageDown work for month navigation
- [ ] Confirm roving tabindex is maintained (only one cell has tabindex="0")
- [ ] Verify ARIA announcements for month changes
- [ ] Test tooltip accessibility with keyboard focus

## Performance Testing

### Page Load Times
- [ ] Measure reservation list page load time
- [ ] Test form submission response time
- [ ] Verify calendar refresh performance
- [ ] Check for any JavaScript errors in console

### Large Data Sets (if applicable)
- [ ] Create multiple reservations (10+)
- [ ] Test pagination functionality
- [ ] Verify list performance with larger datasets
- [ ] Test search/filter performance (if implemented)

## Error Handling Testing

### Network Errors
- [ ] Simulate network disconnection during form submission
- [ ] Verify appropriate error message appears
- [ ] Test recovery when connection is restored

### Server Errors
- [ ] Test behavior with invalid server responses
- [ ] Verify graceful error handling
- [ ] Confirm user-friendly error messages

## Cross-Browser Testing

### Browser Compatibility
- [ ] Test in Chrome (latest)
- [ ] Test in Firefox (latest)
- [ ] Test in Safari (if available)
- [ ] Test in Edge (latest)
- [ ] Verify consistent behavior across browsers

## Mobile Responsiveness

### Mobile Interface
- [ ] Test reservation form on mobile device
- [ ] Verify form elements are properly sized
- [ ] Test touch interactions
- [ ] Confirm calendar integration works on mobile

## Final Verification

### Complete Workflow Test
- [ ] Perform end-to-end reservation workflow:
  1. [ ] Create reservation via admin
  2. [ ] Verify calendar display
  3. [ ] Edit reservation details
  4. [ ] Confirm calendar updates
  5. [ ] Delete reservation
  6. [ ] Verify removal from calendar
- [ ] Test with multiple rooms and overlapping date ranges
- [ ] Verify all accessibility features remain functional
- [ ] Confirm no regressions in existing plugin functionality

## Sign-off

### Testing Completion
- [ ] All test cases completed successfully
- [ ] Critical issues identified and documented
- [ ] Accessibility compliance verified
- [ ] Performance benchmarks met
- [ ] Ready for production deployment

**Tester Name:** _______________  
**Date:** _______________  
**Version Tested:** v1.7.0-alpha  
**Overall Status:** [ ] PASS [ ] FAIL [ ] CONDITIONAL PASS  

**Notes:**
_________________________________
_________________________________
_________________________________
