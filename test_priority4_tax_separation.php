<?php
echo "=== Priority 4: Tax Separation Test ===\n\n";

function test_tax_separation($stay_days, $num_adults, $num_children, $selected_options) {
    $daily_rent = 3000; // Example room rate
    $daily_utilities = 2500; // SS plan utilities
    $total_rent = $daily_rent * $stay_days;
    $total_utilities = $daily_utilities * $stay_days;
    
    $additional_adults = max(0, ($num_adults - 1));
    $additional_children = $num_children;
    
    $adult_additional_rent = $additional_adults * 900 * $stay_days;
    $adult_additional_utilities = $additional_adults * 200 * $stay_days;
    $children_additional_rent = $additional_children * 450 * $stay_days;
    $children_additional_utilities = $additional_children * 100 * $stay_days;
    
    $non_taxable_subtotal = $total_rent + $total_utilities + 
                           $adult_additional_rent + $adult_additional_utilities +
                           $children_additional_rent + $children_additional_utilities;
    
    $cleaning_fee = 38500;
    $key_fee = 11000;
    $adult_bedding_fee = $additional_adults * 11000;
    $children_bedding_fee = $additional_children * 11000;
    $bedding_fee = $adult_bedding_fee + $children_bedding_fee;
    
    $options_total = 0;
    foreach ($selected_options as $option_id => $option_data) {
        $options_total += $option_data['price'] * $option_data['quantity'];
    }
    
    $discount_eligible_count = 0;
    foreach ($selected_options as $option_id => $option_data) {
        if ($option_data['is_discount_target'] == 1) {
            $discount_eligible_count += $option_data['quantity'];
        }
    }
    
    $options_discount = 0;
    if ($discount_eligible_count >= 2) {
        $options_discount = 500;
        if ($discount_eligible_count >= 3) {
            $options_discount += ($discount_eligible_count - 2) * 300;
        }
        $options_discount = min($options_discount, 2000);
    }
    
    $taxable_subtotal = $cleaning_fee + $key_fee + $bedding_fee + $options_total - $options_discount;
    
    $tax_rate = 0.10;
    $tax_exclusive_amount = $taxable_subtotal / (1 + $tax_rate);
    $consumption_tax = $taxable_subtotal - $tax_exclusive_amount;
    
    $total_amount = $non_taxable_subtotal + $taxable_subtotal;
    
    return [
        'non_taxable_subtotal' => $non_taxable_subtotal,
        'taxable_subtotal' => $taxable_subtotal,
        'tax_exclusive_amount' => $tax_exclusive_amount,
        'consumption_tax' => $consumption_tax,
        'total_amount' => $total_amount,
        'breakdown' => [
            'daily_rent' => $total_rent,
            'daily_utilities' => $total_utilities,
            'adult_additional_rent' => $adult_additional_rent,
            'adult_additional_utilities' => $adult_additional_utilities,
            'children_additional_rent' => $children_additional_rent,
            'children_additional_utilities' => $children_additional_utilities,
            'cleaning_fee' => $cleaning_fee,
            'key_fee' => $key_fee,
            'bedding_fee' => $bedding_fee,
            'options_total' => $options_total,
            'options_discount' => $options_discount
        ]
    ];
}

echo "✅ Test Case 1: 基本料金のみ（大人1名、10日滞在）\n";
$test1 = test_tax_separation(10, 1, 0, []);
echo "非課税小計: ¥" . number_format($test1['non_taxable_subtotal']) . "\n";
echo "  - 日額賃料: ¥" . number_format($test1['breakdown']['daily_rent']) . "\n";
echo "  - 共益費: ¥" . number_format($test1['breakdown']['daily_utilities']) . "\n";
echo "課税小計（税込）: ¥" . number_format($test1['taxable_subtotal']) . "\n";
echo "  - 清掃費: ¥" . number_format($test1['breakdown']['cleaning_fee']) . "\n";
echo "  - 鍵交換代: ¥" . number_format($test1['breakdown']['key_fee']) . "\n";
echo "  - 布団代: ¥" . number_format($test1['breakdown']['bedding_fee']) . "\n";
echo "税抜金額: ¥" . number_format($test1['tax_exclusive_amount']) . "\n";
echo "消費税: ¥" . number_format($test1['consumption_tax']) . "\n";
echo "合計金額: ¥" . number_format($test1['total_amount']) . "\n\n";

echo "✅ Test Case 2: 人数追加あり（大人2名、子ども1名、10日滞在）\n";
$test2 = test_tax_separation(10, 2, 1, []);
echo "非課税小計: ¥" . number_format($test2['non_taxable_subtotal']) . "\n";
echo "  - 基本賃料: ¥" . number_format($test2['breakdown']['daily_rent']) . "\n";
echo "  - 基本共益費: ¥" . number_format($test2['breakdown']['daily_utilities']) . "\n";
echo "  - 大人追加賃料: ¥" . number_format($test2['breakdown']['adult_additional_rent']) . "\n";
echo "  - 大人追加共益費: ¥" . number_format($test2['breakdown']['adult_additional_utilities']) . "\n";
echo "  - 子ども追加賃料: ¥" . number_format($test2['breakdown']['children_additional_rent']) . "\n";
echo "  - 子ども追加共益費: ¥" . number_format($test2['breakdown']['children_additional_utilities']) . "\n";
echo "課税小計（税込）: ¥" . number_format($test2['taxable_subtotal']) . "\n";
echo "  - 清掃費: ¥" . number_format($test2['breakdown']['cleaning_fee']) . "\n";
echo "  - 鍵交換代: ¥" . number_format($test2['breakdown']['key_fee']) . "\n";
echo "  - 布団代: ¥" . number_format($test2['breakdown']['bedding_fee']) . " (大人1名+子ども1名)\n";
echo "税抜金額: ¥" . number_format($test2['tax_exclusive_amount']) . "\n";
echo "消費税: ¥" . number_format($test2['consumption_tax']) . "\n";
echo "合計金額: ¥" . number_format($test2['total_amount']) . "\n\n";

echo "✅ Test Case 3: オプション選択あり（割引適用）\n";
$test3_options = [
    1 => ['price' => 3000, 'quantity' => 1, 'is_discount_target' => 1, 'name' => '調理器具セット'],
    2 => ['price' => 2000, 'quantity' => 1, 'is_discount_target' => 1, 'name' => '食器セット'],
    8 => ['price' => 5000, 'quantity' => 1, 'is_discount_target' => 0, 'name' => 'Wi-Fi']
];
$test3 = test_tax_separation(10, 1, 0, $test3_options);
echo "非課税小計: ¥" . number_format($test3['non_taxable_subtotal']) . "\n";
echo "課税小計（税込）: ¥" . number_format($test3['taxable_subtotal']) . "\n";
echo "  - 清掃費: ¥" . number_format($test3['breakdown']['cleaning_fee']) . "\n";
echo "  - 鍵交換代: ¥" . number_format($test3['breakdown']['key_fee']) . "\n";
echo "  - オプション合計: ¥" . number_format($test3['breakdown']['options_total']) . "\n";
echo "  - オプション割引: -¥" . number_format($test3['breakdown']['options_discount']) . " (2個選択)\n";
echo "税抜金額: ¥" . number_format($test3['tax_exclusive_amount']) . "\n";
echo "消費税: ¥" . number_format($test3['consumption_tax']) . "\n";
echo "合計金額: ¥" . number_format($test3['total_amount']) . "\n\n";

echo "=== Priority 4 Implementation Requirements ===\n";
echo "✅ 非課税項目: 日額賃料、共益費（基本・追加）\n";
echo "✅ 課税項目: 清掃費、布団代、鍵交換代、オプション類\n";
echo "✅ 割引対象: 課税対象額に対して適用\n";
echo "✅ 税計算: 税込価格から税抜金額と消費税を分離\n";
echo "✅ 表示項目: 非課税小計、課税小計、税抜金額、消費税、合計金額\n";
?>
