import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BlogEditorHeader from '../BlogEditorHeader.vue';

describe('BlogEditorHeader.vue', () => {
  it('renders editing state correctly', () => {
    const wrapper = mount(BlogEditorHeader, {
      props: {
        isEditing: true,
        isPublished: true,
        saving: false,
      },
      global: {
        stubs: {
          'router-link': {
            template: '<a><slot /></a>',
          },
        },
      },
    });

    expect(wrapper.text()).toContain('Edit');
    expect(wrapper.text()).toContain('Article');
    expect(wrapper.text()).toContain('Live Published');
    expect(wrapper.text()).toContain('Update Article');
  });

  it('renders drafting state and emits save event', async () => {
    const wrapper = mount(BlogEditorHeader, {
      props: {
        isEditing: false,
        isPublished: false,
        saving: false,
      },
      global: {
        stubs: {
          'router-link': {
            template: '<a><slot /></a>',
          },
        },
      },
    });

    expect(wrapper.text()).toContain('Draft');
    expect(wrapper.text()).toContain('Draft Mode');
    expect(wrapper.text()).toContain('Publish Article');

    const saveBtn = wrapper.find('button');
    await saveBtn.trigger('click');
    expect(wrapper.emitted('save')).toBeTruthy();
  });
});

