const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

test('Flaticon UIcons Interface Icons Suite', async (t) => {
  await t.test('UIcon component exports required Flaticon UIcons and vector paths', () => {
    const iconPath = path.resolve(__dirname, '../src/components/common/UIcon.tsx');
    assert.ok(fs.existsSync(iconPath), 'UIcon.tsx must exist');

    const content = fs.readFileSync(iconPath, 'utf8');
    assert.ok(content.includes('export const UIcon'), 'Must export UIcon component');
    assert.ok(content.includes('UICON_PATHS'), 'Must define UICON_PATHS');

    const requiredIcons = [
      'home',
      'scissors',
      'calendar',
      'calendarCheck',
      'calendarClock',
      'chair',
      'clipboardList',
      'clock',
      'user',
      'bell',
      'settings',
      'settingsSliders',
      'search',
      'star',
      'marker',
      'filter',
      'userAdd',
      'check',
      'cross',
      'phoneCall',
    ];

    for (const iconName of requiredIcons) {
      assert.ok(content.includes(`'${iconName}'`), `UIconName must include '${iconName}'`);
      assert.ok(content.includes(`${iconName}: 'M`), `UICON_PATHS must contain SVG path for '${iconName}'`);
    }
  });

  await t.test('Tab navigation consumes Flaticon UIcons', () => {
    const tabLayoutPath = path.resolve(__dirname, '../app/(tabs)/_layout.tsx');
    const content = fs.readFileSync(tabLayoutPath, 'utf8');

    assert.ok(content.includes("import { UIcon"), 'Tab layout must import UIcon');
    assert.ok(content.includes("name={isBarber ? 'chair' : 'home'}"), 'Home tab must use chair/home UIcon');
    assert.ok(content.includes("name=\"scissors\""), 'Services tab must use scissors UIcon');
    assert.ok(content.includes("name=\"calendar\""), 'Bookings tab must use calendar UIcon');
    assert.ok(content.includes("name=\"clipboardList\""), 'Appointments tab must use clipboardList UIcon');
    assert.ok(content.includes("name=\"calendarClock\""), 'Schedule tab must use calendarClock UIcon');
    assert.ok(content.includes("name=\"user\""), 'Profile tab must use user UIcon');
  });

  await t.test('Header consumes Flaticon UIcons for bell notification', () => {
    const headerPath = path.resolve(__dirname, '../src/components/common/Header.tsx');
    const content = fs.readFileSync(headerPath, 'utf8');

    assert.ok(content.includes("import { UIcon }"), 'Header must import UIcon');
    assert.ok(content.includes('name="bell"'), 'Header must render bell UIcon');
  });
});
