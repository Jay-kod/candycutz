import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ServicesStats from '../ServicesStats.vue';

describe('ServicesStats.vue', () => {
  it('renders computed services count, category taxonomies, and headline hook', () => {
    const content = {
      services_title: 'Artisan Haircuts & Beard Grooming',
      services_subtitle: 'Full spectrum of luxury grooming.'
    };

    const services = [
      { id: 1, name: 'Skin Fade' },
      { id: 2, name: 'Beard Trim' },
      { id: 3, name: 'Hot Towel Shave' },
      { id: 4, name: 'VIP Combo' }
    ];

    const categories = ['Haircuts', 'Beard Care', 'Combos'];

    const wrapper = mount(ServicesStats, {
      props: {
        content,
        services,
        categories
      },
      global: {
        stubs: ['router-link']
      }
    });

    const text = wrapper.text();
    expect(text).toContain('Artisan Haircuts & Beard Grooming');
    expect(text).toContain('4');
    expect(text).toContain('treatments');
    expect(text).toContain('3');
    expect(text).toContain('taxonomies');
    expect(text).toContain('Haircuts · Beard Care');
  });

  it('provides safe fallbacks for empty or undefined content', () => {
    const wrapper = mount(ServicesStats, {
      props: {
        content: {},
        services: [],
        categories: []
      },
      global: {
        stubs: ['router-link']
      }
    });

    const text = wrapper.text();
    expect(text).toContain('Our Premium Services');
    expect(text).toContain('0');
    expect(text).toContain('3');
    expect(text).toContain('Haircuts · Beard Styling');
  });
});
