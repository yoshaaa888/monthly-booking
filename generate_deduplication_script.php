<?php
echo "=== オプション重複削除スクリプト生成 ===\n\n";

echo "⚠️ 注意: このスクリプトは調査結果確認後に実行してください\n\n";

echo "-- Step 1: 現在のデータをバックアップ\n";
echo "CREATE TABLE wp_monthly_options_backup AS SELECT * FROM wp_monthly_options;\n\n";

echo "-- Step 2: 重複削除（最古IDを保持）\n";
echo "DELETE o1 FROM wp_monthly_options o1\n";
echo "INNER JOIN wp_monthly_options o2\n";
echo "WHERE o1.option_name = o2.option_name\n";
echo "AND o1.id > o2.id;\n\n";

echo "-- Step 3: display_order正規化\n";
$normalized_options = array(
    '調理器具セット' => array('order' => 1, 'price' => 6600, 'target' => 1),
    '食器類' => array('order' => 2, 'price' => 3900, 'target' => 1),
    '洗剤類' => array('order' => 3, 'price' => 3800, 'target' => 1),
    'タオル類' => array('order' => 4, 'price' => 2900, 'target' => 1),
    'アメニティ類' => array('order' => 5, 'price' => 3500, 'target' => 1),
    '寝具カバーセット' => array('order' => 6, 'price' => 4530, 'target' => 1),
    '毛布' => array('order' => 7, 'price' => 3950, 'target' => 1),
    'アイロン' => array('order' => 8, 'price' => 6860, 'target' => 0),
    '炊飯器（4合炊き）' => array('order' => 9, 'price' => 6600, 'target' => 0)
);

foreach ($normalized_options as $name => $data) {
    echo "UPDATE wp_monthly_options SET\n";
    echo "  display_order = {$data['order']},\n";
    echo "  price = {$data['price']}.00,\n";
    echo "  is_discount_target = {$data['target']}\n";
    echo "WHERE option_name = '{$name}';\n\n";
}

echo "-- Step 4: 検証クエリ\n";
echo "SELECT option_name, price, is_discount_target, display_order\n";
echo "FROM wp_monthly_options\n";
echo "ORDER BY display_order;\n\n";

echo "-- Step 5: 件数確認\n";
echo "SELECT COUNT(*) as total_options FROM wp_monthly_options;\n";
echo "-- 期待値: 9件\n\n";

echo "-- Step 6: 重複確認\n";
echo "SELECT option_name, COUNT(*) as count\n";
echo "FROM wp_monthly_options\n";
echo "GROUP BY option_name\n";
echo "HAVING COUNT(*) > 1;\n";
echo "-- 期待値: 0件（重複なし）\n\n";

echo "🧪 テスト用見積計算\n";
echo "以下のオプション選択でテスト:\n";
echo "- 調理器具セット (¥6,600) + 食器類 (¥3,900) = 2オプション → ¥500割引\n";
echo "- 期待値: ¥10,500 - ¥500 = ¥10,000\n\n";

echo "✅ スクリプト生成完了\n";
echo "実行前に必ず調査結果を確認し、バックアップを取得してください。\n";
?>
