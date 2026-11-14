import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import AdminBlogLivePreview from '../AdminBlogLivePreview.vue';

describe('AdminBlogLivePreview', () => {
  it('reflects article content, author, publication state, and editorial readiness', () => {
    const wrapper = mount(AdminBlogLivePreview, {
      props: {
        title: 'A guide to healthy hair',
        excerpt: 'Simple daily habits for healthy hair.',
        content: 'Start with a gentle shampoo and keep a regular routine.',
        previewImage: '/images/article-cover.jpg',
        authorDisplay: 'CandyCutz Team',
        isPublished: true,
        readingTime: '1 min read',
        formattedDate: 'Oct 29, 2026',
      },
    });

    expect(wrapper.text()).toContain('A guide to healthy hair');
    expect(wrapper.text()).toContain('CandyCutz Team');
    expect(wrapper.text()).toContain('Live');
    expect(wrapper.text()).toContain('100% complete');
  });
});
