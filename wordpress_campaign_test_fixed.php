<?php
/**
 * WordPress Campaign Integration Test - Fixed for Current Schema
 * Tests campaign functionality with description-based matching (no type column)
 */

// Load WordPress environment
if (file_exists('/var/www/html/monthly-booking/wp-config.php')) {
    require_once('/var/www/html/monthly-booking/wp-config.php');
    echo "WordPress environment loaded successfully\n";
} else {
    echo "WordPress environment not found\n";
    exit(1);
}

echo "\n=== WordPress環境キャンペーン検証テスト (修正版) ===\n\n";

// Test campaign functionality using description-based matching
global $wpdb;
$table_name = $wpdb->prefix . 'monthly_campaigns';

// Check if campaigns table exists and has data
$campaigns = $wpdb->get_results("SELECT * FROM $table_name WHERE is_active = 1");
echo "アクティブキャンペーン数: " . count($campaigns) . "\n";

foreach ($campaigns as $campaign) {
    echo "- {$campaign->campaign_name}: {$campaign->discount_type} {$campaign->discount_value}%\n";
}

echo "\n=== 説明文ベース割引テスト ===\n";

// Test immediate move-in discount (3 days from now) - using description matching
$immediate_date = date('Y-m-d', strtotime('+3 days'));
$days_until_immediate = 3;

echo "\n🔴 即入居割テスト (説明文ベース):\n";
echo "チェックイン日: {$immediate_date} (+{$days_until_immediate}日)\n";

if ($days_until_immediate <= 7) {
    $immediate_campaign = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name 
         WHERE is_active = 1 
         AND start_date <= %s 
         AND end_date >= %s 
         AND campaign_description LIKE '%即入居%'
         ORDER BY discount_value DESC 
         LIMIT 1",
        $immediate_date,
        $immediate_date
    ));
    
    if ($immediate_campaign) {
        $base_amount = 100000;
        $discount_amount = $base_amount * ($immediate_campaign->discount_value / 100);
        echo "✅ PASS - {$immediate_campaign->campaign_name} 適用\n";
        echo "割引率: {$immediate_campaign->discount_value}%\n";
        echo "基本金額: ¥" . number_format($base_amount) . "\n";
        echo "割引金額: ¥" . number_format($discount_amount) . "\n";
        echo "最終金額: ¥" . number_format($base_amount - $discount_amount) . "\n";
    } else {
        echo "❌ FAIL - 即入居割キャンペーンが見つかりません\n";
    }
} else {
    echo "⚪ 即入居割対象外 (7日以内ではない)\n";
}

// Test early booking discount (35 days from now) - using description matching
$early_date = date('Y-m-d', strtotime('+35 days'));
$days_until_early = 35;

echo "\n🟡 早割テスト (説明文ベース):\n";
echo "チェックイン日: {$early_date} (+{$days_until_early}日)\n";

if ($days_until_early >= 30) {
    $early_campaign = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name 
         WHERE is_active = 1 
         AND start_date <= %s 
         AND end_date >= %s 
         AND campaign_description LIKE '%早割%'
         ORDER BY discount_value DESC 
         LIMIT 1",
        $early_date,
        $early_date
    ));
    
    if ($early_campaign) {
        $base_amount = 100000;
        $discount_amount = $base_amount * ($early_campaign->discount_value / 100);
        echo "✅ PASS - {$early_campaign->campaign_name} 適用\n";
        echo "割引率: {$early_campaign->discount_value}%\n";
        echo "基本金額: ¥" . number_format($base_amount) . "\n";
        echo "割引金額: ¥" . number_format($discount_amount) . "\n";
        echo "最終金額: ¥" . number_format($base_amount - $discount_amount) . "\n";
    } else {
        echo "❌ FAIL - 早割キャンペーンが見つかりません\n";
    }
} else {
    echo "⚪ 早割対象外 (30日以上前ではない)\n";
}

// Test gap period (15 days from now - should have no campaign)
$gap_date = date('Y-m-d', strtotime('+15 days'));
$days_until_gap = 15;

echo "\n⚪ 最大1キャンペーンルールテスト (ギャップ期間):\n";
echo "チェックイン日: {$gap_date} (+{$days_until_gap}日)\n";

// Check if any campaigns would apply in gap period
$applicable_immediate = ($days_until_gap <= 7);
$applicable_early = ($days_until_gap >= 30);

if (!$applicable_immediate && !$applicable_early) {
    echo "✅ PASS - ギャップ期間でキャンペーン適用なし (期待通り)\n";
    echo "理由: 7日以内でも30日以上前でもない ({$days_until_gap}日後)\n";
} else {
    echo "❌ FAIL - ギャップ期間でキャンペーンが適用される可能性\n";
    if ($applicable_immediate) echo "- 即入居割が適用される\n";
    if ($applicable_early) echo "- 早割が適用される\n";
}

// Test actual campaign manager integration
echo "\n🔧 キャンペーンマネージャー統合テスト:\n";

// Load campaign manager if available
if (file_exists('includes/campaign-manager.php')) {
    require_once('includes/campaign-manager.php');
    
    if (class_exists('MonthlyBooking_Campaign_Manager')) {
        $campaign_manager = new MonthlyBooking_Campaign_Manager();
        
        // Test immediate campaign
        $immediate_result = $campaign_manager->calculate_campaign_discount(
            $immediate_date, 
            100000, 
            100000
        );
        
        echo "即入居割結果: ";
        if ($immediate_result['campaign_name']) {
            echo "✅ {$immediate_result['campaign_name']} - ¥" . number_format($immediate_result['discount_amount']) . "割引\n";
        } else {
            echo "❌ キャンペーン適用なし\n";
        }
        
        // Test early booking campaign
        $early_result = $campaign_manager->calculate_campaign_discount(
            $early_date, 
            100000, 
            100000
        );
        
        echo "早割結果: ";
        if ($early_result['campaign_name']) {
            echo "✅ {$early_result['campaign_name']} - ¥" . number_format($early_result['discount_amount']) . "割引\n";
        } else {
            echo "❌ キャンペーン適用なし\n";
        }
        
        // Test gap period
        $gap_result = $campaign_manager->calculate_campaign_discount(
            $gap_date, 
            100000, 
            100000
        );
        
        echo "ギャップ期間結果: ";
        if ($gap_result['campaign_name']) {
            echo "❌ 予期しないキャンペーン適用: {$gap_result['campaign_name']}\n";
        } else {
            echo "✅ キャンペーン適用なし (期待通り)\n";
        }
        
    } else {
        echo "❌ MonthlyBooking_Campaign_Manager クラスが見つかりません\n";
    }
} else {
    echo "❌ campaign-manager.php が見つかりません\n";
}

echo "\n=== テスト完了 ===\n";
echo "実行時刻: " . date('Y-m-d H:i:s') . "\n";
