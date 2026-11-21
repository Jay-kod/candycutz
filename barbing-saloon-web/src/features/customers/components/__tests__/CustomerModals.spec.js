import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import CustomerFormModal from '../CustomerFormModal.vue';
import CustomerPasswordModal from '../CustomerPasswordModal.vue';

describe('Customer Modals', () => {
  it('renders CustomerFormModal in create mode and emits save on submit', async () => {
    const wrapper = mount(CustomerFormModal, {
      props: {
        isOpen: true,
        mode: 'create',
        initialData: {},
        loading: false
      },
      global: {
        stubs: {
          teleport: true
        }
      }
    });

    expect(wrapper.text()).toContain('Register New Customer');
    expect(wrapper.text()).toContain('Create Customer');

    const nameInput = wrapper.find('input[type="text"]');
    const emailInput = wrapper.find('input[type="email"]');

    await nameInput.setValue('New Customer');
    await emailInput.setValue('new@example.com');

    await wrapper.find('form').trigger('submit.prevent');

    expect(wrapper.emitted('save')).toBeTruthy();
    expect(wrapper.emitted('save')[0][0].name).toBe('New Customer');
    expect(wrapper.emitted('save')[0][0].email).toBe('new@example.com');
  });

  it('renders CustomerFormModal in edit mode with prefilled data', async () => {
    const wrapper = mount(CustomerFormModal, {
      props: {
        isOpen: true,
        mode: 'edit',
        initialData: {
          name: 'Existing Customer',
          email: 'existing@example.com',
          phone: '+2348000000000'
        },
        loading: false
      },
      global: {
        stubs: {
          teleport: true
        }
      }
    });

    expect(wrapper.text()).toContain('Edit Customer Profile');
    expect(wrapper.text()).toContain('Save Changes');

    const nameInput = wrapper.find('input[type="text"]');
    expect(nameInput.element.value).toBe('Existing Customer');
  });

  it('renders CustomerPasswordModal and validates matching passwords before emit', async () => {
    const wrapper = mount(CustomerPasswordModal, {
      props: {
        isOpen: true,
        customer: { id: 1, name: 'Tunde Client', email: 'tunde@example.com' },
        loading: false
      },
      global: {
        stubs: {
          teleport: true
        }
      }
    });

    expect(wrapper.text()).toContain('Reset Password');
    expect(wrapper.text()).toContain('Tunde Client');

    const inputs = wrapper.findAll('input');
    await inputs[0].setValue('NewSecretPass123!');
    await inputs[1].setValue('NewSecretPass123!');

    await wrapper.find('form').trigger('submit.prevent');

    expect(wrapper.emitted('save')).toBeTruthy();
    expect(wrapper.emitted('save')[0][0]).toBe('NewSecretPass123!');
  });
});
