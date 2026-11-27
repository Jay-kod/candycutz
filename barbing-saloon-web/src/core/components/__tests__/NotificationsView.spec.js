import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import NotificationsView from '../NotificationsView.vue';
import NotificationFilterBar from '../notifications/NotificationFilterBar.vue';
import api from '@/shared/api/client';
import { resetNotifications } from '@/core/composables/useNotifications';
import { useAuthStore } from '@/modules/auth/store/auth.store';
import { timeFilters, typeFilters } from '../notifications/notificationUtils';

const mockPush = vi.fn();
const mockConfirm = vi.fn();
const mockToastSuccess = vi.fn();
const mockToastError = vi.fn();

vi.mock('@/shared/api/client', () => ({
  default: {
    get: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
  },
}));

vi.mock('vue-router', () => ({
  useRouter: () => ({ push: mockPush }),
  useRoute: () => ({ path: '/customer/dashboard/notifications' }),
}));

vi.mock('@/core/composables/useConfirm', () => ({
  useConfirm: () => ({
    confirm: mockConfirm,
  }),
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({
    success: mockToastSuccess,
    error: mockToastError,
    info: vi.fn(),
    warning: vi.fn(),
  }),
}));

describe('NotificationsView.vue', () => {
  const now = new Date();
  const sampleNotifications = [
    {
      id: 1,
      type: 'booking_completed',
      category: 'booking',
      priority: 'normal',
      title: 'Session Complete',
      message: 'Your Hot Towel Beard Sculpt & Hydration session is complete!',
      related_entity_id: 88,
      action_url: '/customer/dashboard/reviews?appointment_id=88',
      data: {
        booking_reference: 'CC-8801',
        verification_code: 'CODE88',
        barber_name: 'Marcus Vance',
      },
      is_read: false,
      created_at: new Date(now.getTime() - 8 * 60 * 1000).toISOString(),
    },
    {
      id: 2,
      type: 'payment_verified',
      category: 'payment',
      priority: 'normal',
      title: 'Payment Confirmed',
      message: 'Your payment of ₦5,000 has been verified.',
      related_entity_id: 89,
      action_url: '/customer/dashboard/bookings',
      data: {
        booking_reference: 'CC-8802',
        verification_code: 'PAY89X',
        amount: '5,000.00',
      },
      is_read: true,
      created_at: new Date(now.getTime() - 40 * 60 * 1000).toISOString(),
    },
  ];

  beforeEach(() => {
    vi.clearAllMocks();
    resetNotifications();
    localStorage.setItem('candycutz_auth_token', 'test-token');
    setActivePinia(createPinia());
    const auth = useAuthStore();
    auth.user = { id: 10, name: 'Test Client', role: 'customer' };

    api.get.mockImplementation((url) => {
      if (url === '/v1/notifications') {
        return Promise.resolve({ data: { success: true, data: structuredClone(sampleNotifications) } });
      }
      if (url === '/v1/notifications/unread-count') {
        return Promise.resolve({ data: { success: true, data: { count: 1 } } });
      }
      return Promise.resolve({ data: { success: true, data: [] } });
    });
    api.patch.mockResolvedValue({ data: { success: true } });
    api.delete.mockResolvedValue({ data: { success: true } });

    Object.assign(navigator, {
      clipboard: {
        writeText: vi.fn().mockResolvedValue(undefined),
      },
    });
  });

  it('renders grouped notifications, metadata chips, and non-404 action buttons', async () => {
    const wrapper = mount(NotificationsView, {
      global: {
        stubs: {
          Teleport: true,
        },
      },
    });
    await flushPromises();

    expect(wrapper.text()).toContain('Session Complete');
    expect(wrapper.text()).toContain('Payment Confirmed');
    expect(wrapper.text()).toContain('CODE88');
    expect(wrapper.text()).toContain('CC-8801');
    expect(wrapper.text()).toContain('Leave a Review');
    expect(wrapper.text()).toContain('View Bookings');
    expect(wrapper.text()).toContain('Clear all');
  });

  it('switches between All and Unread segmented tabs', async () => {
    const wrapper = mount(NotificationsView, {
      global: { stubs: { Teleport: true } },
    });
    await flushPromises();

    const unreadTab = wrapper.findAll('[role="tab"]').find((el) => el.text().includes('Unread'));
    expect(unreadTab).toBeTruthy();
    await unreadTab.trigger('click');

    expect(wrapper.text()).toContain('Session Complete');
    expect(wrapper.text()).not.toContain('Payment Confirmed');
  });

  it('copies verification code on 1-tap chip click', async () => {
    const wrapper = mount(NotificationsView, {
      global: { stubs: { Teleport: true } },
    });
    await flushPromises();

    const copyBtn = wrapper.findAll('button').find((b) => b.text().includes('Code: CODE88'));
    expect(copyBtn).toBeTruthy();
    await copyBtn.trigger('click');
    await flushPromises();

    expect(navigator.clipboard.writeText).toHaveBeenCalledWith('CODE88');
    expect(mockToastSuccess).toHaveBeenCalledWith('Booking code copied to clipboard');
  });

  it('opens detail sheet when tapping notification title and marks unread item read', async () => {
    const wrapper = mount(NotificationsView, {
      global: { stubs: { Teleport: true } },
    });
    await flushPromises();

    const titleBtn = wrapper.findAll('button').find((b) => b.text().includes('Session Complete'));
    await titleBtn.trigger('click');
    await flushPromises();

    expect(api.patch).toHaveBeenCalledWith('/v1/notifications/1/read');
    expect(wrapper.find('[role="dialog"]').exists()).toBe(true);
  });

  it('uses ConfirmModal via useConfirm instead of window.confirm on Clear all', async () => {
    mockConfirm.mockResolvedValueOnce(true);
    const windowConfirmSpy = vi.spyOn(window, 'confirm');

    const wrapper = mount(NotificationsView, {
      global: { stubs: { Teleport: true } },
    });
    await flushPromises();

    const clearAllBtn = wrapper.findAll('button').find((b) => b.text().includes('Clear all'));
    await clearAllBtn.trigger('click');
    await flushPromises();

    expect(windowConfirmSpy).not.toHaveBeenCalled();
    expect(mockConfirm).toHaveBeenCalledTimes(1);
    expect(api.delete).toHaveBeenCalledWith('/v1/notifications');
    windowConfirmSpy.mockRestore();
  });

  it('supports progressive load more when more than 20 notifications exist', async () => {
    const many = Array.from({ length: 24 }, (_, idx) => ({
      id: idx + 100,
      type: 'booking_created',
      title: `Booking Event #${idx + 1}`,
      message: 'Appointment updated',
      is_read: true,
      created_at: new Date(now.getTime() - idx * 60 * 1000).toISOString(),
    }));

    api.get.mockImplementation((url) => {
      if (url === '/v1/notifications') {
        return Promise.resolve({ data: { success: true, data: many } });
      }
      return Promise.resolve({ data: { success: true, data: { count: 0 } } });
    });

    const wrapper = mount(NotificationsView, {
      global: { stubs: { Teleport: true } },
    });
    await flushPromises();

    expect(wrapper.text()).toContain('Show older notifications (4 remaining)');
    const loadMoreBtn = wrapper
      .findAll('button')
      .find((b) => b.text().includes('Show older notifications'));
    await loadMoreBtn.trigger('click');
    expect(wrapper.text()).toContain('Booking Event #24');
  });
});

describe('NotificationFilterBar.vue', () => {
  it('highlights the selected activeTypeFilter instead of activeTimeFilter', () => {
    const wrapper = mount(NotificationFilterBar, {
      props: {
        showFilters: true,
        timeFilters,
        typeFilters,
        activeTimeFilter: 'all',
        activeTypeFilter: 'booking',
        hideStaffFilters: true,
      },
    });

    const buttons = wrapper.findAll('button');
    const allTypesBtn = buttons.find((b) => b.text().includes('All Types'));
    const bookingsBtn = buttons.find((b) => b.text().includes('Bookings'));
    const categoryReqBtn = buttons.find((b) => b.text().includes('Category Requests'));

    expect(allTypesBtn.classes().join(' ')).not.toContain('border-[var(--color-accent)]/30');
    expect(bookingsBtn.classes().join(' ')).toContain('text-[var(--color-accent)]');
    expect(categoryReqBtn).toBeUndefined();
  });
});
