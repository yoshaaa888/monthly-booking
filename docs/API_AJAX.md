# Monthly Booking AJAX API Specification

Scope
- Defines current and proposed admin-ajax endpoints for Monthly Booking
- Covers authentication, nonces, payloads, responses, error formats, and versioning
- Target: Admin-side operations (calendar, reservations, campaigns, assignments)

Conventions
- Transport: POST to /wp-admin/admin-ajax.php
- Content-Type: application/x-www-form-urlencoded
- Authentication:
  - Admin endpoints require logged-in user with manage_options capability unless explicitly stated.
  - Nonce fields:
    - monthlyBookingAjax.nonce or field name "nonce" with action "monthly_booking_nonce" for admin/campaign utilities
    - mbp_calendar_nonce for calendar endpoints
    - mbp_reservations_nonce (sent as _ajax_nonce) for reservation CRUD
- Responses:
  - Success: wp_send_json_success(data)
  - Error: wp_send_json_error(messageOrData, [http_status])
  - Error format example: { "success": false, "data": "Invalid nonce" } or { "success": false, "data": { "code": "forbidden", "message": "..." } }
- Versioning:
  - Endpoints include a logical version key in responses: { "api_version": "1.0" }
  - Changes follow additive approach; breaking changes introduce new action names (e.g., ..._v2)

Endpoints

1) Calendar
- Action: mbp_get_calendar_bookings
  - Auth: nonce (mbp_calendar_nonce OR monthly_booking_nonce). Public read allowed with valid nonce.
  - Params: month (1-12, int), year (1970-2100, int), room_id (optional int)
  - Response: 
    - success: { bookings: [ { date: "YYYY-MM-DD", status: "booked|available|cleaning", guest_name?: string } ] } 
      or legacy: [ { date, status, guest_name? } ]
  - Errors:
    - 400: Missing nonce, invalid month/year
    - 403: Invalid nonce

- Action: mbp_load_calendar_matrix
  - Auth: nonce (monthly_booking_nonce)
  - Params: days (int), room_ids[] (array of ints)
  - Response: HTML string of matrix
  - Errors: 403 on nonce failure

2) Reservations (Admin)
- Action: mbp_reservation_create
  - Auth: manage_options + _ajax_nonce=mbp_reservations_nonce
  - Params: room_id, guest_name, guest_email, checkin_date, checkout_date, status?, notes?
  - Response: { message: "予約が正常に作成されました。", reservation_id: number }
  - Errors: 403 (capability), validation messages

- Action: mbp_reservation_update
  - Auth: manage_options + _ajax_nonce=mbp_reservations_nonce
  - Params: reservation_id, room_id, guest_name, guest_email, checkin_date, checkout_date, status, notes
  - Response: { message: "予約が正常に更新されました。" }

- Action: mbp_reservation_delete
  - Auth: manage_options + _ajax_nonce=mbp_reservations_nonce
  - Params: reservation_id
  - Response: { message: "予約が正常に削除されました。" }

- Action: mbp_reservation_list
  - Auth: manage_options + _ajax_nonce=mbp_reservations_nonce
  - Params: page?, per_page?
  - Response: { reservations: [ { id, property_name, room_name, guest_name, guest_email, checkin_date, checkout_date, total_price, status } ], pagination? }

3) Campaigns (Admin)
- Current UI calls (assets/admin.js) use actions:
  - create_campaign, update_campaign, delete_campaign, toggle_campaign
  - save_campaign_assignment, delete_campaign_assignment, get_room_campaign_assignments, get_active_campaigns, get_campaign_assignment, check_campaign_period_overlap, toggle_assignment_status
- Auth: manage_options + nonce=monthly_booking_admin (or monthly_booking_nonce for older actions)
- Payloads:
  - create/update_campaign:
    - name (string), discount_type ('percentage'|'fixed'|'flatrate' if used), discount_value (number)
    - period_type ('fixed'|'checkin_relative'|'unlimited')
    - relative_days (int?)
    - start_date?, end_date? (YYYY-MM-DD)
    - contract_types[]: ['SS','S','M','L']
    - campaign_id (for update)
  - delete_campaign: campaign_id
  - toggle_campaign: campaign_id, is_active (0/1)
- Responses:
  - Success: { message: string } for mutations; list/record JSON for fetch endpoints.
  - Errors: capability/nonce validation, semantic validation (duplicate name, invalid range, etc.)

4) Booking Logic (Public + Admin)
- calculate_booking_price (public)
  - Auth: nonce=monthly_booking_nonce
  - Params: start_date, end_date, property_id
  - Response: { base_total, campaign_discount, final_price, ... }

- calculate_estimate, submit_booking, get_booking_options, search_properties, get_search_filters (as present in includes/booking-logic.php)
  - All require nonce (monthly_booking_nonce), public allowed
  - Standard success/error pattern

Security & Validation
- Nonce required for all endpoints
- Admin-only endpoints check current_user_can('manage_options')
- Sanitize inputs:
  - Dates: sanitize_text_field then validate via DateTime::createFromFormat
  - Numbers: intval/floatval
  - Arrays: map sanitize where needed
- Rate limiting: not implemented (WordPress login/nonce affords basic protection)
- Logging: Critical failures should be logged via error_log with action name and user ID

Error Codes (recommended)
- invalid_nonce (403)
- forbidden (403)
- invalid_params (400) with field-specific messages where possible
- not_found (404) for resource fetches
- conflict (409) e.g., period overlap on assignments
- server_error (500) unexpected issues

Deprecations and Versioning
- Prefer stable action names; introduce new action names suffixed by _v2 for breaking changes.
- Include api_version: "1.0" in success responses for new/updated endpoints.

Testing
- WP-CLI curl examples:
  - wp eval 'echo wp_create_nonce("monthly_booking_nonce");'
  - curl -X POST -d "action=mbp_get_calendar_bookings&month=8&year=2025&nonce=XXX" http://localhost:8080/wp-admin/admin-ajax.php
- Playwright tests should wait for /wp-admin/admin-ajax.php POST and validate JSON structure and error cases.

Appendix: Current Implementations Mapped
- Registered in monthly-booking.php:
  - mbp_load_calendar, mbp_get_calendar_bookings, mbp_load_calendar_matrix
  - mbp_reservation_{create,update,delete,list}
- Client calls:
  - assets/calendar.js → mbp_get_calendar_bookings, mbp_load_calendar_matrix
  - assets/admin.js → create/update/delete/toggle campaign; assignment CRUD; mbp_reservation_list
