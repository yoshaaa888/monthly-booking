<?php
if (!function_exists('mb_i18n_get_locale')) {
    function mb_i18n_get_locale() {
        if (function_exists('determine_locale')) {
            return determine_locale();
        }
        return get_locale();
    }
}

if (!function_exists('mb_i18n_load_ja')) {
    function mb_i18n_load_ja() {
        static $cache = null;
        if ($cache !== null) return $cache;
        $path = plugin_dir_path(__FILE__) . '../assets/i18n/ja.json';
        if (file_exists($path)) {
            $json = file_get_contents($path);
            $data = json_decode($json, true);
            if (is_array($data)) {
                $cache = $data;
                return $cache;
            }
        }
        $cache = array();
        return $cache;
    }
}

if (!function_exists('mb_t')) {
    function mb_t($key) {
        $locale = mb_i18n_get_locale();
        if (strpos($locale, 'ja') === 0) {
            $dict = mb_i18n_load_ja();
            if (isset($dict[$key]['ja'])) {
                return $dict[$key]['ja'];
            }
        }
        return $key;
    }
}
