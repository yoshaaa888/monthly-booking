# Monthly Booking - Ops Quickstart (Gitpod + CLI Utilities)

Self-heal (Gitpod)
- Kill and restart PHP server, expose port 8000, set HTTPS guard
```
lsof -ti :8000 | xargs -r kill -9
php -S 0.0.0.0:8000 -t wordpress >/tmp/php8000.log 2>&1 &
gp ports visibility 8000:public >/dev/null 2>&1 || true
URL="$(gp url 8000)"
grep -q "WP_HOME" wordpress/wp-config.php || \
sed -i "/require_once ABSPATH .*wp-settings.php/i define('WP_HOME', '$URL');\ndefine('WP_SITEURL', '$URL');\ndefine('FORCE_SSL_ADMIN', true);\nif (isset(\$_SERVER['HTTP_X_FORWARDED_PROTO']) && \$_SERVER['HTTP_X_FORWARDED_PROTO']==='https') \$_SERVER['HTTPS']='on';" wordpress/wp-config.php
```

WP-CLI Utilities
- Backfill room_id (idempotent; SQLite/MySQL)
```
npx wp-env run cli -- wp mb backfill-room-id
npx wp-env run cli -- wp mb backfill-room-id --table=wp_monthly_rooms --dry-run
```
- Seed rooms/reservations (idempotent)
```
npx wp-env run cli -- wp mb seed --rooms=3 --reservations=6
```
- DB migration (indexes)
```
npx wp-env run cli -- wp mb migrate
```

Smoke Test (PHP only; no E2E)
- Local
```
php tests/smoke/overlap.php
```
- CI: runs as part of Smoke workflow.

Hook Example (optional)
- Enable sample listener and save a reservation to emit one log line:
```
add_filter('mb_enable_price_example', '__return_true');
# Then create/update a reservation; tail /tmp/php8000.log on Gitpod
```

E2E Policy
- Full E2E is gated by label run-e2e on PRs. Default CI runs only Smoke.

# Monthly Booking WordPress Plugin

A comprehensive WordPress plugin for managing monthly property bookings with advanced campaign management, pricing calculations, and administrative interfaces.

## Features

### Core Booking System
- **Monthly booking calendar** with real-time availability display
- **Multi-tier pricing calculation** (SS/S/M/L plans based on stay duration)
- **Person-based fee calculations** with separate adult/children rates
- **Tax-separated billing** with proper consumption tax handling
- **Option bundle discounts** with tiered pricing (2 options: -¥500, 3+ options: +¥300 each)

### Campaign Management System
- **Room-based campaign assignments** with flexible date ranges
- **Automatic campaign application** based on booking conditions:
  - Early booking discounts (30+ days advance)
  - Immediate move-in discounts (within 7 days)
  - Flatrate campaigns (fixed pricing override)
- **Priority-based selection** (flatrate > highest percentage discount)
- **Campaign exclusivity** (one campaign per room per period)
- **Admin interface** for campaign assignment and management

### Administrative Features
- **WordPress-standard admin interface** with proper capability checks
- **Room management** with property associations
- **Campaign assignment UI** with modal dialogs and validation
- **Calendar view** with booking status indicators
- **AJAX-powered** real-time updates and validation

## File Structure
```
monthly-booking/
├── monthly-booking.php          # Main plugin file with activation hooks
├── includes/
│   ├── booking-logic.php        # Core pricing and calculation logic
│   ├── campaign-manager.php     # Campaign selection and application
│   ├── admin-ui.php            # WordPress admin interface
│   └── calendar-render.php     # Calendar display functionality
├── assets/
│   ├── estimate.js             # Frontend booking estimate logic
│   ├── admin.js               # Admin interface JavaScript
│   └── calendar.js            # Calendar interaction handling
├── tests/
│   └── test-campaign-logic.php # PHPUnit test suite
└── templates/                  # Frontend display templates
```

## Campaign System Architecture

### Campaign Types
1. **Percentage Discounts** - Apply percentage reduction to daily rent
2. **Fixed Amount Discounts** - Apply fixed yen amount reduction
3. **Flatrate Campaigns** - Override all pricing with fixed total amount

### Selection Logic
```php
// Priority order for campaign selection:
1. Flatrate campaigns (complete pricing override)
2. Highest percentage discount available
3. Highest fixed amount discount available
4. No campaign (standard pricing)
```

### Room Assignment Rules
- Each room can have multiple campaign assignments with different date ranges
- No overlapping periods allowed for the same room
- Campaigns must be active (`is_active = 1`) to be considered
- Date validation prevents conflicts during assignment

## Database Schema

### Core Tables
- `wp_monthly_rooms` - Room and property information
- `wp_monthly_campaigns` - Campaign master data
- `wp_monthly_room_campaigns` - Room-campaign assignments
- `wp_monthly_bookings` - Booking records
- `wp_monthly_customers` - Customer information
- `wp_monthly_options` - Available booking options

## Testing Infrastructure

### PHPUnit Test Suite
Run comprehensive campaign logic tests:
```bash
php run_campaign_tests.php
```

### Test Coverage
- Campaign selection algorithm validation
- Date boundary condition testing
- Priority-based selection verification
- Edge case handling (same-day bookings, long-term stays)
- Integration testing with booking logic

## Development Guidelines

### WordPress Standards
- Follows WordPress Coding Standards
- Uses proper sanitization and validation
- Implements capability-based access control
- Supports internationalization (i18n)

### Code Organization
- Separation of concerns between booking logic and UI
- AJAX endpoints with proper nonce verification
- Error handling with user-friendly messages
- Comprehensive logging for debugging

## Installation & Setup

1. Upload plugin files to `/wp-content/plugins/monthly-booking/`
2. Activate plugin through WordPress admin
3. Configure room and property data
4. Set up campaigns and assignments
5. Add booking forms to frontend pages using shortcodes

## Shortcodes
- `[monthly_booking_estimate]` - Booking estimate form
- `[monthly_booking_calendar]` - Availability calendar display

## Configuration

### Hardcoded Values (Recommended for Admin Configuration)
Current hardcoded pricing values that should be made configurable:
- Cleaning fee: ¥38,500
- Key fee: ¥11,000  
- Daily utilities: SS=¥2,500, others=¥2,000
- Person additional rates: adults ¥900/day, children ¥450/day
- Person utilities: adults ¥200/day, children ¥100/day

## Support & Maintenance

### Logging
- WordPress debug.log integration
- Campaign selection decision logging
- Booking calculation audit trail

### Performance
- Optimized database queries with proper indexing
- AJAX operations with loading states
- Efficient campaign selection algorithms

---

**Version**: 1.5.8+  
**WordPress Compatibility**: 5.0+  
**PHP Compatibility**: 7.4+  
**Testing**: Comprehensive PHPUnit suite included

