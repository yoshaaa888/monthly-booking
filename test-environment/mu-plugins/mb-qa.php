<?php
/*
Plugin Name: MB QA Utilities
Description: Test-only QA endpoints (REST ping + admin-ajax echo).
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
  wp_send_json_success(['ok' => true, 'who' => 'admin']);
});
add_action('wp_ajax_nopriv_mb_qa_echo', function () {
  wp_send_json_success(['ok' => true, 'who' => 'guest']);
});
