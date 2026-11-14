import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ServicesGrid from '../ServicesGrid.vue';

describe('ServicesGrid.vue', () => {
  const mockServices = [
    {
      id: 1,
      name: 'Classic Cut',
      price: 2500,
      duration_minutes: 30,
      category_name: 'Haircuts',
      barber_id: null,
      approval_status: 'approved',
      image: '/images/services/service-1.png',
    },
    {
      id: 2,
      name: 'Custom Royal Beard',
      price: 5000,
      duration_minutes: 45,
      category_name: 'Beard Grooming',
      barber_id: 2,
      approval_status: 'pending',
      image_url: 'http://localhost:8000/storage/uploads/services/beard.jpg',
    },
  ];

  it('renders empty state when filteredServices is empty', () => {
    const wrapper = mount(ServicesGrid, {
      props: {
        filteredServices: [],
      },
      global: {
        stubs: {
          'router-link': { template: '<a><slot /></a>' },
        },
      },
    });

    expect(wrapper.text()).toContain('No Services In This Filter');
    expect(wrapper.text()).toContain('Add Custom Service');
  });

  it('renders services list with official salon badge and pending approval badge', () => {
    const wrapper = mount(ServicesGrid, {
      props: {
        filteredServices: mockServices,
      },
      global: {
        stubs: {
          'router-link': { template: '<a><slot /></a>' },
        },
      },
    });

    const cards = wrapper.findAll('[data-test="service-card"]');
    expect(cards.length).toBe(2);

    // First card: Admin official service
    expect(cards[0].text()).toContain('Classic Cut');
    expect(cards[0].text()).toContain('Salon Official');
    expect(cards[0].text()).toContain('Salon Managed');

    // Second card: Barber custom service pending approval
    expect(cards[1].text()).toContain('Custom Royal Beard');
    expect(cards[1].text()).toContain('My Service');
    expect(cards[1].text()).toContain('Pending Admin Approval');
  });

  it('renders service images and resolves image URLs correctly', () => {
    const wrapper = mount(ServicesGrid, {
      props: {
        filteredServices: mockServices,
      },
      global: {
        stubs: {
          'router-link': { template: '<a><slot /></a>' },
        },
      },
    });

    const images = wrapper.findAll('[data-test="service-image"]');
    expect(images.length).toBe(2);
    expect(images[0].attributes('src')).toBe('/images/services/service-1.png');
    expect(images[1].attributes('src')).toBe('http://localhost:8000/storage/uploads/services/beard.jpg');
  });

  it('falls back to signature luxury placeholder when an image fails to load', async () => {
    const wrapper = mount(ServicesGrid, {
      props: {
        filteredServices: [
          {
            id: 99,
            name: 'Broken Image Cut',
            price: 3000,
            duration_minutes: 30,
            category_name: 'Haircuts',
            barber_id: null,
            image: '/non-existent-image.png',
          },
        ],
      },
      global: {
        stubs: {
          'router-link': { template: '<a><slot /></a>' },
        },
      },
    });

    let img = wrapper.find('[data-test="service-image"]');
    expect(img.exists()).toBe(true);

    // Trigger image loading error
    await img.trigger('error');

    // Image is removed, placeholder is shown
    expect(wrapper.find('[data-test="service-image"]').exists()).toBe(false);
    const placeholder = wrapper.find('[data-test="service-placeholder"]');
    expect(placeholder.exists()).toBe(true);
    expect(placeholder.text()).toContain('CandyCutz Signature');
  });

  it('emits delete event when clicking delete on a barber custom service', async () => {
    const wrapper = mount(ServicesGrid, {
      props: {
        filteredServices: mockServices,
      },
      global: {
        stubs: {
          'router-link': { template: '<a><slot /></a>' },
        },
      },
    });

    const deleteBtn = wrapper.find('[data-test="delete-service-btn"]');
    expect(deleteBtn.exists()).toBe(true);

    await deleteBtn.trigger('click');
    expect(wrapper.emitted('delete')).toBeTruthy();
    expect(wrapper.emitted('delete')[0][0]).toEqual(mockServices[1]);
  });
});
