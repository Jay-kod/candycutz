import { ref, onMounted, onUnmounted } from 'vue';
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
      const data = response.data?.data || {};
      
      // Fallback to /health endpoint if system_health not directly in dashboard payload
      if (!data.system_health) {
        try {
          const healthRes = await client.get('/health');
          const hData = healthRes.data?.data || {};
          data.system_health = {
            status: hData.status === 'healthy' ? 'operational' : 'degraded',
            database: {
              status: hData.services?.database === 'connected' ? 'connected' : 'disconnected',
              connection: 'mysql',
              latency_ms: 1
            },
            timestamp: hData.timestamp
          };
        } catch (_) {}
      }
      
      dashboard.value = data;
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
