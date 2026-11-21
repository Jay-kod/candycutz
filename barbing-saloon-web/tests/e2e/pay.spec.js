import { test, expect } from '@playwright/test';

test.describe('E2E Journey: Pay for Appointment', () => {
  test('Customer initiates checkout and views transfer bank details', async ({ page }) => {
    // Authenticate as customer
    await page.addInitScript(() => {
      localStorage.setItem('candycutz_auth_token', 'test-customer-token');
    });

    // Mock auth profile
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

    // Mock notifications
    await page.route('**/notifications**', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ data: [] }),
      });
    });

    // Mock bookings list with one pending booking without prior checkout
    await page.route('**/customer/bookings', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          data: [
            {
              id: 42,
              appointment_date: '2026-09-25',
              appointment_time: '14:00',
              status: 'pending',
              payment_status: null,
              notes: 'First time visit',
              service: {
                id: 1,
                name: 'Executive Precision Cut',
                price: 7500,
              },
              barber: {
                id: 1,
                name: 'Obo Shadow',
              },
            },
          ],
        }),
      });
    });

    let checkoutCalled = false;
    await page.route('**/customer/checkout', async (route) => {
      checkoutCalled = true;
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          status: 'success',
          message: 'Checkout initiated',
        }),
      });
    });

    // Mock bank details for manual transfer
    await page.route('**/customer/checkout/42/payment-details', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          data: {
            bank_name: 'Guaranty Trust Bank',
            account_name: 'CandyCutz Grooming Lounge',
            account_number: '0123456789',
          },
        }),
      });
    });

    // Navigate to customer bookings
    await page.goto('/customer/dashboard/bookings');

    // Verify booking card is displayed
    await expect(page.locator('text=Executive Precision Cut')).toBeVisible();
    await expect(page.locator('text=With Obo Shadow')).toBeVisible();

    // Click "Pay & Upload Receipt" button
    const payBtn = page.locator('button', { hasText: 'Pay & Upload Receipt' });
    await expect(payBtn).toBeVisible();
    await payBtn.click();

    // Assert checkout was initiated and payment details appear
    expect(checkoutCalled).toBe(true);
    await expect(page.getByRole('heading', { name: 'Upload Payment Receipt' })).toBeVisible();
    await expect(page.locator('text=Transfer Payment To')).toBeVisible();
    await expect(page.locator('text=Guaranty Trust Bank')).toBeVisible();
    await expect(page.locator('text=0123456789')).toBeVisible();
    await expect(page.locator('text=CandyCutz Grooming Lounge')).toBeVisible();
  });
});
