<template>
  <SuperAdminLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Command Banner -->
      <div :class="['rounded-2xl border p-8', bannerClass]">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <p :class="['text-sm uppercase tracking-[0.3em] font-semibold', mutedText]">Super Admin Command Center</p>
            <h1 :class="['mt-2 font-display text-4xl font-bold', headingText]">System Overview</h1>
            <p :class="['mt-1 text-sm', mutedText]">{{ currentDate }}</p>
          </div>
          <div class="flex items-center gap-4">
            <!-- Live Clock -->
            <div :class="['flex items-center gap-2 rounded-xl border px-4 py-2.5 font-mono text-lg tabular-nums', clockClass]">
              <ClockIcon class="h-5 w-5 opacity-60" />
              {{ currentTime }}
            </div>
            <!-- Refresh Button -->
            <button
              @click="loadData"
              :disabled="isRefreshing"
              :class="['flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition-all duration-200', refreshBtnClass]"
            >
              <ArrowPathIcon :class="['h-4 w-4', isRefreshing ? 'animate-spin' : '']" />
              {{ isRefreshing ? 'Loading…' : 'Refresh' }}
            </button>
            <!-- System Status -->
            <div :class="['flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-bold uppercase tracking-wider', statusClass]">
              <span class="h-2 w-2 rounded-full bg-current animate-pulse"></span>
              Online
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="grid gap-5 grid-cols-2 lg:grid-cols-5">
        <div v-for="i in 5" :key="i" :class="['h-32 rounded-xl animate-pulse border', skeletonClass]"></div>
      </div>

      <template v-else>
        <!-- Executive KPI Stat Cards -->
        <div class="grid gap-4 grid-cols-2 lg:grid-cols-5">
          <article
            v-for="card in statsCards"
            :key="card.label"
            :class="['group relative rounded-2xl border p-5 transition-all duration-200', cardClass]"
          >
            <div class="flex items-center justify-between">
              <div :class="['flex h-10 w-10 items-center justify-center rounded-xl border', card.iconWrap]">
                <component :is="card.icon" class="h-5 w-5" :class="card.iconColor" />
              </div>
              <div v-if="card.badge" :class="['text-[10px] font-bold px-2 py-0.5 rounded-full border uppercase tracking-wider', card.badgeClass]">
                {{ card.badge }}
              </div>
            </div>
            <p :class="['mt-3 font-display text-3xl font-bold tabular-nums', valueText]">{{ card.value }}</p>
            <p :class="['mt-1 text-sm', mutedText]">{{ card.label }}</p>
          </article>
        </div>

        <!-- Analytics Grid -->
        <div class="grid gap-6 lg:grid-cols-3">
          <!-- Left: User Role Distribution -->
          <div :class="['lg:col-span-2 rounded-2xl border p-6', cardClass]">
            <h2 :class="['font-display text-xl font-bold', headingText]">User Role Distribution</h2>
            <div class="mt-6 space-y-4">
              <div v-for="bar in roleBars" :key="bar.label" class="space-y-1.5">
                <div class="flex items-center justify-between text-sm">
                  <span :class="mutedText">{{ bar.label }}</span>
                  <span :class="['font-semibold tabular-nums', valueText]">{{ bar.value }} ({{ bar.percent }}%)</span>
                </div>
                <div :class="['h-2.5 rounded-full overflow-hidden', barBgClass]">
                  <div
                    :class="['h-full rounded-full transition-all duration-700', bar.barColor]"
                    :style="{ width: bar.percent + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Quick Governance Controls -->
          <div class="space-y-4">
            <h2 :class="['font-display text-xl font-bold', headingText]">Quick Actions</h2>
            <RouterLink
              v-for="action in quickActions"
              :key="action.label"
              :to="action.to"
              :class="['group flex items-center gap-4 rounded-2xl border p-4 transition-all duration-200', cardClass, cardHover]"
            >
              <div :class="['flex h-11 w-11 items-center justify-center rounded-xl border', action.iconWrap]">
                <component :is="action.icon" class="h-5 w-5" :class="action.iconColor" />
              </div>
              <div class="min-w-0 flex-1">
                <p :class="['text-sm font-semibold', valueText]">{{ action.label }}</p>
                <p :class="['text-xs', mutedText]">{{ action.sub }}</p>
              </div>
              <ChevronRightIcon :class="['h-4 w-4 transition-transform group-hover:translate-x-1', mutedText]" />
            </RouterLink>
          </div>
        </div>

        <!-- Security & Audit Activity Stream -->
        <div :class="['rounded-2xl border p-6', cardClass]">
          <div class="flex items-center justify-between mb-6">
            <h2 :class="['font-display text-xl font-bold', headingText]">Recent Audit Activity</h2>
            <RouterLink to="/superadmin/audit-logs" :class="['text-sm font-semibold transition-colors', linkText]">
              View All →
            </RouterLink>
          </div>
          <div v-if="!dashboard.recent_logs?.length" :class="['text-center py-10 text-sm', mutedText]">
            No recent audit activity.
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr :class="tableHeaderClass">
                  <th class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider">User</th>
                  <th class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider">Action</th>
                  <th class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider hidden md:table-cell">Module</th>
                  <th class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider hidden lg:table-cell">IP Address</th>
                  <th class="text-right py-3 px-4 text-xs font-bold uppercase tracking-wider">Time</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="log in dashboard.recent_logs"
                  :key="log.id"
                  :class="['border-t transition-colors', tableRowClass]"
                >
                  <td class="py-3 px-4">
                    <div class="flex items-center gap-2">
                      <div :class="['flex h-7 w-7 items-center justify-center rounded-full text-[10px] font-bold', avatarClass]">
                        {{ getInitials(log.user?.name) }}
                      </div>
                      <span :class="['font-medium', valueText]">{{ log.user?.name || 'System' }}</span>
                    </div>
                  </td>
                  <td class="py-3 px-4">
                    <span :class="['inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold border', getActionPillClass(log.action)]">
                      {{ log.action }}
                    </span>
                  </td>
                  <td :class="['py-3 px-4 hidden md:table-cell', mutedText]">{{ log.module || '—' }}</td>
                  <td :class="['py-3 px-4 hidden lg:table-cell font-mono text-xs', mutedText]">{{ log.ip_address || '—' }}</td>
                  <td :class="['py-3 px-4 text-right tabular-nums', mutedText]">{{ formatTime(log.created_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </section>
  </SuperAdminLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useDark } from '@vueuse/core';
import SuperAdminLayout from '@/portals/superadmin/layouts/SuperAdminLayout.vue';
import { superadminApi } from '@/shared/api/old_superadminApi';
import {
  UsersIcon,
  UserGroupIcon,
  ShieldCheckIcon,
  ScissorsIcon,
  UserPlusIcon,
  ClockIcon,
  ArrowPathIcon,
  ChevronRightIcon,
  ClipboardDocumentListIcon,
  Cog6ToothIcon,
  PresentationChartLineIcon,
  FlagIcon,
  HeartIcon,
  BugAntIcon,
  ArchiveBoxIcon,
} from '@heroicons/vue/24/outline';

const isDark = useDark({
  selector: 'html',
  attribute: 'data-theme',
  valueDark: 'dark',
  valueLight: 'light',
});

const dashboard = ref({ stats: {}, recent_logs: [] });
const loading = ref(true);
const isRefreshing = ref(false);

const currentTime = ref('');
const currentDate = ref('');
let timeInterval = null;

const updateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
};

const loadData = async () => {
  isRefreshing.value = true;
  try {
    const response = await superadminApi.dashboard();
    dashboard.value = response.data.data;
  } catch {
    // Silently handle — data stays as-is
  } finally {
    loading.value = false;
    isRefreshing.value = false;
  }
};

onMounted(() => {
  updateTime();
  timeInterval = setInterval(updateTime, 1000);
  loadData();
});
onUnmounted(() => {
  if (timeInterval) clearInterval(timeInterval);
});

// Format helpers
const formatNumber = (val) => new Intl.NumberFormat('en-NG').format(Number(val || 0));

const getInitials = (name) => {
  if (!name) return 'S';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const formatTime = (dateStr) => {
  if (!dateStr) return '—';
  const d = new Date(dateStr);
  const now = new Date();
  const diffMs = now - d;
  const diffMin = Math.floor(diffMs / 60000);
  if (diffMin < 1) return 'Just now';
  if (diffMin < 60) return `${diffMin}m ago`;
  const diffH = Math.floor(diffMin / 60);
  if (diffH < 24) return `${diffH}h ago`;
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

// ─── Theme-Aware Computed Classes ─────────────────────────
// Night Mode: Red + Black | Light Mode: Green + White

const bannerClass = computed(() => isDark.value
  ? 'bg-gradient-to-br from-red-950/60 via-black to-black border-red-500/20'
  : 'bg-gradient-to-br from-emerald-50 via-white to-white border-emerald-500/20'
);

const headingText = computed(() => isDark.value ? 'text-red-400' : 'text-emerald-700');
const valueText = computed(() => isDark.value ? 'text-white' : 'text-slate-900');
const mutedText = computed(() => isDark.value ? 'text-white/50' : 'text-slate-500');
const linkText = computed(() => isDark.value ? 'text-red-400 hover:text-red-300' : 'text-emerald-600 hover:text-emerald-700');

const clockClass = computed(() => isDark.value
  ? 'bg-black/50 border-red-500/20 text-red-400'
  : 'bg-emerald-50 border-emerald-500/20 text-emerald-700'
);

const refreshBtnClass = computed(() => isDark.value
  ? 'bg-red-500/10 border-red-500/30 text-red-400 hover:bg-red-500/20'
  : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-700 hover:bg-emerald-500/20'
);

const statusClass = computed(() => isDark.value
  ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400'
  : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-600'
);

const cardClass = computed(() => isDark.value
  ? 'bg-[#0a0202]/80 border-red-500/10'
  : 'bg-white border-emerald-500/10 shadow-sm'
);

const cardHover = computed(() => isDark.value
  ? 'hover:border-red-500/30 hover:bg-red-500/[0.04]'
  : 'hover:border-emerald-500/30 hover:bg-emerald-50/50'
);

const skeletonClass = computed(() => isDark.value
  ? 'bg-red-500/5 border-red-500/10'
  : 'bg-emerald-500/5 border-emerald-500/10'
);

const barBgClass = computed(() => isDark.value ? 'bg-white/10' : 'bg-slate-200');

const tableHeaderClass = computed(() => isDark.value
  ? 'text-red-400/70'
  : 'text-emerald-700/70'
);

const tableRowClass = computed(() => isDark.value
  ? 'border-white/[0.05] hover:bg-red-500/[0.04]'
  : 'border-slate-100 hover:bg-emerald-50/50'
);

const avatarClass = computed(() => isDark.value
  ? 'bg-red-500/20 text-red-400'
  : 'bg-emerald-500/15 text-emerald-700'
);

const getActionPillClass = (action) => {
  if (!action) return isDark.value ? 'bg-white/5 text-white/60 border-white/10' : 'bg-slate-100 text-slate-600 border-slate-200';
  const a = action.toLowerCase();
  if (a.includes('create') || a.includes('add') || a.includes('register')) {
    return isDark.value ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-emerald-50 text-emerald-700 border-emerald-200';
  }
  if (a.includes('delete') || a.includes('remove') || a.includes('destroy')) {
    return isDark.value ? 'bg-red-500/10 text-red-400 border-red-500/20' : 'bg-red-50 text-red-700 border-red-200';
  }
  if (a.includes('update') || a.includes('edit') || a.includes('change')) {
    return isDark.value ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-amber-50 text-amber-700 border-amber-200';
  }
  if (a.includes('login') || a.includes('auth') || a.includes('verify')) {
    return isDark.value ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 'bg-blue-50 text-blue-700 border-blue-200';
  }
  return isDark.value ? 'bg-white/5 text-white/60 border-white/10' : 'bg-slate-100 text-slate-600 border-slate-200';
};

// ─── Data Cards ───────────────────────────────────────────

const statsCards = computed(() => {
  const stats = dashboard.value.stats || {};
  const dark = isDark.value;
  return [
    {
      label: 'Total Users',
      value: formatNumber(stats.total_users),
      icon: UsersIcon,
      iconColor: dark ? 'text-red-400' : 'text-emerald-600',
      iconWrap: dark ? 'bg-red-500/10 border-red-500/20' : 'bg-emerald-500/10 border-emerald-500/20',
      badge: 'All',
      badgeClass: dark ? 'bg-red-500/10 text-red-400 border-red-500/20' : 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20',
    },
    {
      label: 'Active Users',
      value: formatNumber(stats.active_users),
      icon: ShieldCheckIcon,
      iconColor: dark ? 'text-emerald-400' : 'text-emerald-600',
      iconWrap: dark ? 'bg-emerald-500/10 border-emerald-500/20' : 'bg-emerald-500/10 border-emerald-500/20',
      badge: 'Live',
      badgeClass: dark ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20',
    },
    {
      label: 'Admins',
      value: formatNumber(stats.total_admins),
      icon: ShieldCheckIcon,
      iconColor: dark ? 'text-red-400' : 'text-emerald-700',
      iconWrap: dark ? 'bg-red-500/10 border-red-500/20' : 'bg-emerald-500/10 border-emerald-500/20',
    },
    {
      label: 'Barbers',
      value: formatNumber(stats.barbers),
      icon: ScissorsIcon,
      iconColor: dark ? 'text-amber-400' : 'text-amber-600',
      iconWrap: dark ? 'bg-amber-500/10 border-amber-500/20' : 'bg-amber-500/10 border-amber-500/20',
    },
    {
      label: 'Customers',
      value: formatNumber(stats.customers),
      icon: UserGroupIcon,
      iconColor: dark ? 'text-blue-400' : 'text-blue-600',
      iconWrap: dark ? 'bg-blue-500/10 border-blue-500/20' : 'bg-blue-500/10 border-blue-500/20',
    },
  ];
});

const roleBars = computed(() => {
  const stats = dashboard.value.stats || {};
  const total = Number(stats.total_users || 1);
  const dark = isDark.value;
  return [
    {
      label: 'Admins & Super Admins',
      value: formatNumber(stats.total_admins),
      percent: Math.round((Number(stats.total_admins || 0) / total) * 100),
      barColor: dark ? 'bg-gradient-to-r from-red-500 to-red-600' : 'bg-gradient-to-r from-emerald-500 to-emerald-600',
    },
    {
      label: 'Barbers',
      value: formatNumber(stats.barbers),
      percent: Math.round((Number(stats.barbers || 0) / total) * 100),
      barColor: dark ? 'bg-gradient-to-r from-amber-500 to-amber-600' : 'bg-gradient-to-r from-amber-400 to-amber-500',
    },
    {
      label: 'Customers',
      value: formatNumber(stats.customers),
      percent: Math.round((Number(stats.customers || 0) / total) * 100),
      barColor: dark ? 'bg-gradient-to-r from-blue-500 to-blue-600' : 'bg-gradient-to-r from-blue-400 to-blue-500',
    },
  ];
});

const quickActions = computed(() => {
  const dark = isDark.value;
  return [
    {
      label: 'User Directory',
      sub: 'Manage all platform users',
      to: '/superadmin/users',
      icon: UserGroupIcon,
      iconColor: dark ? 'text-red-400' : 'text-emerald-600',
      iconWrap: dark ? 'bg-red-500/10 border-red-500/20' : 'bg-emerald-500/10 border-emerald-500/20',
    },
    {
      label: 'Audit Logs',
      sub: 'View full system activity trail',
      to: '/superadmin/audit-logs',
      icon: ClipboardDocumentListIcon,
      iconColor: dark ? 'text-amber-400' : 'text-amber-600',
      iconWrap: dark ? 'bg-amber-500/10 border-amber-500/20' : 'bg-amber-500/10 border-amber-500/20',
    },
    {
      label: 'Feature Flags',
      sub: 'Toggle platform features on/off',
      to: '/superadmin/feature-flags',
      icon: FlagIcon,
      iconColor: dark ? 'text-purple-400' : 'text-purple-600',
      iconWrap: dark ? 'bg-purple-500/10 border-purple-500/20' : 'bg-purple-500/10 border-purple-500/20',
    },
    {
      label: 'Health Checker',
      sub: 'System health & diagnostic test runner',
      to: '/superadmin/health',
      icon: HeartIcon,
      iconColor: dark ? 'text-rose-400' : 'text-rose-600',
      iconWrap: dark ? 'bg-rose-500/10 border-rose-500/20' : 'bg-rose-500/10 border-rose-500/20',
    },
    {
      label: 'System Errors',
      sub: 'Error observability & trace inspect',
      to: '/superadmin/system-errors',
      icon: BugAntIcon,
      iconColor: dark ? 'text-orange-400' : 'text-orange-600',
      iconWrap: dark ? 'bg-orange-500/10 border-orange-500/20' : 'bg-orange-500/10 border-orange-500/20',
    },
    {
      label: 'Backup & Restore',
      sub: 'Database snapshot and recovery',
      to: '/superadmin/backups',
      icon: ArchiveBoxIcon,
      iconColor: dark ? 'text-indigo-400' : 'text-indigo-600',
      iconWrap: dark ? 'bg-indigo-500/10 border-indigo-500/20' : 'bg-indigo-500/10 border-indigo-500/20',
    },
    {
      label: 'System Logs',
      sub: 'Real-time system monitoring',
      to: '/superadmin/system-logs',
      icon: ShieldCheckIcon,
      iconColor: dark ? 'text-cyan-400' : 'text-cyan-600',
      iconWrap: dark ? 'bg-cyan-500/10 border-cyan-500/20' : 'bg-cyan-500/10 border-cyan-500/20',
    },
    {
      label: 'Analytics',
      sub: 'Platform performance insights',
      to: '/superadmin/analytics',
      icon: PresentationChartLineIcon,
      iconColor: dark ? 'text-emerald-400' : 'text-emerald-600',
      iconWrap: dark ? 'bg-emerald-500/10 border-emerald-500/20' : 'bg-emerald-500/10 border-emerald-500/20',
    },
    {
      label: 'System Settings',
      sub: 'Configure platform behavior',
      to: '/superadmin/settings',
      icon: Cog6ToothIcon,
      iconColor: dark ? 'text-blue-400' : 'text-blue-600',
      iconWrap: dark ? 'bg-blue-500/10 border-blue-500/20' : 'bg-blue-500/10 border-blue-500/20',
    },
  ];
});
</script>
