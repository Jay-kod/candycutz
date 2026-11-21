import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ReportsDailyLedger from '../ReportsDailyLedger.vue';

describe('ReportsDailyLedger Component', () => {
  it('renders daily ledger rows and summary totals correctly', () => {
    const dailyLedger = [
      { label: 'Mon, Oct 26', date: '2026-10-26', count: 12, revenue: 36000, avg_ticket: 3000 },
      { label: 'Tue, Oct 27', date: '2026-10-27', count: 18, revenue: 54000, avg_ticket: 3000 },
      { label: 'Wed, Oct 28', date: '2026-10-28', count: 20, revenue: 60000, avg_ticket: 3000 }
    ];

    const wrapper = mount(ReportsDailyLedger, {
      props: { dailyLedger }
    });

    const text = wrapper.text();
    expect(text).toContain('Mon, Oct 26');
    expect(text).toContain('Tue, Oct 27');
    expect(text).toContain('Wed, Oct 28');
    expect(text).toContain('36,000');
    expect(text).toContain('54,000');
    expect(text).toContain('60,000');
    // Summary totals: 12 + 18 + 20 = 50 appointments, 150,000 revenue
    expect(text).toContain('50');
    expect(text).toContain('150,000');
    expect(text).toContain('3 Entries');
  });

  it('renders empty state when no daily entries exist', () => {
    const wrapper = mount(ReportsDailyLedger, {
      props: { dailyLedger: [] }
    });

    expect(wrapper.text()).toContain('No ledger entries recorded');
  });
});

