const { test, expect } = require('@playwright/test');
const mysql = require('mysql2/promise');

test.describe('Monthly Booking - Database Verification', () => {
  let connection;

  test.beforeAll(async () => {
    try {
      connection = await mysql.createConnection({
        host: 'localhost',
        user: 'root',
        password: 'root',
        database: 'local'
      });
      console.log('✅ Database connection established');
    } catch (error) {
      console.error('❌ Database connection failed:', error.message);
      console.log('Note: This test requires Local WP database access');
    }
  });

  test.afterAll(async () => {
    if (connection) {
      await connection.end();
    }
  });

  test('Verify database tables exist and have correct structure', async () => {
    if (!connection) {
      test.skip('Database connection not available');
      return;
    }

    console.log('Verifying database table structure...');

    const requiredTables = [
      'wp_monthly_rooms',
      'wp_monthly_bookings', 
      'wp_monthly_customers',
      'wp_monthly_campaigns',
      'wp_monthly_options',
      'wp_monthly_booking_options',
      'wp_monthly_rates'
    ];

    for (const tableName of requiredTables) {
      const [rows] = await connection.execute(
        'SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ? AND table_name = ?',
        ['local', tableName]
      );
      
      expect(rows[0].count).toBe(1);
      console.log(`✅ Table ${tableName} exists`);
    }

    const [roomsColumns] = await connection.execute(
      'DESCRIBE wp_monthly_rooms'
    );
    expect(roomsColumns.length).toBeGreaterThan(10);
    console.log(`✅ wp_monthly_rooms has ${roomsColumns.length} columns`);

    const [bookingsColumns] = await connection.execute(
      'DESCRIBE wp_monthly_bookings'
    );
    expect(bookingsColumns.length).toBeGreaterThan(15);
    console.log(`✅ wp_monthly_bookings has ${bookingsColumns.length} columns`);
  });

  test('Verify seed data was inserted correctly', async () => {
    if (!connection) {
      test.skip('Database connection not available');
      return;
    }

    console.log('Verifying seed data insertion...');

    const [rooms] = await connection.execute(
      'SELECT COUNT(*) as count FROM wp_monthly_rooms WHERE is_active = 1'
    );
    expect(rooms[0].count).toBe(5);
    console.log(`✅ Found ${rooms[0].count} active rooms`);

    const [options] = await connection.execute(
      'SELECT COUNT(*) as count FROM wp_monthly_options WHERE is_active = 1'
    );
    expect(options[0].count).toBe(9);
    console.log(`✅ Found ${options[0].count} active options`);

    const [campaigns] = await connection.execute(
      'SELECT COUNT(*) as count FROM wp_monthly_campaigns WHERE is_active = 1'
    );
    expect(campaigns[0].count).toBe(2);
    console.log(`✅ Found ${campaigns[0].count} active campaigns`);

    const [customers] = await connection.execute(
      'SELECT COUNT(*) as count FROM wp_monthly_customers WHERE is_active = 1'
    );
    expect(customers[0].count).toBe(3);
    console.log(`✅ Found ${customers[0].count} active customers`);

    const [earlyBooking] = await connection.execute(
      'SELECT * FROM wp_monthly_campaigns WHERE campaign_description LIKE "%早割%"'
    );
    expect(earlyBooking.length).toBe(1);
    expect(earlyBooking[0].discount_value).toBe(10);
    console.log('✅ Early booking campaign configured correctly');

    const [lastMinute] = await connection.execute(
      'SELECT * FROM wp_monthly_campaigns WHERE campaign_description LIKE "%即入居%"'
    );
    expect(lastMinute.length).toBe(1);
    expect(lastMinute[0].discount_value).toBe(20);
    console.log('✅ Last minute campaign configured correctly');
  });

  test('Verify booking data integrity after form submission', async ({ page }) => {
    if (!connection) {
      test.skip('Database connection not available');
      return;
    }

    console.log('Testing booking data integrity...');

    const [initialBookings] = await connection.execute(
      'SELECT COUNT(*) as count FROM wp_monthly_bookings'
    );
    const initialCount = initialBookings[0].count;

    await page.goto('/monthly-estimate/');
    await page.waitForLoadState('networkidle');

    await page.selectOption('#room_id', '1');
    
    const today = new Date();
    const checkinDate = new Date(today);
    checkinDate.setDate(today.getDate() + 35);
    const checkoutDate = new Date(checkinDate);
    checkoutDate.setDate(checkinDate.getDate() + 30);
    
    await page.fill('#move_in_date', checkinDate.toISOString().split('T')[0]);
    await page.fill('#move_out_date', checkoutDate.toISOString().split('T')[0]);
    await page.selectOption('#num_adults', '1');
    await page.selectOption('#num_children', '0');
    await page.check('input[name="options[]"][value="1"]');
    await page.fill('#guest_name', 'DB Test User');
    await page.fill('#guest_email', 'dbtest@example.com');

    await page.click('#calculate-estimate-btn');
    await page.waitForSelector('#estimate-result', { timeout: 10000 });
    
    await page.click('#submit-booking-btn');
    await page.waitForSelector('.booking-success', { timeout: 15000 });

    await page.waitForTimeout(2000);

    const [newBookings] = await connection.execute(
      'SELECT COUNT(*) as count FROM wp_monthly_bookings'
    );
    expect(newBookings[0].count).toBe(initialCount + 1);
    console.log('✅ New booking record created in database');

    const [latestBooking] = await connection.execute(
      'SELECT * FROM wp_monthly_bookings ORDER BY created_at DESC LIMIT 1'
    );
    
    expect(latestBooking.length).toBe(1);
    const booking = latestBooking[0];
    
    expect(booking.room_id).toBe(1);
    expect(booking.num_adults).toBe(1);
    expect(booking.num_children).toBe(0);
    expect(booking.plan_type).toBe('S');
    expect(booking.status).toBe('pending');
    console.log('✅ Booking data saved correctly');

    const [customer] = await connection.execute(
      'SELECT * FROM wp_monthly_customers WHERE customer_id = ?',
      [booking.customer_id]
    );
    expect(customer.length).toBe(1);
    expect(customer[0].email).toBe('dbtest@example.com');
    console.log('✅ Customer data linked correctly');

    const [bookingOptions] = await connection.execute(
      'SELECT * FROM wp_monthly_booking_options WHERE booking_id = ?',
      [booking.booking_id]
    );
    expect(bookingOptions.length).toBe(1);
    expect(bookingOptions[0].option_id).toBe(1);
    console.log('✅ Booking options saved correctly');
  });

  test('Verify campaign application in database', async ({ page }) => {
    if (!connection) {
      test.skip('Database connection not available');
      return;
    }

    console.log('Testing campaign application in database...');

    await page.goto('/monthly-estimate/');
    await page.waitForLoadState('networkidle');

    await page.selectOption('#room_id', '2');
    
    const today = new Date();
    const checkinDate = new Date(today);
    checkinDate.setDate(today.getDate() + 40); // 40 days ahead for early booking
    const checkoutDate = new Date(checkinDate);
    checkoutDate.setDate(checkinDate.getDate() + 60);
    
    await page.fill('#move_in_date', checkinDate.toISOString().split('T')[0]);
    await page.fill('#move_out_date', checkoutDate.toISOString().split('T')[0]);
    await page.selectOption('#num_adults', '2');
    await page.selectOption('#num_children', '0');
    await page.fill('#guest_name', 'Campaign Test User');
    await page.fill('#guest_email', 'campaign.test@example.com');

    await page.click('#calculate-estimate-btn');
    await page.waitForSelector('#estimate-result', { timeout: 10000 });

    const campaignBadge = await page.locator('.campaign-badge.early');
    await expect(campaignBadge).toBeVisible();

    await page.click('#submit-booking-btn');
    await page.waitForSelector('.booking-success', { timeout: 15000 });
    await page.waitForTimeout(2000);

    const [campaignBooking] = await connection.execute(
      'SELECT * FROM wp_monthly_bookings WHERE discount_amount > 0 ORDER BY created_at DESC LIMIT 1'
    );
    
    expect(campaignBooking.length).toBe(1);
    const booking = campaignBooking[0];
    
    expect(booking.discount_amount).toBeGreaterThan(0);
    expect(booking.final_price).toBeLessThan(booking.total_price);
    console.log(`✅ Campaign discount applied: ¥${booking.discount_amount}`);
    console.log(`✅ Final price after discount: ¥${booking.final_price}`);
  });

  test('Verify data consistency across related tables', async () => {
    if (!connection) {
      test.skip('Database connection not available');
      return;
    }

    console.log('Verifying data consistency across tables...');

    const [orphanedBookings] = await connection.execute(`
      SELECT b.booking_id 
      FROM wp_monthly_bookings b 
      LEFT JOIN wp_monthly_rooms r ON b.room_id = r.room_id 
      WHERE r.room_id IS NULL
    `);
    expect(orphanedBookings.length).toBe(0);
    console.log('✅ No orphaned bookings found');

    const [orphanedCustomers] = await connection.execute(`
      SELECT b.booking_id 
      FROM wp_monthly_bookings b 
      LEFT JOIN wp_monthly_customers c ON b.customer_id = c.customer_id 
      WHERE c.customer_id IS NULL
    `);
    expect(orphanedCustomers.length).toBe(0);
    console.log('✅ All bookings have valid customer references');

    const [orphanedOptions] = await connection.execute(`
      SELECT bo.booking_option_id 
      FROM wp_monthly_booking_options bo 
      LEFT JOIN wp_monthly_bookings b ON bo.booking_id = b.booking_id 
      WHERE b.booking_id IS NULL
    `);
    expect(orphanedOptions.length).toBe(0);
    console.log('✅ All booking options have valid booking references');

    const [priceConsistency] = await connection.execute(`
      SELECT booking_id, 
             (base_rent + utilities_fee + initial_costs + person_additional_fee + options_total - options_discount) as calculated_total,
             total_price,
             (total_price - discount_amount) as calculated_final,
             final_price
      FROM wp_monthly_bookings 
      WHERE booking_id IS NOT NULL
    `);

    for (const booking of priceConsistency) {
      expect(Math.abs(booking.calculated_total - booking.total_price)).toBeLessThan(1);
      expect(Math.abs(booking.calculated_final - booking.final_price)).toBeLessThan(1);
    }
    console.log('✅ Price calculations are consistent');
  });
});
