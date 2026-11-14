import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ReportsHistoryTable from '../ReportsHistoryTable.vue';

describe('ReportsHistoryTable Component', () => {
  const mockRecords = [
    {
      id: 1,
      report_id: 'REF-CCZ-20261031-0001',
      action: 'report_pdf_download',
      action_label: 'PDF Document Download',
      range_label: '30 Days',
      title: 'Operations Dossier',
      user_name: 'Super Admin',
      user_role: 'admin',
      ip_address: '10.0.0.1',
      created_at: '2026-10-31T10:00:00Z',
      formatted_date: 'Oct 31, 2026 • 10:00 AM'
    }
  ];

  it('renders records in table and emits verify when button clicked', async () => {
    const wrapper = mount(ReportsHistoryTable, {
      props: {
        records: mockRecords,
        isFiltered: false
      }
    });

    expect(wrapper.text()).toContain('REF-CCZ-20261031-0001');
    expect(wrapper.text()).toContain('PDF Document Download');
    expect(wrapper.text()).toContain('Super Admin');
    expect(wrapper.text()).toContain('VERIFIED');

    const verifyBtn = wrapper.find('button[title*="Verify"]');
    expect(verifyBtn.exists()).toBe(true);
    await verifyBtn.trigger('click');

    expect(wrapper.emitted('verify')).toBeTruthy();
    expect(wrapper.emitted('verify')[0][0]).toEqual(mockRecords[0]);
  });

  it('renders empty state when records array is empty', () => {
    const wrapper = mount(ReportsHistoryTable, {
      props: {
        records: [],
        isFiltered: false
      }
    });

    expect(wrapper.text()).toContain('No Report Export Records Found');
    expect(wrapper.text()).toContain('Generate First Report');
  });
});

