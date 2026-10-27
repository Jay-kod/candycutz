const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

test('Login screen demo credentials match seeded backend accounts', () => {
  const loginPath = path.resolve(__dirname, '../app/auth/login.tsx');
  assert.ok(fs.existsSync(loginPath), 'login.tsx must exist');

  const content = fs.readFileSync(loginPath, 'utf8');

  assert.ok(content.includes('fonetestcuz@candycutz.com'), 'Customer demo should use the designated customer email');
  assert.ok(content.includes('customer123'), 'Customer demo should use the designated customer password');
  assert.ok(content.includes('fonetestbar@candycutz.com'), 'Barber demo should use the designated barber email');
  assert.ok(content.includes('barber123'), 'Barber demo should use the designated barber password');

  assert.ok(!content.includes('customer@candycutz.com'), 'Login screen should not use stale demo customer email');
  assert.ok(!content.includes('marcus@candycutz.com'), 'Login screen should not use stale demo barber email');
});
