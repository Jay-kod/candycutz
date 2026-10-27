<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 backdrop-blur-xl shadow-2xl min-h-[400px]">
    <!-- Loading State -->
    <div v-if="loading" class="p-16 text-center">
      <div class="inline-flex items-center gap-3 text-white/50">
        <div class="h-5 w-5 rounded-full border-2 border-white/20 border-t-[#FF6700] animate-spin"></div>
        <span class="text-xs font-semibold">Loading verification registry...</span>
      </div>
    </div>

    <template v-else>
      <div v-if="verifications.length > 0" class="divide-y divide-white/[0.04]">
        <!-- Table Column Headers -->
        <div class="hidden md:grid grid-cols-[1.5fr_1.1fr_1.5fr_1.2fr_auto] gap-6 px-6 py-3.5 text-[10px] uppercase tracking-[0.2em] text-white/40 font-bold bg-[#0B0F13]/60 border-b border-white/[0.06]">
          <span>Customer</span>
          <span>Barber</span>
          <span>Service Schedule</span>
          <span>Verification Code</span>
          <span class="text-right">Action</span>
        </div>

        <!-- Table Rows -->
        <div
          v-for="(item, idx) in verifications"
          :key="item.id || idx"
          class="grid grid-cols-1 md:grid-cols-[1.5fr_1.1fr_1.5fr_1.2fr_auto] items-start md:items-center gap-4 md:gap-6 px-5 py-4 transition-all duration-200 hover:bg-white/[0.02] group"
        >
          <!-- Customer -->
          <div class="flex items-center gap-3">
            <div
              class="h-10 w-10 rounded-xl overflow-hidden shrink-0 border border-white/10 flex items-center justify-center shadow-lg"
              :class="item.customer_avatar ? '' : 'bg-gradient-to-br from-[#FF6700]/20 to-amber-500/20'"
            >
              <img v-if="item.customer_avatar" :src="item.customer_avatar" class="h-full w-full object-cover" />
              <span v-else class="text-[#FF6700] font-bold text-xs">{{ initials(item.customer_name) }}</span>
            </div>
            <div class="min-w-0">
              <p class="truncate text-xs sm:text-sm font-semibold text-white group-hover:text-[#FF6700] transition-colors">
                {{ item.customer_name || 'Walk-in Client' }}
              </p>
              <p class="text-[10px] text-white/30 md:hidden font-mono mt-0.5">
                {{ item.verification_code }}
              </p>
            </div>
          </div>

          <!-- Barber -->
          <div class="hidden md:flex items-center gap-2 min-w-0">
            <div class="h-6 w-6 rounded-lg bg-white/5 flex items-center justify-center border border-white/10 shrink-0">
              <span class="text-[9px] font-bold text-white/60">{{ initials(item.barber_name) }}</span>
            </div>
            <span class="truncate text-xs text-white/70 font-medium">{{ item.barber_name || 'Unassigned' }}</span>
          </div>

          <!-- Details -->
          <div class="min-w-0">
            <p class="truncate text-xs sm:text-sm font-semibold text-white mb-0.5">{{ item.service_name || 'Custom Cut' }}</p>
            <div class="flex items-center gap-1.5 text-white/40 text-[11px]">
              <CalendarIcon class="h-3 w-3 shrink-0" />
              <span>{{ formatDate(item.appointment_date) }} at {{ item.appointment_time }}</span>
            </div>
          </div>

          <!-- Code Badge -->
          <div>
            <div
              class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border font-mono text-xs sm:text-sm tracking-wider font-bold transition-all duration-200"
              :class="getCodeStyle(item.status, item.appointment_date)"
            >
              <CheckBadgeIcon v-if="item.status === 'completed'" class="h-4 w-4 shrink-0" />
              <XCircleIcon v-else-if="item.status === 'cancelled'" class="h-4 w-4 shrink-0" />
              <ClockIcon v-else-if="isExpired(item.appointment_date) && item.status === 'confirmed'" class="h-4 w-4 shrink-0" />
              <QrCodeIcon v-else class="h-4 w-4 shrink-0" />
              <span>{{ item.verification_code }}</span>
            </div>
          </div>

          <!-- Action Trigger -->
          <div class="flex justify-start md:justify-end">
            <button
              v-if="item.status === 'confirmed' && !isExpired(item.appointment_date)"
              type="button"
              @click="$emit('verify', item)"
              class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-emerald-500/10 px-3.5 py-1.5 text-xs font-bold text-emerald-400 border border-emerald-500/30 transition-all hover:bg-emerald-500/20 hover:scale-105 active:scale-95 shadow-sm hover:shadow-emerald-500/20"
            >
              <ShieldCheckIcon class="h-3.5 w-3.5" />
              <span>Verify</span>
            </button>
            <span
              v-else-if="item.status === 'completed'"
              class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-[11px] font-bold text-emerald-400"
            >
              <CheckBadgeIcon class="h-3 w-3" />
              <span>Verified</span>
            </span>
            <span
              v-else-if="item.status === 'cancelled'"
              class="inline-flex items-center gap-1.5 rounded-lg border border-rose-500/20 bg-rose-500/10 px-2.5 py-1 text-[11px] font-bold text-rose-400"
            >
              <XCircleIcon class="h-3 w-3" />
              <span>Cancelled</span>
            </span>
            <span
              v-else
              class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white/40"
            >
              {{ item.status }}
            </span>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="p-16 text-center">
        <div class="inline-flex items-center justify-center h-16 w-16 rounded-2xl bg-white/[0.03] border border-white/[0.08] mb-4">
          <ShieldCheckIcon class="h-8 w-8 text-white/20" />
        </div>
        <h3 class="text-sm sm:text-base font-bold text-white mb-1">No verification codes found</h3>
        <p class="text-xs text-white/40 max-w-sm mx-auto">
          No appointments matched your active filter or search criteria. Try modifying your filter tabs.
        </p>
      </div>
    </template>
  </div>
</template>

<script setup>
import {
  CalendarIcon,
  CheckBadgeIcon,
  ClockIcon,
  QrCodeIcon,
  ShieldCheckIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline';

defineProps({
  verifications: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
});

defineEmits(['verify']);

function isExpired(dateStr) {
  if (!dateStr) return false;
  return new Date(dateStr) < new Date(new Date().setHours(0, 0, 0, 0));
}

function getCodeStyle(status, dateStr) {
  if (status === 'completed') {
    return 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400';
  }
  if (status === 'cancelled') {
    return 'bg-rose-500/10 border-rose-500/30 text-rose-400 line-through opacity-70';
  }
  if (status === 'confirmed' && isExpired(dateStr)) {
    return 'bg-rose-500/10 border-rose-500/30 text-rose-400 opacity-70 line-through';
  }
  return 'bg-amber-500/10 border-amber-500/30 text-amber-400';
}

function initials(name) {
  if (!name) return 'CC';
  const parts = String(name).trim().split(/\s+/);
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return String(name).slice(0, 2).toUpperCase();
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}
</script>
