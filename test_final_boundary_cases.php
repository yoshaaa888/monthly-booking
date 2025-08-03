<?php

echo "=== FINAL BOUNDARY CASE TESTS ===\n\n";

function test_plan_determination($move_in_date, $move_out_date, $expected_plan) {
    $check_in = new DateTime($move_in_date);
    $check_out = new DateTime($move_out_date);
    
    $interval = $check_in->diff($check_out);
    $stay_days = $interval->days;
    
    $months = 0;
    $current_date = clone $check_in;
    
    while ($current_date < $check_out) {
        $original_day = (int)$current_date->format('d');
        $next_month = clone $current_date;
        $next_month->modify('+1 month');
        
        if ((int)$next_month->format('d') !== $original_day) {
            $next_month->modify('last day of previous month');
        }
        
        if ($next_month <= $check_out) {
            $months++;
            $current_date = clone $next_month;
        } else {
            $days_remaining = $current_date->diff($check_out)->days;
            if ($days_remaining >= 30) { // Strict 30-day minimum for partial month
                $months++;
            }
            break;
        }
    }
    
    if ($stay_days >= 7 && $months < 1) {
        $plan = 'SS';
    } elseif ($months >= 1 && $months < 3) {
        $plan = 'S';
    } elseif ($months >= 3 && $months < 6) {
        $plan = 'M';
    } elseif ($months >= 6) {
        $plan = 'L';
    } else {
        $plan = 'Invalid';
    }
    
    echo "Test: $move_in_date → $move_out_date\n";
    echo "Days: $stay_days, Months: $months\n";
    echo "Expected: $expected_plan Plan\n";
    echo "Actual: $plan Plan\n";
    echo "Result: " . (($plan == $expected_plan) ? "✅ PASS" : "❌ FAIL") . "\n\n";
}

echo "PRIORITY TEST CASES:\n";
echo "====================\n";
test_plan_determination('2025-01-15', '2025-02-14', 'S');
test_plan_determination('2025-01-31', '2025-02-28', 'S');
test_plan_determination('2028-01-31', '2028-02-29', 'S');
test_plan_determination('2025-12-20', '2026-01-19', 'S');

echo "BOUNDARY TESTS:\n";
echo "===============\n";
test_plan_determination('2025-01-01', '2025-01-30', 'SS');
test_plan_determination('2025-01-01', '2025-01-31', 'SS');
test_plan_determination('2025-08-03', '2025-08-29', 'SS');

echo "CALENDAR MONTH BOUNDARY TESTS:\n";
echo "==============================\n";
test_plan_determination('2025-01-01', '2025-04-01', 'M');
test_plan_determination('2025-01-01', '2025-03-31', 'S');
test_plan_determination('2025-01-01', '2025-07-01', 'L');
test_plan_determination('2025-01-01', '2025-06-30', 'M');

?>
