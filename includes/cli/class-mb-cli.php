<?php
if (!defined('ABSPATH')) {
    exit;
}

if (defined('WP_CLI') && WP_CLI) {
    class MB_CLI_Commands {
        public function backfill_room_id($args, $assoc_args) {
            global $wpdb;
            $table = isset($assoc_args['table']) ? $assoc_args['table'] : $wpdb->prefix . 'monthly_rooms';
            $dry_run = isset($assoc_args['dry-run']) || isset($assoc_args['dryrun']);

            $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE COALESCE(room_id,0)=0");
            if ($dry_run) {
                \WP_CLI::log("DRY-RUN: {$count} rows would be updated in {$table}");
                \WP_CLI::success('Dry-run complete');
                return;
            }

            $updated = $wpdb->query("UPDATE {$table} SET room_id = id WHERE COALESCE(room_id,0)=0");
            \WP_CLI::log("Found {$count} rows with room_id=0");
            \WP_CLI::success("Updated rows: " . (int)$updated);
        }

        public function seed($args, $assoc_args) {
            require_once dirname(__DIR__) . '/seeder.php';
            $rooms = isset($assoc_args['rooms']) ? max(0, intval($assoc_args['rooms'])) : 3;
            $reservations = isset($assoc_args['reservations']) ? max(0, intval($assoc_args['reservations'])) : 6;

            $log = MB_Seeder::run($rooms, $reservations);
            foreach ($log as $line) {
                \WP_CLI::log($line);
            }
            \WP_CLI::success('Seed complete');
        }
    }

    $mb_cli = new MB_CLI_Commands();
    \WP_CLI::add_command('mb backfill-room-id', [$mb_cli, 'backfill_room_id']);
    \WP_CLI::add_command('mb seed', [$mb_cli, 'seed']);
}
