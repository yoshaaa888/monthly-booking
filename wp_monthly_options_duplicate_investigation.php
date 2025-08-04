<?php
echo "=== wp_monthly_options 重複調査レポート ===\n\n";

echo "🔍 Step 1: 重複調査用SQL（実行してください）\n\n";

echo "-- 1. オプション名別の重複件数と詳細\n";
echo "SELECT \n";
echo "    option_name, \n";
echo "    COUNT(*) as duplicate_count,\n";
echo "    GROUP_CONCAT(id ORDER BY id) as all_ids,\n";
echo "    GROUP_CONCAT(price ORDER BY id) as all_prices,\n";
echo "    GROUP_CONCAT(is_discount_target ORDER BY id) as all_targets,\n";
echo "    GROUP_CONCAT(display_order ORDER BY id) as all_orders,\n";
echo "    MIN(created_at) as first_created,\n";
echo "    MAX(created_at) as last_created\n";
echo "FROM wp_monthly_options \n";
echo "GROUP BY option_name \n";
echo "ORDER BY duplicate_count DESC, option_name;\n\n";

echo "-- 2. display_order重複確認\n";
echo "SELECT \n";
echo "    display_order, \n";
echo "    COUNT(*) as count,\n";
echo "    GROUP_CONCAT(option_name ORDER BY id) as option_names,\n";
echo "    GROUP_CONCAT(id ORDER BY id) as ids\n";
echo "FROM wp_monthly_options \n";
echo "GROUP BY display_order \n";
echo "ORDER BY display_order;\n\n";

echo "-- 3. is_discount_target正確性チェック（IDベース）\n";
echo "SELECT \n";
echo "    id,\n";
echo "    option_name, \n";
echo "    is_discount_target as current_target,\n";
echo "    CASE \n";
echo "        WHEN id BETWEEN 1 AND 7 THEN 1\n";
echo "        WHEN id BETWEEN 8 AND 9 THEN 0\n";
echo "        ELSE -1\n";
echo "    END as expected_target_by_id,\n";
echo "    CASE \n";
echo "        WHEN option_name IN ('調理器具セット', '食器類', '洗剤類', 'タオル類', 'アメニティ類', '寝具カバーセット', '毛布') THEN 1\n";
echo "        WHEN option_name IN ('アイロン', '炊飯器', '炊飯器（4合炊き）') THEN 0\n";
echo "        ELSE -1\n";
echo "    END as expected_target_by_name,\n";
echo "    CASE \n";
echo "        WHEN is_discount_target = CASE \n";
echo "            WHEN id BETWEEN 1 AND 7 THEN 1\n";
echo "            WHEN id BETWEEN 8 AND 9 THEN 0\n";
echo "            ELSE -1\n";
echo "        END THEN '✅ 正確'\n";
echo "        ELSE '❌ 要修正'\n";
echo "    END as id_rule_status,\n";
echo "    display_order,\n";
echo "    price,\n";
echo "    created_at\n";
echo "FROM wp_monthly_options \n";
echo "ORDER BY id;\n\n";

echo "-- 4. 全レコード一覧（削除対象特定用）\n";
echo "SELECT \n";
echo "    id, \n";
echo "    option_name, \n";
echo "    price, \n";
echo "    is_discount_target, \n";
echo "    display_order, \n";
echo "    is_active,\n";
echo "    created_at,\n";
echo "    updated_at\n";
echo "FROM wp_monthly_options \n";
echo "ORDER BY option_name, id;\n\n";

echo "-- 5. 総件数確認\n";
echo "SELECT COUNT(*) as total_options FROM wp_monthly_options;\n";
echo "-- 期待値: 9件（重複なしの場合）\n\n";

echo "🚨 Step 2: 重複原因分析\n\n";

echo "想定される原因:\n";
echo "1. プラグイン有効化時の重複挿入\n";
echo "   - insert_default_options()の既存チェック不備\n";
echo "   - プラグイン再有効化による多重実行\n\n";

echo "2. 管理画面での手動重複作成\n";
echo "   - オプション管理画面での重複入力\n";
echo "   - フォーム送信の二重実行\n\n";

echo "3. データベース直接操作\n";
echo "   - database_setup.sqlの重複実行\n";
echo "   - 手動SQLによる重複挿入\n\n";

echo "4. プログラムバグ\n";
echo "   - AJAX処理の二重送信\n";
echo "   - トランザクション制御不備\n\n";

echo "🛠 Step 3: 修正プレビュー（実行前確認必要）\n\n";

echo "is_discount_target修正ルール:\n";
echo "- ID 1〜7 → 1（割引対象）\n";
echo "- ID 8〜9 → 0（割引対象外）\n\n";

echo "修正SQL（プレビュー）:\n";
echo "-- バックアップ作成\n";
echo "CREATE TABLE wp_monthly_options_backup AS SELECT * FROM wp_monthly_options;\n\n";

echo "-- 重複削除（最古IDを保持）\n";
echo "DELETE o1 FROM wp_monthly_options o1\n";
echo "INNER JOIN wp_monthly_options o2\n";
echo "WHERE o1.option_name = o2.option_name\n";
echo "AND o1.id > o2.id;\n\n";

echo "-- is_discount_target修正（IDベース）\n";
echo "UPDATE wp_monthly_options SET is_discount_target = 1 WHERE id BETWEEN 1 AND 7;\n";
echo "UPDATE wp_monthly_options SET is_discount_target = 0 WHERE id BETWEEN 8 AND 9;\n\n";

echo "-- display_order正規化\n";
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

echo "\n-- 検証クエリ（修正後実行）\n";
echo "SELECT option_name, price, is_discount_target, display_order FROM wp_monthly_options ORDER BY display_order;\n";
echo "SELECT COUNT(*) as total_count FROM wp_monthly_options;\n";
echo "SELECT option_name, COUNT(*) as count FROM wp_monthly_options GROUP BY option_name HAVING COUNT(*) > 1;\n\n";

echo "🧪 Step 4: 3件オプション選択時の割引計算テスト\n\n";

echo "テストシナリオ: 調理器具セット + 食器類 + 洗剤類\n";
echo "期待される計算:\n";
echo "- 基本価格: ¥6,600 + ¥3,900 + ¥3,800 = ¥14,300\n";
echo "- 割引ロジック:\n";
echo "  * 2件選択: ¥500割引\n";
echo "  * 3件目: 追加¥300割引\n";
echo "  * 合計割引: ¥500 + ¥300 = ¥800\n";
echo "- 最終価格: ¥14,300 - ¥800 = ¥13,500\n\n";

$test_json = array(
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
    "discount_rules" => array(
        "rule_1" => "2つのオプション選択で¥500割引",
        "rule_2" => "3つ目以降1つにつき¥300追加割引",
        "rule_3" => "最大割引額¥2,000まで",
        "rule_4" => "割引対象はis_discount_target=1のオプションのみ（ID 1-7）"
    ),
    "id_based_rules" => array(
        "discount_eligible_ids" => "1-7",
        "discount_ineligible_ids" => "8-9",
        "current_rule" => "ID 1-7 → is_discount_target = 1, ID 8-9 → is_discount_target = 0"
    )
);

echo "JSON出力:\n";
echo json_encode($test_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "⚠️ 重要: 修正実行前に以下を確認してください\n";
echo "1. 上記SQLを実行して重複状況を確認\n";
echo "2. 重複レコードの具体的なID一覧を確認\n";
echo "3. 削除対象レコードの確認\n";
echo "4. 人間の明示的な承認を取得\n";
echo "5. バックアップの作成確認\n\n";

echo "📋 次のステップ:\n";
echo "1. 上記SQLクエリを実行して調査結果を報告\n";
echo "2. 重複削除・修正スクリプトの承認を取得\n";
echo "3. バックアップ作成後に修正実行\n";
echo "4. 修正後の検証クエリ実行\n";
echo "5. フロントエンド動作確認\n\n";

echo "✅ 調査完了 - SQLを実行して結果を報告してください\n";
?>
