import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import PaymentCallbackPage from '../PaymentCallbackPage.vue';
import { client } from '@/shared/api/client';

const pushMock = vi.fn();

vi.mock('vue-router', () => ({
  useRoute: () => ({ query: { reference: 'PSK-RETRY-77' } }),
  useRouter: () => ({ push: pushMock }),
}));

vi.mock('@/shared/api/client', () => ({
  client: {
    get: vi.fn(),
  },
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({
    success: vi.fn(),
    error: vi.fn(),
    info: vi.fn(),
  }),
}));

describe('PaymentCallbackPage Resilience', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    pushMock.mockReset();
    client.get.mockReset();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it('polls past initial pending status and renders the redesigned receipt once confirmed', async () => {
    client.get
      .mockResolvedValueOnce({ data: { data: { status: 'pending' } } })
      .mockResolvedValueOnce({
        data: {
          data: {
            status: 'successful',
            amount_minor: 500000,
            payment_method: 'card',
            paid_at: '2026-11-25T13:10:00Z',
            appointment: {
              id: 77,
              booking_reference: 'CC-CB0077',
              verification_code: 'CC-CB0077',
              payment_status: 'paid',
              total_price: 5000,
              service: { id: 1, name: 'Classic Taper' },
              barber: { id: 2, name: 'Master Tunde', chair_number: 1 },
            },
          },
        },
      });

    const wrapper = mount(PaymentCallbackPage, {
      global: {
        stubs: {
          'router-link': { template: '<a><slot /></a>' },
        },
      },
    });

    await flushPromises();
    expect(wrapper.text()).toContain('Verifying Transaction');

    await vi.advanceTimersByTimeAsync(2000);
    await flushPromises();

    expect(client.get).toHaveBeenCalledTimes(2);
    expect(wrapper.text()).toContain('Payment Confirmed & Saved');
    expect(wrapper.text()).toContain('CC-CB0077');
    expect(wrapper.text()).toContain('RCP-000077');
  });

  it('offers manual re-check option when verification stalls beyond retry window', async () => {
    client.get.mockResolvedValue({ data: { data: { status: 'pending' } } });

    const wrapper = mount(PaymentCallbackPage, {
      global: {
        stubs: {
          'router-link': { template: '<a><slot /></a>' },
        },
      },
    });

    await flushPromises();
    await vi.advanceTimersByTimeAsync(2000);
    await flushPromises();
    await vi.advanceTimersByTimeAsync(3000);
    await flushPromises();
    await vi.advanceTimersByTimeAsync(4000);
    await flushPromises();

    expect(client.get).toHaveBeenCalledTimes(4);
    expect(wrapper.text()).toContain('Transaction Incomplete');
    expect(wrapper.text()).toContain('Re-check Payment Status');
  });
});
