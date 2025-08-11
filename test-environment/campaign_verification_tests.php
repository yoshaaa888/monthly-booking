<?php
/**
 * Campaign Verification Tests for Unified Campaign Integration
 * 
 * Tests the three priority scenarios requested:
 * 1. 即入居割 (7日以内) → 20%割引
 * 2. 早割 (30日以上先) → 10%割引  
 * 3. 7～30日の予約 → 最大1ルール確認
 */

require_once(dirname(__DIR__) . '/monthly-booking.php');
require_once(dirname(__DIR__) . '/includes/campaign-manager.php');
require_once(dirname(__DIR__) . '/includes/booking-logic.php');

echo "=== 統合キャンペーン機能 検証テスト ===\n\n";

class CampaignVerificationTests {
    
    private $campaign_manager;
    private $base_amount = 100000; // ¥100,000 base amount for testing
    
    public function __construct() {
        $this->campaign_manager = new MonthlyBooking_Campaign_Manager();
    }
    
    /**
     * テストパターン1: 即入居割（7日以内）→ 20%割引
     */
    public function test_immediate_move_in_discount() {
        echo "🔴 テストパターン1: 即入居割（7日以内）\n";
        echo "期待値: 20%割引が適用される\n\n";
        
        $test_dates = [
            date('Y-m-d', strtotime('+1 day')),   // 明日
            date('Y-m-d', strtotime('+3 days')),  // 3日後
            date('Y-m-d', strtotime('+7 days'))   // 7日後（境界値）
        ];
        
        foreach ($test_dates as $date) {
            $days_until = (new DateTime($date))->diff(new DateTime())->days;
            echo "チェックイン日: {$date} ({$days_until}日後)\n";
            
            $result = $this->campaign_manager->calculate_campaign_discount($date, $this->base_amount, $this->base_amount);
            $campaigns = $this->campaign_manager->get_applicable_campaigns($date);
            
            echo "適用キャンペーン: " . ($result['campaign_name'] ?? 'なし') . "\n";
            echo "割引率: " . ($result['discount_value'] ?? 0) . "%\n";
            echo "割引額: ¥" . number_format($result['discount_amount']) . "\n";
            echo "キャンペーン数: " . (is_array($campaigns) ? count($campaigns) : 0) . "\n";
            
            $expected_discount = 20;
            $actual_discount = $result['discount_value'] ?? 0;
            $test_passed = ($actual_discount == $expected_discount);
            echo "結果: " . ($test_passed ? "✅ PASS" : "❌ FAIL") . "\n\n";
        }
    }
    
    /**
     * テストパターン2: 早割（30日以上先）→ 10%割引
     */
    public function test_early_booking_discount() {
        echo "🟡 テストパターン2: 早割（30日以上先）\n";
        echo "期待値: 10%割引が適用される\n\n";
        
        $test_dates = [
            date('Y-m-d', strtotime('+30 days')),  // 30日後（境界値）
            date('Y-m-d', strtotime('+35 days')),  // 35日後
            date('Y-m-d', strtotime('+60 days'))   // 60日後
        ];
        
        foreach ($test_dates as $date) {
            $days_until = (new DateTime($date))->diff(new DateTime())->days;
            echo "チェックイン日: {$date} ({$days_until}日後)\n";
            
            $result = $this->campaign_manager->calculate_campaign_discount($date, $this->base_amount, $this->base_amount);
            $campaigns = $this->campaign_manager->get_applicable_campaigns($date);
            
            echo "適用キャンペーン: " . ($result['campaign_name'] ?? 'なし') . "\n";
            echo "割引率: " . ($result['discount_value'] ?? 0) . "%\n";
            echo "割引額: ¥" . number_format($result['discount_amount']) . "\n";
            echo "キャンペーン数: " . (is_array($campaigns) ? count($campaigns) : 0) . "\n";
            
            $expected_discount = 10;
            $actual_discount = $result['discount_value'] ?? 0;
            $test_passed = ($actual_discount == $expected_discount);
            echo "結果: " . ($test_passed ? "✅ PASS" : "❌ FAIL") . "\n\n";
        }
    }
    
    /**
     * テストパターン3: 7～30日の予約 → 最大1ルール確認
     */
    public function test_gap_period_max_one_rule() {
        echo "⚪ テストパターン3: 7～30日の予約（最大1ルール確認）\n";
        echo "期待値: キャンペーン適用なし、または最大1つのみ適用\n\n";
        
        $test_dates = [
            date('Y-m-d', strtotime('+8 days')),   // 8日後
            date('Y-m-d', strtotime('+15 days')),  // 15日後
            date('Y-m-d', strtotime('+29 days'))   // 29日後
        ];
        
        foreach ($test_dates as $date) {
            $days_until = (new DateTime($date))->diff(new DateTime())->days;
            echo "チェックイン日: {$date} ({$days_until}日後)\n";
            
            $result = $this->campaign_manager->calculate_campaign_discount($date, $this->base_amount, $this->base_amount);
            $campaigns = $this->campaign_manager->get_applicable_campaigns($date);
            
            echo "適用キャンペーン: " . ($result['campaign_name'] ?? 'なし') . "\n";
            echo "割引率: " . ($result['discount_value'] ?? 0) . "%\n";
            echo "割引額: ¥" . number_format($result['discount_amount']) . "\n";
            echo "キャンペーン数: " . (is_array($campaigns) ? count($campaigns) : 0) . "\n";
            
            $campaign_count = is_array($campaigns) ? count($campaigns) : 0;
            $test_passed = ($campaign_count <= 1);
            echo "最大1ルール: " . ($test_passed ? "✅ PASS" : "❌ FAIL") . "\n\n";
        }
    }
    
    /**
     * データベース状態の確認
     */
    public function verify_database_state() {
        echo "=== データベース状態確認 ===\n";
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'monthly_campaigns';
        
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        echo "wp_monthly_campaigns テーブル: " . ($table_exists ? "✅ 存在" : "❌ 不存在") . "\n";
        
        if ($table_exists) {
            $campaigns = $wpdb->get_results("SELECT campaign_name, discount_value, start_date, end_date, is_active FROM $table_name WHERE is_active = 1");
            echo "アクティブキャンペーン数: " . count($campaigns) . "\n";
            
            foreach ($campaigns as $campaign) {
                echo "- {$campaign->campaign_name}: {$campaign->discount_value}% ({$campaign->start_date} ～ {$campaign->end_date})\n";
            }
        }
        echo "\n";
    }
    
    /**
     * 統合テスト実行
     */
    public function run_all_tests() {
        $this->verify_database_state();
        $this->test_immediate_move_in_discount();
        $this->test_early_booking_discount();
        $this->test_gap_period_max_one_rule();
        
        echo "=== 検証完了 ===\n";
        echo "✅ 即入居割（7日以内）: 20%割引テスト\n";
        echo "✅ 早割（30日以上先）: 10%割引テスト\n";
        echo "✅ 最大1キャンペーンルール: 境界値テスト\n";
        echo "✅ データベース統合: スキーマ確認\n\n";
        
        echo "🎯 次のステップ:\n";
        echo "1. booking-logic.php の apply_campaign_discount() 関数化\n";
        echo "2. campaign-manager.php への将来拡張コメント追加\n";
        echo "3. 実際のWordPress環境での動作確認\n";
    }
}

$tests = new CampaignVerificationTests();
$tests->run_all_tests();
?>
