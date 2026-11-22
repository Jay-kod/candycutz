import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import DraftBookingBanner from '../DraftBookingBanner.vue';
import client from '@/shared/api/client';

vi.mock('@/shared/api/client', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    delete: vi.fn(),
  },
}));

const mockPush = vi.fn();
vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush,
  }),
}));

const mockToast = {
  success: vi.fn(),
  error: vi.fn(),
  warning: vi.fn(),
  info: vi.fn(),
};
vi.mock('@/core/composables/useToast', () => ({
  useToast: () => mockToast,
}));

describe('DraftBookingBanner.vue', () => {
  const mockDraft = {
    id: 42,
    booking_reference: 'CC-DRAFT9',
    appointment_date: '2026-11-25',
    appointment_time: '14:30',
    draft_expires_at: new Date(Date.now() + 600000).toISOString(),
    draft_time_remaining_seconds: 600,
    service: {
      name: 'Royal Signature Cut',
    },
    barber: {
      name: 'Emmanuel Stylist',
    },
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders nothing when no draft is provided and autoFetch returns empty', async () => {
    client.get.mockResolvedValueOnce({ data: { data: [] } });
    const wrapper = mount(DraftBookingBanner, {
      props: { autoFetch: true },
    });

    await wrapper.vm.$nextTick();
    expect(wrapper.find('div').exists()).toBe(false);
  });

  it('renders draft details and countdown when draft prop is provided', () => {
    const wrapper = mount(DraftBookingBanner, {
      props: {
        draft: mockDraft,
        autoFetch: false,
      },
    });

    expect(wrapper.text()).toContain('Unfinished Booking');
    expect(wrapper.text()).toContain('Royal Signature Cut with Emmanuel Stylist');
    expect(wrapper.text()).toContain('CC-DRAFT9');
    expect(wrapper.text()).toContain('Continue to Payment');
    expect(wrapper.text()).toContain('Discard Draft');
  });

  it('navigates to payment and emits resumed on Continue to Payment click', async () => {
    client.post.mockResolvedValueOnce({ data: { data: mockDraft } });

    const wrapper = mount(DraftBookingBanner, {
      props: {
        draft: mockDraft,
        autoFetch: false,
      },
    });

    const resumeBtn = wrapper.findAll('button').find(b => b.text().includes('Continue to Payment'));
    expect(resumeBtn).toBeDefined();

    await resumeBtn.trigger('click');
    expect(client.post).toHaveBeenCalledWith('/appointments/drafts/42/resume');
    expect(wrapper.emitted('resumed')).toBeTruthy();
    expect(mockPush).toHaveBeenCalledWith('/customer/dashboard/payment/42');
  });

  it('calls delete API, displays toast, and emits discarded on Discard Draft click', async () => {
    client.delete.mockResolvedValueOnce({ data: { success: true } });

    const wrapper = mount(DraftBookingBanner, {
      props: {
        draft: mockDraft,
        autoFetch: false,
      },
    });

    const discardBtn = wrapper.findAll('button').find(b => b.text().includes('Discard Draft'));
    expect(discardBtn).toBeDefined();

    await discardBtn.trigger('click');
    expect(client.delete).toHaveBeenCalledWith('/customer/drafts/42');
    expect(mockToast.success).toHaveBeenCalledWith(expect.stringContaining('discarded'));
    expect(wrapper.emitted('discarded')).toBeTruthy();
  });
});
