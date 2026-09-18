import { ref, computed, onMounted, onUnmounted } from 'vue';
import client from '@/shared/api/client';

export function useAdminDashboard() {
  const dashboard = ref({});
  const loading = ref(true);
  const isRefreshing = ref(false);

  const loadDashboardData = async (silent = false) => {
    if (!silent) {
      loading.value = true;
    } else {
      isRefreshing.value = true;
    }
    
    try {
      const response = await client.get('/admin/dashboard');
      dashboard.value = response.data?.data || {};
    } catch (err) {
      console.error('Failed to load dashboard data:', err);
    } finally {
      loading.value = false;
      isRefreshing.value = false;
    }
  };

  let dashboardInterval = null;

  onMounted(() => {
    loadDashboardData();
    // Auto-reload dashboard every 30 seconds silently
    dashboardInterval = setInterval(() => {
      loadDashboardData(true);
    }, 30000);
  });

  onUnmounted(() => {
    if (dashboardInterval) clearInterval(dashboardInterval);
  });

  return {
    dashboard,
    loading,
    isRefreshing,
    loadDashboardData
  };
}
