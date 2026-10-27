import { ref, computed, onMounted, onUnmounted } from 'vue';
import { client } from '@/shared/api/client';

export function useAdminAnalyticsDashboard() {
  const analytics = ref({});
  const loading = ref(true);
  const refreshing = ref(false);
  const error = ref(null);
  const range = ref('7d');
  const lastRefreshed = ref(null);

  const ranges = [
    { label: '7 Days', value: '7d', shortLabel: '7D' },
    { label: '30 Days', value: '30d', shortLabel: '30D' },
    { label: 'This Month', value: 'month', shortLabel: 'Month' },
    { label: 'All Time', value: 'all', shortLabel: 'All' }
  ];

  const formatCurrency = (val) => {
    const num = Number(val || 0);
    return new Intl.NumberFormat('en-NG', {
      maximumFractionDigits: 0
    }).format(num);
  };

  const updateTimestamp = () => {
    const now = new Date();
    lastRefreshed.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  };

  const fetchAnalytics = async (silent = false) => {
    const cacheKey = `admin_analytics_${range.value}`;
    const cachedData = localStorage.getItem(cacheKey);

    if (cachedData && !silent) {
      try {
        analytics.value = JSON.parse(cachedData);
        loading.value = false;
      } catch (e) {
        console.warn('Failed to parse cached admin analytics', e);
      }
    }

    if (!silent && !analytics.value?.business_stats) {
      loading.value = true;
    } else if (silent) {
      refreshing.value = true;
    }
    error.value = null;

    try {
      const res = await client.get('/admin/analytics', { params: { range: range.value } });
      const data = res.data?.data || res.data || {};
      analytics.value = data;
      localStorage.setItem(cacheKey, JSON.stringify(data));
      updateTimestamp();
    } catch (err) {
      console.error('Failed to load admin analytics:', err);
      if (!analytics.value?.business_stats) {
        error.value = err?.response?.data?.message || 'Failed to load analytics data from server.';
      }
    } finally {
      loading.value = false;
      refreshing.value = false;
    }
  };

  const setRange = (val) => {
    if (range.value === val) return;
    range.value = val;
    fetchAnalytics(false);
  };

  const refreshNow = () => {
    fetchAnalytics(true);
  };

  // Status Breakdown Donut
  const legendItems = computed(() => {
    const breakdown = analytics.value?.status_breakdown || {};
    return [
      { label: 'completed', count: breakdown.completed || 0, bgClass: 'bg-emerald-400', hex: '#34D399' },
      { label: 'confirmed', count: breakdown.confirmed || 0, bgClass: 'bg-sky-400', hex: '#38BDF8' },
      { label: 'pending', count: breakdown.pending || 0, bgClass: 'bg-amber-400', hex: '#FBBF24' },
      { label: 'cancelled', count: breakdown.cancelled || 0, bgClass: 'bg-rose-400', hex: '#FB7185' },
      { label: 'no_show', count: breakdown.no_show || 0, bgClass: 'bg-purple-400', hex: '#C084FC' }
    ].filter(item => item.count > 0);
  });

  const totalDonutCount = computed(() => {
    return legendItems.value.reduce((sum, item) => sum + item.count, 0);
  });

  const completionRate = computed(() => {
    if (totalDonutCount.value === 0) return 0;
    const completed = analytics.value?.status_breakdown?.completed || 0;
    return Math.round((completed / totalDonutCount.value) * 100);
  });

  const donutSegments = computed(() => {
    const total = totalDonutCount.value;
    if (total === 0) return [];

    const circumference = 2 * Math.PI * 40; // r=40
    let currentOffset = 0;

    return legendItems.value.map(item => {
      const percentage = item.count / total;
      const length = Math.max(percentage * circumference - 2, 1);
      const fullGap = circumference - length;
      const offset = -currentOffset;
      currentOffset += length + 2;

      return {
        color: item.hex,
        length,
        gap: fullGap,
        offset,
        percentage: Math.round(percentage * 100)
      };
    });
  });

  // Top stats helpers
  const businessStats = computed(() => analytics.value?.business_stats || {});
  const platformStats = computed(() => analytics.value?.platform_stats || {});
  const topServices = computed(() => analytics.value?.top_services || []);
  const topBarbers = computed(() => analytics.value?.top_barbers || []);
  const revenueTrends = computed(() => analytics.value?.revenue_trends || []);
  const bookingTrends = computed(() => analytics.value?.booking_trends || []);

  let refreshTimer = null;

  onMounted(() => {
    fetchAnalytics(false);
    refreshTimer = setInterval(() => {
      fetchAnalytics(true);
    }, 30000);
  });

  onUnmounted(() => {
    if (refreshTimer) {
      clearInterval(refreshTimer);
      refreshTimer = null;
    }
  });

  return {
    analytics,
    loading,
    refreshing,
    error,
    range,
    ranges,
    lastRefreshed,
    fetchAnalytics,
    setRange,
    refreshNow,
    formatCurrency,
    legendItems,
    totalDonutCount,
    completionRate,
    donutSegments,
    businessStats,
    platformStats,
    topServices,
    topBarbers,
    revenueTrends,
    bookingTrends
  };
}
