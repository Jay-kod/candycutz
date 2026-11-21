import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import PaymentTransactionsManager from '../PaymentTransactionsManager.vue';
import client from '@/shared/api/client';

vi.mock('@/shared/api/client', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
  }
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({
    success: vi.fn(),
    error: vi.fn(),
    info: vi.fn(),
  })
}));

describe('PaymentTransactionsManager', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    client.get.mockImplementation((url) => {
      if (url.includes('/admin/payments')) {
        return Promise.resolve({
          data: {
            data: [
              {
                id: 1,
                reference: 'FLYX-20261113-ABC123',
                amount: 30000,
                channel: 'bank_transfer',
                status: 'successful',
                created_at: '2026-11-13T10:00:00Z',
                customer: { name: 'Ada Obi', email: 'ada@example.com' },
                appointment: {
                  client_name: 'Ada Obi',
                  barber: { user: { name: 'Marcus Sterling' } }
                }
              }
            ]
          }
        });
      }
      if (url.includes('/barbers')) {
        return Promise.resolve({
          data: {
            data: [
              { id: 1, user: { name: 'Marcus Sterling' }, chair_number: 1 },
              { id: 2, user: { name: 'David Fade' }, chair_number: 2 }
            ]
          }
        });
      }
      return Promise.resolve({ data: {} });
    });
  });

  it('renders transactions table with payments and assigned barber', async () => {
    const wrapper = mount(PaymentTransactionsManager);
    await flushPromises();

    expect(wrapper.text()).toContain('Live Payment Transactions & Barber Control');
    expect(wrapper.text()).toContain('FLYX-20261113-ABC123');
    expect(wrapper.text()).toContain('Ada Obi');
    expect(wrapper.text()).toContain('Marcus Sterling');
    expect(wrapper.text()).toContain('successful');
  });
});
