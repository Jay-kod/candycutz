<template>
  <div class="space-y-6">
    <!-- Quick Actions Card -->
    <div class="rounded-2xl border border-white/[0.07] bg-[#0A0F12]/80 backdrop-blur-xl p-5 lg:p-6 shadow-xl">
      <div class="flex items-center gap-3 pb-4 border-b border-white/[0.05]">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-admin/10 border border-admin/20 text-admin shrink-0">
          <BoltIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="text-sm font-bold text-white tracking-wide">Quick Actions</h2>
          <p class="text-[11px] text-white/40">Frequent management operations</p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3 mt-4">
        <RouterLink 
          v-for="action in quickActions" 
          :key="action.label" 
          :to="action.to" 
          class="flex flex-col items-center justify-center gap-2 rounded-xl bg-white/[0.02] border border-white/[0.06] p-4 text-center transition-all duration-300 hover:bg-admin/10 hover:border-admin/30 hover:-translate-y-0.5 group"
        >
          <div class="h-8 w-8 rounded-lg bg-white/[0.04] border border-white/[0.06] flex items-center justify-center text-white/60 group-hover:text-admin group-hover:border-admin/30 transition-colors">
            <component :is="action.icon" class="h-4 w-4" />
          </div>
          <span class="text-xs font-semibold text-white/70 group-hover:text-white transition-colors">
            {{ action.label }}
          </span>
        </RouterLink>
      </div>
    </div>

    <!-- Recent Activity Feed Card -->
    <div class="rounded-2xl border border-white/[0.07] bg-[#0A0F12]/80 backdrop-blur-xl shadow-xl overflow-hidden">
      <div class="px-5 lg:px-6 py-4 border-b border-white/[0.05] flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 shrink-0">
            <ClockIcon class="h-5 w-5" />
          </div>
          <div>
            <h2 class="text-sm font-bold text-white tracking-wide">Recent Activity</h2>
            <p class="text-[11px] text-white/40">Audit trail events</p>
          </div>
        </div>

        <RouterLink 
          to="/admin/system-logs" 
          class="text-xs font-semibold text-admin hover:text-admin-light transition-colors"
        >
          View Logs
        </RouterLink>
      </div>

      <div class="divide-y divide-white/[0.04]">
        <div 
          v-for="(activity, idx) in displayedActivity" 
          :key="activity.id || idx" 
          class="px-5 lg:px-6 py-3.5 flex items-start gap-3 hover:bg-white/[0.02] transition-colors"
        >
          <!-- Activity Icon Badge -->
          <div 
            class="mt-0.5 h-7 w-7 rounded-lg flex items-center justify-center shrink-0 border"
            :class="activityIconClass(activity.action)"
          >
            <component :is="activityIcon(activity.action)" class="h-3.5 w-3.5" />
          </div>

          <!-- Description & Time -->
          <div class="flex-1 min-w-0">
            <p class="text-xs text-white/80 leading-snug">
              <span class="font-semibold text-white">{{ activity.user_name || 'System' }}</span>
              <span class="text-white/50 ml-1">{{ formatActionText(activity.action) }}</span>
            </p>
            <p class="text-[10px] text-white/35 mt-0.5 font-medium">
              {{ timeAgo(activity.created_at) }}
            </p>
          </div>
        </div>

        <div v-if="!displayedActivity?.length" class="flex flex-col items-center justify-center py-10 text-white/30">
          <ClockIcon class="h-8 w-8 mb-2 opacity-40 text-cyan-400" />
          <p class="text-xs font-medium">No recent logs recorded</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { 
  BoltIcon, ClockIcon, UserPlusIcon, CalendarDaysIcon, 
  DocumentChartBarIcon, Cog6ToothIcon, CheckCircleIcon, 
  ArrowTrendingUpIcon, ShieldCheckIcon, UserCircleIcon, 
  ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  recentActivity: { type: Array, default: () => [] }
});

const quickActions = [
  { label: 'Walk-In Booking', icon: UserPlusIcon, to: '/admin/walk-in' },
  { label: 'Appointments', icon: CalendarDaysIcon, to: '/admin/appointments' },
  { label: 'Performance Reports', icon: DocumentChartBarIcon, to: '/admin/reports' },
  { label: 'Platform Settings', icon: Cog6ToothIcon, to: '/admin/settings' }
];

// Cap displayed activity to 6 items to keep sidebar balanced
const displayedActivity = computed(() => {
  return (props.recentActivity || []).slice(0, 6);
});

const activityIcon = (action) => {
  const act = (action || '').toLowerCase();
  if (act.includes('login') || act.includes('auth')) return ArrowRightOnRectangleIcon;
  if (act.includes('appointment') || act.includes('booking')) return CalendarDaysIcon;
  if (act.includes('user') || act.includes('customer') || act.includes('barber')) return UserCircleIcon;
  if (act.includes('complete') || act.includes('approved')) return CheckCircleIcon;
  if (act.includes('setting') || act.includes('config')) return Cog6ToothIcon;
  return ShieldCheckIcon;
};

const activityIconClass = (action) => {
  const act = (action || '').toLowerCase();
  if (act.includes('login')) return 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20';
  if (act.includes('appointment') || act.includes('booking')) return 'bg-admin/10 text-admin border-admin/20';
  if (act.includes('complete') || act.includes('approved')) return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
  if (act.includes('cancel') || act.includes('error')) return 'bg-red-500/10 text-red-400 border-red-500/20';
  return 'bg-white/5 text-white/60 border-white/10';
};

const formatActionText = (action) => {
  if (!action) return 'performed an operation';
  const act = action.toLowerCase();
  if (act === 'auth:login' || act === 'login') return 'signed in to portal';
  if (act === 'auth:logout' || act === 'logout') return 'signed out';
  if (act.includes('appointment_booked')) return 'booked a new appointment';
  if (act.includes('appointment_completed')) return 'marked appointment completed';
  if (act.includes('appointment_cancelled')) return 'cancelled an appointment';
  if (act.includes('settings_updated')) return 'updated salon settings';
  return act.replace(/[:_]/g, ' ');
};

const timeAgo = (dateStr) => {
  if (!dateStr) return 'Just now';
  const diffSec = Math.floor((new Date() - new Date(dateStr)) / 1000);
  if (diffSec < 60) return 'Just now';
  const diffMin = Math.floor(diffSec / 60);
  if (diffMin < 60) return `${diffMin}m ago`;
  const diffHour = Math.floor(diffMin / 60);
  if (diffHour < 24) return `${diffHour}h ago`;
  const diffDay = Math.floor(diffHour / 24);
  return `${diffDay}d ago`;
};
</script>
