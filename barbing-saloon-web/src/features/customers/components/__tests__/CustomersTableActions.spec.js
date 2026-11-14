import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import CustomersTable from '../CustomersTable.vue';

describe('CustomersTable Actions', () => {
  const sampleCustomer = {
    id: 42,
    name: 'Samuel Adekunle',
    email: 'samuel@example.com',
    phone: '+2348012345678',
    is_active: true,
    status: 'active',
    auth_provider: 'local',
    registered_via: 'Registration form',
    avatar: null,
    created_at: '2026-01-15T10:00:00Z',
    total_bookings: 5,
    total_spent: 35000,
    last_booking_date: '2026-03-01'
  };

  it('renders all action buttons: Profile, Edit, Password, Suspend/Activate, and Delete', () => {
    const wrapper = mount(CustomersTable, {
      props: {
        customers: [sampleCustomer],
        search: '',
        currentFilter: 'all'
      }
    });

    // Check button presence
    const buttons = wrapper.findAll('button');
    expect(buttons.length).toBeGreaterThanOrEqual(5);

    // Profile button text
    expect(wrapper.text()).toContain('Profile');
    expect(wrapper.text()).toContain('Active');

    // Emits when Profile button is clicked
    const profileBtn = buttons.find(b => b.text().includes('Profile'));
    profileBtn.trigger('click');
    expect(wrapper.emitted('viewProfile')).toBeTruthy();
    expect(wrapper.emitted('viewProfile')[0][0]).toEqual(sampleCustomer);
  });

  it('emits editCustomer, resetPassword, toggleStatus, and deleteCustomer on button clicks', async () => {
    const wrapper = mount(CustomersTable, {
      props: {
        customers: [sampleCustomer],
        search: '',
        currentFilter: 'all'
      }
    });

    const editBtn = wrapper.find('button[title="Edit Customer"]');
    expect(editBtn.exists()).toBe(true);
    await editBtn.trigger('click');
    expect(wrapper.emitted('editCustomer')).toBeTruthy();
    expect(wrapper.emitted('editCustomer')[0][0]).toEqual(sampleCustomer);

    const pwdBtn = wrapper.find('button[title="Reset Password"]');
    expect(pwdBtn.exists()).toBe(true);
    await pwdBtn.trigger('click');
    expect(wrapper.emitted('resetPassword')).toBeTruthy();
    expect(wrapper.emitted('resetPassword')[0][0]).toEqual(sampleCustomer);

    const toggleBtn = wrapper.find('button[title="Suspend Customer"]');
    expect(toggleBtn.exists()).toBe(true);
    await toggleBtn.trigger('click');
    expect(wrapper.emitted('toggleStatus')).toBeTruthy();
    expect(wrapper.emitted('toggleStatus')[0][0]).toEqual(sampleCustomer);

    const deleteBtn = wrapper.find('button[title="Delete Customer"]');
    expect(deleteBtn.exists()).toBe(true);
    await deleteBtn.trigger('click');
    expect(wrapper.emitted('deleteCustomer')).toBeTruthy();
    expect(wrapper.emitted('deleteCustomer')[0][0]).toEqual(sampleCustomer);
  });

  it('displays Suspended badge and Activate button for suspended customers', () => {
    const suspendedCustomer = {
      ...sampleCustomer,
      id: 99,
      is_active: false,
      status: 'suspended'
    };

    const wrapper = mount(CustomersTable, {
      props: {
        customers: [suspendedCustomer],
        search: '',
        currentFilter: 'all'
      }
    });

    expect(wrapper.text()).toContain('Suspended');
    const activateBtn = wrapper.find('button[title="Activate Customer"]');
    expect(activateBtn.exists()).toBe(true);
  });
});

