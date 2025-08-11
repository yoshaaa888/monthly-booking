# Production Deployment Checklist

## 🚀 Pre-Deployment Verification

### PR Status
- [x] PR #4: Unified Campaign Integration (Merged)
- [ ] PR #5: Type Column + コミコミ10万円キャンペーン (Pending Merge)

### Files Ready for Deployment
- [x] `monthly-booking-production-v2.1.0.zip` - Production plugin package
- [x] `production_campaign_setup.sql` - Campaign data insertion script
- [x] `production_verification_test.php` - Production testing script
- [x] `production_notes.md` - Migration documentation

## 📋 Deployment Steps

### 1. WordPress Plugin Deployment
```bash
# Upload monthly-booking-production-v2.1.0.zip to WordPress admin
# Navigate to: プラグイン → 新規追加 → プラグインのアップロード
# Activate plugin after upload
```

### 2. Database Schema Update
```sql
-- Execute in production database
ALTER TABLE wp_monthly_campaigns ADD COLUMN type VARCHAR(20) DEFAULT NULL;
ALTER TABLE wp_monthly_campaigns ADD COLUMN max_stay_days INT(3) DEFAULT NULL;
```

### 3. Campaign Data Insertion
```bash
# Execute production_campaign_setup.sql in production database
# Verify campaigns with: SELECT * FROM wp_monthly_campaigns WHERE is_active = 1;
```

### 4. Production Verification
```bash
# Run production_verification_test.php in WordPress environment
php production_verification_test.php
```

## 🧪 Testing Scenarios

### Immediate Move-in Discount (≤7 days)
- **Test Date**: Today + 3 days
- **Expected**: 20% discount applied
- **Badge**: "即入居" displayed
- **Verification**: Estimate screen + PDF output

### Early Booking Discount (≥30 days)
- **Test Date**: Today + 35 days
- **Expected**: 10% discount applied
- **Badge**: "早割" displayed
- **Verification**: Estimate screen + PDF output

### Gap Period (8-29 days)
- **Test Date**: Today + 15 days
- **Expected**: No campaign discount
- **Verification**: No badge displayed

### Boundary Conditions
- **7 days exactly**: Should apply immediate discount
- **30 days exactly**: Should apply early booking discount

## ✅ Success Criteria

### Estimate Screen Verification
- [ ] Campaign badges display correctly
- [ ] Discount percentages calculate accurately
- [ ] Final prices reflect campaign discounts
- [ ] Multiple campaigns follow "maximum 1" rule

### PDF Output Verification
- [ ] Campaign information appears in PDF
- [ ] Discount amounts are correctly shown
- [ ] Campaign names are properly displayed

### Database Verification
- [ ] Campaign records inserted correctly
- [ ] Type-based matching works
- [ ] Existing booking data unaffected

## 🔧 Troubleshooting

### Common Issues
1. **Type column missing**: Run ALTER TABLE commands
2. **Campaigns not applying**: Check is_active=1 and date ranges
3. **Multiple campaigns applying**: Verify priority logic

### Log Locations
- WordPress: `wp-content/debug.log`
- Campaign Manager: `includes/campaign-manager.php`
- Booking Logic: `includes/booking-logic.php`

## 📊 Post-Deployment Actions

### Immediate Tasks
1. Verify all test scenarios pass
2. Check estimate screen functionality
3. Confirm PDF output includes campaigns
4. Monitor error logs for issues

### Next Phase Preparation
1. Begin コミコミ10万円キャンペーン frontend implementation
2. Update estimate screen UI for flatrate campaigns
3. Implement stay duration validation (7-10 days)
4. Add fixed price display logic

## 📝 Deployment Notes

- **Schema Migration**: Type-based campaign matching replaces description-based
- **Backward Compatibility**: Old campaigns still work during transition
- **Performance**: Database queries optimized for type-based filtering
- **Security**: All campaign data validated and sanitized

## 🎯 Success Metrics

- ✅ Immediate discount: 20% for ≤7 days
- ✅ Early booking discount: 10% for ≥30 days
- ✅ Gap period: No discount for 8-29 days
- ✅ Maximum 1 campaign rule enforced
- ✅ PDF output includes campaign details
