import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import SuperAdminSubsystemStrip from '../SuperAdminSubsystemStrip.vue';

describe('SuperAdminSubsystemStrip.vue', () => {
  const defaultSubsystems = {
    database: {
      status: 'operational',
      latency_ms: 1.4,
    },
    paystack: {
      service: 'paystack',
      state: 'closed',
      failures_count: 0,
      threshold: 5,
      is_available: true,
    },
    resend: {
      service: 'resend',
      state: 'closed',
      failures_count: 0,
      threshold: 5,
      is_available: true,
    },
    sweeper: {
      status: 'optimal',
      last_run: '2026-11-22T05:30:00Z',
      cancelled_count: 2,
      duration_ms: 12.5,
    },
    security: {
      probes_blocked: 15,
      status: 'operational',
    },
    incidents: {
      unresolved_count: 0,
      failed_jobs_count: 0,
      status: 'optimal',
    },
  };

  it('renders all 6 subsystem monitors with operational states', () => {
    const wrapper = mount(SuperAdminSubsystemStrip, {
      props: {
        subsystems: defaultSubsystems,
      },
      global: {
        stubs: {
          RouterLink: {
            template: '<a :href="to"><slot /></a>',
            props: ['to'],
          },
        },
      },
    });

    expect(wrapper.text()).toContain('Platform Health Signals');
    expect(wrapper.text()).toContain('6 system signals');

    // 1. Paystack
    expect(wrapper.text()).toContain('Paystack Gateway');
    expect(wrapper.text()).toContain('Operational');
    expect(wrapper.text()).toContain('0 consecutive errors');

    // 2. Email Dispatcher
    expect(wrapper.text()).toContain('Email Dispatcher');
    expect(wrapper.text()).toContain('Resend / Transactional');

    // 3. Draft Sweeper
    expect(wrapper.text()).toContain('Draft Sweeper');
    expect(wrapper.text()).toContain('2 Holds Expired');

    // 4. Database
    expect(wrapper.text()).toContain('Database Ping');
    expect(wrapper.text()).toContain('1.4 ms');
    expect(wrapper.text()).toContain('Database response time');

    // 5. Security Shield
    expect(wrapper.text()).toContain('Security Shield');
    expect(wrapper.text()).toContain('15 Blocked');
    expect(wrapper.text()).toContain('Blocked probe attempts');

    // 6. Incident Vault
    expect(wrapper.text()).toContain('Incident Vault');
    expect(wrapper.text()).toContain('0 Unresolved');
  });

  it('displays tripped circuit breaker warning when failures threshold reached', () => {
    const trippedSubsystems = {
      ...defaultSubsystems,
      paystack: {
        service: 'paystack',
        state: 'open',
        failures_count: 5,
        threshold: 5,
        is_available: false,
      },
      incidents: {
        unresolved_count: 3,
        failed_jobs_count: 1,
        status: 'warning',
      },
    };

    const wrapper = mount(SuperAdminSubsystemStrip, {
      props: {
        subsystems: trippedSubsystems,
      },
      global: {
        stubs: {
          RouterLink: {
            template: '<a :href="to"><slot /></a>',
            props: ['to'],
          },
        },
      },
    });

    expect(wrapper.text()).toContain('Circuit breaker open');
    expect(wrapper.text()).toContain('5 consecutive errors');
    expect(wrapper.text()).toContain('3 Unresolved');
    expect(wrapper.text()).toContain('1 failed queue job');
  });

  it('does not present missing subsystem data as operational', () => {
    const wrapper = mount(SuperAdminSubsystemStrip, {
      props: {
        subsystems: {},
      },
      global: {
        stubs: {
          RouterLink: {
            template: '<a :href="to"><slot /></a>',
            props: ['to'],
          },
        },
      },
    });

    expect(wrapper.text()).toContain('Status unavailable');
    expect(wrapper.text()).toContain('Unknown');
    expect(wrapper.text()).not.toContain('Operational');
  });
});
