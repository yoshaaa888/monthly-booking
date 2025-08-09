import { test, expect } from '@playwright/test';

test.describe('Reservation Management MVP', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/wp-admin');
        await page.fill('#user_login', 'admin');
        await page.fill('#user_pass', 'password');
        await page.click('#wp-submit');
        
        await page.goto('/wp-admin/admin.php?page=monthly-room-booking-registration');
    });
    
    test('should create new reservation and update calendar', async ({ page }) => {
        await page.click('text=新規予約追加');
        
        await page.selectOption('#room_id', { index: 1 });
        await page.fill('#guest_name', 'テスト顧客');
        await page.fill('#guest_email', 'test@example.com');
        await page.fill('#checkin_date', '2025-09-01');
        await page.fill('#checkout_date', '2025-09-05');
        await page.fill('#notes', 'テスト予約です');
        
        await page.click('input[type="submit"]');
        
        await expect(page.locator('.notice-success')).toBeVisible();
        
        await expect(page.locator('text=テスト顧客')).toBeVisible();
        
        await page.goto('/wp-admin/admin.php?page=monthly-room-booking-calendar');
        await expect(page.locator('.calendar-day.booked')).toBeVisible();
    });
    
    test('should validate form fields with accessibility', async ({ page }) => {
        await page.click('text=新規予約追加');
        
        await page.click('input[type="submit"]');
        
        await expect(page.locator('#room_id_error')).toContainText('部屋を選択してください');
        await expect(page.locator('#guest_name_error')).toContainText('ゲスト名を入力してください');
        
        await expect(page.locator('#room_id')).toHaveAttribute('aria-invalid', 'true');
        await expect(page.locator('#guest_name')).toHaveAttribute('aria-invalid', 'true');
    });
    
    test('should detect reservation conflicts', async ({ page }) => {
        await page.click('text=新規予約追加');
        await page.selectOption('#room_id', { index: 1 });
        await page.fill('#guest_name', '顧客1');
        await page.fill('#guest_email', 'customer1@example.com');
        await page.fill('#checkin_date', '2025-09-01');
        await page.fill('#checkout_date', '2025-09-05');
        await page.click('input[type="submit"]');
        
        await page.waitForSelector('.notice-success');
        
        await page.click('text=新規予約追加');
        await page.selectOption('#room_id', { index: 1 });
        await page.fill('#guest_name', '顧客2');
        await page.fill('#guest_email', 'customer2@example.com');
        await page.fill('#checkin_date', '2025-09-03');
        await page.fill('#checkout_date', '2025-09-07');
        await page.click('input[type="submit"]');
        
        await expect(page.locator('.notice-error')).toContainText('選択された期間は既に予約されています');
    });
    
    test('should edit existing reservation', async ({ page }) => {
        await page.click('text=新規予約追加');
        await page.selectOption('#room_id', { index: 1 });
        await page.fill('#guest_name', '編集テスト');
        await page.fill('#guest_email', 'edit@example.com');
        await page.fill('#checkin_date', '2025-10-01');
        await page.fill('#checkout_date', '2025-10-05');
        await page.click('input[type="submit"]');
        
        await page.waitForSelector('.notice-success');
        
        await page.click('text=編集');
        
        await page.fill('#guest_name', '編集済み顧客');
        await page.selectOption('#status', 'confirmed');
        await page.click('input[type="submit"]');
        
        await expect(page.locator('.notice-success')).toBeVisible();
        await expect(page.locator('text=編集済み顧客')).toBeVisible();
    });
    
    test('should delete reservation and update calendar', async ({ page }) => {
        await page.click('text=新規予約追加');
        await page.selectOption('#room_id', { index: 1 });
        await page.fill('#guest_name', '削除テスト');
        await page.fill('#guest_email', 'delete@example.com');
        await page.fill('#checkin_date', '2025-11-01');
        await page.fill('#checkout_date', '2025-11-05');
        await page.click('input[type="submit"]');
        
        await page.waitForSelector('.notice-success');
        
        page.on('dialog', dialog => dialog.accept());
        await page.click('.delete-reservation');
        
        await expect(page.locator('.notice-success')).toBeVisible();
        await expect(page.locator('text=削除テスト')).not.toBeVisible();
    });
    
    test('should maintain keyboard navigation accessibility', async ({ page }) => {
        await page.goto('/wp-admin/admin.php?page=monthly-room-booking-calendar');
        
        const calendarGrid = page.locator('.calendar-grid');
        await expect(calendarGrid).toBeVisible();
        
        const firstCell = page.locator('.calendar-day[tabindex="0"]').first();
        await firstCell.focus();
        
        await page.keyboard.press('ArrowRight');
        await page.keyboard.press('ArrowDown');
        await page.keyboard.press('Home');
        await page.keyboard.press('End');
        
        await expect(page.locator('.calendar-day:focus')).toBeVisible();
    });
    
    test('should show feature disabled message when flag is off', async ({ page }) => {
        await page.addInitScript(() => {
            window.MB_FEATURE_RESERVATIONS_MVP = false;
        });
        
        await page.goto('/wp-admin/admin.php?page=monthly-room-booking-registration');
        
        await expect(page.locator('text=機能が無効になっています')).toBeVisible();
        await expect(page.locator('text=新規予約追加')).not.toBeVisible();
    });
});
