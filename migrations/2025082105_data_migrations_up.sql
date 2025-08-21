UPDATE __PREFIX__monthly_campaigns
  SET is_active = 0
  WHERE type IN ('earlybird','immediate');
DELETE rc FROM __PREFIX__monthly_room_campaigns rc
JOIN __PREFIX__monthly_campaigns c ON c.id = rc.campaign_id
WHERE c.type IN ('earlybird','immediate');
INSERT IGNORE INTO __PREFIX__monthly_booking_campaign_contract_types (campaign_id, contract_type)
SELECT id, 'S' FROM __PREFIX__monthly_campaigns;
