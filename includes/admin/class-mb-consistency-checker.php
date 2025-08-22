<?php
























if (!defined('ABSPATH')) {
    exit;
}

class MB_Consistency_Checker {
    private $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function check_campaign_tables() {
        $prefix = $this->wpdb->prefix;
        $rows = array();

        $tbl_booking = $prefix . 'monthly_booking_campaigns';
        $tbl_campaigns = $prefix . 'monthly_campaigns';

        $exists_booking = $this->table_exists($tbl_booking);
        $exists_campaigns = $this->table_exists($tbl_campaigns);

        $cnt_booking = $exists_booking ? $this->get_count($tbl_booking) : null;
        $cnt_campaigns = $exists_campaigns ? $this->get_count($tbl_campaigns) : null;

        $cols_booking = $exists_booking ? $this->get_columns($tbl_booking) : array();
        $cols_campaigns = $exists_campaigns ? $this->get_columns($tbl_campaigns) : array();

        $rows[] = array(
            'category' => 'campaign_table',
            'location' => $tbl_booking,
            'current_value' => json_encode(array('exists' => $exists_booking, 'count' => $cnt_booking, 'columns' => $cols_booking)),
            'expected_value' => json_encode(array('exists' => true, 'columns_example' => array('name','discount_type','discount_value','start_date','end_date','is_active'))),
            'severity' => $exists_booking && $exists_campaigns ? '高' : ($exists_campaigns ? '中' : '中'),
            'note' => 'booking-logic.php はこのテーブルを参照（要統一検討）'
        );

        $rows[] = array(
            'category' => 'campaign_table',
            'location' => $tbl_campaigns,
            'current_value' => json_encode(array('exists' => $exists_campaigns, 'count' => $cnt_campaigns, 'columns' => $cols_campaigns)),
            'expected_value' => json_encode(array('exists' => true, 'columns_example' => array('name','discount_type','discount_value','start_date','end_date','is_active'))),
            'severity' => $exists_campaigns ? '中' : '高',
            'note' => $exists_campaigns ? 'admin-ui.php / campaign-manager.php はこのテーブルを参照' : '管理画面のキャンペーン機能が動作しない可能性'
        );

        return $rows;
    }

    public function check_pricing_sources() {
        $prefix = $this->wpdb->prefix;
        $rooms_tbl = $prefix . 'monthly_rooms';
        $rates_tbl = $prefix . 'monthly_rates';

        $rows = array();

        $rooms = $this->wpdb->get_results("SELECT id, room_id, display_name, daily_rent, is_active FROM $rooms_tbl ORDER BY id ASC LIMIT 200");
        foreach ($rooms as $r) {
            $rate_counts = $this->wpdb->get_row($this->wpdb->prepare(
                "SELECT 
                    SUM(CASE WHEN rate_type='SS' AND is_active=1 THEN 1 ELSE 0 END) AS ss,
                    SUM(CASE WHEN rate_type='S'  AND is_active=1 THEN 1 ELSE 0 END) AS s,
                    SUM(CASE WHEN rate_type='M'  AND is_active=1 THEN 1 ELSE 0 END) AS m,
                    SUM(CASE WHEN rate_type='L'  AND is_active=1 THEN 1 ELSE 0 END) AS l
                 FROM $rates_tbl WHERE room_id = %d", $r->room_id
            ));

            $missing = array();
            foreach (array('SS','S','M','L') as $t) {
                $val = strtolower($t);
                $cnt = isset($rate_counts->$val) ? intval($rate_counts->$val) : 0;
                if ($cnt <= 0) {
                    $missing[] = $t;
                }
            }

            $severity = count($missing) > 0 ? '高' : '中';
            $note = count($missing) > 0 ? ('未定義プラン: ' . implode('/', $missing)) : '全プラン定義あり';
            $rows[] = array(
                'category' => 'pricing_source',
                'location' => 'room_id=' . $r->room_id,
                'current_value' => json_encode(array('daily_rent' => floatval($r->daily_rent), 'active' => intval($r->is_active))),
                'expected_value' => json_encode(array('rates_defined' => array('SS','S','M','L'))),
                'severity' => $severity,
                'note' => $note
            );
        }

        return $rows;
    }

    public function check_options_integrity() {
        $prefix = $this->wpdb->prefix;
        $opt_tbl = $prefix . 'monthly_options';
        $rows = array();

        $dups = $this->wpdb->get_results("
            SELECT display_order, COUNT(*) AS cnt
            FROM $opt_tbl
            GROUP BY display_order
            HAVING COUNT(*) > 1 AND display_order IS NOT NULL
            ORDER BY display_order ASC
        ");
        foreach ($dups as $d) {
            $rows[] = array(
                'category' => 'options',
                'location' => 'display_order=' . intval($d->display_order),
                'current_value' => json_encode(array('count' => intval($d->cnt))),
                'expected_value' => 'unique',
                'severity' => '中',
                'note' => '並び順の重複'
            );
        }

        $negatives = $this->wpdb->get_results("
            SELECT id, option_name, price FROM $opt_tbl
            WHERE price < 0
            ORDER BY id ASC
        ");
        foreach ($negatives as $n) {
            $rows[] = array(
                'category' => 'options',
                'location' => 'id=' . intval($n->id),
                'current_value' => json_encode(array('name' => $n->option_name, 'price' => floatval($n->price))),
                'expected_value' => 'price >= 0',
                'severity' => '中',
                'note' => '価格が負の値'
            );
        }

        $invalid_discount_flag = $this->wpdb->get_results("
            SELECT id, option_name, price, is_discount_target FROM $opt_tbl
            WHERE is_discount_target NOT IN (0,1) OR is_discount_target IS NULL
            ORDER BY id ASC
        ");
        foreach ($invalid_discount_flag as $idf) {
            $rows[] = array(
                'category' => 'options',
                'location' => 'id=' . intval($idf->id),
                'current_value' => json_encode(array('name' => $idf->option_name, 'is_discount_target' => $idf->is_discount_target)),
                'expected_value' => 'is_discount_target in (0,1)',
                'severity' => '中',
                'note' => '割引対象フラグが不正'
            );
        }

        $inactive_with_order = $this->wpdb->get_results("
            SELECT id, option_name, display_order FROM $opt_tbl
            WHERE is_active = 0 AND display_order > 0
            ORDER BY id ASC
        ");
        foreach ($inactive_with_order as $io) {
            $rows[] = array(
                'category' => 'options',
                'location' => 'id=' . intval($io->id),
                'current_value' => json_encode(array('name' => $io->option_name, 'display_order' => intval($io->display_order))),
                'expected_value' => 'inactive => display_order = 0',
                'severity' => '低',
                'note' => '非表示項目の並び順が正規化されていない可能性'
            );
        }

        return $rows;
    }

    public function check_rate_completeness_and_overlaps() {
        $prefix = $this->wpdb->prefix;
        $rates_tbl = $prefix . 'monthly_rates';
        $rows = array();

        $missing_any = $this->wpdb->get_results("
            SELECT r.room_id,
                   SUM(CASE WHEN rate_type='SS' THEN 1 ELSE 0 END) AS ss,
                   SUM(CASE WHEN rate_type='S'  THEN 1 ELSE 0 END) AS s,
                   SUM(CASE WHEN rate_type='M'  THEN 1 ELSE 0 END) AS m,
                   SUM(CASE WHEN rate_type='L'  THEN 1 ELSE 0 END) AS l
            FROM $rates_tbl r
            GROUP BY r.room_id
            HAVING ss=0 OR s=0 OR m=0 OR l=0
            ORDER BY r.room_id ASC
        ");
        foreach ($missing_any as $m) {
            $missing = array();
            if (intval($m->ss) === 0) $missing[] = 'SS';
            if (intval($m->s) === 0) $missing[] = 'S';
            if (intval($m->m) === 0) $missing[] = 'M';
            if (intval($m->l) === 0) $missing[] = 'L';

            $rows[] = array(
                'category' => 'rates_missing',
                'location' => 'room_id=' . intval($m->room_id),
                'current_value' => json_encode(array('missing' => $missing)),
                'expected_value' => json_encode(array('required' => array('SS','S','M','L'))),
                'severity' => '高',
                'note' => '必須プランの未定義'
            );
        }

        $overlaps = $this->wpdb->get_results("
            SELECT a.room_id, a.rate_type, a.id AS id_a, a.valid_from AS a_from, a.valid_to AS a_to,
                   b.id AS id_b, b.valid_from AS b_from, b.valid_to AS b_to
            FROM $rates_tbl a
            JOIN $rates_tbl b
              ON a.room_id = b.room_id
             AND a.rate_type = b.rate_type
             AND a.id < b.id
             AND (a.valid_to IS NULL OR b.valid_from IS NULL OR a.valid_to >= b.valid_from)
             AND (b.valid_to IS NULL OR a.valid_from IS NULL OR b.valid_to >= a.valid_from)
            ORDER BY a.room_id ASC, a.rate_type ASC
            LIMIT 500
        ");
        foreach ($overlaps as $o) {
            $rows[] = array(
                'category' => 'rates_overlap',
                'location' => 'room_id=' . intval($o->room_id) . ',type=' . $o->rate_type,
                'current_value' => json_encode(array('a' => array('id'=>$o->id_a,'from'=>$o->a_from,'to'=>$o->a_to), 'b' => array('id'=>$o->id_b,'from'=>$o->b_from,'to'=>$o->b_to))),
                'expected_value' => 'no overlap',
                'severity' => '高',
                'note' => '期間重複'
            );
        }

        return $rows;
    }

    public function summary_by_severity($rows) {
        $summary = array('高' => 0, '中' => 0, '低' => 0);
        foreach ($rows as $r) {
            $sev = isset($r['severity']) ? $r['severity'] : '中';
            if (!isset($summary[$sev])) {
                $summary[$sev] = 0;
            }
            $summary[$sev]++;
        }
        return $summary;
    }

    public function to_csv($rows) {
        $out = fopen('php://temp', 'r+');
        fputcsv($out, array('category','location','current_value','expected_value','severity','note'));
        foreach ($rows as $r) {
            fputcsv($out, array(
                isset($r['category']) ? $r['category'] : '',
                isset($r['location']) ? $r['location'] : '',
                isset($r['current_value']) ? (is_string($r['current_value']) ? $r['current_value'] : json_encode($r['current_value'], JSON_UNESCAPED_UNICODE)) : '',
                isset($r['expected_value']) ? (is_string($r['expected_value']) ? $r['expected_value'] : json_encode($r['expected_value'], JSON_UNESCAPED_UNICODE)) : '',
                isset($r['severity']) ? $r['severity'] : '',
                isset($r['note']) ? $r['note'] : ''
            ));
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);
        return $csv;
    }

    private function table_exists($table) {
        $like = $this->wpdb->esc_like($table);
        $sql = $this->wpdb->prepare("SHOW TABLES LIKE %s", $like);
        $res = $this->wpdb->get_var($sql);
        return !empty($res);
    }

    private function get_count($table) {
        return intval($this->wpdb->get_var("SELECT COUNT(*) FROM $table"));
    }

    private function get_columns($table) {
        $cols = $this->wpdb->get_results("SHOW COLUMNS FROM $table");
        $names = array();
        foreach ($cols as $c) {
            $names[] = $c->Field;
        }
        return $names;
    }
    private function ensure_audit_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'mb_audit_log';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            scope VARCHAR(20) NOT NULL,
            batch_id VARCHAR(64) NOT NULL,
            payload LONGTEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY scope (scope),
            KEY batch_id (batch_id)
        ) $charset_collate;";
        $wpdb->query($sql);
    }

    private function write_audit($scope, $batch_id, $payload) {
        global $wpdb;
        $this->ensure_audit_table();
        $table = $wpdb->prefix . 'mb_audit_log';
        $wpdb->insert(
            $table,
            array(
                'scope' => $scope,
                'batch_id' => $batch_id,
                'payload' => wp_json_encode($payload, JSON_UNESCAPED_UNICODE),
            ),
            array('%s','%s','%s')
        );
    }
 
    public function fix_missing_rate_plans($room_id) {
        $room_id = intval($room_id);
        if ($room_id <= 0) {
            return false;
        }
        $rooms_tbl = $this->wpdb->prefix . 'monthly_rooms';
        $rates_tbl = $this->wpdb->prefix . 'monthly_rates';
        $room = $this->wpdb->get_row($this->wpdb->prepare("SELECT daily_rent FROM $rooms_tbl WHERE room_id = %d", $room_id));
        if (!$room) {
            return false;
        }

        $batch_id = gmdate('YmdHis') . '-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : wp_rand(1000,9999));
        $before_rows = $this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM $rates_tbl WHERE room_id = %d AND rate_type IN ('SS','S','M','L')", $room_id), ARRAY_A);
        $inserted_ids = array();

        $rate_types = array('SS','S','M','L');
        foreach ($rate_types as $type) {
            $exists = intval($this->wpdb->get_var($this->wpdb->prepare("SELECT COUNT(*) FROM $rates_tbl WHERE room_id = %d AND rate_type = %s AND is_active = 1", $room_id, $type)));
            if ($exists <= 0) {
                $this->wpdb->insert(
                    $rates_tbl,
                    array(
                        'room_id' => $room_id,
                        'rate_type' => $type,
                        'base_price' => floatval($room->daily_rent),
                        'cleaning_fee' => 0,
                        'service_fee' => 0,
                        'valid_from' => gmdate('Y-m-d'),
                        'valid_to' => null,
                        'is_active' => 1
                    ),
                    array('%d','%s','%f','%f','%f','%s','%s','%d')
                );
                $inserted_ids[] = intval($this->wpdb->insert_id);
            }
        }

        $this->write_audit('rates', $batch_id, array(
            'action' => 'fix_missing_rate_plans',
            'room_id' => $room_id,
            'before_rows' => $before_rows,
            'inserted_ids' => $inserted_ids,
        ));
        update_option('mb_last_rates_batch', $batch_id);

        return true;
    }

    public function fix_duplicate_display_orders() {
        $opt_tbl = $this->wpdb->prefix . 'monthly_options';
        $duplicates = $this->wpdb->get_results("
            SELECT display_order, GROUP_CONCAT(id ORDER BY id ASC) AS ids, COUNT(*) AS cnt
            FROM $opt_tbl
            WHERE display_order IS NOT NULL
            GROUP BY display_order
            HAVING COUNT(*) > 1
            ORDER BY display_order ASC
        ");
        if (empty($duplicates)) {
            return 0;
        }

        $batch_id = gmdate('YmdHis') . '-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : wp_rand(1000,9999));
        $affected_map = array();

        foreach ($duplicates as $dup) {
            $ids = explode(',', $dup->ids);
            foreach ($ids as $id) {
                $id = intval($id);
                $old = $this->wpdb->get_row($this->wpdb->prepare("SELECT id, display_order FROM $opt_tbl WHERE id = %d", $id), ARRAY_A);
                if ($old) {
                    $affected_map[] = array('id' => intval($old['id']), 'display_order' => intval($old['display_order']));
                }
            }
        }

        $this->write_audit('options', $batch_id, array(
            'action' => 'fix_duplicate_display_orders',
            'affected' => $affected_map,
        ));
        update_option('mb_last_options_batch', $batch_id);

        $fixed = 0;
        foreach ($duplicates as $dup) {
            $ids = explode(',', $dup->ids);
            for ($i = 1; $i < count($ids); $i++) {
                $new_order = $this->get_next_available_order();
                $this->wpdb->update(
                    $opt_tbl,
                    array('display_order' => $new_order),
                    array('id' => intval($ids[$i])),
                    array('%d'),
                    array('%d')
                );
                $fixed++;
            }
        }
        return $fixed;
    }

    private function get_next_available_order() {
        $opt_tbl = $this->wpdb->prefix . 'monthly_options';
        $max_order = intval($this->wpdb->get_var("SELECT COALESCE(MAX(display_order),0) FROM $opt_tbl"));
        return $max_order + 1;
    }

    public function rollback_rates($batch_id) {
        $batch_id = sanitize_text_field($batch_id);
        if (!$batch_id) return false;
        global $wpdb;
        $table = $wpdb->prefix . 'mb_audit_log';
        $rates_tbl = $wpdb->prefix . 'monthly_rates';
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE scope = 'rates' AND batch_id = %s ORDER BY id DESC LIMIT 1", $batch_id));
        if (!$row) return false;
        $payload = json_decode($row->payload, true);
        if (!is_array($payload)) return false;
        $inserted_ids = isset($payload['inserted_ids']) && is_array($payload['inserted_ids']) ? array_map('intval', $payload['inserted_ids']) : array();
        if (!empty($inserted_ids)) {
            $placeholders = implode(',', array_fill(0, count($inserted_ids), '%d'));
            $wpdb->query($wpdb->prepare("DELETE FROM $rates_tbl WHERE id IN ($placeholders)", $inserted_ids));
        }
        return true;
    }

    public function rollback_options($batch_id) {
        $batch_id = sanitize_text_field($batch_id);
        if (!$batch_id) return false;
        global $wpdb;
        $table = $wpdb->prefix . 'mb_audit_log';
        $opt_tbl = $wpdb->prefix . 'monthly_options';
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE scope = 'options' AND batch_id = %s ORDER BY id DESC LIMIT 1", $batch_id));
        if (!$row) return false;
        $payload = json_decode($row->payload, true);
        if (!is_array($payload) || !isset($payload['affected']) || !is_array($payload['affected'])) return false;
        foreach ($payload['affected'] as $it) {
            $id = intval($it['id']);
            $display_order = intval($it['display_order']);
            $wpdb->update(
                $opt_tbl,
                array('display_order' => $display_order),
                array('id' => $id),
                array('%d'),
                array('%d')
            );
        }
        return true;
    }
}
