<template>
  <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-white dark:from-[#161B22] dark:via-[#0E1217] dark:to-black p-6 lg:p-8 shadow-2xl">
    <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-[var(--color-accent)]/15 blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
          <p class="text-[11px] uppercase tracking-[0.25em] font-bold text-[var(--color-accent)]">Live Financial Channel</p>
        </div>
        <h1 class="font-display text-2xl lg:text-3xl font-bold tracking-tight text-slate-900 dark:text-white drop-shadow-sm">
          Bank & <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] via-amber-500 to-amber-600 dark:via-amber-300 dark:to-[var(--color-accent)]">Payment Controls</span>
        </h1>
        <p class="mt-2 text-xs lg:text-sm text-slate-600 dark:text-[var(--color-text-secondary)] max-w-2xl leading-relaxed">
          Configure salon bank accounts, customize checkout parameters, and oversee incoming payment transactions and barber assignments with full administrative control.
        </p>
      </div>

      <!-- Action buttons -->
      <div class="flex items-center gap-3 shrink-0">
        <button
          type="button"
          @click="$emit('refresh')"
          :disabled="loading || saving"
          class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/[0.04] px-4 py-3 text-xs font-semibold text-slate-700 dark:text-white/80 hover:bg-slate-100 dark:hover:bg-white/[0.08] hover:text-slate-900 dark:hover:text-white transition-all flex items-center gap-2 disabled:opacity-50 shadow-sm cursor-pointer"
          title="Reload from DB"
        >
          <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin': loading }" />
          <span>Refresh</span>
        </button>
        <button
          v-if="currentTab !== 'transactions'"
          type="button"
          @click="$emit('save')"
          :disabled="saving || loading"
          class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--color-accent)] to-amber-500 px-6 py-3 text-xs font-bold text-obsidian shadow-lg shadow-[var(--color-accent)]/20 hover:brightness-110 active:scale-95 transition-all disabled:opacity-50 cursor-pointer"
        >
          <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></span>
          <CheckCircleIcon v-else class="h-4 w-4" />
          <span>{{ saving ? 'Saving Changes...' : 'Save All Settings' }}</span>
        </button>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="relative z-10 flex flex-wrap items-center gap-2 mt-6 pt-4 border-t border-slate-200/80 dark:border-white/[0.08]">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        type="button"
        @click="$emit('update:currentTab', tab.id)"
        :class="[
          'flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer',
          currentTab === tab.id
            ? 'bg-gradient-to-r from-[var(--color-accent)] to-amber-500 text-obsidian shadow-md shadow-[var(--color-accent)]/20'
            : 'bg-white/80 dark:bg-white/[0.04] text-slate-700 dark:text-zinc-300 hover:bg-white dark:hover:bg-white/[0.08] hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-white/[0.06]'
        ]"
      >
        <component :is="tab.icon" class="h-4 w-4" />
        <span>{{ tab.label }}</span>
        <span
          v-if="tab.badge"
          class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-black/20 text-current"
        >
          {{ tab.badge }}
        </span>
      </button>
    </div>
  </div>
</template>

<script setup>
import {
  ArrowPathIcon,
  CheckCircleIcon,
  BuildingLibraryIcon,
  AdjustmentsHorizontalIcon,
  QueueListIcon
} from '@heroicons/vue/24/outline';

defineProps({
  currentTab: {
    type: String,
    default: 'account'
  },
  loading: {
    type: Boolean,
    default: false
  },
  saving: {
    type: Boolean,
    default: false
  }
});

defineEmits(['refresh', 'save', 'update:currentTab']);

const tabs = [
  { id: 'account', label: 'Bank Account & Payout', icon: BuildingLibraryIcon },
  { id: 'gateway', label: 'Payment Page Controls', icon: AdjustmentsHorizontalIcon, badge: 'Live UI' },
  { id: 'transactions', label: 'Payments & Ledger', icon: QueueListIcon, badge: 'Admin Control' }
];
</script>
