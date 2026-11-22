<template>
  <SuperAdminLayout>
    <section class="space-y-6 animate-fade-in pb-10 max-w-[1600px] mx-auto">
      <!-- Command Banner -->
      <SuperAdminCommandBanner
        :is-refreshing="isRefreshing"
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

      <!-- Populated Dashboard -->
      <template v-else>
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

const formatNumber = (val) => new Intl.NumberFormat('en-NG').format(Number(val || 0));

const loadData = async () => {
  isRefreshing.value = true;
  try {
    const response = await superadminApi.dashboard();
    dashboard.value = response.data?.data || {};
  } catch (err) {
    console.error('Failed to load superadmin dashboard:', err);
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
      sub: 'Verified & active',
      icon: ShieldCheckIcon,
    },
    {
      label: 'Admins',
      value: formatNumber(stats.total_admins),
      sub: 'Salon managers',
      icon: ShieldCheckIcon,
    },
    {
      label: 'Barbers',
      value: formatNumber(stats.barbers),
      sub: 'Active chairs',
      icon: ScissorsIcon,
    },
    {
      label: 'Customers',
      value: formatNumber(stats.customers),
      sub: 'Registered clients',
      icon: UserGroupIcon,
    },
  ];
});
</script>
