<template>
  <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-white dark:from-[#161B22] dark:via-[#0E1217] dark:to-black p-6 lg:p-8 shadow-2xl">
    <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-[var(--color-accent)]/15 blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <span
            class="inline-flex h-2.5 w-2.5 rounded-full"
            :class="isConfigured ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'"
          ></span>
          <p class="text-[11px] uppercase tracking-[0.25em] font-bold text-[var(--color-accent)]">
            {{ isConfigured ? 'Resend Gateway Active' : 'Resend Setup Required' }}
          </p>
          <span class="text-[10px] font-mono font-bold text-[var(--color-accent)] px-2 py-0.5 rounded bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20">
            Transactional Email API
          </span>
        </div>
        <h1 class="font-display text-2xl lg:text-3xl font-bold tracking-tight text-slate-900 dark:text-white drop-shadow-sm">
          Resend <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] via-amber-500 to-amber-600 dark:via-amber-300 dark:to-[var(--color-accent)]">Email Gateway</span>
        </h1>
        <p class="mt-2 text-xs lg:text-sm text-slate-600 dark:text-[var(--color-text-secondary)] max-w-2xl leading-relaxed">
          Configure and monitor Resend transactional email delivery for booking confirmations, receipt notifications, password resets, and admin dispatch.
        </p>
      </div>

      <!-- Action buttons -->
      <div class="flex flex-wrap items-center gap-3 shrink-0">
        <a
          href="https://resend.com/api-keys"
          target="_blank"
          rel="noopener noreferrer"
          class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/[0.04] px-4 py-3 text-xs font-semibold text-slate-700 dark:text-white/80 hover:bg-slate-100 dark:hover:bg-white/[0.08] hover:text-slate-900 dark:hover:text-white transition-all flex items-center gap-2 shadow-sm cursor-pointer"
          title="Open Resend API Keys dashboard in a new tab"
        >
          <ArrowTopRightOnSquareIcon class="h-4 w-4 text-[var(--color-accent)]" />
          <span>Resend Console</span>
        </a>

        <button
          type="button"
          @click="$emit('refresh')"
          :disabled="loading || saving"
          class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/[0.04] px-4 py-3 text-xs font-semibold text-slate-700 dark:text-white/80 hover:bg-slate-100 dark:hover:bg-white/[0.08] hover:text-slate-900 dark:hover:text-white transition-all flex items-center gap-2 disabled:opacity-50 shadow-sm cursor-pointer"
          title="Reload configuration and delivery metrics"
        >
          <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin': loading }" />
          <span>Refresh</span>
        </button>

        <button
          v-if="currentTab !== 'deliveries'"
          type="button"
          @click="$emit('save')"
          :disabled="saving || loading"
          class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--color-accent)] to-amber-500 px-6 py-3 text-xs font-bold text-obsidian shadow-lg shadow-[var(--color-accent)]/20 hover:brightness-110 active:scale-95 transition-all disabled:opacity-50 cursor-pointer"
        >
          <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></span>
          <CheckCircleIcon v-else class="h-4 w-4" />
          <span>{{ saving ? 'Saving Changes...' : 'Save Configuration' }}</span>
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
  ArrowTopRightOnSquareIcon,
  KeyIcon,
  AcademicCapIcon,
  QueueListIcon
} from '@heroicons/vue/24/outline';

defineProps({
  currentTab: {
    type: String,
    default: 'config'
  },
  isConfigured: {
    type: Boolean,
    default: false
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
  { id: 'config', label: 'Credentials & SMTP', icon: KeyIcon },
  { id: 'guide', label: 'Step-by-Step Setup Guide', icon: AcademicCapIcon, badge: '5 Steps' },
  { id: 'deliveries', label: 'Email Deliveries & Audit', icon: QueueListIcon, badge: 'Live' }
];
</script>
