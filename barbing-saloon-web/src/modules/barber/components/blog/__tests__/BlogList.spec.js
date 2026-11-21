import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BlogList from '../BlogList.vue';

describe('BlogList.vue', () => {
  const dummyImageUrl = (path) => path;
  const dummyDate = () => 'Oct 28, 2026';

  const defaultMountOptions = {
    global: {
      stubs: {
        'router-link': {
          template: '<a><slot /></a>',
        },
      },
    },
  };

  it('renders empty state when there are no articles', () => {
    const wrapper = mount(BlogList, {
      ...defaultMountOptions,
      props: {
        posts: [],
        getImageUrl: dummyImageUrl,
        formatDate: dummyDate,
      },
    });

    expect(wrapper.text()).toContain('No Blog Articles Yet');
    expect(wrapper.find('[data-test="post-card"]').exists()).toBe(false);
  });

  it('renders blog articles and emits toggle-publish and delete events', async () => {
    const mockPosts = [
      {
        id: 7,
        title: 'Maintaining Your Beard',
        excerpt: 'Beard care tips for every gentleman.',
        is_published: 1,
        created_at: '2026-10-28T00:00:00Z',
      },
    ];

    const wrapper = mount(BlogList, {
      ...defaultMountOptions,
      props: {
        posts: mockPosts,
        getImageUrl: dummyImageUrl,
        formatDate: dummyDate,
      },
    });

    expect(wrapper.text()).toContain('Maintaining Your Beard');
    expect(wrapper.text()).toContain('Live');

    const toggleBtn = wrapper.find('[data-test="toggle-publish-btn"]');
    await toggleBtn.trigger('click');
    expect(wrapper.emitted('toggle-publish')).toBeTruthy();
    expect(wrapper.emitted('toggle-publish')[0][0]).toEqual(mockPosts[0]);

    const deleteBtn = wrapper.find('[data-test="delete-post-btn"]');
    await deleteBtn.trigger('click');
    expect(wrapper.emitted('delete')).toBeTruthy();
    expect(wrapper.emitted('delete')[0][0]).toBe(7);
  });
});
