import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import BarberCta from '../BarberCta.vue';

describe('BarberCta', () => {
  const mountCta = (props = {}) => mount(BarberCta, {
    props,
    global: {
      stubs: {
        RouterLink: {
          props: ['to'],
          template: '<a><slot /></a>',
        },
      },
    },
  });

  it('renders a single colored headline followed by two supporting lines', () => {
    const wrapper = mountCta({
      titlePartOne: 'Fresh Cut.',
      titlePartTwo: 'Zero Stress.',
      titleColorOrder: 'first-white',
      subtitle: 'Pick a barber and book your style.',
      caption: 'Premium grooming, made simple.',
    });

    expect(wrapper.find('h1').text().replace(/\u00a0/g, ' ')).toBe('Fresh Cut. Zero Stress.');
    expect(wrapper.find('.barber-cta__title-white').text()).toBe('Fresh Cut.');
    expect(wrapper.find('.barber-cta__title-gold').text()).toBe('Zero Stress.');
    expect(wrapper.find('.barber-cta__subtitle').text()).toBe('Pick a barber and book your style.');
    expect(wrapper.find('.barber-cta__caption').text()).toBe('Premium grooming, made simple.');
    expect(wrapper.find('.barber-cta__actions').element.previousElementSibling).toBe(wrapper.find('.barber-cta__caption').element);
    expect(wrapper.find('h1').classes()).toContain('whitespace-nowrap');
  });

  it('supports the reverse white and gold headline order', () => {
    const wrapper = mountCta({
      titlePartOne: 'Fresh Cut.',
      titlePartTwo: 'Zero Stress.',
      titleColorOrder: 'first-gold',
    });

    const titleParts = wrapper.find('h1').element.children;
    expect(titleParts[0].classList.contains('barber-cta__title-gold')).toBe(true);
    expect(titleParts[1].classList.contains('barber-cta__title-white')).toBe(true);
  });

  it('splits a legacy headline into two colored phrases', () => {
    const wrapper = mountCta({ title: 'Fresh Cut. Zero Stress.' });

    expect(wrapper.find('.barber-cta__title-white').text()).toBe('Fresh Cut.');
    expect(wrapper.find('.barber-cta__title-gold').text()).toBe('Zero Stress.');
  });
});
