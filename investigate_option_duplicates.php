<?php
echo "=== オプション重複調査スクリプト ===\n\n";


echo "🔍 Step 1: テーブル構造確認\n";
echo "テーブル名: wp_monthly_options\n";
echo "主要カラム:\n";
echo "- id (PRIMARY KEY)\n";
echo "- option_name (VARCHAR)\n";
echo "- option_description (TEXT)\n";
echo "- price (DECIMAL)\n";
echo "- is_discount_target (TINYINT)\n";
echo "- display_order (INT)\n";
echo "- is_active (TINYINT)\n\n";

echo "📊 Step 2: 重複調査用SQL\n";
echo "以下のSQLを実行して重複を確認してください:\n\n";

echo "-- オプション名別の件数集計\n";
echo "SELECT option_name, COUNT(*) as count\n";
echo "FROM wp_monthly_options\n";
echo "GROUP BY option_name\n";
echo "HAVING COUNT(*) > 1\n";
echo "ORDER BY count DESC;\n\n";

echo "-- display_order重複確認\n";
echo "SELECT display_order, COUNT(*) as count\n";
echo "FROM wp_monthly_options\n";
echo "GROUP BY display_order\n";
echo "HAVING COUNT(*) > 1\n";
echo "ORDER BY display_order;\n\n";

echo "-- 全オプション一覧（作成日時順）\n";
echo "SELECT id, option_name, price, is_discount_target, display_order, created_at\n";
echo "FROM wp_monthly_options\n";
echo "ORDER BY option_name, created_at;\n\n";

echo "🚨 Step 3: 想定される重複原因\n";
echo "1. プラグイン有効化時の重複挿入:\n";
echo "   - insert_default_options()が既存チェック不備で重複作成\n";
echo "   - プラグイン再有効化時の多重実行\n\n";

echo "2. 管理画面での手動重複登録:\n";
echo "   - オプション管理画面での重複作成\n";
echo "   - フォーム送信の二重実行\n\n";

echo "3. テストデータの残存:\n";
echo "   - 開発時のテストデータが残存\n";
echo "   - database_setup.sqlの重複実行\n\n";

echo "4. プログラム上のバグ:\n";
echo "   - AJAX処理の二重送信\n";
echo "   - トランザクション制御不備\n\n";

echo "🛠 Step 4: 修正方針（実行前確認必要）\n";
echo "重複削除の基本方針:\n";
echo "1. option_name別に最古のレコード（最小ID）を保持\n";
echo "2. 重複レコードを削除\n";
echo "3. display_orderを1-9で再設定\n";
echo "4. is_discount_targetを正規化（1-7:対象、8-9:対象外）\n\n";

echo "正規化後の期待値:\n";
$expected_options = array(
    array('name' => '調理器具セット', 'price' => 6600, 'target' => 1, 'order' => 1),
    array('name' => '食器類', 'price' => 3900, 'target' => 1, 'order' => 2),
    array('name' => '洗剤類', 'price' => 3800, 'target' => 1, 'order' => 3),
    array('name' => 'タオル類', 'price' => 2900, 'target' => 1, 'order' => 4),
    array('name' => 'アメニティ類', 'price' => 3500, 'target' => 1, 'order' => 5),
    array('name' => '寝具カバーセット', 'price' => 4530, 'target' => 1, 'order' => 6),
    array('name' => '毛布', 'price' => 3950, 'target' => 1, 'order' => 7),
    array('name' => 'アイロン', 'price' => 6860, 'target' => 0, 'order' => 8),
    array('name' => '炊飯器（4合炊き）', 'price' => 6600, 'target' => 0, 'order' => 9)
);

foreach ($expected_options as $i => $option) {
    $target_text = $option['target'] ? '対象' : '対象外';
    echo sprintf("%d. %s - ¥%s - %s\n", 
        $option['order'], 
        $option['name'], 
        number_format($option['price']), 
        $target_text
    );
}

echo "\n⚠️ 重要: 修正実行前に以下を確認してください\n";
echo "1. 現在のオプションテーブルの完全なバックアップ\n";
echo "2. 重複レコードの具体的なID一覧\n";
echo "3. 削除対象レコードの確認\n";
echo "4. 本番環境での影響範囲の確認\n\n";

echo "🔧 次のステップ:\n";
echo "1. 上記SQLを実行して重複状況を報告\n";
echo "2. 重複削除スクリプトの生成（確認後）\n";
echo "3. テスト環境での動作確認\n";
echo "4. 本番環境への適用\n\n";

echo "✅ 調査完了 - SQLを実行して結果を報告してください\n";
?>
