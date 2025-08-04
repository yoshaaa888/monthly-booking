<?php
echo "=== wp_monthly_options 重複調査シミュレーション ===\n\n";

echo "⚠️ 注意: これは実際のデータベースアクセスなしのシミュレーションです\n";
echo "実際の重複データを確認するには、WordPressデータベースで以下のSQLを実行してください\n\n";

echo "🔍 想定される重複パターンの例:\n\n";

$simulated_duplicates = array(
    array(
        'option_name' => '食器類',
        'records' => array(
            array('id' => 2, 'price' => 3900, 'is_discount_target' => 1, 'display_order' => 2, 'created_at' => '2024-01-15 10:00:00'),
            array('id' => 15, 'price' => 3900, 'is_discount_target' => 1, 'display_order' => 2, 'created_at' => '2024-02-01 14:30:00'),
            array('id' => 23, 'price' => 4000, 'is_discount_target' => 0, 'display_order' => 0, 'created_at' => '2024-02-15 09:15:00')
        )
    ),
    array(
        'option_name' => '洗剤類',
        'records' => array(
            array('id' => 3, 'price' => 3800, 'is_discount_target' => 1, 'display_order' => 3, 'created_at' => '2024-01-15 10:00:00'),
            array('id' => 18, 'price' => 3800, 'is_discount_target' => 1, 'display_order' => 3, 'created_at' => '2024-02-10 16:45:00')
        )
    ),
    array(
        'option_name' => 'アイロン',
        'records' => array(
            array('id' => 8, 'price' => 6860, 'is_discount_target' => 1, 'display_order' => 8, 'created_at' => '2024-01-15 10:00:00'),
            array('id' => 25, 'price' => 6860, 'is_discount_target' => 1, 'display_order' => 8, 'created_at' => '2024-02-20 11:20:00')
        )
    )
);

echo "📊 シミュレーション結果:\n\n";

echo "1. オプション名別重複件数:\n";
echo "| option_name | duplicate_count | all_ids | all_prices | all_targets | 問題 |\n";
echo "|-------------|-----------------|---------|------------|-------------|------|\n";

foreach ($simulated_duplicates as $duplicate) {
    $name = $duplicate['option_name'];
    $count = count($duplicate['records']);
    $ids = implode(',', array_column($duplicate['records'], 'id'));
    $prices = implode(',', array_column($duplicate['records'], 'price'));
    $targets = implode(',', array_column($duplicate['records'], 'is_discount_target'));
    
    $issues = array();
    if ($count > 1) $issues[] = "重複{$count}件";
    
    foreach ($duplicate['records'] as $record) {
        $expected_target = ($record['id'] >= 1 && $record['id'] <= 7) ? 1 : 0;
        if ($record['is_discount_target'] != $expected_target) {
            $issues[] = "ID{$record['id']}割引フラグ不正";
        }
    }
    
    $issue_text = empty($issues) ? "正常" : implode(", ", $issues);
    echo "| {$name} | {$count} | {$ids} | {$prices} | {$targets} | {$issue_text} |\n";
}

echo "\n2. display_order重複確認:\n";
echo "| display_order | count | option_names | ids | 問題 |\n";
echo "|---------------|-------|--------------|-----|------|\n";

$display_orders = array();
foreach ($simulated_duplicates as $duplicate) {
    foreach ($duplicate['records'] as $record) {
        $order = $record['display_order'];
        if (!isset($display_orders[$order])) {
            $display_orders[$order] = array();
        }
        $display_orders[$order][] = array(
            'name' => $duplicate['option_name'],
            'id' => $record['id']
        );
    }
}

foreach ($display_orders as $order => $records) {
    $count = count($records);
    $names = implode(',', array_column($records, 'name'));
    $ids = implode(',', array_column($records, 'id'));
    $issue = $count > 1 ? "重複{$count}件" : "正常";
    echo "| {$order} | {$count} | {$names} | {$ids} | {$issue} |\n";
}

echo "\n3. is_discount_target正確性チェック（IDベース）:\n";
echo "| id | option_name | current_target | expected_target | status | 修正必要 |\n";
echo "|----|-------------|----------------|-----------------|--------|----------|\n";

foreach ($simulated_duplicates as $duplicate) {
    foreach ($duplicate['records'] as $record) {
        $id = $record['id'];
        $name = $duplicate['option_name'];
        $current = $record['is_discount_target'];
        $expected = ($id >= 1 && $id <= 7) ? 1 : 0;
        $status = ($current == $expected) ? '✅ 正確' : '❌ 要修正';
        $fix_needed = ($current == $expected) ? 'なし' : "→ {$expected}";
        echo "| {$id} | {$name} | {$current} | {$expected} | {$status} | {$fix_needed} |\n";
    }
}

echo "\n🚨 発見された問題:\n";
echo "1. 重複レコード: 食器類(3件), 洗剤類(2件), アイロン(2件)\n";
echo "2. display_order重複: 複数のオプションが同じ表示順序を使用\n";
echo "3. is_discount_target不正: ID 8以上で割引対象フラグが1になっている\n\n";

echo "🛠 修正が必要な操作:\n";
echo "1. 重複削除: 最古ID(最小ID)を保持し、他を削除\n";
echo "2. is_discount_target修正: ID 1-7 → 1, ID 8-9 → 0\n";
echo "3. display_order正規化: 1-9の連番で再設定\n\n";

echo "📋 実際のデータ確認用SQL:\n";
echo "-- 以下のSQLをWordPressデータベースで実行してください\n\n";

echo "-- 重複確認\n";
echo "SELECT option_name, COUNT(*) as count, GROUP_CONCAT(id) as ids\n";
echo "FROM wp_monthly_options GROUP BY option_name HAVING COUNT(*) > 1;\n\n";

echo "-- 全データ確認\n";
echo "SELECT id, option_name, price, is_discount_target, display_order, created_at\n";
echo "FROM wp_monthly_options ORDER BY option_name, id;\n\n";

echo "-- is_discount_target正確性確認\n";
echo "SELECT id, option_name, is_discount_target,\n";
echo "  CASE WHEN id BETWEEN 1 AND 7 THEN 1 ELSE 0 END as expected,\n";
echo "  CASE WHEN is_discount_target = CASE WHEN id BETWEEN 1 AND 7 THEN 1 ELSE 0 END\n";
echo "    THEN '✅ 正確' ELSE '❌ 要修正' END as status\n";
echo "FROM wp_monthly_options ORDER BY id;\n\n";

echo "🧪 3件オプション選択時の割引計算テスト（正規化後）:\n";
$test_scenario = array(
    "test_scenario" => "3件オプション選択時の割引計算",
    "selected_options" => array(
        array("id" => 1, "name" => "調理器具セット", "price" => 6600, "is_discount_target" => 1),
        array("id" => 2, "name" => "食器類", "price" => 3900, "is_discount_target" => 1),
        array("id" => 3, "name" => "洗剤類", "price" => 3800, "is_discount_target" => 1)
    ),
    "calculation" => array(
        "base_total" => 14300,
        "eligible_count" => 3,
        "discount_breakdown" => array(
            "2_options_discount" => 500,
            "3rd_option_discount" => 300,
            "total_discount" => 800
        ),
        "final_total" => 13500
    ),
    "id_based_rules" => array(
        "discount_eligible_ids" => "1-7",
        "discount_ineligible_ids" => "8-9",
        "rule" => "ID 1-7 → is_discount_target = 1, ID 8-9 → is_discount_target = 0"
    )
);

echo json_encode($test_scenario, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "⚠️ 次のステップ:\n";
echo "1. 上記SQLをWordPressデータベースで実行\n";
echo "2. 実際の重複データを確認・報告\n";
echo "3. 修正承認後にバックアップ作成\n";
echo "4. 重複削除・正規化実行\n";
echo "5. 修正後の検証\n\n";

echo "✅ シミュレーション完了\n";
echo "実際のデータベースでSQLを実行して結果を確認してください。\n";
?>
