<template>
  <div class="bg-[#12161C] rounded-3xl border border-white/[0.08] overflow-hidden shadow-2xl p-6 relative">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xs font-display font-bold text-white uppercase tracking-widest flex items-center gap-2">
        <ClockIcon class="h-4 w-4 text-emerald-400" />
        Recent Walk-Ins Today
      </h3>
      <router-link
        to="/barber/appointments"
        class="text-[10px] text-emerald-400 hover:text-emerald-300 uppercase tracking-widest font-bold"
      >
        View All
      </router-link>
    </div>

    <div
      v-if="recentWalkIns.length === 0"
      class="text-xs text-slate-400 text-center py-6 bg-[#0B0F13] rounded-2xl border border-white/[0.04]"
    >
      No walk-in bookings registered yet today.
    </div>

    <div v-else class="space-y-2.5">
      <div
        v-for="walkIn in recentWalkIns"
        :key="walkIn.id"
        class="flex items-center justify-between p-3 rounded-2xl bg-[#0B0F13] border border-white/[0.06] hover:border-emerald-500/20 transition-colors"
      >
        <div class="flex items-center gap-3 overflow-hidden">
          <div class="h-8 w-8 rounded-full bg-emerald-500/10 flex items-center justify-center shrink-0 border border-emerald-500/20">
            <UserIcon class="h-4 w-4 text-emerald-400" />
          </div>
          <div class="truncate">
            <p class="text-xs font-bold text-white truncate">
              {{ walkIn.customer?.name || walkIn.customer_name || walkIn.client_name }}
            </p>
            <p class="text-[10px] text-slate-400 truncate">
              {{ walkIn.service?.name || 'Walk-In Cut' }} • {{ (walkIn.appointment_time || '').substring(0, 5) }}
            </p>
          </div>
        </div>

        <div class="text-right shrink-0">
          <p class="text-xs font-bold text-emerald-400 font-mono">
            ₦{{ Number(walkIn.service?.price || walkIn.price || 0).toLocaleString() }}
          </p>
          <span
            class="inline-block text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 rounded"
            :class="
              walkIn.status === 'completed'
                ? 'bg-emerald-500/10 text-emerald-400'
                : walkIn.status === 'cancelled'
                ? 'bg-red-500/10 text-red-400'
                : 'bg-amber-500/10 text-amber-400'
            "
          >
            {{ walkIn.status }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ClockIcon, UserIcon } from '@heroicons/vue/24/outline';

defineProps({
  recentWalkIns: {
    type: Array,
    default: () => [],
  },
});
</script>
