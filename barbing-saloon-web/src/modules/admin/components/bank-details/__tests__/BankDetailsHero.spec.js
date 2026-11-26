import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BankDetailsHero from '../BankDetailsHero.vue';

describe('BankDetailsHero', () => {
  it('navigates to the dedicated Paystack connection tab', async () => {
    const wrapper = mount(BankDetailsHero, {
      props: {
        currentTab: 'account',
      },
    });

    const paystackTab = wrapper.findAll('button').find((button) => button.text().includes('Paystack Connection'));
    expect(paystackTab).toBeDefined();

    await paystackTab.trigger('click');

    expect(wrapper.emitted('update:currentTab')).toEqual([['paystack']]);
  });
});
