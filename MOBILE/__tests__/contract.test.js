const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

test('Contract & Mobile Verification Suite', async (t) => {
  await t.test('OpenAPI specification is valid and complete', () => {
    const specPath = path.resolve(__dirname, '../../docs/openapi.json');
    assert.ok(fs.existsSync(specPath), 'docs/openapi.json must exist');

    const spec = JSON.parse(fs.readFileSync(specPath, 'utf8'));
    assert.equal(spec.openapi, '3.0.3');
    assert.equal(spec.info.title, 'CandyCutz API');
    assert.equal(spec.info.version, '1.0.0');

    // Verify critical component schemas
    const requiredSchemas = [
      'ApiResponse',
      'ApiErrorResponse',
      'User',
      'Barber',
      'Service',
      'ServiceZone',
      'Appointment',
      'Payment',
      'DeviceToken',
      'Notification',
      'Customer',
      'Address',
      'LoginRequest',
      'RegisterRequest',
      'CreateBookingRequest',
      'InitiateCheckoutRequest',
      'RegisterDeviceTokenRequest',
    ];

    const schemas = spec.components?.schemas || {};
    for (const schemaName of requiredSchemas) {
      assert.ok(schemas[schemaName], `Missing expected component schema: ${schemaName}`);
    }

    // Verify paths count
    const paths = Object.keys(spec.paths || {});
    assert.ok(paths.length >= 50, `Expected at least 50 paths, found ${paths.length}`);
    assert.ok(spec.paths['/api/v1/notifications/device-token'], 'Missing /api/v1/notifications/device-token endpoint');
    assert.ok(spec.paths['/api/v1/appointments'], 'Missing /api/v1/appointments endpoint');
    assert.ok(spec.paths['/api/v1/services'], 'Missing /api/v1/services endpoint');
  });

  await t.test('Generated mobile types match OpenAPI specification', () => {
    const typesPath = path.resolve(__dirname, '../src/api/types.ts');
    assert.ok(fs.existsSync(typesPath), 'src/api/types.ts must exist');

    const content = fs.readFileSync(typesPath, 'utf8');
    assert.ok(content.includes('export interface User'), 'types.ts must export User interface');
    assert.ok(content.includes('export interface Barber'), 'types.ts must export Barber interface');
    assert.ok(content.includes('export interface Appointment'), 'types.ts must export Appointment interface');
    assert.ok(content.includes('export interface Payment'), 'types.ts must export Payment interface');
    assert.ok(content.includes('export interface DeviceToken'), 'types.ts must export DeviceToken interface');
    assert.ok(content.includes('export interface Notification'), 'types.ts must export Notification interface');
  });

  await t.test('API Client exports all required endpoints including notificationsApi', () => {
    const clientPath = path.resolve(__dirname, '../src/api/client.ts');
    assert.ok(fs.existsSync(clientPath), 'src/api/client.ts must exist');

    const content = fs.readFileSync(clientPath, 'utf8');
    assert.ok(content.includes('export const authApi'), 'client.ts must export authApi');
    assert.ok(content.includes('export const servicesApi'), 'client.ts must export servicesApi');
    assert.ok(content.includes('export const barbersApi'), 'client.ts must export barbersApi');
    assert.ok(content.includes('export const bookingsApi'), 'client.ts must export bookingsApi');
    assert.ok(content.includes('export const zonesApi'), 'client.ts must export zonesApi');
    assert.ok(content.includes('export const notificationsApi'), 'client.ts must export notificationsApi');
    assert.ok(content.includes('registerDeviceToken:'), 'notificationsApi must have registerDeviceToken');
    assert.ok(content.includes('deleteDeviceToken:'), 'notificationsApi must have deleteDeviceToken');
  });

  await t.test('Push notification service is properly configured', () => {
    const servicePath = path.resolve(__dirname, '../src/services/notifications.ts');
    assert.ok(fs.existsSync(servicePath), 'src/services/notifications.ts must exist');

    const content = fs.readFileSync(servicePath, 'utf8');
    assert.ok(content.includes('registerForPushNotifications'), 'notifications.ts must implement registerForPushNotifications');
    assert.ok(content.includes('unregisterDeviceToken'), 'notifications.ts must implement unregisterDeviceToken');
  });
});
