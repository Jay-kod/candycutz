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

  it('renders CustomerPasswordModal with simple presets and allows 1-click simple reset', async () => {
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

    expect(wrapper.text()).toContain('Generate Simple');
    expect(wrapper.text()).toContain('Quick Presets:');
    expect(wrapper.text()).toContain('Tunde1234');
    expect(wrapper.text()).toContain('Candy1234');

    // Click on preset 'Candy1234'
    const presetButtons = wrapper.findAll('button').filter(b => b.text() === 'Candy1234');
    expect(presetButtons.length).toBeGreaterThan(0);
    await presetButtons[0].trigger('click');

    const inputs = wrapper.findAll('input');
    expect(inputs[0].element.value).toBe('Candy1234');
    expect(inputs[1].element.value).toBe('Candy1234');

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.emitted('save')).toBeTruthy();
    expect(wrapper.emitted('save')[0][0]).toBe('Candy1234');
  });

  it('generates a simple temporary password on Generate Simple button click', async () => {
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

    const simpleBtn = wrapper.findAll('button').filter(b => b.text().includes('Generate Simple'))[0];
    await simpleBtn.trigger('click');

    const inputs = wrapper.findAll('input');
    expect(inputs[0].element.value.length).toBeGreaterThanOrEqual(8);
    expect(inputs[0].element.value).toBe(inputs[1].element.value);

    await wrapper.find('form').trigger('submit.prevent');
    expect(wrapper.emitted('save')).toBeTruthy();
  });
});
