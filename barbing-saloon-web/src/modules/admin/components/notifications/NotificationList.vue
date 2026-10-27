<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 backdrop-blur-xl shadow-xl overflow-hidden min-h-[400px]">
    <!-- Empty State: No Notifications at all -->
    <div v-if="totalCount === 0" class="p-20 text-center">
      <div class="h-20 w-20 rounded-2xl bg-white/[0.03] border border-white/10 flex items-center justify-center mx-auto mb-4 text-white/30">
        <BellIcon class="h-9 w-9" />
      </div>
      <h3 class="text-base font-bold text-white mb-1">You're all caught up!</h3>
      <p class="text-xs text-white/40 max-w-sm mx-auto">
        When new system alerts, customer bookings, or security events occur, they will stream here.
      </p>
    </div>

    <!-- Empty State: Filters returned 0 results -->
    <div v-else-if="groupedNotifications.length === 0" class="p-20 text-center">
      <div class="h-20 w-20 rounded-2xl bg-white/[0.03] border border-white/10 flex items-center justify-center mx-auto mb-4 text-white/30">
        <FunnelIcon class="h-9 w-9" />
      </div>
      <h3 class="text-base font-bold text-white mb-1">No matching notifications</h3>
      <p class="text-xs text-white/40 max-w-sm mx-auto mb-4">
        Try modifying your filters or search keywords to view other dispatches.
      </p>
      <button
        type="button"
        @click="$emit('clear-filters')"
        class="inline-flex items-center gap-1.5 rounded-xl border border-[#FF6700]/30 bg-[#FF6700]/10 px-4 py-2 text-xs font-bold text-[#FF6700] hover:bg-[#FF6700]/20 transition-colors"
      >
        <span>Clear Filters</span>
      </button>
    </div>

    <!-- Grouped Notifications Stream -->
    <div v-else class="divide-y divide-white/[0.06]">
      <div v-for="group in groupedNotifications" :key="group.label">
        <!-- Date Header Sticky -->
        <div class="sticky top-0 z-10 px-6 py-3 bg-[#0B0F13]/90 backdrop-blur-md border-b border-white/[0.06] flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="h-1.5 w-1.5 rounded-full bg-[#FF6700]"></span>
            <span class="text-[11px] font-bold uppercase tracking-widest text-[#FF6700]">
              {{ group.label }}
            </span>
          </div>
          <span class="text-[10px] font-mono text-white/40 font-semibold uppercase">
            {{ group.items.length }} {{ group.items.length === 1 ? 'dispatch' : 'dispatches' }}
          </span>
        </div>

        <!-- Notification Rows -->
        <div class="divide-y divide-white/[0.03]">
          <div
            v-for="notif in group.items"
            :key="notif.id"
            class="group relative flex items-start justify-between gap-4 p-4 sm:p-5 transition-colors duration-200 hover:bg-white/[0.02]"
            :class="{ 'bg-[#FF6700]/[0.02]': !notif.is_read }"
          >
            <!-- Left Info & Category Icon -->
            <div class="flex items-start gap-3.5 min-w-0">
              <!-- Type Icon -->
              <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border transition-transform group-hover:scale-105"
                :class="getTypeIconClass(notif.type)"
              >
                <component :is="getTypeIcon(notif.type)" class="h-5 w-5" />
              </div>

              <!-- Content -->
              <div class="space-y-1 min-w-0">
                <div class="flex items-center gap-2">
                  <span
                    v-if="!notif.is_read"
                    class="h-2 w-2 rounded-full bg-[#FF6700] shrink-0"
                    title="Unread"
                  ></span>
                  <h4 class="text-xs sm:text-sm font-bold text-white truncate group-hover:text-[#FF6700] transition-colors">
                    {{ notif.title || 'Platform Notification' }}
                  </h4>
                  <span
                    v-if="notif.type"
                    class="rounded-md border border-white/10 bg-white/[0.03] px-1.5 py-0.5 text-[9px] uppercase font-mono text-white/40 shrink-0"
                  >
                    {{ notif.type }}
                  </span>
                </div>

                <p class="text-xs text-white/60 leading-relaxed break-words">
                  {{ notif.message }}
                </p>

                <p class="text-[10px] font-mono text-white/40 pt-0.5">
                  {{ formatTime(notif.created_at) }}
                </p>
              </div>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-2 shrink-0 opacity-80 group-hover:opacity-100 transition-opacity">
              <button
                v-if="!notif.is_read"
                type="button"
                @click="$emit('read', notif.id)"
                class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-1.5 text-emerald-400 hover:bg-emerald-500/20 hover:scale-105 transition-all"
                title="Mark as read"
              >
                <CheckIcon class="h-4 w-4" />
              </button>

              <button
                type="button"
                @click="$emit('delete', notif.id)"
                class="rounded-lg border border-white/10 bg-white/[0.04] p-1.5 text-white/40 hover:border-rose-500/30 hover:bg-rose-500/10 hover:text-rose-400 hover:scale-105 transition-all"
                title="Delete notification"
              >
                <TrashIcon class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  BellIcon,
  FunnelIcon,
  CalendarDaysIcon,
  BanknotesIcon,
  ShieldAlertIcon,
  CpuChipIcon,
  CheckIcon,
  TrashIcon
} from '@heroicons/vue/24/outline';

defineProps({
  groupedNotifications: {
    type: Array,
    required: true
  },
  totalCount: {
    type: Number,
    required: true
  }
});

defineEmits(['read', 'delete', 'clear-filters']);

const getTypeIcon = (type) => {
  const t = String(type || '').toLowerCase();
  if (t.includes('booking') || t.includes('appointment')) return CalendarDaysIcon;
  if (t.includes('payment')) return BanknotesIcon;
  if (t.includes('security') || t.includes('auth')) return ShieldAlertIcon;
  if (t.includes('system')) return CpuChipIcon;
  return BellIcon;
};

const getTypeIconClass = (type) => {
  const t = String(type || '').toLowerCase();
  if (t.includes('booking') || t.includes('appointment')) {
    return 'border-sky-500/20 bg-sky-500/10 text-sky-400';
  }
  if (t.includes('payment')) {
    return 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400';
  }
  if (t.includes('security') || t.includes('auth')) {
    return 'border-rose-500/20 bg-rose-500/10 text-rose-400';
  }
  if (t.includes('system')) {
    return 'border-amber-500/20 bg-amber-500/10 text-amber-400';
  }
  return 'border-[#FF6700]/20 bg-[#FF6700]/10 text-[#FF6700]';
};

const formatTime = (isoString) => {
  if (!isoString) return '';
  const d = new Date(isoString);
  return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + ' • ' + d.toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>
