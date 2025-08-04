<?php
echo "=== オプション重複削除・修正スクリプト ===\n\n";

echo "⚠️ 警告: このスクリプトは調査結果確認・承認後にのみ実行してください\n\n";

echo "-- Step 1: バックアップ作成（必須）\n";
echo "CREATE TABLE wp_monthly_options_backup_" . date('Ymd_His') . " AS SELECT * FROM wp_monthly_options;\n\n";

echo "-- Step 2: 現在の状況確認\n";
echo "SELECT 'Before cleanup' as status, COUNT(*) as total_count FROM wp_monthly_options;\n";
echo "SELECT option_name, COUNT(*) as count FROM wp_monthly_options GROUP BY option_name HAVING COUNT(*) > 1;\n\n";

echo "-- Step 3: 重複削除（最古IDを保持）\n";
echo "DELETE o1 FROM wp_monthly_options o1\n";
echo "INNER JOIN wp_monthly_options o2\n";
echo "WHERE o1.option_name = o2.option_name\n";
echo "AND o1.id > o2.id;\n\n";

echo "-- Step 4: is_discount_target修正（IDベース）\n";
echo "UPDATE wp_monthly_options SET is_discount_target = 1 WHERE id BETWEEN 1 AND 7;\n";
echo "UPDATE wp_monthly_options SET is_discount_target = 0 WHERE id BETWEEN 8 AND 9;\n\n";

echo "-- Step 5: display_order・価格正規化\n";
$normalized_options = array(
    '調理器具セット' => array('order' => 1, 'price' => 6600),
    '食器類' => array('order' => 2, 'price' => 3900),
    '洗剤類' => array('order' => 3, 'price' => 3800),
    'タオル類' => array('order' => 4, 'price' => 2900),
    'アメニティ類' => array('order' => 5, 'price' => 3500),
    '寝具カバーセット' => array('order' => 6, 'price' => 4530),
    '毛布' => array('order' => 7, 'price' => 3950),
    'アイロン' => array('order' => 8, 'price' => 6860),
    '炊飯器（4合炊き）' => array('order' => 9, 'price' => 6600)
);

foreach ($normalized_options as $name => $data) {
    echo "UPDATE wp_monthly_options SET display_order = {$data['order']}, price = {$data['price']}.00 WHERE option_name = '{$name}';\n";
}

echo "\n-- Step 6: 修正後検証\n";
echo "SELECT 'After cleanup' as status, COUNT(*) as total_count FROM wp_monthly_options;\n";
echo "SELECT option_name, COUNT(*) as count FROM wp_monthly_options GROUP BY option_name HAVING COUNT(*) > 1;\n";
echo "SELECT display_order, COUNT(*) as count FROM wp_monthly_options GROUP BY display_order HAVING COUNT(*) > 1;\n\n";

echo "-- Step 7: 最終確認\n";
echo "SELECT \n";
echo "    id,\n";
echo "    option_name,\n";
echo "    price,\n";
echo "    is_discount_target,\n";
echo "    display_order,\n";
echo "    CASE \n";
echo "        WHEN is_discount_target = CASE \n";
echo "            WHEN id BETWEEN 1 AND 7 THEN 1\n";
echo "            WHEN id BETWEEN 8 AND 9 THEN 0\n";
echo "            ELSE -1\n";
echo "        END THEN '✅ 正確'\n";
echo "        ELSE '❌ 不正確'\n";
echo "    END as target_flag_status\n";
echo "FROM wp_monthly_options \n";
echo "ORDER BY display_order;\n\n";

echo "🧪 Step 8: 割引計算テスト\n";
echo "-- 3件オプション選択時のテスト用クエリ\n";
echo "SELECT \n";
echo "    SUM(price) as base_total,\n";
echo "    COUNT(*) as selected_count,\n";
echo "    SUM(CASE WHEN is_discount_target = 1 THEN 1 ELSE 0 END) as eligible_count,\n";
echo "    CASE \n";
echo "        WHEN SUM(CASE WHEN is_discount_target = 1 THEN 1 ELSE 0 END) >= 2 THEN\n";
echo "            500 + GREATEST(0, (SUM(CASE WHEN is_discount_target = 1 THEN 1 ELSE 0 END) - 2) * 300)\n";
echo "        ELSE 0\n";
echo "    END as expected_discount,\n";
echo "    SUM(price) - CASE \n";
echo "        WHEN SUM(CASE WHEN is_discount_target = 1 THEN 1 ELSE 0 END) >= 2 THEN\n";
echo "            500 + GREATEST(0, (SUM(CASE WHEN is_discount_target = 1 THEN 1 ELSE 0 END) - 2) * 300)\n";
echo "        ELSE 0\n";
echo "    END as final_total\n";
echo "FROM wp_monthly_options \n";
echo "WHERE option_name IN ('調理器具セット', '食器類', '洗剤類');\n";
echo "-- 期待値: base_total=14300, eligible_count=3, expected_discount=800, final_total=13500\n\n";

echo "✅ スクリプト生成完了\n";
echo "実行前に必ず:\n";
echo "1. 調査結果の確認\n";
echo "2. 人間の明示的な承認\n";
echo "3. バックアップの作成\n";
echo "4. 本番環境での影響範囲確認\n";
?>
