import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ReportsHistoryStats from '../ReportsHistoryStats.vue';

describe('ReportsHistoryStats Component', () => {
  it('renders stats counts and latest event details', () => {
    const wrapper = mount(ReportsHistoryStats, {
      props: {
        totalCount: 15,
        pdfCount: 8,
        csvCount: 4,
        latestEventTime: '10:30 AM',
        latestEventUser: 'by Admin User'
      }
    });

    expect(wrapper.text()).toContain('Total Exports Logged');
    expect(wrapper.text()).toContain('15');
    expect(wrapper.text()).toContain('8');
    expect(wrapper.text()).toContain('4');
    expect(wrapper.text()).toContain('10:30 AM');
    expect(wrapper.text()).toContain('by Admin User');
  });
});

