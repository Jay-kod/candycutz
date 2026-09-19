const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

test('ConfirmDialog Redesign Verification Suite', async (t) => {
  await t.test('ConfirmDialog component exports and defines required variants', () => {
    const dialogPath = path.resolve(__dirname, '../src/components/common/ConfirmDialog.tsx');
    assert.ok(fs.existsSync(dialogPath), 'ConfirmDialog.tsx must exist');

    const content = fs.readFileSync(dialogPath, 'utf8');
    assert.ok(content.includes('export function ConfirmDialog'), 'Must export ConfirmDialog function');
    assert.ok(content.includes('ConfirmDialogVariant'), 'Must export or define ConfirmDialogVariant');
    assert.ok(content.includes('AlertTriangle'), 'Must use Lucide AlertTriangle for danger');
    assert.ok(content.includes('AlertCircle'), 'Must use Lucide AlertCircle for warning');
    assert.ok(content.includes('HelpCircle'), 'Must use Lucide HelpCircle for primary');
    assert.ok(content.includes('cancelLabel'), 'Must support customizable cancelLabel');
    assert.ok(content.includes('confirmLabel'), 'Must support customizable confirmLabel');
    assert.ok(content.includes('backdropAnim'), 'Must use animated backdrop');
    assert.ok(content.includes('cardScaleAnim'), 'Must use spring card animation');
  });
});
