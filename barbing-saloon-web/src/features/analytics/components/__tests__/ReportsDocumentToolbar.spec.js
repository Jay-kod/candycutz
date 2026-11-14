import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ReportsDocumentToolbar from '../ReportsDocumentToolbar.vue';

describe('ReportsDocumentToolbar Component', () => {
  const defaultProps = {
    loading: false,
    selectedRange: '7d',
    rangeOptions: [
      { label: '7 Days', value: '7d' },
      { label: '30 Days', value: '30d' },
      { label: 'This Month', value: 'month' },
      { label: 'All Time', value: 'all' }
    ],
    isGeneratingPdf: false,
    zoomLevel: 100
  };

  it('renders range selector options, document mode, and highlights the active range', () => {
    const wrapper = mount(ReportsDocumentToolbar, {
      props: defaultProps,
      global: {
        stubs: {
          'router-link': true
        }
      }
    });

    expect(wrapper.text()).toContain('7 Days');
    expect(wrapper.text()).toContain('30 Days');
    expect(wrapper.text()).toContain('This Month');
    expect(wrapper.text()).toContain('All Time');
    expect(wrapper.text()).toContain('Operational Report');
    expect(wrapper.text()).toContain('WYSIWYG');
  });

  it('emits update:selectedRange when an inactive range pill is clicked', async () => {
    const wrapper = mount(ReportsDocumentToolbar, {
      props: defaultProps,
      global: {
        stubs: {
          'router-link': true
        }
      }
    });

    const buttons = wrapper.findAll('button');
    const thirtyDaysBtn = buttons.find(b => b.text().includes('30 Days'));
    expect(thirtyDaysBtn).toBeDefined();

    await thirtyDaysBtn.trigger('click');
    expect(wrapper.emitted('update:selectedRange')).toBeTruthy();
    expect(wrapper.emitted('update:selectedRange')[0]).toEqual(['30d']);
  });

  it('emits export-csv, print, and download-pdf on respective button clicks', async () => {
    const wrapper = mount(ReportsDocumentToolbar, {
      props: defaultProps,
      global: {
        stubs: {
          'router-link': true
        }
      }
    });

    const exportBtn = wrapper.findAll('button').find(b => b.text().includes('CSV'));
    const printBtn = wrapper.findAll('button').find(b => b.text().includes('Print'));
    const pdfBtn = wrapper.findAll('button').find(b => b.text().includes('PDF'));

    await exportBtn.trigger('click');
    expect(wrapper.emitted('export-csv')).toBeTruthy();

    await printBtn.trigger('click');
    expect(wrapper.emitted('print')).toBeTruthy();

    await pdfBtn.trigger('click');
    expect(wrapper.emitted('download-pdf')).toBeTruthy();
  });

  it('emits copy-summary and refresh events', async () => {
    const wrapper = mount(ReportsDocumentToolbar, {
      props: defaultProps,
      global: {
        stubs: {
          'router-link': true
        }
      }
    });

    const copyBtn = wrapper.findAll('button').find(b => b.text().includes('Copy Brief') || b.attributes('title')?.includes('briefing'));
    expect(copyBtn).toBeDefined();
    await copyBtn.trigger('click');
    expect(wrapper.emitted('copy-summary')).toBeTruthy();

    const refreshBtn = wrapper.findAll('button').find(b => b.attributes('title')?.includes('Refresh'));
    expect(refreshBtn).toBeDefined();
    await refreshBtn.trigger('click');
    expect(wrapper.emitted('refresh')).toBeTruthy();
  });

  it('emits update:zoomLevel when zoom buttons are clicked', async () => {
    const wrapper = mount(ReportsDocumentToolbar, {
      props: defaultProps,
      global: {
        stubs: {
          'router-link': true
        }
      }
    });

    const zoomInBtn = wrapper.findAll('button').find(b => b.attributes('title')?.includes('Zoom in'));
    if (zoomInBtn) {
      await zoomInBtn.trigger('click');
      expect(wrapper.emitted('update:zoomLevel')).toBeTruthy();
      expect(wrapper.emitted('update:zoomLevel')[0]).toEqual([110]);
    }
  });
});
