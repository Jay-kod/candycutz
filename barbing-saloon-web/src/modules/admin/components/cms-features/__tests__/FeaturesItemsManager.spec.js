import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import { reactive, h, markRaw } from 'vue';
import FeaturesItemsManager from '../FeaturesItemsManager.vue';

describe('FeaturesItemsManager.vue', () => {
  const dummyIcon = markRaw({ render: () => h('svg') });
  const availableIcons = [
    { name: 'scissors', comp: dummyIcon, label: 'Scissors' },
    { name: 'crown', comp: dummyIcon, label: 'Crown' },
    { name: 'star', comp: dummyIcon, label: 'Star' }
  ];
  const resolveIconComponent = () => dummyIcon;

  it('renders all feature cards correctly', () => {
    const items = reactive([
      { title: 'Precision Craft', icon: 'scissors', description: 'Flawless fades and lines.' },
      { title: 'Luxury Lounge', icon: 'crown', description: 'Exclusive amenities.' }
    ]);

    const wrapper = mount(FeaturesItemsManager, {
      props: {
        items,
        availableIcons,
        resolveIconComponent
      }
    });

    const text = wrapper.text();
    expect(text).toContain('Feature Cards (2)');
    expect(text).toContain('Precision Craft');
    expect(text).toContain('Luxury Lounge');

    const inputs = wrapper.findAll('input[type="text"]');
    expect(inputs[0].element.value).toBe('Precision Craft');
    expect(inputs[1].element.value).toBe('Luxury Lounge');

    const textareas = wrapper.findAll('textarea');
    expect(textareas[0].element.value).toBe('Flawless fades and lines.');
    expect(textareas[1].element.value).toBe('Exclusive amenities.');
  });

  it('emits add-feature and remove-feature events upon button clicks', async () => {
    const items = reactive([
      { title: 'Card 1', icon: 'scissors', description: 'Desc 1' },
      { title: 'Card 2', icon: 'crown', description: 'Desc 2' }
    ]);

    const wrapper = mount(FeaturesItemsManager, {
      props: {
        items,
        availableIcons,
        resolveIconComponent
      }
    });

    // Add button
    const addButton = wrapper.find('button');
    await addButton.trigger('click');
    expect(wrapper.emitted('add-feature')).toBeTruthy();

    // Remove button on card 1
    const removeButton = wrapper.find('button[title="Remove this feature card"]');
    await removeButton.trigger('click');
    expect(wrapper.emitted('remove-feature')).toBeTruthy();
    expect(wrapper.emitted('remove-feature')[0]).toEqual([0]);
  });
});
