<?php
echo "=== Normalized Options Master Test ===\n\n";

$normalized_options = array(
    array('name' => '調理器具セット', 'price' => 6600, 'is_discount_target' => 1),
    array('name' => '食器類', 'price' => 3900, 'is_discount_target' => 1),
    array('name' => '洗剤類', 'price' => 3800, 'is_discount_target' => 1),
    array('name' => 'タオル類', 'price' => 2900, 'is_discount_target' => 1),
    array('name' => 'アメニティ類', 'price' => 3500, 'is_discount_target' => 1),
    array('name' => '寝具カバーセット', 'price' => 4530, 'is_discount_target' => 1),
    array('name' => '毛布', 'price' => 3950, 'is_discount_target' => 1),
    array('name' => 'アイロン', 'price' => 6860, 'is_discount_target' => 0),
    array('name' => '炊飯器（4合炊き）', 'price' => 6600, 'is_discount_target' => 0)
);

echo "📊 Normalized Options Master Data:\n";
foreach ($normalized_options as $i => $option) {
    $target = $option['is_discount_target'] ? '✅ 対象' : '❌ 対象外';
    echo sprintf("%d. %s - ¥%s - %s\n", 
        $i + 1, 
        $option['name'], 
        number_format($option['price']), 
        $target
    );
}

echo "\n🧮 Discount Logic Test:\n";

function calculate_option_discount($selected_options) {
    $discount_eligible = array_filter($selected_options, function($opt) {
        return $opt['is_discount_target'] == 1;
    });
    
    $count = count($discount_eligible);
    
    if ($count < 2) {
        return 0;
    } elseif ($count == 2) {
        return 500;
    } else {
        return min(500 + ($count - 2) * 300, 2000);
    }
}

$test1 = array($normalized_options[0], $normalized_options[1]); // 調理器具 + 食器類
$discount1 = calculate_option_discount($test1);
echo "Test 1 (2 eligible): " . implode(', ', array_column($test1, 'name')) . " → ¥" . number_format($discount1) . " 割引\n";

$test2 = array($normalized_options[0], $normalized_options[1], $normalized_options[2]); // 調理器具 + 食器類 + 洗剤類
$discount2 = calculate_option_discount($test2);
echo "Test 2 (3 eligible): " . implode(', ', array_column($test2, 'name')) . " → ¥" . number_format($discount2) . " 割引\n";

$test3 = array_slice($normalized_options, 0, 5); // First 5 eligible options
$discount3 = calculate_option_discount($test3);
echo "Test 3 (5 eligible): " . implode(', ', array_column($test3, 'name')) . " → ¥" . number_format($discount3) . " 割引\n";

$test4 = array_slice($normalized_options, 0, 7); // All 7 eligible options
$discount4 = calculate_option_discount($test4);
echo "Test 4 (7 eligible): " . implode(', ', array_column($test4, 'name')) . " → ¥" . number_format($discount4) . " 割引\n";

$test5 = array($normalized_options[0], $normalized_options[1], $normalized_options[7]); // 調理器具 + 食器類 + アイロン(対象外)
$discount5 = calculate_option_discount($test5);
echo "Test 5 (2 eligible + 1 non-eligible): " . implode(', ', array_column($test5, 'name')) . " → ¥" . number_format($discount5) . " 割引\n";

echo "\n✅ Verification:\n";
echo "- Options 1-7: 割引対象 (is_discount_target = 1)\n";
echo "- Options 8-9: 割引対象外 (is_discount_target = 0)\n";
echo "- 2 options: ¥500 discount\n";
echo "- 3+ options: ¥500 + (count-2) × ¥300, max ¥2,000\n";
echo "- Non-eligible options don't count toward discount\n";

echo "\n🎯 Normalized Options Master Implementation Complete!\n";
?>
