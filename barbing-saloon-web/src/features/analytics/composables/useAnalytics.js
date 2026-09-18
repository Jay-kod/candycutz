import { ref, computed } from 'vue';
import { adminApi } from '@/shared/api/old_adminApi';

export function useAnalytics() {
  const loading = ref(true);
  const selectedRange = ref('7d');
  
  const businessStats = ref({});
  const platformStats = ref({});
  const topBarbers = ref([]);
  const topServices = ref([]);
  const statusBreakdown = ref([]);
  
  const rangeOptions = [
    { label: '7 Days', value: '7d' },
    { label: '30 Days', value: '30d' },
    { label: 'This Month', value: 'month' },
    { label: 'All Time', value: 'all' }
  ];

  const activeRangeLabel = computed(() => {
    const match = rangeOptions.find(o => o.value === selectedRange.value);
    return match ? match.label : selectedRange.value;
  });

  const fetchAnalytics = async () => {
    loading.value = true;
    try {
      const response = await adminApi.analytics(selectedRange.value);
      const data = response.data?.data || response.data || {};
      
      businessStats.value = data.business_stats || {};
      platformStats.value = data.platform_stats || {};
      topBarbers.value = data.top_barbers || [];
      topServices.value = data.top_services || [];
      
      // Convert status_breakdown from object to array if needed
      const sb = data.status_breakdown || {};
      if (Array.isArray(sb)) {
        statusBreakdown.value = sb;
      } else {
        statusBreakdown.value = Object.entries(sb)
          .filter(([, count]) => count > 0)
          .map(([status, count]) => ({ status, count }));
      }
    } catch (err) {
      console.error('Failed to fetch analytics:', err);
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
    selectedRange,
    rangeOptions,
    activeRangeLabel,
    businessStats,
    platformStats,
    topBarbers,
    topServices,
    statusBreakdown,
    fetchAnalytics,
    setRange
  };
}
