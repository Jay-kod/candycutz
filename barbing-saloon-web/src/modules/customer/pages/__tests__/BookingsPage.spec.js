import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import BookingsPage from '../BookingsPage.vue';
import { client } from '@/shared/api/client';

const mockToastSuccess = vi.fn();
const mockToastError = vi.fn();
const mockConfirm = vi.fn();

vi.mock('@/shared/api/client', () => {
  const mockClient = {
    get: vi.fn(),
    post: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
  };
  return {
    default: mockClient,
    client: mockClient,
  };
});

vi.mock('@/portals/customer/layouts/CustomerLayout.vue', () => ({
  default: {
    name: 'CustomerLayout',
    template: '<div><slot /></div>',
  },
}));

vi.mock('vue-router', () => ({
  useRouter: () => ({ push: vi.fn() }),
  useRoute: () => ({ path: '/customer/dashboard/bookings' }),
  RouterLink: {
    name: 'RouterLink',
    props: ['to'],
    template: '<a :href="typeof to === \'string\' ? to : to.path"><slot /></a>',
  },
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({
    success: mockToastSuccess,
    error: mockToastError,
    info: vi.fn(),
    warning: vi.fn(),
  }),
}));

vi.mock('@/core/composables/useConfirm', () => ({
  useConfirm: () => ({
    confirm: mockConfirm,
  }),
}));

const MOCK_BOOKINGS = [
  {
    id: 11,
    booking_reference: 'CC-DRFT11',
    appointment_date: '2026-11-28',
    appointment_time: '10:00',
    status: 'draft',
    payment_status: 'unpaid',
    grand_total: 6500,
    service: { id: 1, name: 'Executive Skin Fade', price: 6500 },
    barber: { id: 2, name: 'Obo Shadow' },
  },
  {
    id: 12,
    booking_reference: 'CC-PEND12',
    appointment_date: '2026-11-29',
    appointment_time: '14:30',
    status: 'pending',
    payment_status: 'pending',
    notes: 'Razor edge up cleanly',
    grand_total: 8000,
    service: { id: 2, name: 'Signature Fade & Beard Sculpt', price: 8000 },
    barber: { id: 3, name: 'Master Blade' },
  },
  {
    id: 13,
    booking_reference: 'CC-CONF13',
    appointment_date: '2026-11-30',
    appointment_time: '16:00',
    status: 'confirmed',
    payment_status: 'paid',
    deposit_paid: true,
    verification_code: 'CUTZ99',
    grand_total: 9500,
    service: { id: 3, name: 'Hot Towel Shave Package', price: 9500 },
    barber: { id: 4, name: 'Emmanuel Stylist' },
  },
  {
    id: 14,
    booking_reference: 'CC-COMP14',
    appointment_date: '2026-11-20',
    appointment_time: '11:00',
    status: 'completed',
    payment_status: 'paid',
    deposit_paid: true,
    grand_total: 5000,
    service: { id: 4, name: 'Classic Gentleman Trim', price: 5000 },
    barber: { id: 2, name: 'Obo Shadow' },
  },
];

describe('Customer BookingsPage.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockConfirm.mockResolvedValue(true);

    Object.defineProperty(navigator, 'clipboard', {
      value: { writeText: vi.fn().mockResolvedValue(undefined) },
      configurable: true,
    });

    client.get.mockImplementation((url) => {
      if (url === '/customer/bookings') {
        return Promise.resolve({ data: { success: true, data: MOCK_BOOKINGS } });
      }
      if (url === '/customer/drafts') {
        return Promise.resolve({ data: { success: true, data: [] } });
      }
      if (url === '/settings') {
        return Promise.resolve({
          data: {
            success: true,
            data: {
              bank_name: 'First Bank Nigeria',
              account_name: 'CandyCutz Atelier',
              account_number: '2034567890',
            },
          },
        });
      }
      return Promise.resolve({ data: { success: true, data: {} } });
    });

    client.post.mockResolvedValue({ data: { success: true } });
    client.patch.mockResolvedValue({ data: { success: true } });
    client.delete.mockResolvedValue({ data: { success: true } });
  });

  it('loads bookings, displays status filter tabs, and renders settings bank details fallback', async () => {
    const wrapper = mount(BookingsPage);
    await flushPromises();

    expect(wrapper.text()).toContain('My Bookings');
    expect(wrapper.text()).toContain('Showing 4 of 4 appointments');
    expect(wrapper.text()).toContain('Executive Skin Fade');
    expect(wrapper.text()).toContain('Signature Fade & Beard Sculpt');
    expect(wrapper.text()).toContain('Hot Towel Shave Package');
    expect(wrapper.text()).toContain('Classic Gentleman Trim');
    expect(wrapper.text()).toContain('First Bank Nigeria');
    expect(wrapper.text()).toContain('CUTZ99');
  });

  it('filters bookings across status tabs and resets to all from empty state', async () => {
    const wrapper = mount(BookingsPage);
    await flushPromises();

    const completedTab = wrapper.findAll('button[role="tab"]').find((b) => b.text().includes('Completed'));
    expect(completedTab).toBeDefined();
    await completedTab.trigger('click');

    expect(wrapper.text()).toContain('Showing 1 of 4 appointments');
    expect(wrapper.text()).toContain('Classic Gentleman Trim');
    expect(wrapper.text()).not.toContain('Executive Skin Fade');

    const cancelledTab = wrapper.findAll('button[role="tab"]').find((b) => b.text().includes('Cancelled'));
    await cancelledTab.trigger('click');
    expect(wrapper.text()).toContain('No Bookings Found');

    const resetBtn = wrapper.findAll('button').find((b) => b.text().includes('Show All Bookings'));
    expect(resetBtn).toBeDefined();
    await resetBtn.trigger('click');
    expect(wrapper.text()).toContain('Showing 4 of 4 appointments');
  });

  it('initiates checkout uploader and opens official receipt modal cleanly', async () => {
    const wrapper = mount(BookingsPage);
    await flushPromises();

    const uploadBtn = wrapper.findAll('button').find((b) => b.text().includes('Upload Manual Receipt Slip'));
    expect(uploadBtn).toBeDefined();
    await uploadBtn.trigger('click');
    await flushPromises();

    expect(client.post).toHaveBeenCalledWith(
      '/customer/checkout',
      expect.objectContaining({
        appointment_id: 12,
        payment_method: 'manual_transfer',
      })
    );
    expect(wrapper.text()).toContain('Upload Payment Receipt');

    const receiptBtn = wrapper.findAll('button').find((b) => b.text().includes('View Official Receipt'));
    expect(receiptBtn).toBeDefined();
    await receiptBtn.trigger('click');

    const dialog = wrapper.find('[role="dialog"]');
    expect(dialog.exists()).toBe(true);
    expect(dialog.text()).toContain('Payment Confirmed & Saved');
    expect(dialog.text()).toContain('CUTZ99');
  });

  it('supports cancelling, discarding drafts, deleting history, and copying verification codes', async () => {
    const wrapper = mount(BookingsPage);
    await flushPromises();

    const copyBtn = wrapper.find('button[aria-label="Copy verification code"]');
    await copyBtn.trigger('click');
    await flushPromises();
    expect(navigator.clipboard.writeText).toHaveBeenCalledWith('CUTZ99');
    expect(mockToastSuccess).toHaveBeenCalledWith('Copied to clipboard!');

    const discardBtn = wrapper.findAll('button').find((b) => b.text().includes('Discard Draft (Release Slot)'));
    await discardBtn.trigger('click');
    await flushPromises();
    expect(client.delete).toHaveBeenCalledWith('/customer/drafts/11');

    const cancelBtn = wrapper.findAll('button').find((b) => b.text().includes('Cancel Booking'));
    await cancelBtn.trigger('click');
    await flushPromises();
    expect(client.get).toHaveBeenCalledWith('/appointments/11/refund-eligibility', expect.any(Object));
    const confirmCancelForm = wrapper.findAll('form').find((f) => f.text().includes('Confirm Cancellation'));
    expect(confirmCancelForm).toBeDefined();
    await confirmCancelForm.trigger('submit.prevent');
    await flushPromises();
    expect(client.patch).toHaveBeenCalledWith('/appointments/11/cancel', expect.any(FormData), {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    const removeBtn = wrapper.find('button[aria-label="Remove booking from history"]');
    await removeBtn.trigger('click');
    await flushPromises();
    expect(client.delete).toHaveBeenCalledWith('/customer/bookings/11');
  });
});
