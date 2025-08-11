Phase 1 implementation notes

Scope:
- Unify nonce system to window.monthlyBookingAdmin.reservationsNonce across admin.
- Remove legacy globals monthlyBookingForm and monthlyBookingReservations usage.
- Unify field names to checkin_date/checkout_date; remove JS mapping from start/end.
- Consolidate JS: standardize selector to .mbp-reservation-delete; disable legacy admin-reservations.js and stop enqueuing it.

Key changes:
- assets/admin.js: ensure all reservation AJAX requests include _ajax_nonce from monthlyBookingAdmin.reservationsNonce; removed any start/end to checkin/checkout mapping; standardized delete selector.
- assets/admin-form.js: ensure it uses monthlyBookingAdmin.reservationsNonce for _ajax_nonce.
- assets/admin-reservations.js: neutralized to prevent duplicate handlers; enqueue stopped in PHP.
- includes/admin-ui.php: removed monthly-booking-admin-form localization; ensured monthlyBookingAdmin localization remains the single source; stopped enqueuing admin-reservations.js if present.
- test-environment: updated admin.js to reference reservationsNonce and standardized delete selector.

Verification checklist (Phase 2):
- On admin reservation pages, window.monthlyBookingAdmin exists with keys: ajaxurl, reservationsNonce.
- Network: mbp_reservation_{create,update,delete,list} carry _ajax_nonce equal to mbp_reservations_nonce and return success:true on valid operations.
- Forms submit checkin_date/checkout_date directly; no start/end payloads present.
- UI list actions use .mbp-reservation-delete; no .delete-reservation remains in HTML/JS.
- Legacy globals not referenced by any executing script.
