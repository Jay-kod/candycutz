import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import DashboardPage from '../DashboardPage.vue';
import { superadminApi } from '@/shared/api/old_superadminApi';

vi.mock('@/shared/api/old_superadminApi', () => ({
  superadminApi: {
    dashboard: vi.fn(),
  },
}));

vi.mock('@/portals/superadmin/layouts/SuperAdminLayout.vue', () => ({
  default: {
    name: 'SuperAdminLayout',
    template: '<div class="super-admin-layout-stub"><slot /></div>',
  },
}));

describe('DashboardPage.vue', () => {
  const mockDashboardData = {
    stats: {
      total_users: 150,
      active_users: 142,
      total_admins: 4,
      barbers: 12,
      customers: 134,
    },
    subsystems: {
      database: { status: 'operational', latency_ms: 1.2 },
      paystack: { service: 'paystack', state: 'closed', failures_count: 0, is_available: true },
      resend: { service: 'resend', state: 'closed', failures_count: 0, is_available: true },
      sweeper: { status: 'optimal', last_run: '2026-11-22T05:00:00Z', cancelled_count: 0, duration_ms: 8.2 },
      security: { probes_blocked: 27, status: 'operational' },
      incidents: { unresolved_count: 0, failed_jobs_count: 0, status: 'optimal' },
    },
    recent_logs: [
      {
        id: 1,
        action: 'user_created',
        module: 'SuperAdmin',
        created_at: '2026-11-22T05:10:00Z',
        user: { name: 'Admin One' },
      },
    ],
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('loads dashboard data and renders subsystem telemetry strip and kpi cards', async () => {
    superadminApi.dashboard.mockResolvedValueOnce({
      data: {
        success: true,
        data: mockDashboardData,
      },
    });

    const wrapper = mount(DashboardPage, {
      global: {
        stubs: {
          RouterLink: {
            template: '<a :href="to"><slot /></a>',
            props: ['to'],
          },
          SuperAdminCommandBanner: {
            template: '<div class="command-banner-stub">Command Center</div>',
          },
          SuperAdminRoleDistribution: {
            template: '<div class="role-distribution-stub" />',
          },
          SuperAdminQuickActions: {
            template: '<div class="quick-actions-stub" />',
          },
          SuperAdminAuditTable: {
            template: '<div class="audit-table-stub" />',
          },
        },
      },
    });

    expect(superadminApi.dashboard).toHaveBeenCalledTimes(1);
    await flushPromises();

    // Verify Subsystem Strip is mounted with live data
    expect(wrapper.text()).toContain('Live Subsystem Telemetry & Circuit Breakers');
    expect(wrapper.text()).toContain('Paystack Gateway');
    expect(wrapper.text()).toContain('27 Blocked');

    // Verify KPI Cards are rendered
    expect(wrapper.text()).toContain('Total Users');
    expect(wrapper.text()).toContain('150');
    expect(wrapper.text()).toContain('Active Users');
    expect(wrapper.text()).toContain('142');
  });
});
