Dev Environment — Docker (WordPress + MySQL + WP-CLI)

Purpose
- Provide a reliable, wp-now-free local environment for maintenance and hotfix work.

Requirements
- Docker Desktop / Docker engine with Compose v2
- Make (optional but recommended)

Quick Start
1) Copy env
   - cp dev/.env.example dev/.env

2) Start stack
   - make up
   - Open WordPress: http://localhost:8080 (first run may require wp core install)

3) Install WP (first time only)
   - make reset-db
   - Admin: user=admin / pass=admin (change afterward)

4) Mounted plugin
   - Plugin mounted into wp-content/plugins/monthly-booking from repo root
   - Activate: make activate-plugin

5) Optional demo data
   - make import-sample
   - Uses dist/sample-data.sql (CURDATE()-based)

Utilities
- make logs       # follow container logs
- make wp ARGS="plugin list"   # run arbitrary WP-CLI
- make shell      # open bash in wpcli container
- make down       # stop and remove volumes

Emergency Hotfix Flow
- git checkout -b devin/{epoch}-hotfix-brief-slug
- make up && make reset-db && make activate-plugin
- Reproduce/verify fix using Make targets (and import-sample if needed)
- Commit minimal changes, push, open PR; CI smoke is a second validation gate
- Tag and release per MAINTENANCE.md after approval

Notes
- Containers: db (MySQL 8), wordpress (Apache/PHP 8.1), wpcli, adminer (http://localhost:8081)
- DB credentials are in dev/.env; default user/password is wordpress (dev only)
- No production code changes are included by this setup; it is optional for contributors
