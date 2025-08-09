# Reservations MVP Specification v1.7.0-alpha

## Overview
Admin-only reservation management system with CRUD operations and real-time calendar integration for the Monthly Booking Plugin.

## Features

### Database Schema
- **wp_monthly_reservations** table with proper schema for reservation data
- Half-open interval conflict detection [checkin, checkout)
- Basic pricing calculation (daily_rent × days)
- Guest information tracking (guest_name, guest_email)
- Status management (confirmed, canceled)

### API Endpoints
- `mbp_reservation_create` - Create new reservation with validation
- `mbp_reservation_update` - Update existing reservation
- `mbp_reservation_delete` - Delete reservation
- `mbp_reservation_list` - List reservations with pagination

### Admin Interface
- **Reservation List Page**: Display all reservations with pagination
- **Add/Edit Forms**: Comprehensive forms with validation and error handling
- **Real-time Updates**: Calendar automatically refreshes after CRUD operations
- **Accessibility Compliance**: ARIA labels, error messages, keyboard navigation

### Feature Flag
- `MB_FEATURE_RESERVATIONS_MVP` - Enable/disable MVP features
- Graceful degradation when disabled with informative message

## Technical Implementation

### Conflict Detection
Uses half-open interval logic where checkout date is exclusive:
```sql
WHERE (checkin_date < %checkout AND checkout_date > %checkin)
```

### Pricing Logic
Basic implementation for MVP:
- Base price = room daily_rent × number of nights
- Total price = base price (no campaign discounts in MVP)

### Calendar Integration
- Reservations appear alongside existing bookings in calendar
- Real-time updates via `window.MonthlyBookingCalendar.refresh()`
- Unified display through enhanced `mbp_get_bookings()` API

### Form Validation
- Client-side validation with immediate feedback
- Server-side validation with detailed error messages
- ARIA attributes for accessibility compliance
- Live error announcements for screen readers

## Database Schema

```sql
CREATE TABLE wp_monthly_reservations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    room_id BIGINT UNSIGNED NOT NULL,
    checkin_date DATE NOT NULL,
    checkout_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'confirmed',
    guest_name VARCHAR(190) NOT NULL,
    guest_email VARCHAR(190) NULL,
    base_daily_rate INT NULL,
    total_price INT NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_room_period (room_id, checkin_date),
    KEY idx_room_period2 (room_id, checkout_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Testing Strategy

### E2E Tests (Playwright)
- Reservation creation workflow
- Form validation and accessibility testing
- Conflict detection verification
- Calendar integration testing
- Edit/delete operations
- Feature flag functionality

### Manual Testing
- Admin interface navigation
- Form submission and validation
- Calendar real-time updates
- Accessibility with keyboard and screen reader
- Error handling scenarios

## Accessibility Features

### Form Accessibility
- Proper label associations with `for` attributes
- Required field indicators with visual and semantic markup
- Error messages linked via `aria-describedby`
- Live regions for dynamic error announcements
- Keyboard navigation support

### Calendar Integration
- Maintains existing v1.6.1 keyboard navigation
- Preserves roving tabindex implementation
- Screen reader announcements for month changes
- Focus management during navigation

## Security Considerations

### AJAX Security
- Nonce verification for all AJAX requests
- Capability checks (`manage_options` required)
- Input sanitization and validation
- SQL injection prevention with prepared statements

### Data Validation
- Server-side validation for all inputs
- Email format validation (when provided)
- Date range validation
- Guest name required validation

## Future Enhancements (Not in MVP)

### Campaign Integration
- Apply campaign discounts to reservation pricing
- Campaign-aware conflict detection
- Dynamic pricing based on stay duration

### Advanced Features
- Customer management integration
- Payment processing
- Email notifications
- Booking confirmation workflows
- Advanced reporting and analytics

## Compatibility

### WordPress Requirements
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+

### Plugin Compatibility
- Maintains all existing v1.6.1 functionality
- No breaking changes to existing APIs
- Backward compatible with existing calendar implementations

## Performance Considerations

### Database Optimization
- Proper indexing for reservation queries
- Efficient conflict detection queries
- Pagination for large reservation lists

### Frontend Performance
- Minimal JavaScript footprint
- Efficient AJAX operations
- Calendar refresh optimization

## Documentation

### User Documentation
- Admin interface usage guide
- Reservation workflow documentation
- Troubleshooting guide

### Developer Documentation
- API endpoint documentation
- Database schema reference
- Extension points for future development

## Version History

### v1.7.0-alpha
- Initial MVP implementation
- Basic CRUD operations
- Calendar integration
- Accessibility compliance
- E2E test suite
