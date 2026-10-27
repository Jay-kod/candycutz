const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

test('Toast & ActionDialog System Verification Suite', async (t) => {
  const root = path.resolve(__dirname, '..');

  await t.test('toastStore.ts exists and defines store interface', () => {
    const filePath = path.join(root, 'src/store/toastStore.ts');
    assert.ok(fs.existsSync(filePath), 'toastStore.ts must exist');

    const content = fs.readFileSync(filePath, 'utf8');
    assert.ok(content.includes('export const useToastStore'), 'Must export useToastStore');
    assert.ok(content.includes('ToastVariant'), 'Must export ToastVariant');
    assert.ok(content.includes('ToastConfig'), 'Must export ToastConfig');
    assert.ok(content.includes('show:'), 'Must implement show');
    assert.ok(content.includes('dismiss:'), 'Must implement dismiss');
    assert.ok(content.includes('current:'), 'Must maintain current toast state');
  });

  await t.test('AppToast.tsx exists with 4 variants, spring animation, and swipe dismiss', () => {
    const filePath = path.join(root, 'src/components/common/AppToast.tsx');
    assert.ok(fs.existsSync(filePath), 'AppToast.tsx must exist');

    const content = fs.readFileSync(filePath, 'utf8');
    assert.ok(content.includes('export function AppToast'), 'Must export AppToast');
    assert.ok(content.includes('success:'), 'Must configure success variant');
    assert.ok(content.includes('error:'), 'Must configure error variant');
    assert.ok(content.includes('warning:'), 'Must configure warning variant');
    assert.ok(content.includes('info:'), 'Must configure info variant');
    assert.ok(content.includes('PanResponder'), 'Must support swipe dismiss via PanResponder');
    assert.ok(content.includes('Animated.spring') || content.includes('Animated.timing'), 'Must use animations');
  });

  await t.test('ActionDialog.tsx exists with multi-button action support', () => {
    const filePath = path.join(root, 'src/components/common/ActionDialog.tsx');
    assert.ok(fs.existsSync(filePath), 'ActionDialog.tsx must exist');

    const content = fs.readFileSync(filePath, 'utf8');
    assert.ok(content.includes('export function ActionDialog'), 'Must export ActionDialog');
    assert.ok(content.includes('ActionDialogAction'), 'Must support ActionDialogAction array');
    assert.ok(content.includes('backdropAnim'), 'Must animate backdrop');
    assert.ok(content.includes('cardScaleAnim'), 'Must animate card scale');
  });

  await t.test('AppToast is globally mounted in app/_layout.tsx', () => {
    const layoutPath = path.join(root, 'app/_layout.tsx');
    assert.ok(fs.existsSync(layoutPath), 'app/_layout.tsx must exist');

    const content = fs.readFileSync(layoutPath, 'utf8');
    assert.ok(content.includes('import { AppToast }'), 'Must import AppToast in _layout.tsx');
    assert.ok(content.includes('<AppToast />'), 'Must mount <AppToast /> in _layout.tsx');
  });

  await t.test('All 13 screens and components have replaced Alert.alert with AppToast or ActionDialog', () => {
    const targetFiles = [
      'app/(tabs)/profile.tsx',
      'app/(tabs)/bookings.tsx',
      'app/(tabs)/appointments.tsx',
      'app/(tabs)/schedule.tsx',
      'app/walkin.tsx',
      'app/book/[serviceId].tsx',
      'app/profile/edit.tsx',
      'app/barber/profile-edit.tsx',
      'app/profile/settings.tsx',
      'app/auth/forgot-password.tsx',
      'app/auth/login.tsx',
      'src/components/barber/BarberQueueView.tsx',
      'src/components/customer/CustomerHomeView.tsx',
    ];

    for (const relPath of targetFiles) {
      const fullPath = path.join(root, relPath);
      assert.ok(fs.existsSync(fullPath), `${relPath} must exist`);

      const content = fs.readFileSync(fullPath, 'utf8');
      assert.ok(
        !content.includes('Alert.alert('),
        `${relPath} must not contain any Alert.alert calls`
      );
      assert.ok(
        content.includes('useToastStore') || content.includes('ActionDialog'),
        `${relPath} must import useToastStore or ActionDialog`
      );
    }
  });

  await t.test('Zero remaining Alert.alert calls in app/ and src/ trees', () => {
    function findAlertsInDir(dir) {
      const entries = fs.readdirSync(dir, { withFileTypes: true });
      const violations = [];

      for (const entry of entries) {
        const fullPath = path.join(dir, entry.name);
        if (entry.isDirectory()) {
          violations.push(...findAlertsInDir(fullPath));
        } else if (/\.(tsx?|jsx?)$/.test(entry.name)) {
          const content = fs.readFileSync(fullPath, 'utf8');
          if (content.includes('Alert.alert(')) {
            violations.push(fullPath);
          }
        }
      }
      return violations;
    }

    const appViolations = findAlertsInDir(path.join(root, 'app'));
    const srcViolations = findAlertsInDir(path.join(root, 'src'));
    const allViolations = [...appViolations, ...srcViolations];

    assert.deepEqual(
      allViolations,
      [],
      `Found residual Alert.alert calls in: ${allViolations.join(', ')}`
    );
  });
});
