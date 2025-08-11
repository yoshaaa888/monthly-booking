<?php

class Monthly_Booking_Fee_Manager {
    
    private static $instance = null;
    private $fee_cache = array();
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
    }
    
    /**
     * 料金設定値を取得
     */
    public function get_fee($setting_key, $default_value = 0) {
        if (isset($this->fee_cache[$setting_key])) {
            return $this->fee_cache[$setting_key];
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'monthly_fee_settings';
        
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM {$table_name} WHERE setting_key = %s AND is_active = 1",
            $setting_key
        ));
        
        $result = ($value !== null) ? floatval($value) : $default_value;
        $this->fee_cache[$setting_key] = $result;
        
        return $result;
    }
    
    /**
     * 複数の料金設定を一括取得
     */
    public function get_fees($setting_keys) {
        $fees = array();
        foreach ($setting_keys as $key) {
            $fees[$key] = $this->get_fee($key);
        }
        return $fees;
    }
    
    /**
     * 全ての料金設定を取得（管理画面用）
     */
    public function get_all_fees() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'monthly_fee_settings';
        
        return $wpdb->get_results(
            "SELECT * FROM {$table_name} WHERE is_active = 1 ORDER BY category, display_order ASC"
        );
    }
    
    /**
     * 料金設定を更新
     */
    public function update_fee($setting_key, $value) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'monthly_fee_settings';
        
        $sanitized_value = floatval($value);
        if ($sanitized_value < 0 || $sanitized_value > 9999999) {
            return false;
        }
        
        $result = $wpdb->update(
            $table_name,
            array(
                'setting_value' => $sanitized_value,
                'updated_at' => current_time('mysql')
            ),
            array('setting_key' => $setting_key),
            array('%f', '%s'),
            array('%s')
        );
        
        if ($result !== false) {
            unset($this->fee_cache[$setting_key]);
        }
        
        return $result;
    }
    
    /**
     * 複数の料金設定を一括更新
     */
    public function update_fees($fees) {
        $success_count = 0;
        
        foreach ($fees as $key => $value) {
            if ($this->update_fee($key, $value) !== false) {
                $success_count++;
            }
        }
        
        return $success_count;
    }
    
    /**
     * キャッシュをクリア
     */
    public function clear_cache() {
        $this->fee_cache = array();
    }
    
}
