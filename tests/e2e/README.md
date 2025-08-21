# E2E Tests — Monthly Booking

Prereqs:
- Docker + Docker Compose
- Node 18+ (for local runs)

Install and run:
- cd tests/e2e
- npm install
- npm run pw:install
- MB_ADMIN_USER=admin MB_ADMIN_PASS=password MB_BASE_URL=http://localhost:8080 npm run test:e2e

CI:
- GitHub Actions workflow `.github/workflows/e2e.yml` runs docker stack, seeds data, then executes tests headlessly on Chromium. Artifacts (traces/screenshots/videos) are uploaded on failure.

Notes:
- Selectors use data-testid where available; initial pass may require updating selectors to match current HTML.
- DB checks can be added via dockerized `wp db query` helpers in future iterations.
