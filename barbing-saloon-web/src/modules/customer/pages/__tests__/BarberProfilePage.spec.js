import { mount, flushPromises } from '@vue/test-utils';
import BarberProfilePage from '../BarberProfilePage.vue';
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
    params: { id: '1' },
    query: {},
  }),
  RouterLink: {
    name: 'RouterLink',
    props: ['to'],
    template: '<a :href="typeof to === \'string\' ? to : to.path"><slot /></a>',
  },
}));

describe('BarberProfilePage.vue Working Hours', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('correctly renders open chair hours and off days for working schedule', async () => {
    client.get.mockResolvedValueOnce({
      data: {
        data: {
          id: 1,
          name: 'Marcus Vance',
          rating: 4.9,
          total_reviews: 12,
          experience_years: 5,
          is_available: true,
          chair_status: 'free',
          specialties: ['Skin Fade', 'Beard Trim'],
          bio: 'Expert barber',
          avatar_url: null,
          working_hours: [
            { day_of_week: 0, open_time: '10:00:00', close_time: '18:00:00', is_closed: true },
            { day_of_week: 1, open_time: '08:00:00', close_time: '20:00:00', is_closed: false },
            { day_of_week: 2, open_time: '08:00:00', close_time: '20:00:00', is_closed: false },
            { day_of_week: 3, open_time: '08:00:00', close_time: '20:00:00', is_closed: false },
            { day_of_week: 4, open_time: '08:00:00', close_time: '20:00:00', is_closed: false },
            { day_of_week: 5, open_time: '08:00:00', close_time: '20:00:00', is_closed: false },
            { day_of_week: 6, open_time: '08:00:00', close_time: '20:00:00', is_closed: false },
          ],
          gallery: [],
          testimonials: [],
        },
      },
    });

    const wrapper = mount(BarberProfilePage);
    await flushPromises();

    expect(wrapper.text()).toContain('Regular Chair Hours');
    // Sunday should be off day
    expect(wrapper.text()).toContain('Off Day');
    // Monday-Saturday should show actual times, not Off Day!
    expect(wrapper.text()).toContain('08:00 - 20:00');
  });

  it('supports start_time / end_time and is_working schema aliases', async () => {
    client.get.mockResolvedValueOnce({
      data: {
        data: {
          id: 1,
          name: 'Marcus Vance',
          rating: 4.9,
          is_available: true,
          chair_status: 'free',
          working_hours: [
            { day_of_week: 1, start_time: '09:00', end_time: '19:00', is_working: true },
            { day_of_week: 0, start_time: '10:00', end_time: '18:00', is_working: false },
          ],
          gallery: [],
          testimonials: [],
        },
      },
    });

    const wrapper = mount(BarberProfilePage);
    await flushPromises();

    expect(wrapper.text()).toContain('09:00 - 19:00');
    expect(wrapper.text()).toContain('Off Day');
  });

  it('opens lightbox modal to view profile picture when avatar is clicked', async () => {
    client.get.mockResolvedValueOnce({
      data: {
        data: {
          id: 1,
          name: 'Marcus Vance',
          avatar_url: 'https://example.com/avatar.jpg',
          cover_image_url: 'https://example.com/cover.jpg',
          gallery: [],
          testimonials: [],
          working_hours: [],
        },
      },
    });

    const wrapper = mount(BarberProfilePage);
    await flushPromises();

    const avatarBtn = wrapper.find('[data-test="btn-view-avatar"]');
    expect(avatarBtn.exists()).toBe(true);

    await avatarBtn.trigger('click');

    const modal = wrapper.find('[data-test="lightbox-modal"]');
    expect(modal.exists()).toBe(true);
    expect(modal.text()).toContain('Profile Picture');
    const singleImg = wrapper.find('[data-test="lightbox-single-img"]');
    expect(singleImg.attributes('src')).toBe('https://example.com/avatar.jpg');

    // Close lightbox
    const closeBtn = wrapper.find('[data-test="lightbox-close"]');
    await closeBtn.trigger('click');
    expect(wrapper.find('[data-test="lightbox-modal"]').exists()).toBe(false);
  });

  it('opens lightbox modal to view cover picture when View Cover button is clicked', async () => {
    client.get.mockResolvedValueOnce({
      data: {
        data: {
          id: 1,
          name: 'Marcus Vance',
          avatar_url: 'https://example.com/avatar.jpg',
          cover_image_url: 'https://example.com/cover.jpg',
          gallery: [],
          testimonials: [],
          working_hours: [],
        },
      },
    });

    const wrapper = mount(BarberProfilePage);
    await flushPromises();

    const viewCoverBtn = wrapper.find('[data-test="btn-view-cover"]');
    expect(viewCoverBtn.exists()).toBe(true);

    await viewCoverBtn.trigger('click');

    const modal = wrapper.find('[data-test="lightbox-modal"]');
    expect(modal.exists()).toBe(true);
    expect(modal.text()).toContain('Cover Picture');
    const singleImg = wrapper.find('[data-test="lightbox-single-img"]');
    expect(singleImg.attributes('src')).toBe('https://example.com/cover.jpg');
  });
});

