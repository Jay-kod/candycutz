import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BaseButton from '../BaseButton.vue';
import BaseBadge from '../BaseBadge.vue';
import BaseCard from '../BaseCard.vue';

describe('Base UI Components', () => {
  it('renders BaseButton with slot content', () => {
    const wrapper = mount(BaseButton, {
      slots: {
        default: 'Click Me',
      },
    });
    expect(wrapper.text()).toBe('Click Me');
    expect(wrapper.find('button').exists()).toBe(true);
  });

  it('renders BaseBadge with slot content', () => {
    const wrapper = mount(BaseBadge, {
      slots: {
        default: 'New Badge',
      },
    });
    expect(wrapper.text()).toBe('New Badge');
    expect(wrapper.find('span').exists()).toBe(true);
  });

  it('renders BaseCard with slot content', () => {
    const wrapper = mount(BaseCard, {
      slots: {
        default: '<p>Card content</p>',
      },
    });
    expect(wrapper.html()).toContain('Card content');
  });
});
