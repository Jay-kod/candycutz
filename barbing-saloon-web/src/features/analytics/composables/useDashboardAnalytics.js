import { ref, computed } from 'vue';
import { client } from '@/shared/api/client';

export function useDashboardAnalytics(role = 'admin') {
  const analytics = ref({});
  const loading = ref(true);
  const error = ref(false);
  const range = ref('7d');

  const ranges = [
    { label: '7 Days', value: '7d', shortLabel: '7D' },
    { label: '30 Days', value: '30d', shortLabel: '30D' },
    { label: 'This Month', value: 'month', shortLabel: 'Month' },
    { label: 'All Time', value: 'all', shortLabel: 'All' }
  ];

  const fetchAnalytics = async (silent = false) => {
    const cacheKey = `${role}_analytics_${range.value}`;
    const cachedData = localStorage.getItem(cacheKey);
    
    if (cachedData) {
      try {
        analytics.value = JSON.parse(cachedData);
        if (!silent) loading.value = false;
      } catch (e) {
        console.warn('Failed to parse cached analytics', e);
      }
    }

    if (!silent && !cachedData) loading.value = true;
    error.value = false;
    try {
      const res = await client.get(`/${role}/analytics`, { params: { range: range.value } });
      analytics.value = res.data.data;
      localStorage.setItem(cacheKey, JSON.stringify(res.data.data));
    } catch (err) {
      console.error(err);
      if (!silent && !cachedData) error.value = true;
    } finally {
      if (!silent) loading.value = false;
    }
  };

  const setRange = (val) => {
    range.value = val;
    fetchAnalytics();
  };
  
  // Status Breakdown Donut Chart Logic
  const legendItems = computed(() => {
    const breakdown = analytics.value.status_breakdown || {};
    return [
      { label: 'completed', count: breakdown.completed || 0, bgClass: 'bg-emerald-400', hex: '#34d399' },
      { label: 'pending', count: breakdown.pending || 0, bgClass: 'bg-amber-400', hex: '#fbbf24' },
      { label: 'confirmed', count: breakdown.confirmed || 0, bgClass: 'bg-blue-400', hex: '#60a5fa' },
      { label: 'cancelled', count: breakdown.cancelled || 0, bgClass: 'bg-red-400', hex: '#f87171' },
      { label: 'no_show', count: breakdown.no_show || 0, bgClass: 'bg-purple-400', hex: '#c084fc' }
    ].filter(item => item.count > 0);
  });

  const totalDonutCount = computed(() => {
    return legendItems.value.reduce((sum, item) => sum + item.count, 0);
  });
  
  const completionRate = computed(() => {
    if (totalDonutCount.value === 0) return 0;
    const completed = (analytics.value.status_breakdown?.completed || 0);
    return Math.round((completed / totalDonutCount.value) * 100);
  });

  const donutSegments = computed(() => {
    const total = totalDonutCount.value;
    if (total === 0) return [];
    
    const circumference = 2 * Math.PI * 40; // r=40
    let currentOffset = 0;
    const gap = role === 'customer' ? 2 : 0; // Customer has gap
    
    return legendItems.value.map(item => {
      const percentage = item.count / total;
      const length = Math.max(percentage * circumference - gap, 1);
      const fullGap = circumference - length;
      
      const offset = -currentOffset;
      currentOffset += length + gap;
      
      return {
        color: item.hex,
        length,
        gap: fullGap,
        offset
      };
    });
  });

  return {
    analytics,
    loading,
    error,
    range,
    ranges,
    fetchAnalytics,
    setRange,
    legendItems,
    totalDonutCount,
    completionRate,
    donutSegments
  };
}
