<?php
if (!defined('ABSPATH')) {
    exit;
}

class MonthlyBooking_Calendar_API {
    
    public function __construct() {
        
    }
    
    public function mbp_get_rooms() {
        global $wpdb;
        
        $rooms_table = $wpdb->prefix . 'monthly_rooms';
        
        $sql = "SELECT room_id as id, display_name as name, room_name, property_name 
                FROM $rooms_table 
                WHERE is_active = 1 
                ORDER BY property_name, room_name";
        
        $results = $wpdb->get_results($sql);
        
        if ($wpdb->last_error) {
            error_log('Monthly Booking Calendar API - Room query error: ' . $wpdb->last_error);
            return array();
        }
        
        return $results ? $results : array();
    }
    
    public function mbp_get_bookings($room_id, $from, $to) {
        global $wpdb;
        
        if (!$room_id || !$from || !$to) {
            return array();
        }
        
        $bookings_table = $wpdb->prefix . 'monthly_bookings';
        $reservations_table = $wpdb->prefix . 'monthly_reservations';
        
        $bookings = array();
        
        $sql = "SELECT start_date as checkin_date, end_date as checkout_date, status
                FROM $bookings_table 
                WHERE room_id = %d 
                AND (start_date <= %s AND end_date >= %s) 
                AND status != 'cancelled'
                ORDER BY start_date";
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $room_id, $to, $from));
        
        if ($results) {
            $bookings = array_merge($bookings, $results);
        }
        
        if (defined('MB_FEATURE_RESERVATIONS_MVP') && MB_FEATURE_RESERVATIONS_MVP) {
            $sql = "SELECT checkin_date, checkout_date, status
                    FROM $reservations_table 
                    WHERE room_id = %d 
                    AND (checkin_date <= %s AND checkout_date >= %s) 
                    AND status != 'canceled'
                    ORDER BY checkin_date";
            
            $reservation_results = $wpdb->get_results($wpdb->prepare($sql, $room_id, $to, $from));
            
            if ($reservation_results) {
                foreach ($reservation_results as $reservation) {
                    $reservation->checkin_date = $reservation->checkin_date;
                    $reservation->checkout_date = $reservation->checkout_date;
                }
                $bookings = array_merge($bookings, $reservation_results);
            }
        }
        
        if ($wpdb->last_error) {
            error_log('Monthly Booking Calendar API - Booking query error: ' . $wpdb->last_error);
            return array();
        }
        
        return $bookings;
    }
    
    public function mbp_get_campaign_days($room_id, $from, $to) {
        global $wpdb;
        
        if (!$room_id || !$from || !$to) {
            return array();
        }
        
        $campaigns_table = $wpdb->prefix . 'monthly_campaigns';
        $room_campaigns_table = $wpdb->prefix . 'monthly_room_campaigns';
        
        $sql = "SELECT c.campaign_name as name, c.type, rc.start_date, rc.end_date
                FROM $campaigns_table c
                INNER JOIN $room_campaigns_table rc ON c.id = rc.campaign_id
                WHERE rc.room_id = %d 
                AND c.is_active = 1 
                AND rc.is_active = 1
                AND (rc.start_date <= %s AND rc.end_date >= %s)
                ORDER BY rc.start_date";
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $room_id, $to, $from));
        
        if ($wpdb->last_error) {
            error_log('Monthly Booking Calendar API - Campaign query error: ' . $wpdb->last_error);
            return array();
        }
        
        $campaign_days = array();
        
        if ($results) {
            foreach ($results as $campaign) {
                $start = new DateTime($campaign->start_date);
                $end = new DateTime($campaign->end_date);
                $end->modify('+1 day');
                
                $period = new DatePeriod($start, new DateInterval('P1D'), $end);
                
                foreach ($period as $date) {
                    $date_str = $date->format('Y-m-d');
                    if ($date_str >= $from && $date_str <= $to) {
                        $campaign_days[$date_str] = array(
                            'name' => $campaign->name,
                            'type' => $campaign->type
                        );
                    }
                }
            }
        }
        
        return $campaign_days;
    }
    
    public function get_global_campaigns($from, $to) {
        global $wpdb;
        
        if (!$from || !$to) {
            return array();
        }
        
        $campaigns_table = $wpdb->prefix . 'monthly_campaigns';
        
        $sql = "SELECT campaign_name as name, type, start_date, end_date
                FROM $campaigns_table 
                WHERE is_active = 1 
                AND (start_date <= %s AND end_date >= %s)
                ORDER BY start_date";
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $to, $from));
        
        if ($wpdb->last_error) {
            error_log('Monthly Booking Calendar API - Global campaign query error: ' . $wpdb->last_error);
            return array();
        }
        
        $campaign_days = array();
        
        if ($results) {
            foreach ($results as $campaign) {
                $start = new DateTime($campaign->start_date);
                $end = new DateTime($campaign->end_date);
                $end->modify('+1 day');
                
                $period = new DatePeriod($start, new DateInterval('P1D'), $end);
                
                foreach ($period as $date) {
                    $date_str = $date->format('Y-m-d');
                    if ($date_str >= $from && $date_str <= $to) {
                        if (!isset($campaign_days[$date_str])) {
                            $campaign_days[$date_str] = array();
                        }
                        $campaign_days[$date_str][] = array(
                            'name' => $campaign->name,
                            'type' => $campaign->type
                        );
                    }
                }
            }
        }
        
        return $campaign_days;
    }
}
