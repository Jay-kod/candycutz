import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { nextTick, markRaw, ref } from 'vue';
import DashboardSidebar from '../DashboardSidebar.vue';

vi.mock('@/core/composables/useTheme', () => ({
  useTheme: () => ({ isDark: { value: true } }),
}));

const DummyIcon = markRaw({ template: '<svg />' });

const createGroups = () => [
  {
    label: 'Operations',
    items: [{ name: 'Dashboard', to: '/admin/dashboard', icon: DummyIcon }],
  },
  {
    label: 'Website CMS',
    items: [
      { name: 'Home Page', to: '/admin/websites/home', icon: DummyIcon },
      { name: 'Terms of Service Page', to: '/admin/websites/terms', icon: DummyIcon },
    ],
  },
];

const stubs = {
  RouterLink: { template: '<a><slot /></a>' },
  SidebarBrand: { template: '<div />' },
  SidebarUserProfile: { template: '<div />' },
};

describe('DashboardSidebar.vue', () => {
  beforeEach(() => {
    vi.restoreAllMocks();
  });

  it('auto-scrolls an out-of-view active sidebar item into the visible container on mount', async () => {
    const scrollContainerRect = {
      top: 60,
      bottom: 560,
      height: 500,
      left: 0,
      right: 280,
      width: 280,
    };
    const activeItemRect = {
      top: 900,
      bottom: 940,
      height: 40,
      left: 0,
      right: 280,
      width: 280,
    };

    const getBoundingClientRectSpy = vi
      .spyOn(HTMLElement.prototype, 'getBoundingClientRect')
      .mockImplementation(function mockRect() {
        if (this.dataset?.active === 'true') {
          return activeItemRect;
        }
        if (this.className?.includes('overflow-y-auto')) {
          return scrollContainerRect;
        }
        return { top: 0, bottom: 0, height: 0, left: 0, right: 0, width: 0 };
      });

    const wrapper = mount(DashboardSidebar, {
      props: {
        portalName: 'Admin',
        homeRoute: '/admin/dashboard',
        groupedNavItems: createGroups(),
        isActiveRoute: (to) => to === '/admin/websites/terms',
      },
      global: { stubs },
    });

    await nextTick();
    await nextTick();

    const scrollContainer = wrapper.find('.overflow-y-auto').element;
    const activeLink = wrapper.find('[data-active="true"]');

    expect(activeLink.exists()).toBe(true);
    expect(activeLink.attributes('aria-current')).toBe('page');
    expect(activeLink.text()).toContain('Terms of Service Page');
    // Expected centered scrollTop: 0 + (900 - 60) - (500 - 40) / 2 = 610
    expect(scrollContainer.scrollTop).toBe(610);

    getBoundingClientRectSpy.mockRestore();
  });

  it('does not jump scroll when the active sidebar item is already within the visible container', async () => {
    vi.spyOn(HTMLElement.prototype, 'getBoundingClientRect').mockImplementation(function mockRect() {
      if (this.dataset?.active === 'true') {
        return { top: 200, bottom: 240, height: 40, left: 0, right: 280, width: 280 };
      }
      if (this.className?.includes('overflow-y-auto')) {
        return { top: 60, bottom: 560, height: 500, left: 0, right: 280, width: 280 };
      }
      return { top: 0, bottom: 0, height: 0, left: 0, right: 0, width: 0 };
    });

    const wrapper = mount(DashboardSidebar, {
      props: {
        portalName: 'Admin',
        homeRoute: '/admin/dashboard',
        groupedNavItems: createGroups(),
        isActiveRoute: (to) => to === '/admin/websites/terms',
      },
      global: { stubs },
    });

    const scrollContainer = wrapper.find('.overflow-y-auto').element;
    scrollContainer.scrollTop = 180;

    await nextTick();
    await nextTick();

    expect(scrollContainer.scrollTop).toBe(180);
  });

  it('keeps the active collapsible group open and re-scrolls when route changes', async () => {
    const activePath = ref('/admin/dashboard');

    vi.spyOn(HTMLElement.prototype, 'getBoundingClientRect').mockImplementation(function mockRect() {
      if (this.dataset?.active === 'true') {
        return activePath.value === '/admin/websites/terms'
          ? { top: 820, bottom: 860, height: 40, left: 0, right: 280, width: 280 }
          : { top: 80, bottom: 120, height: 40, left: 0, right: 280, width: 280 };
      }
      if (this.className?.includes('overflow-y-auto')) {
        return { top: 60, bottom: 560, height: 500, left: 0, right: 280, width: 280 };
      }
      return { top: 0, bottom: 0, height: 0, left: 0, right: 0, width: 0 };
    });

    const wrapper = mount(DashboardSidebar, {
      props: {
        portalName: 'Admin',
        homeRoute: '/admin/dashboard',
        groupedNavItems: createGroups(),
        isActiveRoute: (to) => to === activePath.value,
      },
      global: { stubs },
    });

    await nextTick();
    const scrollContainer = wrapper.find('.overflow-y-auto').element;
    expect(scrollContainer.scrollTop).toBe(0);

    activePath.value = '/admin/websites/terms';
    await nextTick();
    await nextTick();

    const activeLink = wrapper.find('[data-active="true"]');
    expect(activeLink.text()).toContain('Terms of Service Page');
    // Expected centered scrollTop: 0 + (820 - 60) - (500 - 40) / 2 = 530
    expect(scrollContainer.scrollTop).toBe(530);

    // Attempting to collapse the group containing the active item keeps it visible
    const cmsHeader = wrapper.findAll('.group\\/hdr').find((el) => el.text().includes('Website CMS'));
    await cmsHeader.trigger('click');
    await nextTick();

    const cmsNav = cmsHeader.element.parentElement.querySelector('nav');
    expect(cmsNav.style.display).not.toBe('none');
  });
});
