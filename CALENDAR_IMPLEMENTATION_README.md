# Monthly Booking Calendar Implementation

## Overview
This document describes the enhanced 6-month calendar feature implementation for the Monthly Booking Plugin.

## New Features

### 1. Enhanced Calendar Shortcode
- **Shortcode**: `[monthly_booking_calendar]`
- **Attributes**:
  - `room_id` (optional): Specific room ID to display
  - `months` (optional): Number of months to display (default: 6)

### 2. Room Selection
- Dropdown selector when `room_id` not specified
- AJAX-powered room switching without page reload
- Automatic selection of first available room

### 3. 6-Month Display
- Consecutive 180-day calendar view
- Month-by-month grid layout
- Japanese date formatting (年月日)

### 4. Color-Coded Availability
- **Green (〇)**: Available dates
- **Red (×)**: Booked dates or cleaning buffer periods
- **Orange (△)**: Campaign-eligible dates with tooltips

### 5. Cleaning Buffer Logic
- 5-day buffer before and after each booking
- Automatic conflict detection
- Half-open interval booking logic [checkin, checkout)

### 6. Campaign Integration
- Room-specific campaign display
- Global campaign fallback
- Hover tooltips with campaign names
- Priority system: booked > campaign > available

### 7. Accessibility Features
- ARIA labels for screen readers
- Keyboard navigation support
- Focus indicators
- Semantic HTML structure

### 8. Responsive Design
- Mobile-optimized layout for ≤768px screens
- Touch-friendly interaction areas
- Scalable font sizes and spacing

## File Structure

```
includes/
├── calendar-api.php        # Data retrieval functions
├── calendar-utils.php      # Date/buffer calculation utilities
├── calendar-render.php     # Enhanced HTML generation (modified)
└── campaign-manager.php    # Fixed stdClass::$id warnings

assets/
└── calendar.css           # Enhanced styles with new features
```

## API Functions

### MonthlyBooking_Calendar_API
- `mbp_get_rooms()`: Retrieve active rooms
- `mbp_get_bookings($room_id, $from, $to)`: Get bookings for date range
- `mbp_get_campaign_days($room_id, $from, $to)`: Get campaign-eligible dates
- `get_global_campaigns($from, $to)`: Get global campaigns as fallback

### MonthlyBooking_Calendar_Utils
- `date_ranges_overlap()`: Check date range conflicts
- `calculate_cleaning_buffer()`: Calculate 5-day buffer periods
- `get_wp_timezone_date()`: WordPress timezone-aware date handling
- `format_japanese_date()`: Japanese date formatting
- `get_day_status()`: Determine day availability status
- `generate_6_month_dates()`: Generate 180-day date array
- `group_dates_by_month()`: Group dates by month for display

## Usage Examples

### Basic Calendar Display
```php
[monthly_booking_calendar]
```
Displays calendar with room selection dropdown.

### Specific Room Calendar
```php
[monthly_booking_calendar room_id="123"]
```
Displays calendar for room ID 123 only.

### Custom Month Count
```php
[monthly_booking_calendar months="3"]
```
Displays 3-month calendar view.

## Database Integration

### Required Tables
- `wp_monthly_rooms`: Room master data
- `wp_monthly_bookings`: Booking records
- `wp_monthly_campaigns`: Campaign definitions
- `wp_monthly_room_campaigns`: Room-campaign assignments

### Query Optimization
- Indexed queries on date ranges
- Efficient JOIN operations for campaign data
- Error logging for debugging

## AJAX Functionality

### Room Selection
- Endpoint: `wp_ajax_mbp_load_calendar`
- Nonce verification: `mbp_calendar_nonce`
- Dynamic calendar content loading

## CSS Classes

### Calendar Structure
- `.monthly-booking-calendar-container`: Main wrapper
- `.calendar-month`: Individual month container
- `.calendar-grid`: Day grid layout
- `.calendar-day`: Individual day cell

### Status Classes
- `.available`: Available dates (green)
- `.booked`: Booked/cleaning dates (red)
- `.campaign`: Campaign dates (orange)
- `.today`: Current date highlight

### Interactive Elements
- `.room-selector`: Room selection dropdown
- `.campaign-tooltip`: Campaign hover tooltip
- `.loading`: Loading state indicator

## Accessibility Compliance

### ARIA Support
- `aria-label`: Date and status announcements
- `tabindex="0"`: Keyboard navigation
- Semantic HTML structure

### Keyboard Navigation
- Tab navigation through calendar days
- Focus indicators for selected dates
- Screen reader compatible

## Browser Compatibility

### Supported Browsers
- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+

### Mobile Support
- iOS Safari 12+
- Chrome Mobile 70+
- Responsive breakpoint: 768px

## Performance Considerations

### Database Queries
- Optimized date range queries
- Indexed foreign key lookups
- Error handling and logging

### Frontend Performance
- Minimal DOM manipulation
- Efficient AJAX requests
- CSS-based styling (no JavaScript animations)

## Testing Scenarios

### Booking Conflicts
- Single-day bookings
- Multi-day bookings
- Consecutive bookings
- Cleaning buffer overlaps

### Campaign Display
- Room-specific campaigns
- Global campaign fallback
- Campaign-booking conflicts
- Tooltip functionality

### Edge Cases
- Month boundaries (Dec→Jan)
- Year transitions
- Leap year handling
- Timezone considerations

### Responsive Testing
- 768px breakpoint
- Mobile touch interactions
- Tablet landscape/portrait

## Troubleshooting

### Common Issues
1. **Empty Calendar**: Check room data and database connectivity
2. **Missing Campaigns**: Verify campaign table data and date ranges
3. **AJAX Errors**: Check nonce verification and endpoint registration
4. **Styling Issues**: Verify CSS file loading and cache clearing

### Debug Logging
- PHP errors logged to WordPress debug.log
- AJAX response validation
- Database query error handling

## Future Enhancements

### Planned Features
- Calendar export functionality
- Booking creation from calendar
- Advanced filtering options
- Multi-room comparison view

### Extensibility
- Hook system for custom status types
- Filter hooks for date formatting
- Action hooks for calendar events

## Support

For technical support or feature requests, refer to the plugin documentation or contact the development team.

---

**Version**: 2.2-final  
**Last Updated**: August 8, 2025  
**Compatibility**: WordPress 5.0+, PHP 7.4+
