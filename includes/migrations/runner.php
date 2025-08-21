<?php
if (!defined('ABSPATH')) {
    exit;
}
class MB_Migrations_Runner {
    private static function dir() {
        return plugin_dir_path(__FILE__) . '';
    }
    private static function migrations_dir() {
        return dirname(self::dir()) . '/../migrations';
    }
    private static function get_applied() {
        $applied = get_option('monthly_booking_applied_migrations', '[]');
        $arr = json_decode($applied, true);
        return is_array($arr) ? $arr : array();
    }
    private static function save_applied($arr) {
        update_option('monthly_booking_applied_migrations', wp_json_encode(array_values(array_unique($arr))));
    }
    private static function list_up_files() {
        $dir = self::migrations_dir();
        if (!is_dir($dir)) return array();
        $files = glob($dir . '/*_up.sql');
        sort($files, SORT_STRING);
        return $files ?: array();
    }
    private static function list_down_files() {
        $dir = self::migrations_dir();
        if (!is_dir($dir)) return array();
        $files = glob($dir . '/*_down.sql');
        sort($files, SORT_STRING);
        return $files ?: array();
    }
    private static function file_key($path) {
        return basename($path);
    }
    private static function substitute_prefix($sql) {
        global $wpdb;
        return str_replace('__PREFIX__', $wpdb->prefix, $sql);
    }
    private static function split_sql($sql) {
        $statements = array();
        $buffer = '';
        $in_string = false;
        $string_char = '';
        $len = strlen($sql);
        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];
            if ($in_string) {
                if ($ch === $string_char) {
                    $in_string = false;
                    $string_char = '';
                } elseif ($ch === '\\' && $i + 1 < $len) {
                    $buffer .= $ch;
                    $i++;
                    $buffer .= $sql[$i];
                    continue;
                }
                $buffer .= $ch;
                continue;
            } else {
                if ($ch === '\'' || $ch === '"') {
                    $in_string = true;
                    $string_char = $ch;
                    $buffer .= $ch;
                    continue;
                }
                if ($ch === ';') {
                    $trim = trim($buffer);
                    if ($trim !== '') $statements[] = $trim;
                    $buffer = '';
                    continue;
                }
                $buffer .= $ch;
            }
        }
        $trim = trim($buffer);
        if ($trim !== '') $statements[] = $trim;
        return $statements;
    }
    public static function runUpAll($dry_run = false) {
        global $wpdb;
        delete_option('monthly_booking_last_migration_error');
        $applied = self::get_applied();
        $files = self::list_up_files();
        $ran = array();
        foreach ($files as $f) {
            $key = self::file_key($f);
            if (in_array($key, $applied, true)) continue;
            $sql = file_get_contents($f);
            if ($sql === false) continue;
            $sql = self::substitute_prefix($sql);
            $stmts = self::split_sql($sql);
            if ($dry_run) {
                $ran[] = $key;
                continue;
            }
            $ok = true;
            foreach ($stmts as $stmt) {
                $r = $wpdb->query($stmt);
                if ($r === false) {
                    $ok = false;
                    $last_error = $wpdb->last_error ? $wpdb->last_error : 'unknown_error';
                    update_option('monthly_booking_last_migration_error', $last_error . ' while executing: ' . $stmt);
                    return 'error:' . $last_error;
                }
            }
            if ($ok) {
                $applied[] = $key;
                $ran[] = $key;
                self::save_applied($applied);
            }
        }
        return 'applied=' . implode(',', $ran);
    }
    public static function runDown($name_without_suffix, $dry_run = false) {
        global $wpdb;
        $dir = self::migrations_dir();
        $pattern = $dir . '/' . $name_without_suffix . '_down.sql';
        $matches = glob($pattern);
        if (!$matches) return 'not_found';
        $file = $matches[0];
        $sql = file_get_contents($file);
        if ($sql === false) return 'read_error';
        $sql = self::substitute_prefix($sql);
        $stmts = self::split_sql($sql);
        if ($dry_run) return 'ok';
        foreach ($stmts as $stmt) {
            $r = $wpdb->query($stmt);
            if ($r === false) {
                $last_error = $wpdb->last_error ? $wpdb->last_error : 'unknown_error';
                return 'error:' . $last_error;
            }
        }
        return 'ok';
    }
}
