import { test, expect } from '@playwright/test';

const VIEWPORTS = [
  { name: 'Small Phone (320x640)', width: 320, height: 640 },
  { name: 'Standard Android (360x800)', width: 360, height: 800 },
  { name: 'iPhone (390x844)', width: 390, height: 844 },
  { name: 'Large Phone (430x932)', width: 430, height: 932 },
  { name: 'Tablet (768x1024)', width: 768, height: 1024 },
  { name: 'Desktop (1366x768)', width: 1366, height: 768 },
];

async function setupCustomerProfileMocks(page) {
  await page.addInitScript(() => {
    localStorage.setItem('candycutz_auth_token', 'e2e-mock-token');
    localStorage.setItem(
      'candycutz_cached_user',
      JSON.stringify({
        id: 10,
        name: 'Adebayo Client',
        email: 'christopher.alexander.client@candycutz.com',
        role: 'customer',
        is_email_verified: false,
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
          email: 'christopher.alexander.client@candycutz.com',
          role: 'customer',
          is_email_verified: false,
        },
      }),
    });
  });

  await page.route('**/api/v1/auth/email/status**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ success: true, data: { is_email_verified: false } }),
    });
  });

  await page.route('**/api/v1/auth/email/verification-notification**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ success: true, message: 'Code dispatched' }),
    });
  });

  await page.route('**/api/v1/customer/profile**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        success: true,
        data: {
          profile: {
            name: 'Adebayo Client',
            username: 'adebayo_c',
            email: 'christopher.alexander.client@candycutz.com',
            phone: '+2348012345678',
            bio: 'Low skin fade with crisp razor lineup and hot towel finish.',
            avatar_url: null,
          },
          history: {
            latest_bookings: [
              {
                id: 77,
                service: { name: 'Hot Towel Beard Sculpt & Hydration' },
                appointment_date: '2026-11-25',
                appointment_time: '12:25 PM',
                status: 'completed',
              },
            ],
          },
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

test.describe('Customer Profile Mobile Display Responsiveness', () => {
  for (const vp of VIEWPORTS) {
    test(`renders cleanly with zero horizontal overflow at ${vp.name}`, async ({ page }) => {
      await page.setViewportSize({ width: vp.width, height: vp.height });
      await setupCustomerProfileMocks(page);

      await page.goto('/customer/dashboard/profile', { waitUntil: 'domcontentloaded' });
      await expect(page.getByRole('heading', { name: /Account Settings/i })).toBeVisible();
      await expect(page.getByText('Hot Towel Beard Sculpt & Hydration')).toBeVisible();

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

      // Verify paragraphs stay left-aligned on mobile instead of stretched justification
      if (vp.width < 768) {
        const descAlign = await page
          .locator('section p')
          .nth(1)
          .evaluate((el) => getComputedStyle(el).textAlign);
        expect(descAlign).toBe('left');
      }
    });
  }

  test('stacks OTP input & action buttons cleanly without clipping on 320px phone', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 640 });
    await setupCustomerProfileMocks(page);

    await page.goto('/customer/dashboard/profile', { waitUntil: 'domcontentloaded' });
    await page.getByRole('button', { name: /Send Verification Code/i }).click();

    const otpInput = page.locator('input[autocomplete="one-time-code"]');
    await expect(otpInput).toBeVisible();
    const inputBox = await otpInput.boundingBox();
    expect(inputBox?.width).toBeGreaterThan(220);

    await expect(page.getByRole('button', { name: /Paste/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /Verify/i })).toBeVisible();
  });
});
