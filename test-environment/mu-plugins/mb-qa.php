<?php
/*
Plugin Name: MB QA Utilities
Description: Test-only QA endpoints (REST ping + admin-ajax echo + marker + ajaxurl fix).
*/

add_action('rest_api_init', function () {
  register_rest_route('mb-qa/v1', '/ping', [
    'methods'  => 'GET',
    'permission_callback' => '__return_true',
    'callback' => function () {
      return ['ok' => true, 'time' => time()];
    },
  ]);
});

add_action('wp_ajax_mb_qa_echo', function () {
  wp_send_json_success(['ok' => true, 'who' => (is_user_logged_in() ? 'admin' : 'guest')]);
});
add_action('wp_ajax_nopriv_mb_qa_echo', function () {
  wp_send_json_success(['ok' => true, 'who' => 'guest']);
});

/**
 * Front marker + local https→http の ajaxurl 強制
 * （wp-now/Codespaces で https://127.0.0.1:8888 が失敗する対策）
 */
add_action('wp_footer', function () {
  echo "\n<!-- MB_FIXER_ACTIVE -->\n";
  ?>
  <script>
  (function () {
    try {
      if (window.monthlyBookingAjax && /^https:\/\/127\.0\.0\.1:8888\//.test(monthlyBookingAjax.ajaxurl)) {
        monthlyBookingAjax.ajaxurl = monthlyBookingAjax.ajaxurl.replace(/^https:/, 'http:');
        console.log('[mb-qa] ajaxurl forced to http:', monthlyBookingAjax.ajaxurl);
      }
    } catch (e) {}
  })();
  </script>
  <?php
}, 9999);
