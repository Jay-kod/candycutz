import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it } from 'vitest';
import CookiePreferencesModal from '../CookiePreferencesModal.vue';
import { useCookieConsent } from '@/shared/composables/useCookieConsent';

describe('CookiePreferencesModal.vue', () => {
  let consent;

  beforeEach(() => {
    localStorage.clear();
    consent = useCookieConsent();
    consent.resetConsent();
    consent.openModal();
  });

  it('renders modal when isModalOpen is true', () => {
    const wrapper = mount(CookiePreferencesModal, {
      global: {
        stubs: {
          Teleport: true,
          RouterLink: true,
        },
      },
    });

    const modal = wrapper.find('[data-testid="cookie-preferences-modal"]');
    expect(modal.exists()).toBe(true);
    expect(modal.text()).toContain('Cookie & Privacy Preferences');
  });

  it('displays all 4 consent categories with necessary locked', () => {
    const wrapper = mount(CookiePreferencesModal, {
      global: {
        stubs: {
          Teleport: true,
          RouterLink: true,
        },
      },
    });

    expect(wrapper.find('[data-testid="cookie-category-necessary"]').exists()).toBe(true);
    expect(wrapper.find('[data-testid="cookie-category-preferences"]').exists()).toBe(true);
    expect(wrapper.find('[data-testid="cookie-category-analytics"]').exists()).toBe(true);
    expect(wrapper.find('[data-testid="cookie-category-marketing"]').exists()).toBe(true);

    const necessarySwitch = wrapper.find('[data-testid="cookie-category-necessary"] button[role="switch"]');
    expect(necessarySwitch.attributes('disabled')).toBeDefined();
    expect(necessarySwitch.attributes('aria-checked')).toBe('true');
  });

  it('accept all button approves all categories and closes modal', async () => {
    const wrapper = mount(CookiePreferencesModal, {
      global: {
        stubs: {
          Teleport: true,
          RouterLink: true,
        },
      },
    });

    await wrapper.find('[data-testid="cookie-modal-accept"]').trigger('click');

    expect(consent.hasConsented.value).toBe(true);
    expect(consent.consentStatus.value).toBe('all');
    expect(consent.isModalOpen.value).toBe(false);
  });

  it('reject non-essential button limits to strictly necessary and closes modal', async () => {
    const wrapper = mount(CookiePreferencesModal, {
      global: {
        stubs: {
          Teleport: true,
          RouterLink: true,
        },
      },
    });

    await wrapper.find('[data-testid="cookie-modal-reject"]').trigger('click');

    expect(consent.hasConsented.value).toBe(true);
    expect(consent.consentStatus.value).toBe('necessary_only');
    expect(consent.isModalOpen.value).toBe(false);
  });

  it('close button dismisses modal without altering current consent', async () => {
    const wrapper = mount(CookiePreferencesModal, {
      global: {
        stubs: {
          Teleport: true,
          RouterLink: true,
        },
      },
    });

    await wrapper.find('[data-testid="cookie-modal-close"]').trigger('click');
    expect(consent.isModalOpen.value).toBe(false);
  });
});
