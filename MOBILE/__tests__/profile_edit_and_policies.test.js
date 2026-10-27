import { describe, it } from 'node:test';
import assert from 'node:assert';
import fs from 'node:fs';
import path from 'node:path';

const ROOT = path.resolve(import.meta.dirname, '..');

describe('Profile Edit, Username Cooldown, Error Pages & Policies Suite', () => {
  it('User interface in types.ts includes last_username_change_at', () => {
    const content = fs.readFileSync(path.join(ROOT, 'src/api/types.ts'), 'utf-8');
    assert.match(content, /last_username_change_at\?:/);
  });

  it('client.ts updateProfile accepts username and exports cmsApi', () => {
    const content = fs.readFileSync(path.join(ROOT, 'src/api/client.ts'), 'utf-8');
    assert.match(content, /username\?: string/);
    assert.match(content, /export const cmsApi\b/);
    assert.match(content, /getSettings:\s*async/);
  });

  it('edit.tsx implements username editing with 60-day cooldown and field-level error display', () => {
    const content = fs.readFileSync(path.join(ROOT, 'app/profile/edit.tsx'), 'utf-8');
    // Username state & cooldown
    assert.match(content, /const \[username, setUsername\] = useState/);
    assert.match(content, /getUsernameCooldown/);
    assert.match(content, /diffDays < 60/);
    assert.match(content, /usernameCooldownDays/);
    // Field errors state & extraction
    assert.match(content, /const \[fieldErrors, setFieldErrors\] = useState/);
    assert.match(content, /errorsObj/);
    assert.match(content, /extractedFieldErrors/);
    // Field component with error & badge props
    assert.match(content, /error=\{fieldErrors\.name\}/);
    assert.match(content, /error=\{fieldErrors\.username\}/);
    assert.match(content, /error=\{fieldErrors\.email\}/);
    assert.match(content, /styles\.inputError/);
    assert.match(content, /styles\.errorText/);
  });

  it('ErrorScreen.tsx component is implemented with all required variants', () => {
    const content = fs.readFileSync(path.join(ROOT, 'src/components/common/ErrorScreen.tsx'), 'utf-8');
    assert.match(content, /export function ErrorScreen/);
    assert.match(content, /'network'/);
    assert.match(content, /'server'/);
    assert.match(content, /'notFound'/);
    assert.match(content, /'forbidden'/);
    assert.match(content, /'maintenance'/);
    assert.match(content, /'generic'/);
    assert.match(content, /onRetry/);
    assert.match(content, /onGoBack/);
    assert.match(content, /onGoHome/);
  });

  it('+not-found.tsx route renders ErrorScreen with notFound variant', () => {
    const content = fs.readFileSync(path.join(ROOT, 'app/+not-found.tsx'), 'utf-8');
    assert.match(content, /ErrorScreen/);
    assert.match(content, /variant="notFound"/);
  });

  it('Root layout exports ErrorBoundary and registers policy & not-found routes', () => {
    const content = fs.readFileSync(path.join(ROOT, 'app/_layout.tsx'), 'utf-8');
    assert.match(content, /export function ErrorBoundary/);
    assert.match(content, /name="policy\/terms"/);
    assert.match(content, /name="policy\/privacy"/);
    assert.match(content, /name="\+not-found"/);
  });

  it('Terms and Privacy policy screens exist and consume cmsApi', () => {
    const terms = fs.readFileSync(path.join(ROOT, 'app/policy/terms.tsx'), 'utf-8');
    assert.match(terms, /cmsApi\.getSettings/);
    assert.match(terms, /terms_sections/);
    assert.match(terms, /Terms of Service/);

    const privacy = fs.readFileSync(path.join(ROOT, 'app/policy/privacy.tsx'), 'utf-8');
    assert.match(privacy, /cmsApi\.getSettings/);
    assert.match(privacy, /privacy_sections/);
    assert.match(privacy, /Privacy Policy/);
  });

  it('Settings screen includes links to Terms of Service and Privacy Policy', () => {
    const content = fs.readFileSync(path.join(ROOT, 'app/profile/settings.tsx'), 'utf-8');
    assert.match(content, /Legal & Policies/);
    assert.match(content, /router\.push\('\/policy\/terms'/);
    assert.match(content, /router\.push\('\/policy\/privacy'/);
  });
});
