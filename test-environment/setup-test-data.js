const mysql = require('mysql2/promise');

async function setupTestData() {
  const connection = await mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: 'root',
    database: 'local'
  });

  try {
    console.log('Setting up test data...');

    await connection.execute(`
      INSERT IGNORE INTO wp_monthly_rooms (room_id, room_name, is_active, daily_rate) VALUES
      (633, 'Test Room A', 1, 5000),
      (634, 'Test Room B', 1, 6000),
      (635, 'Test Room C', 1, 7000)
    `);

    const today = new Date();
    const futureDate1 = new Date(today);
    futureDate1.setDate(today.getDate() + 10);
    const futureDate2 = new Date(today);
    futureDate2.setDate(today.getDate() + 15);

    await connection.execute(`
      INSERT IGNORE INTO wp_monthly_bookings (booking_id, room_id, checkin_date, checkout_date, status) VALUES
      (1001, 633, ?, ?, 'confirmed'),
      (1002, 634, ?, ?, 'confirmed')
    `, [
      futureDate1.toISOString().split('T')[0],
      futureDate2.toISOString().split('T')[0],
      futureDate1.toISOString().split('T')[0],
      futureDate2.toISOString().split('T')[0]
    ]);

    const campaignStart = new Date(today);
    campaignStart.setDate(today.getDate() + 30);
    const campaignEnd = new Date(today);
    campaignEnd.setDate(today.getDate() + 45);

    await connection.execute(`
      INSERT IGNORE INTO wp_monthly_campaigns (campaign_id, campaign_name, campaign_type, start_date, end_date, is_active) VALUES
      (101, 'Test Campaign A', 'discount', ?, ?, 1),
      (102, 'Test Campaign B', 'earlybird', ?, ?, 1)
    `, [
      campaignStart.toISOString().split('T')[0],
      campaignEnd.toISOString().split('T')[0],
      campaignStart.toISOString().split('T')[0],
      campaignEnd.toISOString().split('T')[0]
    ]);

    await connection.execute(`
      INSERT IGNORE INTO wp_monthly_room_campaigns (room_id, campaign_id) VALUES
      (633, 101),
      (634, 102)
    `);

    console.log('Test data setup completed successfully');

  } catch (error) {
    console.error('Error setting up test data:', error);
  } finally {
    await connection.end();
  }
}

if (require.main === module) {
  setupTestData();
}

module.exports = { setupTestData };
