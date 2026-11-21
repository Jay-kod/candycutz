import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ReportsDossierDocument from '../ReportsDossierDocument.vue';

describe('ReportsDossierDocument Component', () => {
  const mockProps = {
    activeRangeLabel: '30 Days',
    businessStats: {
      total_revenue: 1250000,
      realized_revenue: 1100000,
      total_appointments: 150,
      completed_count: 140,
      average_ticket: 7850,
      new_customers: 24,
      total_customers: 310
    },
    platformStats: {
      active_barbers: 4,
      total_services: 12,
      avg_rating: 4.9
    },
    dailyLedger: [
      { label: '2026-10-28', date: '2026-10-28', count: 10, revenue: 85000, avg_ticket: 8500 },
      { label: '2026-10-29', date: '2026-10-29', count: 12, revenue: 105000, avg_ticket: 8750 }
    ],
    topBarbers: [
      { id: 1, name: 'Suleiman Ahmed', bookings: 75, completed_appointments: 75, rating: 4.9, revenue: 620000 }
    ],
    topServices: [
      { id: 1, name: 'Executive Fade & Beard Sculpt', category: 'Haircut', count: 60, bookings: 60, revenue: 450000 }
    ],
    statusBreakdown: [
      { status: 'completed', count: 140 },
      { status: 'confirmed', count: 10 }
    ]
  };

  it('renders executive letterhead, report metadata, and period label', () => {
    const wrapper = mount(ReportsDossierDocument, {
      props: mockProps
    });

    expect(wrapper.text()).toContain('CANDYCUTZ');
    expect(wrapper.text()).toContain('Luxury Grooming Flagship');
    expect(wrapper.text()).toContain('Plot 14, Commercial District, Keffi, Nasarawa State');
    expect(wrapper.text()).toContain('30 Days');
    expect(wrapper.text()).toContain('AUDITED & RECONCILED');
    expect(wrapper.text()).toContain('REF-CCZ-');
  });

  it('renders all key performance metrics, ledger rows, and dual audit tables', () => {
    const wrapper = mount(ReportsDossierDocument, {
      props: mockProps
    });

    // Check KPIs
    expect(wrapper.text()).toContain('Realized Revenue');
    expect(wrapper.text()).toContain('Fulfilled Appts');
    expect(wrapper.text()).toContain('Average Ticket');

    // Check Ledger Table
    expect(wrapper.text()).toContain('2026-10-28');
    expect(wrapper.text()).toContain('2026-10-29');

    // Check Top Barbers and Services
    expect(wrapper.text()).toContain('Suleiman Ahmed');
    expect(wrapper.text()).toContain('Executive Fade & Beard Sculpt');

    // Check Lifecycle
    expect(wrapper.text()).toContain('Booking Lifecycle & Retention Audit');

    // Check Certification Seal
    expect(wrapper.text()).toContain('Official Certification');
    expect(wrapper.text()).toContain('RECONCILED & VALIDATED');
  });
});

