
TRUNCATE TABLE wp_monthly_bookings;
TRUNCATE TABLE wp_monthly_campaigns;
TRUNCATE TABLE wp_monthly_rooms;

INSERT INTO wp_monthly_rooms (room_id, display_name, room_name, property_name, is_active)
VALUES (1, 'E2Eデモ101', 'Room 101', 'Demo Building', 1);

INSERT INTO wp_monthly_campaigns (room_id, title, type, start_date, end_date, is_active)
VALUES (1, 'Post-merge E2E Campaign', 'discount', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1);

INSERT INTO wp_monthly_bookings (room_id, start_date, end_date, status)
VALUES (1, DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'confirmed');
