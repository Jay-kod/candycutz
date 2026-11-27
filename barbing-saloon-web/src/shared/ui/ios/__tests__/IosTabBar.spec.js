import { defineComponent, h } from 'vue';
import { mount } from '@vue/test-utils';
import { createMemoryHistory, createRouter } from 'vue-router';
import { describe, expect, it } from 'vitest';
import IosTabBar from '../IosTabBar.vue';

const IconStub = defineComponent({
  render: () => h('svg'),
});

const tabs = [
  { name: 'Dashboard', to: '/customer/dashboard', icon: IconStub },
  { name: 'Profile', to: '/customer/dashboard/profile', icon: IconStub },
];

async function createWrapper(userAvatarUrl = null) {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/customer/dashboard', component: IconStub },
      { path: '/customer/dashboard/profile', component: IconStub },
    ],
  });
  await router.push('/customer/dashboard');
  await router.isReady();

  const wrapper = mount(IosTabBar, {
    props: { tabs, userAvatarUrl },
    global: { plugins: [router] },
  });

  return wrapper;
}

describe('IosTabBar', () => {
  it('shows the active colour without a dot and uses the uploaded profile image', async () => {
    const wrapper = await createWrapper('/storage/avatars/customer.webp');
    const dashboardTab = wrapper.get('a[href="/customer/dashboard"]');
    const profileTab = wrapper.get('a[href="/customer/dashboard/profile"]');

    expect(dashboardTab.classes()).toContain('text-gold');
    expect(wrapper.find('.absolute.bottom-1').exists()).toBe(false);
    expect(profileTab.get('img').attributes('src')).toBe('/storage/avatars/customer.webp');
    expect(profileTab.find('svg').exists()).toBe(false);
  });

  it('keeps the profile icon fallback when no uploaded image exists', async () => {
    const wrapper = await createWrapper();
    const profileTab = wrapper.get('a[href="/customer/dashboard/profile"]');

    expect(profileTab.find('img').exists()).toBe(false);
    expect(profileTab.find('svg').exists()).toBe(true);
  });
});
