<?php
if (!defined('ABSPATH')) {
    exit;
}

class MonthlyBooking_Calendar_Utils {
    
    public static function date_ranges_overlap($start1, $end1, $start2, $end2) {
        return ($start1 <= $end2) && ($end1 >= $start2);
    }
    
    public static function calculate_cleaning_buffer($checkin_date, $checkout_date) {
        $checkin = new DateTime($checkin_date);
        $checkout = new DateTime($checkout_date);
        
        $buffer_start = clone $checkin;
        $buffer_start->modify('-5 days');
        
        $buffer_end = clone $checkout;
        $buffer_end->modify('+5 days');
        
        return array(
            'start' => $buffer_start->format('Y-m-d'),
            'end' => $buffer_end->format('Y-m-d')
        );
    }
    
    public static function get_wp_timezone_date($date_string = 'now') {
        $timezone = wp_timezone();
        $date = new DateTime($date_string, $timezone);
        return $date;
    }
    
    public static function format_japanese_date($date_string) {
        $date = new DateTime($date_string);
        $month = $date->format('n');
        $day = $date->format('j');
        $day_of_week = $date->format('w');
        
        $japanese_days = array('日', '月', '火', '水', '木', '金', '土');
        
        return array(
            'month' => $month,
            'day' => $day,
            'day_of_week' => $japanese_days[$day_of_week],
            'formatted' => $month . '/' . $day . '(' . $japanese_days[$day_of_week] . ')'
        );
    }
    
    public static function get_day_status($date, $bookings, $campaign_days) {
        $date_obj = new DateTime($date);
        $date_str = $date_obj->format('Y-m-d');
        
        foreach ($bookings as $booking) {
            $checkin = new DateTime($booking->checkin_date);
            $checkout = new DateTime($booking->checkout_date);
            
            if ($date_obj >= $checkin && $date_obj < $checkout) {
                return array(
                    'status' => 'booked',
                    'symbol' => '×',
                    'class' => 'booked',
                    'label' => '予約済み'
                );
            }
            
            $buffer = self::calculate_cleaning_buffer($booking->checkin_date, $booking->checkout_date);
            $buffer_start = new DateTime($buffer['start']);
            $buffer_end = new DateTime($buffer['end']);
            
            if ($date_obj >= $buffer_start && $date_obj <= $buffer_end && 
                !($date_obj >= $checkin && $date_obj < $checkout)) {
                return array(
                    'status' => 'cleaning',
                    'symbol' => '×',
                    'class' => 'booked',
                    'label' => '清掃期間'
                );
            }
        }
        
        if (isset($campaign_days[$date_str])) {
            $campaign = $campaign_days[$date_str];
            return array(
                'status' => 'campaign',
                'symbol' => '△',
                'class' => 'campaign',
                'label' => 'キャンペーン対象',
                'campaign_name' => $campaign['name'],
                'campaign_type' => $campaign['type']
            );
        }
        
        return array(
            'status' => 'available',
            'symbol' => '〇',
            'class' => 'available',
            'label' => '空室'
        );
    }
    
    public static function generate_6_month_dates($start_date = null) {
        if (!$start_date) {
            $start_date = self::get_wp_timezone_date('today')->format('Y-m-d');
        }
        
        $start = new DateTime($start_date);
        $end = clone $start;
        $end->modify('+180 days');
        
        $dates = array();
        $current = clone $start;
        
        while ($current < $end) {
            $dates[] = $current->format('Y-m-d');
            $current->modify('+1 day');
        }
        
        return $dates;
    }
    
    public static function group_dates_by_month($dates) {
        $months = array();
        
        foreach ($dates as $date) {
            $date_obj = new DateTime($date);
            $month_key = $date_obj->format('Y-m');
            
            if (!isset($months[$month_key])) {
                $months[$month_key] = array(
                    'year' => $date_obj->format('Y'),
                    'month' => $date_obj->format('n'),
                    'month_name' => $date_obj->format('Y年n月'),
                    'dates' => array()
                );
            }
            
            $months[$month_key]['dates'][] = $date;
        }
        
        return $months;
    }
}
