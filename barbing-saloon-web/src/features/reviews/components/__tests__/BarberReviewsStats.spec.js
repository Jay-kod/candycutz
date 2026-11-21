import { mount } from '@vue/test-utils';
import BarberReviewsStats from '../BarberReviewsStats.vue';

describe('BarberReviewsStats.vue', () => {
  it('renders computed review KPI metrics correctly', () => {
    const stats = {
      totalCount: 42,
      approvedCount: 38,
      pendingCount: 4,
      fiveStarCount: 35,
      averageRating: '4.9',
      satisfactionRate: 98,
    };

    const wrapper = mount(BarberReviewsStats, {
      props: { stats },
    });

    expect(wrapper.text()).toContain('4.9');
    expect(wrapper.text()).toContain('/ 5.0');
    expect(wrapper.text()).toContain('42');
    expect(wrapper.text()).toContain('Based on 42 feedback');
    expect(wrapper.text()).toContain('98% positive (4-5★)');
    expect(wrapper.text()).toContain('35');
    expect(wrapper.text()).toContain('perfect cuts');
    expect(wrapper.text()).toContain('38');
    expect(wrapper.text()).toContain('4 pending review');
  });

  it('handles empty and zero stats gracefully', () => {
    const stats = {
      totalCount: 0,
      approvedCount: 0,
      pendingCount: 0,
      fiveStarCount: 0,
      averageRating: '—',
      satisfactionRate: 100,
    };

    const wrapper = mount(BarberReviewsStats, {
      props: { stats },
    });

    expect(wrapper.text()).toContain('—');
    expect(wrapper.text()).toContain('All reviews published');
  });
});

