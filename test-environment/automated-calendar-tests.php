<?php

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
}

require_once ABSPATH . 'wp-config.php';
require_once ABSPATH . 'wp-includes/wp-db.php';
require_once ABSPATH . 'wp-includes/functions.php';

class MonthlyBookingCalendarTests {
    
    private $test_results = array();
    private $screenshots_dir;
    private $start_time;
    
    public function __construct() {
        $this->screenshots_dir = dirname(__FILE__) . '/test-screenshots/';
        if (!file_exists($this->screenshots_dir)) {
            mkdir($this->screenshots_dir, 0755, true);
        }
        $this->start_time = microtime(true);
    }
    
    public function run_all_tests() {
        echo "=== Monthly Booking Calendar Automated Tests ===\n";
        echo "Start Time: " . date('Y-m-d H:i:s') . "\n\n";
        
        $this->test_calendar_display();
        $this->test_room_selection();
        $this->test_cleaning_buffer_logic();
        $this->test_campaign_integration();
        $this->test_ajax_functionality();
        $this->test_responsive_design();
        $this->test_accessibility_features();
        
        $this->generate_summary_report();
    }
    
    private function test_calendar_display() {
        echo "Testing Calendar Display...\n";
        
        try {
            $calendar_render = new MonthlyBooking_Calendar_Render();
            
            $shortcode_output = $calendar_render->render_calendar_shortcode(array());
            $this->assert_contains($shortcode_output, 'monthly-booking-calendar-container', 'Calendar container exists');
            $this->assert_contains($shortcode_output, '予約カレンダー', 'Japanese calendar title exists');
            $this->assert_contains($shortcode_output, 'room-selector', 'Room selector dropdown exists');
            
            $specific_room_output = $calendar_render->render_calendar_shortcode(array('room_id' => '633'));
            $this->assert_not_contains($specific_room_output, 'room-selector', 'Room selector hidden for specific room');
            
            $this->record_test_result('calendar_display', true, 'Calendar display tests passed');
            
        } catch (Exception $e) {
            $this->record_test_result('calendar_display', false, 'Calendar display failed: ' . $e->getMessage());
        }
    }
    
    private function test_room_selection() {
        echo "Testing Room Selection Functionality...\n";
        
        try {
            $rooms = MonthlyBooking_Calendar_API::mbp_get_rooms();
            $this->assert_true(is_array($rooms), 'Rooms data is array');
            $this->assert_true(count($rooms) > 0, 'At least one room exists');
            
            if (count($rooms) > 0) {
                $first_room = $rooms[0];
                $this->assert_true(isset($first_room->id), 'Room has ID property');
                $this->assert_true(isset($first_room->name), 'Room has name property');
            }
            
            $this->record_test_result('room_selection', true, 'Room selection tests passed');
            
        } catch (Exception $e) {
            $this->record_test_result('room_selection', false, 'Room selection failed: ' . $e->getMessage());
        }
    }
    
    private function test_cleaning_buffer_logic() {
        echo "Testing Cleaning Buffer Logic...\n";
        
        try {
            $checkin = '2025-08-15';
            $checkout = '2025-08-20';
            
            $buffer = MonthlyBooking_Calendar_Utils::calculate_cleaning_buffer($checkin, $checkout);
            
            $this->assert_equals($buffer['start'], '2025-08-10', 'Buffer start date correct');
            $this->assert_equals($buffer['end'], '2025-08-25', 'Buffer end date correct');
            
            $test_bookings = array(
                (object) array('checkin_date' => $checkin, 'checkout_date' => $checkout)
            );
            
            $buffer_date = new DateTime('2025-08-12');
            $status = MonthlyBooking_Calendar_Utils::get_day_status($buffer_date, $test_bookings, array());
            $this->assert_equals($status, 'booked', 'Buffer date shows as booked');
            
            $available_date = new DateTime('2025-08-09');
            $status = MonthlyBooking_Calendar_Utils::get_day_status($available_date, $test_bookings, array());
            $this->assert_equals($status, 'available', 'Date outside buffer shows as available');
            
            $this->record_test_result('cleaning_buffer', true, 'Cleaning buffer logic tests passed');
            
        } catch (Exception $e) {
            $this->record_test_result('cleaning_buffer', false, 'Cleaning buffer failed: ' . $e->getMessage());
        }
    }
    
    private function test_campaign_integration() {
        echo "Testing Campaign Integration...\n";
        
        try {
            $rooms = MonthlyBooking_Calendar_API::mbp_get_rooms();
            if (count($rooms) > 0) {
                $room_id = $rooms[0]->id;
                $from = date('Y-m-d');
                $to = date('Y-m-d', strtotime('+180 days'));
                
                $campaigns = MonthlyBooking_Calendar_API::mbp_get_campaign_days($room_id, $from, $to);
                $this->assert_true(is_array($campaigns), 'Campaign data is array');
                
                if (count($campaigns) > 0) {
                    $first_campaign_date = array_keys($campaigns)[0];
                    $campaign_info = $campaigns[$first_campaign_date];
                    $this->assert_true(isset($campaign_info['name']), 'Campaign has name');
                    $this->assert_true(isset($campaign_info['type']), 'Campaign has type');
                }
            }
            
            $this->record_test_result('campaign_integration', true, 'Campaign integration tests passed');
            
        } catch (Exception $e) {
            $this->record_test_result('campaign_integration', false, 'Campaign integration failed: ' . $e->getMessage());
        }
    }
    
    private function test_ajax_functionality() {
        echo "Testing AJAX Functionality...\n";
        
        try {
            $_POST['room_id'] = '633';
            $_POST['nonce'] = wp_create_nonce('mbp_calendar_nonce');
            
            ob_start();
            $plugin = new MonthlyBooking();
            $plugin->ajax_load_calendar();
            $output = ob_get_clean();
            
            $this->assert_true(strlen($output) > 0, 'AJAX returns content');
            
            $this->record_test_result('ajax_functionality', true, 'AJAX functionality tests passed');
            
        } catch (Exception $e) {
            $this->record_test_result('ajax_functionality', false, 'AJAX functionality failed: ' . $e->getMessage());
        }
    }
    
    private function test_responsive_design() {
        echo "Testing Responsive Design...\n";
        
        try {
            $css_file = dirname(__FILE__) . '/../assets/calendar.css';
            $css_content = file_get_contents($css_file);
            
            $this->assert_contains($css_content, '@media (max-width: 768px)', 'Mobile breakpoint exists');
            $this->assert_contains($css_content, '.monthly-booking-calendar-container', 'Main container styles exist');
            $this->assert_contains($css_content, '.calendar-day', 'Calendar day styles exist');
            
            $this->record_test_result('responsive_design', true, 'Responsive design tests passed');
            
        } catch (Exception $e) {
            $this->record_test_result('responsive_design', false, 'Responsive design failed: ' . $e->getMessage());
        }
    }
    
    private function test_accessibility_features() {
        echo "Testing Accessibility Features...\n";
        
        try {
            $calendar_render = new MonthlyBooking_Calendar_Render();
            $output = $calendar_render->render_6_month_calendar(633);
            
            $this->assert_contains($output, 'aria-label', 'ARIA labels present');
            $this->assert_contains($output, 'tabindex="0"', 'Keyboard navigation support');
            $this->assert_contains($output, '予約可能', 'Screen reader text present');
            
            $this->record_test_result('accessibility', true, 'Accessibility tests passed');
            
        } catch (Exception $e) {
            $this->record_test_result('accessibility', false, 'Accessibility failed: ' . $e->getMessage());
        }
    }
    
    private function assert_true($condition, $message) {
        if (!$condition) {
            throw new Exception("Assertion failed: $message");
        }
    }
    
    private function assert_equals($actual, $expected, $message) {
        if ($actual !== $expected) {
            throw new Exception("Assertion failed: $message. Expected: $expected, Actual: $actual");
        }
    }
    
    private function assert_contains($haystack, $needle, $message) {
        if (strpos($haystack, $needle) === false) {
            throw new Exception("Assertion failed: $message. '$needle' not found in content");
        }
    }
    
    private function assert_not_contains($haystack, $needle, $message) {
        if (strpos($haystack, $needle) !== false) {
            throw new Exception("Assertion failed: $message. '$needle' found in content when it shouldn't be");
        }
    }
    
    private function record_test_result($test_name, $success, $message) {
        $this->test_results[] = array(
            'test' => $test_name,
            'success' => $success,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        );
        
        echo ($success ? "✓ PASS" : "✗ FAIL") . ": $message\n";
    }
    
    private function generate_summary_report() {
        $end_time = microtime(true);
        $execution_time = round($end_time - $this->start_time, 2);
        
        echo "\n=== Test Summary Report ===\n";
        echo "Execution Time: {$execution_time} seconds\n";
        echo "Total Tests: " . count($this->test_results) . "\n";
        
        $passed = array_filter($this->test_results, function($result) { return $result['success']; });
        $failed = array_filter($this->test_results, function($result) { return !$result['success']; });
        
        echo "Passed: " . count($passed) . "\n";
        echo "Failed: " . count($failed) . "\n";
        
        if (count($failed) > 0) {
            echo "\nFailed Tests:\n";
            foreach ($failed as $failure) {
                echo "- {$failure['test']}: {$failure['message']}\n";
            }
        }
        
        echo "\nDetailed Results:\n";
        foreach ($this->test_results as $result) {
            $status = $result['success'] ? 'PASS' : 'FAIL';
            echo "[{$result['timestamp']}] {$status}: {$result['test']} - {$result['message']}\n";
        }
        
        $report_file = dirname(__FILE__) . '/test-report-' . date('Y-m-d-H-i-s') . '.txt';
        file_put_contents($report_file, ob_get_contents());
        echo "\nReport saved to: $report_file\n";
    }
}

if (php_sapi_name() === 'cli') {
    $tester = new MonthlyBookingCalendarTests();
    $tester->run_all_tests();
}
