import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import AuditLogsPage from '../AuditLogsPage.vue';
import { superadminApi } from '@/shared/api/old_superadminApi';

vi.mock('@/shared/api/old_superadminApi', () => ({
  superadminApi: {
    auditLogs: vi.fn(),
  },
}));

vi.mock('@/portals/superadmin/layouts/SuperAdminLayout.vue', () => ({
  default: {
    name: 'SuperAdminLayout',
    template: '<div class="super-admin-layout-stub"><slot /></div>',
  },
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({
    error: vi.fn(),
    success: vi.fn(),
  }),
}));

describe('AuditLogsPage.vue', () => {
  const mockLogs = [
    {
      id: 101,
      action: 'user_deactivated',
      module: 'SuperAdmin',
      target_type: 'User',
      target_id: 15,
      ip_address: '192.168.1.100',
      user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
      created_at: '2026-11-22T04:30:00Z',
      user_role: 'super_admin',
      user: {
        id: 1,
        name: 'Chief Admin',
        email: 'chief@candycutz.internal',
      },
      old_value: { is_active: true, status: 'active' },
      new_value: { is_active: false, status: 'deactivated' },
    },
    {
      id: 102,
      action: 'backup_created',
      module: 'SystemBackup',
      target_type: null,
      target_id: null,
      ip_address: '127.0.0.1',
      user_agent: 'Console/System',
      created_at: '2026-11-22T04:35:00Z',
      user_role: 'admin',
      user: {
        id: 2,
        name: 'Sarah Floor',
        email: 'sarah@candycutz.internal',
      },
      old_value: null,
      new_value: { filename: 'cc_backup_20261122.sql.enc', is_encrypted: true },
    },
  ];

  beforeEach(() => {
    vi.clearAllMocks();
    superadminApi.auditLogs.mockResolvedValue({
      data: {
        data: mockLogs,
        meta: {
          current_page: 1,
          last_page: 2,
          total: 2,
          from: 1,
          to: 2,
        },
      },
    });
  });

  it('renders page header and loads audit logs table', async () => {
    const wrapper = mount(AuditLogsPage);
    await wrapper.vm.$nextTick();
    await new Promise((resolve) => setTimeout(resolve, 50));

    expect(superadminApi.auditLogs).toHaveBeenCalled();
    expect(wrapper.text()).toContain('Audit Activity Trail');
    expect(wrapper.text()).toContain('user_deactivated');
    expect(wrapper.text()).toContain('backup_created');
  });

  it('opens forensic detail modal when a row is clicked and displays state delta', async () => {
    const wrapper = mount(AuditLogsPage);
    await wrapper.vm.$nextTick();
    await new Promise((resolve) => setTimeout(resolve, 50));

    const rows = wrapper.findAll('tbody tr');
    expect(rows.length).toBeGreaterThanOrEqual(2);

    // Click first row to open detail modal
    await rows[0].trigger('click');
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).toContain('Administrative Action Forensic Detail');
    expect(wrapper.text()).toContain('Chief Admin');
    expect(wrapper.text()).toContain('192.168.1.100');
    expect(wrapper.text()).toContain('State Delta (Audit Payload)');
    expect(wrapper.text()).toContain('is_active');
  });

  it('renders pagination bar with page information and buttons', async () => {
    const wrapper = mount(AuditLogsPage);
    await wrapper.vm.$nextTick();
    await new Promise((resolve) => setTimeout(resolve, 50));

    expect(wrapper.text()).toContain('Showing');
    expect(wrapper.text()).toContain('Page 1 of 2');

    const nextBtn = wrapper.findAll('button').find((b) => b.text().includes('Next'));
    expect(nextBtn).toBeDefined();
    await nextBtn.trigger('click');

    expect(superadminApi.auditLogs).toHaveBeenCalledWith(expect.objectContaining({ page: 2 }));
  });
});
