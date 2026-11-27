import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import CookiePreferencesPage from '../CookiePreferencesPage.vue';
import { useCookieConsent } from '@/shared/composables/useCookieConsent';

vi.mock('../../../core/layouts/PublicLayout.vue', () => ({
  default: {
    name: 'PublicLayout',
    template: '<div><slot /></div>',
  },
}));

vi.mock('@/shared/ui/PageHero.vue', () => ({
  default: {
    name: 'PageHero',
    template: '<div class="page-hero-stub" />',
  },
}));

vi.mock('vue-router', () => ({
  useRoute: () => ({ path: '/cookies', fullPath: '/cookies' }),
  useRouter: () => ({ push: vi.fn() }),
  RouterLink: {
    name: 'RouterLink',
    props: ['to'],
    template: '<a><slot /></a>',
  },
}));

vi.mock('@/shared/api/old_publicApi', () => ({
  publicApi: {
    settings: vi.fn().mockResolvedValue({
      data: {
        data: {
          cookie_title: 'Cookie & Privacy Controls',
        },
      },
    }),
  },
}));

describe('CookiePreferencesPage.vue', () => {
  let consent;

  beforeEach(() => {
    localStorage.clear();
    consent = useCookieConsent();
    consent.resetConsent();
  });

  it('renders the interactive live preview banner and category settings', async () => {
    const wrapper = mount(CookiePreferencesPage);
    await flushPromises();

    expect(wrapper.text()).toContain('Live System Consent Widget');
    expect(wrapper.text()).toContain('Granular Cookie Categories');
    expect(wrapper.text()).toContain('Strictly Necessary');
    expect(wrapper.text()).toContain('User Preferences');
    expect(wrapper.text()).toContain('Performance & Analytics');
    expect(wrapper.text()).toContain('Marketing & Promotions');
  });

  it('handles Allow All button correctly', async () => {
    const wrapper = mount(CookiePreferencesPage);
    await flushPromises();

    const allowAllBtn = wrapper.findAll('button').find((b) => b.text().includes('Allow All'));
    expect(allowAllBtn).toBeDefined();
    await allowAllBtn.trigger('click');

    expect(consent.hasConsented.value).toBe(true);
    expect(consent.consentStatus.value).toBe('all');
    expect(wrapper.text()).toContain('All Cookies Allowed');
  });

  it('handles Essential Only button correctly', async () => {
    const wrapper = mount(CookiePreferencesPage);
    await flushPromises();

    const essentialBtn = wrapper.findAll('button').find((b) => b.text().includes('Essential Only'));
    expect(essentialBtn).toBeDefined();
    await essentialBtn.trigger('click');

    expect(consent.hasConsented.value).toBe(true);
    expect(consent.consentStatus.value).toBe('necessary_only');
    expect(wrapper.text()).toContain('Essential Only Allowed');
  });

  it('handles Save Preferences button correctly', async () => {
    const wrapper = mount(CookiePreferencesPage);
    await flushPromises();

    const saveBtn = wrapper.find('[data-testid="page-save-preferences"]');
    expect(saveBtn.exists()).toBe(true);
    await saveBtn.trigger('click');

    expect(consent.hasConsented.value).toBe(true);
  });
});
