const opentype = require('opentype.js');
const fs = require('fs');
const path = require('path');

const cssPath = path.resolve(__dirname, '../node_modules/@flaticon/flaticon-uicons/css/regular/rounded.css');
const css = fs.readFileSync(cssPath, 'utf8');

const fontPath = path.resolve(__dirname, '../node_modules/@flaticon/flaticon-uicons/css/uicons-regular-rounded-KDJ23353.woff');
const buffer = fs.readFileSync(fontPath);
const font = opentype.parse(buffer.buffer.slice(buffer.byteOffset, buffer.byteOffset + buffer.byteLength));

const iconList = [
  { key: 'home', name: 'fi-rr-home' },
  { key: 'scissors', name: 'fi-rr-scissors' },
  { key: 'calendar', name: 'fi-rr-calendar' },
  { key: 'calendarCheck', name: 'fi-rr-calendar-check' },
  { key: 'calendarClock', name: 'fi-rr-calendar-clock' },
  { key: 'chair', name: 'fi-rr-chair' },
  { key: 'chairOffice', name: 'fi-rr-chair-office' },
  { key: 'clipboardList', name: 'fi-rr-clipboard-list' },
  { key: 'clock', name: 'fi-rr-clock' },
  { key: 'user', name: 'fi-rr-user' },
  { key: 'circleUser', name: 'fi-rr-circle-user' },
  { key: 'bell', name: 'fi-rr-bell' },
  { key: 'settings', name: 'fi-rr-settings' },
  { key: 'settingsSliders', name: 'fi-rr-settings-sliders' },
  { key: 'search', name: 'fi-rr-search' },
  { key: 'star', name: 'fi-rr-star' },
  { key: 'marker', name: 'fi-rr-marker' },
  { key: 'filter', name: 'fi-rr-filter' },
  { key: 'userAdd', name: 'fi-rr-user-add' },
  { key: 'check', name: 'fi-rr-check' },
  { key: 'cross', name: 'fi-rr-cross' },
  { key: 'phoneCall', name: 'fi-rr-phone-call' },
];

const results = {};

for (const { key, name } of iconList) {
  const reg = new RegExp(name + ':before{content:"\\\\([a-f0-9]+)"');
  const m = css.match(reg);
  if (!m) {
    console.log(`Class ${name} not found in css`);
    continue;
  }
  const unicodeChar = String.fromCharCode(parseInt(m[1], 16));
  const glyph = font.charToGlyph(unicodeChar);
  if (!glyph || !glyph.path) {
    console.log(`Glyph not found for ${key} (\\u${m[1]})`);
    continue;
  }
  const glyphPath = glyph.getPath(0, font.unitsPerEm, font.unitsPerEm);
  const pathData = glyphPath.toPathData(2);
  const box = glyph.getBoundingBox();
  results[key] = {
    name,
    unicode: `\\u${m[1]}`,
    pathData,
    unitsPerEm: font.unitsPerEm,
    bbox: box,
  };
  console.log(`Extracted ${key} (${name} -> \\u${m[1]}): length ${pathData.length}`);
}

fs.writeFileSync(
  path.resolve(__dirname, 'extracted_icons.json'),
  JSON.stringify(results, null, 2)
);
console.log('Saved all extracted icons to extracted_icons.json');
