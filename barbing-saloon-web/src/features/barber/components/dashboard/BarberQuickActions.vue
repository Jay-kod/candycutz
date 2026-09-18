<template>
  <div class="space-y-6">
    <!-- Today's Progress Card -->
    <div class="rounded-2xl border border-white/[0.06] bg-theme-surface/60 backdrop-blur-sm p-6 overflow-hidden relative group">
      <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gold/10 blur-[30px] group-hover:bg-gold/20 transition-colors duration-500"></div>
      <div class="relative z-10 flex items-center gap-3 mb-5">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gold/10 border border-gold/15">
          <ClockIcon class="h-4.5 w-4.5 text-gold" />
        </div>
        <div>
          <h3 class="text-sm font-bold text-theme-text">Today's Progress</h3>
          <p class="text-[10px] text-ivory/35 mt-0.5">{{ currentDate }}</p>
        </div>
      </div>
      <div class="relative z-10 space-y-3">
        <div>
          <div class="flex justify-between items-end mb-2">
            <span class="text-[11px] font-bold uppercase tracking-widest text-ivory/40">Appointments completed</span>
            <span class="text-xl font-black text-theme-text tracking-tight font-sans">
              {{ stats?.completed_bookings ?? 0 }}<span class="text-sm text-ivory/30">/{{ stats?.today_bookings ?? 0 }}</span>
            </span>
          </div>
          <div class="h-2 rounded-full bg-white/[0.04] overflow-hidden shadow-inner">
            <div
              class="h-full rounded-full bg-gradient-to-r from-gold to-amber-400 transition-all duration-1000 ease-out relative"
              :style="{ width: progressWidth + '%' }"
            >
              <div class="absolute top-0 right-0 bottom-0 left-0 bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.2)_50%,transparent_75%,transparent_100%)] bg-[length:20px_20px] animate-[shimmer_2s_linear_infinite]"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="rounded-2xl border border-white/[0.06] bg-theme-surface/60 backdrop-blur-sm p-6">
      <div class="flex items-center gap-3 mb-5">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/5 border border-white/10">
          <BoltIcon class="h-4.5 w-4.5 text-ivory/70" />
        </div>
        <div>
          <h2 class="text-sm font-bold text-theme-text">Quick Actions</h2>
          <p class="text-[10px] text-ivory/35 mt-0.5">Fast access to tools</p>
        </div>
      </div>
      <div class="grid grid-cols-1 gap-3">
        <RouterLink
          v-for="action in quickActions"
          :key="action.to"
          :to="action.to"
          class="group relative flex items-center gap-4 rounded-xl border border-white/[0.04] bg-[#1a1a1a]/50 p-3.5 transition-all duration-300 hover:border-gold/30 hover:bg-gold/5 hover:shadow-[0_4px_20px_rgba(212,175,55,0.05)] hover:-translate-y-0.5"
        >
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-colors border shadow-sm"
            :class="action.iconWrap"
          >
            <component :is="action.icon" class="h-5 w-5" :class="action.iconColor" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-theme-text group-hover:text-gold transition-colors truncate">{{ action.label }}</p>
            <p class="text-[11px] text-ivory/40 mt-0.5 truncate">{{ action.description }}</p>
          </div>
          <ChevronRightIcon class="h-4 w-4 text-ivory/20 transition-all group-hover:text-gold/60 group-hover:translate-x-1" />
        </RouterLink>

        <button
          @click="$emit('open-notification')"
          class="group relative flex items-center gap-4 rounded-xl border border-white/[0.04] bg-[#1a1a1a]/50 p-3.5 transition-all duration-300 hover:border-purple-500/30 hover:bg-purple-500/5 hover:shadow-[0_4px_20px_rgba(168,85,247,0.05)] hover:-translate-y-0.5 text-left"
        >
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-colors border shadow-sm bg-purple-500/10 border-purple-500/20 group-hover:bg-purple-500/20">
            <MegaphoneIcon class="h-5 w-5 text-purple-400" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-theme-text group-hover:text-purple-400 transition-colors truncate">Notify Customers</p>
            <p class="text-[11px] text-ivory/40 mt-0.5 truncate">Send a quick broadcast alert</p>
          </div>
          <ChevronRightIcon class="h-4 w-4 text-ivory/20 transition-all group-hover:text-purple-400/60 group-hover:translate-x-1" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { ClockIcon, ChevronRightIcon, CalendarIcon, ClipboardDocumentCheckIcon, UserIcon, MegaphoneIcon } from '@heroicons/vue/24/outline';
import { BoltIcon } from '@heroicons/vue/24/solid';

defineProps({
  stats: { type: Object, required: true },
  progressWidth: { type: Number, required: true },
  currentDate: { type: String, required: true }
});

defineEmits(['open-notification']);

const quickActions = [
  {
    label: 'Open Schedule',
    description: 'Manage working hours',
    to: '/barber/schedule',
    icon: CalendarIcon,
    iconWrap: 'bg-gold/10 border-gold/20',
    iconColor: 'text-gold',
  },
  {
    label: 'Appointments',
    description: 'Review all bookings',
    to: '/barber/appointments',
    icon: ClipboardDocumentCheckIcon,
    iconWrap: 'bg-blue-400/10 border-blue-400/20',
    iconColor: 'text-blue-400',
  },
  {
    label: 'My Profile',
    description: 'Update your details',
    to: '/barber/profile',
    icon: UserIcon,
    iconWrap: 'bg-emerald-400/10 border-emerald-400/20',
    iconColor: 'text-emerald-400',
  },
  {
    label: 'Payments & Account',
    description: 'Verify receipts & bank info',
    to: '/barber/payments',
    icon: ClipboardDocumentCheckIcon,
    iconWrap: 'bg-amber-600/10 border-amber-600/20',
    iconColor: 'text-amber-500',
  },
];
</script>
