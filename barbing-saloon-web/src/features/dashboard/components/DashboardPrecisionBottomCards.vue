<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1: Top Services with Dashed Leader Lines -->
    <div class="rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 relative overflow-hidden group hover:border-admin/40 transition-all flex flex-col justify-between">
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-admin/30 group-hover:border-admin transition-colors"></span>

      <div class="flex items-center justify-between pb-4 border-b border-white/[0.04]">
        <div class="flex items-center gap-2 text-xs font-medium text-ivory/70">
          <SparklesIcon class="h-3.5 w-3.5 text-admin" />
          <span>Top Salon Services</span>
        </div>
        <span class="text-[10px] text-ivory/40 font-mono">By appointments</span>
      </div>

      <div class="py-3 space-y-3.5 flex-1 flex flex-col justify-center">
        <div 
          v-for="service in displayedServices" 
          :key="service.id || service.name"
          class="flex items-center justify-between text-xs group/line"
        >
          <span class="text-ivory/70 group-hover/line:text-admin transition-colors shrink-0 truncate max-w-[140px]">
            {{ service.name }}
          </span>
          <span class="flex-1 mx-2 border-b border-dashed border-white/15"></span>
          <span class="text-white font-mono font-semibold tabular-nums shrink-0">
            {{ service.bookings || service.count || 0 }} cuts
          </span>
        </div>

        <div v-if="!displayedServices.length" class="text-xs text-ivory/40 text-center py-6 font-mono">
          No service bookings recorded yet
        </div>
      </div>
    </div>

    <!-- Card 2: Barber Team Card with Real Barber Data -->
    <div class="rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 relative overflow-hidden group hover:border-admin/40 transition-all flex flex-col justify-between text-center">
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-admin/30 group-hover:border-admin transition-colors"></span>

      <div class="flex items-center justify-between pb-3 border-b border-white/[0.04] text-left">
        <div class="flex items-center gap-2 text-xs font-medium text-ivory/70">
          <UserGroupIcon class="h-3.5 w-3.5 text-admin" />
          <span>Barbers & Stylists</span>
        </div>
        <span class="text-[10px] text-ivory/40 font-mono">{{ barberCount }} active staff</span>
      </div>

      <div class="py-4 flex flex-col items-center justify-center flex-1">
        <div class="flex items-center -space-x-2.5 mb-3">
          <div
            v-for="(barber, bIdx) in displayedBarbers"
            :key="barber.id || bIdx"
            class="h-10 w-10 rounded-full bg-gradient-to-br from-admin/40 to-admin/10 border-2 border-[#0B0F13] flex items-center justify-center text-xs font-bold text-white shadow"
            :title="barber.name"
          >
            {{ getInitials(barber.name) }}
          </div>
          <div v-if="!displayedBarbers.length" class="h-10 w-10 rounded-full bg-white/10 border-2 border-[#0B0F13] flex items-center justify-center text-xs font-bold text-white">
            CC
          </div>
        </div>

        <p class="text-xs font-bold text-white">
          {{ barberCount > 0 ? `${barberCount} Active Barbers` : 'No Barbers Enrolled' }}
        </p>
        <p class="text-[11px] text-ivory/40 max-w-[180px] mt-1 leading-snug">
          Manage chairs, rosters, commissions and appointments.
        </p>

        <RouterLink
          to="/admin/barbers"
          class="mt-3.5 inline-flex items-center gap-1.5 rounded-lg border border-admin/30 bg-admin/10 hover:bg-admin hover:text-white px-3 py-1.5 text-[11px] font-semibold text-admin transition-all shadow-sm"
        >
          <PlusIcon class="h-3 w-3 text-admin group-hover:text-white" />
          <span>Manage Barbers</span>
        </RouterLink>
      </div>
    </div>

    <!-- Card 3: Hourly Booking Heatmap Matrix -->
    <div class="rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 relative overflow-hidden group hover:border-admin/40 transition-all flex flex-col justify-between">
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-admin/30 group-hover:border-admin transition-colors"></span>

      <div class="flex items-center justify-between pb-3 border-b border-white/[0.04]">
        <div class="flex items-center gap-2 text-xs font-medium text-ivory/70">
          <ClockIcon class="h-3.5 w-3.5 text-admin" />
          <span>Appointment Density</span>
        </div>
        <span class="text-[10px] text-ivory/40 font-mono">Hourly traffic</span>
      </div>

      <div class="pt-2 flex items-baseline justify-between">
        <div>
          <span class="text-xl font-bold text-white tabular-nums tracking-tight font-sans">
            ₦{{ formatCurrency(stats?.today_revenue || 0) }}
          </span>
          <p class="text-[10px] text-ivory/40">Today's Revenue</p>
        </div>
        <div class="text-right">
          <span class="text-xs font-bold text-admin tabular-nums">
            {{ peakHoursText }}
          </span>
          <p class="text-[10px] text-ivory/40">Peak Salon Hours</p>
        </div>
      </div>

      <div class="py-2.5">
        <div class="flex items-center gap-1.5">
          <div class="flex flex-col justify-between h-[84px] text-[8px] font-mono text-ivory/30 shrink-0 leading-none">
            <span>Mon</span>
            <span>Tue</span>
            <span>Wed</span>
            <span>Thu</span>
            <span>Fri</span>
            <span>Sat</span>
            <span>Sun</span>
          </div>

          <div class="flex-1 flex flex-col gap-[2px]">
            <div v-for="d in 7" :key="'hrow-' + d" class="flex justify-between gap-[2px]">
              <span
                v-for="h in 24"
                :key="'hcell-' + d + '-' + h"
                class="flex-1 h-[9px] rounded-[1px] transition-colors"
                :class="getSalonCellColor(d, h - 1)"
              ></span>
            </div>
          </div>
        </div>

        <div class="flex justify-between text-[8px] text-ivory/30 font-mono pl-5 pt-1.5">
          <span>12a</span>
          <span>6a</span>
          <span>12p</span>
          <span>6p</span>
          <span>12a</span>
        </div>
      </div>

      <div class="flex items-center justify-start gap-1 text-[9px] text-ivory/40 font-mono pt-1 border-t border-white/[0.03]">
        <span>Lower</span>
        <span class="h-2 w-2 rounded-[1px] bg-[#16181F]"></span>
        <span class="h-2 w-2 rounded-[1px] bg-[#4A2600]"></span>
        <span class="h-2 w-2 rounded-[1px] bg-[#803300]"></span>
        <span class="h-2 w-2 rounded-[1px] bg-[#CC5200]"></span>
        <span class="h-2 w-2 rounded-[1px] bg-[#FF6700]"></span>
        <span>Higher</span>
      </div>
    </div>

    <!-- Card 4: Quick Actions with Direct Salon Routes -->
    <div class="rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 relative overflow-hidden group hover:border-admin/40 transition-all flex flex-col justify-between">
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-admin/30 group-hover:border-admin transition-colors"></span>

      <div class="flex items-center justify-between pb-3 border-b border-white/[0.04]">
        <div class="flex items-center gap-2 text-xs font-medium text-ivory/70">
          <BoltIcon class="h-3.5 w-3.5 text-admin" />
          <span>Salon Operations</span>
        </div>
      </div>

      <div class="py-2 space-y-2.5 flex-1 flex flex-col justify-center">
        <RouterLink
          v-for="action in quickActions"
          :key="action.title"
          :to="action.to"
          class="flex items-center justify-between p-1.5 rounded-lg hover:bg-admin/10 transition-colors group/item"
        >
          <div class="flex items-center gap-2.5 min-w-0">
            <component :is="action.icon" class="h-4 w-4 text-admin group-hover/item:text-admin-light transition-colors shrink-0" />
            <div class="min-w-0">
              <p class="text-xs font-semibold text-white/90 group-hover/item:text-admin-light transition-colors truncate leading-snug">
                {{ action.title }}
              </p>
              <p class="text-[10px] text-ivory/40 truncate">
                {{ action.desc }}
              </p>
            </div>
          </div>
          <ChevronRightIcon class="h-3.5 w-3.5 text-ivory/30 group-hover/item:text-admin group-hover/item:translate-x-0.5 transition-all shrink-0 ml-2" />
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { 
  SparklesIcon, UserGroupIcon, ClockIcon, BoltIcon, 
  PlusIcon, ChevronRightIcon,
  CalendarDaysIcon, DocumentChartBarIcon, Cog6ToothIcon, UserPlusIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  stats: { type: Object, default: () => ({}) },
  topServices: { type: Array, default: () => [] },
  topBarbers: { type: Array, default: () => [] },
  recentAppointments: { type: Array, default: () => [] },
  hourlyDistribution: { type: Object, default: () => ({}) }
});

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(val || 0));
};

const getInitials = (name) => {
  if (!name) return 'CC';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const displayedServices = computed(() => {
  return (props.topServices || []).slice(0, 5);
});

const displayedBarbers = computed(() => {
  return (props.topBarbers || []).slice(0, 3);
});

const barberCount = computed(() => {
  return props.topBarbers?.length ?? props.stats?.barbers ?? 0;
});

const peakHoursText = computed(() => {
  if (props.hourlyDistribution && Object.keys(props.hourlyDistribution).length) {
    const sorted = Object.entries(props.hourlyDistribution).sort((a, b) => Number(b[1]) - Number(a[1]));
    if (sorted.length && Number(sorted[0][1]) > 0) {
      const hr = Number(sorted[0][0]);
      return `${hr}:00 - ${hr + 1}:00`;
    }
  }
  return '10:00 - 18:00';
});

const quickActions = [
  { title: 'Walk-In Booking', desc: 'Register a walk-in client.', icon: UserPlusIcon, to: '/admin/walk-in' },
  { title: 'Appointments', desc: 'Manage schedule & slots.', icon: CalendarDaysIcon, to: '/admin/appointments' },
  { title: 'Reports & Revenue', desc: 'Audit statements & ledger.', icon: DocumentChartBarIcon, to: '/admin/reports' },
  { title: 'Salon Settings', desc: 'Pricing, hours & CMS.', icon: Cog6ToothIcon, to: '/admin/settings' }
];

const getSalonCellColor = (day, hour) => {
  const hrCount = Number(props.hourlyDistribution?.[hour] || 0);
  if (hrCount > 0) {
    if (hrCount >= 4) return 'bg-[#FF6700]';
    if (hrCount >= 2) return 'bg-[#CC5200]';
    return 'bg-[#803300]';
  }
  const isOperatingHour = hour >= 9 && hour <= 19;
  return isOperatingHour ? 'bg-[#22160E]' : 'bg-[#12141A]';
};
</script>
