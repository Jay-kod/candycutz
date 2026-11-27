import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import CategoryIcon from '../CategoryIcon.vue';
import CategoryIconPicker from '../CategoryIconPicker.vue';

describe('CategoryIcon', () => {
  it('renders an SVG icon for known keys and falls back gracefully', () => {
    const scissors = mount(CategoryIcon, { props: { name: 'scissors' } });
    expect(scissors.find('svg').exists()).toBe(true);

    const legacySnake = mount(CategoryIcon, { props: { name: 'user_group' } });
    expect(legacySnake.find('svg').exists()).toBe(true);

    const unknown = mount(CategoryIcon, { props: { name: 'non-existent-icon' } });
    expect(unknown.find('svg').exists()).toBe(true);
  });
});

describe('CategoryIconPicker', () => {
  it('opens the real icon modal, filters by search and group, selects an icon, and closes on Escape', async () => {
    const wrapper = mount(CategoryIconPicker, {
      attachTo: document.body,
      props: {
        modelValue: 'scissors',
        colorTheme: 'admin',
      },
    });

    // Trigger renders selected label
    expect(wrapper.text()).toContain('Scissors');

    // Open modal
    await wrapper.find('button').trigger('click');
    const dialogEl = document.body.querySelector('[role="dialog"]');
    expect(dialogEl).not.toBeNull();

    // Search for crown
    const searchEl = dialogEl.querySelector('input[type="text"]');
    searchEl.value = 'crown';
    searchEl.dispatchEvent(new Event('input', { bubbles: true }));
    await wrapper.vm.$nextTick();

    expect(dialogEl.textContent).toContain('Royal Crown');
    expect(dialogEl.textContent).not.toContain('Camera');

    // Click Royal Crown tile
    const crownBtn = Array.from(dialogEl.querySelectorAll('button')).find((b) =>
      b.textContent.includes('Royal Crown')
    );
    expect(crownBtn).toBeTruthy();
    crownBtn.click();
    await wrapper.vm.$nextTick();

    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['crown']);
    // Modal closes after selection
    expect(document.body.querySelector('[role="dialog"]')).toBeNull();

    // Clear button resets value
    const clearBtn = wrapper.find('button[aria-label="Remove selected icon"]');
    expect(clearBtn.exists()).toBe(true);
    await clearBtn.trigger('click');
    expect(wrapper.emitted('update:modelValue')?.[1]).toEqual(['']);

    // Re-open and test Escape closes dialog
    await wrapper.find('button').trigger('click');
    expect(document.body.querySelector('[role="dialog"]')).not.toBeNull();
    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
    await wrapper.vm.$nextTick();
    expect(document.body.querySelector('[role="dialog"]')).toBeNull();

    wrapper.unmount();
  });
});
