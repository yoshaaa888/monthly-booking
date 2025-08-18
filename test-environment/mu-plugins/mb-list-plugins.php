<?php
/**
 * Plugin Name: MB List Plugins (MU)
 * Description: List installed & active plugins via REST for QA.
 */

add_action('rest_api_init', function () {
  register_rest_route('mb-qa/v1', '/plugins', [
    'methods'  => 'GET',
    'callback' => function () {
      // インストール済み一覧
      $installed = [];
      if (defined('WP_PLUGIN_DIR') && is_dir(WP_PLUGIN_DIR)) {
        foreach (scandir(WP_PLUGIN_DIR) as $f) {
          if ($f === '.' || $f === '..') continue;
          $installed[] = $f;
        }
      }
      // MU プラグイン一覧
      $mu_installed = [];
      if (defined('WPMU_PLUGIN_DIR') && is_dir(WPMU_PLUGIN_DIR)) {
        foreach (scandir(WPMU_PLUGIN_DIR) as $f) {
          if ($f === '.' || $f === '..') continue;
          $mu_installed[] = $f;
        }
      }
      // 有効化済み（通常プラグイン）
      $active = get_option('active_plugins', []);

      return [
        'installed'    => array_values($installed),
        'mu_installed' => array_values($mu_installed),
        'active'       => array_values($active),
      ];
    },
    'permission_callback' => '__return_true',
  ]);
});
