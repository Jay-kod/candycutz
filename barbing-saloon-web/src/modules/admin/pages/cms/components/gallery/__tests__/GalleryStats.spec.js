import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import GalleryStats from '../GalleryStats.vue';

describe('GalleryStats.vue', () => {
  it('renders computed portfolio headline, cover presence, items count, and opacity', () => {
    const content = {
      gallery_title: 'Haircut & Styling Portfolio',
      gallery_subtitle: 'Handcrafted master barber cuts.'
    };

    const items = [
      { id: 1, title: 'Fade 1' },
      { id: 2, title: 'Fade 2' },
      { id: 3, title: 'Fade 3' },
      { id: 4, title: 'Fade 4' },
      { id: 5, title: 'Fade 5' }
    ];

    const categories = ['Skin Fades', 'Beard Care'];

    const wrapper = mount(GalleryStats, {
      props: {
        content,
        heroImage: 'uploads/settings/gallery_hero.jpg',
        heroOpacity: 80,
        items,
        categories
      },
      global: {
        stubs: ['router-link']
      }
    });

    const text = wrapper.text();
    expect(text).toContain('Haircut & Styling Portfolio');
    expect(text).toContain('Custom Cover');
    expect(text).toContain('Active Asset');
    expect(text).toContain('Overlay Dim: 80%');
    expect(text).toContain('5');
    expect(text).toContain('photos');
    expect(text).toContain('Skin Fades · Beard Care');
  });

  it('provides safe fallbacks for empty or undefined content', () => {
    const wrapper = mount(GalleryStats, {
      props: {
        content: {},
        heroImage: '',
        heroOpacity: 76,
        items: [],
        categories: []
      },
      global: {
        stubs: ['router-link']
      }
    });

    const text = wrapper.text();
    expect(text).toContain('Our Gallery');
    expect(text).toContain('Default Cover');
    expect(text).toContain('Fallback');
    expect(text).toContain('0');
    expect(text).toContain('Haircuts · Transformations');
  });
});

