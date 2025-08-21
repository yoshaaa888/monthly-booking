# E2E Testing Plan — Monthly Booking v1.0.0

Scope
- Validate critical business flows across UI + backend using Docker WordPress stack.
- Cover happy paths and representative error scenarios.
- Verify DB state via WP-CLI in Docker.
- Integrate with GitHub Actions CI.

Framework Selection
- Playwright (TypeScript)
  - Cross-browser (Chromium, Firefox, WebKit)
  - Reliable auto-waiting and trace/videos for debugging
  - Parallelization and retries per test
- Runner: @playwright/test
- Data/Env orchestration: Docker Compose + WP-CLI scripts invoked from Node before/after tests.

Test Environment
- Reuse dev/docker-compose.yml to spin up: wordpress, mysql, wpcli
- Base URL: http://localhost:8080
- Admin auth: via test mu-plugin (test-environment/mu-plugins/mb-test-auth.php) or scripted login
- Nonces: Use canonical nonces (monthly_booking_admin for admin, monthly_booking_nonce for public). JS-driven flows should work end-to-end.

Target User Flows (Priority 1)
1) Campaign Management Flow
   - Create campaign (rate/fixed), set period and contract types
   - Assign to rooms
   - Verify calendar display shows campaign indicators/badges
   - DB checks: wp_monthly_campaigns, wp_monthly_room_campaigns row presence and values

2) Room Management Flow
   - Bulk select rooms -> Assign campaign
   - Verify status badges in room list update
   - DB checks: assignment count matches selected rooms; audit logs written if enabled

3) Calendar Operations Flow
   - Navigate months, select room, assign/unassign campaign from calendar UI
   - Verify right panel indicators update
   - DB checks: assignment window matches selected dates

4) Emergency Response Flow
   - Detect vacancy (10+ day gap)
   - Apply quick discount preset (e.g., 20/30/50% escalation)
   - Verify result in calendar and room list; DB reflects discount campaign active

Error Scenarios
- Invalid nonce → 403 (simulate by tampering request or calling ajax directly)
- Capability check failure for admin-only actions
- Period overlap conflict on assignments (expect 409/conflict JSON)
- Validation errors on missing required fields

Test Data Strategy
- Before each suite:
  - Reset DB to sample state (dist/sample-data.sql) or dedicated seed script
  - Create admin user and log in
  - Ensure minimum properties/rooms exist
- After each suite:
  - Collect Playwright traces/screenshots
  - Optionally revert DB

Assertions
- UI assertions: visible text, table rows, badges, toasts
- Network assertions: admin-ajax responses success:true
- DB assertions via dockerized wp-cli:
  - wp db query "SELECT ..."
  - wp option get for flags if needed

CI Integration
- New workflow: .github/workflows/e2e.yml
  - Jobs:
    - Set up Node + Playwright
    - Start Docker stack (dev/docker-compose.yml)
    - Wait for WP readiness
    - Import sample data
    - Run tests: pnpm test:e2e (or npm/yarn)
    - Upload artifacts: traces, screenshots, videos
  - Matrix: browsers (at least chromium), OS ubuntu-latest
  - Trigger: PR and push on main

Repository Structure
- tests/e2e/
  - package.json
  - playwright.config.ts
  - fixtures/
    - wp.ts (helpers to run wp-cli in docker)
    - auth.ts (admin login helper)
  - specs/
    - campaigns.spec.ts
    - rooms-bulk.spec.ts
    - calendar-ops.spec.ts
    - emergency.spec.ts
  - README.md (how to run locally)
- scripts/
  - e2e_start.sh: docker compose up, wait, seed
  - e2e_stop.sh: docker compose down -v

Maintenance Guide
- Adding new flows: copy spec, reuse fixtures
- Debugging: use PW trace viewer and videos
- Updating selectors: prefer data-testid attributes in templates (future improvement)

Risks and Mitigations
- Flaky nonces: rely on real UI flows, not manual ajax; canonical nonce standardization already implemented
- Env boot time: add robust wait-for-WP script
- Data drift: always import known sample data before tests

Timeline (Phase)
- Phase A: Scaffold + 1 happy path (Campaign Management)
- Phase B: Add remaining 3 flows
- Phase C: Error scenarios + CI tuning
