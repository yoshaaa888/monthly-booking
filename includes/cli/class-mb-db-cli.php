<?php
if (!defined('ABSPATH')) {
    exit;
}
if (defined('WP_CLI') && WP_CLI) {
    class MB_DB_CLI {
        public function migrate($args, $assoc_args) {
            $down = isset($assoc_args['down']) ? $assoc_args['down'] : null;
            $dry = isset($assoc_args['dry-run']);
            require_once dirname(__DIR__) . '/migrations/runner.php';
            if ($down) {
                $msg = MB_Migrations_Runner::runDown($down, $dry);
            } else {
                $msg = MB_Migrations_Runner::runUpAll($dry);
            }
            \WP_CLI::success($msg);
        }
    }
    \WP_CLI::add_command('mb', 'MB_DB_CLI');
}
