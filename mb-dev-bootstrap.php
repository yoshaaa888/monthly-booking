<?php
/**
 * Dev bootstrap: 初回だけ
 * - パーマリンクを /%postname%/ に設定 & flush
 * - /monthly-estimate/ ページを [monthly_booking_estimate] で作成
 * - rooms テーブルを用意して最小データ投入
 */
add_action('init', function () {
  // 1) パーマリンク
  if (get_option('permalink_structure') !== '/%postname%/') {
    update_option('permalink_structure', '/%postname%/');
    if (function_exists('flush_rewrite_rules')) flush_rewrite_rules(false);
  }

  // 2) ページ作成
  if (!get_page_by_path('monthly-estimate', OBJECT, 'page')) {
    wp_insert_post([
      'post_title'   => 'Monthly Estimate',
      'post_name'    => 'monthly-estimate',
      'post_status'  => 'publish',
      'post_type'    => 'page',
      'post_content' => '[monthly_booking_estimate]',
    ]);
  }

  // 3) rooms テーブル & データ
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
    $wpdb->query("
      INSERT INTO {$t} (room_id, display_name, room_name, property_name, daily_rent, is_active) VALUES
      (1,'立川マンスリー101号室','101','立川',5000,1),
      (2,'新宿レジデンス205号室','205','新宿',7000,1),
      (3,'渋谷アパートメント302号室','302','渋谷',8000,1),
      (4,'池袋ハイツ403号室','403','池袋',6500,1)
    ");
  }
}, 20);
