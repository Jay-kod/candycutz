import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ReportsDetailedBreakdowns from '../ReportsDetailedBreakdowns.vue';

describe('ReportsDetailedBreakdowns Component', () => {
  const topBarbers = [
    { id: 1, name: 'David Master', rating: 4.9, bookings: 25, revenue: 85000 },
    { id: 2, name: 'Samuel Blade', rating: 4.8, bookings: 15, revenue: 50000 }
  ];

  const topServices = [
    { id: 1, name: 'Executive Fade & Beard Sculpt', category: 'Haircut', count: 30, revenue: 90000 },
    { id: 2, name: 'Classic Beard Trim', category: 'Beard Care', count: 20, revenue: 40000 }
  ];

  it('renders staff productivity table by default', () => {
    const wrapper = mount(ReportsDetailedBreakdowns, {
      props: {
        topBarbers,
        topServices,
        totalRevenue: 135000
      }
    });

    const text = wrapper.text();
    expect(text).toContain('David Master');
    expect(text).toContain('Samuel Blade');
    expect(text).toContain('85,000');
    expect(text).toContain('50,000');
    expect(text).toContain('★ 4.9');
  });

  it('switches to service yield table when tab is clicked', async () => {
    const wrapper = mount(ReportsDetailedBreakdowns, {
      props: {
        topBarbers,
        topServices,
        totalRevenue: 130000
      }
    });

    const buttons = wrapper.findAll('button');
    const serviceTab = buttons.find(b => b.text().includes('Service Yield'));
    expect(serviceTab).toBeDefined();

    await serviceTab.trigger('click');

    const text = wrapper.text();
    expect(text).toContain('Executive Fade & Beard Sculpt');
    expect(text).toContain('Classic Beard Trim');
    expect(text).toContain('90,000');
    expect(text).toContain('40,000');
    expect(text).toContain('Haircut');
    expect(text).toContain('Beard Care');
  });
});

