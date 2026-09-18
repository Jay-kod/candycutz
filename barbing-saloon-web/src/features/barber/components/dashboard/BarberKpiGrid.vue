<template>
  <div class="grid gap-5 grid-cols-2 lg:grid-cols-4">
    <article v-for="card in statsCards" :key="card.label" class="group relative overflow-hidden rounded-3xl border border-white/[0.08] bg-[#1a1a1a]/80 p-6 backdrop-blur-xl transition-all duration-500 hover:border-white/20 hover:shadow-2xl hover:-translate-y-1">
      <!-- Subtle gradient overlay -->
      <div class="absolute inset-0 bg-gradient-to-br opacity-0 transition-opacity duration-500 group-hover:opacity-100" :class="card.gradient"></div>
      <!-- Watermark icon top-right background -->
      <div class="absolute -right-4 -top-4 opacity-[0.06] transition-all duration-700 group-hover:opacity-[0.12] group-hover:scale-110 group-hover:rotate-6 pointer-events-none" :class="card.iconColor">
        <component :is="card.icon" class="w-28 h-28" />
      </div>

      <div class="relative z-10 flex items-start gap-4">
        <!-- Large icon on left, no background wrapper -->
        <component :is="card.icon" class="w-10 h-10 shrink-0 mt-0.5 transition-colors duration-300" :class="card.iconColor" />
        <div class="flex-1 min-w-0">
          <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-white/50 mb-2">{{ card.label }}</p>
          <p class="text-4xl lg:text-5xl font-black tracking-tight font-sans drop-shadow-sm text-white">
            {{ card.value }}
          </p>
        </div>
      </div>
    </article>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import {
  CalendarDaysIcon,
  ArrowTrendingUpIcon,
  UserGroupIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  stats: { type: Object, required: true }
});

const statsCards = computed(() => [
  {
    label: "Today's Bookings",
    value: props.stats?.today_bookings ?? 0,
    icon: CalendarDaysIcon,
    iconColor: 'text-gold',
    gradient: 'from-gold/[0.05] to-transparent',
  },
  {
    label: 'Upcoming',
    value: props.stats?.upcoming_bookings ?? 0,
    icon: ArrowTrendingUpIcon,
    iconColor: 'text-blue-400',
    gradient: 'from-blue-400/[0.05] to-transparent',
  },
  {
    label: 'Completed',
    value: props.stats?.completed_bookings ?? 0,
    icon: UserGroupIcon,
    iconColor: 'text-emerald-400',
    gradient: 'from-emerald-400/[0.05] to-transparent',
  },
  {
    label: 'No Shows',
    value: props.stats?.no_show_count ?? 0,
    icon: ExclamationTriangleIcon,
    iconColor: 'text-red-400',
    gradient: 'from-red-400/[0.05] to-transparent',
  },
]);
</script>
