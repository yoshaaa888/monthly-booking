# Manual Testing Procedures - Enhanced for Beginners
## Monthly Booking Plugin Priority 1-4 Fixes Verification

**Target Environment:** Local WP at `http://t-monthlycampaign.local/`  
**Plugin Version:** v2.2-final  
**Testing Focus:** Priority 1-4 fix verification  
**Skill Level:** Beginner-friendly with detailed instructions

---

## 🎯 Testing Overview

This document provides comprehensive step-by-step manual testing procedures to verify that Priority 1-4 fixes are working correctly in the WordPress admin interface. Each test includes detailed navigation instructions, visual confirmation steps, and troubleshooting guidance.

### Prerequisites Checklist:
- [ ] Local WP environment is running (check Local by Flywheel app)
- [ ] WordPress admin access confirmed (login credentials ready)
- [ ] Monthly Booking plugin is activated (visible in admin menu)
- [ ] Test data is loaded (rooms and campaigns exist)

### Before You Start:
1. **Open a web browser** (Chrome, Firefox, or Safari recommended)
2. **Clear browser cache** to ensure fresh page loads
3. **Open Developer Tools** (F12 key) to monitor for errors
4. **Have a notepad ready** to record test results

---

## 🔴 Priority 1: 料金データ一元化 Testing

### **Test Case 1.1: Verify Default Daily Rent Section Removal**

**Objective:** Confirm "デフォルト日額賃料" (Default Daily Rent) section is completely removed from fee settings

**Detailed Steps:**

1. **Access WordPress Admin**
   - Open browser and navigate to: `http://t-monthlycampaign.local/wp-admin/`
   - **Login Credentials:**
     - Username: `t-monthly-admin`
     - Password: `t-monthly`
   - Click **ログイン** (Login) button
   - **Visual Confirmation:** You should see the WordPress dashboard

2. **Navigate to Fee Settings**
   - Look for **Monthly Room Booking** in the left sidebar menu
   - **If you don't see it:** The plugin may not be activated - check Plugins page
   - Click **Monthly Room Booking** to expand the submenu
   - Click **料金設定** (Fee Settings)
   - **Visual Confirmation:** Page title should show "料金設定" at the top

3. **Inspect Page Content**
   - Scroll through the entire page from top to bottom
   - **Look for:** Any section titled "デフォルト日額賃料" (Default Daily Rent)
   - **Expected Result:** This section should NOT exist anywhere on the page
   - **What you SHOULD see:** Only these fee categories:
     - 基本料金 (Basic Fees)
     - 光熱費 (Utilities)
     - 追加人数料金 (Additional Person Fees)
     - オプション割引設定 (Option Discount Settings)

4. **Take Screenshot**
   - Capture the entire fee settings page
   - **File name suggestion:** `priority1_fee_settings_page.png`

**Pass Criteria:** ✅ No "デフォルト日額賃料" section found anywhere on the page

**Troubleshooting:**
- **If page doesn't load:** Check Local WP is running, try refreshing
- **If section still exists:** Priority 1 fix may not be applied correctly
- **If different menu items:** Plugin version may be incorrect

### **Test Case 1.2: Verify Unified Fee Management**

**Objective:** Confirm fee modifications save correctly through the unified system

**Detailed Steps:**

1. **Locate a Fee Input Field**
   - In the **料金設定** page, find any numerical input field
   - **Example locations:** Basic fees section, utilities section
   - **Recommended:** Choose "清掃費" (Cleaning Fee) if available

2. **Record Current Value**
   - Note the current value in your notepad
   - **Example:** "Current cleaning fee: 3000"

3. **Modify the Value**
   - Click in the input field
   - Change the value (add 100 to current value)
   - **Example:** Change 3000 to 3100

4. **Save Changes**
   - Scroll to bottom of page
   - Click **設定を保存** (Save Settings) button
   - **Visual Confirmation:** Look for success message (usually green background)

5. **Verify Persistence**
   - Refresh the page (F5 or Ctrl+R)
   - **Wait for page to fully load**
   - Check the same input field
   - **Expected Result:** Modified value should still be displayed

6. **Restore Original Value**
   - Change the value back to original
   - Save again to maintain test environment

**Pass Criteria:** ✅ Fee modifications save and persist correctly after page refresh

**Input Examples:**
- **Cleaning Fee:** 3000 → 3100 → 3000
- **Utility Fee:** 2000 → 2100 → 2000
- **Additional Person Fee:** 500 → 600 → 500

**Error Handling:**
- **If save fails:** Check for error messages, verify form validation
- **If value doesn't persist:** Database connection issue or caching problem
- **If no success message:** JavaScript errors may be preventing save

---

## 🟡 Priority 2: JavaScript安定化 Testing

### **Test Case 2.1: Calendar Error Handling**

**Objective:** Verify JavaScript errors don't crash the system and show user-friendly messages

**Detailed Steps:**

1. **Navigate to Calendar Page**
   - From WordPress admin, click **Monthly Room Booking** → **予約カレンダー**
   - **Visual Confirmation:** Page should load with calendar interface

2. **Open Developer Tools**
   - Press **F12** key (or right-click → Inspect)
   - Click **Console** tab in developer tools
   - **Clear any existing messages** (click trash icon)

3. **Test Room Selection**
   - Look for room selection dropdown on the calendar page
   - **If no dropdown visible:** This may be expected behavior
   - Try selecting different rooms if dropdown exists
   - **Monitor Console:** Watch for any red error messages

4. **Check for Error Messages**
   - **Expected Result:** No uncaught JavaScript errors (red text in console)
   - **If errors occur:** Should see user-friendly Japanese messages on page
   - **Page should remain functional:** Buttons and links still work

5. **Test Calendar Navigation**
   - Try clicking month navigation buttons (if available)
   - Try clicking on calendar dates
   - **Monitor for crashes:** Page should not freeze or become unresponsive

**Pass Criteria:** ✅ No system crashes from JavaScript errors, graceful error handling

**What to Look For:**
- **Good:** Warning messages in yellow, info messages in blue
- **Bad:** Uncaught errors in red, page freezing, broken functionality
- **Good:** Japanese error messages displayed to user
- **Bad:** Technical error messages or blank screens

### **Test Case 2.2: Room Selection Dropdown Functionality**

**Objective:** Verify room selection works smoothly without JavaScript errors

**Detailed Steps:**

1. **Locate Room Selection**
   - In **予約カレンダー** page, look for dropdown labeled "部屋を選択" or similar
   - **If not visible:** Check if rooms are pre-selected or if feature is disabled

2. **Test Dropdown Population**
   - Click on the room selection dropdown
   - **Expected Result:** Dropdown should populate with room options
   - **Example options:** "Room 101", "Room 102", etc.
   - **If empty:** Check if test data is loaded correctly

3. **Test Room Selection**
   - Select a different room from dropdown
   - **Expected Result:** Calendar should update to show selected room's data
   - **Visual Confirmation:** Calendar content changes, loading indicator appears briefly

4. **Monitor Console During Selection**
   - Keep Developer Tools Console open
   - **Expected Result:** No red error messages during room changes
   - **Acceptable:** Blue info messages or yellow warnings

5. **Test Multiple Selections**
   - Try selecting 3-4 different rooms in sequence
   - **Expected Result:** Each selection works smoothly
   - **Performance Check:** No significant delays or freezing

**Pass Criteria:** ✅ Room selection works without errors, calendar updates correctly

**Troubleshooting:**
- **If dropdown is empty:** Check database for room data
- **If calendar doesn't update:** Check AJAX functionality
- **If console shows errors:** Note exact error message for reporting

### **Test Case 2.3: AJAX Error Handling and Network Resilience**

**Objective:** Verify system handles network issues gracefully with Japanese error messages

**Detailed Steps:**

1. **Prepare Network Monitoring**
   - In Developer Tools, click **Network** tab
   - **Clear network log** (click trash icon)
   - Keep this tab open during testing

2. **Test Normal AJAX Operations**
   - Try changing room selection or calendar navigation
   - **Monitor Network tab:** Should see AJAX requests (XHR type)
   - **Expected Result:** Requests complete successfully (status 200)

3. **Simulate Network Issues** (Optional - Advanced)
   - In Developer Tools, go to **Network** tab
   - Click **Network throttling** dropdown
   - Select **Offline** to simulate network failure
   - Try changing room selection
   - **Expected Result:** User-friendly Japanese error message appears

4. **Test Error Message Content**
   - **Look for Japanese messages** like:
     - "エラーが発生しました" (An error occurred)
     - "ネットワークエラー" (Network error)
     - "再試行してください" (Please try again)
   - **Should NOT see:** Technical error codes or English-only messages

5. **Test System Recovery**
   - Restore network connection (set throttling back to "No throttling")
   - Try room selection again
   - **Expected Result:** System should work normally again

**Pass Criteria:** ✅ Graceful error handling with Japanese messages, system recovery

**Network Test Examples:**
- **Good Response:** Status 200, calendar updates
- **Handled Error:** Status 500, Japanese error message shown
- **Bad Error:** Status 500, page crashes or shows technical details

**Error Message Examples:**
- **Good:** "カレンダーの読み込みに失敗しました。ページを再読み込みしてください。"
- **Bad:** "AJAX Error 500: Internal Server Error"

---

## 🟡 Priority 3: キャンペーン設定UI改善 Testing

### **Test Case 3.1: 180-Day Date Limits Verification**

**Objective:** Verify campaign dates are limited to 180 days from today (practical business constraint)

**Detailed Steps:**

1. **Navigate to Campaign Settings**
   - Click **Monthly Room Booking** → **キャンペーン設定**
   - **Visual Confirmation:** Page should show campaign management interface

2. **Access Campaign Creation**
   - Look for button labeled **新規キャンペーン追加** (Add New Campaign) or similar
   - Click the button
   - **Expected Result:** Campaign creation form or modal should appear

3. **Test Start Date Limits**
   - Click on **開始日** (Start Date) field
   - **Date picker should open**
   - **Check minimum date:** Should not allow dates before today
   - **Check maximum date:** Should not allow dates beyond 180 days from today
   - **Calculate 180 days:** Today + 180 days = maximum selectable date

4. **Test End Date Limits**
   - Click on **終了日** (End Date) field
   - **Date picker should open**
   - **Check maximum date:** Should not allow dates beyond 180 days from today
   - **Try selecting far future date:** Should be disabled or not selectable

5. **Visual Date Picker Verification**
   - **Disabled dates should be:** Grayed out or not clickable
   - **Enabled dates should be:** Clearly selectable
   - **Today should be:** Highlighted or marked as current date

**Pass Criteria:** ✅ Date inputs limited to 180-day range, future dates beyond limit are disabled

**Date Calculation Example:**
- **Today:** August 8, 2025
- **180 days later:** February 4, 2026
- **Should be disabled:** Any date after February 4, 2026

### **Test Case 3.2: Date Validation Messages**

**Objective:** Verify Japanese validation messages appear for invalid date ranges

**Detailed Steps:**

1. **Attempt Invalid Date Entry**
   - In campaign creation form, try to manually enter end date beyond 180 days
   - **Methods to try:**
     - Type date directly in field (if allowed)
     - Use browser developer tools to modify date value
     - Try copy-pasting future date

2. **Submit Form with Invalid Dates**
   - Fill in other required fields (campaign name, etc.)
   - Click **保存** (Save) or **作成** (Create) button
   - **Expected Result:** Form should not submit successfully

3. **Check Validation Messages**
   - **Look for Japanese error message** such as:
     - "終了日は今日から180日以内に設定してください。" (End date must be within 180 days from today)
     - "無効な日付範囲です。" (Invalid date range)
   - **Message should appear:** Near the date field or at top of form
   - **Message color:** Usually red or orange to indicate error

4. **Test Form Prevention**
   - **Expected Result:** Form submission should be prevented
   - **Page should:** Stay on campaign creation form
   - **Should NOT:** Create campaign with invalid dates

5. **Test Valid Date Correction**
   - Change end date to within 180-day limit
   - Submit form again
   - **Expected Result:** Form should submit successfully

**Pass Criteria:** ✅ Japanese validation messages appear, form submission prevented for invalid dates

**Validation Message Examples:**
- **Good:** "終了日は今日から180日以内に設定してください。"
- **Acceptable:** "日付の範囲が無効です。"
- **Bad:** "Invalid date range" (English only)

### **Test Case 3.3: UI Layout and Organization Improvements**

**Objective:** Verify campaign settings interface is well-organized and user-friendly

**Detailed Steps:**

1. **Review Overall Page Layout**
   - Examine the campaign settings page structure
   - **Check for:** Clear section divisions
   - **Look for:** Logical grouping of related fields

2. **Test Modal/Dialog Organization**
   - Open campaign creation form
   - **Check for:** Well-organized sections within modal
   - **Examples of good organization:**
     - Basic Information section
     - Date Settings section
     - Discount Settings section
     - Advanced Options section

3. **Verify Dynamic Unit Displays**
   - Look for fields that show units (days, months, percentage)
   - **Check for:** Clear unit labels next to input fields
   - **Example:** "割引率: [10] %" or "期間: [7] 日"
   - **Dynamic behavior:** Units should change based on selection

4. **Test Form Field Grouping**
   - **Related fields should be grouped together:**
     - Date fields near each other
     - Discount settings grouped
     - Campaign details grouped
   - **Visual separation:** Clear borders or spacing between groups

5. **Check Responsive Design**
   - Resize browser window to test mobile view
   - **Expected Result:** Layout should adapt to smaller screens
   - **Form should remain:** Usable on mobile devices

6. **Test User Experience Flow**
   - Try creating a campaign from start to finish
   - **Check for:** Logical step-by-step process
   - **Should feel:** Intuitive and easy to follow

**Pass Criteria:** ✅ Improved UI layout, logical organization, clear visual hierarchy

**UI Improvement Indicators:**
- **Good:** Clear section headers, grouped related fields
- **Good:** Consistent spacing and alignment
- **Good:** Helpful labels and units displayed
- **Bad:** Cluttered layout, confusing field placement

---

## 🟢 Priority 4: 不要ページ削除 Testing

### **Test Case 4.1: Plugin Settings Menu Removal Verification**

**Objective:** Verify "プラグイン設定" (Plugin Settings) menu item is completely removed for cleaner admin interface

**Detailed Steps:**

1. **Locate Main Plugin Menu**
   - In WordPress admin sidebar, find **Monthly Room Booking** menu
   - **Visual Confirmation:** Menu should be visible in left sidebar

2. **Expand and Count Menu Items**
   - Click on **Monthly Room Booking** to expand submenu
   - **Count all visible submenu items**
   - **Take screenshot** of expanded menu for documentation

3. **Verify Expected Menu Items**
   - **Should be visible (7 items total):**
     - 物件マスタ管理 (Property Master Management)
     - 予約カレンダー (Booking Calendar)
     - 予約登録 (Booking Registration)
     - 売上サマリー (Sales Summary)
     - キャンペーン設定 (Campaign Settings)
     - オプション管理 (Options Management)
     - 料金設定 (Fee Settings)

4. **Verify Removed Menu Item**
   - **Should NOT be visible:**
     - プラグイン設定 (Plugin Settings)
   - **Double-check:** Scroll through entire submenu list
   - **Confirm:** No settings-related menu items beyond 料金設定

5. **Test Direct URL Access**
   - Try accessing plugin settings directly via URL
   - **Test URL:** `http://t-monthlycampaign.local/wp-admin/admin.php?page=monthly-booking-plugin-settings`
   - **Expected Result:** Should show 404 error or redirect to valid page

**Pass Criteria:** ✅ Plugin settings menu completely removed, only 7 expected items visible

**Menu Count Verification:**
- **Before Priority 4:** 8 menu items (including plugin settings)
- **After Priority 4:** 7 menu items (plugin settings removed)

### **Test Case 4.2: Remaining Menu Functionality Verification**

**Objective:** Verify all remaining menu items function correctly without broken links

**Detailed Steps:**

1. **Test Each Menu Item Systematically**
   - Click each of the 7 remaining menu items one by one
   - **For each page, verify:**
     - Page loads without errors
     - Page title is correct
     - Main content area displays properly
     - No broken images or missing elements

2. **物件マスタ管理 (Property Master Management)**
   - Click menu item
   - **Expected Result:** Property/room management interface loads
   - **Check for:** Room list, add/edit buttons, search functionality
   - **Visual Confirmation:** Page shows room data or empty state message

3. **予約カレンダー (Booking Calendar)**
   - Click menu item
   - **Expected Result:** Calendar interface loads
   - **Check for:** Calendar grid, room selection, navigation controls
   - **Visual Confirmation:** Calendar displays current month

4. **予約登録 (Booking Registration)**
   - Click menu item
   - **Expected Result:** Booking form or booking list loads
   - **Check for:** Form fields or booking data table
   - **Visual Confirmation:** Functional booking interface

5. **売上サマリー (Sales Summary)**
   - Click menu item
   - **Expected Result:** Sales/revenue summary page loads
   - **Check for:** Summary data, charts, or tables
   - **Visual Confirmation:** Financial data display

6. **キャンペーン設定 (Campaign Settings)**
   - Click menu item
   - **Expected Result:** Campaign management interface loads
   - **Check for:** Campaign list, add/edit functionality
   - **Visual Confirmation:** Campaign data or creation form

7. **オプション管理 (Options Management)**
   - Click menu item
   - **Expected Result:** Options/services management page loads
   - **Check for:** Option list, pricing, availability settings
   - **Visual Confirmation:** Options configuration interface

8. **料金設定 (Fee Settings)**
   - Click menu item (already tested in Priority 1)
   - **Expected Result:** Fee configuration page loads
   - **Check for:** Fee categories, input fields, save functionality
   - **Visual Confirmation:** Unified fee management interface

**Pass Criteria:** ✅ All 7 remaining menu items function correctly, no broken links or missing pages

**Error Indicators to Watch For:**
- **Bad:** White screen of death (WSOD)
- **Bad:** "Page not found" or 404 errors
- **Bad:** PHP error messages displayed
- **Bad:** Incomplete page loading (missing sections)
- **Good:** Proper page titles and content display
- **Good:** Functional buttons and forms

---

## 🧪 Comprehensive Integration Testing

### **Test Case 5.1: End-to-End Admin Workflow**

**Objective:** Verify all Priority 1-4 fixes work together in a complete business workflow

**Detailed Steps:**

1. **Property Management Test**
   - Navigate to **物件マスタ管理** (Property Master Management)
   - **Add new room:** Click add button, fill required fields
   - **Test data example:**
     - Room Name: "Test Room 999"
     - Daily Rent: 2500
     - Property: "Demo Building"
   - **Save and verify:** Room appears in list

2. **Calendar Integration Test**
   - Navigate to **予約カレンダー** (Booking Calendar)
   - **Check room dropdown:** Newly added room should appear
   - **Select new room:** Calendar should update to show room's availability
   - **Visual Confirmation:** Calendar displays without errors

3. **Campaign Setup with 180-Day Limit**
   - Navigate to **キャンペーン設定** (Campaign Settings)
   - **Create new campaign:**
     - Name: "Integration Test Campaign"
     - Start Date: Today
     - End Date: 30 days from today (within 180-day limit)
     - Discount: 10%
   - **Verify:** Campaign saves successfully

4. **Fee Configuration Test**
   - Navigate to **料金設定** (Fee Settings)
   - **Update any fee:** Change cleaning fee by 100 yen
   - **Save and verify:** Change persists after page refresh
   - **Confirm:** No "デフォルト日額賃料" section visible

5. **Booking Registration Test**
   - Navigate to **予約登録** (Booking Registration)
   - **Create test booking:** Use newly added room
   - **Verify:** Booking form accepts room selection

6. **Sales Summary Verification**
   - Navigate to **売上サマリー** (Sales Summary)
   - **Check for:** New booking data appears (if applicable)
   - **Verify:** Page loads without errors

**Pass Criteria:** ✅ Complete admin workflow functions without errors, all Priority fixes work together

### **Test Case 5.2: Error Recovery and Resilience Testing**

**Objective:** Verify system handles various error conditions gracefully

**Detailed Steps:**

1. **JavaScript Error Recovery**
   - Navigate to **予約カレンダー**
   - **Simulate error:** Try rapid room selection changes
   - **Monitor console:** Check for error handling
   - **Expected Result:** System remains responsive, shows Japanese error messages

2. **Campaign Date Validation**
   - Navigate to **キャンペーン設定**
   - **Try invalid dates:** Attempt to set end date beyond 180 days
   - **Expected Result:** Japanese validation message appears
   - **Verify:** Form submission is prevented

3. **Fee Update Validation**
   - Navigate to **料金設定**
   - **Try invalid values:** Enter negative numbers or text in fee fields
   - **Expected Result:** Proper validation prevents invalid data
   - **Verify:** System shows appropriate error messages

4. **Network Resilience** (Optional)
   - **Simulate slow connection:** Use browser throttling
   - **Test AJAX operations:** Room selection, form submissions
   - **Expected Result:** Loading indicators appear, graceful timeout handling

**Pass Criteria:** ✅ System handles errors gracefully throughout, maintains functionality

---

## 📊 Comprehensive Test Results Template

### 🔴 Priority 1 Results (料金データ一元化):
- [ ] ✅ PASS / ❌ FAIL - Test 1.1: Default daily rent section removed
- [ ] ✅ PASS / ❌ FAIL - Test 1.2: Unified fee management works
- **Screenshots:** `priority1_fee_settings_page.png`
- **Notes:** ________________________________

### 🟡 Priority 2 Results (JavaScript安定化):
- [ ] ✅ PASS / ❌ FAIL - Test 2.1: Calendar error handling functional
- [ ] ✅ PASS / ❌ FAIL - Test 2.2: Room selection dropdown works
- [ ] ✅ PASS / ❌ FAIL - Test 2.3: AJAX error handling with Japanese messages
- **Screenshots:** `priority2_calendar_console.png`
- **Notes:** ________________________________

### 🟡 Priority 3 Results (キャンペーン設定UI改善):
- [ ] ✅ PASS / ❌ FAIL - Test 3.1: 180-day date limits enforced
- [ ] ✅ PASS / ❌ FAIL - Test 3.2: Japanese validation messages appear
- [ ] ✅ PASS / ❌ FAIL - Test 3.3: UI layout improvements visible
- **Screenshots:** `priority3_campaign_settings.png`
- **Notes:** ________________________________

### 🟢 Priority 4 Results (不要ページ削除):
- [ ] ✅ PASS / ❌ FAIL - Test 4.1: Plugin settings menu removed
- [ ] ✅ PASS / ❌ FAIL - Test 4.2: All remaining menus functional
- **Screenshots:** `priority4_menu_structure.png`
- **Notes:** ________________________________

### 🧪 Integration Results:
- [ ] ✅ PASS / ❌ FAIL - Test 5.1: End-to-end workflow complete
- [ ] ✅ PASS / ❌ FAIL - Test 5.2: Error recovery functional
- **Screenshots:** `integration_workflow.png`
- **Notes:** ________________________________

---

## 🚨 Enhanced Troubleshooting Guide

### Environment Issues:

**Issue:** Local WP site not accessible (net::ERR_SOCKS_CONNECTION_FAILED)
**Solutions:**
1. Check Local by Flywheel app is running
2. Verify site status is "Running" in Local app
3. Try restarting the site in Local app
4. Check hosts file: `127.0.0.1 t-monthlycampaign.local`
5. Disable VPN or proxy connections
6. Try accessing via IP: `http://127.0.0.1:[port]/`

**Issue:** Plugin not activated or missing
**Solutions:**
1. Go to **Plugins** → **Installed Plugins**
2. Find "Monthly Room Booking" and click **Activate**
3. If not found, upload plugin zip file
4. Check for plugin conflicts

**Issue:** Database tables missing or empty
**Solutions:**
1. Deactivate and reactivate plugin (creates tables)
2. Import `seed_data.sql` via Adminer or phpMyAdmin
3. Check database connection in wp-config.php

**Issue:** JavaScript console errors
**Solutions:**
1. **Expected behavior:** Some errors may be normal
2. **Focus on:** Whether errors crash the system
3. **Check for:** Japanese error messages to users
4. **Clear cache:** Browser and WordPress cache

### Testing Issues:

**Issue:** Screenshots not saving properly
**Solutions:**
1. Use browser's built-in screenshot tool
2. Save to desktop with descriptive names
3. Include browser URL bar in screenshots
4. Capture full page, not just visible area

**Issue:** Test data not appearing
**Solutions:**
1. Check if seed data was imported correctly
2. Verify plugin activation created tables
3. Try adding test data manually through admin interface

### Performance Issues:

**Issue:** Pages loading slowly
**Solutions:**
1. Check Local WP resource allocation
2. Disable unnecessary plugins during testing
3. Clear WordPress object cache
4. Monitor browser Network tab for slow requests

---

## 📋 Final Testing Completion Checklist

### Pre-Testing Setup:
- [ ] Local WP environment confirmed running
- [ ] WordPress admin access verified
- [ ] Monthly Booking plugin activated
- [ ] Test data loaded and verified
- [ ] Browser developer tools ready
- [ ] Screenshot capture method prepared

### Testing Execution:
- [ ] All Priority 1 tests completed and documented
- [ ] All Priority 2 tests completed and documented
- [ ] All Priority 3 tests completed and documented
- [ ] All Priority 4 tests completed and documented
- [ ] Integration tests completed
- [ ] Screenshots captured for each priority
- [ ] Error conditions tested and documented

### Post-Testing Documentation:
- [ ] Test results recorded in template above
- [ ] Screenshots organized and named properly
- [ ] Issues documented with reproduction steps
- [ ] Overall assessment completed
- [ ] Next steps identified

**Testing Completed By:** ________________  
**Date:** ________________  
**Environment:** Local WP at t-monthlycampaign.local  
**Browser Used:** ________________  
**Overall Result:** ✅ ALL PASS / ⚠️ PARTIAL PASS / ❌ FAIL  
**Ready for Production:** ✅ YES / ❌ NO / ⚠️ WITH FIXES

---

## 📞 Enhanced Support Information

### Immediate Help:
If testing reveals critical issues:
1. **Document exact error messages** (copy-paste from console)
2. **Note browser and version** (Chrome 91, Firefox 89, etc.)
3. **Record reproduction steps** (step-by-step to recreate issue)
4. **Check WordPress debug.log** (wp-content/debug.log)
5. **Capture network requests** (from browser Developer Tools)

### Reporting Template:
```
**Priority:** [1/2/3/4]
**Test Case:** [Specific test that failed]
**Expected:** [What should happen]
**Actual:** [What actually happened]
**Browser:** [Chrome/Firefox/Safari + version]
**Console Errors:** [Copy exact error messages]
**Screenshots:** [Attach relevant screenshots]
**Reproduction Steps:** 
1. [Step 1]
2. [Step 2]
3. [Step 3]
```

### Next Steps After Testing:
1. **If all tests pass:** Plugin ready for production deployment
2. **If minor issues found:** Document for future improvement
3. **If major issues found:** Require fixes before production
4. **Update stakeholders:** Share results and recommendations

This enhanced manual testing procedure provides comprehensive, beginner-friendly instructions for verifying all Priority 1-4 fixes in the Monthly Booking Plugin.
