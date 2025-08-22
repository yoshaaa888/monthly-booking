SET NAMES utf8mb4;

SET @ROOMS_TABLE := 'wp_monthly_rooms';
SET @OPTIONS_TABLE := 'wp_monthly_options';
SET @CAMPAIGNS_TABLE := 'wp_monthly_campaigns';
SET @CUSTOMERS_TABLE := 'wp_monthly_customers';
SET @BOOKINGS_TABLE := 'wp_monthly_bookings';
SET @BOOKING_OPTIONS_TABLE := 'wp_monthly_booking_options';

SELECT @ROOMS_TABLE := TABLE_NAME
FROM information_schema.tables
WHERE table_schema = DATABASE() AND TABLE_NAME LIKE '%_monthly_rooms'
LIMIT 1;

SELECT @OPTIONS_TABLE := TABLE_NAME
FROM information_schema.tables
WHERE table_schema = DATABASE() AND TABLE_NAME LIKE '%_monthly_options'
LIMIT 1;

SELECT @CAMPAIGNS_TABLE := TABLE_NAME
FROM information_schema.tables
WHERE table_schema = DATABASE() AND TABLE_NAME LIKE '%_monthly_campaigns'
LIMIT 1;

SELECT @CUSTOMERS_TABLE := TABLE_NAME
FROM information_schema.tables
WHERE table_schema = DATABASE() AND TABLE_NAME LIKE '%_monthly_customers'
LIMIT 1;

SELECT @BOOKINGS_TABLE := TABLE_NAME
FROM information_schema.tables
WHERE table_schema = DATABASE() AND TABLE_NAME LIKE '%_monthly_bookings'
LIMIT 1;

SELECT @BOOKING_OPTIONS_TABLE := TABLE_NAME
FROM information_schema.tables
WHERE table_schema = DATABASE() AND TABLE_NAME LIKE '%_monthly_booking_options'
LIMIT 1;

SET @UNIT_DAY := '日';
SET @UNIT_MONTH := '月';

SELECT @UNIT_DAY := '日'
WHERE EXISTS (
  SELECT 1
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = @ROOMS_TABLE
    AND column_name = 'min_stay_unit'
    AND COLUMN_TYPE LIKE "%'日'%"
);

SELECT @UNIT_MONTH := '月'
WHERE EXISTS (
  SELECT 1
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = @ROOMS_TABLE
    AND column_name = 'min_stay_unit'
    AND COLUMN_TYPE LIKE "%'月'%"
);

SELECT @UNIT_DAY := 'day'
WHERE NOT EXISTS (
  SELECT 1
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = @ROOMS_TABLE
    AND column_name = 'min_stay_unit'
    AND COLUMN_TYPE LIKE "%'日'%"
) AND EXISTS (
  SELECT 1
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = @ROOMS_TABLE
    AND column_name = 'min_stay_unit'
    AND COLUMN_TYPE LIKE "%'day'%"
);

SELECT @UNIT_MONTH := 'month'
WHERE NOT EXISTS (
  SELECT 1
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = @ROOMS_TABLE
    AND column_name = 'min_stay_unit'
    AND COLUMN_TYPE LIKE "%'月'%"
) AND EXISTS (
  SELECT 1
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = @ROOMS_TABLE
    AND column_name = 'min_stay_unit'
    AND COLUMN_TYPE LIKE "%'month'%"
);

SET @sql_trunc := CONCAT(
  'SET FOREIGN_KEY_CHECKS=0;',
  'TRUNCATE TABLE `', @BOOKING_OPTIONS_TABLE, '`;',
  'TRUNCATE TABLE `', @BOOKINGS_TABLE, '`;',
  'TRUNCATE TABLE `', @CUSTOMERS_TABLE, '`;',
  'TRUNCATE TABLE `', @CAMPAIGNS_TABLE, '`;',
  'TRUNCATE TABLE `', @OPTIONS_TABLE, '`;',
  'TRUNCATE TABLE `', @ROOMS_TABLE, '`;',
  'SET FOREIGN_KEY_CHECKS=1;'
);
PREPARE stmt_trunc FROM @sql_trunc; EXECUTE stmt_trunc; DEALLOCATE PREPARE stmt_trunc;

SET @sql_rooms := CONCAT(
  'INSERT INTO `', @ROOMS_TABLE, '` ',
  '(room_id, property_id, mor_g, property_name, display_name, room_name, room_description, min_stay_days, min_stay_unit, max_occupants, address, layout, floor_area, structure, built_year, daily_rent, line1, station1, access1_type, access1_time, line2, station2, access2_type, access2_time, line3, station3, access3_type, access3_time, room_size, room_amenities, room_images, is_active) VALUES ',
  '(1, 1, ''M'', ''立川マンスリー'', ''立川マンスリー101号室'', ''101号室'', ''立川駅徒歩8分の好立地マンスリーマンション。WiFi完備、宅配BOX有り。'', 7, ''', @UNIT_DAY, ''', 2, ''東京都立川市曙町1丁目22-25'', ''マンスリー'', 20.0, ''鉄骨鉄筋コンクリート造'', ''2018年'', 2400, ''JR中央線'', ''立川'', ''徒歩'', 8, ''JR南武線'', ''立川'', ''徒歩'', 8, ''多摩都市モノレール'', ''立川北'', ''徒歩'', 6, 20.00, ''WiFi対応、宅配BOX有、エアコン、冷蔵庫、洗濯機'', '''', 1),',
  '(2, 2, ''M'', ''新宿レジデンス'', ''新宿レジデンス205号室'', ''205号室'', ''新宿駅徒歩5分の都心マンスリー。ビジネス利用に最適。'', 7, ''', @UNIT_DAY, ''', 2, ''東京都新宿区西新宿1丁目5-10'', ''マンスリー'', 25.0, ''鉄筋コンクリート造'', ''2020年'', 3200, ''JR山手線'', ''新宿'', ''徒歩'', 5, ''JR中央線'', ''新宿'', ''徒歩'', 5, ''都営大江戸線'', ''新宿'', ''徒歩'', 3, 25.00, ''WiFi対応、オートロック、宅配BOX有、エアコン、冷蔵庫、洗濯機、電子レンジ'', '''', 1),',
  '(3, 3, ''M'', ''渋谷アパートメント'', ''渋谷アパートメント302号室'', ''302号室'', ''渋谷駅徒歩10分のスタイリッシュなマンスリー。若者に人気のエリア。'', 7, ''', @UNIT_DAY, ''', 2, ''東京都渋谷区道玄坂2丁目15-8'', ''マンスリー'', 22.0, ''鉄筋コンクリート造'', ''2019年'', 2800, ''JR山手線'', ''渋谷'', ''徒歩'', 10, ''JR埼京線'', ''渋谷'', ''徒歩'', 10, ''東急東横線'', ''渋谷'', ''徒歩'', 8, 22.00, ''WiFi対応、オートロック、エアコン、冷蔵庫、洗濯機'', '''', 1),',
  '(4, 4, ''M'', ''池袋ハイツ'', ''池袋ハイツ403号室'', ''403号室'', ''池袋駅徒歩7分の便利な立地。ショッピングにも便利。'', 7, ''', @UNIT_DAY, ''', 3, ''東京都豊島区南池袋2丁目12-5'', ''マンスリー'', 28.0, ''鉄筋コンクリート造'', ''2017年'', 2600, ''JR山手線'', ''池袋'', ''徒歩'', 7, ''JR埼京線'', ''池袋'', ''徒歩'', 7, ''東武東上線'', ''池袋'', ''徒歩'', 5, 28.00, ''WiFi対応、宅配BOX有、エアコン、冷蔵庫、洗濯機、電子レンジ、IHコンロ'', '''', 1),',
  '(5, 5, ''M'', ''品川タワー'', ''品川タワー1205号室'', ''1205号室'', ''品川駅徒歩3分の高層マンション。ビジネス・観光に最適。'', 7, ''', @UNIT_DAY, ''', 2, ''東京都港区港南2丁目8-15'', ''マンスリー'', 30.0, ''鉄筋コンクリート造'', ''2021年'', 3800, ''JR山手線'', ''品川'', ''徒歩'', 3, ''JR東海道線'', ''品川'', ''徒歩'', 3, ''京急本線'', ''品川'', ''徒歩'', 2, 30.00, ''WiFi対応、オートロック、宅配BOX有、エアコン、冷蔵庫、洗濯機、電子レンジ、IHコンロ、食洗機'', '''', 1);'
);
PREPARE stmt_rooms FROM @sql_rooms; EXECUTE stmt_rooms; DEALLOCATE PREPARE stmt_rooms;

SET @sql_options := CONCAT(
  'INSERT INTO `', @OPTIONS_TABLE, '` ',
  '(option_name, option_description, price, is_discount_target, display_order, is_active) VALUES ',
  '(''調理器具セット'', ''フライパン、鍋、包丁、まな板などの基本調理器具一式'', 6600.00, 1, 1, 1),',
  '(''食器類'', ''皿、茶碗、コップ、箸、スプーン、フォークなどの食器一式'', 3900.00, 1, 2, 1),',
  '(''洗剤類'', ''洗濯洗剤、食器用洗剤、お風呂用洗剤、トイレ用洗剤一式'', 3800.00, 1, 3, 1),',
  '(''タオル類'', ''バスタオル、フェイスタオル、ハンドタオル各2枚セット'', 2900.00, 1, 4, 1),',
  '(''アメニティ類'', ''シャンプー、リンス、ボディソープ、歯ブラシ、歯磨き粉一式'', 3500.00, 1, 5, 1),',
  '(''寝具カバーセット'', ''枕カバー、シーツ、掛け布団カバーのセット'', 4530.00, 1, 6, 1),',
  '(''毛布'', ''暖かい毛布1枚（冬季におすすめ）'', 3950.00, 1, 7, 1),',
  '(''アイロン'', ''スチームアイロン（セット割引対象外）'', 6860.00, 0, 8, 1),',
  '(''炊飯器'', ''3合炊き炊飯器（セット割引対象外）'', 6600.00, 0, 9, 1);'
);
PREPARE stmt_options FROM @sql_options; EXECUTE stmt_options; DEALLOCATE PREPARE stmt_options;

SET @sql_campaigns := CONCAT(
  'INSERT INTO `', @CAMPAIGNS_TABLE, '` ',
  '(campaign_name, campaign_description, discount_type, discount_value, min_stay_days, max_discount_amount, applicable_rooms, start_date, end_date, booking_start_date, booking_end_date, usage_limit, usage_count, is_active) VALUES ',
  '(''早割キャンペーン'', ''入居30日以上前のご予約で賃料・共益費10%OFF 早割'', ''percentage'', 10.00, 7, 50000.00, '''', ''2025-01-01'', ''2025-12-31'', ''2025-01-01'', ''2025-12-31'', 100, 0, 1),',
  '(''即入居割'', ''入居7日以内のご予約で賃料・共益費20%OFF 即入居'', ''percentage'', 20.00, 7, 80000.00, '''', ''2025-01-01'', ''2025-12-31'', ''2025-01-01'', ''2025-12-31'', 50, 0, 1);'
);
PREPARE stmt_campaigns FROM @sql_campaigns; EXECUTE stmt_campaigns; DEALLOCATE PREPARE stmt_campaigns;

SET @sql_customers := CONCAT(
  'INSERT INTO `', @CUSTOMERS_TABLE, '` ',
  '(first_name, last_name, email, phone, address, city, postal_code, country, date_of_birth, emergency_contact_name, emergency_contact_phone, identification_type, identification_number, notes, is_active) VALUES ',
  '(''太郎'', ''田中'', ''tanaka.taro@example.com'', ''090-1234-5678'', ''東京都世田谷区三軒茶屋1-1-1'', ''東京都'', ''154-0024'', ''Japan'', ''1985-05-15'', ''田中花子'', ''090-1234-5679'', ''運転免許証'', ''123456789012'', ''テスト顧客1'', 1),',
  '(''花子'', ''佐藤'', ''sato.hanako@example.com'', ''080-9876-5432'', ''神奈川県横浜市西区みなとみらい2-2-2'', ''横浜市'', ''220-0012'', ''Japan'', ''1990-08-22'', ''佐藤次郎'', ''080-9876-5433'', ''運転免許証'', ''987654321098'', ''テスト顧客2'', 1),',
  '(''次郎'', ''鈴木'', ''suzuki.jiro@example.com'', ''070-5555-1111'', ''大阪府大阪市北区梅田3-3-3'', ''大阪市'', ''530-0001'', ''Japan'', ''1988-12-03'', ''鈴木美香'', ''070-5555-1112'', ''パスポート'', ''AB1234567'', ''テスト顧客3'', 1);'
);
PREPARE stmt_customers FROM @sql_customers; EXECUTE stmt_customers; DEALLOCATE PREPARE stmt_customers;

SET @sql_bookings := CONCAT(
  'INSERT INTO `', @BOOKINGS_TABLE, '` ',
  '(room_id, customer_id, start_date, end_date, num_adults, num_children, plan_type, base_rent, utilities_fee, initial_costs, person_additional_fee, options_total, options_discount, total_price, discount_amount, final_price, status, payment_status, notes) VALUES ',
  '(1, 1, ''2025-02-01'', ''2025-04-01'', 2, 0, ''S'', 144000.00, 120000.00, 60500.00, 59000.00, 14300.00, 500.00, 397300.00, 26440.00, 370860.00, ''confirmed'', ''paid'', ''早割キャンペーン適用''),',
  '(2, 2, ''2025-08-10'', ''2025-08-20'', 1, 1, ''SS'', 32000.00, 25000.00, 60500.00, 5000.00, 7800.00, 0.00, 130300.00, 11400.00, 118900.00, ''pending'', ''unpaid'', ''即入居割適用'');'
);
PREPARE stmt_bookings FROM @sql_bookings; EXECUTE stmt_bookings; DEALLOCATE PREPARE stmt_bookings;

SET @sql_booking_options := CONCAT(
  'INSERT INTO `', @BOOKING_OPTIONS_TABLE, '` ',
  '(booking_id, option_id, quantity, unit_price, total_price) VALUES ',
  '(1, 1, 1, 6600.00, 6600.00),',
  '(1, 2, 1, 3900.00, 3900.00),',
  '(1, 3, 1, 3800.00, 3800.00),',
  '(2, 4, 1, 2900.00, 2900.00),',
  '(2, 5, 1, 3500.00, 3500.00),',
  '(2, 6, 1, 1400.00, 1400.00);'
);
PREPARE stmt_booking_options FROM @sql_booking_options; EXECUTE stmt_booking_options; DEALLOCATE PREPARE stmt_booking_options;

SELECT 'Rooms inserted:' as info, COUNT(*) as count FROM (SELECT 1) AS t
JOIN (SELECT @ROOMS_TABLE AS tn) as tbl
JOIN (SELECT @ROOMS_TABLE) as _dummy
;
