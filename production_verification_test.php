<?php
/**
 * Production Environment Campaign Verification
 * Test immediate move-in and early booking discounts in production
 */

if (file_exists('/var/www/html/monthly-booking/wp-config.php')) {
    require_once('/var/www/html/monthly-booking/wp-config.php');
    echo "WordPress production environment loaded\n";
} else {
    echo "WordPress production environment not found\n";
    exit(1);
}

echo "\n=== 本番環境キャンペーン動作確認 ===\n\n";

global $wpdb;
$table_name = $wpdb->prefix . 'monthly_campaigns';

$campaigns = $wpdb->get_results("SELECT * FROM $table_name WHERE is_active = 1 ORDER BY campaign_name");
echo "アクティブキャンペーン:\n";
foreach ($campaigns as $campaign) {
    echo "- {$campaign->campaign_name}: type={$campaign->type}, {$campaign->discount_value}%\n";
}

require_once('includes/campaign-manager.php');
$campaign_manager = new MonthlyBooking_Campaign_Manager();

echo "\n=== 即入居割テスト (type-based) ===\n";
$immediate_date = date('Y-m-d', strtotime('+3 days'));
$immediate_result = $campaign_manager->calculate_campaign_discount($immediate_date, 100000, 100000);

echo "チェックイン: {$immediate_date} (+3日)\n";
if ($immediate_result['campaign_name']) {
    echo "✅ PASS: {$immediate_result['campaign_name']}\n";
    echo "割引: {$immediate_result['discount_value']}% (¥" . number_format($immediate_result['discount_amount']) . ")\n";
    echo "最終: ¥" . number_format(100000 - $immediate_result['discount_amount']) . "\n";
} else {
    echo "❌ FAIL: 即入居割が適用されませんでした\n";
}

echo "\n=== 早割テスト (type-based) ===\n";
$early_date = date('Y-m-d', strtotime('+35 days'));
$early_result = $campaign_manager->calculate_campaign_discount($early_date, 100000, 100000);

echo "チェックイン: {$early_date} (+35日)\n";
if ($early_result['campaign_name']) {
    echo "✅ PASS: {$early_result['campaign_name']}\n";
    echo "割引: {$early_result['discount_value']}% (¥" . number_format($early_result['discount_amount']) . ")\n";
    echo "最終: ¥" . number_format(100000 - $early_result['discount_amount']) . "\n";
} else {
    echo "❌ FAIL: 早割が適用されませんでした\n";
}

echo "\n=== ギャップ期間テスト ===\n";
$gap_date = date('Y-m-d', strtotime('+15 days'));
$gap_result = $campaign_manager->calculate_campaign_discount($gap_date, 100000, 100000);

echo "チェックイン: {$gap_date} (+15日)\n";
if (!$gap_result['campaign_name']) {
    echo "✅ PASS: ギャップ期間でキャンペーン適用なし\n";
} else {
    echo "❌ FAIL: ギャップ期間でキャンペーンが適用されました: {$gap_result['campaign_name']}\n";
}

echo "\n=== 境界値テスト ===\n";

$boundary_7_date = date('Y-m-d', strtotime('+7 days'));
$boundary_7_result = $campaign_manager->calculate_campaign_discount($boundary_7_date, 100000, 100000);
echo "7日後 ({$boundary_7_date}): ";
if ($boundary_7_result['campaign_name']) {
    echo "✅ {$boundary_7_result['campaign_name']} 適用\n";
} else {
    echo "⚪ キャンペーン適用なし\n";
}

$boundary_30_date = date('Y-m-d', strtotime('+30 days'));
$boundary_30_result = $campaign_manager->calculate_campaign_discount($boundary_30_date, 100000, 100000);
echo "30日後 ({$boundary_30_date}): ";
if ($boundary_30_result['campaign_name']) {
    echo "✅ {$boundary_30_result['campaign_name']} 適用\n";
} else {
    echo "⚪ キャンペーン適用なし\n";
}

echo "\n=== 本番環境動作確認完了 ===\n";
echo "実行時刻: " . date('Y-m-d H:i:s') . "\n";
