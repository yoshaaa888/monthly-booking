<?php
/*
Plugin Name: MB Test REST
Description: Test-only REST endpoints (nonces + simple migration) for e2e.
*/
add_action('rest_api_init', function() {
  register_rest_route('mb-test/v1', '/nonces', array(
    'methods'  => 'GET',
    'permission_callback' => '__return_true',
    'callback' => function() {
      return array(
        'monthly_booking_nonce'  => wp_create_nonce('monthly_booking'),
        'monthly_booking_admin'  => wp_create_nonce('monthly_booking_admin'),
      );
    },
  ));
  register_rest_route('mb-test/v1', '/campaigns-migrate-name', array(
    'methods'  => 'POST',
    'permission_callback' => '__return_true',
    'callback' => function() {
      global $wpdb;
      $table = $wpdb->prefix . 'monthly_campaigns';
      $cols  = $wpdb->get_results("PRAGMA table_info($table)");
      $names = array();
      foreach ($cols as $c) { $names[] = isset($c->name) ? $c->name : (is_array($c)&&isset($c['name'])?$c['name']:null); }
      if (!in_array('name', $names, true)) {
        $wpdb->query("ALTER TABLE $table ADD COLUMN name TEXT NULL");
      }
      $wpdb->query("UPDATE $table SET name = COALESCE(NULLIF(name,''), COALESCE(campaign_name,''))");
      return array('ok' => true, 'last_error' => $wpdb->last_error);
    },
  ));
});
