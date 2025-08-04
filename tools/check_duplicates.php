<?php
/**
 * wp_monthly_options 重複検知・監視スクリプト
 * 
 * 定期実行用の重複チェックツール
 * 運用環境での誤登録防止とデータ整合性監視
 */

$wp_config_path = dirname(__FILE__) . '/../../wp-config.php';
if (file_exists($wp_config_path)) {
    require_once($wp_config_path);
} else {
    $alt_paths = array(
        dirname(__FILE__) . '/../wp-config.php',
        dirname(__FILE__) . '/../../../../wp-config.php'
    );
    
    foreach ($alt_paths as $path) {
        if (file_exists($path)) {
            require_once($path);
            break;
        }
    }
}

class MonthlyOptionsDuplicateChecker {
    
    private $wpdb;
    private $table_name;
    private $log_file;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'monthly_options';
        $this->log_file = dirname(__FILE__) . '/duplicate_check.log';
    }
    
    /**
     * 包括的な重複チェック実行
     */
    public function run_comprehensive_check() {
        $this->log("=== wp_monthly_options 重複チェック開始 ===");
        $this->log("実行日時: " . date('Y-m-d H:i:s'));
        
        $issues_found = array();
        
        $name_duplicates = $this->check_option_name_duplicates();
        if (!empty($name_duplicates)) {
            $issues_found['option_name_duplicates'] = $name_duplicates;
        }
        
        $order_duplicates = $this->check_display_order_duplicates();
        if (!empty($order_duplicates)) {
            $issues_found['display_order_duplicates'] = $order_duplicates;
        }
        
        $target_issues = $this->check_discount_target_accuracy();
        if (!empty($target_issues)) {
            $issues_found['discount_target_issues'] = $target_issues;
        }
        
        $price_issues = $this->check_price_consistency();
        if (!empty($price_issues)) {
            $issues_found['price_issues'] = $price_issues;
        }
        
        $record_count_issue = $this->check_record_count();
        if ($record_count_issue) {
            $issues_found['record_count_issue'] = $record_count_issue;
        }
        
        $this->generate_report($issues_found);
        
        return $issues_found;
    }
    
    /**
     * オプション名重複チェック
     */
    private function check_option_name_duplicates() {
        $sql = "
            SELECT 
                option_name, 
                COUNT(*) as duplicate_count,
                GROUP_CONCAT(id ORDER BY id) as all_ids,
                GROUP_CONCAT(price ORDER BY id) as all_prices,
                GROUP_CONCAT(is_discount_target ORDER BY id) as all_targets
            FROM {$this->table_name} 
            GROUP BY option_name 
            HAVING COUNT(*) > 1
            ORDER BY duplicate_count DESC
        ";
        
        $duplicates = $this->wpdb->get_results($sql);
        
        if (!empty($duplicates)) {
            $this->log("🚨 オプション名重複を発見:");
            foreach ($duplicates as $duplicate) {
                $this->log("  - {$duplicate->option_name}: {$duplicate->duplicate_count}件 (ID: {$duplicate->all_ids})");
            }
        } else {
            $this->log("✅ オプション名重複: なし");
        }
        
        return $duplicates;
    }
    
    /**
     * display_order重複チェック
     */
    private function check_display_order_duplicates() {
        $sql = "
            SELECT 
                display_order, 
                COUNT(*) as count,
                GROUP_CONCAT(option_name ORDER BY id) as option_names,
                GROUP_CONCAT(id ORDER BY id) as ids
            FROM {$this->table_name} 
            GROUP BY display_order 
            HAVING COUNT(*) > 1
            ORDER BY display_order
        ";
        
        $duplicates = $this->wpdb->get_results($sql);
        
        if (!empty($duplicates)) {
            $this->log("🚨 display_order重複を発見:");
            foreach ($duplicates as $duplicate) {
                $this->log("  - 順序{$duplicate->display_order}: {$duplicate->count}件 ({$duplicate->option_names})");
            }
        } else {
            $this->log("✅ display_order重複: なし");
        }
        
        return $duplicates;
    }
    
    /**
     * is_discount_target正確性チェック（IDベースルール）
     */
    private function check_discount_target_accuracy() {
        $sql = "
            SELECT 
                id, option_name, is_discount_target,
                CASE 
                    WHEN id BETWEEN 1 AND 7 THEN 1
                    WHEN id BETWEEN 8 AND 9 THEN 0
                    ELSE -1
                END as expected_target
            FROM {$this->table_name} 
            WHERE is_discount_target != CASE 
                WHEN id BETWEEN 1 AND 7 THEN 1
                WHEN id BETWEEN 8 AND 9 THEN 0
                ELSE -1
            END
            ORDER BY id
        ";
        
        $issues = $this->wpdb->get_results($sql);
        
        if (!empty($issues)) {
            $this->log("🚨 is_discount_target不正を発見:");
            foreach ($issues as $issue) {
                $this->log("  - ID{$issue->id} ({$issue->option_name}): 現在値={$issue->is_discount_target}, 期待値={$issue->expected_target}");
            }
        } else {
            $this->log("✅ is_discount_target: 全て正確");
        }
        
        return $issues;
    }
    
    /**
     * 価格整合性チェック（正規化された価格との比較）
     */
    private function check_price_consistency() {
        $normalized_prices = array(
            '調理器具セット' => 6600,
            '食器類' => 3900,
            '洗剤類' => 3800,
            'タオル類' => 2900,
            'アメニティ類' => 3500,
            '寝具カバーセット' => 4530,
            '毛布' => 3950,
            'アイロン' => 6860,
            '炊飯器（4合炊き）' => 6600
        );
        
        $issues = array();
        
        foreach ($normalized_prices as $option_name => $expected_price) {
            $sql = $this->wpdb->prepare(
                "SELECT id, option_name, price FROM {$this->table_name} WHERE option_name = %s AND price != %f",
                $option_name,
                $expected_price
            );
            
            $price_issues = $this->wpdb->get_results($sql);
            
            if (!empty($price_issues)) {
                foreach ($price_issues as $issue) {
                    $issues[] = array(
                        'id' => $issue->id,
                        'option_name' => $issue->option_name,
                        'current_price' => $issue->price,
                        'expected_price' => $expected_price
                    );
                }
            }
        }
        
        if (!empty($issues)) {
            $this->log("🚨 価格不整合を発見:");
            foreach ($issues as $issue) {
                $this->log("  - ID{$issue['id']} ({$issue['option_name']}): 現在価格=¥{$issue['current_price']}, 期待価格=¥{$issue['expected_price']}");
            }
        } else {
            $this->log("✅ 価格整合性: 正常");
        }
        
        return $issues;
    }
    
    /**
     * 総レコード数チェック
     */
    private function check_record_count() {
        $count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        $expected_count = 9;
        
        if ($count != $expected_count) {
            $this->log("🚨 レコード数異常: 現在{$count}件、期待値{$expected_count}件");
            return array(
                'current_count' => $count,
                'expected_count' => $expected_count,
                'message' => "レコード数が期待値と異なります"
            );
        } else {
            $this->log("✅ レコード数: 正常（{$count}件）");
            return null;
        }
    }
    
    /**
     * 結果レポート生成
     */
    private function generate_report($issues_found) {
        $this->log("\n=== 重複チェック結果サマリー ===");
        
        if (empty($issues_found)) {
            $this->log("🎉 問題なし: wp_monthly_optionsテーブルは正常です");
            echo "✅ 重複チェック完了: 問題は発見されませんでした\n";
        } else {
            $this->log("⚠️ 問題発見: 以下の問題が見つかりました");
            echo "🚨 重複チェック完了: " . count($issues_found) . "種類の問題が発見されました\n";
            
            foreach ($issues_found as $issue_type => $issues) {
                switch ($issue_type) {
                    case 'option_name_duplicates':
                        echo "  - オプション名重複: " . count($issues) . "件\n";
                        break;
                    case 'display_order_duplicates':
                        echo "  - 表示順序重複: " . count($issues) . "件\n";
                        break;
                    case 'discount_target_issues':
                        echo "  - 割引対象フラグ不正: " . count($issues) . "件\n";
                        break;
                    case 'price_issues':
                        echo "  - 価格不整合: " . count($issues) . "件\n";
                        break;
                    case 'record_count_issue':
                        echo "  - レコード数異常: 1件\n";
                        break;
                }
            }
            
            echo "\n詳細はログファイルを確認してください: {$this->log_file}\n";
        }
        
        $this->log("チェック完了日時: " . date('Y-m-d H:i:s'));
        $this->log("=== 重複チェック終了 ===\n");
    }
    
    /**
     * 簡易チェック（重複のみ）
     */
    public function quick_duplicate_check() {
        echo "=== 簡易重複チェック ===\n";
        
        $name_duplicates = $this->check_option_name_duplicates();
        
        $order_duplicates = $this->check_display_order_duplicates();
        
        $total_issues = count($name_duplicates) + count($order_duplicates);
        
        if ($total_issues == 0) {
            echo "✅ 重複なし: データは正常です\n";
        } else {
            echo "🚨 重複発見: {$total_issues}件の重複があります\n";
        }
        
        return $total_issues == 0;
    }
    
    /**
     * 自動修復（重複削除）
     * 
     * 注意: 本番環境では慎重に使用してください
     */
    public function auto_fix_duplicates($dry_run = true) {
        $this->log("=== 自動修復開始 ===");
        $this->log("ドライラン: " . ($dry_run ? "有効" : "無効"));
        
        if (!$dry_run) {
            $this->log("⚠️ 警告: 実際のデータ修正を実行します");
        }
        
        $name_duplicates = $this->check_option_name_duplicates();
        $fixed_count = 0;
        
        foreach ($name_duplicates as $duplicate) {
            $ids = explode(',', $duplicate->all_ids);
            $keep_id = min($ids); // 最小IDを保持
            $delete_ids = array_filter($ids, function($id) use ($keep_id) {
                return $id != $keep_id;
            });
            
            foreach ($delete_ids as $delete_id) {
                $sql = "DELETE FROM {$this->table_name} WHERE id = %d";
                
                if ($dry_run) {
                    $this->log("ドライラン: " . $this->wpdb->prepare($sql, $delete_id));
                } else {
                    $result = $this->wpdb->query($this->wpdb->prepare($sql, $delete_id));
                    if ($result !== false) {
                        $this->log("削除完了: ID {$delete_id} ({$duplicate->option_name})");
                        $fixed_count++;
                    } else {
                        $this->log("削除失敗: ID {$delete_id}");
                    }
                }
            }
        }
        
        if ($dry_run) {
            $this->log("ドライラン完了: {$fixed_count}件の重複が修正対象です");
        } else {
            $this->log("自動修復完了: {$fixed_count}件の重複を削除しました");
        }
        
        return $fixed_count;
    }
    
    /**
     * ログ出力
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] {$message}\n";
        
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
        
        if (php_sapi_name() === 'cli') {
            echo $log_entry;
        }
    }
    
    /**
     * ログファイルクリア
     */
    public function clear_log() {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
    }
}

/**
 * CLI実行時のメイン処理
 */
if (php_sapi_name() === 'cli') {
    echo "wp_monthly_options 重複検知スクリプト\n";
    echo "=====================================\n\n";
    
    $checker = new MonthlyOptionsDuplicateChecker();
    
    $command = isset($argv[1]) ? $argv[1] : 'check';
    
    switch ($command) {
        case 'check':
        case 'full':
            echo "包括的チェックを実行中...\n\n";
            $issues = $checker->run_comprehensive_check();
            break;
            
        case 'quick':
            echo "簡易チェックを実行中...\n\n";
            $is_clean = $checker->quick_duplicate_check();
            break;
            
        case 'fix':
            echo "自動修復を実行中（ドライラン）...\n\n";
            $fixed = $checker->auto_fix_duplicates(true);
            echo "\n実際に修復するには 'fix-real' を使用してください\n";
            break;
            
        case 'fix-real':
            echo "⚠️ 実際のデータ修復を実行します。続行しますか？ (y/N): ";
            $handle = fopen("php://stdin", "r");
            $line = fgets($handle);
            fclose($handle);
            
            if (trim($line) === 'y' || trim($line) === 'Y') {
                echo "自動修復を実行中...\n\n";
                $fixed = $checker->auto_fix_duplicates(false);
            } else {
                echo "キャンセルしました\n";
            }
            break;
            
        case 'clear-log':
            $checker->clear_log();
            echo "ログファイルをクリアしました\n";
            break;
            
        default:
            echo "使用方法:\n";
            echo "  php check_duplicates.php [command]\n\n";
            echo "コマンド:\n";
            echo "  check      包括的チェック（デフォルト）\n";
            echo "  quick      簡易チェック（重複のみ）\n";
            echo "  fix        自動修復（ドライラン）\n";
            echo "  fix-real   自動修復（実行）\n";
            echo "  clear-log  ログファイルクリア\n";
            break;
    }
    
    echo "\n実行完了\n";
}

/**
 * WordPress管理画面での使用例
 */
function monthly_options_duplicate_check_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die('権限がありません');
    }
    
    $checker = new MonthlyOptionsDuplicateChecker();
    
    if (isset($_POST['run_check'])) {
        echo '<div class="notice notice-info"><p>重複チェックを実行中...</p></div>';
        $issues = $checker->run_comprehensive_check();
        
        if (empty($issues)) {
            echo '<div class="notice notice-success"><p>✅ 問題は発見されませんでした</p></div>';
        } else {
            echo '<div class="notice notice-warning"><p>🚨 ' . count($issues) . '種類の問題が発見されました</p></div>';
        }
    }
    
    echo '<div class="wrap">';
    echo '<h1>wp_monthly_options 重複チェック</h1>';
    echo '<form method="post">';
    echo '<input type="submit" name="run_check" class="button-primary" value="重複チェック実行" />';
    echo '</form>';
    echo '</div>';
}
?>
