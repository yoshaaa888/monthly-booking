# Monthly Booking v1.0.0 — Maintenance & Support Guide

Scope
- This document defines support procedures, triage workflow, log collection, and hotfix/release operations for the Monthly Booking plugin v1.0.0.
- Audience: Operators, maintainers, and on-call support.

Support Channels
- GitHub Issues: Open an issue in yoshaaa888/monthly-booking with a clear title and severity label.
- Labels: bug, performance, a11y, documentation, question, needs-repro, needs-info, high-impact, low-impact.
- Triage cadence: Daily on weekdays; within 24h for high-impact items affecting production operations.

Triage Workflow
1) Intake
- Database migrations: see docs/DB_MIGRATIONS.md for running and rollback procedures.

   - Verify plugin version (Plugins → Monthly Room Booking shows 1.0.0).
   - Capture environment (WordPress version, PHP version, DB version).
   - Identify operation context (Calendar, Rooms, Campaigns, Booking).
2) Reproduce
   - Follow steps in the issue, capture actual vs expected behavior.
   - If data-related, export minimal SQL to reproduce (anonymized).
3) Classify
   - Severity:
     - Critical: Calendar unusable, CRUD blocked, data corruption risk
     - High: Operational degradation, wrong prices, assignment failure
     - Medium: UX issues, visibility glitches, non-blocking errors
     - Low: Cosmetic, docs, internal improvements
   - Impact:
     - High-impact: 50+ rooms affected or core workflows blocked
     - Low-impact: Isolated, workaround available
4) Decide
   - Hotfix on main (patch bump) for Critical/High with user confirmation.
   - Batch into minor release for Medium/Low.

Log Collection
- Browser console/network logs when errors occur (export HAR if possible).
- WordPress debug log if enabled (wp-content/debug.log).
- AJAX response payloads (status code, JSON body).
- Screenshot or screen recording of the calendar/operation flow showing the issue.
- SQL snapshot for target rows (rooms, reservations, campaigns, assignments) where applicable.

Common Issues & Solutions
- Symbols not matching expectations
  - Check reservation intervals [checkin, checkout) and cleaning buffer [checkout, +5d]
  - Verify room is_active and campaign assignment is_active for target date
  - Confirm timezone in WordPress General settings
- Right panel not updating
  - Reload the month view; ensure admin session is valid
  - Inspect console network requests for 4xx/5xx or nonce errors
- Campaign assignment conflicts
  - Validate period overlaps and contract types; ensure the room is active
  - Review server responses for validation messages
- Price anomalies on booking recalculation
  - Ensure only one discount applies; re-run “Recalculate”
  - Capture and attach the response payload in the issue
- Activation or upgrade issues
  - Verify PHP 7.4+ (8.x compatible), WP 6.x
  - Re-activate the plugin; check wp-content/debug.log if enabled


Known Limitations (v1.0.0)
- Accessibility: Keyboard navigation and announcements can be expanded.
- Performance: At 450-room scale, further virtualization/pagination and query/index tuning may be needed.
- Sample data: Uses CURDATE()—intended for quick demonstrations near the current date.

Hotfix Procedure
- See also: docs/DEV_ENV.md for Docker-based local setup and Emergency Hotfix Flow
- Branch: devin/{epoch}-hotfix-brief-slug
- Changes:
  - Keep scope minimal and risk-contained.
  - Update version in monthly-booking.php (e.g., 1.0.1).
  - Update CHANGELOG (if present) and release notes draft.
- Testing:
  - Prefer Docker dev stack: follow docs/DEV_ENV.md (make up, make reset-db, make activate-plugin, make import-sample).
  - PHP lint for changed PHP files.
  - Smoke scripts (scripts/smoke_local.sh) if feasible.
  - Manual verification for the impacted flows.
- PR:
  - Title: fix(...): one-line summary
  - Description: root cause, fix, verification steps, backout plan.
  - Await CI smoke; request review.
- Release:
  - Create GitHub Release (v1.0.1), attach rebuilt ZIP, include notes (overview, changes, risks, verification).

Operational Playbooks
- Calendar symptoms:
  - Wrong symbols: Verify reservation intervals [checkin, checkout), cleaning buffer [checkout, +5d], and campaign assignment is_active and period.
  - Right panel stale: Reload month; confirm admin session and nonce validity; check console for network errors.
- Campaign assignment:
  - Conflicts or save failure: Check period overlaps and status; ensure rooms are active; review API responses for validation messages.
- Booking recalculation:
  - Price anomalies: Validate discount precedence and ensure no double-discount; re-run “Recalculate” and capture response JSON.

Release Maintenance
- Packaging:
  - Build clean ZIP (exclude .git, .github, docs, tests, node_modules).
  - Verify ZIP has a single “monthly-booking/” root folder with runtime files.
- Notes:
  - Include overview, main changes, compatibility, known issues, verification summary, rollback.
- Artifacts:
  - Attach ZIP, link INSTALLATION.md and QUICK_START.md, and sample-data.sql.

SLAs (suggested)
- Critical: Triage < 4h; Hotfix PR < 24h
- High: Triage < 1 business day; Fix plan < 3 days
- Medium: Next minor release
- Low: Backlog grooming; schedule per roadmap

Contact and Ownership
- Technical owner: @yoshaaa888
- Maintainer: Devin AI (assists via PRs and CI monitoring)
