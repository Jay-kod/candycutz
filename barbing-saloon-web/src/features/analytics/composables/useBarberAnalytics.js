import { ref, computed } from 'vue';
import { barberApi } from '@/shared/api/old_barberApi';

export function useBarberAnalytics() {
  const loading = ref(true);
  const selectedRange = ref('7d');
  
  const performanceMetrics = ref({});
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
      const response = await barberApi.analytics(selectedRange.value);
      const data = response.data?.data || response.data || {};
      
      performanceMetrics.value = data.performance_metrics || {};
      topServices.value = data.top_services || [];
      
      const sb = data.status_breakdown || {};
      if (Array.isArray(sb)) {
        statusBreakdown.value = sb;
      } else {
        statusBreakdown.value = Object.entries(sb)
          .filter(([, count]) => count > 0)
          .map(([status, count]) => ({ status, count }));
      }
    } catch (err) {
      console.error('Failed to fetch barber analytics:', err);
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
    performanceMetrics,
    topServices,
    statusBreakdown,
    fetchAnalytics,
    setRange
  };
}
