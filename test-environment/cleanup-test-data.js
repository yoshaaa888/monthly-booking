const mysql = require('mysql2/promise');

async function cleanupTestData() {
  const connection = await mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: 'root',
    database: 'local'
  });

  try {
    console.log('Cleaning up test data...');

    await connection.execute('DELETE FROM wp_monthly_room_campaigns WHERE room_id IN (633, 634, 635)');
    await connection.execute('DELETE FROM wp_monthly_campaigns WHERE campaign_id IN (101, 102)');
    await connection.execute('DELETE FROM wp_monthly_bookings WHERE booking_id IN (1001, 1002)');
    await connection.execute('DELETE FROM wp_monthly_rooms WHERE room_id IN (633, 634, 635)');

    console.log('Test data cleanup completed successfully');

  } catch (error) {
    console.error('Error cleaning up test data:', error);
  } finally {
    await connection.end();
  }
}

if (require.main === module) {
  cleanupTestData();
}

module.exports = { cleanupTestData };
