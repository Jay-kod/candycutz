import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ServicesTabs from '../ServicesTabs.vue';

describe('ServicesTabs.vue', () => {
  const categories = ['Haircuts', 'Beard Grooming', 'VIP Packages'];
  const mockServices = [
    { id: 1, name: 'Skin Fade', category_name: 'Haircuts' },
    { id: 2, name: 'Taper Fade', category_name: 'Haircuts' },
    { id: 3, name: 'Beard Sculpt', category_name: 'Beard Grooming' },
    { id: 4, name: 'Full Executive Package', category_name: 'VIP Packages' },
  ];

  it('renders all category tabs and counts correctly', () => {
    const wrapper = mount(ServicesTabs, {
      props: {
        categories,
        activeCategory: 'all',
        totalCount: 4,
        services: mockServices,
      },
    });

    const allTab = wrapper.find('[data-test="tab-all"]');
    expect(allTab.exists()).toBe(true);
    expect(allTab.text()).toContain('All Treatments');
    expect(allTab.text()).toContain('4');

    const catTabs = wrapper.findAll('[data-test="category-tab"]');
    expect(catTabs.length).toBe(3);
    expect(catTabs[0].text()).toContain('Haircuts');
    expect(catTabs[0].text()).toContain('2');
    expect(catTabs[1].text()).toContain('Beard Grooming');
    expect(catTabs[1].text()).toContain('1');
  });

  it('emits update:activeCategory when clicking a category tab', async () => {
    const wrapper = mount(ServicesTabs, {
      props: {
        categories,
        activeCategory: 'all',
        totalCount: 4,
        services: mockServices,
      },
    });

    const catTabs = wrapper.findAll('[data-test="category-tab"]');
    await catTabs[0].trigger('click');

    expect(wrapper.emitted('update:activeCategory')).toBeTruthy();
    expect(wrapper.emitted('update:activeCategory')[0]).toEqual(['Haircuts']);
  });
});

