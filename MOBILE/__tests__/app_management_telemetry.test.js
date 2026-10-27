import { describe, it } from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const ROOT = path.resolve(__dirname, '..');

describe('App Management, Telemetry & Version Enforcement Suite', () => {
  it('CONFIG in config.ts defines APP_VERSION', () => {
    const content = fs.readFileSync(path.join(ROOT, 'src/constants/config.ts'), 'utf8');
    assert.match(content, /APP_VERSION:\s*'1\.0\.0'/);
  });

  it('client.ts attaches version headers and handles 426 and 503 errors', () => {
    const content = fs.readFileSync(path.join(ROOT, 'src/api/client.ts'), 'utf8');
    assert.match(content, /'X-App-Version':\s*CONFIG\.APP_VERSION/);
    assert.match(content, /'X-App-Platform':\s*Platform\.OS/);
    assert.match(content, /status === 426/);
    assert.match(content, /status === 503/);
    assert.match(content, /useSystemStatusStore\.getState\(\)\.setUpgradeRequired/);
    assert.match(content, /useSystemStatusStore\.getState\(\)\.setMaintenance/);
    assert.match(content, /export const featureFlagsApi/);
    assert.match(content, /export const appTelemetryApi/);
  });

  it('telemetry service exists and defines crash reporting logic', () => {
    const content = fs.readFileSync(path.join(ROOT, 'src/services/telemetry.ts'), 'utf8');
    assert.match(content, /export async function reportCrash/);
    assert.match(content, /app\/crashes/);
    assert.match(content, /initGlobalErrorHandler/);
  });

  it('systemStatusStore exists with maintenance and upgrade state handlers', () => {
    const content = fs.readFileSync(path.join(ROOT, 'src/store/systemStatusStore.ts'), 'utf8');
    assert.match(content, /useSystemStatusStore/);
    assert.match(content, /isUpgradeRequired/);
    assert.match(content, /isMaintenance/);
    assert.match(content, /setUpgradeRequired/);
    assert.match(content, /setMaintenance/);
  });

  it('app/_layout.tsx integrates telemetry and displays upgrade / maintenance screens', () => {
    const content = fs.readFileSync(path.join(ROOT, 'app/_layout.tsx'), 'utf8');
    assert.match(content, /reportCrash\(error\)/);
    assert.match(content, /useSystemStatusStore/);
    assert.match(content, /variant="upgrade"/);
    assert.match(content, /variant="maintenance"/);
  });
});
