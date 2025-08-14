<?php
if (!defined('ABSPATH')) {
    exit;
}
if (defined('WP_CLI') && WP_CLI) {
    class MB_DB_CLI {
        public function migrate($args, $assoc_args) {
            require_once dirname(__DIR__) . '/migrations/2025_08_add_reservation_indexes.php';
            $msg = MB_Migration_AddReservationIndexes::run();
            \WP_CLI::success($msg);
        }
    }
    \WP_CLI::add_command('mb', 'MB_DB_CLI');
}
