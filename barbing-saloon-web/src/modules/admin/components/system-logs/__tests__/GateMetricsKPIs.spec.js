import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import GateMetricsKPIs from '../GateMetricsKPIs.vue';

describe('GateMetricsKPIs.vue', () => {
  it('renders all 4 telemetry KPI cards properly', () => {
    const metrics = {
      total_requests: 12500,
      avg_latency_ms: 42,
      slow_requests_count: 5,
      avg_query_count: 8,
      budget_warnings_count: 2,
      error_rate_percent: 0.8,
      error_count: 10
    };

    const wrapper = mount(GateMetricsKPIs, {
      props: { metrics }
    });

    const text = wrapper.text();
    expect(text).toContain('12,500');
    expect(text).toContain('42');
    expect(text).toContain('5 slow requests');
    expect(text).toContain('8');
    expect(text).toContain('2 budget breaches');
    expect(text).toContain('0.8%');
    expect(text).toContain('Calibrated Rate Limiting Active');
  });

  it('handles default empty metrics gracefully', () => {
    const wrapper = mount(GateMetricsKPIs, {
      props: { metrics: {} }
    });

    expect(wrapper.exists()).toBe(true);
    const text = wrapper.text();
    expect(text).toContain('24h Ingress Requests');
    expect(text).toContain('Avg Response Time');
    expect(text).toContain('Avg DB Queries / Req');
    expect(text).toContain('Error Rate (4xx / 5xx)');
  });
});
