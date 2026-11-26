import { beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import ServiceVerificationModal from '../ServiceVerificationModal.vue';
import {
  extractVerificationError,
  isValidVerificationCode,
  normalizeVerificationCode,
} from '@/features/appointments/utils/verificationCode';

const toastMock = vi.hoisted(() => ({
  success: vi.fn(),
  error: vi.fn(),
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => toastMock,
}));

describe('verificationCode helpers', () => {
  it('normalizes whitespace, leading hash, and lowercase characters', () => {
    expect(normalizeVerificationCode(' cc-esnnsr ')).toBe('CC-ESNNSR');
    expect(normalizeVerificationCode('#CC - ESN NSR')).toBe('CC-ESNNSR');
    expect(normalizeVerificationCode('esnnsr')).toBe('ESNNSR');
  });

  it('validates all generated backend verification code formats', () => {
    expect(isValidVerificationCode('CC-ESNNSR')).toBe(true);
    expect(isValidVerificationCode('ESNNSR')).toBe(true);
    expect(isValidVerificationCode('CC-00001')).toBe(true);
    expect(isValidVerificationCode('CC-A1B2C3D4')).toBe(true);
    expect(isValidVerificationCode('CC-KEF-A1B2C3')).toBe(true);
    expect(isValidVerificationCode('BK-1234AB')).toBe(true);

    expect(isValidVerificationCode('')).toBe(false);
    expect(isValidVerificationCode('CC-')).toBe(false);
    expect(isValidVerificationCode('CC-12')).toBe(false);
    expect(isValidVerificationCode('CC--123456')).toBe(false);
  });

  it('extracts field errors cleanly from ApiError or Axios responses', () => {
    expect(
      extractVerificationError({
        message: 'The given data was invalid.',
        details: { field_errors: { verification_code: ['Code mismatch.'] } },
      })
    ).toBe('Code mismatch.');

    expect(extractVerificationError({ message: 'Forbidden action' })).toBe('Forbidden action');
  });
});

describe('ServiceVerificationModal.vue', () => {
  const sampleBooking = {
    id: 42,
    customer_name: 'Chidi Okafor',
    service_name: 'Signature Royal Fade',
    appointment_date: '2026-11-25',
    appointment_time: '14:00',
    status: 'confirmed',
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('displays booking context and guidance matching generated CC-XXXXXX codes', () => {
    const wrapper = mount(ServiceVerificationModal, {
      props: {
        open: true,
        booking: sampleBooking,
      },
    });

    expect(wrapper.text()).toContain('Service Verification');
    expect(wrapper.text()).toContain('CC-ESNNSR');
    expect(wrapper.text()).not.toContain('12-character');
    expect(wrapper.text()).not.toContain('ABCD-1234-EFGH');
    expect(wrapper.text()).toContain('Chidi Okafor');
    expect(wrapper.text()).toContain('Signature Royal Fade');
  });

  it('enables Verify & Complete for 9-char CC-ESNNSR and emits normalized code on submit', async () => {
    const wrapper = mount(ServiceVerificationModal, {
      props: {
        open: true,
        booking: sampleBooking,
      },
    });

    const input = wrapper.find('#service-verification-code');
    const submitBtn = wrapper.findAll('button').find((b) => b.text().includes('Verify & Complete'));

    expect(submitBtn.attributes('disabled')).toBeDefined();

    await input.setValue(' cc-esnnsr ');
    expect(submitBtn.attributes('disabled')).toBeUndefined();
    expect(wrapper.text()).toContain('Ready to verify');

    await submitBtn.trigger('click');
    expect(wrapper.emitted('submit')).toEqual([['CC-ESNNSR']]);
  });

  it('supports shorter 6-char passcodes and longer seeded codes', async () => {
    const wrapper = mount(ServiceVerificationModal, {
      props: {
        open: true,
        booking: sampleBooking,
      },
    });

    const input = wrapper.find('#service-verification-code');
    const submitBtn = wrapper.findAll('button').find((b) => b.text().includes('Verify & Complete'));

    await input.setValue('esnnsr');
    expect(submitBtn.attributes('disabled')).toBeUndefined();

    await input.setValue('CC-KEF-A1B2C3');
    expect(submitBtn.attributes('disabled')).toBeUndefined();
    await submitBtn.trigger('click');
    expect(wrapper.emitted('submit')).toEqual([['CC-KEF-A1B2C3']]);
  });

  it('keeps Verify & Complete disabled for incomplete codes', async () => {
    const wrapper = mount(ServiceVerificationModal, {
      props: {
        open: true,
        booking: sampleBooking,
      },
    });

    const input = wrapper.find('#service-verification-code');
    const submitBtn = wrapper.findAll('button').find((b) => b.text().includes('Verify & Complete'));

    await input.setValue('CC-12');
    expect(submitBtn.attributes('disabled')).toBeDefined();

    await input.trigger('keydown.enter');
    expect(wrapper.emitted('submit')).toBeFalsy();
  });

  it('pastes and normalizes code from clipboard when clicking Paste from Clipboard', async () => {
    Object.defineProperty(navigator, 'clipboard', {
      configurable: true,
      value: {
        readText: vi.fn().mockResolvedValue(' #cc-esnnsr \n'),
      },
    });

    const wrapper = mount(ServiceVerificationModal, {
      props: {
        open: true,
        booking: sampleBooking,
      },
    });

    const pasteBtn = wrapper.findAll('button').find((b) => b.text().includes('Paste from Clipboard'));
    await pasteBtn.trigger('click');
    await flushPromises();

    const input = wrapper.find('#service-verification-code');
    expect(input.element.value).toBe('CC-ESNNSR');
    expect(toastMock.success).toHaveBeenCalledWith('Code pasted');

    const submitBtn = wrapper.findAll('button').find((b) => b.text().includes('Verify & Complete'));
    expect(submitBtn.attributes('disabled')).toBeUndefined();
  });

  it('renders inline errorMessage and emits clear-error when the code is edited', async () => {
    const wrapper = mount(ServiceVerificationModal, {
      props: {
        open: true,
        booking: sampleBooking,
        errorMessage: 'Invalid verification code.',
      },
    });

    const alert = wrapper.find('[role="alert"]');
    expect(alert.exists()).toBe(true);
    expect(alert.text()).toContain('Invalid verification code.');

    const input = wrapper.find('#service-verification-code');
    await input.setValue('CC-ESNNSR');
    expect(wrapper.emitted('clear-error')).toBeTruthy();
  });
});
