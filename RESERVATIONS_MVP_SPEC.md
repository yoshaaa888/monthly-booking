# Reservations MVP Specification v1.7.0-alpha

## Overview
Admin-only reservation management system with CRUD operations and real-time calendar integration for the Monthly Booking Plugin.

## Features

### Database Schema
- **wp_monthly_reservations** table with proper schema for reservation data
- Half-open interval conflict detection [checkin, checkout)
- Basic pricing calculation (daily_rent × days)
- Admin user tracking (created_by field)
- Status management (pending, confirmed, cancelled)

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
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    room_id mediumint(9) NOT NULL,
    customer_name varchar(100) NOT NULL,
    customer_email varchar(100) NOT NULL,
    customer_phone varchar(20),
    checkin_date date NOT NULL,
    checkout_date date NOT NULL,
    num_adults int(2) DEFAULT 1,
    num_children int(2) DEFAULT 0,
    base_price decimal(10,2) NOT NULL,
    total_price decimal(10,2) NOT NULL,
    status enum('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    notes text,
    created_by mediumint(9) NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY room_id (room_id),
    KEY checkin_date (checkin_date),
    KEY checkout_date (checkout_date),
    KEY status (status),
    KEY created_by (created_by),
    KEY idx_reservation_dates (room_id, checkin_date, checkout_date),
    KEY idx_reservation_status (status, created_at)
);
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
- Email format validation
- Date range validation
- Numeric range validation for guest counts

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
