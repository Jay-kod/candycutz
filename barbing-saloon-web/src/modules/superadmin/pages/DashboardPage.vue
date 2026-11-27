<template>
  <SuperAdminLayout>
    <section class="space-y-6 animate-fade-in pb-10 max-w-[1600px] mx-auto">
      <!-- Command Banner -->
      <SuperAdminCommandBanner
        :is-refreshing="isRefreshing"
        :platform-status="platformStatus"
        :last-updated="lastUpdatedAt"
        @refresh="loadData"
      />

      <!-- Loading State -->
      <div v-if="loading" class="space-y-6">
        <div class="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
          <div
            v-for="i in 6"
            :key="'subsystem-skel-' + i"
            class="h-24 rounded-2xl animate-pulse bg-[var(--color-bg-secondary)] border border-[var(--color-border)]"
          ></div>
        </div>
        <div class="grid gap-3 sm:gap-4 grid-cols-1 xs:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5">
          <div
            v-for="i in 5"
            :key="'kpi-skel-' + i"
            class="h-28 rounded-2xl animate-pulse bg-[var(--color-bg-secondary)] border border-[var(--color-border)]"
          ></div>
        </div>
      </div>

      <div
        v-else-if="!hasDashboardData"
        role="alert"
        class="rounded-xl border border-rose-500/30 bg-[var(--color-bg-secondary)] p-6"
      >
        <h2 class="font-semibold text-[var(--color-text-primary)]">
          {{ isRefreshing ? 'Loading dashboard data' : 'Dashboard data is unavailable' }}
        </h2>
        <p class="mt-1 text-sm text-[var(--color-text-muted)]">
          The platform overview could not be loaded. Try again to retrieve the latest status.
        </p>
        <button
          type="button"
          class="mt-4 rounded-lg border border-rose-500/30 px-3 py-2 text-sm font-semibold text-rose-500 transition-colors hover:bg-rose-500/10 disabled:opacity-50"
          :disabled="isRefreshing"
          @click="loadData"
        >
          {{ isRefreshing ? 'Retrying…' : 'Retry' }}
        </button>
      </div>

      <!-- Populated Dashboard -->
      <template v-else>
        <div
          v-if="loadError"
          role="status"
          class="rounded-lg border border-amber-500/30 bg-amber-500/5 px-4 py-3 text-sm text-[var(--color-text-secondary)]"
        >
          Refresh failed. Showing the last successfully loaded data.
        </div>

        <div
          v-if="platformStatus !== 'operational'"
          role="status"
          class="flex flex-col gap-3 rounded-xl border px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
          :class="platformStatus === 'degraded' ? 'border-rose-500/30 bg-rose-500/5' : 'border-amber-500/30 bg-amber-500/5'"
        >
          <div>
            <p class="font-semibold text-[var(--color-text-primary)]">
              {{ platformStatusMessage }}
            </p>
            <p class="mt-0.5 text-xs text-[var(--color-text-muted)]">
              Check system health and unresolved incidents before taking action.
            </p>
          </div>
          <RouterLink
            :to="platformStatusTarget"
            class="inline-flex shrink-0 items-center text-sm font-semibold text-rose-500 hover:text-rose-400"
          >
            Investigate
          </RouterLink>
        </div>

        <!-- Telemetry & Circuit Breakers Cockpit Strip -->
        <SuperAdminSubsystemStrip :subsystems="dashboard.subsystems" />

        <!-- Executive KPI Cards -->
        <div class="grid gap-3 sm:gap-4 grid-cols-1 xs:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5">
          <StatCard
            v-for="card in kpiCards"
            :key="card.label"
            :title="card.label"
            :value="card.value"
            :subtitle="card.sub"
            :icon="card.icon"
            variant="rose"
          />
        </div>

        <!-- Telemetry & Controls Grid -->
        <div class="grid gap-6 lg:grid-cols-3">
          <!-- Role Distribution -->
          <div class="lg:col-span-2">
            <SuperAdminRoleDistribution :stats="dashboard.stats" />
          </div>

          <!-- Quick Governance Actions -->
          <div>
            <SuperAdminQuickActions />
          </div>
        </div>

        <!-- Audit Activity Table -->
        <SuperAdminAuditTable
          :logs="dashboard.recent_logs"
          :loading="isRefreshing"
        />
      </template>
    </section>
  </SuperAdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import SuperAdminLayout from '@/portals/superadmin/layouts/SuperAdminLayout.vue';
import { superadminApi } from '@/shared/api/old_superadminApi';
import StatCard from '@/shared/ui/StatCard.vue';
import SuperAdminCommandBanner from '../components/SuperAdminCommandBanner.vue';
import SuperAdminSubsystemStrip from '../components/SuperAdminSubsystemStrip.vue';
import SuperAdminRoleDistribution from '../components/SuperAdminRoleDistribution.vue';
import SuperAdminQuickActions from '../components/SuperAdminQuickActions.vue';
import SuperAdminAuditTable from '../components/SuperAdminAuditTable.vue';
import {
  UsersIcon,
  UserGroupIcon,
  ShieldCheckIcon,
  ScissorsIcon,
} from '@heroicons/vue/24/outline';

const dashboard = ref({ stats: {}, recent_logs: [] });
const loading = ref(true);
const isRefreshing = ref(false);
const hasDashboardData = ref(false);
const loadError = ref(false);
const lastUpdatedAt = ref(null);

const formatNumber = (val) => new Intl.NumberFormat('en-NG').format(Number(val || 0));

const platformStatus = computed(() => {
  const systems = dashboard.value.subsystems;
  if (!systems) return 'unknown';

  const signals = [
    systems.database?.status,
    systems.paystack?.state,
    systems.resend?.state,
    systems.sweeper?.status,
    systems.security?.status,
    systems.incidents?.status,
  ];
  if (signals.some((signal) => !signal)) return 'unknown';
  if (
    !['operational', 'degraded'].includes(systems.database.status) ||
    !['closed', 'half_open', 'open'].includes(systems.paystack.state) ||
    !['closed', 'half_open', 'open'].includes(systems.resend.state) ||
    !['optimal', 'operational', 'idle', 'warning', 'degraded'].includes(systems.sweeper.status) ||
    !['operational', 'warning', 'degraded', 'critical'].includes(systems.security.status) ||
    !['optimal', 'warning'].includes(systems.incidents.status) ||
    systems.incidents.unresolved_count == null ||
    systems.incidents.failed_jobs_count == null ||
    !Number.isFinite(Number(systems.incidents.unresolved_count)) ||
    !Number.isFinite(Number(systems.incidents.failed_jobs_count))
  ) {
    return 'unknown';
  }

  if (
    systems.database.status !== 'operational' ||
    systems.paystack.state === 'open' ||
    systems.resend.state === 'open' ||
    systems.security.status === 'degraded' ||
    systems.security.status === 'critical'
  ) {
    return 'degraded';
  }

  if (
    systems.paystack.state === 'half_open' ||
    systems.resend.state === 'half_open' ||
    !['optimal', 'operational'].includes(systems.sweeper.status) ||
    systems.security.status === 'warning' ||
    systems.incidents.status === 'warning' ||
    Number(systems.incidents.unresolved_count || 0) > 0 ||
    Number(systems.incidents.failed_jobs_count || 0) > 0
  ) {
    return 'attention';
  }

  return 'operational';
});

const platformStatusMessage = computed(() => {
  if (platformStatus.value === 'degraded') return 'A critical platform service is degraded.';
  if (platformStatus.value === 'attention') return 'Platform activity needs review.';
  return 'Live platform health data is incomplete.';
});

const platformStatusTarget = computed(() => {
  const incidents = dashboard.value.subsystems?.incidents;
  return incidents?.status === 'warning' ||
    Number(incidents?.unresolved_count || 0) > 0 ||
    Number(incidents?.failed_jobs_count || 0) > 0
    ? '/superadmin/system-errors'
    : '/superadmin/health';
});

const loadData = async () => {
  isRefreshing.value = true;
  loadError.value = false;
  try {
    const response = await superadminApi.dashboard();
    const data = response.data?.data;
    if (!data) throw new Error('Dashboard response did not contain data.');
    dashboard.value = data;
    hasDashboardData.value = true;
    lastUpdatedAt.value = new Date();
  } catch (err) {
    console.error('Failed to load superadmin dashboard:', err);
    loadError.value = true;
  } finally {
    loading.value = false;
    isRefreshing.value = false;
  }
};

onMounted(() => {
  loadData();
});

const kpiCards = computed(() => {
  const stats = dashboard.value.stats || {};
  return [
    {
      label: 'Total Users',
      value: formatNumber(stats.total_users),
      sub: 'All platform accounts',
      icon: UsersIcon,
    },
    {
      label: 'Active Users',
      value: formatNumber(stats.active_users),
      sub: 'Enabled accounts',
      icon: ShieldCheckIcon,
    },
    {
      label: 'Admin Accounts',
      value: formatNumber(stats.total_admins),
      sub: 'Admins & superadmins',
      icon: ShieldCheckIcon,
    },
    {
      label: 'Barbers',
      value: formatNumber(stats.barbers),
      sub: 'Accounts with barber role',
      icon: ScissorsIcon,
    },
    {
      label: 'Customers',
      value: formatNumber(stats.customers),
      sub: 'Registered customer accounts',
      icon: UserGroupIcon,
    },
  ];
});
</script>
