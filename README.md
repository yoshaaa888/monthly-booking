# Monthly Booking WordPress Plugin

A WordPress plugin for managing monthly property bookings with calendar display, pricing logic, and comprehensive campaign management.

## Features
- Monthly booking calendar with availability display
- Multi-tier pricing calculation (SS/S/M/L plans)
- Room-based campaign management system
- Automatic campaign application (early booking, immediate move-in, flatrate)
- Option bundle discounts and person-based fees
- Admin interface using WordPress standards
- Comprehensive PHPUnit testing suite

## Structure
- monthly-booking.php - Main plugin file
- includes/ - Core functionality (booking-logic.php, campaign-manager.php, admin-ui.php)
- assets/ - Frontend JavaScript and CSS
- tests/ - PHPUnit test suite
- templates/ - Frontend templates

## Campaign System
- Room-specific campaign assignments with date ranges
- Priority-based selection (flatrate > highest discount)
- Automatic eligibility validation based on booking dates
- Integration with existing pricing calculation system

## Testing
Run PHPUnit tests to validate campaign auto-application logic:
```bash
php run_campaign_tests.php
```

## Development
This plugin follows WordPress coding standards and includes comprehensive testing infrastructure for campaign management functionality.

