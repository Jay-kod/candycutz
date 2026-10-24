<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in">
      <DashboardWelcomeBanner 
        :current-time="currentTime" 
        :current-date="currentDate" 
        :is-refreshing="isRefreshing" 
        @refresh="loadDashboardData" 
      />

      <!-- Loading Skeleton State -->
      <div v-if="loading" class="grid gap-4 sm:gap-5 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        <div v-for="i in 5" :key="i" class="h-36 rounded-2xl bg-white/[0.03] animate-pulse border border-white/[0.05]"></div>
      </div>

      <template v-else>
        <!-- Unified 5-Card Operational Metric Grid -->
        <DashboardKpiGrid 
          :kpis="statsCards" 
        />

        <!-- Main Workspace Grid: 2/3 Content & 1/3 Action/Activity Sidebar -->
        <div class="grid gap-6 lg:grid-cols-3 items-start">
          <!-- Left Column (col-span-2) -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Row 1: Performance Trends & Status Donut -->
            <div class="grid gap-6 md:grid-cols-2 items-stretch">
              <DashboardCharts 
                :revenue-trend="dashboard.revenue_trend" 
                :booking-trend="dashboard.booking_trend" 
              />
              <DashboardStatusDonut 
                :status-breakdown="dashboard.status_breakdown" 
                :total-appointments="dashboard.stats?.total_appointments || 0" 
              />
            </div>

            <!-- Row 2: Recent Appointments -->
            <DashboardRecentAppointments :appointments="dashboard.recent_appointments" />

            <!-- Row 3: Top Barbers & Popular Services side-by-side -->
            <DashboardTopEntities 
              :top-barbers="dashboard.top_barbers" 
              :top-services="dashboard.top_services" 
            />
          </div>

          <!-- Right Column (col-span-1) -->
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
  UsersIcon, CalendarDaysIcon, CheckBadgeIcon, 
  BanknotesIcon, ArrowTrendingUpIcon, ChartBarIcon, ClockIcon
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
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
};

onMounted(() => {
  updateTime();
  timeInterval = setInterval(updateTime, 1000);
});
onUnmounted(() => {
  if (timeInterval) clearInterval(timeInterval);
});

// Format helpers
const formatNumber = (value) => new Intl.NumberFormat('en-NG').format(Number(value || 0));
const formatCurrency = (value) => new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(value || 0));

const statsCards = computed(() => {
  const stats = dashboard.value.stats || {};
  const rev = dashboard.value.revenue || {};

  return [
    {
      label: "Today's Bookings",
      value: formatNumber(stats.today_appointments),
      icon: CalendarDaysIcon,
      iconColor: 'text-admin',
      iconWrap: 'bg-admin/10 border-admin/25',
      accentBarClass: 'bg-admin',
      badge: `${stats.pending_appointments || 0} Pending`,
      badgeIcon: ClockIcon,
      badgeClass: 'bg-admin/10 text-admin border-admin/20',
      sub: `vs yesterday: ${stats.appointments_yesterday || 0}`,
      subDotColor: 'bg-admin'
    },
    {
      label: "Today's Revenue",
      value: `₦${formatCurrency(stats.today_revenue || 0)}`,
      icon: BanknotesIcon,
      iconColor: 'text-emerald-400',
      iconWrap: 'bg-emerald-500/10 border-emerald-500/25',
      accentBarClass: 'bg-emerald-400',
      badge: 'Daily Flow',
      badgeClass: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
      sub: `Week: ₦${formatCurrency(stats.week_revenue || 0)}`,
      subDotColor: 'bg-emerald-400'
    },
    {
      label: "This Week",
      value: `₦${formatCurrency(stats.week_revenue || 0)}`,
      icon: ArrowTrendingUpIcon,
      iconColor: 'text-teal-400',
      iconWrap: 'bg-teal-500/10 border-teal-500/25',
      accentBarClass: 'bg-teal-400',
      badge: '7 Days',
      badgeClass: 'bg-teal-500/10 text-teal-400 border-teal-500/20',
      sub: `Month: ₦${formatCurrency(stats.month_revenue || 0)}`,
      subDotColor: 'bg-teal-400'
    },
    {
      label: "This Month",
      value: `₦${formatCurrency(stats.month_revenue || 0)}`,
      icon: ChartBarIcon,
      iconColor: 'text-cyan-400',
      iconWrap: 'bg-cyan-500/10 border-cyan-500/25',
      accentBarClass: 'bg-cyan-400',
      badge: 'MTD',
      badgeClass: 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
      sub: `Total: ₦${formatCurrency(rev.total_revenue || 0)}`,
      subDotColor: 'bg-cyan-400'
    },
    {
      label: "Total Customers",
      value: formatNumber(stats.total_customers),
      icon: UsersIcon,
      iconColor: 'text-sky-400',
      iconWrap: 'bg-sky-500/10 border-sky-500/25',
      accentBarClass: 'bg-sky-400',
      badge: 'Active',
      badgeIcon: CheckBadgeIcon,
      badgeClass: 'bg-sky-500/10 text-sky-400 border-sky-500/20',
      sub: `+${stats.new_customers_week || 0} this week`,
      subDotColor: 'bg-sky-400'
    }
  ];
});
</script>
