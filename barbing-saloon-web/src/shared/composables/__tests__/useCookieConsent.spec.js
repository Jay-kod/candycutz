import { beforeEach, describe, expect, it, vi } from 'vitest';
import {
  useCookieConsent,
  STORAGE_KEY,
  CONSENT_EVENT,
  initCookieConsent,
} from '../useCookieConsent';

describe('useCookieConsent', () => {
  beforeEach(() => {
    localStorage.clear();
    const consent = useCookieConsent();
    consent.resetConsent();
  });

  it('initializes with default necessary-only state when nothing is in localStorage', () => {
    const consent = useCookieConsent();
    expect(consent.preferences.necessary).toBe(true);
    expect(consent.preferences.preferences).toBe(false);
    expect(consent.preferences.analytics).toBe(false);
    expect(consent.preferences.marketing).toBe(false);
    expect(consent.hasConsented.value).toBe(false);
  });

  it('acceptAll allows all categories and saves to localStorage', () => {
    const consent = useCookieConsent();
    const listener = vi.fn();
    window.addEventListener(CONSENT_EVENT, listener);

    consent.acceptAll();

    expect(consent.preferences.necessary).toBe(true);
    expect(consent.preferences.preferences).toBe(true);
    expect(consent.preferences.analytics).toBe(true);
    expect(consent.preferences.marketing).toBe(true);
    expect(consent.hasConsented.value).toBe(true);
    expect(consent.isBannerVisible.value).toBe(false);
    expect(consent.consentStatus.value).toBe('all');

    const stored = JSON.parse(localStorage.getItem(STORAGE_KEY));
    expect(stored.preferences).toBe(true);
    expect(stored.analytics).toBe(true);
    expect(stored.marketing).toBe(true);
    expect(listener).toHaveBeenCalled();
  });

  it('rejectNonEssential disables all optional categories', () => {
    const consent = useCookieConsent();
    consent.acceptAll(); // First accept all
    expect(consent.consentStatus.value).toBe('all');

    consent.rejectNonEssential();

    expect(consent.preferences.necessary).toBe(true);
    expect(consent.preferences.preferences).toBe(false);
    expect(consent.preferences.analytics).toBe(false);
    expect(consent.preferences.marketing).toBe(false);
    expect(consent.hasConsented.value).toBe(true);
    expect(consent.consentStatus.value).toBe('necessary_only');

    const stored = JSON.parse(localStorage.getItem(STORAGE_KEY));
    expect(stored.preferences).toBe(false);
    expect(stored.analytics).toBe(false);
    expect(stored.marketing).toBe(false);
  });

  it('savePreferences stores custom configuration', () => {
    const consent = useCookieConsent();
    consent.savePreferences({
      preferences: true,
      analytics: false,
      marketing: true,
    });

    expect(consent.preferences.preferences).toBe(true);
    expect(consent.preferences.analytics).toBe(false);
    expect(consent.preferences.marketing).toBe(true);
    expect(consent.consentStatus.value).toBe('custom');
  });

  it('openModal and closeModal manage modal visibility correctly', () => {
    const consent = useCookieConsent();
    expect(consent.isModalOpen.value).toBe(false);
    consent.openModal();
    expect(consent.isModalOpen.value).toBe(true);
    consent.closeModal();
    expect(consent.isModalOpen.value).toBe(false);
  });

  it('resetConsent clears stored consent and marks as unconsented', () => {
    const consent = useCookieConsent();
    consent.acceptAll();
    expect(consent.hasConsented.value).toBe(true);

    consent.resetConsent();
    expect(consent.hasConsented.value).toBe(false);
    expect(consent.isBannerVisible.value).toBe(true);
    expect(localStorage.getItem(STORAGE_KEY)).toBeNull();
  });
});
