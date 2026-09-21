const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

test('Customer Home Screen Verification Suite', async (t) => {
  await t.test('CustomerHomeView renders luxury customer home components and actions', () => {
    const homeViewPath = path.resolve(__dirname, '../src/components/customer/CustomerHomeView.tsx');
    assert.ok(fs.existsSync(homeViewPath), 'CustomerHomeView.tsx must exist');

    const content = fs.readFileSync(homeViewPath, 'utf8');

    // 1. Header & Greeting
    assert.ok(content.includes('Header showLocationBadge={false}'), 'Must use clean Header without duplicate location banner');
    assert.ok(content.includes('greetingTitle'), 'Must render personalized greeting title');
    assert.ok(content.includes('branchStatusPill'), 'Must render Keffi branch status pill');

    // 2. Next Appointment Spotlight
    assert.ok(content.includes('spotlightCard'), 'Must support upcoming appointment spotlight card');
    assert.ok(content.includes('CHAIR VERIFICATION CODE'), 'Must render chair verification code section');
    assert.ok(content.includes('handleCopyCode'), 'Must support one-tap verification code viewing/copying');

    // 3. Hero & Quick Actions
    assert.ok(content.includes('heroCard'), 'Must render luxury hero booking banner when no active appointment');
    assert.ok(content.includes('quickGrid'), 'Must render quick navigation tiles');
    assert.ok(content.includes('Services'), 'Must have Services quick action');
    assert.ok(content.includes('Bookings'), 'Must have Bookings quick action');
    assert.ok(content.includes('Concierge'), 'Must have Concierge quick action');
    assert.ok(content.includes('Location'), 'Must have Location quick action');

    // 4. Popular Services & Master Stylists
    assert.ok(content.includes('featuredServices'), 'Must display popular services');
    assert.ok(content.includes('barberAvatar'), 'Must render master stylists carousel with avatar monograms');
    assert.ok(content.includes('conciergeCard'), 'Must render VIP concierge home service card');
    assert.ok(content.includes('loungeCard'), 'Must render Keffi flagship lounge info card');
  });
});
