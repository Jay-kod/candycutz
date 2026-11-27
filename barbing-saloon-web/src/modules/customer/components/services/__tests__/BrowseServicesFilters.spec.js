import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BrowseServicesFilters from '../BrowseServicesFilters.vue';

describe('BrowseServicesFilters.vue', () => {
  const categories = [
    { name: 'All', count: 10 },
    { name: 'Haircut', count: 2 },
    { name: 'Beard', count: 2 },
    { name: 'Combo', count: 2 },
    { name: 'Kids', count: 2 },
    { name: 'Special', count: 2 }
  ];

  it('renders all category pills with their dynamic counts', () => {
    const wrapper = mount(BrowseServicesFilters, {
      props: {
        categories,
        activeCategory: 'All',
        searchQuery: '',
        sortOrder: 'featured'
      }
    });

    const text = wrapper.text();
    expect(text).toContain('All');
    expect(text).toContain('10');
    expect(text).toContain('Haircut');
    expect(text).toContain('2');
    expect(text).toContain('Beard');
    expect(text).toContain('Kids');
  });

  it('emits category-change when a category button is clicked', async () => {
    const wrapper = mount(BrowseServicesFilters, {
      props: {
        categories,
        activeCategory: 'All',
        searchQuery: '',
        sortOrder: 'featured'
      }
    });

    const buttons = wrapper.findAll('button');
    const haircutBtn = buttons.find(b => b.text().includes('Haircut'));
    expect(haircutBtn).toBeDefined();

    await haircutBtn.trigger('click');
    expect(wrapper.emitted('category-change')).toBeTruthy();
    expect(wrapper.emitted('category-change')[0]).toEqual(['Haircut']);
  });

  it('emits update:searchQuery when typing in the search box', async () => {
    const wrapper = mount(BrowseServicesFilters, {
      props: {
        categories,
        activeCategory: 'All',
        searchQuery: '',
        sortOrder: 'featured'
      }
    });

    const input = wrapper.find('input[type="text"]');
    await input.setValue('Fade');

    expect(wrapper.emitted('update:searchQuery')).toBeTruthy();
    expect(wrapper.emitted('update:searchQuery')[0]).toEqual(['Fade']);
  });

  it('emits search event when search form is submitted or Search button is clicked', async () => {
    const wrapper = mount(BrowseServicesFilters, {
      props: {
        categories,
        activeCategory: 'All',
        searchQuery: 'Beard Trim',
        sortOrder: 'featured'
      }
    });

    const searchBtn = wrapper.findAll('button').find(b => b.text() === 'Search');
    expect(searchBtn).toBeDefined();

    await searchBtn.trigger('click');
    expect(wrapper.emitted('search')).toBeTruthy();
    expect(wrapper.emitted('search')[0]).toEqual(['Beard Trim']);
  });

  it('clears search query and emits search with empty string when clear button is clicked', async () => {
    const wrapper = mount(BrowseServicesFilters, {
      props: {
        categories,
        activeCategory: 'All',
        searchQuery: 'Fade',
        sortOrder: 'featured'
      }
    });

    const clearBtn = wrapper.find('button[title="Clear search"]');
    expect(clearBtn.exists()).toBe(true);

    await clearBtn.trigger('click');
    expect(wrapper.emitted('update:searchQuery')).toBeTruthy();
    expect(wrapper.emitted('update:searchQuery')[0]).toEqual(['']);
    expect(wrapper.emitted('search')).toBeTruthy();
    expect(wrapper.emitted('search')[0]).toEqual(['']);
  });
});
