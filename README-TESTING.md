# Monthly Booking Calendar - Testing Documentation

## Overview
This document provides comprehensive testing procedures for the Monthly Booking Plugin's 6-month calendar feature.

## Test Environment Setup

### Prerequisites
- WordPress Local environment running at `http://t-monthlycampaign.local`
- Node.js 16+ installed
- Monthly Booking Plugin v2.2-final activated

### Installation
```bash
# Install test dependencies
npm install

# Install Playwright browsers
npx playwright install

# Setup test data (optional)
npm run test:setup
```

### Test Data Setup
The test suite includes automated test data creation:
- 3 test rooms (Room A, B, C)
- Sample bookings with cleaning buffer scenarios
- Campaign data with date overlaps
- Various booking conflict scenarios

## Running Tests

### Full Test Suite
```bash
# Run all tests with HTML report
npm run test:e2e

# Run tests in headed mode (visible browser)
npm run test:e2e:headed

# Debug mode (step through tests)
npm run test:e2e:debug
```

### Specific Test Categories
```bash
# Run only calendar display tests
npx playwright test --grep "Calendar displays"

# Run only responsive design tests
npx playwright test --grep "Responsive design"

# Run only accessibility tests
npx playwright test --grep "accessibility"
```

## Test Coverage

### 1. Calendar Display Tests
- ✅ 6-month consecutive display
- ✅ Japanese month formatting (年月)
- ✅ Calendar structure and legend
- ✅ Day grid layout with weekday headers

### 2. Room Selection Tests
- ✅ Dropdown population with active rooms
- ✅ Room switching via AJAX
- ✅ Specific room_id attribute handling
- ✅ Default room selection behavior

### 3. Booking Status Tests
- ✅ Available dates (〇 symbol)
- ✅ Booked dates (× symbol)
- ✅ Cleaning buffer logic (5-day periods)
- ✅ Status priority: booked > campaign > available

### 4. Campaign Integration Tests
- ✅ Campaign dates (△ symbol)
- ✅ Tooltip display with campaign names
- ✅ Room-specific vs global campaigns
- ✅ Campaign date range calculations

### 5. AJAX Functionality Tests
- ✅ Room selection triggers calendar update
- ✅ Loading states during AJAX requests
- ✅ Error handling for failed requests
- ✅ Response time measurement (<3 seconds)

### 6. Responsive Design Tests
- ✅ Mobile breakpoint (375px)
- ✅ Tablet breakpoint (768px)
- ✅ Desktop layout (1280px+)
- ✅ No horizontal scrolling on mobile

### 7. Accessibility Tests
- ✅ Keyboard navigation (Tab key)
- ✅ ARIA labels for screen readers
- ✅ Focus indicators on calendar days
- ✅ Semantic HTML structure

### 8. Performance Tests
- ✅ Initial page load time (<5 seconds)
- ✅ AJAX response time (<3 seconds)
- ✅ Calendar rendering performance
- ✅ Memory usage monitoring

## Test Results and Reporting

### HTML Report
After running tests, view the detailed HTML report:
```bash
npx playwright show-report
```

### Screenshots and Videos
- Failed tests automatically capture screenshots
- Videos recorded for debugging complex failures
- Results stored in `test-results/` directory

### Performance Metrics
Tests measure and report:
- Page load times
- AJAX response times
- Calendar rendering duration
- Memory usage patterns

## Expected Test Results

### Success Criteria
- All calendar display tests pass
- Room selection works across all browsers
- Cleaning buffer logic correctly implemented
- Campaign tooltips display properly
- AJAX updates complete within performance targets
- Responsive design works on all breakpoints
- Accessibility standards met (ARIA, keyboard navigation)

### Common Failure Scenarios
1. **Calendar not displaying**: Check WordPress environment and plugin activation
2. **AJAX errors**: Verify nonce generation and endpoint registration
3. **Responsive layout issues**: Check CSS media queries and viewport settings
4. **Accessibility failures**: Ensure ARIA labels and tabindex attributes present

## Debugging Failed Tests

### Browser Developer Tools
```bash
# Run tests with browser dev tools open
npx playwright test --headed --debug
```

### Test Isolation
```bash
# Run single test file
npx playwright test tests/e2e/calendar.spec.js

# Run specific test
npx playwright test --grep "Room selection dropdown"
```

### Environment Issues
If tests fail due to environment issues:
1. Verify WordPress Local is running
2. Check plugin activation status
3. Ensure test pages exist (`/monthly-calendar/`)
4. Verify database connectivity

## Continuous Integration

### GitHub Actions Integration
The test suite can be integrated with GitHub Actions:
```yaml
name: E2E Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
      - run: npm ci
      - run: npx playwright install
      - run: npm run test:e2e
```

## Test Maintenance

### Updating Test Data
```bash
# Clean existing test data
npm run test:cleanup

# Setup fresh test data
npm run test:setup
```

### Adding New Tests
1. Create test files in `tests/e2e/`
2. Follow existing naming conventions
3. Use page object patterns for reusability
4. Include proper assertions and error handling

## Performance Benchmarks

### Target Metrics
- Initial page load: <5 seconds
- AJAX calendar update: <3 seconds
- Calendar rendering: <1 second
- Memory usage: <50MB per page

### Monitoring
Tests automatically measure and report performance metrics. Failures occur if targets are exceeded.

## Browser Compatibility

### Tested Browsers
- ✅ Chrome (Desktop & Mobile)
- ✅ Firefox (Desktop)
- ✅ Safari (Desktop & Mobile)
- ✅ Edge (Desktop)

### Known Issues
- Safari may have slight timing differences in AJAX requests
- Mobile browsers may require longer timeouts for complex operations

## Support and Troubleshooting

### Common Issues
1. **Test timeouts**: Increase timeout values in playwright.config.js
2. **Element not found**: Check CSS selectors and DOM structure
3. **AJAX failures**: Verify WordPress nonce and endpoint configuration
4. **Responsive issues**: Test viewport settings and CSS media queries

### Getting Help
- Check test output logs for detailed error messages
- Use `--debug` flag for step-by-step test execution
- Review screenshots and videos in test-results/
- Verify WordPress environment configuration

---

**Version**: 2.2-final  
**Last Updated**: August 8, 2025  
**Test Framework**: Playwright 1.40+  
**Node.js**: 16+ required
