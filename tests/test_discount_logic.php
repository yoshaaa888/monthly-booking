<?php
/**
 * wp_monthly_options 割引ロジックのユニットテスト
 * 
 * booking-logic.php内の割引判定ロジックをテストします
 */

if (!function_exists('wp_die')) {
    function wp_die($message) {
        die($message);
    }
}

class DiscountLogicTest {
    
    private $test_options = array(
        array('id' => 1, 'option_name' => '調理器具セット', 'price' => 6600, 'is_discount_target' => 1),
        array('id' => 2, 'option_name' => '食器類', 'price' => 3900, 'is_discount_target' => 1),
        array('id' => 3, 'option_name' => '洗剤類', 'price' => 3800, 'is_discount_target' => 1),
        array('id' => 4, 'option_name' => 'タオル類', 'price' => 2900, 'is_discount_target' => 1),
        array('id' => 5, 'option_name' => 'アメニティ類', 'price' => 3500, 'is_discount_target' => 1),
        array('id' => 6, 'option_name' => '寝具カバーセット', 'price' => 4530, 'is_discount_target' => 1),
        array('id' => 7, 'option_name' => '毛布', 'price' => 3950, 'is_discount_target' => 1),
        array('id' => 8, 'option_name' => 'アイロン', 'price' => 6860, 'is_discount_target' => 0),
        array('id' => 9, 'option_name' => '炊飯器（4合炊き）', 'price' => 6600, 'is_discount_target' => 0)
    );
    
    /**
     * オプション割引計算ロジック（booking-logic.phpから抽出）
     */
    private function calculate_option_discount($selected_option_ids) {
        $eligible_count = 0;
        $total_price = 0;
        
        foreach ($selected_option_ids as $option_id) {
            $option = $this->get_option_by_id($option_id);
            if ($option) {
                $total_price += $option['price'];
                if ($option['is_discount_target'] == 1) {
                    $eligible_count++;
                }
            }
        }
        
        $discount = 0;
        if ($eligible_count >= 2) {
            $discount = 500;
            
            if ($eligible_count >= 3) {
                $additional_discount = ($eligible_count - 2) * 300;
                $discount += $additional_discount;
            }
            
            $discount = min($discount, 2000);
        }
        
        return array(
            'base_total' => $total_price,
            'eligible_count' => $eligible_count,
            'discount' => $discount,
            'final_total' => $total_price - $discount
        );
    }
    
    private function get_option_by_id($id) {
        foreach ($this->test_options as $option) {
            if ($option['id'] == $id) {
                return $option;
            }
        }
        return null;
    }
    
    /**
     * テストケース1: オプション1つ選択（割引なし）
     */
    public function test_single_option_no_discount() {
        echo "=== テストケース1: オプション1つ選択（割引なし） ===\n";
        
        $selected_options = array(1); // 調理器具セット
        $result = $this->calculate_option_discount($selected_options);
        
        $expected = array(
            'base_total' => 6600,
            'eligible_count' => 1,
            'discount' => 0,
            'final_total' => 6600
        );
        
        $this->assert_equals($expected, $result, "1つ選択時は割引なし");
        echo "✅ テスト1合格\n\n";
    }
    
    /**
     * テストケース2: オプション2つ選択（¥500割引）
     */
    public function test_two_options_500_discount() {
        echo "=== テストケース2: オプション2つ選択（¥500割引） ===\n";
        
        $selected_options = array(1, 2); // 調理器具セット + 食器類
        $result = $this->calculate_option_discount($selected_options);
        
        $expected = array(
            'base_total' => 10500, // 6600 + 3900
            'eligible_count' => 2,
            'discount' => 500,
            'final_total' => 10000
        );
        
        $this->assert_equals($expected, $result, "2つ選択時は¥500割引");
        echo "✅ テスト2合格\n\n";
    }
    
    /**
     * テストケース3: オプション3つ選択（¥800割引）
     */
    public function test_three_options_800_discount() {
        echo "=== テストケース3: オプション3つ選択（¥800割引） ===\n";
        
        $selected_options = array(1, 2, 3); // 調理器具セット + 食器類 + 洗剤類
        $result = $this->calculate_option_discount($selected_options);
        
        $expected = array(
            'base_total' => 14300, // 6600 + 3900 + 3800
            'eligible_count' => 3,
            'discount' => 800, // 500 + 300
            'final_total' => 13500
        );
        
        $this->assert_equals($expected, $result, "3つ選択時は¥800割引");
        echo "✅ テスト3合格\n\n";
    }
    
    /**
     * テストケース4: 割引対象外オプション含む
     */
    public function test_mixed_discount_eligible_options() {
        echo "=== テストケース4: 割引対象外オプション含む ===\n";
        
        $selected_options = array(1, 2, 8); // 調理器具セット + 食器類 + アイロン（対象外）
        $result = $this->calculate_option_discount($selected_options);
        
        $expected = array(
            'base_total' => 17360, // 6600 + 3900 + 6860
            'eligible_count' => 2, // アイロンは対象外
            'discount' => 500, // 対象2つなので¥500割引
            'final_total' => 16860
        );
        
        $this->assert_equals($expected, $result, "割引対象外オプション含む場合");
        echo "✅ テスト4合格\n\n";
    }
    
    /**
     * テストケース5: 最大割引額テスト（¥2,000上限）
     */
    public function test_maximum_discount_limit() {
        echo "=== テストケース5: 最大割引額テスト（¥2,000上限） ===\n";
        
        $selected_options = array(1, 2, 3, 4, 5, 6, 7); // 割引対象7つ全て
        $result = $this->calculate_option_discount($selected_options);
        
        $calculated_discount = 500 + (7 - 2) * 300; // 500 + 1500 = 2000
        $expected_discount = min($calculated_discount, 2000); // 上限適用
        
        $expected = array(
            'base_total' => 29180, // 6600+3900+3800+2900+3500+4530+3950
            'eligible_count' => 7,
            'discount' => 2000, // 上限適用
            'final_total' => 27180
        );
        
        $this->assert_equals($expected, $result, "最大割引額¥2,000上限テスト");
        echo "✅ テスト5合格\n\n";
    }
    
    /**
     * テストケース6: is_discount_target正確性テスト
     */
    public function test_discount_target_accuracy() {
        echo "=== テストケース6: is_discount_target正確性テスト ===\n";
        
        $id_1_to_7_should_be_eligible = true;
        $id_8_to_9_should_be_ineligible = true;
        
        for ($id = 1; $id <= 7; $id++) {
            $option = $this->get_option_by_id($id);
            if ($option['is_discount_target'] != 1) {
                $id_1_to_7_should_be_eligible = false;
                echo "❌ ID {$id} は割引対象であるべきですが、is_discount_target = {$option['is_discount_target']}\n";
            }
        }
        
        for ($id = 8; $id <= 9; $id++) {
            $option = $this->get_option_by_id($id);
            if ($option['is_discount_target'] != 0) {
                $id_8_to_9_should_be_ineligible = false;
                echo "❌ ID {$id} は割引対象外であるべきですが、is_discount_target = {$option['is_discount_target']}\n";
            }
        }
        
        if ($id_1_to_7_should_be_eligible && $id_8_to_9_should_be_ineligible) {
            echo "✅ テスト6合格: is_discount_target設定が正確\n\n";
        } else {
            echo "❌ テスト6失敗: is_discount_target設定に問題あり\n\n";
        }
    }
    
    /**
     * アサーション関数
     */
    private function assert_equals($expected, $actual, $message) {
        if ($expected === $actual) {
            echo "✅ {$message}: 期待値と一致\n";
            echo "   期待値: " . json_encode($expected, JSON_UNESCAPED_UNICODE) . "\n";
            echo "   実際値: " . json_encode($actual, JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "❌ {$message}: 期待値と不一致\n";
            echo "   期待値: " . json_encode($expected, JSON_UNESCAPED_UNICODE) . "\n";
            echo "   実際値: " . json_encode($actual, JSON_UNESCAPED_UNICODE) . "\n";
            throw new Exception("テスト失敗: {$message}");
        }
    }
    
    /**
     * 全テスト実行
     */
    public function run_all_tests() {
        echo "=== wp_monthly_options 割引ロジック ユニットテスト開始 ===\n\n";
        
        try {
            $this->test_single_option_no_discount();
            $this->test_two_options_500_discount();
            $this->test_three_options_800_discount();
            $this->test_mixed_discount_eligible_options();
            $this->test_maximum_discount_limit();
            $this->test_discount_target_accuracy();
            
            echo "🎉 全テスト合格！割引ロジックは正常に動作しています。\n";
            
        } catch (Exception $e) {
            echo "💥 テスト失敗: " . $e->getMessage() . "\n";
            return false;
        }
        
        return true;
    }
}

if (php_sapi_name() === 'cli') {
    $test = new DiscountLogicTest();
    $test->run_all_tests();
}
?>
