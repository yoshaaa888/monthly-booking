<?php
/**
 * Test Hardcode Removal Verification
 * Verifies that all hardcoded discount logic has been removed from the codebase
 */

echo "=== Hardcode Removal Verification Test ===\n";
echo "Checking for remaining hardcoded discount logic in the codebase\n\n";

function check_file_for_hardcode($file_path, $patterns) {
    if (!file_exists($file_path)) {
        echo "❌ File not found: $file_path\n";
        return false;
    }
    
    $content = file_get_contents($file_path);
    $found_hardcode = false;
    
    echo "Checking file: $file_path\n";
    
    foreach ($patterns as $pattern_name => $pattern) {
        if (preg_match($pattern, $content)) {
            echo "❌ FOUND HARDCODE: $pattern_name\n";
            $found_hardcode = true;
        } else {
            echo "✅ Clean: $pattern_name\n";
        }
    }
    
    return !$found_hardcode;
}

$hardcode_patterns = array(
    'SS plan hardcode multiplication' => '/\$daily_rent\s*\*=?\s*0\.8/',
    'SS plan hardcode conditional' => '/if\s*\(\s*\$plan\s*===?\s*[\'"]SS[\'"]\s*\).*\$daily_rent.*\*.*0\.8/',
    'Immediate discount hardcode' => '/即入居.*\$daily_rent.*\*.*0\.8/',
    'Plan-specific discount hardcode' => '/if\s*\(\s*\$plan\s*===?\s*[\'"]SS[\'"]\s*\).*\{[^}]*\$daily_rent[^}]*\*[^}]*0\.8/',
    'Direct 20% discount hardcode' => '/\$daily_rent.*\*.*0\.8|0\.8.*\*.*\$daily_rent/'
);

$files_to_check = array(
    'includes/booking-logic.php',
    'assets/estimate.js'
);

echo "=== Hardcode Pattern Detection ===\n";
$all_clean = true;

foreach ($files_to_check as $file) {
    $file_clean = check_file_for_hardcode($file, $hardcode_patterns);
    $all_clean = $all_clean && $file_clean;
    echo "\n";
}

echo "=== Unified Campaign Integration Verification ===\n";

function verify_unified_integration($file_path) {
    if (!file_exists($file_path)) {
        echo "❌ File not found: $file_path\n";
        return false;
    }
    
    $content = file_get_contents($file_path);
    
    $required_patterns = array(
        'Campaign manager integration' => '/get_applicable_campaigns/',
        'Unified discount application' => '/campaign.*discount_type.*percentage/',
        'Original daily rent tracking' => '/original_daily_rent/'
    );
    
    echo "Verifying unified integration in: $file_path\n";
    $integration_complete = true;
    
    foreach ($required_patterns as $pattern_name => $pattern) {
        if (preg_match($pattern, $content)) {
            echo "✅ Found: $pattern_name\n";
        } else {
            echo "❌ Missing: $pattern_name\n";
            $integration_complete = false;
        }
    }
    
    return $integration_complete;
}

$integration_files = array(
    'includes/booking-logic.php'
);

$integration_complete = true;
foreach ($integration_files as $file) {
    $file_integration = verify_unified_integration($file);
    $integration_complete = $integration_complete && $file_integration;
    echo "\n";
}

echo "=== UI Display Verification ===\n";

function verify_ui_display($file_path) {
    if (!file_exists($file_path)) {
        echo "❌ File not found: $file_path\n";
        return false;
    }
    
    $content = file_get_contents($file_path);
    
    $ui_patterns = array(
        'Pre-discount display' => '/割引前/',
        'Post-discount display' => '/適用後/',
        'Campaign name display' => '/campaign.*name/',
        'Original daily rent check' => '/original_daily_rent/'
    );
    
    echo "Verifying UI display in: $file_path\n";
    $ui_complete = true;
    
    foreach ($ui_patterns as $pattern_name => $pattern) {
        if (preg_match($pattern, $content)) {
            echo "✅ Found: $pattern_name\n";
        } else {
            echo "❌ Missing: $pattern_name\n";
            $ui_complete = false;
        }
    }
    
    return $ui_complete;
}

$ui_files = array(
    'assets/estimate.js'
);

$ui_complete = true;
foreach ($ui_files as $file) {
    $file_ui = verify_ui_display($file);
    $ui_complete = $ui_complete && $file_ui;
    echo "\n";
}

echo "=== Summary ===\n";

if ($all_clean) {
    echo "✅ PASS: All hardcoded discount logic has been removed\n";
} else {
    echo "❌ FAIL: Hardcoded discount logic still exists\n";
}

if ($integration_complete) {
    echo "✅ PASS: Unified campaign integration is complete\n";
} else {
    echo "❌ FAIL: Unified campaign integration is incomplete\n";
}

if ($ui_complete) {
    echo "✅ PASS: UI display for pre/post discount is implemented\n";
} else {
    echo "❌ FAIL: UI display for pre/post discount is incomplete\n";
}

$overall_success = $all_clean && $integration_complete && $ui_complete;

echo "\n=== Overall Result ===\n";
if ($overall_success) {
    echo "🎉 SUCCESS: Unified campaign integration is complete!\n";
    echo "✅ All hardcoded discount logic removed\n";
    echo "✅ Unified campaign system integrated\n";
    echo "✅ UI displays pre/post discount amounts\n";
} else {
    echo "❌ INCOMPLETE: Additional work required\n";
    if (!$all_clean) echo "- Remove remaining hardcoded discount logic\n";
    if (!$integration_complete) echo "- Complete unified campaign integration\n";
    if (!$ui_complete) echo "- Implement UI display for discount breakdown\n";
}

echo "\n=== Next Steps ===\n";
echo "1. Run comprehensive campaign tests\n";
echo "2. Test in WordPress environment\n";
echo "3. Verify all plan types (SS/S/M/L) work correctly\n";
echo "4. Commit and push unified campaign integration\n";

echo "\n=== Test Complete ===\n";
