<template>
  <div class="space-y-4">
    <h2 class="font-display text-lg font-bold text-[var(--color-text-primary)]">
      Platform Governance
    </h2>
    <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-1">
      <RouterLink
        v-for="action in quickActions"
        :key="action.label"
        :to="action.to"
        class="group flex items-center gap-3.5 rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-3.5 transition-all duration-200 hover:border-rose-500/30 hover:bg-rose-500/[0.04] shadow-sm"
      >
        <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border', action.iconWrap]">
          <component :is="action.icon" class="h-5 w-5" :class="action.iconColor" />
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-xs sm:text-sm font-semibold text-[var(--color-text-primary)] group-hover:text-rose-500 transition-colors truncate">
            {{ action.label }}
          </p>
          <p class="text-[11px] text-[var(--color-text-muted)] truncate">
            {{ action.sub }}
          </p>
        </div>
        <ChevronRightIcon class="h-4 w-4 text-[var(--color-text-muted)] transition-transform group-hover:translate-x-1 shrink-0" />
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import {
  UserGroupIcon,
  ClipboardDocumentListIcon,
  FlagIcon,
  HeartIcon,
  BugAntIcon,
  ArchiveBoxIcon,
  ShieldCheckIcon,
  PresentationChartLineIcon,
  Cog6ToothIcon,
  ChevronRightIcon,
} from '@heroicons/vue/24/outline';

const quickActions = [
  {
    label: 'User Directory',
    sub: 'Manage all accounts across roles',
    to: '/superadmin/users',
    icon: UserGroupIcon,
    iconColor: 'text-rose-500',
    iconWrap: 'bg-rose-500/10 border-rose-500/20',
  },
  {
    label: 'Audit Trail',
    sub: 'Security and administrative events',
    to: '/superadmin/audit-logs',
    icon: ClipboardDocumentListIcon,
    iconColor: 'text-amber-500',
    iconWrap: 'bg-amber-500/10 border-amber-500/20',
  },
  {
    label: 'Feature Toggles',
    sub: 'Runtime platform feature switches',
    to: '/superadmin/feature-flags',
    icon: FlagIcon,
    iconColor: 'text-purple-500',
    iconWrap: 'bg-purple-500/10 border-purple-500/20',
  },
  {
    label: 'Health Observability',
    sub: 'Real-time telemetry and diagnostics',
    to: '/superadmin/health',
    icon: HeartIcon,
    iconColor: 'text-emerald-500',
    iconWrap: 'bg-emerald-500/10 border-emerald-500/20',
  },
  {
    label: 'Database Backups',
    sub: 'Snapshots and point-in-time recovery',
    to: '/superadmin/backups',
    icon: ArchiveBoxIcon,
    iconColor: 'text-indigo-500',
    iconWrap: 'bg-indigo-500/10 border-indigo-500/20',
  },
  {
    label: 'Global Settings',
    sub: 'Core platform parameters & config',
    to: '/superadmin/settings',
    icon: Cog6ToothIcon,
    iconColor: 'text-sky-500',
    iconWrap: 'bg-sky-500/10 border-sky-500/20',
  },
];
</script>

