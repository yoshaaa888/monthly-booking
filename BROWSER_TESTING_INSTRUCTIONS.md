# Browser Testing Instructions for Priority 1-4 Verification
## Monthly Booking Plugin v2.2-final

**Environment:** http://t-monthlycampaign.local/wp-admin/  
**Date:** August 8, 2025  
**Status:** Ready for User Execution

---

## 🎯 Testing Overview

Since the Local WP environment is accessible to you but not from this testing environment, please execute these browser-based verification tests to confirm all Priority 1-4 fixes are working correctly.

---

## 🔴 Priority 1: 料金データ一元化 Browser Testing

### Test 1.1: Verify Default Daily Rent Section Removal

**Steps:**
1. Navigate to: `http://t-monthlycampaign.local/wp-admin/`
2. Login to WordPress admin
3. Click **Monthly Room Booking** in sidebar
4. Click **料金設定** (Fee Settings)
5. **VERIFY:** No "デフォルト日額賃料" section exists on the page
6. **VERIFY:** Only these fee categories are visible:
   - 基本料金 (Basic Fees)
   - 光熱費 (Utilities) 
   - 追加人数料金 (Additional Person Fees)
   - オプション割引設定 (Option Discount Settings)

**Expected Result:** ✅ Default daily rent section completely removed

### Test 1.2: Fee Management Functionality

**Steps:**
1. In 料金設定 page, modify any fee value
2. Click **設定を保存** button
3. Refresh page
4. **VERIFY:** Modified value persists correctly

**Expected Result:** ✅ Unified fee system works correctly

---

## 🟡 Priority 2: JavaScript安定化 Browser Testing

### Test 2.1: Calendar Error Handling

**Steps:**
1. Navigate to **Monthly Room Booking** → **予約カレンダー**
2. Open browser Developer Tools (F12)
3. Go to **Console** tab
4. Try selecting different rooms from dropdown
5. **VERIFY:** No uncaught JavaScript errors crash the system
6. **VERIFY:** If errors occur, Japanese error messages appear
7. **VERIFY:** Page remains functional despite any errors

**Expected Result:** ✅ JavaScript errors don't crash the system

### Test 2.2: Room Selection Dropdown

**Steps:**
1. In 予約カレンダー page, click room selection dropdown
2. **VERIFY:** Dropdown populates with room options
3. Select a room
4. **VERIFY:** Calendar updates correctly
5. **VERIFY:** No console errors appear

**Expected Result:** ✅ Room selection works without system failure

---

## 🟡 Priority 3: キャンペーン設定UI改善 Browser Testing

### Test 3.1: 180-Day Date Limits

**Steps:**
1. Navigate to **Monthly Room Booking** → **キャンペーン設定**
2. Click button to add new campaign
3. Check date picker for **開始日** (Start Date):
   - **VERIFY:** Minimum date is today
   - **VERIFY:** Maximum date is 180 days from today
4. Check date picker for **終了日** (End Date):
   - **VERIFY:** Maximum date is 180 days from today

**Expected Result:** ✅ Date pickers enforce 180-day limits

### Test 3.2: Date Validation Messages

**Steps:**
1. Try to manually enter end date beyond 180 days
2. Submit form
3. **VERIFY:** Japanese validation message appears:
   "終了日は今日から180日以内に設定してください。"
4. **VERIFY:** Form submission is prevented

**Expected Result:** ✅ Japanese validation prevents invalid dates

### Test 3.3: UI Layout Improvements

**Steps:**
1. Review campaign settings page layout
2. **VERIFY:** Modal sections are well-organized
3. **VERIFY:** Form fields are logically grouped
4. **VERIFY:** UI is more user-friendly than before

**Expected Result:** ✅ Improved UI layout visible

---

## 🟢 Priority 4: 不要ページ削除 Browser Testing

### Test 4.1: Plugin Settings Menu Removal

**Steps:**
1. In WordPress admin sidebar, locate **Monthly Room Booking** menu
2. Expand the menu to see all submenu items
3. **VERIFY:** Only these 7 items are visible:
   - 物件マスタ管理 (Property Master Management)
   - 予約カレンダー (Booking Calendar)
   - 予約登録 (Booking Registration)
   - 売上サマリー (Sales Summary)
   - キャンペーン設定 (Campaign Settings)
   - オプション管理 (Options Management)
   - 料金設定 (Fee Settings)
4. **VERIFY:** No "プラグイン設定" menu item exists anywhere

**Expected Result:** ✅ Plugin settings menu completely removed

### Test 4.2: Menu Functionality

**Steps:**
1. Click each of the 7 remaining menu items
2. **VERIFY:** Each page loads without errors
3. **VERIFY:** No broken links or missing pages
4. **VERIFY:** All pages serve their intended purpose

**Expected Result:** ✅ All remaining menu items function correctly

---

## 📊 Test Results Recording

Please record your test results here:

### Priority 1 Results:
- [ ] ✅ PASS / ❌ FAIL - Default daily rent section removed
- [ ] ✅ PASS / ❌ FAIL - Unified fee management works
- **Notes:** ________________________________

### Priority 2 Results:
- [ ] ✅ PASS / ❌ FAIL - Calendar error handling functional
- [ ] ✅ PASS / ❌ FAIL - Room selection dropdown works
- [ ] ✅ PASS / ❌ FAIL - No system crashes from JavaScript errors
- **Notes:** ________________________________

### Priority 3 Results:
- [ ] ✅ PASS / ❌ FAIL - 180-day date limits enforced
- [ ] ✅ PASS / ❌ FAIL - Japanese validation messages appear
- [ ] ✅ PASS / ❌ FAIL - UI layout improvements visible
- **Notes:** ________________________________

### Priority 4 Results:
- [ ] ✅ PASS / ❌ FAIL - Plugin settings menu removed
- [ ] ✅ PASS / ❌ FAIL - All remaining menus functional
- **Notes:** ________________________________

---

## 🚨 Issue Reporting

If you discover any issues during testing:

### Issue Template:
```
**Priority:** [1/2/3/4]
**Test Case:** [Specific test that failed]
**Expected:** [What should happen]
**Actual:** [What actually happened]
**Browser Console Errors:** [Any JavaScript errors]
**Screenshots:** [If applicable]
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
