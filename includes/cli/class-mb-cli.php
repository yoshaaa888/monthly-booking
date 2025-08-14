<?php
if (!defined('ABSPATH')) {
    exit;
}
if (defined('WP_CLI') && WP_CLI) {
    class MB_CLI {
        public function backfill_room_id($args, $assoc_args) {
            global $wpdb;
            $rooms = $wpdb->prefix . 'monthly_rooms';
            $affected = $wpdb->query("UPDATE {$rooms} SET room_id = id WHERE room_id IS NULL OR room_id = 0");
            if ($affected === false) {
                \WP_CLI::error('backfill_room_id failed');
            }
            \WP_CLI::success("backfill_room_id affected={$affected}");
        }

        public function seed($args, $assoc_args) {
            global $wpdb;
            $rooms_t = $wpdb->prefix . 'monthly_rooms';
            $res_t = $wpdb->prefix . 'monthly_reservations';

            $rooms = [
                ['room_name' => 'A', 'display_name' => 'A'],
                ['room_name' => 'B', 'display_name' => 'B'],
                ['room_name' => 'C', 'display_name' => 'C'],
            ];
            $room_ids = [];
            foreach ($rooms as $r) {
                $id = (int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$rooms_t} WHERE room_name=%s LIMIT 1", $r['room_name']));
                if (!$id) {
                    $ins = $wpdb->insert($rooms_t, [
                        'room_name' => $r['room_name'],
                        'display_name' => $r['display_name'],
                        'is_active' => 1,
                    ], ['%s','%s','%d']);
                    if ($ins !== false) {
                        $id = (int)$wpdb->insert_id;
                        $wpdb->query($wpdb->prepare("UPDATE {$rooms_t} SET room_id = id WHERE id=%d", $id));
                    }
                }
                if ($id) {
                    $room_ids[$r['room_name']] = $id;
                }
            }

            $seed = [
                ['R' => 'A', 'ci' => '2025-09-01', 'co' => '2025-09-15', 'name' => 'Seed A1'],
                ['R' => 'A', 'ci' => '2025-09-10', 'co' => '2025-09-20', 'name' => 'Seed A-conflict'],
                ['R' => 'B', 'ci' => '2025-09-01', 'co' => '2025-10-01', 'name' => 'Seed B1'],
                ['R' => 'C', 'ci' => '2025-09-05', 'co' => '2025-09-25', 'name' => 'Seed C1'],
                ['R' => 'A', 'ci' => '2025-10-01', 'co' => '2025-10-31', 'name' => 'Seed A2'],
                ['R' => 'B', 'ci' => '2025-10-15', 'co' => '2025-11-15', 'name' => 'Seed B2'],
            ];
            $inserted = 0;
            $skipped = 0;

            foreach ($seed as $s) {
                if (empty($room_ids[$s['R']])) {
                    continue;
                }
                $rid = (int)$room_ids[$s['R']];
                $exists = (int)$wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$res_t} WHERE room_id=%d AND checkin_date=%s AND checkout_date=%s AND notes='seed' LIMIT 1",
                    $rid, $s['ci'], $s['co']
                ));
                if ($exists) {
                    $skipped++;
                    continue;
                }
                $ins = $wpdb->insert($res_t, [
                    'room_id' => $rid,
                    'checkin_date' => $s['ci'],
                    'checkout_date' => $s['co'],
                    'status' => 'confirmed',
                    'guest_name' => $s['name'],
                    'guest_email' => 'seed@example.com',
                    'notes' => 'seed',
                ], ['%d','%s','%s','%s','%s','%s','%s']);
                if ($ins !== false) {
                    $inserted++;
                }
            }

            \WP_CLI::success("seed rooms=3 inserted={$inserted} skipped={$skipped}");
        }
    }
    \WP_CLI::add_command('mb', 'MB_CLI');
}
