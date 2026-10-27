import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import AdminGalleryStats from '../AdminGalleryStats.vue';

describe('AdminGalleryStats.vue', () => {
  it('renders computed gallery metrics correctly', () => {
    const stats = {
      totalCount: 24,
      distinctCats: 6,
      distinctBarbers: 5,
      shopPhotosCount: 4,
      hairAndBeardCount: 18
    };

    const wrapper = mount(AdminGalleryStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('24');
    expect(wrapper.text()).toContain('18 signature cuts & beard styles');
    expect(wrapper.text()).toContain('6');
    expect(wrapper.text()).toContain('categories');
    expect(wrapper.text()).toContain('5');
    expect(wrapper.text()).toContain('stylists');
    expect(wrapper.text()).toContain('4');
    expect(wrapper.text()).toContain('captures');
  });

  it('handles empty and zero stats gracefully', () => {
    const stats = {
      totalCount: 0,
      distinctCats: 0,
      distinctBarbers: 0,
      shopPhotosCount: 0,
      hairAndBeardCount: 0
    };

    const wrapper = mount(AdminGalleryStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('0');
  });
});

