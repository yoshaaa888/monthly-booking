# [Critical] AJAX booking data loading failure - admin-ajax.php returns 400 Bad Request

**Labels**: `bug`, `critical`, `functionality`, `ajax`

## Description
The calendar is failing to load booking data due to a 400 Bad Request error from the WordPress admin-ajax.php endpoint. This prevents the calendar from displaying accurate booking status and affects core functionality for all users.

## Environment
- **Browser**: Chrome (mobile and desktop modes tested)
- **WordPress**: localhost:8888
- **Plugin Version**: monthly-booking v1.6.0
- **URL**: http://localhost:8888/monthly-calendar/
- **Test Date**: August 9, 2025

## WCAG Impact
- **Affected**: All users trying to view accurate booking information
- **Severity**: Critical - core functionality broken

## Reproduction Steps
1. Navigate to http://localhost:8888/monthly-calendar/
2. Open browser developer tools (F12)
3. Go to Console tab
4. Select any room from the dropdown (e.g., "東都マンスリー立川")
5. Observe console errors

## Expected Behavior
- AJAX requests to admin-ajax.php should return 200 OK status
- Booking data should load successfully for selected room
- Calendar should display accurate booking status (available/booked/campaign)
- No JavaScript errors in browser console
- Room selection should trigger successful data loading

## Actual Behavior
- Console shows: `Failed to load resource: the server responded with a status of 400 (Bad Request)` from admin-ajax.php
- Console shows: `Failed to load booking data` from calendar.js:94
- Calendar displays campaign data but without proper booking status integration
- Room selection triggers failed AJAX requests consistently

## Impact Assessment
- **User Groups Affected**: All users trying to view calendar
- **Functionality Impact**: Cannot view accurate booking availability
- **Severity**: Critical - core booking functionality broken
- **Workaround Available**: No

## Technical Details
- **AJAX Endpoint**: `/wp-admin/admin-ajax.php` returning 400 Bad Request
- **JavaScript File**: `assets/calendar.js` line 94 logging failure
- **Network Request**: Failing when room selection changes
- **Error Type**: Server-side request processing failure
- **Request Method**: Likely POST request to admin-ajax.php

## Console Error Details
```
Failed to load resource: the server responded with a status of 400 (Bad Request) admin-ajax.php:1
Failed to load booking data calendar.js:94
```

## Screenshots
- Console error screenshot: Available upon request
- Calendar display: Shows campaign data but missing booking integration

## Code References
- **File**: `assets/calendar.js`
- **Lines**: Around line 94 (AJAX failure logging)
- **Function**: Booking data loading functionality
- **Endpoint**: WordPress admin-ajax.php handler

## Related Issues
- This may be related to WordPress admin-ajax.php action registration
- Could be caused by missing or incorrect AJAX action handlers
- May require verification of nonce handling and security checks

## Acceptance Criteria
- [ ] AJAX requests to admin-ajax.php return 200 OK status
- [ ] Booking data loads successfully for all rooms
- [ ] No JavaScript errors in browser console
- [ ] Calendar displays accurate booking status for selected room
- [ ] Room selection works without AJAX failures
- [ ] Integration between campaign data and booking data works correctly

## Priority
**Critical** - This blocks core calendar functionality and affects all users attempting to view accurate booking information.
