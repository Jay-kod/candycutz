<template>
  <div class="space-y-3">
    <DataTable
      :columns="tableColumns"
      :items="formattedAppointments"
      :loading="loading"
      title="Recent Appointments"
      subtitle="Live queue of latest customer appointments and walk-in visits"
      empty-message="No recent appointment activity recorded"
      empty-subtext="New bookings and walk-ins will appear here in real time."
    >
      <template #header>
        <RouterLink
          to="/admin/appointments"
          class="inline-flex items-center gap-1.5 text-xs font-semibold text-admin hover:text-admin/80 transition-colors"
        >
          <span>View All</span>
          <ArrowRightIcon class="h-3.5 w-3.5" />
        </RouterLink>
      </template>

      <!-- Client Column -->
      <template #cell(client)="{ item }">
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-admin/25 to-admin/5 border border-admin/20 flex items-center justify-center shrink-0 text-admin font-bold text-xs shadow-sm">
            {{ getInitials(item.client_name) }}
          </div>
          <div class="min-w-0">
            <p class="font-semibold text-xs sm:text-sm text-[var(--color-text-primary)] truncate">
              {{ item.client_name || 'Walk-In Client' }}
            </p>
            <p class="text-[11px] text-[var(--color-text-muted)] truncate">
              {{ item.customer?.name ? 'Registered Client' : 'In-Store Walk-In' }}
            </p>
          </div>
        </div>
      </template>

      <!-- Service Column -->
      <template #cell(service)="{ item }">
        <div class="min-w-0">
          <p class="font-medium text-xs text-[var(--color-text-primary)] truncate">
            {{ item.service?.name || 'General Grooming' }}
          </p>
          <p class="text-[11px] text-[var(--color-text-muted)] flex items-center gap-1 mt-0.5 truncate">
            <span>Barber:</span>
            <span class="font-semibold text-admin">{{ item.barber?.name || 'Assigned Staff' }}</span>
          </p>
        </div>
      </template>

      <!-- Schedule Column -->
      <template #cell(schedule)="{ item }">
        <div class="text-xs">
          <p class="font-mono font-medium text-[var(--color-text-primary)] tabular-nums">
            {{ item.appointment_time || '--:--' }}
          </p>
          <p class="text-[11px] text-[var(--color-text-muted)] mt-0.5">
            {{ formatDate(item.appointment_date) }}
          </p>
        </div>
      </template>

      <!-- Amount Column -->
      <template #cell(amount)="{ item }">
        <span class="font-mono font-bold text-xs text-emerald-600 dark:text-emerald-400">
          ₦{{ formatCurrency(item.total_price || item.price || 0) }}
        </span>
      </template>

      <!-- Status Column -->
      <template #cell(status)="{ item }">
        <StatusBadge :status="item.status || 'pending'" size="sm" />
      </template>

      <!-- Actions Column -->
      <template #cell(actions)="{ item }">
        <div class="flex items-center justify-end">
          <ActionDropdown
            :items="getActionItems(item)"
            align="right"
            @select="onActionSelect"
          />
        </div>
      </template>
    </DataTable>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter, RouterLink } from 'vue-router';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import DataTable from '@/shared/ui/DataTable.vue';
import StatusBadge from '@/shared/ui/StatusBadge.vue';
import ActionDropdown from '@/shared/ui/ActionDropdown.vue';

const props = defineProps({
  appointments: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const router = useRouter();

const tableColumns = [
  { key: 'client', label: 'Client', sortable: false },
  { key: 'service', label: 'Service & Barber', sortable: false },
  { key: 'schedule', label: 'Time & Date', sortable: false },
  { key: 'amount', label: 'Price', align: 'right', sortable: false },
  { key: 'status', label: 'Status', align: 'center', sortable: false },
  { key: 'actions', label: '', align: 'right', sortable: false },
];

const formattedAppointments = computed(() => {
  return props.appointments || [];
});

const getInitials = (name) => {
  if (!name) return 'WI';
  return name
    .split(' ')
    .filter(Boolean)
    .map((n) => n[0])
    .join('')
    .substring(0, 2)
    .toUpperCase();
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'Today';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(val || 0));
};

const getActionItems = (item) => [
  {
    label: 'View Appointments',
    action: () => router.push('/admin/appointments'),
  },
  {
    label: 'Create Walk-In',
    action: () => router.push('/admin/walk-in'),
  },
  {
    divider: true,
  },
  {
    label: 'Customer Profiles',
    action: () => router.push('/admin/customers'),
  },
];

const onActionSelect = (item) => {
  if (item?.action) {
    item.action();
  }
};
</script>

