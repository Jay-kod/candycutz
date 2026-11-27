<template>
  <component :is="layoutComponent">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 animate-fade-in pb-12">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span class="text-[10px] font-mono uppercase tracking-[0.25em] text-admin font-bold">
            Policy & Enforcement
          </span>
          <h1 class="font-display text-2xl sm:text-3xl font-bold text-theme-text mt-1">
            No-Show Policy & Refunds
          </h1>
          <p class="text-xs text-theme-text-muted mt-1">
            Set arrival deadlines, grace extensions, consequence thresholds, forfeit deposits, and start refund flows.
          </p>
        </div>

        <button
          type="button"
          @click="loadDashboard(true)"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-theme-surface border border-theme-border text-xs font-bold text-theme-text hover:border-admin/40 transition-all cursor-pointer self-start sm:self-auto"
        >
          <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin': loading }" />
          <span>Refresh</span>
        </button>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-theme-border bg-theme-surface p-4 shadow-lg">
          <p class="text-[10px] font-bold uppercase tracking-wider text-theme-text-muted">Total No-Shows</p>
          <p class="font-display text-2xl font-bold text-theme-text mt-1">{{ stats.total_no_shows }}</p>
        </div>
        <div class="rounded-2xl border border-amber-500/30 bg-theme-surface p-4 shadow-lg">
          <p class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Pending Appeals</p>
          <p class="font-display text-2xl font-bold text-amber-400 mt-1">{{ stats.pending_appeals }}</p>
        </div>
        <div class="rounded-2xl border border-red-500/30 bg-theme-surface p-4 shadow-lg">
          <p class="text-[10px] font-bold uppercase tracking-wider text-red-400">Deposits Forfeited</p>
          <p class="font-display text-2xl font-bold text-red-400 mt-1">{{ stats.forfeited_count }}</p>
        </div>
        <div class="rounded-2xl border border-purple-500/30 bg-theme-surface p-4 shadow-lg">
          <p class="text-[10px] font-bold uppercase tracking-wider text-purple-400">Deposit Required Clients</p>
          <p class="font-display text-2xl font-bold text-purple-400 mt-1">{{ stats.flagged_customers }}</p>
        </div>
      </div>

      <!-- Policy Form -->
      <NoShowPolicyForm :policy="policy" :saving="savingPolicy" @save="savePolicy" />

      <!-- Filter Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
          <button
            v-for="tab in filterTabs"
            :key="tab.value"
            type="button"
            @click="filter = tab.value; loadDashboard(true)"
            class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap border transition-all cursor-pointer"
            :class="filter === tab.value ? 'bg-admin text-obsidian border-admin' : 'bg-theme-surface border-theme-border text-theme-text-muted hover:text-theme-text'"
          >
            {{ tab.label }}
          </button>
        </div>

        <input
          v-model="search"
          @keyup.enter="loadDashboard(true)"
          type="text"
          placeholder="Search client or booking ref..."
          class="w-full sm:w-64 px-3.5 py-2 rounded-xl bg-theme-surface border border-theme-border text-xs text-theme-text focus:outline-none focus:border-admin"
        />
      </div>

      <!-- Records List -->
      <NoShowRecordsList
        :records="records"
        :processing-id="processingId"
        @forfeit="forfeitDeposit"
        @open-refund="activeRefundRecord = $event"
        @open-appeal="activeAppealRecord = $event"
      />

      <!-- Customers Requiring Deposit -->
      <div v-if="flaggedCustomers.length > 0" class="rounded-2xl border border-theme-border bg-theme-surface p-5 space-y-4">
        <h2 class="text-sm font-bold text-theme-text">Customers Flagged for Mandatory Deposit</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <div
            v-for="cust in flaggedCustomers"
            :key="cust.id"
            class="p-3.5 rounded-xl bg-theme-bg border border-theme-border flex items-center justify-between gap-3"
          >
            <div class="min-w-0">
              <p class="text-xs font-bold text-theme-text truncate">{{ cust.name }}</p>
              <p class="text-[11px] text-theme-text-muted truncate">{{ cust.email }} · {{ cust.no_show_count }} no-show(s)</p>
            </div>
            <button
              type="button"
              @click="resetCustomerFlag(cust)"
              class="px-2.5 py-1.5 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-[11px] font-bold shrink-0 cursor-pointer"
            >
              Clear Flag
            </button>
          </div>
        </div>
      </div>

      <NoShowActionModals
        :refund-record="activeRefundRecord"
        :appeal-record="activeAppealRecord"
        :submitting="Boolean(processingId)"
        @close-refund="activeRefundRecord = null"
        @close-appeal="activeAppealRecord = null"
        @submit-refund="handleRefund"
        @submit-appeal="handleAppeal"
      />
    </div>
  </component>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { ArrowPathIcon } from '@heroicons/vue/24/outline';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import SuperAdminLayout from '@/portals/superadmin/layouts/SuperAdminLayout.vue';
import { useAdminNoShows } from '../composables/useAdminNoShows';
import NoShowPolicyForm from '../components/NoShowPolicyForm.vue';
import NoShowRecordsList from '../components/NoShowRecordsList.vue';
import NoShowActionModals from '../components/NoShowActionModals.vue';

const route = useRoute();
const layoutComponent = computed(() => (route.path.startsWith('/superadmin') ? SuperAdminLayout : AdminLayout));

const {
  loading,
  savingPolicy,
  processingId,
  filter,
  search,
  records,
  flaggedCustomers,
  stats,
  policy,
  loadDashboard,
  savePolicy,
  forfeitDeposit,
  submitRefund,
  submitAppealDecision,
  resetCustomerFlag,
} = useAdminNoShows();

const activeRefundRecord = ref(null);
const activeAppealRecord = ref(null);

const filterTabs = [
  { label: 'All Records', value: 'all' },
  { label: 'Pending Appeals', value: 'pending_appeals' },
  { label: 'Deposits Forfeited', value: 'forfeited' },
  { label: 'Refundable Paid', value: 'refundable' },
];

async function handleRefund(recordId, payload) {
  const ok = await submitRefund(recordId, payload);
  if (ok) activeRefundRecord.value = null;
}

async function handleAppeal(recordId, payload) {
  const ok = await submitAppealDecision(recordId, payload);
  if (ok) activeAppealRecord.value = null;
}

onMounted(() => loadDashboard());
</script>
