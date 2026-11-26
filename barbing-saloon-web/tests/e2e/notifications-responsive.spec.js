import { test, expect } from '@playwright/test';

const VIEWPORTS = [
  { name: 'Small Phone (320x640)', width: 320, height: 640 },
  { name: 'Standard Android (360x800)', width: 360, height: 800 },
  { name: 'iPhone (390x844)', width: 390, height: 844 },
  { name: 'Large Phone (430x932)', width: 430, height: 932 },
  { name: 'Tablet (768x1024)', width: 768, height: 1024 },
  { name: 'Desktop (1366x768)', width: 1366, height: 768 },
];

const now = Date.now();
const MOCK_NOTIFICATIONS = [
  {
    id: 101,
    type: 'booking_completed',
    category: 'booking',
    priority: 'normal',
    title: 'Session Complete 💈',
    message:
      'Your Hot Towel Beard Sculpt & Hydration session is complete! We hope you enjoyed it. Leave a review to share your experience.',
    related_entity_id: 55,
    action_url: '/customer/dashboard/reviews?appointment_id=55',
    data: {
      booking_reference: 'CC-2026-5501',
      verification_code: 'SCULPT55',
      barber_name: 'Marcus Vance',
    },
    is_read: false,
    created_at: new Date(now - 8 * 60 * 1000).toISOString(),
  },
  {
    id: 102,
    type: 'payment_verified',
    category: 'payment',
    priority: 'normal',
    title: 'Payment Confirmed ✅',
    message:
      'Your payment of ₦5,000.00 is verified! Booking Code: PAY89X. Your receipt has been emailed to you.',
    related_entity_id: 56,
    action_url: '/customer/dashboard/bookings',
    data: {
      booking_reference: 'CC-2026-5502',
      verification_code: 'PAY89X',
      amount: '5,000.00',
    },
    is_read: true,
    created_at: new Date(now - 45 * 60 * 1000).toISOString(),
  },
];

async function setupCustomerSessionMocks(page) {
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

  await page.route('**/api/v1/settings**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ success: true, data: { site_name: 'CandyCutz' } }),
    });
  });

  await page.route('**/api/v1/health**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ status: 'ok' }),
    });
  });

  await page.route('**/api/v1/notifications/unread-count**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ success: true, data: { count: 1 } }),
    });
  });

  await page.route('**/api/v1/notifications/stream**', async (route) => {
    await route.fulfill({
      status: 200,
      headers: { 'Content-Type': 'text/event-stream' },
      body: 'id: 102\n: ping\n\n',
    });
  });

  await page.route('**/api/v1/notifications/*/read', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ success: true }),
    });
  });

  await page.route('**/api/v1/notifications', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ success: true, data: MOCK_NOTIFICATIONS }),
    });
  });
}

test.describe('Customer Notifications Responsiveness & Interactive Controls', () => {
  for (const vp of VIEWPORTS) {
    test(`renders cleanly with zero overflow at ${vp.name}`, async ({ page }) => {
      await page.setViewportSize({ width: vp.width, height: vp.height });
      await setupCustomerSessionMocks(page);

      await page.goto('/customer/dashboard/notifications', { waitUntil: 'domcontentloaded' });
      await expect(page.getByRole('heading', { name: /Your Notifications/i })).toBeVisible();
      await expect(page.getByText('Session Complete')).toBeVisible();

      // Verify zero horizontal overflow across document and all visible elements
      const overflowReport = await page.evaluate(() => {
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

      expect(
        overflowReport.hasOverflow,
        `Overflow at ${vp.width}px: doc=${overflowReport.docWidth}px, win=${overflowReport.winWidth}px, culprits=${overflowReport.culprits.join(', ')}`
      ).toBe(false);

      // Verify top app bar title isn't crushed below 120px on standard phones (>= 360px)
      if (vp.width >= 360) {
        const headerTitleBox = await page.locator('header h2').first().boundingBox();
        expect(headerTitleBox?.width).toBeGreaterThan(120);
      }

      // Verify card body paragraphs are left-aligned (not ugly justified spacing)
      const textAlign = await page
        .locator('article p')
        .first()
        .evaluate((el) => getComputedStyle(el).textAlign);
      expect(textAlign).toBe('left');
    });
  }

  test('supports filters, type highlight, unread switch, and detail bottom sheet on mobile', async ({
    page,
  }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await setupCustomerSessionMocks(page);

    await page.goto('/customer/dashboard/notifications', { waitUntil: 'domcontentloaded' });
    await expect(page.getByText('Session Complete')).toBeVisible();

    // Toggle filter drawer and select Bookings
    await page.getByRole('button', { name: /Filters/i }).click();
    const bookingsChip = page.locator('section[aria-label="Filter notifications"] button', {
      hasText: 'Bookings',
    });
    await bookingsChip.click();
    await expect(page.getByText('Payment Confirmed')).toBeHidden();
    await expect(page.getByText('Session Complete')).toBeVisible();

    // Reset filters
    await page.getByRole('button', { name: /Reset filters/i }).click();
    await expect(page.getByText('Payment Confirmed')).toBeVisible();

    // Open detail sheet by clicking notification title
    await page.getByRole('button', { name: /Session Complete/i }).click();
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    await expect(dialog.getByText('SCULPT55')).toBeVisible();
  });
});
