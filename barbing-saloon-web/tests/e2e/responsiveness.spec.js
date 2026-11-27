import { test, expect } from '@playwright/test';

const VIEWPORTS = [
  { name: 'Small Phone (320x640)', width: 320, height: 640 },
  { name: 'Standard Android (360x800)', width: 360, height: 800 },
  { name: 'iPhone-sized (390x844)', width: 390, height: 844 },
  { name: 'Large Android (393x852)', width: 393, height: 852 },
  { name: 'Large Phone (430x932)', width: 430, height: 932 },
  { name: 'Tablet (768x1024)', width: 768, height: 1024 },
  { name: 'Laptop (1366x768)', width: 1366, height: 768 },
  { name: 'Desktop (1920x1080)', width: 1920, height: 1080 },
];

const AUDIT_ROUTES = [
  { path: '/', name: 'Public Home' },
  { path: '/services', name: 'Public Services' },
  { path: '/gallery', name: 'Public Gallery' },
  { path: '/about', name: 'Public About' },
  { path: '/contact', name: 'Public Contact' },
  { path: '/privacy', name: 'Public Privacy' },
  { path: '/terms', name: 'Public Terms' },
  { path: '/cookies', name: 'Cookie Preferences' },
  { path: '/account-deletion', name: 'Account Deletion Guide' },
  { path: '/customer/login', name: 'Customer Login' },
  { path: '/customer/register', name: 'Customer Register' },
  { path: '/barber/login', name: 'Barber Login' },
  { path: '/admin/login', name: 'Admin Login' },
  { path: '/forgot-password', name: 'Forgot Password' },
];

test.describe('CandyCutz Multi-Viewport Responsiveness & Overflow Test Suite', () => {
  for (const vp of VIEWPORTS) {
    test.describe(`Viewport: ${vp.name}`, () => {
      for (const route of AUDIT_ROUTES) {
        test(`Page: ${route.name} (${route.path}) has zero horizontal overflow`, async ({ page }) => {
          await page.setViewportSize({ width: vp.width, height: vp.height });

          // Mock public settings to ensure offline repeatability
          await page.route('**/api/v1/settings**', async (r) => {
            await r.fulfill({
              status: 200,
              contentType: 'application/json',
              body: JSON.stringify({
                data: {
                  site_name: 'CandyCutz',
                  brand_name: 'CandyCutz',
                  brand_name_part_one: 'Candy',
                  brand_name_part_two: 'Cutz',
                  cta_title: 'Fresh Cut. Zero Stress.',
                  cta_title_part_one: 'Fresh Cut.',
                  cta_title_part_two: 'Zero Stress.',
                  cta_subtitle: 'Pick your barber, choose your style, lock your time.',
                  cta_primary_text: 'Book your cut',
                  hero_image_opacity: 22,
                  hero_overlay_opacity: 45,
                  hero_overlay_color: '#D4AF37',
                  phone: '+234 800 000 0000',
                  email: 'info@candycutz.com',
                  address: 'Victoria Island, Lagos, Nigeria',
                },
              }),
            });
          });

          // Mock animation settings
          await page.route('**/api/v1/animations**', async (r) => {
            await r.fulfill({
              status: 200,
              contentType: 'application/json',
              body: JSON.stringify({
                data: {
                  preset: 'balanced',
                  hero_image_opacity: 25,
                  hero_overlay_opacity: 50,
                },
              }),
            });
          });

          // Mock public services
          await page.route('**/api/v1/services**', async (r) => {
            await r.fulfill({
              status: 200,
              contentType: 'application/json',
              body: JSON.stringify({
                data: [
                  {
                    id: 1,
                    name: 'Executive Precision Cut',
                    slug: 'executive-precision-cut',
                    description: 'Top-tier precision haircut tailored to your head shape.',
                    price: 5000,
                    duration_minutes: 45,
                    category: { id: 1, name: 'Haircut', slug: 'haircut' },
                    image: null,
                  },
                  {
                    id: 2,
                    name: 'Royal Beard Sculpting',
                    slug: 'royal-beard-sculpting',
                    description: 'Hot towel prep, organic beard oil hydration, razor edge finishing.',
                    price: 3500,
                    duration_minutes: 30,
                    category: { id: 2, name: 'Beard', slug: 'beard' },
                    image: null,
                  },
                ],
              }),
            });
          });

          // Mock public barbers
          await page.route('**/api/v1/barbers**', async (r) => {
            await r.fulfill({
              status: 200,
              contentType: 'application/json',
              body: JSON.stringify({
                data: [
                  {
                    id: 1,
                    name: 'Marcus Master-Cutter',
                    specialty: 'Fades & Textures',
                    experience_years: 8,
                    rating: 4.9,
                    avatar: null,
                  },
                ],
              }),
            });
          });

          // Mock testimonials
          await page.route('**/api/v1/testimonials**', async (r) => {
            await r.fulfill({
              status: 200,
              contentType: 'application/json',
              body: JSON.stringify({
                data: [
                  {
                    id: 1,
                    client_name: 'Damilola Adebayo',
                    rating: 5,
                    comment: 'Best cut in Lagos. Punctual, professional, and immaculate attention to detail.',
                  },
                ],
              }),
            });
          });

          // Navigate to target route
          await page.goto(route.path, { waitUntil: 'domcontentloaded' });
          await page.waitForTimeout(300); // Allow responsive hydration

          // Check for horizontal overflow
          const overflowReport = await page.evaluate(() => {
            const docWidth = Math.max(
              document.documentElement.scrollWidth,
              document.body.scrollWidth
            );
            const winWidth = window.innerWidth;
            const hasDocOverflow = docWidth > winWidth + 1.5; // 1.5px subpixel tolerance

            let worstOverflow = 0;
            const overflowingElements = [];

            const elements = document.querySelectorAll('*');
            for (const el of elements) {
              const rect = el.getBoundingClientRect();
              if (rect.right > winWidth + 1.5) {
                const diff = rect.right - winWidth;
                if (diff > worstOverflow) worstOverflow = diff;
                const tag = el.tagName.toLowerCase();
                const id = el.id ? `#${el.id}` : '';
                const cls = typeof el.className === 'string' && el.className.trim()
                  ? `.${el.className.trim().split(/\s+/).slice(0, 3).join('.')}`
                  : '';
                overflowingElements.push(`${tag}${id}${cls} (overflow: +${Math.round(diff)}px)`);
              }
            }

            return {
              hasDocOverflow,
              docWidth,
              winWidth,
              worstOverflow: Math.round(worstOverflow),
              culprits: overflowingElements.slice(0, 5),
            };
          });

          expect(
            overflowReport.hasDocOverflow,
            `\n🚨 Horizontal overflow detected on "${route.name}" (${route.path}) at ${vp.width}px viewport!\n` +
            `Document width: ${overflowReport.docWidth}px, Viewport: ${overflowReport.winWidth}px (+${overflowReport.worstOverflow}px)\n` +
            `Offending elements:\n  ${overflowReport.culprits.join('\n  ')}\n`
          ).toBe(false);
        });
      }
    });
  }
});
