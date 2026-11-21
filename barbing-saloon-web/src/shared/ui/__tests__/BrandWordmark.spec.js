import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import BrandWordmark from '../BrandWordmark.vue';

describe('BrandWordmark', () => {
  it('renders default wordmark parts (Candy and Cutz)', () => {
    const wrapper = mount(BrandWordmark);
    expect(wrapper.text()).toBe('CandyCutz');

    const primarySpan = wrapper.find('.brand-wordmark__white');
    const goldSpan = wrapper.find('.brand-wordmark__gold');

    expect(primarySpan.exists()).toBe(true);
    expect(goldSpan.exists()).toBe(true);
    expect(primarySpan.text()).toBe('Candy');
    expect(goldSpan.text()).toBe('Cutz');
  });

  it('supports custom brand words', () => {
    const wrapper = mount(BrandWordmark, {
      props: {
        first: 'Elite',
        second: 'Barbers',
      },
    });

    expect(wrapper.text()).toBe('EliteBarbers');
    expect(wrapper.find('.brand-wordmark__white').text()).toBe('Elite');
    expect(wrapper.find('.brand-wordmark__gold').text()).toBe('Barbers');
  });

  it('swaps the gold and primary parts when goldFirst is true', () => {
    const wrapper = mount(BrandWordmark, {
      props: {
        first: 'Candy',
        second: 'Cutz',
        goldFirst: true,
      },
    });

    const spans = wrapper.findAll('.brand-wordmark > span');
    expect(spans[0].classes()).toContain('brand-wordmark__gold');
    expect(spans[0].text()).toBe('Candy');
    expect(spans[1].classes()).toContain('brand-wordmark__white');
    expect(spans[1].text()).toBe('Cutz');
  });

  it('inserts space between wordmark parts when spaceBetween is true', () => {
    const wrapper = mount(BrandWordmark, {
      props: {
        first: 'Candy',
        second: 'Cutz',
        spaceBetween: true,
      },
    });

    expect(wrapper.text()).toContain('Candy');
    expect(wrapper.text()).toContain('Cutz');
    const spaceSpan = wrapper.find('span[aria-hidden="true"]');
    expect(spaceSpan.exists()).toBe(true);
  });

  it('includes brand-wordmark__primary class on non-gold span for theme reactivity', () => {
    const wrapper = mount(BrandWordmark);
    const primaryPart = wrapper.find('.brand-wordmark__primary');
    expect(primaryPart.exists()).toBe(true);
    expect(primaryPart.classes()).toContain('brand-wordmark__white');
  });
});

