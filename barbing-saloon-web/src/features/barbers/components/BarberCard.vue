<template>
  <div class="group relative flex flex-col rounded-3xl border border-white/[0.08] bg-[#12161C] p-6 shadow-xl hover:border-admin/40 transition-all duration-300 overflow-hidden">
    <!-- Ambient luxury top accent -->
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-admin via-amber-400 to-amber-200 opacity-0 group-hover:opacity-100 transition-opacity"></div>
    <div class="absolute -right-10 -bottom-10 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all pointer-events-none"></div>

    <!-- Header: Avatar, Name & Quick Actions -->
    <div class="flex items-start justify-between gap-3 mb-4 relative z-10">
      <div class="flex items-center gap-3.5 min-w-0">
        <div class="relative h-14 w-14 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-admin/25 to-admin/10 border border-admin/30 flex items-center justify-center text-admin font-display text-xl font-bold shadow-[inset_0_1px_0_rgba(255,255,255,0.1)] group-hover:scale-105 transition-transform">
          <img v-if="barber.avatar || barber.avatar_url" :src="barber.avatar || barber.avatar_url" :alt="barber.name" class="h-full w-full object-cover" />
          <span v-else>{{ initials(barber.name) }}</span>
          <div 
            class="absolute -top-1 -right-1 h-3 w-3 rounded-full border-2 border-[#12161C]"
            :class="isChairReady(barber) ? 'bg-emerald-400' : 'bg-amber-400'"
            :title="isChairReady(barber) ? 'Chair Ready' : 'Chair Occupied'"
          ></div>
        </div>
        <div class="min-w-0">
          <h3 class="font-bold text-white text-base leading-tight truncate">{{ barber.name }}</h3>
          <p class="text-xs text-white/40 mt-0.5 truncate font-mono">@{{ barber.username || 'stylist' }}</p>
          <div class="flex items-center gap-2 mt-1">
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider border shadow-sm" :class="statusStyle(barber.status)">
              {{ statusLabel(barber.status) }}
            </span>
            <span class="text-amber-400 text-xs font-mono font-bold flex items-center gap-0.5">
              <span>★</span>
              <span>{{ Number(barber.rating || 5.0).toFixed(1) }}</span>
            </span>
          </div>
        </div>
      </div>

      <!-- Quick Action Buttons -->
      <div class="flex items-center gap-1 bg-white/[0.03] rounded-xl p-1 border border-white/[0.06] opacity-90 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity shrink-0">
        <router-link 
          :to="`/admin/barbers/${barber.id}/edit`" 
          class="p-1.5 rounded-lg text-white/50 hover:text-white hover:bg-white/10 transition-colors" 
          title="Edit Barber"
        >
          <PencilSquareIcon class="h-4 w-4" />
        </router-link>
        <button 
          @click="$emit('delete', barber)" 
          class="p-1.5 rounded-lg text-white/50 hover:text-red-400 hover:bg-red-500/15 transition-colors" 
          title="Delete Barber"
        >
          <TrashIcon class="h-4 w-4" />
        </button>
      </div>
    </div>

    <!-- Contact & Experience -->
    <div class="space-y-2 py-3 border-y border-white/[0.05] text-xs relative z-10">
      <div class="flex items-center gap-2 text-white/60">
        <EnvelopeIcon class="h-3.5 w-3.5 text-white/30 shrink-0" />
        <a v-if="barber.email" :href="`mailto:${barber.email}`" class="truncate hover:text-white">{{ barber.email }}</a>
        <span v-else class="text-white/30">No email</span>
      </div>
      <div class="flex items-center justify-between text-white/60 pt-0.5">
        <div class="flex items-center gap-2">
          <AcademicCapIcon class="h-3.5 w-3.5 text-white/30 shrink-0" />
          <span>{{ barber.experience_years || 5 }} Years Experience</span>
        </div>
        <span 
          class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md"
          :class="isChairReady(barber) ? 'bg-emerald-500/10 text-emerald-400' : 'bg-white/5 text-white/40'"
        >
          {{ isChairReady(barber) ? 'Chair Ready' : 'Chair Busy' }}
        </span>
      </div>
    </div>

    <!-- Specialties Tags -->
    <div class="mt-3.5 pt-1 relative z-10 flex-1">
      <div class="flex flex-wrap gap-1.5">
        <span 
          v-for="spec in (barber.specialties || []).slice(0, 3)" 
          :key="spec" 
          class="rounded-lg bg-white/[0.03] px-2.5 py-1 text-[10px] uppercase tracking-wider font-semibold text-white/70 border border-white/[0.06] flex items-center gap-1"
        >
          <SparklesIcon class="h-3 w-3 text-admin/80" />
          {{ spec }}
        </span>
        <span 
          v-if="(barber.specialties || []).length > 3" 
          class="rounded-lg bg-white/[0.03] px-2 py-1 text-[10px] font-semibold text-white/40 border border-white/[0.06]"
        >
          +{{ barber.specialties.length - 3 }} more
        </span>
        <span v-if="!(barber.specialties || []).length" class="text-[10px] text-white/30 italic">
          Master All-Round Barber
        </span>
      </div>
    </div>

    <!-- Access Level Control -->
    <div class="mt-4 pt-3 border-t border-white/[0.05] flex items-center justify-between relative z-10">
      <span class="text-[10px] uppercase tracking-wider text-white/40 font-bold">Access Status</span>
      <select
        :value="barber.status"
        @change="$emit('status-change', barber, $event.target.value)"
        class="rounded-xl border border-white/[0.08] bg-black/40 px-2.5 py-1.5 text-[11px] font-bold uppercase tracking-wider text-white/90 outline-none focus:border-admin/50 cursor-pointer transition-all hover:bg-white/[0.05]"
      >
        <option value="active" class="bg-[#12161C] text-emerald-400">🟢 Active</option>
        <option value="pending_approval" class="bg-[#12161C] text-amber-400">🟡 Pending</option>
        <option value="on_leave" class="bg-[#12161C] text-blue-400">⚪ On Leave</option>
        <option value="suspended" class="bg-[#12161C] text-red-400">🔴 Suspended</option>
      </select>
    </div>
  </div>
</template>

<script setup>
import { 
  EnvelopeIcon, 
  SparklesIcon, 
  AcademicCapIcon, 
  PencilSquareIcon, 
  TrashIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  barber: {
    type: Object,
    required: true
  }
});

defineEmits(['delete', 'status-change']);

function initials(name) {
  return (name || 'B').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function isChairReady(b) {
  return b.chair_status === 'free' || (b.is_available && b.status === 'active');
}

function statusStyle(status) {
  const map = {
    active: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
    pending_approval: 'bg-amber-500/15 text-amber-400 border-amber-500/30',
    suspended: 'bg-red-500/15 text-red-400 border-red-500/30',
    on_leave: 'bg-blue-500/15 text-blue-400 border-blue-500/30'
  };
  return map[status] || 'bg-white/5 text-white/50 border-white/10';
}

function statusLabel(status) {
  const map = {
    active: 'Active',
    pending_approval: 'Pending',
    suspended: 'Suspended',
    on_leave: 'On Leave'
  };
  return map[status] || status;
}
</script>
