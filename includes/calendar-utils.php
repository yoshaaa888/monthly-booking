<?php

if (!defined('ABSPATH')) { exit; }

if (!class_exists('MonthlyBooking_Calendar_Utils')) {
class MonthlyBooking_Calendar_Utils {

    public static function get_wp_timezone_date($rel = 'today') {
        $tz = wp_timezone();
        $dt = new DateTime('now', $tz);
        if ($rel !== 'today') {
            $dt = new DateTime($rel, $tz);
        }
        return $dt;
    }

    public static function generate_6_month_dates($startYmd) {
        $start = self::get_wp_timezone_date($startYmd);
        $dates = array();
        for ($i=0; $i<180; $i++) {
            $d = clone $start;
            $d->modify("+$i days");
            $dates[] = $d->format('Y-m-d');
        }
        return $dates;
    }

    public static function group_dates_by_month($dates) {
        $res = array();
        foreach ($dates as $d) {
            $y = intval(substr($d,0,4));
            $m = intval(substr($d,5,2));
            $key = "$y-$m";
            if (!isset($res[$key])) {
                $month_name = $y.'年'.$m.'月';
                $res[$key] = array('year'=>$y,'month'=>$m,'month_name'=>$month_name,'dates'=>array());
            }
            $res[$key]['dates'][] = $d;
        }
        return array_values($res);
    }

    public static function generate_dates_span($startYmd, $days) {
        $start = self::get_wp_timezone_date($startYmd);
        $dates = array();
        $n = max(1, min(180, intval($days)));
        for ($i=0; $i<$n; $i++) {
            $d = clone $start;
            $d->modify("+$i days");
            $dates[] = $d->format('Y-m-d');
        }
        return $dates;
    }

    public static function format_japanese_date($ymd) {
        $y = intval(substr($ymd,0,4));
        $m = intval(substr($ymd,5,2));
        $d = intval(substr($ymd,8,2));
        return array(
            'formatted' => sprintf('%04d年%02d月%02d日', $y, $m, $d),
            'day' => $d
        );
    }

    public static function format_day_short($ymd) {
        $m = intval(substr($ymd,5,2));
        $d = intval(substr($ymd,8,2));
        return $m.'/'.$d;
    }

    public static function get_day_status($date, $bookings, $campaign_days) {
        $isBooked = false;
        foreach ($bookings as $b) {
            $ci = $b['checkin'];
            $co = $b['checkout'];
            if ($date >= $ci && $date < $co) {
                $isBooked = true;
                break;
            }
        }
        if ($isBooked) {
            return array('class'=>'booked','label'=>__('予約済み','monthly-booking'),'symbol'=>'×');
        }
        if (is_array($campaign_days) && in_array($date, $campaign_days, true)) {
            return array('class'=>'campaign','label'=>__('キャンペーン対象','monthly-booking'),'symbol'=>'△','campaign_name'=>__('キャンペーン','monthly-booking'),'campaign_type'=>'generic');
        }
        return array('class'=>'available','label'=>__('空室','monthly-booking'),'symbol'=>'〇');
    }

    public static function get_day_status_for_room($date, $room_bookings, $room_assignments = array(), $room_active = true) {
        if (!$room_active) {
            return array('class'=>'unavailable','label'=>__('利用不可','monthly-booking'),'symbol'=>'×','code'=>'unavail');
        }
        $isOccupied = false;
        $isCleaning = false;
        foreach ($room_bookings as $b) {
            $ci = $b['checkin'];
            $co = $b['checkout'];
            if ($date >= $ci && $date < $co) { $isOccupied = true; break; }
            if ($date >= $co && $date < date('Y-m-d', strtotime($co . ' +5 days'))) { $isCleaning = true; }
        }
        if ($isOccupied) {
            return array('class'=>'occupied','label'=>__('予約中','monthly-booking'),'symbol'=>'◆','code'=>'occ');
        }
        if ($isCleaning) {
            return array('class'=>'cleaning','label'=>__('清掃期間','monthly-booking'),'symbol'=>'△','code'=>'clean');
        }
        $hasActiveCampaign = false;
        if (is_array($room_assignments)) {
            foreach ($room_assignments as $a) {
                $sd = isset($a['start_date']) ? $a['start_date'] : null;
                $ed = isset($a['end_date']) ? $a['end_date'] : null;
                $active = isset($a['is_active']) ? intval($a['is_active']) === 1 : true;
                if (!$active) continue;
                if ($sd && $date < $sd) continue;
                if ($ed && $date >= $ed) continue;
                $hasActiveCampaign = true;
                break;
            }
        }
        if ($hasActiveCampaign) {
            return array('class'=>'vacant-campaign','label'=>__('空室＋キャンペーン','monthly-booking'),'symbol'=>'◎','code'=>'vac_camp');
        }
        return array('class'=>'available','label'=>__('空室','monthly-booking'),'symbol'=>'〇','code'=>'vac');
    }
}}
