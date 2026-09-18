import { test, expect } from '@playwright/test';

test.describe('E2E Journey: Cancel Appointment', () => {
  test('Customer successfully cancels an appointment with confirmation', async ({ page }) => {
    // Authenticate as customer
    await page.addInitScript(() => {
      localStorage.setItem('candycutz_auth_token', 'test-customer-token');
    });

    await page.route('**/auth/me', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          data: {
            id: 1,
            name: 'Jane Customer',
            email: 'jane@example.com',
            role: 'customer',
          },
        }),
      });
    });

    await page.route('**/notifications**', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ data: [] }),
      });
    });

    let bookingStatus = 'pending';

    await page.route('**/customer/bookings', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          data: [
            {
              id: 77,
              appointment_date: '2026-09-28',
              appointment_time: '15:30',
              status: bookingStatus,
              payment_status: 'pending',
              notes: 'Please cancel if rescheduling is needed',
              service: {
                id: 1,
                name: 'Traditional Royal Shave',
                price: 4500,
              },
              barber: {
                id: 3,
                name: 'Obo Shadow',
              },
            },
          ],
        }),
      });
    });

    let cancelCalled = false;
    await page.route('**/customer/bookings/77/cancel', async (route) => {
      cancelCalled = true;
      bookingStatus = 'cancelled';
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          status: 'success',
          message: 'Booking cancelled successfully.',
        }),
      });
    });

    // Navigate to bookings page
    await page.goto('/customer/dashboard/bookings');

    // Verify booking card is visible
    await expect(page.locator('text=Traditional Royal Shave')).toBeVisible();
    await expect(page.locator('article').getByText('pending', { exact: true })).toBeVisible();

    // Click "Cancel Booking" on the appointment card
    const cancelBtn = page.locator('article button', { hasText: 'Cancel Booking' });
    await expect(cancelBtn).toBeVisible();
    await cancelBtn.click();

    // Confirm cancellation in modal
    const modalConfirmBtn = page.locator('.fixed button.bg-red-500', { hasText: 'Cancel Booking' });
    await expect(modalConfirmBtn).toBeVisible();
    await modalConfirmBtn.click();

    // Verify cancellation API was invoked
    expect(cancelCalled).toBe(true);

    // Status badge updates to cancelled
    await expect(page.locator('article').getByText('cancelled', { exact: true })).toBeVisible();
  });
});
