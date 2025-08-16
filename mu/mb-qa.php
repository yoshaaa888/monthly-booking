<?php
/*
Plugin Name: MB QA MU (minimal)
Description: Header, REST ping, footer marker, admin-ajax echo, page bootstrap.
Version: 0.2.3
*/
if (defined('MB_MU_DISABLE') && MB_MU_DISABLE) { return; }

/** 1) 診断ヘッダ */
if (function_exists('header')) { @header('X-MB-OK: global'); }

/** 2) REST: /wp-json/mb-qa/v1/ping -> { ok: true } */
add_action('rest_api_init', function () {
  register_rest_route('mb-qa/v1', '/ping', [
    'methods'  => 'GET',
    'permission_callback' => '__return_true',
    'callback' => function () { return [ 'ok' => true, 'ts' => time() ]; },
  ]);
});

/** 3) admin-ajax: action=mb_qa_echo -> { success: true, data: { pong: <ts> } }
 *   function_exists ガードで二重定義も防止
 */
if (!function_exists('mb_qa_echo_handler')) {
  function mb_qa_echo_handler() {
    error_log('[MB-QA] AJAX action=mb_qa_echo');
    wp_send_json_success([ 'pong' => time() ]);
  }
  add_action('wp_ajax_mb_qa_echo',        'mb_qa_echo_handler');
  add_action('wp_ajax_nopriv_mb_qa_echo', 'mb_qa_echo_handler');
}

/** 4) footer marker */
add_action('wp_footer', function () { echo "\n<!-- MB_FIXER_ACTIVE -->\n"; }, 999);

/** 5) UI: [mb_monthly_estimate] -> ★重複しない ID を使用（mb_room_id） */
add_shortcode('mb_monthly_estimate', function () {
  ob_start(); ?>
  <div id="mb-monthly-estimate">
    <label for="room_id">Room</label>
    <select id="room_id" name="room">
      <option value="101">Room 101</option>
      <option value="102">Room 102</option>
    </select>
    <button id="calculate-estimate-btn" type="button">Calculate</button>
  </div>
  <?php return ob_get_clean();
});

/** 6) 一度だけのブートストラップ（permalink, page作成, flush） */
function mb_mu_bootstrap_once() {
  $ver = '0.2.3';
  if (get_option('mb_mu_bootstrap_version') === $ver) return;

  // パーマリンクが plain の場合のみ /%postname%/ に設定
  $ps = get_option('permalink_structure', '');
  if (empty($ps)) update_option('permalink_structure', '/%postname%/');

  // /monthly-estimate/ を用意（なければ新規。既存があっても内容は壊さない）
  $page = get_page_by_path('monthly-estimate');
  if (!$page) {
    $pid = wp_insert_post([
      'post_title'   => 'Monthly Estimate',
      'post_name'    => 'monthly-estimate',
      'post_type'    => 'page',
      'post_status'  => 'publish',
      'post_content' => '[mb_monthly_estimate]',
    ]);
    if ($pid && !is_wp_error($pid)) update_option('mb_mu_estimate_page_id', (int)$pid);
  }

  flush_rewrite_rules(false);
  update_option('mb_mu_bootstrap_version', $ver);
}
add_action('init', 'mb_mu_bootstrap_once', 20);
