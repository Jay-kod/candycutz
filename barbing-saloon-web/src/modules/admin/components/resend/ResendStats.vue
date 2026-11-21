<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1: Gateway Status -->
    <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-5 shadow-lg group hover:border-[var(--color-accent)]/40 transition-all duration-300">
      <div class="flex items-center justify-between">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--color-accent)]/15 text-[var(--color-accent)] border border-[var(--color-accent)]/30">
          <ShieldCheckIcon v-if="isConfigured" class="h-5 w-5" />
          <ExclamationTriangleIcon v-else class="h-5 w-5 text-amber-400" />
        </span>
        <span
          class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border"
          :class="isConfigured
            ? 'bg-emerald-950/40 text-emerald-400 border-emerald-500/30'
            : 'bg-amber-950/40 text-amber-400 border-amber-500/30'"
        >
          <span class="h-1.5 w-1.5 rounded-full" :class="isConfigured ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'"></span>
          <span>{{ isConfigured ? 'Ready' : 'Incomplete' }}</span>
        </span>
      </div>
      <div class="mt-4">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-white/50">Gateway Status</p>
        <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5 truncate">
          {{ isConfigured ? 'Connected & Verified' : 'Action Required' }}
        </p>
        <p class="text-[11px] text-slate-500 dark:text-white/40 mt-1">
          {{ isConfigured ? 'Resend API key configured and ready.' : 'Provide Resend API key and verified sender.' }}
        </p>
      </div>
    </div>

    <!-- Card 2: Transport & API Provider -->
    <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-5 shadow-lg group hover:border-[var(--color-accent)]/40 transition-all duration-300">
      <div class="flex items-center justify-between">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-500/15 text-blue-400 border border-blue-500/30">
          <ServerStackIcon class="h-5 w-5" />
        </span>
        <span class="text-[10px] font-mono font-bold text-blue-400 px-2 py-0.5 rounded bg-blue-500/10 border border-blue-500/20">
          Resend API
        </span>
      </div>
      <div class="mt-4">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-white/50">Transport Provider</p>
        <p class="text-lg font-mono font-bold text-slate-900 dark:text-white mt-0.5 truncate">
          Resend PHP SDK
        </p>
        <p class="text-[11px] text-slate-500 dark:text-white/40 mt-1">
          HTTPS REST API with rate limit safety
        </p>
      </div>
    </div>

    <!-- Card 3: Sender Identity -->
    <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-5 shadow-lg group hover:border-[var(--color-accent)]/40 transition-all duration-300">
      <div class="flex items-center justify-between">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-500/15 text-purple-400 border border-purple-500/30">
          <EnvelopeIcon class="h-5 w-5" />
        </span>
        <span class="text-[10px] font-bold uppercase tracking-wider text-purple-400 px-2 py-0.5 rounded bg-purple-500/10 border border-purple-500/20 truncate max-w-[120px]">
          {{ senderName || 'CandyCutz' }}
        </span>
      </div>
      <div class="mt-4">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-white/50">Verified Sender</p>
        <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5 truncate" :title="senderEmail">
          {{ senderEmail || 'Not set' }}
        </p>
        <p class="text-[11px] text-slate-500 dark:text-white/40 mt-1 truncate">
          Outbound "From" address for clients
        </p>
      </div>
    </div>

    <!-- Card 4: Delivery Health -->
    <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-5 shadow-lg group hover:border-[var(--color-accent)]/40 transition-all duration-300">
      <div class="flex items-center justify-between">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
          <PaperAirplaneIcon class="h-5 w-5" />
        </span>
        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 px-2 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/20">
          {{ successRate }}% Success
        </span>
      </div>
      <div class="mt-4">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-white/50">Emails Dispatched</p>
        <p class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">
          {{ totalDeliveries }} <span class="text-xs font-normal text-slate-500 dark:text-white/40">recorded</span>
        </p>
        <p class="text-[11px] text-slate-500 dark:text-white/40 mt-1">
          {{ failedDeliveries }} failure{{ failedDeliveries === 1 ? '' : 's' }} logged
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import {
  ShieldCheckIcon,
  ExclamationTriangleIcon,
  ServerStackIcon,
  EnvelopeIcon,
  PaperAirplaneIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  isConfigured: {
    type: Boolean,
    default: false
  },
  hostName: {
    type: String,
    default: 'smtp-relay.brevo.com'
  },
  hostPort: {
    type: [String, Number],
    default: '587'
  },
  senderEmail: {
    type: String,
    default: ''
  },
  senderName: {
    type: String,
    default: 'CandyCutz'
  },
  totalDeliveries: {
    type: Number,
    default: 0
  },
  failedDeliveries: {
    type: Number,
    default: 0
  }
});

const successRate = computed(() => {
  if (props.totalDeliveries === 0) return 100;
  const successful = Math.max(0, props.totalDeliveries - props.failedDeliveries);
  return Math.round((successful / props.totalDeliveries) * 100);
});
</script>
