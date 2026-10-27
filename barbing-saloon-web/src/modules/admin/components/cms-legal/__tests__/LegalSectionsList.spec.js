import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import LegalSectionsList from '../LegalSectionsList.vue';

describe('LegalSectionsList.vue', () => {
  it('renders all legal clause cards correctly with initial values', () => {
    const sections = reactive([
      { heading: '1. Information Collection', body: 'We collect personal identification information.' },
      { heading: '2. Payment Processing', body: 'Payments are handled securely via verified gateways.' }
    ]);

    const wrapper = mount(LegalSectionsList, {
      props: { sections }
    });

    const text = wrapper.text();
    expect(text).toContain('Document Articles & Clauses (2)');
    expect(text).toContain('1. Information Collection');
    expect(text).toContain('2. Payment Processing');

    const inputs = wrapper.findAll('input[type="text"]');
    expect(inputs[0].element.value).toBe('1. Information Collection');
    expect(inputs[1].element.value).toBe('2. Payment Processing');

    const textareas = wrapper.findAll('textarea');
    expect(textareas[0].element.value).toBe('We collect personal identification information.');
    expect(textareas[1].element.value).toBe('Payments are handled securely via verified gateways.');
  });

  it('emits add-section and remove-section events on user interaction', async () => {
    const sections = reactive([
      { heading: 'Clause 1', body: 'Content 1' },
      { heading: 'Clause 2', body: 'Content 2' }
    ]);

    const wrapper = mount(LegalSectionsList, {
      props: { sections }
    });

    // Top Add Section button
    const addButton = wrapper.find('button');
    await addButton.trigger('click');
    expect(wrapper.emitted('add-section')).toBeTruthy();

    // Remove Clause 1
    const removeButton = wrapper.find('button[title="Remove this legal clause"]');
    await removeButton.trigger('click');
    expect(wrapper.emitted('remove-section')).toBeTruthy();
    expect(wrapper.emitted('remove-section')[0]).toEqual([0]);
  });
});

