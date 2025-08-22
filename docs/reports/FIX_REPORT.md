# FIX REPORT

- Timestamp: YYYY-MM-DD HH:mm:ss (JST)
- Executor: admin user
- Environment: WordPress Admin

## Campaign Reference Unification
- Code change: booking-logic.php uses {$wpdb->prefix}monthly_campaigns
- Additional: campaign-manager.php references unified to monthly_campaigns
- Verification: Estimate/booking paths read monthly_campaigns without error

## Rates Auto-Fix (fix_missing_rate_plans)
- Target room_id: 633 (example)
- Before snapshot (saved to audit log batch_id): ...
- Action: Created missing SS/S/M/L rows with base_price=daily_rent, cleaning_fee=0, service_fee=0, valid_from=today, is_active=1
- After state: ...
- Result: Consistency dashboard “必須プラン欠損” cleared
- Rollback: batch_id ..., executed/not executed

## Options Display Order Auto-Fix (fix_duplicate_display_orders)
- Before snapshot (audit log batch_id): ...
- Action: Reassigned duplicates to next available display_order values
- After state: no duplicates
- Rollback: batch_id ..., executed/not executed

## Notes
- All fix actions are gated by manage_options and nonce checks
- Audit log table: wp_mb_audit_log, batch_id recorded for rollback
- No schema changes; only scoped INSERT/UPDATE on impacted rows
