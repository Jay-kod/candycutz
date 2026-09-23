<template>
  <AdminLayout>
    <section class="space-y-8 animate-fade-in">
      <DashboardWelcomeBanner 
        :current-time="currentTime" 
        :current-date="currentDate" 
        :is-refreshing="isRefreshing" 
        @refresh="loadDashboardData" 
      />

      <!-- Loading State -->
      <div v-if="loading" class="grid gap-5 grid-cols-2 lg:grid-cols-4">
        <div v-for="i in 8" :key="i" class="h-36 rounded-xl bg-white/[0.03] animate-pulse border border-white/[0.04]"></div>
      </div>

      <template v-else>
        <DashboardKpiGrid 
          :kpis="statsCards" 
          :revenue-cards="revenueCards" 
        />

        <div class="grid gap-6 lg:grid-cols-3">
          <!-- Left Column -->
          <div class="lg:col-span-2 space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
              <DashboardCharts 
                class="md:col-span-1"
                :revenue-trend="dashboard.revenue_trend" 
                :booking-trend="dashboard.booking_trend" 
              />
              <DashboardStatusDonut 
                class="md:col-span-1"
                :status-breakdown="dashboard.status_breakdown" 
                :total-appointments="dashboard.stats?.today_appointments || 0" 
              />
            </div>
            <DashboardRecentAppointments :appointments="dashboard.recent_appointments" />
            <DashboardTopEntities 
              :top-barbers="dashboard.top_barbers" 
              :top-services="dashboard.top_services" 
            />
          </div>

          <!-- Right Column -->
          <div class="space-y-6">
            <DashboardActivitySidebar :recent-activity="dashboard.recent_activity" />
          </div>
        </div>
      </template>
    </section>
  </AdminLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { 
  UsersIcon, CalendarDaysIcon, CheckBadgeIcon, ChartBarIcon
} from '@heroicons/vue/24/outline';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import { useAdminDashboard } from '@/features/dashboard/composables/useAdminDashboard';
import DashboardWelcomeBanner from '@/features/dashboard/components/DashboardWelcomeBanner.vue';
import DashboardKpiGrid from '@/features/dashboard/components/DashboardKpiGrid.vue';
import DashboardCharts from '@/features/dashboard/components/DashboardCharts.vue';
import DashboardStatusDonut from '@/features/dashboard/components/DashboardStatusDonut.vue';
import DashboardRecentAppointments from '@/features/dashboard/components/DashboardRecentAppointments.vue';
import DashboardTopEntities from '@/features/dashboard/components/DashboardTopEntities.vue';
import DashboardActivitySidebar from '@/features/dashboard/components/DashboardActivitySidebar.vue';

const { dashboard, loading, isRefreshing, loadDashboardData } = useAdminDashboard();

// Time formatting logic
const currentTime = ref('');
const currentDate = ref('');
let timeInterval = null;

const updateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
};

onMounted(() => {
  updateTime();
  timeInterval = setInterval(updateTime, 1000);
});
onUnmounted(() => {
  if (timeInterval) clearInterval(timeInterval);
});

// Format numbers
const formatNumber = (value) => new Intl.NumberFormat('en-NG').format(Number(value || 0));

const statsCards = computed(() => {
  const stats = dashboard.value.stats || {};
  return [
    {
      label: 'Today\'s Bookings',
      value: formatNumber(stats.today_appointments),
      icon: CalendarDaysIcon,
      iconColor: 'text-blue-400',
      iconWrap: 'bg-blue-500/10 border-blue-500/20',
      badge: '+12%',
      badgeIcon: ChartBarIcon,
      badgeClass: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
      sub: 'vs yesterday'
    },
    {
      label: 'Total Customers',
      value: formatNumber(stats.total_customers),
      icon: UsersIcon,
      iconColor: 'text-purple-400',
      iconWrap: 'bg-purple-500/10 border-purple-500/20',
      badge: 'Active',
      badgeIcon: CheckBadgeIcon,
      badgeClass: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
      sub: 'All time'
    }
  ];
});

const revenueCards = computed(() => {
  const stats = dashboard.value.stats || {};
  return [
    { label: 'Today\'s Revenue', value: stats.today_revenue || 0 },
    { label: 'This Week', value: stats.week_revenue || 0 },
    { label: 'This Month', value: stats.month_revenue || 0 }
  ];
});
</script>