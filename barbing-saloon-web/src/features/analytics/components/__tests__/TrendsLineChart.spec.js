import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import TrendsLineChart from '../TrendsLineChart.vue';

describe('TrendsLineChart.vue', () => {
  it('limits axis labels for long ranges while retaining every data point', () => {
    const trends = Array.from({ length: 30 }, (_, index) => ({
      month: `Day ${index + 1}`,
      count: index,
    }));
    const wrapper = mount(TrendsLineChart, {
      props: {
        trends,
        valueKey: 'count',
        labelKey: 'month',
      },
    });

    const labels = wrapper.findAll('[data-testid="trend-axis-label"]');

    expect(labels).toHaveLength(5);
    expect(labels[0].text()).toBe('Day 1');
    expect(labels[labels.length - 1].text()).toBe('Day 30');
    expect(wrapper.findAll('[class~="group/point"]')).toHaveLength(30);
  });
});
