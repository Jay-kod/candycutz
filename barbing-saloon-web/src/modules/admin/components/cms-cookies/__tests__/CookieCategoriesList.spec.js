import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import CookieCategoriesList from '../CookieCategoriesList.vue';
import { COOKIE_CATEGORIES, DEFAULT_COOKIE_CONTENT } from '../../../composables/useAdminCookieCms';

describe('CookieCategoriesList.vue', () => {
  it('renders all 4 cookie categories with title and description bindings', () => {
    const content = reactive({ ...DEFAULT_COOKIE_CONTENT });

    const wrapper = mount(CookieCategoriesList, {
      props: {
        categories: COOKIE_CATEGORIES,
        content
      }
    });

    const text = wrapper.text();
    expect(text).toContain('Consent Categories (4)');
    expect(text).toContain('Strictly Necessary');
    expect(text).toContain('User Preferences');
    expect(text).toContain('Performance & Analytics');
    expect(text).toContain('Marketing & Outreach');
    expect(text).toContain('Always Active');

    const inputs = wrapper.findAll('input[type="text"]');
    expect(inputs.length).toBe(4);
    expect(inputs[0].element.value).toBe('Necessary');

    const textareas = wrapper.findAll('textarea');
    expect(textareas.length).toBe(4);
    expect(textareas[0].element.value).toContain('Required for core site functions');
  });

  it('updates reactive content when input values change', async () => {
    const content = reactive({ ...DEFAULT_COOKIE_CONTENT });

    const wrapper = mount(CookieCategoriesList, {
      props: {
        categories: COOKIE_CATEGORIES,
        content
      }
    });

    const inputs = wrapper.findAll('input[type="text"]');
    await inputs[1].setValue('Custom Preferences Name');
    expect(content.cookie_preferences_title).toBe('Custom Preferences Name');
  });
});
