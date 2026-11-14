import { describe, it, expect, vi } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import PaymentModalPage from '../PaymentModalPage.vue';

vi.mock('vue-router', () => ({
  useRoute: vi.fn(() => ({ params: {}, query: {} })),
  useRouter: vi.fn(() => ({ push: vi.fn(), back: vi.fn() }))
}));

vi.mock('@/shared/api/client', () => ({
  default: {
    get: vi.fn().mockResolvedValue({
      data: {
        success: true,
        data: {
          bank_details: {
            bank_name: 'Sterling Bank',
            account_name: 'NextGen PG',
            account_number: '5261935770',
            currency: 'NGN'
          },
          barbers: [
            { id: 1, name: 'Adeola Vance', chair_number: 1, avatar: null }
          ],
          services: [
            { id: 1, name: 'VIP Cut & Beard', price: 30000, duration_minutes: 45 }
          ],
          appointment: {
            id: 10,
            booking_reference: 'FLYX-20261031-PPJ047',
            amount: 30000
          }
        }
      }
    }),
    post: vi.fn().mockResolvedValue({
      data: {
        success: true,
        data: {
          reference: 'FLYX-20261031-PPJ047',
          payment_status: 'paid',
          barber: { id: 1, name: 'Adeola Vance', chair_number: 1 }
        }
      }
    })
  }
}));

describe('PaymentModalPage Component', () => {
  it('renders payment header, channel selector, and bank transfer details', async () => {
    const wrapper = mount(PaymentModalPage);
    await flushPromises();

    expect(wrapper.text()).toContain('Choose Payment Option');
    expect(wrapper.text()).toContain('Bank Transfer');
    expect(wrapper.text()).toContain('Sterling Bank');
    expect(wrapper.text()).toContain('5261935770');
    expect(wrapper.text()).toContain('NextGen PG');
    expect(wrapper.text()).toContain('I Have Paid');
  });

  it('submits payment and shows success receipt', async () => {
    const wrapper = mount(PaymentModalPage);
    await flushPromises();

    const payButton = wrapper.find('button.bg-\\[\\#FDB913\\]');
    expect(payButton.exists()).toBe(true);
    await payButton.trigger('click');
    await flushPromises();

    expect(wrapper.text()).toContain('Payment Confirmed & Saved');
    expect(wrapper.text()).toContain('Adeola Vance');
  });
});
