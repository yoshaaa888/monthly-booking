<?php
require '/var/www/html/wp-load.php';
global $wpdb;
$prefix = $wpdb->prefix;

$wpdb->query("
CREATE TABLE IF NOT EXISTS {$prefix}monthly_campaigns (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  campaign_name varchar(191) NOT NULL,
  campaign_description text,
  discount_type varchar(32) NOT NULL,
  discount_value decimal(10,2) NOT NULL DEFAULT 0,
  is_active tinyint(1) NOT NULL DEFAULT 1,
  start_date date NOT NULL,
  end_date date NOT NULL,
  created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8mb4;
");

$count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}monthly_campaigns");
if ($count < 6) {
    $today = new DateTime();
    $start = $today->format('Y-m-d');
    $end = $today->modify('+60 days')->format('Y-m-d');

    $rows = array(
        array('即入居割 20%', 'last_minute campaign', 'percentage', 20),
        array('早割 10%', 'early campaign', 'percentage', 10),
        array('フラット 3000', 'flat rate', 'fixed', 3000),
        array('早割 15%', 'early campaign high', 'percentage', 15),
        array('即入居割 10%', 'last_minute small', 'percentage', 10),
        array('フラット 5000', 'flat rate high', 'fixed', 5000),
    );

    foreach ($rows as $r) {
        $wpdb->insert(
            "{$prefix}monthly_campaigns",
            array(
                'campaign_name' => $r[0],
                'campaign_description' => $r[1],
                'discount_type' => $r[2],
                'discount_value' => $r[3],
                'is_active' => 1,
                'start_date' => $start,
                'end_date' => $end,
            ),
            array('%s','%s','%s','%f','%d','%s','%s')
        );
    }
}

$res = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}monthly_campaigns");
echo "RES_COUNT=" . $res . PHP_EOL;
