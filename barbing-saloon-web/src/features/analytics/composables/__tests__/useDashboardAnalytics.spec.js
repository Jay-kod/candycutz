import { beforeEach, describe, expect, it, vi } from 'vitest';
import { client } from '@/shared/api/client';
import { useDashboardAnalytics } from '../useDashboardAnalytics';

vi.mock('@/shared/api/client', () => ({
  client: { get: vi.fn() },
}));

describe('useDashboardAnalytics', () => {
  beforeEach(() => {
    localStorage.clear();
    vi.clearAllMocks();
  });

  it('fetches current customer analytics from the API without caching it', async () => {
    localStorage.setItem('customer_analytics_7d', JSON.stringify({
      total_spent: 2500,
      monthly: [],
    }));
    const analytics = {
      spending_summary: { total_spent: 2500 },
      activity_timeline: { wishlist_items: 2, reviews_given: 1 },
      status_breakdown: { completed: 1 },
      booking_trends: [{ month: 'Nov 27', count: 1 }],
    };
    client.get.mockResolvedValue({ data: { data: analytics } });

    const dashboard = useDashboardAnalytics('customer');
    await dashboard.fetchAnalytics();

    expect(client.get).toHaveBeenCalledWith('/customer/analytics', { params: { range: '7d' } });
    expect(dashboard.analytics.value).toEqual(analytics);
    expect(localStorage.getItem('customer_analytics_v2_7d')).toBeNull();
  });

  it('does not show cached customer analytics when the API request fails', async () => {
    const cachedAnalytics = { spending_summary: { total_spent: 2500 } };
    localStorage.setItem('customer_analytics_v2_7d', JSON.stringify(cachedAnalytics));
    client.get.mockRejectedValue(new Error('API unavailable'));

    const dashboard = useDashboardAnalytics('customer');
    await dashboard.fetchAnalytics();

    expect(dashboard.analytics.value).toEqual({});
    expect(dashboard.error.value).toBe(true);
    expect(localStorage.getItem('customer_analytics_v2_7d')).toBe(JSON.stringify(cachedAnalytics));
  });

  it('includes in-progress appointment statuses in the customer breakdown', () => {
    const dashboard = useDashboardAnalytics('customer');
    dashboard.analytics.value = {
      status_breakdown: { checked_in: 1, in_progress: 2 },
    };

    expect(dashboard.totalDonutCount.value).toBe(3);
    expect(dashboard.legendItems.value.map(item => item.label)).toEqual(['checked_in', 'in_progress']);
  });
});
