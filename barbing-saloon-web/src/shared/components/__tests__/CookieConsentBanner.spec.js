import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it } from 'vitest';
import CookieConsentBanner from '../CookieConsentBanner.vue';
import { useCookieConsent } from '@/shared/composables/useCookieConsent';

describe('CookieConsentBanner.vue', () => {
  let consent;

  beforeEach(() => {
    localStorage.clear();
    consent = useCookieConsent();
    consent.resetConsent();
    consent.openBanner();
  });

  it('renders the pill banner with text and action buttons', () => {
    const wrapper = mount(CookieConsentBanner);

    const pill = wrapper.find('[data-testid="cookie-consent-pill"]');
    expect(pill.exists()).toBe(true);
    expect(pill.text()).toContain('We use cookies.');

    expect(wrapper.find('[data-testid="cookie-btn-settings"]').exists()).toBe(true);
    expect(wrapper.find('[data-testid="cookie-btn-reject"]').exists()).toBe(true);
    expect(wrapper.find('[data-testid="cookie-btn-accept"]').exists()).toBe(true);
  });

  it('clicking settings button opens the preferences modal', async () => {
    const wrapper = mount(CookieConsentBanner);

    expect(consent.isModalOpen.value).toBe(false);
    await wrapper.find('[data-testid="cookie-btn-settings"]').trigger('click');
    expect(consent.isModalOpen.value).toBe(true);
  });

  it('clicking accept button accepts all cookies and hides banner', async () => {
    const wrapper = mount(CookieConsentBanner);

    await wrapper.find('[data-testid="cookie-btn-accept"]').trigger('click');

    expect(consent.hasConsented.value).toBe(true);
    expect(consent.isBannerVisible.value).toBe(false);
    expect(consent.consentStatus.value).toBe('all');
  });

  it('clicking reject button rejects non-essential cookies and hides banner', async () => {
    const wrapper = mount(CookieConsentBanner);

    await wrapper.find('[data-testid="cookie-btn-reject"]').trigger('click');

    expect(consent.hasConsented.value).toBe(true);
    expect(consent.isBannerVisible.value).toBe(false);
    expect(consent.consentStatus.value).toBe('necessary_only');
  });

  it('remains completely hidden on the website after consent choices are made', async () => {
    consent.acceptAll();
    const wrapper = mount(CookieConsentBanner);

    expect(wrapper.find('[data-testid="cookie-consent-pill"]').exists()).toBe(false);
    expect(wrapper.find('[data-testid="cookie-consent-aside"]').exists()).toBe(false);
  });
});
