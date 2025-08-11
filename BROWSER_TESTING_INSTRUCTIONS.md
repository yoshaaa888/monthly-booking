# Browser Testing Instructions - Enhanced Visual Verification Guide
## Monthly Booking Plugin v2.2-final Priority 1-4 Verification

**Environment:** http://t-monthlycampaign.local/wp-admin/  
**Login Credentials:** t-monthly-admin / t-monthly  
**Date:** August 8, 2025  
**Status:** Ready for User Execution with Visual Confirmation Steps

---

## 🎯 Enhanced Testing Overview

This guide provides detailed browser-based verification tests with visual confirmation steps, screenshot requirements, and comprehensive error detection procedures to confirm all Priority 1-4 fixes are working correctly.

### Testing Approach:
- **Visual Confirmation:** Each test includes specific visual elements to verify
- **Screenshot Documentation:** Required screenshots for each priority test
- **Error Detection:** Specific error patterns to watch for
- **Success Criteria:** Clear pass/fail indicators with visual evidence

### Required Tools:
- **Web Browser:** Chrome, Firefox, or Safari (latest version)
- **Developer Tools:** F12 for console monitoring
- **Screenshot Capability:** Built-in browser tools or system capture
- **Notepad:** For recording observations and results

---

## 🔴 Priority 1: 料金データ一元化 Enhanced Browser Testing

### **Test 1.1: Visual Verification of Default Daily Rent Section Removal**

**Objective:** Confirm "デフォルト日額賃料" section is completely removed with visual evidence

**Detailed Steps:**

1. **Initial Access and Login**
   - Navigate to: `http://t-monthlycampaign.local/wp-admin/`
   - **Login Credentials:**
     - Username: `t-monthly-admin`
     - Password: `t-monthly`
   - **Visual Confirmation:** WordPress dashboard loads successfully
   - **Screenshot Required:** `01_wordpress_dashboard.png`

2. **Navigate to Fee Settings**
   - Click **Monthly Room Booking** in left sidebar
   - **Visual Confirmation:** Submenu expands showing 7 items
   - Click **料金設定** (Fee Settings)
   - **Visual Confirmation:** Fee settings page loads with title "料金設定"
   - **Screenshot Required:** `02_fee_settings_page_full.png`

3. **Systematic Page Content Inspection**
   - **Scroll from top to bottom** of the entire page
   - **Look for:** Any section titled "デフォルト日額賃料" (Default Daily Rent)
   - **Expected Result:** This section should NOT exist anywhere
   - **Visual Confirmation:** Only these sections should be visible:
     - 基本料金 (Basic Fees) - with input fields
     - 光熱費 (Utilities) - with input fields
     - 追加人数料金 (Additional Person Fees) - with input fields
     - オプション割引設定 (Option Discount Settings) - with checkboxes/dropdowns

4. **Document Section Layout**
   - **Count total sections:** Should be exactly 4 sections
   - **Verify section headers:** Each should have clear Japanese titles
   - **Check for orphaned fields:** No input fields without clear section association
   - **Screenshot Required:** `03_fee_sections_detail.png`

**Pass Criteria:** ✅ No "デフォルト日額賃料" section found, only 4 expected sections visible

**Visual Success Indicators:**
- **Good:** Clean, organized layout with 4 distinct sections
- **Good:** All input fields have clear labels and purposes
- **Bad:** Any reference to "デフォルト" or "Default" in section headers
- **Bad:** Orphaned input fields or unclear section organization

### **Test 1.2: Unified Fee Management Functionality Verification**

**Objective:** Verify fee modifications save correctly through unified system with visual confirmation

**Detailed Steps:**

1. **Select Test Fee for Modification**
   - **Recommended:** Choose "清掃費" (Cleaning Fee) if available
   - **Alternative:** Any numerical input field in fee sections
   - **Record current value:** Write down existing value
   - **Example:** "Current cleaning fee: 3000 yen"

2. **Perform Fee Modification**
   - **Click in the input field** to focus
   - **Clear existing value** and enter new value
   - **Test value:** Add 100 to current value (e.g., 3000 → 3100)
   - **Visual Confirmation:** New value displays in field
   - **Screenshot Required:** `04_fee_modification_before_save.png`

3. **Save Changes and Verify**
   - **Scroll to bottom** of page to find save button
   - **Click:** "設定を保存" (Save Settings) button
   - **Visual Confirmation:** Look for success message
   - **Expected message:** Green background with success text
   - **Screenshot Required:** `05_fee_save_success_message.png`

4. **Test Persistence with Page Refresh**
   - **Refresh page:** Press F5 or Ctrl+R
   - **Wait for complete reload:** Ensure page fully loads
   - **Navigate back to same input field**
   - **Visual Confirmation:** Modified value should still be displayed
   - **Screenshot Required:** `06_fee_persistence_verification.png`

5. **Restore Original Value**
   - **Change value back** to original amount
   - **Save again** to maintain test environment
   - **Confirm restoration:** Original value is restored

**Pass Criteria:** ✅ Fee modifications save and persist correctly after page refresh

**Visual Success Indicators:**
- **Good:** Clear success message after saving
- **Good:** Modified value persists after page refresh
- **Good:** Save button responds immediately when clicked
- **Bad:** No success message or error message appears
- **Bad:** Value reverts to original after refresh
- **Bad:** Save button appears unresponsive

---

## 🟡 Priority 2: JavaScript安定化 Enhanced Browser Testing

### **Test 2.1: Calendar Error Handling and System Stability**

**Objective:** Verify JavaScript errors don't crash the system and show user-friendly messages

**Detailed Steps:**

1. **Navigate to Calendar Interface**
   - Click **Monthly Room Booking** → **予約カレンダー**
   - **Visual Confirmation:** Calendar page loads with interface
   - **Expected elements:** Calendar grid, room selection, navigation
   - **Screenshot Required:** `07_calendar_page_initial_load.png`

2. **Prepare Error Monitoring**
   - **Press F12** to open browser Developer Tools
   - **Click Console tab** in developer tools
   - **Clear existing messages:** Click trash/clear icon
   - **Position windows:** Arrange so you can see both page and console
   - **Screenshot Required:** `08_console_monitoring_setup.png`

3. **Test Room Selection Stability**
   - **Look for room selection dropdown** on calendar page
   - **If dropdown exists:** Try selecting different rooms rapidly
   - **Monitor console:** Watch for red error messages
   - **Expected behavior:** System remains responsive
   - **Visual Confirmation:** Page doesn't freeze or become unresponsive

4. **Test Error Message Display**
   - **If errors occur:** Look for user-facing error messages
   - **Expected language:** Japanese error messages for users
   - **Examples of good messages:**
     - "エラーが発生しました" (An error occurred)
     - "カレンダーの読み込みに失敗しました" (Calendar loading failed)
   - **Screenshot Required:** `09_error_message_display.png` (if errors occur)

5. **Test System Recovery**
   - **After any errors:** Try using calendar functions again
   - **Expected behavior:** System should continue working
   - **Test actions:** Room selection, calendar navigation
   - **Visual Confirmation:** Functions remain accessible

**Pass Criteria:** ✅ No system crashes from JavaScript errors, graceful error handling

**Console Error Analysis:**
- **Acceptable:** Yellow warnings or blue info messages
- **Concerning:** Red uncaught errors that stop execution
- **Good:** Error messages followed by recovery attempts
- **Bad:** Continuous error loops or page freezing

### **Test 2.2: Room Selection Dropdown Functionality**

**Objective:** Verify room selection works smoothly without JavaScript errors

**Detailed Steps:**

1. **Locate Room Selection Interface**
   - **Look for dropdown** labeled "部屋を選択" or similar
   - **If not visible:** Check if rooms are pre-selected
   - **Alternative:** Look for room tabs or buttons
   - **Visual Confirmation:** Room selection interface is present

2. **Test Dropdown Population**
   - **Click on room selection dropdown**
   - **Expected result:** Dropdown opens with room options
   - **Example options:** "Room 101", "Room 102", "Demo Room"
   - **If empty:** May indicate test data issue
   - **Screenshot Required:** `10_room_dropdown_options.png`

3. **Test Room Selection Process**
   - **Select first room** from dropdown
   - **Visual Confirmation:** Calendar content updates
   - **Expected behavior:** Brief loading indicator, then new data
   - **Monitor console:** No red error messages during selection

4. **Test Multiple Room Switches**
   - **Select 3-4 different rooms** in sequence
   - **Expected behavior:** Smooth transitions between rooms
   - **Performance check:** No significant delays (< 3 seconds)
   - **Visual Confirmation:** Calendar data changes for each room

5. **Test AJAX Functionality**
   - **Open Network tab** in Developer Tools
   - **Select different room** while monitoring network
   - **Expected:** AJAX requests appear (XHR type)
   - **Status codes:** Should be 200 (success)
   - **Screenshot Required:** `11_ajax_network_monitoring.png`

**Pass Criteria:** ✅ Room selection works without errors, calendar updates correctly

**Performance Indicators:**
- **Good:** Room selection responds within 1-2 seconds
- **Good:** Loading indicators appear during transitions
- **Good:** Calendar data visibly changes for each room
- **Bad:** Long delays (> 5 seconds) or timeouts
- **Bad:** Calendar doesn't update or shows same data for all rooms

---

## 🟡 Priority 3: キャンペーン設定UI改善 Enhanced Browser Testing

### **Test 3.1: 180-Day Date Limits Visual Verification**

**Objective:** Verify campaign dates are limited to 180 days with visual confirmation of constraints

**Detailed Steps:**

1. **Navigate to Campaign Settings**
   - Click **Monthly Room Booking** → **キャンペーン設定**
   - **Visual Confirmation:** Campaign management page loads
   - **Expected elements:** Campaign list, add button, settings interface
   - **Screenshot Required:** `12_campaign_settings_page.png`

2. **Access Campaign Creation Interface**
   - **Look for button:** "新規キャンペーン追加" (Add New Campaign) or similar
   - **Click the button** to open campaign creation
   - **Expected result:** Form or modal dialog appears
   - **Visual Confirmation:** Campaign creation interface is displayed
   - **Screenshot Required:** `13_campaign_creation_form.png`

3. **Test Start Date Constraints**
   - **Click on 開始日 (Start Date) field**
   - **Expected behavior:** Date picker opens
   - **Visual verification:**
     - **Minimum date:** Today should be selectable
     - **Past dates:** Should be disabled/grayed out
     - **Future limit:** 180 days from today should be maximum
   - **Calculate 180-day limit:** Today (Aug 8, 2025) + 180 days = Feb 4, 2026
   - **Screenshot Required:** `14_start_date_picker_limits.png`

4. **Test End Date Constraints**
   - **Click on 終了日 (End Date) field**
   - **Expected behavior:** Date picker opens
   - **Visual verification:**
     - **Maximum date:** 180 days from today
     - **Disabled dates:** Beyond 180-day limit should be unselectable
     - **Visual indicators:** Disabled dates grayed out or not clickable
   - **Screenshot Required:** `15_end_date_picker_limits.png`

5. **Test Date Picker Interaction**
   - **Try clicking on disabled dates** (beyond 180 days)
   - **Expected behavior:** Clicks should have no effect
   - **Try selecting valid dates** within 180-day range
   - **Expected behavior:** Dates should be selectable

**Pass Criteria:** ✅ Date pickers enforce 180-day limits, disabled dates are visually distinct

**Visual Success Indicators:**
- **Good:** Clear visual distinction between enabled/disabled dates
- **Good:** Date picker shows current date highlighted
- **Good:** 180-day limit is consistently enforced
- **Bad:** Can select dates beyond 180-day limit
- **Bad:** No visual indication of date constraints

### **Test 3.2: Date Validation Messages and Form Prevention**

**Objective:** Verify Japanese validation messages appear for invalid date ranges

**Detailed Steps:**

1. **Attempt Invalid Date Entry**
   - **In campaign creation form:** Try to set end date beyond 180 days
   - **Methods to try:**
     - Manual typing in date field (if allowed)
     - Using browser developer tools to modify values
     - Copy-pasting future dates

2. **Fill Required Fields**
   - **Campaign name:** Enter "Test Campaign 180-Day Limit"
   - **Other required fields:** Fill with valid test data
   - **Leave invalid end date:** Keep date beyond 180-day limit

3. **Attempt Form Submission**
   - **Click save/submit button** (保存 or 作成)
   - **Expected behavior:** Form should not submit successfully
   - **Visual Confirmation:** Page remains on campaign creation form

4. **Verify Validation Messages**
   - **Look for error messages** near date fields or at top of form
   - **Expected Japanese messages:**
     - "終了日は今日から180日以内に設定してください。"
     - "無効な日付範囲です。"
     - "日付の設定に問題があります。"
   - **Message styling:** Usually red text or red border
   - **Screenshot Required:** `16_date_validation_error_message.png`

5. **Test Form Prevention**
   - **Verify:** No campaign is created with invalid dates
   - **Check campaign list:** Invalid campaign should not appear
   - **Expected behavior:** User remains on creation form to fix errors

6. **Test Valid Date Correction**
   - **Change end date** to within 180-day limit
   - **Submit form again**
   - **Expected result:** Form should submit successfully
   - **Visual Confirmation:** Success message or return to campaign list

**Pass Criteria:** ✅ Japanese validation messages appear, form submission prevented for invalid dates

**Error Message Quality Indicators:**
- **Good:** Clear Japanese error messages
- **Good:** Messages appear near relevant fields
- **Good:** Error styling (red color, borders) is visible
- **Bad:** English-only error messages
- **Bad:** Technical error codes or unclear messages

### **Test 3.3: UI Layout and Organization Improvements**

**Objective:** Verify campaign settings interface is well-organized and user-friendly

**Detailed Steps:**

1. **Evaluate Overall Page Layout**
   - **Review campaign settings page structure**
   - **Check for:** Clear section divisions and logical organization
   - **Visual elements:** Headers, spacing, grouping
   - **Screenshot Required:** `17_campaign_ui_layout_overview.png`

2. **Analyze Campaign Creation Form Organization**
   - **Open campaign creation interface**
   - **Check for well-organized sections:**
     - Basic Information (name, description)
     - Date Settings (start/end dates)
     - Discount Settings (type, value)
     - Advanced Options (if any)
   - **Visual grouping:** Related fields should be grouped together

3. **Test Modal/Dialog Organization** (if applicable)
   - **If campaign creation uses modal:** Check modal layout
   - **Expected features:**
     - Clear modal title
     - Organized form sections
     - Visible save/cancel buttons
     - Proper spacing and alignment

4. **Verify Dynamic Unit Displays**
   - **Look for fields with units:** Percentage (%), days (日), etc.
   - **Check for clear unit labels:** Next to or within input fields
   - **Example:** "割引率: [10] %" or "期間: [7] 日"
   - **Dynamic behavior:** Units should be clearly displayed

5. **Test Responsive Design**
   - **Resize browser window** to simulate mobile view
   - **Expected behavior:** Layout adapts to smaller screens
   - **Check for:** All elements remain accessible and usable
   - **Screenshot Required:** `18_campaign_mobile_responsive.png`

6. **Evaluate User Experience Flow**
   - **Try creating a campaign** from start to finish
   - **Check for:** Logical step-by-step process
   - **User-friendly elements:**
     - Clear instructions or help text
     - Intuitive field placement
     - Obvious save/cancel actions

**Pass Criteria:** ✅ Improved UI layout, logical organization, clear visual hierarchy

**UI Improvement Indicators:**
- **Good:** Clear section headers and logical grouping
- **Good:** Consistent spacing and alignment throughout
- **Good:** Helpful labels and unit displays
- **Good:** Responsive design works on mobile
- **Bad:** Cluttered or confusing layout
- **Bad:** Poor field organization or unclear labels

---

## 🟢 Priority 4: 不要ページ削除 Enhanced Browser Testing

### **Test 4.1: Plugin Settings Menu Removal Verification**

**Objective:** Verify "プラグイン設定" menu item is completely removed with visual documentation

**Detailed Steps:**

1. **Locate Main Plugin Menu**
   - **In WordPress admin sidebar:** Find **Monthly Room Booking** menu
   - **Visual Confirmation:** Menu should be visible in left sidebar
   - **Menu icon:** Should have plugin-specific icon or default WordPress icon

2. **Expand and Document Menu Structure**
   - **Click on Monthly Room Booking** to expand submenu
   - **Count all visible submenu items** systematically
   - **Expected total:** Exactly 7 menu items (no more, no less)
   - **Screenshot Required:** `19_plugin_menu_structure_complete.png`

3. **Verify Expected Menu Items (Systematic Check)**
   - **Item 1:** 物件マスタ管理 (Property Master Management) ✓
   - **Item 2:** 予約カレンダー (Booking Calendar) ✓
   - **Item 3:** 予約登録 (Booking Registration) ✓
   - **Item 4:** 売上サマリー (Sales Summary) ✓
   - **Item 5:** キャンペーン設定 (Campaign Settings) ✓
   - **Item 6:** オプション管理 (Options Management) ✓
   - **Item 7:** 料金設定 (Fee Settings) ✓

4. **Verify Removed Menu Item**
   - **Should NOT be visible:** プラグイン設定 (Plugin Settings)
   - **Double-check:** Scroll through entire submenu list
   - **Confirm:** No settings-related items beyond 料金設定
   - **Visual verification:** Menu ends with 料金設定 as last item

5. **Test Direct URL Access Prevention**
   - **Try accessing plugin settings directly via URL:**
   - **Test URL:** `http://t-monthlycampaign.local/wp-admin/admin.php?page=monthly-booking-plugin-settings`
   - **Expected result:** 404 error, redirect, or "page not found" message
   - **Screenshot Required:** `20_direct_url_access_blocked.png`

**Pass Criteria:** ✅ Plugin settings menu completely removed, only 7 expected items visible

**Menu Structure Success Indicators:**
- **Good:** Exactly 7 menu items, no more or less
- **Good:** All menu items have clear Japanese labels
- **Good:** Menu structure is clean and organized
- **Bad:** 8 or more menu items (indicates incomplete removal)
- **Bad:** Any reference to "プラグイン設定" or "Plugin Settings"

### **Test 4.2: Remaining Menu Functionality Verification**

**Objective:** Verify all 7 remaining menu items function correctly without broken links

**Detailed Steps:**

1. **Systematic Menu Item Testing**
   - **Test each menu item individually**
   - **For each page, verify:**
     - Page loads without errors (no white screen)
     - Page title is correct and matches menu item
     - Main content area displays properly
     - No broken images or missing elements
     - Page serves its intended business purpose

2. **物件マスタ管理 (Property Master Management) Testing**
   - **Click menu item**
   - **Expected result:** Property/room management interface loads
   - **Visual elements to verify:**
     - Room list or table display
     - Add/edit buttons or forms
     - Search or filter functionality
   - **Screenshot Required:** `21_property_management_page.png`

3. **予約カレンダー (Booking Calendar) Testing**
   - **Click menu item**
   - **Expected result:** Calendar interface loads
   - **Visual elements to verify:**
     - Calendar grid display
     - Room selection dropdown
     - Month navigation controls
   - **Screenshot Required:** `22_booking_calendar_page.png`

4. **予約登録 (Booking Registration) Testing**
   - **Click menu item**
   - **Expected result:** Booking form or booking list loads
   - **Visual elements to verify:**
     - Booking form fields or booking data table
     - Customer information inputs
     - Date selection interfaces
   - **Screenshot Required:** `23_booking_registration_page.png`

5. **売上サマリー (Sales Summary) Testing**
   - **Click menu item**
   - **Expected result:** Sales/revenue summary page loads
   - **Visual elements to verify:**
     - Summary data display
     - Charts, graphs, or tables
     - Financial information presentation
   - **Screenshot Required:** `24_sales_summary_page.png`

6. **キャンペーン設定 (Campaign Settings) Testing**
   - **Click menu item** (already tested in Priority 3)
   - **Expected result:** Campaign management interface loads
   - **Visual elements to verify:**
     - Campaign list display
     - Add/edit campaign functionality
     - Campaign configuration options

7. **オプション管理 (Options Management) Testing**
   - **Click menu item**
   - **Expected result:** Options/services management page loads
   - **Visual elements to verify:**
     - Option list or configuration interface
     - Pricing and availability settings
     - Service management tools
   - **Screenshot Required:** `25_options_management_page.png`

8. **料金設定 (Fee Settings) Testing**
   - **Click menu item** (already tested in Priority 1)
   - **Expected result:** Fee configuration page loads
   - **Visual elements to verify:**
     - Fee categories and input fields
     - Save functionality
     - Unified fee management interface

**Pass Criteria:** ✅ All 7 remaining menu items function correctly, no broken links or missing pages

**Page Functionality Success Indicators:**
- **Good:** All pages load within 3 seconds
- **Good:** Page titles match menu item names
- **Good:** Content areas display relevant information
- **Good:** No PHP errors or white screens
- **Bad:** Any page shows error messages
- **Bad:** Broken layouts or missing content sections
- **Bad:** Pages that don't serve their intended purpose

### **Test 4.3: Menu Organization and User Experience**

**Objective:** Verify menu organization improves user experience

**Detailed Steps:**

1. **Evaluate Menu Logic and Flow**
   - **Review menu order:** Check if items are logically ordered
   - **Expected flow:** Property → Calendar → Booking → Summary → Settings
   - **User perspective:** Does order make sense for typical workflow?

2. **Test Menu Navigation Speed**
   - **Click through all 7 menu items** in sequence
   - **Time each page load:** Should be < 3 seconds each
   - **Check for:** Smooth transitions between pages
   - **Overall experience:** Should feel responsive and professional

3. **Verify Menu Consistency**
   - **Check menu styling:** All items should have consistent appearance
   - **Icon consistency:** Icons should be appropriate and consistent
   - **Text formatting:** All Japanese text should display correctly

**Pass Criteria:** ✅ Menu organization is logical, navigation is smooth and efficient

---

## 📊 Comprehensive Test Results Recording Template

Please record your test results using this detailed template:

### 🔴 Priority 1 Results (料金データ一元化):
- [ ] ✅ PASS / ❌ FAIL - Test 1.1: Default daily rent section removed
  - **Visual Confirmation:** Section "デフォルト日額賃料" not found ✓ / ✗
  - **Section Count:** Exactly 4 fee sections visible ✓ / ✗
  - **Screenshot:** `02_fee_settings_page_full.png` attached ✓ / ✗
- [ ] ✅ PASS / ❌ FAIL - Test 1.2: Unified fee management functionality
  - **Fee Modification:** Value change saved successfully ✓ / ✗
  - **Persistence Test:** Value persists after page refresh ✓ / ✗
  - **Screenshot:** `05_fee_save_success_message.png` attached ✓ / ✗
- **Detailed Notes:** ________________________________
- **Issues Found:** ________________________________

### 🟡 Priority 2 Results (JavaScript安定化):
- [ ] ✅ PASS / ❌ FAIL - Test 2.1: Calendar error handling and system stability
  - **Console Monitoring:** No uncaught JavaScript errors ✓ / ✗
  - **Error Messages:** Japanese error messages displayed ✓ / ✗
  - **System Stability:** Page remains functional during errors ✓ / ✗
- [ ] ✅ PASS / ❌ FAIL - Test 2.2: Room selection dropdown functionality
  - **Dropdown Population:** Room options display correctly ✓ / ✗
  - **Selection Process:** Calendar updates when room changed ✓ / ✗
  - **AJAX Functionality:** Network requests complete successfully ✓ / ✗
- **Console Error Log:** ________________________________
- **Screenshot:** `08_console_monitoring_setup.png` attached ✓ / ✗
- **Detailed Notes:** ________________________________

### 🟡 Priority 3 Results (キャンペーン設定UI改善):
- [ ] ✅ PASS / ❌ FAIL - Test 3.1: 180-day date limits verification
  - **Start Date Limits:** Past dates disabled, 180-day max enforced ✓ / ✗
  - **End Date Limits:** 180-day maximum consistently applied ✓ / ✗
  - **Visual Indicators:** Disabled dates clearly marked ✓ / ✗
- [ ] ✅ PASS / ❌ FAIL - Test 3.2: Date validation messages
  - **Japanese Messages:** Validation errors in Japanese ✓ / ✗
  - **Form Prevention:** Invalid submissions blocked ✓ / ✗
  - **Error Styling:** Clear visual error indicators ✓ / ✗
- [ ] ✅ PASS / ❌ FAIL - Test 3.3: UI layout and organization improvements
  - **Section Organization:** Logical grouping of form fields ✓ / ✗
  - **Responsive Design:** Layout adapts to mobile screens ✓ / ✗
  - **User Experience:** Intuitive workflow and navigation ✓ / ✗
- **Date Calculation Verification:** Today + 180 days = ________________
- **Screenshot:** `14_start_date_picker_limits.png` attached ✓ / ✗
- **Detailed Notes:** ________________________________

### 🟢 Priority 4 Results (不要ページ削除):
- [ ] ✅ PASS / ❌ FAIL - Test 4.1: Plugin settings menu removal
  - **Menu Count:** Exactly 7 menu items visible ✓ / ✗
  - **Missing Item:** "プラグイン設定" not found ✓ / ✗
  - **Direct URL Test:** Plugin settings URL returns 404 ✓ / ✗
- [ ] ✅ PASS / ❌ FAIL - Test 4.2: Remaining menu functionality
  - **All Pages Load:** 7 menu items load without errors ✓ / ✗
  - **Page Titles:** Titles match menu item names ✓ / ✗
  - **Content Display:** All pages show relevant content ✓ / ✗
- **Menu Items Verified:**
  - [ ] 物件マスタ管理 (Property Master Management)
  - [ ] 予約カレンダー (Booking Calendar)
  - [ ] 予約登録 (Booking Registration)
  - [ ] 売上サマリー (Sales Summary)
  - [ ] キャンペーン設定 (Campaign Settings)
  - [ ] オプション管理 (Options Management)
  - [ ] 料金設定 (Fee Settings)
- **Screenshot:** `19_plugin_menu_structure_complete.png` attached ✓ / ✗
- **Detailed Notes:** ________________________________

### 🧪 Integration Test Results:
- [ ] ✅ PASS / ❌ FAIL - Cross-priority functionality integration
- [ ] ✅ PASS / ❌ FAIL - End-to-end admin workflow
- [ ] ✅ PASS / ❌ FAIL - Performance and loading times
- **Workflow Test:** Property → Calendar → Campaign → Fee Settings ✓ / ✗
- **Data Consistency:** Changes reflect across all interfaces ✓ / ✗
- **Overall Performance:** All pages load within 3 seconds ✓ / ✗

---

## 📊 Testing Summary Dashboard

### Overall Test Completion:
- **Total Test Cases:** _____ / 12 completed
- **Pass Rate:** _____ % (_____ passed / _____ total)
- **Critical Issues:** _____ found
- **Minor Issues:** _____ found

### Browser and Environment Information:
- **Browser Used:** ________________ (version: ______)
- **Operating System:** ________________
- **Screen Resolution:** ________________
- **Testing Date:** ________________
- **Testing Duration:** _____ minutes

### Screenshot Checklist:
- [ ] `01_wordpress_dashboard.png` - WordPress dashboard
- [ ] `02_fee_settings_page_full.png` - Fee settings page
- [ ] `03_fee_sections_detail.png` - Fee sections detail
- [ ] `04_fee_modification_before_save.png` - Fee modification
- [ ] `05_fee_save_success_message.png` - Save success message
- [ ] `06_fee_persistence_verification.png` - Persistence verification
- [ ] `07_calendar_page_initial_load.png` - Calendar page
- [ ] `08_console_monitoring_setup.png` - Console monitoring
- [ ] `09_error_message_display.png` - Error messages (if any)
- [ ] `10_room_dropdown_options.png` - Room dropdown
- [ ] `11_ajax_network_monitoring.png` - AJAX monitoring
- [ ] `12_campaign_settings_page.png` - Campaign settings
- [ ] `13_campaign_creation_form.png` - Campaign creation
- [ ] `14_start_date_picker_limits.png` - Start date limits
- [ ] `15_end_date_picker_limits.png` - End date limits
- [ ] `16_date_validation_error_message.png` - Validation errors
- [ ] `17_campaign_ui_layout_overview.png` - UI layout
- [ ] `18_campaign_mobile_responsive.png` - Mobile responsive
- [ ] `19_plugin_menu_structure_complete.png` - Menu structure
- [ ] `20_direct_url_access_blocked.png` - URL access blocked
- [ ] `21_property_management_page.png` - Property management
- [ ] `22_booking_calendar_page.png` - Booking calendar
- [ ] `23_booking_registration_page.png` - Booking registration
- [ ] `24_sales_summary_page.png` - Sales summary
- [ ] `25_options_management_page.png` - Options management

---

## 🚨 Enhanced Issue Reporting System

### Critical Issue Template:
```
**CRITICAL ISSUE REPORT**
**Priority:** [1/2/3/4]
**Test Case:** [Specific test that failed]
**Severity:** [High/Medium/Low]
**Impact:** [System crash/Feature broken/UI issue/Performance]

**Expected Behavior:**
[Detailed description of what should happen]

**Actual Behavior:**
[Detailed description of what actually happened]

**Reproduction Steps:**
1. [Step 1]
2. [Step 2]
3. [Step 3]
4. [Result]

**Environment Details:**
- **Browser:** [Chrome/Firefox/Safari + version]
- **OS:** [Windows/Mac/Linux + version]
- **Screen Size:** [Desktop/Tablet/Mobile]
- **Local WP Version:** [Version number]

**Error Information:**
- **Console Errors:** [Copy exact error messages]
- **Network Errors:** [HTTP status codes, failed requests]
- **PHP Errors:** [Server-side errors if visible]

**Visual Evidence:**
- **Screenshots:** [List attached screenshot files]
- **Screen Recording:** [If applicable]

**Workaround:**
[Any temporary solution found]

**Business Impact:**
[How this affects end users or business operations]
```

### Minor Issue Template:
```
**MINOR ISSUE REPORT**
**Priority:** [1/2/3/4]
**Test Case:** [Specific test case]
**Issue Type:** [UI/UX/Performance/Documentation]

**Description:**
[Brief description of the issue]

**Expected vs Actual:**
- **Expected:** [What should happen]
- **Actual:** [What actually happens]

**Suggestion:**
[Proposed improvement or fix]

**Priority Level:** [Low/Medium]
```

### Performance Issue Template:
```
**PERFORMANCE ISSUE REPORT**
**Test Case:** [Specific performance test]
**Metric:** [Load time/Response time/Memory usage]

**Measurements:**
- **Expected:** [< 3 seconds, etc.]
- **Actual:** [Measured time]
- **Browser:** [Browser and version]

**Network Conditions:**
- **Connection:** [Fast/Slow/Offline simulation]
- **Throttling:** [Applied throttling settings]

**Recommendations:**
[Suggestions for performance improvement]
```

---

## 📋 Post-Testing Actions Checklist

### Immediate Actions (Within 24 hours):
- [ ] All screenshots organized and properly named
- [ ] Test results documented in this template
- [ ] Critical issues reported using issue templates
- [ ] Test summary shared with development team
- [ ] Environment issues documented for IT support

### Follow-up Actions (Within 1 week):
- [ ] Retesting of any failed test cases after fixes
- [ ] Verification of issue resolutions
- [ ] Updated test procedures based on findings
- [ ] Performance optimization recommendations implemented
- [ ] User acceptance testing scheduled

### Documentation Updates:
- [ ] MANUAL_TESTING_PROCEDURES.md updated with lessons learned
- [ ] Known issues documented for future reference
- [ ] Test case improvements identified and documented
- [ ] Environment setup improvements noted

---

## 🎯 Success Criteria Verification

### Production Readiness Checklist:
- [ ] **All Priority 1-4 fixes verified working** ✅ / ❌
- [ ] **No critical issues found** ✅ / ❌
- [ ] **Performance meets requirements** (< 3 second load times) ✅ / ❌
- [ ] **User experience is intuitive** ✅ / ❌
- [ ] **Error handling is appropriate** ✅ / ❌
- [ ] **All menu items functional** ✅ / ❌
- [ ] **Data consistency maintained** ✅ / ❌

### Deployment Recommendation:
- [ ] ✅ **APPROVED FOR PRODUCTION** - All tests pass, no critical issues
- [ ] ⚠️ **CONDITIONAL APPROVAL** - Minor issues found, acceptable for production
- [ ] ❌ **NOT APPROVED** - Critical issues found, requires fixes before deployment

### Final Sign-off:
**Tester Name:** ________________  
**Date:** ________________  
**Overall Assessment:** ________________  
**Recommendation:** ________________

This enhanced browser testing instruction guide provides comprehensive verification procedures with detailed result recording and issue reporting capabilities for the Monthly Booking Plugin Priority 1-4 modifications.]
**Steps to Reproduce:** [Detailed steps]
```

---

## 📋 Testing Completion

After completing all tests:

1. **Record Results:** Fill in the test results section above
2. **Report Issues:** Use the issue template for any problems found
3. **Overall Assessment:** 
   - [ ] ✅ ALL TESTS PASS - Ready for production
   - [ ] ❌ ISSUES FOUND - Requires fixes

**Testing Completed By:** ________________  
**Date:** ________________  
**Overall Result:** ✅ PASS / ❌ FAIL  
**Ready for Production:** ✅ YES / ❌ NO

---

## 🎯 Next Steps

### If All Tests Pass:
- Plugin is ready for production deployment
- No further fixes required
- Proceed with live environment rollout

### If Issues Found:
- Report specific issues using the template above
- Minimal fixes will be implemented
- Re-test after fixes applied

---

## 📞 Support

If you need assistance with testing or encounter unexpected behavior:
1. Document the specific issue with screenshots
2. Note any browser console errors
3. Record the exact steps that caused the problem
4. Report findings for immediate resolution

**The comprehensive code analysis confirms all fixes are implemented correctly. Browser testing will validate the user experience and catch any remaining edge cases.**
