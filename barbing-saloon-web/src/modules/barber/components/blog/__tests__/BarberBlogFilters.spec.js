import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BarberBlogFilters from '../BarberBlogFilters.vue';

describe('BarberBlogFilters.vue', () => {
  const mockStats = {
    totalCount: 10,
    publishedCount: 7,
    draftsCount: 3,
  };

  it('renders filter tabs and emits update:statusFilter', async () => {
    const wrapper = mount(BarberBlogFilters, {
      props: {
        searchQuery: '',
        statusFilter: 'all',
        sortOrder: 'newest',
        viewMode: 'grid',
        stats: mockStats,
        hasActiveFilters: false,
      },
    });

    expect(wrapper.text()).toContain('All');
    expect(wrapper.text()).toContain('10');
    expect(wrapper.text()).toContain('Live');
    expect(wrapper.text()).toContain('7');
    expect(wrapper.text()).toContain('Drafts');
    expect(wrapper.text()).toContain('3');

    const publishedBtn = wrapper.find('[data-test="filter-published-btn"]');
    await publishedBtn.trigger('click');
    expect(wrapper.emitted('update:statusFilter')).toBeTruthy();
    expect(wrapper.emitted('update:statusFilter')[0][0]).toBe('published');
  });

  it('emits view mode and reset events', async () => {
    const wrapper = mount(BarberBlogFilters, {
      props: {
        searchQuery: 'fade',
        statusFilter: 'all',
        sortOrder: 'newest',
        viewMode: 'grid',
        stats: mockStats,
        hasActiveFilters: true,
      },
    });

    const listBtn = wrapper.find('[data-test="view-list-btn"]');
    await listBtn.trigger('click');
    expect(wrapper.emitted('update:viewMode')).toBeTruthy();
    expect(wrapper.emitted('update:viewMode')[0][0]).toBe('list');

    const resetBtn = wrapper.find('[data-test="reset-filters-btn"]');
    await resetBtn.trigger('click');
    expect(wrapper.emitted('reset')).toBeTruthy();
  });
});

