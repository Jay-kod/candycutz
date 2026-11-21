import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BlogLivePreviewCard from '../BlogLivePreviewCard.vue';

describe('BlogLivePreviewCard.vue', () => {
  it('renders title, excerpt, and author name', () => {
    const wrapper = mount(BlogLivePreviewCard, {
      props: {
        title: 'Mastering the Low Fade',
        excerpt: 'An expert barber walkthrough.',
        content: 'Full body content here...',
        previewImage: 'https://example.com/fade.jpg',
        authorDisplay: 'Barber Marcus',
        isPublished: true,
        readingTime: '3 min read',
        formattedDate: 'Oct 29, 2026',
      },
    });

    expect(wrapper.text()).toContain('Mastering the Low Fade');
    expect(wrapper.text()).toContain('An expert barber walkthrough.');
    expect(wrapper.text()).toContain('Barber Marcus');
    expect(wrapper.text()).toContain('3 min read');
    expect(wrapper.text()).toContain('Live');
    expect(wrapper.find('img').attributes('src')).toBe('https://example.com/fade.jpg');
  });

  it('renders fallbacks when fields are empty', () => {
    const wrapper = mount(BlogLivePreviewCard, {
      props: {
        title: '',
        excerpt: '',
        content: '',
        previewImage: '',
        authorDisplay: '',
        isPublished: false,
        readingTime: '1 min read',
        formattedDate: 'Oct 29, 2026',
      },
    });

    expect(wrapper.text()).toContain('Article Title Preview...');
    expect(wrapper.text()).toContain('CandyCutz Stylist');
    expect(wrapper.text()).toContain('Draft');
    expect(wrapper.find('img').exists()).toBe(false);
  });
});

