import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import BrandIdentityForm from '../BrandIdentityForm.vue';

describe('BrandIdentityForm.vue', () => {
  it('renders input fields with initial form data correctly', () => {
    const form = reactive({
      first: 'Executive',
      second: 'Grooming',
      separator: 'none',
      goldPart: 'second',
      tagline: 'Master haircutting and beard design.'
    });

    const wrapper = mount(BrandIdentityForm, {
      props: { form }
    });

    const inputs = wrapper.findAll('input[type="text"]');
    expect(inputs.length).toBe(2);
    expect(inputs[0].element.value).toBe('Executive');
    expect(inputs[1].element.value).toBe('Grooming');

    const textarea = wrapper.find('textarea');
    expect(textarea.element.value).toBe('Master haircutting and beard design.');
  });

  it('allows switching wordmark spacing treatments and gold highlights', async () => {
    const form = reactive({
      first: 'Candy',
      second: 'Cutz',
      separator: 'none',
      goldPart: 'second',
      tagline: ''
    });

    const wrapper = mount(BrandIdentityForm, {
      props: { form }
    });

    const buttons = wrapper.findAll('button[type="button"]');
    // Button index 0: 'none', index 1: 'space'
    await buttons[1].trigger('click');
    expect(form.separator).toBe('space');

    // Button index 2: 'second', index 3: 'first'
    await buttons[3].trigger('click');
    expect(form.goldPart).toBe('first');
  });
});

