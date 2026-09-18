import { test, expect } from '@playwright/test';

test.describe('E2E Journey: Book Appointment', () => {
  test('Customer successfully books an appointment for a service', async ({ page }) => {
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

    // Mock public services
    await page.route('**/public/services*', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          data: [
            {
              id: 1,
              name: 'Executive Precision Cut',
              description: 'Top-tier precision haircut tailored to your head shape.',
              price: 5000,
              duration_minutes: 45,
              category: { name: 'Haircut' },
              image: null,
            },
          ],
        }),
      });
    });

    // Mock public barbers
    await page.route('**/public/barbers*', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          data: [
            {
              id: 1,
              name: 'Obo Shadow',
              rating: '4.9',
              years_experience: 6,
              avatar: null,
            },
          ],
        }),
      });
    });

    // Mock available slots
    await page.route('**/public/available-slots*', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          data: ['09:00', '10:00', '11:30', '14:00', '16:00'],
        }),
      });
    });

    let bookingPayload = null;
    await page.route('**/customer/bookings', async (route) => {
      if (route.request().method() === 'POST') {
        bookingPayload = route.request().postDataJSON();
        await route.fulfill({
          status: 201,
          contentType: 'application/json',
          body: JSON.stringify({
            status: 'success',
            message: 'Booking created successfully! Please pay to confirm.',
            data: { id: 101, ...bookingPayload },
          }),
        });
      } else {
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({ data: [] }),
        });
      }
    });

    // Navigate to book page for service 1
    await page.goto('/customer/dashboard/book/1');

    // Verify service details are displayed
    await expect(page.locator('h1')).toContainText('Secure your Seat');
    await expect(page.locator('h2', { hasText: 'Executive Precision Cut' })).toBeVisible();

    // Select barber Obo Shadow
    await page.locator('text=Obo Shadow').click();

    // Pick tomorrow's date
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const dateStr = tomorrow.toISOString().split('T')[0];

    await page.locator('input[type="date"]').fill(dateStr);
    await page.locator('input[type="date"]').dispatchEvent('change');

    // Pick 10:00 time slot
    await page.locator('button', { hasText: '10:00' }).click();

    // Enter notes
    await page.locator('textarea').fill('Sharp fade with clean beard alignment');

    // Verify booking summary
    await expect(page.locator('text=Booking Summary')).toBeVisible();
    await expect(page.locator('form').getByText('₦5,000')).toBeVisible();

    // Submit booking
    await page.locator('button', { hasText: 'Confirm Booking' }).click();

    // Verify redirection to bookings page
    await page.waitForURL('**/customer/dashboard/bookings');
    expect(bookingPayload).not.toBeNull();
    expect(bookingPayload.barber_id).toBe(1);
    expect(bookingPayload.appointment_time).toBe('10:00');
  });
});
