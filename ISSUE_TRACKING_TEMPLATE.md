# GitHub Issue Templates for Monthly Booking Plugin Testing

## UI/UX Issue Template

```markdown
**Issue Title**: [UI/UX] [Brief description of the issue]

**Labels**: `ui-ux`, `bug`, `[severity-level]`

**Description**
A clear and concise description of the UI/UX issue.

**Environment**
- **Browser**: Chrome/Firefox/Safari [version]
- **Device**: Desktop/Tablet/Mobile
- **Viewport**: [width x height]
- **OS**: Windows/macOS/iOS/Android
- **Plugin Version**: v1.6.0

**Reproduction Steps**
1. Navigate to [URL]
2. Click on [element]
3. Observe [behavior]
4. Expected: [expected behavior]
5. Actual: [actual behavior]

**Expected Behavior**
Describe what should happen.

**Actual Behavior**
Describe what actually happens.

**Screenshots**
![Screenshot description](screenshot-url)

**Additional Context**
- Related code files: [file paths]
- Console errors: [if any]
- Network requests: [if relevant]

**Severity Assessment**
- [ ] **Critical**: Blocks core functionality, affects all users
- [ ] **Serious**: Significantly impacts user experience
- [ ] **Minor**: Cosmetic or edge case issue

**Acceptance Criteria**
- [ ] Issue is reproducible
- [ ] Fix addresses root cause
- [ ] No regression in related functionality
- [ ] Cross-browser compatibility maintained
```

## Accessibility Issue Template

```markdown
**Issue Title**: [a11y] [Brief description of the accessibility violation]

**Labels**: `accessibility`, `a11y`, `bug`, `[severity-level]`

**Description**
A clear description of the accessibility issue and its impact on users with disabilities.

**Environment**
- **Browser**: Chrome/Firefox/Safari [version]
- **Assistive Technology**: Screen reader/Keyboard navigation/Voice control
- **Device**: Desktop/Tablet/Mobile
- **Plugin Version**: v1.6.0

**WCAG 2.1 Criteria**
- **Level**: A/AA/AAA
- **Success Criterion**: [e.g., 2.1.1 Keyboard, 1.4.3 Contrast]
- **Guideline**: [e.g., Operable, Perceivable]

**Reproduction Steps**
1. Navigate to [URL]
2. Use [assistive technology/keyboard]
3. Attempt to [action]
4. Observe [behavior]

**Expected Behavior**
Describe the accessible behavior that should occur.

**Actual Behavior**
Describe the inaccessible behavior that occurs.

**Impact Assessment**
- **User Groups Affected**: [Screen reader users, keyboard users, etc.]
- **Severity**: [How severely this impacts accessibility]
- **Workaround Available**: [Yes/No - describe if yes]

**Technical Details**
- **ARIA Attributes**: [Missing or incorrect ARIA]
- **HTML Structure**: [Semantic issues]
- **Keyboard Behavior**: [Navigation problems]
- **Focus Management**: [Focus issues]

**Screenshots/Evidence**
![Accessibility tree screenshot](screenshot-url)
![Focus indicator issue](screenshot-url)

**Code References**
- **File**: [path/to/file.php]
- **Lines**: [line numbers]
- **Function**: [function name if applicable]

**Automated Testing Results**
- **axe-core Results**: [violation details]
- **Lighthouse Score**: [if available]
- **Other Tools**: [WAVE, etc.]

**Severity Assessment**
- [ ] **Critical**: Completely blocks access for users with disabilities
- [ ] **Serious**: Significantly impairs accessibility
- [ ] **Minor**: Minor accessibility improvement

**Acceptance Criteria**
- [ ] WCAG 2.1 AA compliance achieved
- [ ] Automated accessibility tests pass
- [ ] Manual testing with assistive technology successful
- [ ] No regression in existing accessibility features
```

## Performance Issue Template

```markdown
**Issue Title**: [Performance] [Brief description of the performance issue]

**Labels**: `performance`, `bug`, `[severity-level]`

**Description**
Description of the performance issue and its impact on user experience.

**Environment**
- **Browser**: Chrome/Firefox/Safari [version]
- **Device**: Desktop/Tablet/Mobile
- **Network**: [connection type if relevant]
- **Plugin Version**: v1.6.0

**Performance Metrics**
- **Load Time**: [seconds]
- **Time to Interactive**: [seconds]
- **Largest Contentful Paint**: [seconds]
- **Cumulative Layout Shift**: [score]

**Reproduction Steps**
1. Navigate to [URL]
2. Measure [metric] using [tool]
3. Observe [performance issue]

**Expected Performance**
- Load time: < 3 seconds
- Interactive: < 2 seconds
- Smooth animations: 60fps

**Actual Performance**
[Measured performance metrics]

**Screenshots/Evidence**
![Performance timeline](screenshot-url)
![Network waterfall](screenshot-url)

**Root Cause Analysis**
- **Suspected Cause**: [JavaScript/CSS/Images/Network]
- **Code Location**: [file paths]
- **Resource Size**: [if relevant]

**Severity Assessment**
- [ ] **Critical**: Severely impacts usability
- [ ] **Serious**: Noticeably slow performance
- [ ] **Minor**: Minor performance optimization

**Acceptance Criteria**
- [ ] Performance metrics meet targets
- [ ] No regression in other areas
- [ ] Cross-device performance verified
```

## Responsive Design Issue Template

```markdown
**Issue Title**: [Responsive] [Brief description of the responsive design issue]

**Labels**: `responsive`, `ui-ux`, `bug`, `[severity-level]`

**Description**
Description of how the layout or functionality breaks at certain viewport sizes.

**Environment**
- **Browser**: Chrome/Firefox/Safari [version]
- **Device**: Desktop/Tablet/Mobile
- **Viewport Size**: [width x height]
- **Orientation**: Portrait/Landscape

**Breakpoint Analysis**
- **Works at**: [viewport sizes]
- **Breaks at**: [viewport sizes]
- **Critical breakpoint**: [size where major issues occur]

**Reproduction Steps**
1. Navigate to [URL]
2. Resize viewport to [size]
3. Observe [layout issue]

**Expected Behavior**
Layout should adapt gracefully to all viewport sizes.

**Actual Behavior**
[Description of layout problems]

**Screenshots**
![Desktop view](screenshot-url)
![Tablet view](screenshot-url)
![Mobile view](screenshot-url)

**CSS Analysis**
- **Media Queries**: [relevant breakpoints]
- **Flexbox/Grid**: [layout method issues]
- **Overflow**: [horizontal scroll issues]

**Severity Assessment**
- [ ] **Critical**: Completely unusable on affected devices
- [ ] **Serious**: Significantly impaired usability
- [ ] **Minor**: Minor layout adjustment needed

**Acceptance Criteria**
- [ ] Layout works on all target devices
- [ ] No horizontal scrolling on mobile
- [ ] Touch targets meet minimum size requirements
- [ ] Content remains accessible and readable
```

## Issue Severity Guidelines

### Critical
- Completely blocks core functionality
- Makes the plugin unusable for all or most users
- Security vulnerabilities
- Complete accessibility barriers

### Serious
- Significantly impacts user experience
- Affects important functionality
- Causes confusion or frustration
- Major accessibility barriers

### Minor
- Cosmetic issues
- Edge case problems
- Minor usability improvements
- Small accessibility enhancements

## Issue Lifecycle

1. **Discovery**: Issue identified during testing
2. **Documentation**: Issue created with proper template
3. **Triage**: Severity and priority assigned
4. **Investigation**: Root cause analysis
5. **Resolution**: Fix implemented and tested
6. **Verification**: Issue resolution confirmed
7. **Closure**: Issue closed with verification notes
