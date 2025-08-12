<?php
if (!defined('ABSPATH')) {
    exit;
}

class MB_Seeder {
    public static function run($rooms = 3, $reservations = 6) {
        global $wpdb;
        $log = [];
        $rooms_table = $wpdb->prefix . 'monthly_rooms';
        $res_table = $wpdb->prefix . 'monthly_reservations';

        $using_sqlite = !($wpdb->dbh instanceof mysqli);

        if ($using_sqlite) {
            $wpdb->query('BEGIN');
        } else {
            $wpdb->query('START TRANSACTION');
        }

        try {
            $room_names = ['A', 'B', 'C'];
            $room_ids = [];

            for ($i = 0; $i < min(3, $rooms); $i++) {
                $name = $room_names[$i];
                $existing_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$rooms_table} WHERE display_name = %s LIMIT 1", $name));
                if ($existing_id) {
                    $room_ids[$name] = (int)$existing_id;
                    $log[] = "room exists: {$name} id={$existing_id}";
                } else {
                    $wpdb->insert(
                        $rooms_table,
                        [
                            'room_name' => $name . '01',
                            'display_name' => $name,
                            'is_active' => 1,
                            'room_id' => null,
                        ],
                        ['%s', '%s', '%d', '%d']
                    );
                    $rid = (int)$wpdb->insert_id;
                    $wpdb->query($wpdb->prepare("UPDATE {$rooms_table} SET room_id = id WHERE id = %d", $rid));
                    $room_ids[$name] = $rid;
                    $log[] = "room created: {$name} id={$rid}";
                }
            }

            require_once __DIR__ . '/reservation-service.php';
            $service = new MonthlyBooking_Reservation_Service();

            $seed_items = [
                ['room' => 'A', 'in' => '2025-09-01', 'out' => '2025-09-15', 'guest' => 'Seed Alpha', 'email' => 'alpha@example.com'],
                ['room' => 'A', 'in' => '2025-09-10', 'out' => '2025-09-20', 'guest' => 'Seed Conflict', 'email' => 'conflict@example.com'],
                ['room' => 'B', 'in' => '2025-09-01', 'out' => '2025-10-01', 'guest' => 'Seed Beta', 'email' => 'beta@example.com'],
                ['room' => 'C', 'in' => '2025-09-05', 'out' => '2025-09-25', 'guest' => 'Seed Gamma', 'email' => 'gamma@example.com'],
                ['room' => 'A', 'in' => '2025-10-01', 'out' => '2025-10-31', 'guest' => 'Seed Delta', 'email' => 'delta@example.com'],
                ['room' => 'B', 'in' => '2025-10-15', 'out' => '2025-11-15', 'guest' => 'Seed Epsilon', 'email' => 'eps@example.com'],
            ];

            $created = 0;
            foreach ($seed_items as $idx => $item) {
                if (!isset($room_ids[$item['room']])) {
                    $log[] = "skip: room {$item['room']} not present";
                    continue;
                }
                $room_id = $room_ids[$item['room']];

                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$res_table} WHERE room_id=%d AND checkin_date=%s AND checkout_date=%s LIMIT 1",
                    $room_id, $item['in'], $item['out']
                ));
                if ($exists) {
                    $log[] = "reservation exists: room={$item['room']} {$item['in']}→{$item['out']} id={$exists}";
                    continue;
                }

                $data = [
                    'room_id' => $room_id,
                    'customer_name' => $item['guest'],
                    'customer_email' => $item['email'],
                    'checkin_date' => $item['in'],
                    'checkout_date' => $item['out'],
                    'status' => 'confirmed',
                    'notes' => 'seed',
                    'adults' => 1,
                    'children' => 0,
                ];

                $result = $service->create_reservation($data);
                if (is_wp_error($result)) {
                    $log[] = "create failed: room={$item['room']} {$item['in']}→{$item['out']} reason=" . $result->get_error_message();
                } else {
                    $created++;
                    $log[] = "created: id={$result} room={$item['room']} {$item['in']}→{$item['out']}";
                }
            }

            $wpdb->query('COMMIT');
            $log[] = "seed summary: rooms=" . count($room_ids) . " created_reservations={$created}";
        } catch (Throwable $e) {
            $wpdb->query('ROLLBACK');
            $log[] = "error: " . $e->getMessage();
        }

        return $log;
    }
}
