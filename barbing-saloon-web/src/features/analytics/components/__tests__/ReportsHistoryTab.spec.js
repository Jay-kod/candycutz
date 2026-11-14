import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ReportsHistoryTab from '../ReportsHistoryTab.vue';

describe('ReportsHistoryTab Component', () => {
  const mockHistory = [
    {
      id: 1,
      report_id: 'REF-CCZ-20261031-ABCD',
      action: 'report_pdf_download',
      action_label: 'PDF Document Download',
      range: '30d',
      range_label: '30 Days',
      title: 'Executive Operations Dossier',
      user_name: 'Suleiman Admin',
      user_role: 'admin',
      ip_address: '192.168.1.10',
      created_at: '2026-10-31T10:00:00Z',
      formatted_date: 'Oct 31, 2026 • 10:00 AM',
      checksum: 'SHA256:abcdef123456',
      verified: true
    },
    {
      id: 2,
      report_id: 'REF-CCZ-20261031-CSV1',
      action: 'report_csv_export',
      action_label: 'CSV Spreadsheet Export',
      range: '7d',
      range_label: '7 Days',
      title: 'Executive Operational Report (CSV)',
      user_name: 'Manager User',
      user_role: 'admin',
      ip_address: '192.168.1.20',
      created_at: '2026-10-31T09:30:00Z',
      formatted_date: 'Oct 31, 2026 • 09:30 AM',
      checksum: 'SHA256:7890abcdef12',
      verified: true
    }
  ];

  it('renders history summary cards and export records correctly', () => {
    const wrapper = mount(ReportsHistoryTab, {
      props: {
        historyList: mockHistory,
        loading: false
      }
    });

    expect(wrapper.text()).toContain('Report Audit & Export');
    expect(wrapper.text()).toContain('Total Exports Logged');
    expect(wrapper.text()).toContain('REF-CCZ-20261031-ABCD');
    expect(wrapper.text()).toContain('REF-CCZ-20261031-CSV1');
    expect(wrapper.text()).toContain('Suleiman Admin');
    expect(wrapper.text()).toContain('PDF Document Download');
    expect(wrapper.text()).toContain('CSV Spreadsheet Export');
    expect(wrapper.text()).toContain('VERIFIED');
  });

  it('filters history by search query', async () => {
    const wrapper = mount(ReportsHistoryTab, {
      props: {
        historyList: mockHistory,
        loading: false
      }
    });

    const searchInput = wrapper.find('input[type="text"]');
    await searchInput.setValue('CSV1');

    expect(wrapper.text()).toContain('REF-CCZ-20261031-CSV1');
    expect(wrapper.text()).not.toContain('REF-CCZ-20261031-ABCD');
  });

  it('emits switch-to-dossier and refresh events', async () => {
    const wrapper = mount(ReportsHistoryTab, {
      props: {
        historyList: mockHistory,
        loading: false
      }
    });

    const dossierBtn = wrapper.findAll('button').find(b => b.text().includes('View Current Dossier'));
    expect(dossierBtn).toBeDefined();
    await dossierBtn.trigger('click');
    expect(wrapper.emitted('switch-to-dossier')).toBeTruthy();

    const refreshBtn = wrapper.findAll('button').find(b => b.text().includes('Refresh'));
    expect(refreshBtn).toBeDefined();
    await refreshBtn.trigger('click');
    expect(wrapper.emitted('refresh')).toBeTruthy();
  });

  it('opens verification modal when clicking verify button', async () => {
    const wrapper = mount(ReportsHistoryTab, {
      props: {
        historyList: mockHistory,
        loading: false
      },
      global: {
        stubs: {
          teleport: true
        }
      }
    });

    const verifyBtn = wrapper.findAll('button').find(b => b.text().includes('Verify'));
    expect(verifyBtn).toBeDefined();
    await verifyBtn.trigger('click');

    expect(wrapper.findComponent({ name: 'ReportVerificationModal' }).props('show')).toBe(true);
  });
});

