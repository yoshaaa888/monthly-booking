<?php
/**
 * Final Unified Campaign Integration Script
 * Removes old schema conflicts and inserts user campaigns with proper column mapping
 */

if (file_exists('wp-config.php')) {
    require_once('wp-config.php');
} else {
    echo "Error: wp-config.php not found. Please run this script from WordPress root directory.\n";
    exit(1);
}

global $wpdb;

echo "=== Final Unified Campaign Integration ===\n\n";

echo "Step 1: Clearing existing conflicting campaigns...\n";
$deleted = $wpdb->query("DELETE FROM {$wpdb->prefix}monthly_campaigns WHERE campaign_name IN ('即入居割20%', '早割10%', '早割キャンペーン', '即入居割')");
echo "Deleted {$deleted} existing campaigns.\n\n";

echo "Step 2: Inserting unified campaigns with authoritative schema...\n";

$unified_campaigns = array(
    array(
        'campaign_name' => '即入居割20%',
        'campaign_description' => '入居7日以内のご予約で賃料・共益費20%OFF 即入居',
        'discount_type' => 'percentage',
        'discount_value' => 20.00,
        'min_stay_days' => 7,
        'max_discount_amount' => 80000.00,
        'applicable_rooms' => '',
        'start_date' => '2025-01-01',
        'end_date' => '2099-12-31',
        'booking_start_date' => '2025-01-01',
        'booking_end_date' => '2099-12-31',
        'usage_limit' => 50,
        'usage_count' => 0,
        'is_active' => 1
    ),
    array(
        'campaign_name' => '早割10%',
        'campaign_description' => '入居30日以上前のご予約で賃料・共益費10%OFF 早割',
        'discount_type' => 'percentage',
        'discount_value' => 10.00,
        'min_stay_days' => 7,
        'max_discount_amount' => 50000.00,
        'applicable_rooms' => '',
        'start_date' => '2025-01-01',
        'end_date' => '2099-12-31',
        'booking_start_date' => '2025-01-01',
        'booking_end_date' => '2099-12-31',
        'usage_limit' => 100,
        'usage_count' => 0,
        'is_active' => 1
    )
);

$success_count = 0;
foreach ($unified_campaigns as $campaign) {
    $result = $wpdb->insert($wpdb->prefix . 'monthly_campaigns', $campaign);
    
    if ($result === false) {
        echo "❌ Error inserting campaign: {$campaign['campaign_name']}\n";
        echo "   Error: {$wpdb->last_error}\n";
    } else {
        echo "✅ Successfully inserted: {$campaign['campaign_name']}\n";
        $success_count++;
    }
}

echo "\n=== Integration Summary ===\n";
echo "Total campaigns: " . count($unified_campaigns) . "\n";
echo "Successfully inserted: {$success_count}\n";
echo "Errors: " . (count($unified_campaigns) - $success_count) . "\n\n";

if ($success_count == count($unified_campaigns)) {
    echo "✅ Final unified campaign integration completed successfully!\n";
    echo "Schema conflicts resolved and hardcoded logic removed.\n";
} else {
    echo "❌ Integration completed with errors.\n";
    echo "Please check the error messages above.\n";
}

echo "\n=== Verification ===\n";
echo "Run test_unified_campaigns.php to verify the unified campaign system.\n";
?>
