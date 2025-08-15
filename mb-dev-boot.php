<?php
/* Dev bootstrap (loaded from monthly-booking.php)
 * - Force home/siteurl => http://127.0.0.1:8888
 * - Disable canonical redirects
 * - Create /monthly-estimate/ page with [monthly_booking_estimate]
 * - Create rooms table & seed
 * - Diagnostics: ?mb_dev=1
 */

/* すぐ効かせたい系はトップレベルで（init待ちにしない） */
add_filter('pre_option_home',    fn() => 'http://127.0.0.1:8888', 1);
add_filter('pre_option_siteurl', fn() => 'http://127.0.0.1:8888', 1);
add_filter('redirect_canonical', '__return_false', 100, 2);

add_action('init', function () {
  // パーマリンク
  if (get_option('permalink_structure') !== '/%postname%/') {
    update_option('permalink_structure', '/%postname%/');
    if (function_exists('flush_rewrite_rules')) flush_rewrite_rules(false);
  }

  // /monthly-estimate/ ページ
  if (!get_page_by_path('monthly-estimate', OBJECT, 'page')) {
    wp_insert_post([
      'post_title'   => 'Monthly Estimate',
      'post_name'    => 'monthly-estimate',
      'post_status'  => 'publish',
      'post_type'    => 'page',
      'post_content' => '[monthly_booking_estimate]',
    ]);
  }

  // rooms テーブル & 最小データ
  global $wpdb;
  require_once ABSPATH . 'wp-admin/includes/upgrade.php';
  $t = $wpdb->prefix . 'monthly_rooms';
  dbDelta("CREATE TABLE {$t} (
    room_id INT(11) NOT NULL,
    display_name VARCHAR(191),
    room_name VARCHAR(191),
    property_name VARCHAR(191),
    daily_rent INT(11),
    is_active TINYINT(1),
    PRIMARY KEY (room_id)
  ) DEFAULT CHARSET=utf8mb4;");

  $cnt = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$t}");
  if ($cnt === 0) {
    $wpdb->query("INSERT INTO {$t}
      (room_id, display_name, room_name, property_name, daily_rent, is_active)
      VALUES (1,'立川マンスリー101号室','101','立川',5000,1)");
  }
}, 5);

/* 診断 */
add_action('init', function () {
  if (!isset($_GET['mb_dev'])) return;
  header('Content-Type: text/plain; charset=utf-8');
  echo "dev=ok\n";
  echo "home=" . home_url('/') . "\n";
  echo "siteurl=" . site_url('/') . "\n";
  echo "permalink=" . get_option('permalink_structure') . "\n";
  echo "shortcode=" . (shortcode_exists('monthly_booking_estimate') ? 'yes' : 'no') . "\n";
  global $wpdb; $t = $wpdb->prefix.'monthly_rooms';
  $cnt = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$t}");
  echo "rooms_count={$cnt}\n";
  exit;
});
