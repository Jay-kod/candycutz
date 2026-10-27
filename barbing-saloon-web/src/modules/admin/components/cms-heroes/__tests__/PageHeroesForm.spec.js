import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import PageHeroesForm from '../PageHeroesForm.vue';

describe('PageHeroesForm.vue', () => {
  it('renders title and subtitle inputs with initial form data', () => {
    const form = reactive({
      title: 'Precision Cuts. Made for You.',
      subtitle: 'Expert barbers. Fresh fades.',
      image: 'covers/home.jpg',
      opacity: 45
    });

    const wrapper = mount(PageHeroesForm, {
      props: {
        form,
        selectedFile: null,
        previewImage: 'https://example.com/cover.jpg',
        pageLabel: 'Home'
      }
    });

    const input = wrapper.find('input[type="text"]');
    expect(input.element.value).toBe('Precision Cuts. Made for You.');

    const textarea = wrapper.find('textarea');
    expect(textarea.element.value).toBe('Expert barbers. Fresh fades.');

    const opacityText = wrapper.text();
    expect(opacityText).toContain('45%');
  });

  it('emits clear-image and reset-text events upon button actions', async () => {
    const form = reactive({
      title: 'Services',
      subtitle: 'Our Cuts',
      image: 'services.jpg',
      opacity: 60
    });

    const wrapper = mount(PageHeroesForm, {
      props: {
        form,
        selectedFile: null,
        previewImage: 'https://example.com/services.jpg',
        pageLabel: 'Services'
      }
    });

    // Clear image button
    const clearButton = wrapper.find('button[title="Remove cover photo"]');
    await clearButton.trigger('click');
    expect(wrapper.emitted('clear-image')).toBeTruthy();

    // Reset copy button
    const resetButton = wrapper.findAll('button').find(b => b.text().includes('Reset Copy'));
    expect(resetButton).toBeTruthy();
    await resetButton.trigger('click');
    expect(wrapper.emitted('reset-text')).toBeTruthy();
  });
});
