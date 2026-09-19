const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

test('Barber Home & Pending Bookings Verification Suite', async (t) => {
  await t.test('BarberQueueView renders pending booking request stage and redirect actions', () => {
    const queueViewPath = path.resolve(__dirname, '../src/components/barber/BarberQueueView.tsx');
    assert.ok(fs.existsSync(queueViewPath), 'BarberQueueView.tsx must exist');

    const content = fs.readFileSync(queueViewPath, 'utf8');
    assert.ok(content.includes('barberPendingBookings'), 'Must query pending bookings for active barber');
    assert.ok(content.includes('pendingBannerCard'), 'Must render pending banner card on home screen');
    assert.ok(content.includes('navigateToPendingBookings'), 'Must provide navigation handler to pending bookings');
    assert.ok(content.includes('/(tabs)/appointments?status=pending'), 'Must redirect to appointments page with pending query param');
    assert.ok(content.includes('home_service'), 'Must handle home service indicator');
    assert.ok(content.includes('pendingAcceptBtn'), 'Must provide direct accept action on pending booking');
    assert.ok(content.includes('pendingDeclineBtn'), 'Must provide direct decline action on pending booking');
  });

  await t.test('BarberAppointmentsScreen supports pending filter, service modes, and accept/decline actions', () => {
    const apptsPath = path.resolve(__dirname, '../app/(tabs)/appointments.tsx');
    assert.ok(fs.existsSync(apptsPath), 'app/(tabs)/appointments.tsx must exist');

    const content = fs.readFileSync(apptsPath, 'utf8');
    assert.ok(content.includes("'pending'"), 'STATUS_FILTERS must include pending status');
    assert.ok(content.includes('useLocalSearchParams'), 'Must parse route query params for status');
    assert.ok(content.includes('Home Service'), 'Must display Home Service badge for home appointments');
    assert.ok(content.includes('In-Shop'), 'Must display In-Shop badge');
    assert.ok(content.includes('Accept Booking'), 'Must render Accept Booking button for pending requests');
    assert.ok(content.includes('handleDecline'), 'Must render Decline action for pending requests');
    assert.ok(content.includes('destination_address'), 'Must show customer location landmark for home service');
  });
});
