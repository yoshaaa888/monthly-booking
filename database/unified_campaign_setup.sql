INSERT INTO wp_monthly_campaigns (
    campaign_name, 
    type, 
    start_date, 
    end_date, 
    earlybird_days, 
    discount_type, 
    discount_value, 
    max_stay_days, 
    tax_type, 
    target_plan, 
    is_active
) VALUES
('即入居割20%', 'immediate', '2025-01-01', '2099-12-31', 0, 'percentage', 20.00, 30, 'taxable', 'ALL', 1),
('早割10%', 'earlybird', '2025-01-01', '2099-12-31', 30, 'percentage', 10.00, 30, 'taxable', 'S,M,L', 1)
ON DUPLICATE KEY UPDATE
    discount_value = VALUES(discount_value),
    is_active = VALUES(is_active),
    target_plan = VALUES(target_plan);
