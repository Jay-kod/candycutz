<template>
  <div class="space-y-6">
    <!-- Filter & Toolbar Header -->
    <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-5 shadow-xl flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="h-9 w-9 rounded-xl bg-purple-500/15 border border-purple-500/30 flex items-center justify-center text-purple-400">
          <QueueListIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-sm font-bold text-slate-900 dark:text-white">Brevo Transactional Email Audit</h2>
          <p class="text-xs text-slate-500 dark:text-white/50">Recent outbound emails dispatched via the Brevo SMTP &amp; API engine.</p>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <select
          v-model="statusFilter"
          @change="$emit('filter-change', statusFilter)"
          class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 text-xs text-slate-700 dark:text-white outline-none focus:border-[var(--color-accent)] cursor-pointer font-medium"
        >
          <option value="all">All Delivery Statuses</option>
          <option value="delivered">Delivered Only</option>
          <option value="accepted">Accepted by Brevo</option>
          <option value="pending">Pending Queue</option>
          <option value="failed">Failed Only</option>
        </select>

        <button
          type="button"
          @click="$emit('refresh')"
          :disabled="loading"
          class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 text-xs font-semibold text-slate-700 dark:text-white hover:bg-slate-200 dark:hover:bg-white/[0.08] transition-all disabled:opacity-50 cursor-pointer"
        >
          <ArrowPathIcon class="h-3.5 w-3.5" :class="{ 'animate-spin': loading }" />
          <span>Refresh Logs</span>
        </button>
      </div>
    </div>

    <!-- Table Container (Strictly wrapped in overflow-x-auto for responsiveness) -->
    <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] shadow-2xl overflow-hidden">
      <!-- Loading Skeleton -->
      <div v-if="loading && !deliveries.length" class="p-8 text-center text-xs text-slate-400 dark:text-white/40 space-y-3">
        <ArrowPathIcon class="h-6 w-6 animate-spin mx-auto text-[var(--color-accent)]" />
        <p>Loading email delivery records from database...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="!filteredDeliveries.length" class="p-12 text-center space-y-3">
        <div class="h-12 w-12 rounded-2xl bg-slate-100 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 flex items-center justify-center text-slate-400 dark:text-white/30 mx-auto">
          <EnvelopeIcon class="h-6 w-6" />
        </div>
        <p class="font-display text-sm font-bold text-slate-900 dark:text-white">No Email Delivery Records Found</p>
        <p class="text-xs text-slate-500 dark:text-white/50 max-w-md mx-auto">
          {{ statusFilter === 'all'
            ? 'No outbound emails have been dispatched yet. Use the "Dispatch Test Email" tool in the Credentials tab to run your first test.'
            : 'No delivery logs match the selected status filter.' }}
        </p>
      </div>

      <!-- Deliveries Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="border-b border-slate-200 dark:border-white/[0.06] bg-slate-50/70 dark:bg-white/[0.02] text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-white/50">
              <th class="py-3.5 px-6">Recipient &amp; Subject</th>
              <th class="py-3.5 px-4">Status</th>
              <th class="py-3.5 px-4">Provider Ref / Message ID</th>
              <th class="py-3.5 px-4">Attempts</th>
              <th class="py-3.5 px-4">Dispatched At</th>
              <th class="py-3.5 px-6 text-right">Details</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-white/[0.04]">
            <tr
              v-for="item in filteredDeliveries"
              :key="item.id"
              class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors"
            >
              <!-- Recipient & Subject -->
              <td class="py-4 px-6">
                <div class="font-semibold text-slate-900 dark:text-white truncate max-w-[240px]">
                  {{ item.notification?.title || item.event_type || 'Transactional Email' }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-white/50 font-mono mt-0.5 truncate max-w-[240px]">
                  {{ item.notification?.recipient?.email || item.notification?.user?.email || item.recipient_email || 'Recipient' }}
                </div>
              </td>

              <!-- Status Badge -->
              <td class="py-4 px-4 whitespace-nowrap">
                <span
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                  :class="statusBadgeClass(item.status)"
                >
                  <span class="h-1.5 w-1.5 rounded-full" :class="statusDotClass(item.status)"></span>
                  <span>{{ formatStatus(item.status) }}</span>
                </span>
              </td>

              <!-- Provider Ref -->
              <td class="py-4 px-4 font-mono text-[11px] text-slate-600 dark:text-white/60">
                <span v-if="item.provider_reference" class="truncate max-w-[180px] block" :title="item.provider_reference">
                  {{ item.provider_reference }}
                </span>
                <span v-else class="text-slate-400 dark:text-white/30 italic">None</span>
              </td>

              <!-- Attempts -->
              <td class="py-4 px-4 font-mono text-slate-700 dark:text-white/70">
                {{ item.attempts || 1 }}
              </td>

              <!-- Dispatched At -->
              <td class="py-4 px-4 whitespace-nowrap text-slate-500 dark:text-white/50">
                {{ formatDateTime(item.delivered_at || item.created_at) }}
              </td>

              <!-- Details / Error -->
              <td class="py-4 px-6 text-right">
                <span
                  v-if="item.sanitized_failure_reason"
                  class="text-[10px] font-mono text-rose-400 truncate max-w-[200px] inline-block text-left"
                  :title="item.sanitized_failure_reason"
                >
                  {{ item.sanitized_failure_reason }}
                </span>
                <span v-else class="text-[10px] text-emerald-400 font-semibold">
                  Delivered OK
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import {
  QueueListIcon,
  ArrowPathIcon,
  EnvelopeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  deliveries: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
});

defineEmits(['refresh', 'filter-change']);

const statusFilter = ref('all');

const filteredDeliveries = computed(() => {
  if (statusFilter.value === 'all') {
    return props.deliveries;
  }
  return props.deliveries.filter(d => String(d.status).toLowerCase() === statusFilter.value.toLowerCase());
});

function formatStatus(status) {
  if (!status) return 'Unknown';
  return status.replace(/_/g, ' ');
}

function statusBadgeClass(status) {
  switch (String(status).toLowerCase()) {
    case 'delivered':
      return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
    case 'accepted':
      return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
    case 'pending':
    case 'processing':
    case 'retrying':
      return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
    case 'failed':
    case 'exhausted':
    case 'suppressed':
      return 'bg-rose-500/10 text-rose-400 border-rose-500/20';
    default:
      return 'bg-slate-500/10 text-slate-400 border-slate-500/20';
  }
}

function statusDotClass(status) {
  switch (String(status).toLowerCase()) {
    case 'delivered':
      return 'bg-emerald-400';
    case 'accepted':
      return 'bg-blue-400';
    case 'pending':
    case 'processing':
    case 'retrying':
      return 'bg-amber-400 animate-pulse';
    case 'failed':
    case 'exhausted':
    case 'suppressed':
      return 'bg-rose-400';
    default:
      return 'bg-slate-400';
  }
}

function formatDateTime(val) {
  if (!val) return '—';
  try {
    const d = new Date(val);
    return d.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch {
    return String(val);
  }
}
</script>
