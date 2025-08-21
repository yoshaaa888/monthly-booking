# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifactsromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Integrate with GitHub Actions CI.
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifactsFramework Selection
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Playwright (TypeScript)
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - Cross-browser (Chromium, Firefox, WebKit)
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - Reliable auto-waiting and trace/videos for debugging
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - Parallelization and retries per test
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Runner: @playwright/test
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Data/Env orchestration: Docker Compose + WP-CLI scripts invoked from Node before/after tests.
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifactsTest Environment
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Reuse dev/docker-compose.yml to spin up: wordpress, mysql, wpcli
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Base URL: http://localhost:8080
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Admin auth: via test mu-plugin (test-environment/mu-plugins/mb-test-auth.php) or scripted login
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Nonces: Use canonical nonces (monthly_booking_admin for admin, monthly_booking_nonce for public). JS-driven flows should work end-to-end.
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifactsTarget User Flows (Priority 1)
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts1) Campaign Management Flow
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts   - Create campaign (rate/fixed), set period and contract types
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts   - Assign to rooms
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts   - Verify calendar display shows campaign indicators/badges
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts   - DB checks: wp_monthly_campaigns, wp_monthly_room_campaigns row presence and values
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts2) Room Management Flow
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts   - Bulk select rooms -> Assign campaign
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts   - Verify status badges in room list update
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts   - DB checks: assignment count matches selected rooms; audit logs written if enabled
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts3) Calendar Operations Flow
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts   - Navigate months, select room, assign/unassign campaign from calendar UI
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts   - Verify right panel indicators update
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts   - DB checks: assignment window matches selected dates
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts4) Emergency Response Flow
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts   - Detect vacancy (10+ day gap)
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts   - Apply quick discount preset (e.g., 20/30/50% escalation)
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts   - Verify result in calendar and room list; DB reflects discount campaign active
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifactsError Scenarios
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Invalid nonce → 403 (simulate by tampering request or calling ajax directly)
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Capability check failure for admin-only actions
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Period overlap conflict on assignments (expect 409/conflict JSON)
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Validation errors on missing required fields
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifactsTest Data Strategy
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Before each suite:
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - Reset DB to sample state (dist/sample-data.sql) or dedicated seed script
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - Create admin user and log in
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - Ensure minimum properties/rooms exist
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- After each suite:
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - Collect Playwright traces/screenshots
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - Optionally revert DB
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifactsAssertions
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- UI assertions: visible text, table rows, badges, toasts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
Updates: CI runs Firefox as the primary browser inside the official Playwright container; Chromium remains configured with hardened flags for limited usage. Artifacts (playwright-report and test-results with traces/videos) are always uploaded. For local instability with Chromium on some VMs, prefer running via CI.
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Network assertions: admin-ajax responses success:true
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- DB assertions via dockerized wp-cli:
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - wp db query "SELECT ..."
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - wp option get for flags if needed
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifactsCI Integration
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- New workflow: .github/workflows/e2e.yml
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - Jobs:
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts    - Set up Node + Playwright
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts    - Start Docker stack (dev/docker-compose.yml)
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts    - Wait for WP readiness
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts    - Import sample data
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts    - Run tests: pnpm test:e2e (or npm/yarn)
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts    - Upload artifacts: traces, screenshots, videos
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - Matrix: browsers (at least chromium), OS ubuntu-latest
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - Trigger: PR and push on main
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifactsRepository Structure
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- tests/e2e/
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - package.json
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - playwright.config.ts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - fixtures/
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts    - wp.ts (helpers to run wp-cli in docker)
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts    - auth.ts (admin login helper)
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - specs/
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts    - campaigns.spec.ts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts    - rooms-bulk.spec.ts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts    - calendar-ops.spec.ts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts    - emergency.spec.ts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - README.md (how to run locally)
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- scripts/
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - e2e_start.sh: docker compose up, wait, seed
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts  - e2e_stop.sh: docker compose down -v
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifactsMaintenance Guide
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Adding new flows: copy spec, reuse fixtures
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Debugging: use PW trace viewer and videos
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Updating selectors: prefer data-testid attributes in templates (future improvement)
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifactsRisks and Mitigations
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Flaky nonces: rely on real UI flows, not manual ajax; canonical nonce standardization already implemented
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Env boot time: add robust wait-for-WP script
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Data drift: always import known sample data before tests
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifactsTimeline (Phase)
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Phase A: Scaffold + 1 happy path (Campaign Management)
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Phase B: Add remaining 3 flows
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts- Phase C: Error scenarios + CI tuning
# E2E Plan (Phase A–E)

Selectors
- Use data-testid="mb-{feature}-{element}"
- Examples: mb-campaign-create, mb-campaign-form, mb-room-row, mb-room-select, mb-room-bulk-assign, mb-room-campaign-badge, mb-calendar-content, mb-calendar-cell, mb-calendar-room-selector

Phases
- A: Scaffold (Playwright + Docker scripts + CI)
- B: Campaign flow (create → assign rooms → verify calendar + DB)
- C: Rooms bulk flow (bulk select → assign → UI + DB)
- D: Calendar operations (render, room switch)
- E: Emergency quick discount (skip if not present)

DB verification
- fixtures/wp.ts provides wpScalar and wpDbQuery using docker compose + WP-CLI

CI
- .github/workflows/e2e.yml runs Chromium-only, starts Docker stack, health checks MB_BASE_URL, runs tests, uploads artifacts
