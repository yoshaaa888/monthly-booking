# Manual Testing Procedure for Bedding Fee Fix

## Overview
This document provides step-by-step manual testing procedures to verify that the bedding fee calculation bug has been fixed correctly.

## Bug Description
**Before Fix**: Bedding fees were calculated as a fixed ¥11,000 per person regardless of stay duration
**After Fix**: Bedding fees should be calculated as ¥1,100 per person per day × stay duration

## Test Scenarios

### Test Scenario 1: Single Additional Adult
**Setup**: 
- 大人2名 (1 additional adult)
- 10日滞在 (10-day stay)

**Expected Result**: 
- 追加布団費用 = 1名 × ¥1,100 × 10日 = ¥11,000
- Display should show: "布団代 (1名 × ¥1,100/日 × 10日)"

**Test Steps**:
1. Access the estimate form
2. Set check-in and check-out dates for exactly 10 days
3. Set adult count to 2, children count to 0
4. Click calculate estimate
5. Verify bedding fee shows ¥11,000 with daily rate display

### Test Scenario 2: Single Child
**Setup**:
- 大人1名、子ども1名 (1 adult, 1 child)
- 5日滞在 (5-day stay)

**Expected Result**:
- 追加布団費用 = 1名 × ¥1,100 × 5日 = ¥5,500
- Display should show: "布団代 (1名 × ¥1,100/日 × 5日)"

**Test Steps**:
1. Access the estimate form
2. Set check-in and check-out dates for exactly 5 days
3. Set adult count to 1, children count to 1
4. Click calculate estimate
5. Verify bedding fee shows ¥5,500 with daily rate display

### Test Scenario 3: Multiple Additional People
**Setup**:
- 大人3名、子ども2名 (3 adults, 2 children = 2 additional adults + 2 children)
- 7日滞在 (7-day stay)

**Expected Result**:
- 大人追加布団費用 = 2名 × ¥1,100 × 7日 = ¥15,400
- 子ども追加布団費用 = 2名 × ¥1,100 × 7日 = ¥15,400
- 合計追加布団費用 = ¥30,800
- Display should show both adult and children bedding fees separately

**Test Steps**:
1. Access the estimate form
2. Set check-in and check-out dates for exactly 7 days
3. Set adult count to 3, children count to 2
4. Click calculate estimate
5. Verify adult bedding fee shows ¥15,400
6. Verify children bedding fee shows ¥15,400
7. Verify total additional person fee includes both amounts

### Test Scenario 4: Edge Case - 1 Day Stay
**Setup**:
- 大人2名 (1 additional adult)
- 1日滞在 (1-day stay)

**Expected Result**:
- 追加布団費用 = 1名 × ¥1,100 × 1日 = ¥1,100
- Display should show: "布団代 (1名 × ¥1,100/日 × 1日)"

**Test Steps**:
1. Access the estimate form
2. Set check-in and check-out dates for exactly 1 day
3. Set adult count to 2, children count to 0
4. Click calculate estimate
5. Verify bedding fee shows ¥1,100 with daily rate display

## Verification Checklist

### Frontend Display Verification
- [ ] Bedding fee display text includes "¥1,100/日" instead of fixed "¥11,000"
- [ ] Display shows stay duration in the calculation (e.g., "× 10日")
- [ ] Adult and children bedding fees are calculated separately when both exist
- [ ] Total amounts match the daily rate calculation

### Backend Calculation Verification
- [ ] PHP calculation uses `$stay_days` variable in bedding fee formula
- [ ] Adult bedding fee: `$additional_adults * 1100 * $stay_days`
- [ ] Children bedding fee: `$additional_children * 1100 * $stay_days`
- [ ] Final totals include the correct daily-calculated bedding fees

### Cross-Validation
- [ ] Frontend JavaScript display matches backend PHP calculation
- [ ] Different stay durations produce proportionally different bedding fees
- [ ] Zero additional people results in zero bedding fees
- [ ] Bedding fees are included in taxable subtotal calculations

## Failure Detection
If any of the following occur, the fix has failed:
- Bedding fees still show as fixed ¥11,000 regardless of stay duration
- Display text still shows "× ¥11,000" instead of daily rate format
- Different stay durations produce the same bedding fee amount
- Frontend display doesn't match backend calculation results

## Test Environment
- WordPress admin interface: Monthly Room Booking plugin
- Frontend estimate form: [monthly_booking_estimate] shortcode page
- Test with various room types and plan durations (SS/S/M/L)
