import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import BarbersPage from '../BarbersPage.vue';
import { client } from '@/shared/api/client';

vi.mock('@/shared/api/client', () => ({
  client: {
    get: vi.fn(),
  },
}));

vi.mock('@/portals/customer/layouts/CustomerLayout.vue', () => ({
  default: {
    name: 'CustomerLayout',
    template: '<div><slot /></div>',
  },
}));

vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: vi.fn(),
  }),
  useRoute: () => ({
    params: {},
    query: {},
  }),
  RouterLink: {
    name: 'RouterLink',
    props: ['to'],
    template: '<a :href="typeof to === \'string\' ? to : to.path"><slot /></a>',
  },
}));

describe('Customer BarbersPage.vue', () => {
  const mockBarbers = [
    {
      id: 1,
      name: 'Marcus Vance',
      rating: 4.9,
      total_reviews: 18,
      experience_years: 7,
      is_available: true,
      chair_status: 'free',
      specialties: ['Skin Fade', 'Beard Trim'],
      bio: 'Master barber specializing in precision skin fades.',
      avatar: null,
    },
    {
      id: 2,
      name: 'Elena Rostova',
      rating: 4.7,
      total_reviews: 9,
      experience_years: 4,
      is_available: false,
      chair_status: 'busy',
      specialties: ['Scissor Cut', 'Styling'],
      bio: 'Creative scissor styling and modern classic textures.',
      avatar: null,
    },
  ];

  beforeEach(() => {
    vi.clearAllMocks();
    window.scrollTo = vi.fn();
  });

  it('renders barber list correctly after fetching', async () => {
    client.get.mockResolvedValueOnce({
      data: {
        success: true,
        data: mockBarbers,
      },
    });

    const wrapper = mount(BarbersPage);

    await flushPromises();

    const text = wrapper.text();
    expect(text).toContain('Marcus Vance');
    expect(text).toContain('Elena Rostova');
    expect(text).toContain('Skin Fade');
    expect(text).toContain('Scissor Cut');

    const cards = wrapper.findAll('[data-test="barber-card"]');
    expect(cards).toHaveLength(2);
  });

  it('filters barbers based on real-time search query', async () => {
    client.get.mockResolvedValueOnce({
      data: {
        success: true,
        data: mockBarbers,
      },
    });

    const wrapper = mount(BarbersPage);

    await flushPromises();

    const searchInput = wrapper.find('[data-test="search-barber-input"]');
    await searchInput.setValue('Marcus');

    const cards = wrapper.findAll('[data-test="barber-card"]');
    expect(cards).toHaveLength(1);
    expect(wrapper.text()).toContain('Marcus Vance');
    expect(wrapper.text()).not.toContain('Elena Rostova');
  });

  it('shows empty state when search query matches no barber', async () => {
    client.get.mockResolvedValueOnce({
      data: {
        success: true,
        data: mockBarbers,
      },
    });

    const wrapper = mount(BarbersPage);

    await flushPromises();

    const searchInput = wrapper.find('[data-test="search-barber-input"]');
    await searchInput.setValue('NonExistentStylistName');

    expect(wrapper.text()).toContain('No Stylists Found');
    expect(wrapper.findAll('[data-test="barber-card"]')).toHaveLength(0);
  });

  it('filters by availability when Available Now tab is selected', async () => {
    client.get.mockResolvedValueOnce({
      data: {
        success: true,
        data: mockBarbers,
      },
    });

    const wrapper = mount(BarbersPage);

    await flushPromises();

    // Click 'Available Now' tab
    const tabs = wrapper.findAll('button');
    const availableTab = tabs.find((btn) => btn.text().includes('Available Now'));
    expect(availableTab).toBeDefined();
    await availableTab.trigger('click');

    const cards = wrapper.findAll('[data-test="barber-card"]');
    expect(cards).toHaveLength(1);
    expect(wrapper.text()).toContain('Marcus Vance');
    expect(wrapper.text()).not.toContain('Elena Rostova');
  });
});
