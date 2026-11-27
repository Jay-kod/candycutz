import { test, expect } from '@playwright/test';

const VIEWPORTS = [
  { name: 'Small Phone (320x640)', width: 320, height: 640 },
  { name: 'Standard Android (360x800)', width: 360, height: 800 },
  { name: 'iPhone (390x844)', width: 390, height: 844 },
  { name: 'Large Phone (430x932)', width: 430, height: 932 },
  { name: 'Tablet (768x1024)', width: 768, height: 1024 },
  { name: 'Desktop (1366x768)', width: 1366, height: 768 },
];

const MOCK_BOOKINGS = [
  {
    id: 21,
    booking_reference: 'CC-DRFT21',
    appointment_date: '2026-11-28',
    appointment_time: '10:00 AM',
    status: 'draft',
    payment_status: 'unpaid',
    grand_total: 6500,
    service: { id: 1, name: 'Executive Skin Fade & Beard Lineup', price: 6500 },
    barber: { id: 2, name: 'Obo Shadow' },
    bank_details: {
      bank_name: 'Guaranty Trust Bank',
      account_name: 'CandyCutz Luxury Grooming',
      account_number: '0123456789',
    },
  },
  {
    id: 22,
    booking_reference: 'CC-PEND22',
    appointment_date: '2026-11-29',
    appointment_time: '02:30 PM',
    status: 'pending',
    payment_status: 'pending',
    notes: 'Please use clipper guard #1.5 on sides and light razor lineup.',
    grand_total: 8000,
    service: { id: 2, name: 'Hot Towel Beard Sculpt & Precision Cut', price: 8000 },
    barber: { id: 3, name: 'Master Blade' },
    bank_details: {
      bank_name: 'Zenith Bank Plc',
      account_name: 'CandyCutz Atelier Services',
      account_number: '1012345678',
    },
  },
  {
    id: 23,
    booking_reference: 'CC-CONF23',
    appointment_date: '2026-11-30',
    appointment_time: '04:00 PM',
    status: 'confirmed',
    payment_status: 'paid',
    deposit_paid: true,
    verification_code: 'CUTZ2026',
    grand_total: 9500,
    service: { id: 3, name: 'Royal Grooming Experience', price: 9500 },
    barber: { id: 4, name: 'Emmanuel Stylist', chair_number: 2 },
  },
  {
    id: 24,
    booking_reference: 'CC-COMP24',
    appointment_date: '2026-11-20',
    appointment_time: '11:00 AM',
    status: 'completed',
    payment_status: 'paid',
    deposit_paid: true,
    grand_total: 5000,
    service: { id: 4, name: 'Classic Gentleman Trim', price: 5000 },
    barber: { id: 2, name: 'Obo Shadow' },
  },
];

async function setupCustomerBookingsMocks(page) {
  await page.addInitScript(() => {
    localStorage.setItem('candycutz_auth_token', 'e2e-mock-token');
    localStorage.setItem(
      'candycutz_cached_user',
      JSON.stringify({
        id: 10,
        name: 'Adebayo Client',
        email: 'client@candycutz.com',
        role: 'customer',
        is_email_verified: true,
      })
    );
  });

  await page.route('**/api/v1/auth/me**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        success: true,
        data: {
          id: 10,
          name: 'Adebayo Client',
          email: 'client@candycutz.com',
          role: 'customer',
          is_email_verified: true,
        },
      }),
    });
  });

  await page.route('**/api/v1/customer/bookings', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ success: true, data: MOCK_BOOKINGS }),
    });
  });

  await page.route('**/api/v1/customer/drafts', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        success: true,
        data: [
          {
            ...MOCK_BOOKINGS[0],
            draft_time_remaining_seconds: 540,
          },
        ],
      }),
    });
  });

  await page.route('**/api/v1/customer/checkout**', async (route) => {
    if (route.request().url().includes('/payment-details')) {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          success: true,
          data: {
            bank_name: 'Zenith Bank Plc',
            account_name: 'CandyCutz Atelier Services',
            account_number: '1012345678',
          },
        }),
      });
      return;
    }
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ success: true }),
    });
  });

  await page.route('**/api/v1/settings**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        success: true,
        data: {
          site_name: 'CandyCutz',
          bank_name: 'Zenith Bank Plc',
          account_name: 'CandyCutz Atelier Services',
          account_number: '1012345678',
        },
      }),
    });
  });

  await page.route('**/api/v1/health**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ status: 'ok' }),
    });
  });

  await page.route('**/api/v1/notifications**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ success: true, data: [] }),
    });
  });

  await page.route('**/api/v1/notifications/unread-count**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ success: true, data: { count: 0 } }),
    });
  });

  await page.route('**/api/v1/notifications/stream**', async (route) => {
    await route.fulfill({
      status: 200,
      headers: { 'Content-Type': 'text/event-stream' },
      body: 'id: 0\n: ping\n\n',
    });
  });
}

async function checkHorizontalOverflow(page) {
  return page.evaluate(() => {
    const docWidth = Math.max(
      document.documentElement.scrollWidth,
      document.body.scrollWidth
    );
    const winWidth = window.innerWidth;
    const culprits = [];
    for (const el of document.querySelectorAll('*')) {
      const rect = el.getBoundingClientRect();
      if (rect.width > 0 && rect.right > winWidth + 1.5) {
        culprits.push(`${el.tagName.toLowerCase()}.${String(el.className).slice(0, 60)}`);
      }
    }
    return {
      hasOverflow: docWidth > winWidth + 1.5,
      docWidth,
      winWidth,
      culprits: culprits.slice(0, 5),
    };
  });
}

test.describe('Customer Bookings Mobile Display Responsiveness', () => {
  for (const vp of VIEWPORTS) {
    test(`renders cleanly with zero horizontal overflow at ${vp.name}`, async ({ page }) => {
      await page.setViewportSize({ width: vp.width, height: vp.height });
      await setupCustomerBookingsMocks(page);

      await page.goto('/customer/dashboard/bookings', { waitUntil: 'domcontentloaded' });
      await expect(page.getByRole('heading', { name: /My Bookings/i })).toBeVisible();
      await expect(page.getByText('Hot Towel Beard Sculpt & Precision Cut')).toBeVisible();
      await expect(page.getByText('CUTZ2026')).toBeVisible();

      const overflowReport = await checkHorizontalOverflow(page);
      expect(
        overflowReport.hasOverflow,
        `Overflow at ${vp.width}px: doc=${overflowReport.docWidth}px, win=${overflowReport.winWidth}px, culprits=${overflowReport.culprits.join(', ')}`
      ).toBe(false);

      if (vp.width < 768) {
        const descAlign = await page
          .locator('p.text-left-forced')
          .first()
          .evaluate((el) => getComputedStyle(el).textAlign);
        expect(descAlign).toBe('left');

        const ctaBox = await page.getByRole('link', { name: /Book New Cut/i }).boundingBox();
        expect(ctaBox?.height).toBeGreaterThanOrEqual(42);
      }
    });
  }

  test('supports filters, inline receipt uploader, and receipt modal cleanly on 320px phone', async ({
    page,
  }) => {
    await page.setViewportSize({ width: 320, height: 640 });
    await setupCustomerBookingsMocks(page);

    await page.goto('/customer/dashboard/bookings', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: /My Bookings/i })).toBeVisible();

    // Filter to Confirmed and verify official receipt modal fits cleanly at 320px
    await page.getByRole('tab', { name: /Confirmed/i }).click();
    await expect(page.getByText('Royal Grooming Experience')).toBeVisible();
    await expect(page.getByText('Hot Towel Beard Sculpt & Precision Cut')).toBeHidden();

    await page.getByRole('button', { name: /View Official Receipt/i }).click();
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    await expect(dialog.getByText('CUTZ2026')).toBeVisible();

    const modalOverflow = await checkHorizontalOverflow(page);
    expect(modalOverflow.hasOverflow).toBe(false);

    await page.getByRole('button', { name: /Close receipt/i }).click();
    await expect(dialog).toBeHidden();

    // Filter to Pending and open inline ReceiptUploader at 320px width
    await page.getByRole('tab', { name: /Pending/i }).click();
    await page.getByRole('button', { name: /Upload Manual Receipt Slip/i }).click();
    await expect(page.getByRole('heading', { name: /Upload Payment Receipt/i })).toBeVisible();

    const uploaderOverflow = await checkHorizontalOverflow(page);
    expect(uploaderOverflow.hasOverflow).toBe(false);
  });
});
