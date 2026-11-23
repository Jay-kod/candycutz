import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it } from 'vitest';
import UserProfileCookiePreferencesCard from '../UserProfileCookiePreferencesCard.vue';
import { useCookieConsent } from '@/shared/composables/useCookieConsent';

describe('UserProfileCookiePreferencesCard.vue', () => {
  let consent;

  beforeEach(() => {
    localStorage.clear();
    consent = useCookieConsent();
    consent.resetConsent();
  });

  it('renders title, live state badge, and all 4 categories', () => {
    const wrapper = mount(UserProfileCookiePreferencesCard);

    expect(wrapper.find('[data-testid="user-profile-cookie-card"]').exists()).toBe(true);
    expect(wrapper.text()).toContain('Cookie & Tracking Preferences');
    expect(wrapper.find('[data-testid="profile-cookie-cat-necessary"]').exists()).toBe(true);
    expect(wrapper.find('[data-testid="profile-cookie-cat-preferences"]').exists()).toBe(true);
    expect(wrapper.find('[data-testid="profile-cookie-cat-analytics"]').exists()).toBe(true);
    expect(wrapper.find('[data-testid="profile-cookie-cat-marketing"]').exists()).toBe(true);
  });

  it('strictly necessary category switch is locked and always active', () => {
    const wrapper = mount(UserProfileCookiePreferencesCard);

    const necessarySwitch = wrapper.find('[data-testid="profile-cookie-cat-necessary"] button[role="switch"]');
    expect(necessarySwitch.attributes('disabled')).toBeDefined();
    expect(necessarySwitch.attributes('aria-checked')).toBe('true');
  });

  it('handles Allow All button to grant all categories', async () => {
    const wrapper = mount(UserProfileCookiePreferencesCard);

    const allowAllBtn = wrapper.findAll('button').find((b) => b.text().includes('Allow All'));
    expect(allowAllBtn).toBeDefined();
    await allowAllBtn.trigger('click');

    expect(consent.hasConsented.value).toBe(true);
    expect(consent.consentStatus.value).toBe('all');
    expect(wrapper.text()).toContain('All Cookies Allowed');
  });

  it('handles Essential Only button to decline optional categories', async () => {
    consent.acceptAll(); // First accept all
    const wrapper = mount(UserProfileCookiePreferencesCard);

    const essentialBtn = wrapper.findAll('button').find((b) => b.text().includes('Essential Only'));
    expect(essentialBtn).toBeDefined();
    await essentialBtn.trigger('click');

    expect(consent.hasConsented.value).toBe(true);
    expect(consent.consentStatus.value).toBe('necessary_only');
    expect(wrapper.text()).toContain('Essential Only Allowed');
  });

  it('saves custom preferences when save button is clicked', async () => {
    const wrapper = mount(UserProfileCookiePreferencesCard);

    const saveBtn = wrapper.find('[data-testid="profile-cookie-save"]');
    await saveBtn.trigger('click');

    expect(consent.hasConsented.value).toBe(true);
  });

  it('handles Reset button to re-arm the website consent banner', async () => {
    consent.acceptAll();
    const wrapper = mount(UserProfileCookiePreferencesCard);

    const resetBtn = wrapper.findAll('button').find((b) => b.text().includes('Reset to Website Prompt'));
    expect(resetBtn).toBeDefined();
    await resetBtn.trigger('click');

    expect(consent.hasConsented.value).toBe(false);
    expect(consent.isBannerVisible.value).toBe(true);
  });
});
