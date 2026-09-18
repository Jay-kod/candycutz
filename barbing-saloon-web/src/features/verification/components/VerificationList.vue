<template>
  <div class="rounded-3xl border border-white/[0.05] bg-[#141414]/80 backdrop-blur-2xl shadow-2xl overflow-hidden min-h-[400px]">
    <!-- Loading State -->
    <div v-if="loading" class="p-16 text-center">
      <div class="inline-flex items-center gap-3 text-white/40">
        <div class="h-5 w-5 rounded-full border-2 border-white/20 border-t-admin animate-spin"></div>
        <span class="text-sm font-medium">Loading verification data...</span>
      </div>
    </div>
    
    <template v-else>
      <div v-if="verifications.length > 0" class="divide-y divide-white/[0.04]">
        <!-- Table Header -->
        <div class="hidden md:grid grid-cols-[1.5fr_1fr_1.5fr_1.2fr_auto] gap-6 px-8 py-4 text-[10px] uppercase tracking-[0.2em] text-white/30 font-bold bg-black/40 border-b border-white/[0.05]">
          <span>Customer</span>
          <span>Barber</span>
          <span>Service Details</span>
          <span>Verification Code</span>
          <span class="text-right">Action</span>
        </div>

        <!-- Rows -->
        <div 
          v-for="(item, idx) in verifications" 
          :key="item.id" 
          class="grid grid-cols-1 md:grid-cols-[1.5fr_1fr_1.5fr_1.2fr_auto] items-center gap-4 md:gap-6 px-6 md:px-8 py-5 transition-all duration-300 hover:bg-white/[0.025] group"
          :class="{ 'animate-row-in': true }"
          :style="{ animationDelay: idx * 30 + 'ms' }"
        >
          <!-- Customer -->
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl overflow-hidden flex-shrink-0 border border-white/10 flex items-center justify-center shadow-lg"
                 :class="item.customer_avatar ? '' : 'bg-gradient-to-br from-admin/20 to-amber-500/20'">
              <img v-if="item.customer_avatar" :src="item.customer_avatar" class="h-full w-full object-cover" />
              <span v-else class="text-admin font-bold text-xs">{{ initials(item.customer_name) }}</span>
            </div>
            <div>
              <p class="text-sm font-bold text-white group-hover:text-admin transition-colors">{{ item.customer_name || 'Walk-in' }}</p>
              <p class="text-[10px] text-white/30 md:hidden font-mono">{{ item.verification_code }}</p>
            </div>
          </div>

          <!-- Barber -->
          <div class="hidden md:flex items-center gap-2">
            <div class="h-6 w-6 rounded-lg bg-white/5 flex items-center justify-center border border-white/10 flex-shrink-0">
              <span class="text-[9px] font-bold text-white/60">{{ initials(item.barber_name) }}</span>
            </div>
            <span class="text-sm text-white/70 font-medium">{{ item.barber_name || 'Unassigned' }}</span>
          </div>

          <!-- Details -->
          <div>
            <p class="text-sm font-semibold text-white mb-0.5">{{ item.service_name || 'Custom Service' }}</p>
            <div class="flex items-center gap-2">
              <CalendarIcon class="h-3 w-3 text-white/30 flex-shrink-0" />
              <p class="text-[11px] text-white/40">{{ formatDate(item.appointment_date) }} at {{ item.appointment_time }}</p>
            </div>
          </div>

          <!-- Code -->
          <div>
            <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border font-mono text-sm tracking-wider font-bold transition-all duration-300"
                 :class="getCodeStyle(item.status, item.appointment_date)">
              <CheckBadgeIcon v-if="item.status === 'completed'" class="h-4 w-4 flex-shrink-0" />
              <ExclamationTriangleIcon v-else-if="item.status === 'cancelled'" class="h-4 w-4 flex-shrink-0" />
              <ClockIcon v-else-if="isExpired(item.appointment_date) && item.status === 'confirmed'" class="h-4 w-4 flex-shrink-0" />
              <QrCodeIcon v-else class="h-4 w-4 flex-shrink-0" />
              {{ item.verification_code }}
            </div>
          </div>

          <!-- Action -->
          <div class="flex justify-end">
            <button v-if="item.status === 'confirmed' && !isExpired(item.appointment_date)" 
                    @click="$emit('verify', item)"
                    class="flex items-center gap-2 px-4 py-2.5 bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/20 rounded-xl text-xs font-bold transition-all duration-200 hover:shadow-[0_0_15px_rgba(16,185,129,0.2)] hover:scale-105 active:scale-95">
              <ShieldCheckIcon class="h-3.5 w-3.5" />
              Verify
            </button>
            <span v-else-if="item.status === 'completed'" 
                  class="flex items-center gap-1.5 text-[10px] uppercase tracking-wider text-emerald-500/70 font-bold px-3 py-1.5 bg-emerald-500/5 rounded-lg border border-emerald-500/10">
              <CheckBadgeIcon class="h-3 w-3" />
              Verified
            </span>
            <span v-else-if="item.status === 'cancelled'"
                  class="flex items-center gap-1.5 text-[10px] uppercase tracking-wider text-red-400/60 font-bold px-3 py-1.5 bg-red-500/5 rounded-lg border border-red-500/10">
              <XCircleIcon class="h-3 w-3" />
              Cancelled
            </span>
            <span v-else class="text-[10px] uppercase tracking-wider text-white/30 font-bold px-3 py-1.5 bg-white/5 rounded-lg border border-white/5">{{ item.status }}</span>
          </div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-else class="p-20 text-center">
        <div class="inline-flex items-center justify-center h-20 w-20 rounded-2xl bg-white/[0.03] border border-white/[0.05] mb-5">
          <ShieldCheckIcon class="h-10 w-10 text-white/10" />
        </div>
        <h3 class="text-lg font-bold text-white mb-2">No verification codes found</h3>
        <p class="text-sm text-white/40 max-w-sm mx-auto">Try adjusting your filters or search query to find the codes you're looking for.</p>
      </div>
    </template>
  </div>
</template>

<script setup>
import {
  CalendarIcon,
  CheckBadgeIcon,
  ExclamationTriangleIcon,
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
  return new Date(dateStr) < new Date(new Date().setHours(0,0,0,0));
}

function getCodeStyle(status, dateStr) {
  if (status === 'completed') return 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400 shadow-[0_0_10px_rgba(16,185,129,0.1)]';
  if (status === 'cancelled') return 'bg-red-500/10 border-red-500/30 text-red-400 line-through opacity-60';
  if (status === 'confirmed' && isExpired(dateStr)) return 'bg-red-500/10 border-red-500/30 text-red-400 opacity-60 line-through';
  return 'bg-amber-500/10 border-amber-500/30 text-amber-400 shadow-[0_0_10px_rgba(245,158,11,0.1)]';
}

function initials(name) {
  if (!name) return 'U';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}
</script>

<style scoped>
@keyframes rowIn {
  from { opacity: 0; transform: translateX(-8px); }
  to { opacity: 1; transform: translateX(0); }
}

.animate-row-in {
  animation: rowIn 0.35s ease-out both;
}
</style>
