<template>
  <div class="space-y-3">
    <DataTable
      :columns="tableColumns"
      :items="formattedLogs"
      :loading="loading"
      title="Recent Audit Activity"
      subtitle="Immutable event log of administrative and system operations"
      empty-message="No audit logs recorded yet"
      empty-subtext="Administrative events will stream into this view automatically."
    >
      <template #header>
        <RouterLink
          to="/superadmin/audit-logs"
          class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-500 hover:text-rose-400 transition-colors"
        >
          <span>View Full Trail</span>
          <ArrowRightIcon class="h-3.5 w-3.5" />
        </RouterLink>
      </template>

      <!-- User Column -->
      <template #cell(user)="{ item }">
        <div class="flex items-center gap-2.5">
          <div class="flex h-7 w-7 items-center justify-center rounded-xl bg-rose-500/15 border border-rose-500/25 text-[10px] font-bold text-rose-500 shrink-0">
            {{ getInitials(item.user?.name) }}
          </div>
          <span class="font-medium text-xs text-[var(--color-text-primary)] truncate">
            {{ item.user?.name || 'System Service' }}
          </span>
        </div>
      </template>

      <!-- Action Column -->
      <template #cell(action)="{ item }">
        <StatusBadge :status="getBadgeStatus(item.action)" :label="item.action" size="sm" />
      </template>

      <!-- Module Column -->
      <template #cell(module)="{ item }">
        <span class="text-xs text-[var(--color-text-secondary)] font-mono">
          {{ item.module || item.auditable_type || '—' }}
        </span>
      </template>

      <!-- IP Address Column -->
      <template #cell(ip_address)="{ item }">
        <span class="text-[11px] font-mono text-[var(--color-text-muted)]">
          {{ item.ip_address || '127.0.0.1' }}
        </span>
      </template>

      <!-- Time Column -->
      <template #cell(time)="{ item }">
        <span class="text-xs font-mono text-[var(--color-text-muted)] tabular-nums">
          {{ formatTime(item.created_at) }}
        </span>
      </template>
    </DataTable>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import DataTable from '@/shared/ui/DataTable.vue';
import StatusBadge from '@/shared/ui/StatusBadge.vue';

const props = defineProps({
  logs: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const tableColumns = [
  { key: 'user', label: 'Operator' },
  { key: 'action', label: 'Action Event' },
  { key: 'module', label: 'Target Module' },
  { key: 'ip_address', label: 'Origin IP' },
  { key: 'time', label: 'Timestamp', align: 'right' },
];

const formattedLogs = computed(() => props.logs || []);

const getInitials = (name) => {
  if (!name) return 'SYS';
  return name
    .split(' ')
    .filter(Boolean)
    .map((n) => n[0])
    .join('')
    .substring(0, 2)
    .toUpperCase();
};

const getBadgeStatus = (action) => {
  if (!action) return 'info';
  const a = action.toLowerCase();
  if (a.includes('delete') || a.includes('remove') || a.includes('destroy')) return 'danger';
  if (a.includes('create') || a.includes('add') || a.includes('store')) return 'completed';
  if (a.includes('update') || a.includes('edit')) return 'pending';
  return 'super_admin';
};

const formatTime = (dateStr) => {
  if (!dateStr) return '—';
  const d = new Date(dateStr);
  const diffMs = Date.now() - d.getTime();
  const diffMin = Math.floor(diffMs / 60000);
  if (diffMin < 1) return 'Just now';
  if (diffMin < 60) return `${diffMin}m ago`;
  const diffH = Math.floor(diffMin / 60);
  if (diffH < 24) return `${diffH}h ago`;
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};
</script>

