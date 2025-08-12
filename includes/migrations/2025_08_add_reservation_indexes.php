<?php
if (!defined('ABSPATH')) {
    exit;
}
class MB_Migration_AddReservationIndexes {
    public static function run() {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $table = $prefix . 'monthly_reservations';
        $idx_in = 'idx_mbp_res_room_in';
        $idx_out = 'idx_mbp_res_room_out';
        $created = [];
        $skipped = [];

        $is_mysql = (isset($wpdb->use_mysqli) && $wpdb->use_mysqli) || (isset($wpdb->dbh) && $wpdb->dbh instanceof mysqli);

        if ($is_mysql) {
            $exists_in = (int)$wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = %s AND index_name = %s",
                $table, $idx_in
            ));
            if (!$exists_in) {
                $r = $wpdb->query("CREATE INDEX {$idx_in} ON {$table} (room_id, checkin_date)");
                if ($r !== false) $created[] = $idx_in; else $skipped[] = $idx_in;
            } else {
                $skipped[] = $idx_in;
            }
            $exists_out = (int)$wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = %s AND index_name = %s",
                $table, $idx_out
            ));
            if (!$exists_out) {
                $r = $wpdb->query("CREATE INDEX {$idx_out} ON {$table} (room_id, checkout_date)");
                if ($r !== false) $created[] = $idx_out; else $skipped[] = $idx_out;
            } else {
                $skipped[] = $idx_out;
            }
        } else {
            $wpdb->query("CREATE INDEX IF NOT EXISTS {$idx_in} ON {$table} (room_id, checkin_date)");
            $wpdb->query("CREATE INDEX IF NOT EXISTS {$idx_out} ON {$table} (room_id, checkout_date)");
            $skipped[] = $idx_in;
            $skipped[] = $idx_out;
        }

        return 'created=[' . implode(',', $created) . '] skipped=[' . implode(',', $skipped) . ']';
    }
}
