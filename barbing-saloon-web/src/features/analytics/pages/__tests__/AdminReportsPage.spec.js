import { describe, it, expect, vi } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import AdminReportsPage from '../AdminReportsPage.vue';

// Mock vue-router
vi.mock('vue-router', () => ({
  useRoute: vi.fn(() => ({ query: {} })),
  useRouter: vi.fn(() => ({ replace: vi.fn().mockResolvedValue() }))
}));

// Mock child components and layout
vi.mock('@/portals/admin/layouts/AdminLayout.vue', () => ({
  default: {
    template: '<div><slot /></div>'
  }
}));

vi.mock('@/shared/api/client', () => ({
  client: {
    get: vi.fn().mockResolvedValue({
      data: {
        success: true,
        data: {
          business_stats: { total_revenue: 500000 },
          platform_stats: { active_barbers: 3 },
          top_barbers: [],
          top_services: [],
          status_breakdown: { completed: 50 },
          daily_ledger: []
        }
      }
    }),
    post: vi.fn().mockResolvedValue({
      data: {
        success: true,
        data: {
          id: 1,
          report_id: 'REF-CCZ-TEST',
          action: 'report_pdf_download',
          verified: true
        }
      }
    })
  }
}));

describe('AdminReportsPage Component', () => {
  it('renders primary navigation tabs and switches between Dossier and History views', async () => {
    const wrapper = mount(AdminReportsPage, {
      global: {
        stubs: {
          ReportsSkeleton: true,
          ReportsDocumentToolbar: true,
          ReportsDossierDocument: true,
          ReportsHistoryTab: {
            template: '<div id="history-view">History View</div>'
          }
        }
      }
    });

    await flushPromises();

    // Both tabs rendered
    expect(wrapper.text()).toContain('Executive Dossier');
    expect(wrapper.text()).toContain('Export & Audit History');

    // Default tab is dossier
    expect(wrapper.findComponent({ name: 'ReportsDossierDocument' }).exists()).toBe(true);
    expect(wrapper.find('#history-view').exists()).toBe(false);

    // Click history tab
    const historyBtn = wrapper.find('#tab-btn-history');
    expect(historyBtn.exists()).toBe(true);
    await historyBtn.trigger('click');

    // History view is now active
    expect(wrapper.find('#history-view').exists()).toBe(true);
    expect(wrapper.findComponent({ name: 'ReportsDossierDocument' }).exists()).toBe(false);
  });
});
