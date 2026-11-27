import { effectScope } from 'vue';
import { describe, expect, it, vi } from 'vitest';
import { useAdminIntegrations } from '../useAdminIntegrations';

const clientMock = vi.hoisted(() => ({
  get: vi.fn(),
  post: vi.fn()
}));

vi.mock('@/shared/api/client', () => ({
  default: clientMock
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({ success: vi.fn(), error: vi.fn() })
}));

describe('useAdminIntegrations email status', () => {
  it('requires a Resend API key and sender instead of SMTP defaults', () => {
    const scope = effectScope();
    let integrations;
    scope.run(() => {
      integrations = useAdminIntegrations();
    });

    integrations.form.mail_host = 'smtp.resend.com';
    integrations.form.mail_password = 'smtp-password';
    integrations.form.mail_from = 'noreply@candycutz.com';
    expect(integrations.mailConfigured.value).toBe(false);

    integrations.form.resend_api_key = '••••••••5678';
    expect(integrations.mailConfigured.value).toBe(true);

    scope.stop();
  });
});
