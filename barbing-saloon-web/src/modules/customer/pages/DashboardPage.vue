<template>
  <CustomerLayout>
    <section class="space-y-6 sm:space-y-8 animate-fade-in pb-10 max-w-[1600px] mx-auto">
      <!-- Welcome Header -->
      <CustomerWelcomeHeader />

      <!-- Active Draft Reservation Banner -->
      <DraftBookingBanner @discarded="loadDashboard" @resumed="loadDashboard" />

      <!-- Skeleton Loading State -->
      <div v-if="loading" class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <div v-for="i in 4" :key="i" class="h-28 rounded-2xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)] animate-pulse"></div>
        </div>
        <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
          <div class="h-80 rounded-2xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)] animate-pulse"></div>
          <div class="h-80 rounded-2xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)] animate-pulse"></div>
        </div>
      </div>

      <template v-else>
        <!-- Customer KPI Stats Grid -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <StatCard
            v-for="item in statsCards"
            :key="item.label"
            :title="item.label"
            :value="item.value"
            :icon="item.icon"
            variant="customer"
          />
        </div>

        <!-- Bookings & Shortcuts Grid -->
        <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
          <!-- Upcoming Appointments -->
          <CustomerUpcomingBookings
            :appointments="dashboard.upcoming_appointments || []"
            :fallback-bank="salonFallbackBank"
            @copy="copyCode"
          />

          <!-- Quick Navigation Hub -->
          <CustomerQuickLinks />

          <!-- Recent Notifications & Alerts -->
          <CustomerRecentNotifications
            :notifications="notifications"
          />
        </div>
      </template>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { client } from '@/shared/api/client';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import StatCard from '@/shared/ui/StatCard.vue';
import CustomerWelcomeHeader from '../components/dashboard/CustomerWelcomeHeader.vue';
import CustomerUpcomingBookings from '../components/dashboard/CustomerUpcomingBookings.vue';
import CustomerQuickLinks from '../components/dashboard/CustomerQuickLinks.vue';
import CustomerRecentNotifications from '../components/dashboard/CustomerRecentNotifications.vue';
import DraftBookingBanner from '../components/booking/DraftBookingBanner.vue';
import { 
  CalendarIcon, 
  ClockIcon, 
  CheckCircleIcon, 
  StarIcon, 
} from '@heroicons/vue/24/outline';
import { useToast } from '../../../core/composables/useToast';

const dashboard = ref({ stats: {}, upcoming_appointments: [] });
const notifications = ref([]);
const loading = ref(true);
const toast = useToast();
let pollInterval = null;

const salonFallbackBank = ref({
  bank_name: 'Zenith Bank',
  account_name: 'Candy Cutz Luxury Grooming',
  account_number: '1012345678',
});

const formatNumber = (val) => new Intl.NumberFormat('en-NG').format(Number(val || 0));

const statsCards = computed(() => [
  { label: 'Total Bookings', value: formatNumber(dashboard.value.stats?.total_bookings), icon: CalendarIcon },
  { label: 'Upcoming', value: formatNumber(dashboard.value.stats?.upcoming_bookings), icon: ClockIcon },
  { label: 'Completed', value: formatNumber(dashboard.value.stats?.completed_bookings), icon: CheckCircleIcon },
  { label: 'Reviews Given', value: formatNumber(dashboard.value.stats?.reviews_count), icon: StarIcon },
]);

async function loadData(silent = false) {
  if (!silent) loading.value = true;
  try {
    const [dashRes, notifRes] = await Promise.all([
      client.get('/customer/dashboard').catch(() => ({ data: { data: {} } })),
      client.get('/customer/notifications').catch(() => ({ data: { data: [] } })),
    ]);
    dashboard.value = dashRes.data?.data || dashRes.data || { stats: {}, upcoming_appointments: [] };
    notifications.value = notifRes.data?.data || notifRes.data || [];
  } catch (err) {
    console.error('Error loading customer dashboard data:', err);
  } finally {
    if (!silent) loading.value = false;
  }
}

async function copyCode(code) {
  if (!code) return;
  try {
    await navigator.clipboard.writeText(code);
    toast.success('Copied to clipboard!');
  } catch (err) {
    toast.error('Failed to copy');
  }
}

onMounted(async () => {
  loadData();
  pollInterval = setInterval(() => loadData(true), 30000);
  try {
    const res = await client.get('/settings');
    const data = res.data?.data || res.data || {};
    if (data.bank_name && data.account_number) {
      salonFallbackBank.value = {
        bank_name: data.bank_name,
        account_name: data.account_name || 'Candy Cutz Luxury Grooming',
        account_number: data.account_number,
      };
    }
  } catch (_) {
    // Non-blocking fallback
  }
});

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval);
});
</script>
