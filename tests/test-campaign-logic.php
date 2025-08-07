<?php
/**
 * PHPUnit Tests for Campaign Auto-Application Logic
 * 
 * Tests the get_best_applicable_campaign_for_room() function and integrated
 * booking logic to ensure correct campaign selection and application.
 * 
 * @package Monthly_Booking
 * @subpackage Tests
 */

class Campaign_Logic_Tests extends WP_UnitTestCase {

    /**
     * Campaign Manager instance
     * @var MonthlyBooking_Campaign_Manager
     */
    private $campaign_manager;

    /**
     * Test campaign IDs for cleanup
     * @var array
     */
    private $test_campaign_ids = array();

    /**
     * Test room campaign assignment IDs for cleanup
     * @var array
     */
    private $test_assignment_ids = array();

    /**
     * Set up test environment before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        if (!class_exists('MonthlyBooking_Campaign_Manager')) {
            require_once plugin_dir_path(__FILE__) . '../includes/campaign-manager.php';
        }
        
        $this->campaign_manager = new MonthlyBooking_Campaign_Manager();
        
        $this->create_test_tables();
    }

    /**
     * Clean up test data after each test
     */
    public function tearDown(): void {
        global $wpdb;
        
        if (!empty($this->test_assignment_ids)) {
            $assignment_ids = implode(',', array_map('intval', $this->test_assignment_ids));
            $wpdb->query("DELETE FROM {$wpdb->prefix}monthly_room_campaigns WHERE id IN ($assignment_ids)");
        }
        
        if (!empty($this->test_campaign_ids)) {
            $campaign_ids = implode(',', array_map('intval', $this->test_campaign_ids));
            $wpdb->query("DELETE FROM {$wpdb->prefix}monthly_campaigns WHERE id IN ($campaign_ids)");
        }
        
        $this->test_campaign_ids = array();
        $this->test_assignment_ids = array();
        
        parent::tearDown();
    }

    /**
     * Create necessary test tables
     */
    private function create_test_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $campaigns_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}monthly_campaigns (
            id int(11) NOT NULL AUTO_INCREMENT,
            campaign_name varchar(255) NOT NULL,
            campaign_description text,
            discount_type enum('percentage','fixed','flatrate') NOT NULL,
            discount_value decimal(10,2) NOT NULL,
            type varchar(50) DEFAULT 'general',
            is_active tinyint(1) DEFAULT 1,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        $room_campaigns_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}monthly_room_campaigns (
            id int(11) NOT NULL AUTO_INCREMENT,
            room_id int(11) NOT NULL,
            campaign_id int(11) NOT NULL,
            start_date date NOT NULL,
            end_date date NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_room_period (room_id, start_date, end_date),
            KEY idx_room_dates (room_id, start_date, end_date),
            KEY idx_campaign_active (campaign_id, is_active)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($campaigns_table);
        dbDelta($room_campaigns_table);
    }

    /**
     * Helper method to create test campaign
     */
    private function create_test_campaign($name, $description, $discount_type, $discount_value, $type = 'general') {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'monthly_campaigns',
            array(
                'campaign_name' => $name,
                'campaign_description' => $description,
                'discount_type' => $discount_type,
                'discount_value' => $discount_value,
                'type' => $type,
                'is_active' => 1
            ),
            array('%s', '%s', '%s', '%f', '%s', '%d')
        );
        
        $campaign_id = $wpdb->insert_id;
        $this->test_campaign_ids[] = $campaign_id;
        
        return $campaign_id;
    }

    /**
     * Helper method to create room campaign assignment
     */
    private function create_room_campaign_assignment($room_id, $campaign_id, $start_date, $end_date, $is_active = 1) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'monthly_room_campaigns',
            array(
                'room_id' => $room_id,
                'campaign_id' => $campaign_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'is_active' => $is_active
            ),
            array('%d', '%d', '%s', '%s', '%d')
        );
        
        $assignment_id = $wpdb->insert_id;
        $this->test_assignment_ids[] = $assignment_id;
        
        return $assignment_id;
    }

    /**
     * TC-01: 「即入居割」が条件（チェックイン7日以内）を満たした時に正しく適用される
     */
    public function test_tc01_immediate_discount_applies_correctly() {
        $campaign_id = $this->create_test_campaign(
            '即入居割',
            '7日以内の入居で20%OFF',
            'percentage',
            20.00,
            'immediate'
        );
        
        $this->create_room_campaign_assignment(
            1, // room_id
            $campaign_id,
            date('Y-m-d', strtotime('-10 days')), // start_date
            date('Y-m-d', strtotime('+30 days'))  // end_date
        );
        
        $checkin_date = date('Y-m-d', strtotime('+5 days'));
        $checkout_date = date('Y-m-d', strtotime('+15 days'));
        $base_price = 100000;
        
        $result = $this->campaign_manager->get_best_applicable_campaign_for_room(
            1, $checkin_date, $checkout_date, $base_price
        );
        
        $this->assertNotNull($result, 'Campaign should be returned');
        $this->assertCount(1, $result, 'Exactly one campaign should be returned');
        
        $campaign = $result[0];
        $this->assertEquals('即入居割', $campaign['name']);
        $this->assertEquals('immediate', $campaign['type']);
        $this->assertEquals(20000, $campaign['discount_amount']); // 20% of 100000
        $this->assertEquals(5, $campaign['days_until_checkin']);
    }

    /**
     * TC-02: 「早割」が条件（チェックイン30日以上前）を満たした時に正しく適用される
     */
    public function test_tc02_earlybird_discount_applies_correctly() {
        $campaign_id = $this->create_test_campaign(
            '早割',
            '30日前予約で10%OFF',
            'percentage',
            10.00,
            'earlybird'
        );
        
        $this->create_room_campaign_assignment(
            2, $campaign_id,
            date('Y-m-d', strtotime('-10 days')),
            date('Y-m-d', strtotime('+60 days'))
        );
        
        $checkin_date = date('Y-m-d', strtotime('+35 days'));
        $checkout_date = date('Y-m-d', strtotime('+65 days'));
        $base_price = 150000;
        
        $result = $this->campaign_manager->get_best_applicable_campaign_for_room(
            2, $checkin_date, $checkout_date, $base_price
        );
        
        $this->assertNotNull($result);
        $this->assertCount(1, $result);
        
        $campaign = $result[0];
        $this->assertEquals('早割', $campaign['name']);
        $this->assertEquals('earlybird', $campaign['type']);
        $this->assertEquals(15000, $campaign['discount_amount']); // 10% of 150000
        $this->assertEquals(35, $campaign['days_until_checkin']);
    }

    /**
     * TC-03: 「コミコミプラン」が条件（7〜10日滞在）を満たした時に正しく適用される
     */
    public function test_tc03_flatrate_plan_applies_correctly() {
        $campaign_id = $this->create_test_campaign(
            'コミコミプラン',
            '7-10日滞在で10万円固定',
            'flatrate',
            100000.00,
            'flatrate'
        );
        
        $this->create_room_campaign_assignment(
            3, $campaign_id,
            date('Y-m-d', strtotime('-10 days')),
            date('Y-m-d', strtotime('+30 days'))
        );
        
        $checkin_date = date('Y-m-d', strtotime('+15 days'));
        $checkout_date = date('Y-m-d', strtotime('+23 days')); // 8 days stay
        $base_price = 120000;
        
        $result = $this->campaign_manager->get_best_applicable_campaign_for_room(
            3, $checkin_date, $checkout_date, $base_price
        );
        
        $this->assertNotNull($result);
        $this->assertCount(1, $result);
        
        $campaign = $result[0];
        $this->assertEquals('コミコミプラン', $campaign['name']);
        $this->assertEquals('flatrate', $campaign['type']);
        $this->assertEquals(20000, $campaign['discount_amount']); // 120000 - 100000
        $this->assertEquals(999999, $campaign['priority']); // Flatrate has highest priority
    }

    /**
     * TC-04: 「即入居割」と「コミコミプラン」の両方の条件を満たす場合、「コミコミプラン」が最優先で適用される
     */
    public function test_tc04_flatrate_takes_priority_over_immediate() {
        $immediate_id = $this->create_test_campaign(
            '即入居割',
            '7日以内の入居で20%OFF',
            'percentage',
            20.00,
            'immediate'
        );
        
        $flatrate_id = $this->create_test_campaign(
            'コミコミプラン',
            '7-10日滞在で10万円固定',
            'flatrate',
            100000.00,
            'flatrate'
        );
        
        $this->create_room_campaign_assignment(4, $immediate_id, date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+30 days')));
        $this->create_room_campaign_assignment(4, $flatrate_id, date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+30 days')));
        
        $checkin_date = date('Y-m-d', strtotime('+5 days'));
        $checkout_date = date('Y-m-d', strtotime('+13 days')); // 8 days stay
        $base_price = 120000;
        
        $result = $this->campaign_manager->get_best_applicable_campaign_for_room(
            4, $checkin_date, $checkout_date, $base_price
        );
        
        $this->assertNotNull($result);
        $this->assertCount(1, $result, 'Only one campaign should be returned');
        
        $campaign = $result[0];
        $this->assertEquals('コミコミプラン', $campaign['name']);
        $this->assertEquals('flatrate', $campaign['type']);
    }

    /**
     * TC-05: 割引率が異なる2つのキャンペーン（例: 15%OFFと20%OFF）が適用可能な場合、割引額が最も高い方（20%OFF）が自動選択される
     */
    public function test_tc05_higher_discount_amount_selected() {
        $campaign_15_id = $this->create_test_campaign(
            'キャンペーンA',
            '15%割引',
            'percentage',
            15.00,
            'general'
        );
        
        $campaign_20_id = $this->create_test_campaign(
            'キャンペーンB',
            '20%割引',
            'percentage',
            20.00,
            'general'
        );
        
        $this->create_room_campaign_assignment(5, $campaign_15_id, date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+30 days')));
        $this->create_room_campaign_assignment(5, $campaign_20_id, date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+30 days')));
        
        $checkin_date = date('Y-m-d', strtotime('+15 days'));
        $checkout_date = date('Y-m-d', strtotime('+45 days'));
        $base_price = 100000;
        
        $result = $this->campaign_manager->get_best_applicable_campaign_for_room(
            5, $checkin_date, $checkout_date, $base_price
        );
        
        $this->assertNotNull($result);
        $this->assertCount(1, $result);
        
        $campaign = $result[0];
        $this->assertEquals('キャンペーンB', $campaign['name']);
        $this->assertEquals(20000, $campaign['discount_amount']); // 20% of 100000
    }

    /**
     * TC-06: いかなる場合でも、複数のキャンペーンが同時に適用され、二重割引が発生しないことを確認する
     */
    public function test_tc06_no_double_discount_applied() {
        $immediate_id = $this->create_test_campaign('即入居割', '20%OFF', 'percentage', 20.00, 'immediate');
        $earlybird_id = $this->create_test_campaign('早割', '10%OFF', 'percentage', 10.00, 'earlybird');
        $general_id = $this->create_test_campaign('一般割引', '15%OFF', 'percentage', 15.00, 'general');
        
        $this->create_room_campaign_assignment(6, $immediate_id, date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+60 days')));
        $this->create_room_campaign_assignment(6, $earlybird_id, date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+60 days')));
        $this->create_room_campaign_assignment(6, $general_id, date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+60 days')));
        
        $checkin_date = date('Y-m-d', strtotime('+35 days')); // Satisfies earlybird
        $checkout_date = date('Y-m-d', strtotime('+65 days'));
        $base_price = 100000;
        
        $result = $this->campaign_manager->get_best_applicable_campaign_for_room(
            6, $checkin_date, $checkout_date, $base_price
        );
        
        $this->assertNotNull($result);
        $this->assertCount(1, $result, 'Exactly one campaign should be returned to prevent double discount');
        
        $campaign = $result[0];
        $this->assertLessThanOrEqual(20000, $campaign['discount_amount'], 'Discount should not exceed single campaign maximum');
    }

    /**
     * TC-07: チェックイン日がちょうど7日後の場合、「即入居割」が適用される
     */
    public function test_tc07_immediate_discount_at_7_day_boundary() {
        $campaign_id = $this->create_test_campaign('即入居割', '7日以内で20%OFF', 'percentage', 20.00, 'immediate');
        $this->create_room_campaign_assignment(7, $campaign_id, date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+30 days')));
        
        $checkin_date = date('Y-m-d', strtotime('+7 days'));
        $checkout_date = date('Y-m-d', strtotime('+17 days'));
        $base_price = 100000;
        
        $result = $this->campaign_manager->get_best_applicable_campaign_for_room(
            7, $checkin_date, $checkout_date, $base_price
        );
        
        $this->assertNotNull($result, 'Campaign should be applied at 7-day boundary');
        $this->assertCount(1, $result);
        
        $campaign = $result[0];
        $this->assertEquals('即入居割', $campaign['name']);
        $this->assertEquals(7, $campaign['days_until_checkin']);
    }

    /**
     * TC-08: チェックイン日が8日後の場合、「即入居割」が適用されない
     */
    public function test_tc08_immediate_discount_not_applied_at_8_days() {
        $campaign_id = $this->create_test_campaign('即入居割', '7日以内で20%OFF', 'percentage', 20.00, 'immediate');
        $this->create_room_campaign_assignment(8, $campaign_id, date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+30 days')));
        
        $checkin_date = date('Y-m-d', strtotime('+8 days'));
        $checkout_date = date('Y-m-d', strtotime('+18 days'));
        $base_price = 100000;
        
        $result = $this->campaign_manager->get_best_applicable_campaign_for_room(
            8, $checkin_date, $checkout_date, $base_price
        );
        
        $this->assertNull($result, 'Immediate discount should not apply beyond 7 days');
    }

    /**
     * TC-09: チェックイン日がちょうど30日後の場合、「早割」が適用される
     */
    public function test_tc09_earlybird_discount_at_30_day_boundary() {
        $campaign_id = $this->create_test_campaign('早割', '30日前予約で10%OFF', 'percentage', 10.00, 'earlybird');
        $this->create_room_campaign_assignment(9, $campaign_id, date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+60 days')));
        
        $checkin_date = date('Y-m-d', strtotime('+30 days'));
        $checkout_date = date('Y-m-d', strtotime('+60 days'));
        $base_price = 100000;
        
        $result = $this->campaign_manager->get_best_applicable_campaign_for_room(
            9, $checkin_date, $checkout_date, $base_price
        );
        
        $this->assertNotNull($result, 'Campaign should be applied at 30-day boundary');
        $this->assertCount(1, $result);
        
        $campaign = $result[0];
        $this->assertEquals('早割', $campaign['name']);
        $this->assertEquals(30, $campaign['days_until_checkin']);
    }

    /**
     * TC-10: チェックイン日が29日後の場合、「早割」が適用されない
     */
    public function test_tc10_earlybird_discount_not_applied_at_29_days() {
        $campaign_id = $this->create_test_campaign('早割', '30日前予約で10%OFF', 'percentage', 10.00, 'earlybird');
        $this->create_room_campaign_assignment(10, $campaign_id, date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+60 days')));
        
        $checkin_date = date('Y-m-d', strtotime('+29 days'));
        $checkout_date = date('Y-m-d', strtotime('+59 days'));
        $base_price = 100000;
        
        $result = $this->campaign_manager->get_best_applicable_campaign_for_room(
            10, $checkin_date, $checkout_date, $base_price
        );
        
        $this->assertNull($result, 'Early bird discount should not apply before 30 days');
    }

    /**
     * TC-11: 滞在日数がちょうど7日間の場合、「コミコミプラン」が適用される
     */
    public function test_tc11_flatrate_plan_at_7_day_stay_boundary() {
        $campaign_id = $this->create_test_campaign('コミコミプラン', '7-10日滞在で10万円', 'flatrate', 100000.00, 'flatrate');
        $this->create_room_campaign_assignment(11, $campaign_id, date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+30 days')));
        
        $checkin_date = date('Y-m-d', strtotime('+15 days'));
        $checkout_date = date('Y-m-d', strtotime('+22 days')); // 7 days stay
        $base_price = 100000;
        
        $result = $this->campaign_manager->get_best_applicable_campaign_for_room(
            11, $checkin_date, $checkout_date, $base_price
        );
        
        $this->assertNotNull($result, 'Flatrate should be applied at 7-day stay boundary');
        $this->assertCount(1, $result);
        
        $campaign = $result[0];
        $this->assertEquals('コミコミプラン', $campaign['name']);
        $this->assertEquals('flatrate', $campaign['type']);
    }

    /**
     * TC-12: 滞在日数がちょうど10日間の場合、「コミコミプラン」が適用される
     */
    public function test_tc12_flatrate_plan_at_10_day_stay_boundary() {
        $campaign_id = $this->create_test_campaign('コミコミプラン', '7-10日滞在で10万円', 'flatrate', 100000.00, 'flatrate');
        $this->create_room_campaign_assignment(12, $campaign_id, date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+30 days')));
        
        $checkin_date = date('Y-m-d', strtotime('+15 days'));
        $checkout_date = date('Y-m-d', strtotime('+25 days')); // 10 days stay
        $base_price = 100000;
        
        $result = $this->campaign_manager->get_best_applicable_campaign_for_room(
            12, $checkin_date, $checkout_date, $base_price
        );
        
        $this->assertNotNull($result, 'Flatrate should be applied at 10-day stay boundary');
        $this->assertCount(1, $result);
        
        $campaign = $result[0];
        $this->assertEquals('コミコミプラン', $campaign['name']);
        $this->assertEquals('flatrate', $campaign['type']);
    }

    /**
     * TC-13: どのキャンペーンの条件にも合致しない場合、割引は適用されず、通常料金で計算される
     */
    public function test_tc13_no_campaign_conditions_met_returns_null() {
        $immediate_id = $this->create_test_campaign('即入居割', '7日以内', 'percentage', 20.00, 'immediate');
        $earlybird_id = $this->create_test_campaign('早割', '30日前', 'percentage', 10.00, 'earlybird');
        $flatrate_id = $this->create_test_campaign('コミコミプラン', '7-10日滞在', 'flatrate', 100000.00, 'flatrate');
        
        $this->create_room_campaign_assignment(13, $immediate_id, date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+30 days')));
        $this->create_room_campaign_assignment(13, $earlybird_id, date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+60 days')));
        $this->create_room_campaign_assignment(13, $flatrate_id, date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+30 days')));
        
        $checkin_date = date('Y-m-d', strtotime('+15 days'));
        $checkout_date = date('Y-m-d', strtotime('+20 days')); // 5 days stay
        $base_price = 100000;
        
        $result = $this->campaign_manager->get_best_applicable_campaign_for_room(
            13, $checkin_date, $checkout_date, $base_price
        );
        
        $this->assertNull($result, 'Should return null when no campaign conditions are met');
    }

    /**
     * TC-14: 部屋にキャンペーンが割り当てられているが、そのステータスが「無効」の場合、割引は適用されない
     */
    public function test_tc14_inactive_campaign_not_applied() {
        $campaign_id = $this->create_test_campaign('即入居割', '7日以内で20%OFF', 'percentage', 20.00, 'immediate');
        
        $this->create_room_campaign_assignment(
            14, $campaign_id,
            date('Y-m-d', strtotime('-10 days')),
            date('Y-m-d', strtotime('+30 days')),
            0 // is_active = 0 (inactive)
        );
        
        $checkin_date = date('Y-m-d', strtotime('+5 days'));
        $checkout_date = date('Y-m-d', strtotime('+15 days'));
        $base_price = 100000;
        
        $result = $this->campaign_manager->get_best_applicable_campaign_for_room(
            14, $checkin_date, $checkout_date, $base_price
        );
        
        $this->assertNull($result, 'Inactive campaigns should not be applied');
    }

    /**
     * TC-15: 予約しようとしている期間が、部屋に割り当てられたキャンペーンの適用期間外である場合、割引は適用されない
     */
    public function test_tc15_booking_period_outside_campaign_period() {
        $campaign_id = $this->create_test_campaign('即入居割', '7日以内で20%OFF', 'percentage', 20.00, 'immediate');
        
        $this->create_room_campaign_assignment(
            15, $campaign_id,
            '2025-01-01', // start_date
            '2025-01-31'  // end_date
        );
        
        $checkin_date = '2025-02-05';  // Outside campaign period
        $checkout_date = '2025-02-15';
        $base_price = 100000;
        
        $result = $this->campaign_manager->get_best_applicable_campaign_for_room(
            15, $checkin_date, $checkout_date, $base_price
        );
        
        $this->assertNull($result, 'Campaigns outside their application period should not be applied');
    }
}
