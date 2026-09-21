const fs = require('fs');
const css = fs.readFileSync('node_modules/@flaticon/flaticon-uicons/css/regular/rounded.css', 'utf8');
const exact = [
  'fi-rr-home',
  'fi-rr-scissors',
  'fi-rr-calendar',
  'fi-rr-calendar-check',
  'fi-rr-calendar-clock',
  'fi-rr-chair',
  'fi-rr-chair-office',
  'fi-rr-clipboard-list',
  'fi-rr-clock',
  'fi-rr-user',
  'fi-rr-circle-user',
  'fi-rr-bell',
  'fi-rr-settings',
  'fi-rr-settings-sliders'
];

exact.forEach(name => {
  const reg = new RegExp(name + ':before{content:"\\\\([a-f0-9]+)"');
  const match = css.match(reg);
  console.log(name, '->', match ? '\\u' + match[1] : 'not found');
});

