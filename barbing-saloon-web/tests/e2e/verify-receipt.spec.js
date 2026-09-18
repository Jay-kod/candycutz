import { test, expect } from '@playwright/test';

test.describe('E2E Journey: Verify Receipt', () => {
  test('Customer uploads bank transfer receipt and Barber approves it', async ({ page }) => {
    // -------------------------------------------------------------
    // Part 1: Customer uploads receipt
    // -------------------------------------------------------------
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

    let customerBookingsState = 'pending_payment';

    await page.route('**/customer/bookings', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          data: [
            {
              id: 55,
              appointment_date: '2026-09-26',
              appointment_time: '11:00',
              status: 'pending',
              payment_status: customerBookingsState === 'receipt_uploaded' ? 'awaiting_verification' : null,
              service: {
                id: 1,
                name: 'Signature Fade & Beard Sculpt',
                price: 8000,
              },
              barber: {
                id: 2,
                name: 'Master Blade',
              },
            },
          ],
        }),
      });
    });

    await page.route('**/customer/checkout', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ status: 'success' }),
      });
    });

    await page.route('**/customer/checkout/55/payment-details', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          data: {
            bank_name: 'Access Bank',
            account_name: 'CandyCutz Grooming',
            account_number: '0987654321',
          },
        }),
      });
    });

    let receiptUploaded = false;
    await page.route('**/customer/checkout/55/receipt', async (route) => {
      receiptUploaded = true;
      customerBookingsState = 'receipt_uploaded';
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          status: 'success',
          message: 'Receipt uploaded successfully.',
        }),
      });
    });

    // Go to customer bookings
    await page.goto('/customer/dashboard/bookings');
    await expect(page.locator('text=Signature Fade & Beard Sculpt')).toBeVisible();

    // Start payment
    await page.locator('button', { hasText: 'Pay & Upload Receipt' }).click();
    await expect(page.getByRole('heading', { name: 'Upload Payment Receipt' })).toBeVisible();

    // Upload mock receipt file
    const fileInput = page.locator('input[type="file"]');
    await fileInput.setInputFiles({
      name: 'bank_transfer_receipt.png',
      mimeType: 'image/png',
      buffer: Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', 'base64'),
    });

    // Submit receipt
    const submitBtn = page.locator('button', { hasText: 'Submit Receipt' });
    await expect(submitBtn).toBeVisible();
    await submitBtn.click();

    expect(receiptUploaded).toBe(true);

    // -------------------------------------------------------------
    // Part 2: Barber views and verifies receipt
    // -------------------------------------------------------------
    // Switch auth to barber
    await page.evaluate(() => {
      localStorage.setItem('candycutz_auth_token', 'test-barber-token');
    });

    await page.route('**/auth/me', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          data: {
            id: 2,
            name: 'Master Blade',
            email: 'barber@candycutz.test',
            role: 'barber',
          },
        }),
      });
    });

    await page.route('**/barber/bookings*', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          data: [
            {
              id: 55,
              customer_name: 'Jane Customer',
              customer_email: 'jane@example.com',
              customer_phone: '+2348011223344',
              service_name: 'Signature Fade & Beard Sculpt',
              price: 8000,
              duration_minutes: 60,
              appointment_date: '2026-09-26',
              appointment_time: '11:00',
              status: 'pending',
              payment_status: 'awaiting_verification',
              receipt_image: 'receipts/mock_receipt.png',
              transaction_ref: 'TXN-CCZ-20260926-55',
            },
          ],
        }),
      });
    });

    await page.route('**/payments/appointments/55/receipt', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'image/png',
        body: Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', 'base64'),
      });
    });

    let verificationAction = null;
    await page.route('**/barber/bookings/55/verify-payment', async (route) => {
      const data = route.request().postDataJSON();
      verificationAction = data?.action;
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          status: 'success',
          message: 'Payment verified successfully.',
        }),
      });
    });

    // Barber navigates directly to receipt review page
    await page.goto('/barber/payments/55/receipt');

    // Verify receipt view loads customer details and price
    await expect(page.getByRole('heading', { name: 'Receipt Details', exact: true })).toBeVisible();
    await expect(page.locator('text=Jane Customer')).toBeVisible();
    await expect(page.locator('text=₦8,000')).toBeVisible();
    await expect(page.locator('text=awaiting_verification')).toBeVisible();

    // Click "Approve Payment"
    const approveBtn = page.locator('button', { hasText: 'Approve Payment' });
    await expect(approveBtn).toBeVisible();
    await approveBtn.click();

    // Barber should be redirected back to payments list
    await page.waitForURL('**/barber/payments');
    expect(verificationAction).toBe('approve');
  });
});
