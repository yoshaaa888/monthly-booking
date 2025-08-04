<?php
/**
 * WordPress Production Environment Integration Test
 * Comprehensive testing for unified campaign functionality
 */

$wp_paths = [
    '/var/www/html/wp-config.php',
    '/var/www/html/monthly-booking/wp-config.php',
    '../wp-config.php',
    '../../wp-config.php',
    '../../../wp-config.php'
];

$wp_loaded = false;
foreach ($wp_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_loaded = true;
        echo "WordPress environment loaded from: $path\n";
        break;
    }
}

if (!$wp_loaded) {
    echo "WordPress environment not found. Please run this script from WordPress root or provide correct path.\n";
    exit(1);
}

echo "\n=== WordPress Production Campaign Integration Test ===\n";
echo "実行時刻: " . date('Y-m-d H:i:s') . "\n\n";

if (!class_exists('MonthlyBooking_Campaign_Manager')) {
    $plugin_paths = [
        'wp-content/plugins/monthly-booking/includes/campaign-manager.php',
        'includes/campaign-manager.php'
    ];
    
    foreach ($plugin_paths as $path) {
        if (file_exists($path)) {
            require_once($path);
            echo "Campaign manager loaded from: $path\n";
            break;
        }
    }
}

if (!class_exists('MonthlyBooking_Campaign_Manager')) {
    echo "❌ Campaign manager not found. Please ensure plugin is installed.\n";
    exit(1);
}

$campaign_manager = new MonthlyBooking_Campaign_Manager();

global $wpdb;
$table_name = $wpdb->prefix . 'monthly_campaigns';

echo "\n=== Database Schema Check ===\n";
$columns = $wpdb->get_results("DESCRIBE $table_name");
$has_type_column = false;
foreach ($columns as $column) {
    if ($column->Field === 'type') {
        $has_type_column = true;
        break;
    }
}

if ($has_type_column) {
    echo "✅ Type column exists - using type-based matching\n";
    $use_type_based = true;
} else {
    echo "⚠️ Type column missing - using description-based matching\n";
    $use_type_based = false;
}

$campaigns = $wpdb->get_results("SELECT * FROM $table_name WHERE is_active = 1 ORDER BY campaign_name");
echo "\nアクティブキャンペーン (" . count($campaigns) . "件):\n";
foreach ($campaigns as $campaign) {
    $type_info = $has_type_column ? " (type: {$campaign->type})" : "";
    echo "- {$campaign->campaign_name}: {$campaign->discount_value}%{$type_info}\n";
}

echo "\n=== Campaign Application Tests ===\n";

$test_scenarios = [
    [
        'name' => '即入居割テスト',
        'days_offset' => 3,
        'expected_campaign' => '即入居',
        'expected_discount' => 20
    ],
    [
        'name' => '早割テスト', 
        'days_offset' => 35,
        'expected_campaign' => '早割',
        'expected_discount' => 10
    ],
    [
        'name' => 'ギャップ期間テスト',
        'days_offset' => 15,
        'expected_campaign' => null,
        'expected_discount' => 0
    ],
    [
        'name' => '境界値テスト (7日)',
        'days_offset' => 7,
        'expected_campaign' => '即入居',
        'expected_discount' => 20
    ],
    [
        'name' => '境界値テスト (30日)',
        'days_offset' => 31,
        'expected_campaign' => '早割',
        'expected_discount' => 10
    ]
];

$test_results = [];
$base_amount = 100000;

foreach ($test_scenarios as $scenario) {
    echo "\n--- {$scenario['name']} ---\n";
    
    $test_date = date('Y-m-d', strtotime("+{$scenario['days_offset']} days"));
    echo "チェックイン日: {$test_date} (+{$scenario['days_offset']}日)\n";
    
    try {
        $result = $campaign_manager->calculate_campaign_discount($test_date, $base_amount, $base_amount);
        
        $test_passed = true;
        $messages = [];
        
        if ($scenario['expected_campaign']) {
            if ($result['campaign_name']) {
                if (strpos($result['campaign_name'], $scenario['expected_campaign']) !== false) {
                    $messages[] = "✅ 期待されるキャンペーン適用: {$result['campaign_name']}";
                } else {
                    $messages[] = "❌ 予期しないキャンペーン: {$result['campaign_name']} (期待: {$scenario['expected_campaign']})";
                    $test_passed = false;
                }
                
                if ($result['discount_value'] == $scenario['expected_discount']) {
                    $messages[] = "✅ 正しい割引率: {$result['discount_value']}%";
                } else {
                    $messages[] = "❌ 間違った割引率: {$result['discount_value']}% (期待: {$scenario['expected_discount']}%)";
                    $test_passed = false;
                }
                
                $expected_amount = $base_amount * ($scenario['expected_discount'] / 100);
                if (abs($result['discount_amount'] - $expected_amount) < 1) {
                    $messages[] = "✅ 正しい割引金額: ¥" . number_format($result['discount_amount']);
                } else {
                    $messages[] = "❌ 間違った割引金額: ¥" . number_format($result['discount_amount']) . " (期待: ¥" . number_format($expected_amount) . ")";
                    $test_passed = false;
                }
            } else {
                $messages[] = "❌ キャンペーンが適用されませんでした (期待: {$scenario['expected_campaign']})";
                $test_passed = false;
            }
        } else {
            if (!$result['campaign_name']) {
                $messages[] = "✅ キャンペーン適用なし (期待通り)";
            } else {
                $messages[] = "❌ 予期しないキャンペーン適用: {$result['campaign_name']}";
                $test_passed = false;
            }
        }
        
        foreach ($messages as $message) {
            echo $message . "\n";
        }
        
        $test_results[] = [
            'scenario' => $scenario['name'],
            'passed' => $test_passed,
            'result' => $result
        ];
        
    } catch (Exception $e) {
        echo "❌ エラー: " . $e->getMessage() . "\n";
        $test_results[] = [
            'scenario' => $scenario['name'],
            'passed' => false,
            'error' => $e->getMessage()
        ];
    }
}

echo "\n=== テスト結果サマリー ===\n";
$passed_count = 0;
$total_count = count($test_results);

foreach ($test_results as $result) {
    $status = $result['passed'] ? '✅ PASS' : '❌ FAIL';
    echo "{$status}: {$result['scenario']}\n";
    if ($result['passed']) {
        $passed_count++;
    }
}

echo "\n総合結果: {$passed_count}/{$total_count} テスト通過\n";

if ($passed_count === $total_count) {
    echo "🎉 すべてのテストが成功しました！\n";
    echo "統合キャンペーン機能は正常に動作しています。\n";
} else {
    echo "⚠️ 一部のテストが失敗しました。詳細を確認してください。\n";
}

echo "\n=== 次のステップ ===\n";
echo "1. 見積もり画面での動作確認\n";
echo "2. PDF出力でのキャンペーン情報確認\n";
echo "3. 本番環境での最終検証\n";

echo "\n実行完了: " . date('Y-m-d H:i:s') . "\n";
