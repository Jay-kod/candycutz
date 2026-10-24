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
      title: 'Fresh Cut.\nZero Stress.',
      titleColor: '#ff9900',
      subtitle: 'Pick a barber and book your style.',
      caption: 'Premium grooming, made simple.',
    });

    expect(wrapper.find('h1').text()).toBe('Fresh Cut. Zero Stress.');
    expect(wrapper.find('h1').attributes('style')).toContain('color: rgb(255, 153, 0)');
    expect(wrapper.find('.barber-cta__subtitle').text()).toBe('Pick a barber and book your style.');
    expect(wrapper.find('.barber-cta__caption').text()).toBe('Premium grooming, made simple.');
    expect(wrapper.find('.barber-cta__actions').element.previousElementSibling).toBe(wrapper.find('.barber-cta__caption').element);
  });

  it('uses the default accent when the provided headline color is invalid', () => {
    const wrapper = mountCta({ titleColor: 'red; background: url(javascript:alert(1))' });

    expect(wrapper.find('h1').attributes('style')).toContain('color: rgb(255, 184, 77)');
  });
});
