<?php
/**
 * wp_monthly_options 重複防止機能
 * 
 * option_name + price の組み合わせで重複登録を防止します
 */

class MonthlyOptionsDuplicatePrevention {
    
    /**
     * オプション保存前の重複チェック
     * 
     * @param string $option_name オプション名
     * @param float $price 価格
     * @param int $exclude_id 除外するID（更新時に自分自身を除外）
     * @return array 検証結果
     */
    public static function validate_option_uniqueness($option_name, $price, $exclude_id = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_options';
        
        $query = $wpdb->prepare(
            "SELECT id, option_name, price FROM {$table_name} 
             WHERE option_name = %s AND price = %f",
            $option_name,
            $price
        );
        
        if ($exclude_id) {
            $query .= $wpdb->prepare(" AND id != %d", $exclude_id);
        }
        
        $existing_records = $wpdb->get_results($query);
        
        $result = array(
            'is_valid' => empty($existing_records),
            'error_message' => '',
            'existing_records' => $existing_records
        );
        
        if (!empty($existing_records)) {
            $existing_ids = array_column($existing_records, 'id');
            $result['error_message'] = sprintf(
                'オプション名「%s」と価格「¥%s」の組み合わせは既に存在します（ID: %s）',
                $option_name,
                number_format($price),
                implode(', ', $existing_ids)
            );
        }
        
        return $result;
    }
    
    /**
     * オプション名のみの重複チェック
     * 
     * @param string $option_name オプション名
     * @param int $exclude_id 除外するID
     * @return array 検証結果
     */
    public static function validate_option_name_uniqueness($option_name, $exclude_id = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_options';
        
        $query = $wpdb->prepare(
            "SELECT id, option_name, price FROM {$table_name} WHERE option_name = %s",
            $option_name
        );
        
        if ($exclude_id) {
            $query .= $wpdb->prepare(" AND id != %d", $exclude_id);
        }
        
        $existing_records = $wpdb->get_results($query);
        
        $result = array(
            'is_valid' => empty($existing_records),
            'error_message' => '',
            'existing_records' => $existing_records
        );
        
        if (!empty($existing_records)) {
            $existing_data = array();
            foreach ($existing_records as $record) {
                $existing_data[] = sprintf('ID:%d (¥%s)', $record->id, number_format($record->price));
            }
            
            $result['error_message'] = sprintf(
                'オプション名「%s」は既に存在します（%s）',
                $option_name,
                implode(', ', $existing_data)
            );
        }
        
        return $result;
    }
    
    /**
     * display_order の重複チェック
     * 
     * @param int $display_order 表示順序
     * @param int $exclude_id 除外するID
     * @return array 検証結果
     */
    public static function validate_display_order_uniqueness($display_order, $exclude_id = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_options';
        
        $query = $wpdb->prepare(
            "SELECT id, option_name, display_order FROM {$table_name} WHERE display_order = %d",
            $display_order
        );
        
        if ($exclude_id) {
            $query .= $wpdb->prepare(" AND id != %d", $exclude_id);
        }
        
        $existing_records = $wpdb->get_results($query);
        
        $result = array(
            'is_valid' => empty($existing_records),
            'error_message' => '',
            'existing_records' => $existing_records
        );
        
        if (!empty($existing_records)) {
            $existing_names = array_column($existing_records, 'option_name');
            $result['error_message'] = sprintf(
                '表示順序「%d」は既に使用されています（%s）',
                $display_order,
                implode(', ', $existing_names)
            );
        }
        
        return $result;
    }
    
    /**
     * 包括的なオプション検証
     * 
     * @param array $option_data オプションデータ
     * @param int $exclude_id 除外するID（更新時）
     * @return array 検証結果
     */
    public static function validate_option_comprehensive($option_data, $exclude_id = null) {
        $errors = array();
        
        if (empty($option_data['option_name'])) {
            $errors[] = 'オプション名は必須です';
        }
        
        if (!isset($option_data['price']) || $option_data['price'] < 0) {
            $errors[] = '価格は0以上の数値で入力してください';
        }
        
        if (!isset($option_data['display_order']) || $option_data['display_order'] < 1) {
            $errors[] = '表示順序は1以上の数値で入力してください';
        }
        
        if (!isset($option_data['is_discount_target']) || !in_array($option_data['is_discount_target'], array(0, 1))) {
            $errors[] = '割引対象フラグは0または1で設定してください';
        }
        
        if (empty($errors)) {
            $name_check = self::validate_option_name_uniqueness($option_data['option_name'], $exclude_id);
            if (!$name_check['is_valid']) {
                $errors[] = $name_check['error_message'];
            }
            
            $order_check = self::validate_display_order_uniqueness($option_data['display_order'], $exclude_id);
            if (!$order_check['is_valid']) {
                $errors[] = $order_check['error_message'];
            }
        }
        
        return array(
            'is_valid' => empty($errors),
            'errors' => $errors
        );
    }
    
    /**
     * データベース制約の追加（MySQL）
     * 
     * 注意: この関数は慎重に使用してください
     * 既存データに重複がある場合は事前にクリーンアップが必要です
     */
    public static function add_database_constraints() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_options';
        
        $constraints = $wpdb->get_results($wpdb->prepare(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
             WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND CONSTRAINT_TYPE = 'UNIQUE'",
            DB_NAME,
            $table_name
        ));
        
        $existing_constraints = array_column($constraints, 'CONSTRAINT_NAME');
        
        $results = array();
        
        if (!in_array('uk_monthly_options_name', $existing_constraints)) {
            $sql = "ALTER TABLE {$table_name} ADD CONSTRAINT uk_monthly_options_name UNIQUE (option_name)";
            $result = $wpdb->query($sql);
            $results['option_name_constraint'] = $result !== false;
        } else {
            $results['option_name_constraint'] = 'already_exists';
        }
        
        if (!in_array('uk_monthly_options_order', $existing_constraints)) {
            $sql = "ALTER TABLE {$table_name} ADD CONSTRAINT uk_monthly_options_order UNIQUE (display_order)";
            $result = $wpdb->query($sql);
            $results['display_order_constraint'] = $result !== false;
        } else {
            $results['display_order_constraint'] = 'already_exists';
        }
        
        return $results;
    }
    
    /**
     * 制約削除（テスト用）
     */
    public static function remove_database_constraints() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'monthly_options';
        
        $results = array();
        
        $sql = "ALTER TABLE {$table_name} DROP INDEX uk_monthly_options_name";
        $results['option_name_constraint_removed'] = $wpdb->query($sql) !== false;
        
        $sql = "ALTER TABLE {$table_name} DROP INDEX uk_monthly_options_order";
        $results['display_order_constraint_removed'] = $wpdb->query($sql) !== false;
        
        return $results;
    }
}

/**
 * WordPress フック統合
 */

add_action('wp_ajax_save_monthly_option', 'validate_monthly_option_before_save', 5);
add_action('wp_ajax_update_monthly_option', 'validate_monthly_option_before_update', 5);

function validate_monthly_option_before_save() {
    if (!current_user_can('manage_options')) {
        wp_die('権限がありません');
    }
    
    $option_data = array(
        'option_name' => sanitize_text_field($_POST['option_name'] ?? ''),
        'price' => floatval($_POST['price'] ?? 0),
        'display_order' => intval($_POST['display_order'] ?? 0),
        'is_discount_target' => intval($_POST['is_discount_target'] ?? 0)
    );
    
    $validation = MonthlyOptionsDuplicatePrevention::validate_option_comprehensive($option_data);
    
    if (!$validation['is_valid']) {
        wp_die(json_encode(array(
            'success' => false,
            'errors' => $validation['errors']
        )));
    }
    
}

function validate_monthly_option_before_update() {
    if (!current_user_can('manage_options')) {
        wp_die('権限がありません');
    }
    
    $option_id = intval($_POST['option_id'] ?? 0);
    $option_data = array(
        'option_name' => sanitize_text_field($_POST['option_name'] ?? ''),
        'price' => floatval($_POST['price'] ?? 0),
        'display_order' => intval($_POST['display_order'] ?? 0),
        'is_discount_target' => intval($_POST['is_discount_target'] ?? 0)
    );
    
    $validation = MonthlyOptionsDuplicatePrevention::validate_option_comprehensive($option_data, $option_id);
    
    if (!$validation['is_valid']) {
        wp_die(json_encode(array(
            'success' => false,
            'errors' => $validation['errors']
        )));
    }
    
}
?>
