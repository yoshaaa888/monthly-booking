# Post-Release Backlog — Monthly Booking v1.0.0

Purpose
- Prioritized improvement roadmap based on production feedback for 450-room operations.

Legend
- Priority: P1 (Essential), P2 (Important), P3 (Nice-to-have)
- Effort: S (Small), M (Medium), L (Large)
- Impact: High / Medium / Low

Phase 1 — Essential (Operational Impact)
1) Calendar performance at scale (P1, L, High)
   - Virtualize room list, paginate or lazy-load months.
   - Precompute monthly availability summary; invalidate on CRUD changes.
   - Add DB indexes: reservations(room_id, checkin_date, checkout_date), room_campaigns(room_id, start_date, end_date, is_active).

2) Availability caching (P1, M, High)
   - Cache per-room, per-day statuses for a month.
   - Invalidate on new reservation, checkout, cleaning toggle, campaign assignment changes.

3) Robust error messaging and retries (P1, S, Medium)
   - Standardize admin AJAX error handling and user-facing messages.
   - Add retry/backoff for batch operations.

Phase 2 — UX Improvements
4) Keyboard navigation and a11y (P2, M, Medium)
   - Arrow/Home/End/PageUp/Down movements on grid.
   - aria-live updates for side panel; focus states for row actions.

5) Loading states and feedback (P2, S, Medium)
   - Indicate fetching/calculating states on calendar and lists.
   - Progress UI for bulk assignment/removal.

6) Advanced filtering/sorting (P2, M, Medium)
   - Calendar/Rooms: filter by campaign presence, status, property, price range.
   - Persist filters per admin user.

Phase 3 — Long-term Quality
7) Test coverage expansion (P3, M, Medium)
   - CRUD E2E for rooms, reservations, campaigns.
   - Calendar interaction and symbol precedence tests.

8) Configurability of operational values (P3, M, Medium)
   - Make cleaning buffer days configurable (default 5).
   - Expose certain thresholds (e.g., max discount days) via settings.

9) Documentation and playbooks (P3, S, Low)
   - Deepen troubleshooting guides with common field symptoms and remedies.
   - Add more example datasets (seasonal campaigns, long-stay constraints).

Notes
- Prioritize based on live operational feedback (daily dashboards and operator reports).
- Keep packaging lightweight; avoid bundling dev artifacts in releases.
- Leverage incremental PRs with focused scope to reduce risk.
