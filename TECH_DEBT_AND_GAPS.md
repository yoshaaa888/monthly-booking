# TECH_DEBT_AND_GAPS

Purpose
- Investigation-only summary of admin UI robustness and related gaps. No code changes implemented in this document. Findings are evidence-linked to current repository state.

Scope of this report
- Admin UI hardening targets
- Booking calendar frontend ↔ PHP handler integration
- Campaign management data model and UI/JS inconsistencies
- Room activation status usage inconsistencies
- Shortcode and AJAX localization landscape

Status summary
- Investigation completed within constraints; changes are NOT applied. This document enumerates prioritized issues and suggested next-step fixes for future implementation sessions.

---

Priority 1 (High impact, low risk, unblock core UX)

1) Admin AJAX nonce not localized (admin.js expects monthlyBookingAdmin.nonce)
- Symptom
  - admin.js references monthlyBookingAdmin.nonce, but there is no wp_localize_script for monthly-booking-admin.
- Evidence
  - Admin enqueue loads admin.css/admin.js only: includes/admin-ui.php:101-120
  - JS reference: assets/admin.js (monthlyBookingAdmin.nonce)
- Impact
  - Admin-side AJAX will intermittently fail or always fail with undefined nonce, leading to 400/permission errors and broken UI interactions.
- Suggestion (implementation later)
  - Add wp_localize_script('monthly-booking-admin', 'monthlyBookingAdmin', { ajaxurl: admin_url('admin-ajax.php'), nonce: wp_create_nonce('monthly_booking_admin') }).
  - Ensure PHP handlers check_ajax_referer('monthly_booking_admin', 'nonce') to match.

2) Booking calendar AJAX handler missing for get_calendar_bookings
- Symptom
  - calendar.js posts to action=get_calendar_bookings, but there is no PHP handler registered.
- Evidence
  - assets/calendar.js posts with action=get_calendar_bookings
  - No add_action('wp_ajax_(nopriv)_get_calendar_bookings', ...) found across repo.
- Impact
  - Frontend calendar cannot load data, leading to empty or static calendar rendering.
- Suggestion (implementation later)
  - Implement wp_ajax_get_calendar_bookings and wp_ajax_nopriv_get_calendar_bookings.
  - Nonce verification using existing monthlyBookingAjax.nonce (or introduce a calendar-specific nonce).
  - Validate month, year, and optional room_id; return JSON with booking/campaign data.

3) Campaign table naming inconsistency (monthly_campaigns vs monthly_booking_campaigns)
- Symptom
  - DB creation and sample insertion use monthly_campaigns. Business logic in some places references monthly_booking_campaigns (which does not exist).
- Evidence
  - monthly-booking.php creates and seeds monthly_campaigns.
  - includes/campaign-manager.php contains reads/writes to monthly_booking_campaigns, but get_campaign_by_type and other reads point to monthly_campaigns.
- Impact
  - Admin/read/write operations can silently fail; toggles or listings may show empty states despite data being present.
- Suggestion (implementation later)
  - Decide on canonical table name. Recommendation: unify on monthly_campaigns to match existing creation and seed paths.
  - Refactor all references to the canonical name in campaign-manager.php, booking-logic.php, admin-ui.php.
  - Consider migration adapter if any real data already exists in the non-canonical table in some environments.

---

Priority 2 (Medium impact, straightforward unification)

4) Room activation condition mismatch (status='active' vs is_active=1)
- Symptom
  - Queries in some files use WHERE status='active' whereas schema uses is_active for monthly_rooms.
- Evidence
  - Inconsistent usage: calendar-render.php and possibly other selects; most current selects already use is_active=1.
- Impact
  - Empty dropdowns or missing room listings; hard to diagnose UX failures.
- Suggestion (implementation later)
  - Standardize all room queries to is_active=1.
  - Add minimal repository-wide check to ensure no lingering status='active' usages on monthly_rooms.

5) Admin UI elements missing for campaign interactions
- Symptom
  - admin.js contains toggle handler logic (e.g., class .toggle-campaign-status), but Admin PHP does not render corresponding DOM elements.
- Evidence
  - includes/admin-ui.php campaign settings page is placeholder; search shows no .toggle-campaign-status/data-campaign-id outputs.
- Impact
  - Admin users cannot interact with campaigns; JS handlers are dead/unused.
- Suggestion (implementation later)
  - Render read-only list plus minimal toggle button or status switch UI with data attributes (data-campaign-id) and ensure nonce present via localization.

---

Priority 3 (Design/consistency/UX)

6) Shortcode inconsistency with CI
- Symptom
  - Plugin implements [monthly_booking_calendar] and [monthly_booking_estimate], CI references [monthly_calendar].
- Impact
  - Automated flows or sample pages may break; confusion for setup.
- Suggestion (documentation later)
  - Align CI and docs to plugin’s shortcodes, or provide backward-compatible alias shortcode registration.

7) AJAX localization landscape audit
- Observations
  - Frontend estimate.js has monthlyBookingAjax localized and used correctly.
  - Admin side lacks monthlyBookingAdmin localization.
- Suggestion
  - Centralize enqueue/localize across admin and frontend for consistency; ensure each AJAX consumer has nonce and ajaxurl.

---

Risks and decision points
- Campaign table unification
  - Decision required: canonical name. Recommend monthly_campaigns to minimize migration work.
  - If environments exist with monthly_booking_campaigns, a data migration script will be required (export/import or INSERT ... SELECT).
- Handler naming standard
  - Consider standardized action names prefixed with mbp_ (e.g., mbp_get_calendar_bookings) for future clarity.

Operational impact
- P1 items restore core UX paths: admin interactions and calendar data loading.
- P2 items stabilize data access for rooms and connect Admin UI with JS handlers.
- P3 items reduce confusion in CI/docs and complete localization hygiene.

Suggested implementation sequencing (for a future session; not applied now)
- Phase 1 (≤2h)
  - Add admin wp_localize_script for admin.js and verify nonce handling.
  - Implement get_calendar_bookings handler (both logged-in and non-logged-in) with nonce and parameter validation.
- Phase 2 (≤2h)
  - Standardize room queries to is_active=1.
  - Render minimal campaign toggle UI elements in Admin page to match admin.js handlers.
- Phase 3 (≤2h)
  - Unify campaign table references to monthly_campaigns; optional migration adapter if needed.
  - Align shortcode references in CI/docs or register alias shortcodes.

Verification approach (future)
- Admin Network tab shows 200 responses for admin AJAX with {success:true}.
- Calendar endpoint returns bookings/campaign data for the selected month/year.
- Room dropdowns show active rooms; no empty results caused by column mismatches.
- Campaign toggle buttons operate and persist expected state changes.
- No PHP warnings/notices; nonce check passes; handlers registered for both wp_ajax_ and wp_ajax_nopriv_ where appropriate.

References
- Admin enqueue without localization: includes/admin-ui.php:101-120
- Admin UI class/hooks: includes/admin-ui.php:12-18
- calendar.js posts action=get_calendar_bookings: assets/calendar.js
- No get_calendar_bookings PHP handlers present in repo
- monthly_campaigns created/seeded: monthly-booking.php (insert_sample_campaigns and table creation)
- campaign-manager references monthly_booking_campaigns and monthly_campaigns: includes/campaign-manager.php
- rooms select should use is_active=1: includes/admin-ui.php (e.g., get rooms list), calendar-render.php
- Shortcodes implemented: includes/calendar-render.php; Frontend estimate logic: assets/estimate.js (localized monthlyBookingAjax)
