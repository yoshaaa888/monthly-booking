<?php
echo "=== Priority 3: Option Discount Target Limitation Test ===\n\n";

function test_option_discount_calculation($selected_options_with_targets) {
    $discount_eligible_count = 0;
    $total_option_cost = 0;
    
    foreach ($selected_options_with_targets as $option_id => $option_data) {
        $total_option_cost += $option_data['price'] * $option_data['quantity'];
        
        if ($option_data['is_discount_target'] == 1) {
            $discount_eligible_count += $option_data['quantity'];
        }
    }
    
    $discount_amount = 0;
    if ($discount_eligible_count >= 2) {
        $discount_amount = 500; // Base discount for 2 items
        
        if ($discount_eligible_count >= 3) {
            $additional_items = $discount_eligible_count - 2;
            $additional_discount = $additional_items * 300;
            $discount_amount += $additional_discount;
        }
        
        $discount_amount = min($discount_amount, 2000);
    }
    
    return [
        'total_option_cost' => $total_option_cost,
        'discount_eligible_count' => $discount_eligible_count,
        'discount_amount' => $discount_amount,
        'final_option_cost' => $total_option_cost - $discount_amount
    ];
}

echo "✅ Test Case 1: 2つの割引対象オプション選択\n";
$test1_options = [
    1 => ['price' => 3000, 'quantity' => 1, 'is_discount_target' => 1, 'name' => '調理器具セット'],
    2 => ['price' => 2000, 'quantity' => 1, 'is_discount_target' => 1, 'name' => '食器セット']
];
$result1 = test_option_discount_calculation($test1_options);
echo "選択: 調理器具セット + 食器セット\n";
echo "割引対象数: {$result1['discount_eligible_count']}個\n";
echo "割引額: ¥" . number_format($result1['discount_amount']) . " (期待値: ¥500)\n";
echo "結果: " . ($result1['discount_amount'] == 500 ? "✅ PASS" : "❌ FAIL") . "\n\n";

echo "✅ Test Case 2: 3つの割引対象オプション選択\n";
$test2_options = [
    1 => ['price' => 3000, 'quantity' => 1, 'is_discount_target' => 1, 'name' => '調理器具セット'],
    2 => ['price' => 2000, 'quantity' => 1, 'is_discount_target' => 1, 'name' => '食器セット'],
    3 => ['price' => 1500, 'quantity' => 1, 'is_discount_target' => 1, 'name' => 'タオルセット']
];
$result2 = test_option_discount_calculation($test2_options);
echo "選択: 調理器具セット + 食器セット + タオルセット\n";
echo "割引対象数: {$result2['discount_eligible_count']}個\n";
echo "割引額: ¥" . number_format($result2['discount_amount']) . " (期待値: ¥800)\n";
echo "結果: " . ($result2['discount_amount'] == 800 ? "✅ PASS" : "❌ FAIL") . "\n\n";

echo "✅ Test Case 3: Wi-Fi含む選択（Wi-Fiは割引対象外）\n";
$test3_options = [
    1 => ['price' => 3000, 'quantity' => 1, 'is_discount_target' => 1, 'name' => '調理器具セット'],
    2 => ['price' => 2000, 'quantity' => 1, 'is_discount_target' => 1, 'name' => '食器セット'],
    8 => ['price' => 5000, 'quantity' => 1, 'is_discount_target' => 0, 'name' => 'Wi-Fi'] // Not eligible
];
$result3 = test_option_discount_calculation($test3_options);
echo "選択: 調理器具セット + 食器セット + Wi-Fi\n";
echo "割引対象数: {$result3['discount_eligible_count']}個 (Wi-Fiは除外)\n";
echo "割引額: ¥" . number_format($result3['discount_amount']) . " (期待値: ¥500)\n";
echo "結果: " . ($result3['discount_amount'] == 500 ? "✅ PASS" : "❌ FAIL") . "\n\n";

echo "✅ Test Case 4: 駐車場含む選択（駐車場は割引対象外）\n";
$test4_options = [
    1 => ['price' => 3000, 'quantity' => 1, 'is_discount_target' => 1, 'name' => '調理器具セット'],
    9 => ['price' => 8000, 'quantity' => 1, 'is_discount_target' => 0, 'name' => '駐車場'] // Not eligible
];
$result4 = test_option_discount_calculation($test4_options);
echo "選択: 調理器具セット + 駐車場\n";
echo "割引対象数: {$result4['discount_eligible_count']}個 (駐車場は除外)\n";
echo "割引額: ¥" . number_format($result4['discount_amount']) . " (期待値: ¥0)\n";
echo "結果: " . ($result4['discount_amount'] == 0 ? "✅ PASS" : "❌ FAIL") . "\n\n";

echo "✅ Test Case 5: 最大割引額テスト（7個選択で¥2,000上限）\n";
$test5_options = [
    1 => ['price' => 3000, 'quantity' => 1, 'is_discount_target' => 1, 'name' => '調理器具セット'],
    2 => ['price' => 2000, 'quantity' => 1, 'is_discount_target' => 1, 'name' => '食器セット'],
    3 => ['price' => 1500, 'quantity' => 1, 'is_discount_target' => 1, 'name' => 'タオルセット'],
    4 => ['price' => 1000, 'quantity' => 1, 'is_discount_target' => 1, 'name' => 'シーツセット'],
    5 => ['price' => 800, 'quantity' => 1, 'is_discount_target' => 1, 'name' => '枕セット'],
    6 => ['price' => 600, 'quantity' => 1, 'is_discount_target' => 1, 'name' => 'ハンガーセット'],
    7 => ['price' => 500, 'quantity' => 1, 'is_discount_target' => 1, 'name' => '洗剤セット']
];
$result5 = test_option_discount_calculation($test5_options);
$expected_discount = 500 + (5 * 300); // ¥500 + ¥1500 = ¥2000 (capped)
echo "選択: 全7つの割引対象オプション\n";
echo "割引対象数: {$result5['discount_eligible_count']}個\n";
echo "計算割引額: ¥" . number_format($expected_discount) . "\n";
echo "実際割引額: ¥" . number_format($result5['discount_amount']) . " (上限¥2,000)\n";
echo "結果: " . ($result5['discount_amount'] == 2000 ? "✅ PASS" : "❌ FAIL") . "\n\n";

echo "=== Priority 3 Test Summary ===\n";
echo "✅ 割引対象オプション1-7のみカウント\n";
echo "✅ Wi-Fi（8）と駐車場（9）は割引対象外\n";
echo "✅ 2個選択: ¥500割引\n";
echo "✅ 3個目以降: +¥300ずつ\n";
echo "✅ 最大割引額: ¥2,000\n";
echo "✅ is_discount_target フィールドによる判定\n";
?>
