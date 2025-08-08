# Manual Testing Procedures
## Monthly Booking Plugin Priority 1-4 Fixes Verification

**Target Environment:** Local WP at `http://t-monthlycampaign.local/`  
**Plugin Version:** v2.2-final  
**Testing Focus:** Priority 1-4 fix verification

---

## 🎯 Testing Overview

This document provides step-by-step manual testing procedures to verify that Priority 1-4 fixes are working correctly in the WordPress admin interface.

### Prerequisites:
- ✅ Local WP environment running
- ✅ WordPress admin access
- ✅ Monthly Booking plugin activated
- ✅ Test data loaded via `seed_data.sql`

---

## 🔴 Priority 1: 料金データ一元化 Testing

### **Test Case 1.1: Verify Default Daily Rent Section Removal**

**Objective:** Confirm "デフォルト日額賃料" section is completely removed from fee settings

**Steps:**
1. Login to WordPress admin: `http://t-monthlycampaign.local/wp-admin/`
2. Navigate to **Monthly Room Booking** → **料金設定**
3. Scroll through entire page content
4. **Expected Result:** No "デフォルト日額賃料" section visible
5. **Expected Result:** Only unified fee categories displayed:
   - 基本料金 (Basic Fees)
   - 光熱費 (Utilities)
   - 追加人数料金 (Additional Person Fees)
   - オプション割引設定 (Option Discount Settings)

**Pass Criteria:** ✅ No default daily rent section found

### **Test Case 1.2: Verify Unified Fee Management**

**Steps:**
1. In **料金設定** page, modify any fee value
2. Click **設定を保存** button
3. Refresh page and verify value persists
4. **Expected Result:** Fee updates save correctly through unified system

**Pass Criteria:** ✅ Fee modifications save and persist correctly

---

## 🟡 Priority 2: JavaScript安定化 Testing

### **Test Case 2.1: Calendar Error Handling**

**Objective:** Verify JavaScript errors don't crash the system

**Steps:**
1. Navigate to **Monthly Room Booking** → **予約カレンダー**
2. Open browser Developer Tools (F12)
3. Go to **Console** tab
4. Select different rooms from dropdown
5. **Expected Result:** No uncaught JavaScript errors
6. **Expected Result:** If errors occur, user-friendly Japanese messages appear
7. **Expected Result:** Page remains functional despite errors

**Pass Criteria:** ✅ No system crashes from JavaScript errors

### **Test Case 2.2: Room Selection Dropdown**

**Steps:**
1. In **予約カレンダー** page, click room selection dropdown
2. **Expected Result:** Dropdown populates with room options
3. Select a room from dropdown
4. **Expected Result:** Calendar updates to show selected room
5. **Expected Result:** No JavaScript console errors

**Pass Criteria:** ✅ Room selection works without errors

### **Test Case 2.3: AJAX Error Handling**

**Steps:**
1. In **予約カレンダー** page, open Network tab in Developer Tools
2. Simulate network failure (disconnect internet temporarily)
3. Try to change room selection
4. **Expected Result:** User-friendly error message in Japanese
5. **Expected Result:** System remains responsive

**Pass Criteria:** ✅ Graceful error handling with Japanese messages

---

## 🟡 Priority 3: キャンペーン設定UI改善 Testing

### **Test Case 3.1: 180-Day Date Limits**

**Objective:** Verify campaign dates are limited to 180 days from today

**Steps:**
1. Navigate to **Monthly Room Booking** → **キャンペーン設定**
2. Click **新規キャンペーン追加** or similar button
3. In date picker for **開始日**, verify:
   - Minimum date is today
   - Maximum date is 180 days from today
4. In date picker for **終了日**, verify:
   - Maximum date is 180 days from today
5. **Expected Result:** Date pickers enforce 180-day limits

**Pass Criteria:** ✅ Date inputs limited to 180-day range

### **Test Case 3.2: Date Validation Messages**

**Steps:**
1. In campaign creation form, try to set end date beyond 180 days
2. Submit form
3. **Expected Result:** Japanese validation message appears:
   "終了日は今日から180日以内に設定してください。"
4. **Expected Result:** Form submission is prevented

**Pass Criteria:** ✅ Japanese validation messages for date limits

### **Test Case 3.3: UI Layout Improvements**

**Steps:**
1. Review campaign settings page layout
2. **Expected Result:** Modal sections are well-organized
3. **Expected Result:** Dynamic unit displays are clear
4. **Expected Result:** Form fields are logically grouped

**Pass Criteria:** ✅ Improved UI layout and organization

---

## 🟢 Priority 4: 不要ページ削除 Testing

### **Test Case 4.1: Plugin Settings Menu Removal**

**Objective:** Verify "プラグイン設定" menu item is completely removed

**Steps:**
1. In WordPress admin sidebar, locate **Monthly Room Booking** menu
2. Expand the menu to see all submenu items
3. **Expected Result:** Only these 7 items should be visible:
   - 物件マスタ管理 (Property Master Management)
   - 予約カレンダー (Booking Calendar)
   - 予約登録 (Booking Registration)
   - 売上サマリー (Sales Summary)
   - キャンペーン設定 (Campaign Settings)
   - オプション管理 (Options Management)
   - 料金設定 (Fee Settings)
4. **Expected Result:** No "プラグイン設定" menu item exists

**Pass Criteria:** ✅ Plugin settings menu completely removed

### **Test Case 4.2: Menu Functionality**

**Steps:**
1. Click each of the 7 remaining menu items
2. **Expected Result:** Each page loads without errors
3. **Expected Result:** No broken links or missing pages
4. **Expected Result:** All pages serve their intended purpose

**Pass Criteria:** ✅ All remaining menu items function correctly

---

## 🧪 Comprehensive Integration Testing

### **Test Case 5.1: End-to-End Admin Workflow**

**Steps:**
1. **Property Management:** Add/edit a room in 物件マスタ管理
2. **Calendar View:** Verify room appears in 予約カレンダー dropdown
3. **Campaign Setup:** Create a campaign in キャンペーン設定 with 180-day limit
4. **Fee Configuration:** Update fees in 料金設定 using unified system
5. **Booking Registration:** Test booking creation in 予約登録
6. **Sales Summary:** Verify data appears in 売上サマリー

**Pass Criteria:** ✅ Complete admin workflow functions without errors

### **Test Case 5.2: Error Recovery Testing**

**Steps:**
1. Intentionally trigger JavaScript errors (invalid room selection)
2. **Expected Result:** System recovers gracefully
3. Try invalid campaign dates
4. **Expected Result:** Validation prevents submission with clear messages
5. Test fee updates with invalid values
6. **Expected Result:** Proper validation and error handling

**Pass Criteria:** ✅ System handles errors gracefully throughout

---

## 📊 Test Results Template

### Priority 1 Results:
- [ ] Test 1.1: Default daily rent section removed
- [ ] Test 1.2: Unified fee management works

### Priority 2 Results:
- [ ] Test 2.1: Calendar error handling functional
- [ ] Test 2.2: Room selection dropdown works
- [ ] Test 2.3: AJAX error handling with Japanese messages

### Priority 3 Results:
- [ ] Test 3.1: 180-day date limits enforced
- [ ] Test 3.2: Japanese validation messages appear
- [ ] Test 3.3: UI layout improvements visible

### Priority 4 Results:
- [ ] Test 4.1: Plugin settings menu removed
- [ ] Test 4.2: All remaining menus functional

### Integration Results:
- [ ] Test 5.1: End-to-end workflow complete
- [ ] Test 5.2: Error recovery functional

---

## 🚨 Troubleshooting

### Common Issues:

**Issue:** Local WP site not accessible  
**Solution:** Check Local WP app, ensure site is started

**Issue:** Plugin not activated  
**Solution:** Go to Plugins → Activate Monthly Room Booking

**Issue:** Database tables missing  
**Solution:** Deactivate and reactivate plugin, or run `seed_data.sql`

**Issue:** JavaScript console errors  
**Solution:** This is expected - verify they don't crash the system

### Environment Verification:
```bash
# Check if site is accessible
curl http://t-monthlycampaign.local

# Verify WordPress is running
curl http://t-monthlycampaign.local/wp-admin/
```

---

## 📋 Testing Completion Checklist

- [ ] All Priority 1 tests passed
- [ ] All Priority 2 tests passed  
- [ ] All Priority 3 tests passed
- [ ] All Priority 4 tests passed
- [ ] Integration tests completed
- [ ] No critical errors found
- [ ] Documentation updated with results

**Testing Completed By:** ________________  
**Date:** ________________  
**Environment:** ________________  
**Overall Result:** ✅ PASS / ❌ FAIL  

---

## 📞 Support Information

If testing reveals issues:
1. Document specific error messages
2. Note browser console errors
3. Record steps to reproduce
4. Check WordPress debug.log
5. Report findings with screenshots

**Next Steps After Testing:**
- Update verification report with results
- Address any discovered issues
- Proceed with production deployment if all tests pass
