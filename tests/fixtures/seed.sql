
TRUNCATE TABLE wp_monthly_bookings;
TRUNCATE TABLE wp_monthly_campaigns;
TRUNCATE TABLE wp_monthly_rooms;

INSERT INTO wp_monthly_rooms (room_id, property_name, display_name, room_name, daily_rent, is_active)
VALUES (101, 'E2Eテスト物件', 'E2Eデモ101', 'Room 101', 3000, 1);

INSERT INTO wp_monthly_campaigns (
    campaign_name, 
    campaign_description, 
    type, 
    discount_type, 
    discount_value, 
    min_stay_days, 
    start_date, 
    end_date, 
    is_active
) VALUES (
    'Post-merge E2E Campaign', 
    'E2Eテスト用キャンペーン', 
    'earlybird', 
    'percentage', 
    10.00, 
    7, 
    CURDATE(), 
    DATE_ADD(CURDATE(), INTERVAL 30 DAY), 
    1
);

INSERT INTO wp_monthly_bookings (
    room_id, 
    customer_id, 
    start_date, 
    end_date, 
    status, 
    base_rent, 
    utilities_fee, 
    initial_costs, 
    total_price, 
    final_price
) VALUES (
    101, 
    1, 
    DATE_ADD(CURDATE(), INTERVAL 10 DAY), 
    DATE_ADD(CURDATE(), INTERVAL 17 DAY), 
    'confirmed', 
    21000.00, 
    14000.00, 
    49500.00, 
    84500.00, 
    84500.00
);
