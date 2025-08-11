
DELETE FROM wp_monthly_campaigns WHERE campaign_name IN ('即入居割20%', '早割10%');

INSERT INTO wp_monthly_campaigns (
    campaign_name, campaign_description, type, discount_type, discount_value, 
    min_stay_days, earlybird_days, max_discount_amount, max_discount_days, 
    tax_type, target_plan, start_date, end_date, is_active
) VALUES 
(
    '即入居割20%', 
    '入居7日以内のご予約で賃料・共益費20%OFF 即入居割', 
    'immediate', 
    'percentage', 
    20.00, 
    1, 
    0, 
    80000.00, 
    30, 
    'taxable', 
    'ALL', 
    '2025-01-01', 
    '2099-12-31', 
    1
),
(
    '早割10%', 
    '入居30日以上前のご予約で賃料・共益費10%OFF 早割', 
    'earlybird', 
    'percentage', 
    10.00, 
    7, 
    30, 
    50000.00, 
    30, 
    'taxable', 
    'S,M,L', 
    '2025-01-01', 
    '2099-12-31', 
    1
);

SELECT campaign_name, type, discount_type, discount_value, earlybird_days, is_active 
FROM wp_monthly_campaigns 
WHERE campaign_name IN ('即入居割20%', '早割10%');
