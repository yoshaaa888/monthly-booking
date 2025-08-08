# Frontend Testing Procedures - Shortcode Verification
## Monthly Booking Plugin v2.2-final

**Target Environment:** Local WP at `http://t-monthlycampaign.local/`  
**Testing Focus:** Frontend shortcode functionality and user experience  
**Shortcodes:** `[monthly_booking_estimate]`, `[monthly_booking_calendar]`, `[monthly_booking_admin]`

---

## 🎯 Frontend Testing Overview

This document provides comprehensive testing procedures for the three main shortcodes that power the frontend functionality of the Monthly Booking Plugin. These tests verify that Priority 1-4 improvements work correctly from the user perspective.

### Shortcode Functions:
- **`[monthly_booking_estimate]`** - Booking estimate form with pricing calculation
- **`[monthly_booking_calendar]`** - 6-month availability calendar with room selection
- **`[monthly_booking_admin]`** - Admin interface for booking management

### Testing Objectives:
- Verify shortcodes render correctly on frontend pages
- Test JavaScript functionality and error handling (Priority 2)
- Confirm unified pricing system works in estimates (Priority 1)
- Validate 180-day limits in calendar display (Priority 3)
- Ensure stable user experience across all interfaces

---

## 📄 Test Page Setup Requirements

### Step 1: Create Test Pages

Before testing shortcodes, create WordPress pages with the following content:

#### 1.1 Estimate Test Page
```
Page Title: Monthly Booking Estimate Test
Page Slug: estimate-test
Content:
---
<h2>🏠 月額マンスリー見積り</h2>
<p>ご希望の条件を入力して、料金を確認してください。</p>

[monthly_booking_estimate]

<div style="margin-top: 30px; padding: 20px; background-color: #f9f9f9;">
<h3>📋 テスト確認項目</h3>
<ul>
<li>フォームが正常に表示される</li>
<li>部屋選択ドロップダウンが動作する</li>
<li>料金計算が正確に実行される</li>
<li>エラーハンドリングが適切に動作する</li>
</ul>
</div>
---
```

#### 1.2 Calendar Test Page
```
Page Title: Monthly Booking Calendar Test
Page Slug: calendar-test
Content:
---
<h2>📅 予約カレンダー・空室状況</h2>
<p>各物件の予約状況をカレンダーで確認できます。</p>

[monthly_booking_calendar]

<div style="margin-top: 30px; padding: 20px; background-color: #f0f8f0;">
<h3>📊 テスト確認項目</h3>
<ul>
<li>6ヶ月カレンダーが表示される</li>
<li>部屋選択機能が動作する</li>
<li>予約状況が正確に表示される</li>
<li>180日制限が適用されている</li>
</ul>
</div>
---
```

#### 1.3 Admin Test Page (Private)
```
Page Title: Monthly Booking Admin Test
Page Slug: admin-test
Visibility: Private (管理者のみ)
Content:
---
<h2>🔧 予約管理システム</h2>
<p>管理者用の予約管理機能です。</p>

[monthly_booking_admin]

<div style="margin-top: 30px;">
<h3>📋 テスト確認項目</h3>
<ul>
<li>管理インターフェースが表示される</li>
<li>予約一覧が正常に動作する</li>
<li>編集機能が適切に動作する</li>
</ul>
</div>
---
```

---

## 🏠 Test Case 1: Monthly Booking Estimate Shortcode

### **Test Case 1.1: Basic Form Rendering**

**Objective:** Verify estimate form displays correctly with all required elements

**Steps:**
1. **Navigate to estimate test page**
   - URL: `http://t-monthlycampaign.local/estimate-test/`
   - **Visual Confirmation:** Page loads without errors

2. **Verify form structure**
   - **Check for:** Form title "月額賃貸見積もり"
   - **Check for:** Room selection dropdown
   - **Check for:** Date input fields (入居日/退去日)
   - **Check for:** Person count selector
   - **Check for:** Submit button

3. **Test responsive design**
   - **Resize browser window** to mobile size
   - **Expected Result:** Form adapts to smaller screen
   - **Check for:** All elements remain accessible

**Pass Criteria:** ✅ Form renders completely with all required elements

### **Test Case 1.2: Room Selection Functionality**

**Objective:** Verify room dropdown populates and functions correctly

**Steps:**
1. **Test room dropdown population**
   - Click on room selection dropdown
   - **Expected Result:** Dropdown shows available rooms
   - **Example options:** "Room 101", "Room 102", etc.

2. **Test room selection**
   - Select different rooms from dropdown
   - **Expected Result:** Selection updates correctly
   - **Monitor console:** No JavaScript errors

3. **Test empty state handling**
   - **If no rooms available:** Should show appropriate message
   - **Expected message:** "部屋が選択されていません" or similar

**Pass Criteria:** ✅ Room selection works without errors, appropriate messaging

### **Test Case 1.3: Pricing Calculation (Priority 1 Integration)**

**Objective:** Verify unified pricing system works correctly in estimates

**Steps:**
1. **Complete estimate form**
   - Select room: Any available room
   - Enter check-in date: Today + 7 days
   - Enter check-out date: Today + 37 days (30-day stay)
   - Select person count: 1 person

2. **Submit estimate request**
   - Click submit/calculate button
   - **Expected Result:** Pricing calculation displays

3. **Verify pricing components**
   - **Check for:** Daily rent calculation
   - **Check for:** Utility fees
   - **Check for:** Cleaning fees
   - **Check for:** Tax breakdown (課税/非課税)
   - **Check for:** Total amount

4. **Test pricing consistency**
   - **Compare with admin fee settings:** Values should match
   - **No duplicate pricing sources:** Single source of truth (Priority 1)

**Pass Criteria:** ✅ Pricing calculation accurate, consistent with admin settings

### **Test Case 1.4: Error Handling (Priority 2 Integration)**

**Objective:** Verify JavaScript error handling works correctly

**Steps:**
1. **Test invalid date inputs**
   - Enter past dates or invalid date formats
   - **Expected Result:** Validation messages in Japanese
   - **System should:** Remain functional, not crash

2. **Test network error simulation**
   - Open browser Developer Tools → Network tab
   - Set throttling to "Offline"
   - Try submitting estimate
   - **Expected Result:** User-friendly Japanese error message

3. **Test form validation**
   - Submit form with missing required fields
   - **Expected Result:** Clear validation messages
   - **Language:** Japanese error messages

**Pass Criteria:** ✅ Graceful error handling with Japanese messages

---

## 📅 Test Case 2: Monthly Booking Calendar Shortcode

### **Test Case 2.1: Calendar Display and Structure**

**Objective:** Verify 6-month calendar renders correctly with proper structure

**Steps:**
1. **Navigate to calendar test page**
   - URL: `http://t-monthlycampaign.local/calendar-test/`
   - **Visual Confirmation:** Page loads without errors

2. **Verify calendar structure**
   - **Check for:** 6 months displayed (current month + 5 future months)
   - **Check for:** Month headers in Japanese (年月 format)
   - **Check for:** Day-of-week headers (日月火水木金土)
   - **Check for:** Calendar grid layout

3. **Test calendar legend**
   - **Check for:** Legend showing availability symbols
   - **Expected symbols:** 〇 (available), × (booked), △ (campaign)
   - **Check for:** Color coding matches legend

**Pass Criteria:** ✅ 6-month calendar displays with proper structure and legend

### **Test Case 2.2: Room Selection and Calendar Updates**

**Objective:** Verify room selection updates calendar data correctly

**Steps:**
1. **Test room selector dropdown**
   - **Check for:** Room selection dropdown above calendar
   - **Expected label:** "部屋を選択:" or similar
   - **Dropdown should:** Populate with available rooms

2. **Test room switching**
   - Select different rooms from dropdown
   - **Expected Result:** Calendar updates to show selected room's data
   - **Loading behavior:** Brief loading indicator during update
   - **Monitor console:** No JavaScript errors

3. **Test AJAX functionality**
   - **Monitor Network tab:** AJAX requests for room data
   - **Expected behavior:** Smooth transitions between rooms
   - **Error handling:** Graceful handling if request fails

**Pass Criteria:** ✅ Room selection updates calendar smoothly without errors

### **Test Case 2.3: 180-Day Limit Display (Priority 3 Integration)**

**Objective:** Verify calendar respects 180-day business constraint

**Steps:**
1. **Count displayed months**
   - **Expected:** 6 months maximum
   - **Calculate:** Today + 180 days should be maximum displayed date

2. **Verify date range**
   - **Start date:** Current month, today's date
   - **End date:** Should not exceed 180 days from today
   - **Future dates:** Beyond 180 days should not be displayed

3. **Test date calculations**
   - **Today:** August 8, 2025
   - **180 days later:** February 4, 2026
   - **Calendar should:** Not show dates beyond February 4, 2026

**Pass Criteria:** ✅ Calendar respects 180-day limit, no dates beyond constraint

### **Test Case 2.4: Availability Status Display**

**Objective:** Verify booking status and campaign information display correctly

**Steps:**
1. **Test availability symbols**
   - **Available dates:** Should show 〇 symbol
   - **Booked dates:** Should show × symbol
   - **Campaign dates:** Should show △ symbol

2. **Test hover/click interactions**
   - **Hover over dates:** Tooltip or additional info should appear
   - **Campaign dates:** Should show campaign name/details
   - **Booked dates:** Should indicate booking status

3. **Test visual indicators**
   - **Color coding:** Matches legend
   - **Today's date:** Should be highlighted differently
   - **Past dates:** Should be visually distinct (grayed out)

**Pass Criteria:** ✅ Availability status displays accurately with proper visual indicators

---

## 🔧 Test Case 3: Monthly Booking Admin Shortcode

### **Test Case 3.1: Admin Interface Access Control**

**Objective:** Verify admin shortcode respects user permissions

**Steps:**
1. **Test as logged-in admin**
   - Navigate to admin test page while logged in as admin
   - **Expected Result:** Admin interface displays fully

2. **Test as non-admin user** (if possible)
   - Create or use non-admin WordPress user
   - Navigate to admin test page
   - **Expected Result:** Access denied or limited functionality

3. **Test as logged-out user**
   - Log out of WordPress
   - Navigate to admin test page
   - **Expected Result:** Login prompt or access denied message

**Pass Criteria:** ✅ Proper access control based on user permissions

### **Test Case 3.2: Admin Interface Functionality**

**Objective:** Verify admin interface provides expected management capabilities

**Steps:**
1. **Test booking list display**
   - **Check for:** List of existing bookings
   - **Expected columns:** Date, room, customer, status
   - **Sorting/filtering:** Should be available if implemented

2. **Test booking management actions**
   - **Check for:** Edit, delete, view buttons
   - **Test functionality:** Click actions work correctly
   - **Error handling:** Graceful handling of failed actions

3. **Test data consistency**
   - **Compare with admin backend:** Data should match
   - **Real-time updates:** Changes should reflect immediately

**Pass Criteria:** ✅ Admin interface provides functional booking management

---

## 🧪 Integration Testing Across Shortcodes

### **Test Case 4.1: Cross-Shortcode Data Consistency**

**Objective:** Verify data consistency across all three shortcodes

**Steps:**
1. **Create test booking via estimate form**
   - Complete estimate and booking process
   - Note booking details (room, dates, amount)

2. **Verify in calendar shortcode**
   - Check calendar shows booking as unavailable
   - Verify dates are marked correctly

3. **Verify in admin shortcode**
   - Check booking appears in admin interface
   - Verify all details match original booking

**Pass Criteria:** ✅ Data consistency maintained across all shortcodes

### **Test Case 4.2: Performance and Loading**

**Objective:** Verify all shortcodes load efficiently

**Steps:**
1. **Test page load times**
   - **Measure:** Time to fully render each shortcode
   - **Expected:** < 3 seconds for initial load
   - **Monitor:** Network requests and resource loading

2. **Test concurrent usage**
   - **Open multiple tabs:** With different shortcodes
   - **Expected:** No performance degradation
   - **Memory usage:** Should remain reasonable

**Pass Criteria:** ✅ All shortcodes load efficiently without performance issues

---

## 📊 Frontend Testing Results Template

### Estimate Shortcode Results:
- [ ] ✅ PASS / ❌ FAIL - Basic form rendering
- [ ] ✅ PASS / ❌ FAIL - Room selection functionality
- [ ] ✅ PASS / ❌ FAIL - Pricing calculation accuracy
- [ ] ✅ PASS / ❌ FAIL - Error handling and validation
- **Screenshots:** `estimate_shortcode_test.png`
- **Notes:** ________________________________

### Calendar Shortcode Results:
- [ ] ✅ PASS / ❌ FAIL - 6-month calendar display
- [ ] ✅ PASS / ❌ FAIL - Room selection and updates
- [ ] ✅ PASS / ❌ FAIL - 180-day limit compliance
- [ ] ✅ PASS / ❌ FAIL - Availability status display
- **Screenshots:** `calendar_shortcode_test.png`
- **Notes:** ________________________________

### Admin Shortcode Results:
- [ ] ✅ PASS / ❌ FAIL - Access control functionality
- [ ] ✅ PASS / ❌ FAIL - Admin interface features
- **Screenshots:** `admin_shortcode_test.png`
- **Notes:** ________________________________

### Integration Results:
- [ ] ✅ PASS / ❌ FAIL - Cross-shortcode data consistency
- [ ] ✅ PASS / ❌ FAIL - Performance and loading
- **Notes:** ________________________________

---

## 🚨 Frontend Testing Troubleshooting

### Common Issues:

**Issue:** Shortcode displays as text instead of rendering
**Solution:** 
1. Verify plugin is activated
2. Check for PHP errors in debug.log
3. Ensure shortcode syntax is correct

**Issue:** JavaScript functionality not working
**Solution:**
1. Check browser console for errors
2. Verify jQuery is loaded
3. Check for plugin conflicts

**Issue:** Styling/CSS not applied
**Solution:**
1. Check if plugin CSS is loading
2. Verify theme compatibility
3. Clear browser and WordPress cache

**Issue:** AJAX requests failing
**Solution:**
1. Check WordPress AJAX endpoints
2. Verify nonce validation
3. Check server error logs

---

## 📋 Frontend Testing Completion Checklist

- [ ] Test pages created with all three shortcodes
- [ ] Estimate shortcode fully tested and documented
- [ ] Calendar shortcode fully tested and documented
- [ ] Admin shortcode fully tested and documented
- [ ] Integration testing completed
- [ ] Performance testing completed
- [ ] Screenshots captured for all tests
- [ ] Issues documented with reproduction steps
- [ ] Overall frontend functionality assessment completed

**Frontend Testing Completed By:** ________________  
**Date:** ________________  
**Environment:** Local WP at t-monthlycampaign.local  
**Overall Frontend Result:** ✅ PASS / ❌ FAIL  
**Ready for User Testing:** ✅ YES / ❌ NO

This comprehensive frontend testing procedure ensures all shortcode functionality works correctly and integrates properly with the Priority 1-4 improvements.
