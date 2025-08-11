# [Critical] AJAX booking data loading failure causing calendar malfunction

**Labels**: `bug`, `critical`, `functionality`

## Description
The calendar is failing to load booking data due to a 400 Bad Request error from the WordPress admin-ajax.php endpoint. This prevents the calendar from displaying accurate booking status and affects core functionality.

## Environment
- **Browser**: Chrome (mobile mode)
- **Device**: Mobile viewport (375px width)
- **WordPress**: localhost:8888
- **Plugin Version**: monthly-booking v1.6.0
- **URL**: http://localhost:8888/monthly-calendar/

## Reproduction Steps
1. Navigate to http://localhost:8888/monthly-calendar/
2. Open browser developer tools (F12)
3. Go to Console tab
4. Select a room from the dropdown
5. Observe the console errors

## Expected Behavior
- AJAX requests to admin-ajax.php should return 200 OK
- Booking data should load successfully
- Calendar should display accurate booking status for selected room
- No JavaScript errors in console

## Actual Behavior
- Console shows: `Failed to load resource: the server responded with a status of 400 (Bad Request)` from admin-ajax.php
- Console shows: `Failed to load booking data` from calendar.js:94
- Calendar displays but without proper booking status data
- Room selection triggers failed AJAX requests

## Impact Assessment
- **User Groups Affected**: All users trying to view calendar
- **Severity**: Critical - core functionality broken
- **Workaround Available**: No

## Technical Details
- **AJAX Endpoint**: admin-ajax.php returning 400 Bad Request
- **JavaScript File**: calendar.js line 94 logging failure
- **Network Request**: Failing when room selection changes
- **Error Type**: Server-side request processing failure

## Screenshots
Console error screenshot: /home/ubuntu/screenshots/localhost_8888_062435.png

## Code References
- **File**: assets/calendar.js
- **Lines**: Around line 94 (AJAX failure logging)
- **Function**: Booking data loading functionality

## Acceptance Criteria
- [ ] AJAX requests to admin-ajax.php return 200 OK status
- [ ] Booking data loads successfully for all rooms
- [ ] No JavaScript errors in browser console
- [ ] Calendar displays accurate booking status
- [ ] Room selection works without errors
