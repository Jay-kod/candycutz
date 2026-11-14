import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useCustomers } from '../useCustomers';
import { adminApi } from '@/shared/api/old_adminApi';

vi.mock('@/shared/api/old_adminApi', () => ({
  adminApi: {
    customers: vi.fn(),
    customerProfile: vi.fn(),
    createCustomer: vi.fn(),
    updateCustomer: vi.fn(),
    updateCustomerStatus: vi.fn(),
    resetCustomerPassword: vi.fn(),
    deleteCustomer: vi.fn(),
  },
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({
    success: vi.fn(),
    error: vi.fn(),
  }),
}));

vi.mock('@/core/composables/useConfirm', () => ({
  useConfirm: () => ({
    confirm: vi.fn().mockResolvedValue(true),
  }),
}));

describe('useCustomers Composable', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('manages modal states for adding, editing, and password reset', () => {
    const {
      isCustomerModalOpen,
      customerModalMode,
      customerModalData,
      openCreateModal,
      openEditModal,
      closeCustomerModal,
      isPasswordModalOpen,
      passwordModalCustomer,
      openPasswordModal,
      closePasswordModal
    } = useCustomers();

    expect(isCustomerModalOpen.value).toBe(false);

    openCreateModal();
    expect(isCustomerModalOpen.value).toBe(true);
    expect(customerModalMode.value).toBe('create');

    closeCustomerModal();
    expect(isCustomerModalOpen.value).toBe(false);

    openEditModal({ id: 5, name: 'John Doe', email: 'john@example.com' });
    expect(isCustomerModalOpen.value).toBe(true);
    expect(customerModalMode.value).toBe('edit');
    expect(customerModalData.value.name).toBe('John Doe');

    openPasswordModal({ id: 5, name: 'John Doe' });
    expect(isPasswordModalOpen.value).toBe(true);
    expect(passwordModalCustomer.value.name).toBe('John Doe');

    closePasswordModal();
    expect(isPasswordModalOpen.value).toBe(false);
  });

  it('calls createCustomer and reloads customer roster on saveCustomer in create mode', async () => {
    adminApi.createCustomer.mockResolvedValueOnce({ data: { success: true } });
    adminApi.customers.mockResolvedValueOnce({ data: { data: [{ id: 1, name: 'New Client' }] } });

    const { openCreateModal, saveCustomer, customers } = useCustomers();
    openCreateModal();

    await saveCustomer({ name: 'New Client', email: 'new@example.com' });

    expect(adminApi.createCustomer).toHaveBeenCalledWith({ name: 'New Client', email: 'new@example.com' });
    expect(customers.value.length).toBe(1);
  });

  it('calls deleteCustomer and updates local list upon confirmation', async () => {
    adminApi.deleteCustomer.mockResolvedValueOnce({ data: { success: true } });
    adminApi.customers.mockResolvedValueOnce({ data: { data: [] } });

    const { deleteCustomer, customers } = useCustomers();
    customers.value = [{ id: 10, name: 'Deleted User' }];

    await deleteCustomer({ id: 10, name: 'Deleted User' });

    expect(adminApi.deleteCustomer).toHaveBeenCalledWith(10);
    expect(adminApi.customers).toHaveBeenCalled();
  });

  it('toggles customer status and updates active state', async () => {
    adminApi.updateCustomerStatus.mockResolvedValueOnce({
      data: { data: { id: 7, is_active: false, status: 'suspended' } }
    });

    const { toggleStatus } = useCustomers();
    const customer = { id: 7, name: 'Active User', is_active: true, status: 'active' };

    await toggleStatus(customer);

    expect(adminApi.updateCustomerStatus).toHaveBeenCalledWith(7, { is_active: false });
    expect(customer.is_active).toBe(false);
    expect(customer.status).toBe('suspended');
  });

  it('resets customer password successfully', async () => {
    adminApi.resetCustomerPassword.mockResolvedValueOnce({ data: { success: true } });

    const { openPasswordModal, savePassword } = useCustomers();
    openPasswordModal({ id: 12, name: 'Reset User' });

    await savePassword('BrandNewPass123!');

    expect(adminApi.resetCustomerPassword).toHaveBeenCalledWith(12, { password: 'BrandNewPass123!' });
  });
});

