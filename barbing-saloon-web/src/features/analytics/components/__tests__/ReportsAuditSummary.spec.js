import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ReportsAuditSummary from '../ReportsAuditSummary.vue';

describe('ReportsAuditSummary Component', () => {
  it('renders certification details and active range label', () => {
    const wrapper = mount(ReportsAuditSummary, {
      props: {
        activeRangeLabel: '30 Days'
      }
    });

    const text = wrapper.text();
    expect(text).toContain('Audit Certification & Ledger Integrity');
    expect(text).toContain('VERIFIED');
    expect(text).toContain('Keffi Flagship');
    expect(text).toContain('Period: 30 Days');
    expect(text).toContain('Export CSV');
    expect(text).toContain('Print Report');
    expect(text).toContain('Download PDF');
  });

  it('emits events on export actions', async () => {
    const wrapper = mount(ReportsAuditSummary, {
      props: {
        activeRangeLabel: '7 Days'
      }
    });

    const buttons = wrapper.findAll('button');
    const csvBtn = buttons.find(b => b.text().includes('Export CSV'));
    const printBtn = buttons.find(b => b.text().includes('Print Report'));
    const pdfBtn = buttons.find(b => b.text().includes('Download PDF'));

    await csvBtn.trigger('click');
    expect(wrapper.emitted('export-csv')).toBeTruthy();

    await printBtn.trigger('click');
    expect(wrapper.emitted('print')).toBeTruthy();

    await pdfBtn.trigger('click');
    expect(wrapper.emitted('generate')).toBeTruthy();
  });
});

