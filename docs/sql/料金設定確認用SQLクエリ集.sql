--
--
--


/******************************
 * 1) 料金設定確認用
 ******************************/

SELECT
  r.id                AS room_pk,
  r.room_id,
  r.property_id,
  r.display_name,
  r.room_name,
  r.mor_g            AS room_type,
  r.daily_rent       AS basic_daily_rent,
  r.max_occupants,
  r.is_active,
  r.updated_at
FROM {{PREFIX}}monthly_rooms r
ORDER BY r.updated_at DESC, r.id DESC
LIMIT 20;

SELECT
  r.room_id,
  r.display_name,
  mr.rate_type,           -- 例: 'SS','S','M','L','monthly' 等の任意分類
  mr.base_price,
  mr.cleaning_fee,
  mr.service_fee,
  mr.tax_rate,
  mr.valid_from,
  mr.valid_to,
  mr.is_active
FROM {{PREFIX}}monthly_rooms r
JOIN {{PREFIX}}monthly_rates mr
  ON mr.room_id = r.room_id
ORDER BY r.room_id ASC, mr.rate_type ASC, mr.valid_from DESC
LIMIT 20;

SELECT
  c.id,
  c.campaign_name,
  c.discount_type,        -- 'percentage' / 'fixed'
  c.discount_value,
  c.min_stay_days,
  c.max_discount_amount,
  c.applicable_rooms,     -- CSV（例: "101,102"）
  c.start_date,
  c.end_date,
  c.booking_start_date,
  c.booking_end_date,
  c.usage_limit,
  c.usage_count,
  c.is_active
FROM {{PREFIX}}monthly_campaigns c
WHERE c.is_active = 1
  AND CURDATE() BETWEEN c.start_date AND c.end_date
ORDER BY c.start_date DESC, c.discount_value DESC
LIMIT 20;


/******************************
 * 2) オプション設定確認用
 ******************************/

SELECT
  o.id            AS option_id,
  o.option_name,
  o.option_description,
  o.price,
  o.is_discount_target,
  o.display_order,
  o.is_active,
  o.updated_at
FROM {{PREFIX}}monthly_options o
ORDER BY o.is_active DESC, o.display_order ASC, o.id ASC
LIMIT 20;

SELECT
  o.id AS option_id,
  o.option_name,
  o.price,
  o.display_order,
  o.is_active
FROM {{PREFIX}}monthly_options o
WHERE o.is_discount_target = 0
ORDER BY o.display_order ASC, o.id ASC
LIMIT 20;

SELECT
  o.display_order,
  COUNT(*)                           AS cnt,
  SUM(CASE WHEN o.is_active=1 THEN 1 ELSE 0 END) AS active_cnt
FROM {{PREFIX}}monthly_options o
GROUP BY o.display_order
ORDER BY o.display_order ASC
LIMIT 20;


/******************************
 * 3) 整合性チェック用
 ******************************/

SELECT
  a.room_id,
  a.rate_type,
  a.id    AS rate_id_a,
  a.valid_from AS a_from, a.valid_to AS a_to,
  b.id    AS rate_id_b,
  b.valid_from AS b_from, b.valid_to AS b_to
FROM {{PREFIX}}monthly_rates a
JOIN {{PREFIX}}monthly_rates b
  ON a.room_id = b.room_id
 AND a.rate_type = b.rate_type
 AND a.id < b.id
 AND (a.valid_to IS NULL OR b.valid_from IS NULL OR a.valid_to >= b.valid_from)
 AND (b.valid_to IS NULL OR a.valid_from IS NULL OR b.valid_to >= a.valid_from)
ORDER BY a.room_id ASC, a.rate_type ASC, a.valid_from DESC
LIMIT 20;

SELECT
  r.room_id,
  r.display_name,
  r.room_name,
  r.daily_rent,
  r.updated_at
FROM {{PREFIX}}monthly_rooms r
LEFT JOIN {{PREFIX}}monthly_rates mr
  ON mr.room_id = r.room_id
WHERE mr.room_id IS NULL
ORDER BY r.updated_at DESC, r.room_id ASC
LIMIT 20;

SELECT
  o.id AS option_id,
  o.option_name,
  o.is_active,
  o.display_order,
  o.price
FROM {{PREFIX}}monthly_options o
WHERE (o.is_active = 0 AND o.display_order > 0)
   OR (o.price < 0)
ORDER BY o.id ASC
LIMIT 20;

SELECT
  c1.id AS campaign_id_1,
  c1.campaign_name AS name_1,
  c1.start_date AS start_1, c1.end_date AS end_1,
  c2.id AS campaign_id_2,
  c2.campaign_name AS name_2,
  c2.start_date AS start_2, c2.end_date AS end_2
FROM {{PREFIX}}monthly_campaigns c1
JOIN {{PREFIX}}monthly_campaigns c2
  ON c1.id < c2.id
 AND c1.is_active = 1 AND c2.is_active = 1
 AND c1.start_date <= c2.end_date
 AND c2.start_date <= c1.end_date
ORDER BY c1.start_date DESC
LIMIT 20;

SELECT
  c.id,
  c.campaign_name,
  c.applicable_rooms
FROM {{PREFIX}}monthly_campaigns c
WHERE c.is_active = 1
  AND FIND_IN_SET('101', REPLACE(REPLACE(c.applicable_rooms, ' ', ''), '　', '')) > 0
ORDER BY c.start_date DESC
LIMIT 20;


SELECT
  c.campaign_name,
  c.applicable_rooms,
  c.start_date, c.end_date
FROM {{PREFIX}}monthly_campaigns c
WHERE c.is_active = 1
  AND c.applicable_rooms IS NOT NULL
  AND c.applicable_rooms <> ''
ORDER BY c.start_date DESC
LIMIT 20;


/******************************
 * 付録: ユーザー変数を使った実行雛形
 ******************************/

SET @p := 'wp_';
SET @tmpl := '
SELECT r.id, r.room_id, r.property_id, r.display_name, r.room_name, r.mor_g, r.daily_rent, r.max_occupants, r.is_active, r.updated_at
FROM {{PREFIX}}monthly_rooms r
ORDER BY r.updated_at DESC, r.id DESC
LIMIT 20
';
SET @sql := REPLACE(@tmpl, '{{PREFIX}}', @p);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
