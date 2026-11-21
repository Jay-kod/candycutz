import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import PageHero from '../PageHero.vue';

vi.mock('@/shared/api/old_publicApi', () => ({
  publicApi: {
    settings: vi.fn().mockResolvedValue({ data: { data: {} } }),
  },
}));

describe('PageHero', () => {
  const mountHero = (props = {}) => mount(PageHero, {
    props: {
      pageKey: 'about',
      fallbackTitle: 'About Us',
      fallbackSubtitle: 'Our story and craft',
      loadSettings: false,
      ...props,
    },
  });

  it('renders title and subtitle', () => {
    const wrapper = mountHero({
      fallbackTitle: 'Signature Cuts',
      fallbackSubtitle: 'Precision grooming',
    });

    expect(wrapper.find('.page-hero__title').text()).toBe('Signature Cuts');
    expect(wrapper.find('.page-hero__subtitle').text()).toBe('Precision grooming');
  });

  it('renders the hero gradient pattern layer', () => {
    const wrapper = mountHero();
    expect(wrapper.find('.page-hero__gradient').exists()).toBe(true);
  });

  it('adds page-hero--has-image class and renders image when backgroundImage is provided', () => {
    const wrapper = mountHero({
      backgroundImage: 'https://example.com/hero.jpg',
    });

    expect(wrapper.classes()).toContain('page-hero--has-image');
    expect(wrapper.find('.page-hero__image').exists()).toBe(true);
    expect(wrapper.find('.page-hero__overlay').exists()).toBe(true);
    expect(wrapper.find('.page-hero__gradient').exists()).toBe(true);
  });
});

