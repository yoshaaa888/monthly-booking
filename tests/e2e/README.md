E2E tests run Firefox as the primary browser in CI. Use CI artifacts (playwright-report and test-results) for debugging traces/videos.
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)# E2E Tests — Monthly Booking
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)Prereqs:
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)- Docker + Docker Compose
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)- Node 18+ (for local runs)
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)Install and run:
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)- cd tests/e2e
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)- npm install
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)- npm run pw:install
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)- MB_ADMIN_USER=admin MB_ADMIN_PASS=password MB_BASE_URL=http://localhost:8080 npm run test:e2e
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)CI:
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)- GitHub Actions workflow `.github/workflows/e2e.yml` runs docker stack, seeds data, then executes tests headlessly on Chromium. Artifacts (traces/screenshots/videos) are uploaded on failure.
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)Notes:
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)- Selectors use data-testid where available; initial pass may require updating selectors to match current HTML.
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)- DB checks can be added via dockerized `wp db query` helpers in future iterations.
# Monthly Booking — E2E Tests

This folder contains the Playwright-based end-to-end tests for the Monthly Booking plugin.

How to run locally
- Prerequisites: Docker and Node.js 20
- Start WordPress stack and install browsers:
  cd tests/e2e
  npm ci
  npm run pw:install
  npm run pretest:e2e

- Run tests:
  MB_BASE_URL=http://localhost:8080 MB_ADMIN_USER=admin MB_ADMIN_PASS=password npm run test:e2e

- Stop stack:
  npm run posttest:e2e

Reports and artifacts
- HTML report: tests/e2e/playwright-report/index.html
- Traces/screenshots/videos: tests/e2e/test-results

Conventions
- Stable selectors use data-testid="mb-{feature}-{element}"
- DB verification uses dockerized WP-CLI (see fixtures/wp.ts)
