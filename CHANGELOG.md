# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.6.0] - 2025-08-09

### Added
- **ARIA Grid Structure**: Implemented comprehensive ARIA roles for calendar accessibility
  - Added `role="grid"` to calendar container with proper `aria-labelledby`
  - Structured weekday headers with `role="columnheader"`
  - Applied `role="gridcell"` to calendar date cells
  - Removed inappropriate `role="application"` from root container

- **Keyboard Navigation**: Full keyboard accessibility for calendar interaction
  - Implemented roving tabindex pattern (single `tabindex="0"` at a time)
  - Arrow key navigation (Up/Down/Left/Right) with `preventDefault()`
  - Home/End keys for first/last date navigation
  - PageUp/PageDown for month navigation with focus restoration to same weekday column
  - Tab/Shift+Tab exits grid (no focus trap)
  - Event delegation pattern for performance optimization

- **Live Region Announcements**: Screen reader support for dynamic content
  - Single `aria-live="polite" aria-atomic="true"` region for announcements
  - Month change announcements with 500ms throttling to prevent spam
  - Internationalized announcement patterns: "YYYY年M月を表示"
  - Focused announcements (month changes only, not excessive calendar updates)

- **Tooltip Accessibility**: Enhanced campaign tooltip interaction
  - Proper `aria-describedby` relationships with unique tooltip IDs
  - `role="tooltip"` attributes for screen reader recognition
  - `aria-hidden` state management for visibility control
  - Focus/blur and Escape key support for keyboard users
  - Single tooltip open policy with automatic dismissal
  - Mobile touch support with event delegation

- **Screen Reader Support**: Comprehensive accessibility infrastructure
  - `.sr-only` CSS class for screen-reader-only content
  - Proper ARIA labeling throughout calendar interface
  - Enhanced focus management and visual indicators

### Changed
- **Calendar Structure**: Improved DOM hierarchy for accessibility compliance
  - Proper grid row structure with semantic markup
  - Enhanced ARIA labeling using `aria-labelledby` instead of `aria-label`
  - Maintained existing visual design while improving semantic structure

- **JavaScript Performance**: Optimized event handling
  - Vanilla JavaScript implementation for keyboard navigation (removed jQuery dependency)
  - Event delegation pattern reduces memory footprint
  - Throttled announcements prevent excessive screen reader verbosity

### Technical Details
- **WCAG 2.1 AA Compliance**: All implementations follow Web Content Accessibility Guidelines
- **No Breaking Changes**: Existing functionality preserved, smoke tests remain green
- **Progressive Enhancement**: Accessibility features enhance existing interface without disruption
- **Cross-browser Compatibility**: Tested keyboard navigation and ARIA support

### Files Modified
- `includes/calendar-render.php` - ARIA structure and live regions
- `assets/calendar.js` - Keyboard navigation and announcement logic  
- `assets/calendar.css` - Focus indicators and screen reader styles

### Testing
- All accessibility tests pass with enhanced keyboard navigation
- Post-merge smoke tests maintain green status
- Screen reader compatibility verified
- Keyboard-only navigation fully functional

---

## [1.5.x] - Previous Versions
- Priority 1-4 fixes:料金データ一元化, JavaScript安定化, キャンペーン設定UI改善, 不要ページ削除
- Core booking functionality and calendar display
- Campaign management and pricing logic
