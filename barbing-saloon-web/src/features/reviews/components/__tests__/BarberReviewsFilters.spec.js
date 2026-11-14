import { mount } from '@vue/test-utils';
import BarberReviewsFilters from '../BarberReviewsFilters.vue';

describe('BarberReviewsFilters.vue', () => {
  const defaultProps = {
    activeStatusFilter: 'all',
    activeServiceFilter: 'all',
    activeRatingFilter: 'all',
    sortOrder: 'newest',
    services: [
      { id: 1, name: 'Skin Fade' },
      { id: 2, name: 'Beard Sculpting' },
    ],
    totalItems: 25,
    approvedCount: 20,
    pendingCount: 5,
    hasActiveFilters: false,
  };

  it('renders status tab buttons with correct counts', () => {
    const wrapper = mount(BarberReviewsFilters, {
      props: defaultProps,
    });

    expect(wrapper.text()).toContain('All Reviews (25)');
    expect(wrapper.text()).toContain('Published (20)');
    expect(wrapper.text()).toContain('Pending Moderation (5)');
  });

  it('emits update:activeStatusFilter when tab buttons are clicked', async () => {
    const wrapper = mount(BarberReviewsFilters, {
      props: defaultProps,
    });

    const buttons = wrapper.findAll('button');
    // Button 0: All Reviews, Button 1: Published, Button 2: Pending Moderation
    await buttons[1].trigger('click');
    expect(wrapper.emitted('update:activeStatusFilter')).toBeTruthy();
    expect(wrapper.emitted('update:activeStatusFilter')[0]).toEqual(['approved']);

    await buttons[2].trigger('click');
    expect(wrapper.emitted('update:activeStatusFilter')[1]).toEqual(['pending']);
  });

  it('emits resetFilters when reset button is clicked', async () => {
    const wrapper = mount(BarberReviewsFilters, {
      props: {
        ...defaultProps,
        hasActiveFilters: true,
      },
    });

    expect(wrapper.text()).toContain('Reset');
    const resetButton = wrapper.findAll('button').find((b) => b.text().includes('Reset'));
    expect(resetButton).toBeDefined();
    await resetButton.trigger('click');
    expect(wrapper.emitted('resetFilters')).toBeTruthy();
  });
});

