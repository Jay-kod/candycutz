import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BrowseServiceCard from '../BrowseServiceCard.vue';

describe('BrowseServiceCard.vue', () => {
  const service = {
    id: 1,
    name: 'Classic Cut',
    description: 'Precision haircut with clean contours',
    price: 1500,
    duration_minutes: 30,
    category_name: 'Haircut',
    category: { id: 1, name: 'Haircut' },
    is_home_service_eligible: true,
    barber: { id: 1, name: 'Chukwudi' }
  };

  it('renders service details, price, duration and category badge', () => {
    const wrapper = mount(BrowseServiceCard, {
      props: {
        service,
        isWishlisted: false,
        isToggling: false
      },
      global: {
        stubs: {
          RouterLink: {
            template: '<a><slot /></a>'
          }
        }
      }
    });

    const text = wrapper.text();
    expect(text).toContain('Classic Cut');
    expect(text).toContain('Haircut');
    expect(text).toContain('30 mins');
    expect(text).toContain('Home Service');
    expect(text).toContain('Chukwudi');
  });

  it('emits toggle-wishlist with service id when heart button is clicked', async () => {
    const wrapper = mount(BrowseServiceCard, {
      props: {
        service,
        isWishlisted: false,
        isToggling: false
      },
      global: {
        stubs: {
          RouterLink: {
            template: '<a><slot /></a>'
          }
        }
      }
    });

    const wishlistBtn = wrapper.find('button[title="Add to wishlist"]');
    expect(wishlistBtn.exists()).toBe(true);

    await wishlistBtn.trigger('click');
    expect(wrapper.emitted('toggle-wishlist')).toBeTruthy();
    expect(wrapper.emitted('toggle-wishlist')[0]).toEqual([1]);
  });
});

