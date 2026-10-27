import { ref, computed } from 'vue';
import { client } from '@/shared/api/client';

export function useAnalytics() {
  const loading = ref(true);
  const error = ref(null);
  const selectedRange = ref('7d');
  const lastRefreshed = ref(null);
  
  const businessStats = ref({});
  const platformStats = ref({});
  const topBarbers = ref([]);
  const topServices = ref([]);
  const statusBreakdown = ref([]);
  const revenueTrends = ref([]);
  const bookingTrends = ref([]);
  
  const rangeOptions = [
    { label: '7 Days', value: '7d', shortLabel: '7D' },
    { label: '30 Days', value: '30d', shortLabel: '30D' },
    { label: 'This Month', value: 'month', shortLabel: 'Month' },
    { label: 'All Time', value: 'all', shortLabel: 'All' }
  ];

  const activeRangeLabel = computed(() => {
    const match = rangeOptions.find(o => o.value === selectedRange.value);
    return match ? match.label : selectedRange.value;
  });

  const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-NG', {
      maximumFractionDigits: 0
    }).format(Number(val || 0));
  };

  const updateTimestamp = () => {
    const now = new Date();
    lastRefreshed.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };

  const fetchAnalytics = async () => {
    loading.value = true;
    error.value = null;

    try {
      const response = await client.get('/admin/reports', {
        params: { range: selectedRange.value }
      });
      const data = response.data?.data || response.data || {};
      
      businessStats.value = data.business_stats || {};
      platformStats.value = data.platform_stats || {};
      topBarbers.value = data.top_barbers || [];
      topServices.value = data.top_services || [];
      revenueTrends.value = data.revenue_trends || [];
      bookingTrends.value = data.booking_trends || [];
      
      // Convert status_breakdown from object to normalized array
      const sb = data.status_breakdown || {};
      if (Array.isArray(sb)) {
        statusBreakdown.value = sb;
      } else {
        statusBreakdown.value = Object.entries(sb)
          .map(([status, count]) => ({ status, count: Number(count || 0) }))
          .sort((a, b) => b.count - a.count);
      }

      updateTimestamp();
    } catch (err) {
      console.error('Failed to fetch reports analytics:', err);
      error.value = err?.response?.data?.message || 'Failed to load report data.';
    } finally {
      loading.value = false;
    }
  };

  const setRange = (range) => {
    if (selectedRange.value === range) return;
    selectedRange.value = range;
    fetchAnalytics();
  };

  return {
    loading,
    error,
    selectedRange,
    rangeOptions,
    activeRangeLabel,
    lastRefreshed,
    businessStats,
    platformStats,
    topBarbers,
    topServices,
    statusBreakdown,
    revenueTrends,
    bookingTrends,
    fetchAnalytics,
    setRange,
    formatCurrency
  };
}
