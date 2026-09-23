<template>
  <div class="space-y-6">
    <!-- Quick Actions -->
    <div class="rounded-xl border border-theme-border bg-theme-surface p-5">
      <h3 class="text-sm font-bold text-theme-text mb-4 flex items-center gap-2">
        <BoltIcon class="h-4 w-4 text-admin" />
        Quick Actions
      </h3>
      <div class="grid grid-cols-2 gap-2.5">
        <RouterLink v-for="action in quickActions" :key="action.label" :to="action.to" class="flex flex-col items-center justify-center gap-1.5 rounded-lg bg-white/[0.02] p-4 border border-theme-border hover:bg-admin/8 hover:border-admin/20 transition-all group/action">
          <component :is="action.icon" class="h-5 w-5 text-ivory/40 group-hover/action:text-admin transition-colors" />
          <span class="text-[10px] font-bold text-ivory/50 uppercase tracking-wider group-hover/action:text-admin-light transition-colors">{{ action.label }}</span>
        </RouterLink>
      </div>
    </div>

    <!-- Recent Activity Feed -->
    <div class="rounded-xl border border-theme-border bg-theme-surface overflow-hidden">
      <div class="border-b border-theme-border px-6 py-4">
        <h3 class="text-sm font-bold text-theme-text flex items-center gap-2">
          <ClockIcon class="h-4 w-4 text-cyan-400" />
          Recent Activity
        </h3>
      </div>
      <div class="divide-y divide-white/[0.03]">
        <div v-for="(activity, idx) in recentActivity" :key="idx" class="px-6 py-3 flex items-start gap-3">
          <div class="mt-1 h-6 w-6 rounded-md flex items-center justify-center shrink-0" :class="activityIconClass(activity.action)">
            <component :is="activityIcon(activity.action)" class="h-3.5 w-3.5" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-xs text-ivory/60 leading-relaxed">
              <span class="font-semibold text-theme-text">{{ activity.user_name || 'System' }}</span>
              {{ activityVerb(activity.action) }}
            </p>
            <p class="text-[10px] text-ivory/25 mt-0.5">{{ timeAgo(activity.created_at) }}</p>
          </div>
        </div>
        <div v-if="!recentActivity?.length" class="flex flex-col items-center py-10 text-ivory/20">
          <ClockIcon class="h-8 w-8 mb-2" />
          <p class="text-xs">No recent activity</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  BoltIcon, ClockIcon, DocumentTextIcon, PhotoIcon, Cog6ToothIcon, UserGroupIcon, 
  CheckCircleIcon, ArrowTrendingUpIcon, ArrowTrendingDownIcon, ExclamationTriangleIcon, ClipboardDocumentListIcon
} from '@heroicons/vue/24/outline';

defineProps({
  recentActivity: { type: Array, default: () => [] }
});

const quickActions = [
  { label: 'Walk-In', icon: UserGroupIcon, to: '/admin/appointments/walk-in' },
  { label: 'Reports', icon: DocumentTextIcon, to: '/admin/reports' },
  { label: 'Gallery', icon: PhotoIcon, to: '/admin/gallery' },
  { label: 'Settings', icon: Cog6ToothIcon, to: '/admin/settings' }
];

const activityIcon = (action) => {
  if (action.includes('created') || action.includes('booked')) return ClipboardDocumentListIcon;
  if (action.includes('completed') || action.includes('approved')) return CheckCircleIcon;
  if (action.includes('cancelled') || action.includes('failed')) return ExclamationTriangleIcon;
  if (action.includes('updated')) return ArrowTrendingUpIcon;
  if (action.includes('deleted')) return ArrowTrendingDownIcon;
  return ClockIcon;
};

const activityIconClass = (action) => {
  if (action.includes('created') || action.includes('booked')) return 'bg-blue-500/20 text-blue-400';
  if (action.includes('completed') || action.includes('approved')) return 'bg-emerald-500/20 text-emerald-400';
  if (action.includes('cancelled') || action.includes('failed')) return 'bg-red-500/20 text-red-400';
  if (action.includes('updated')) return 'bg-amber-500/20 text-amber-400';
  if (action.includes('deleted')) return 'bg-purple-500/20 text-purple-400';
  return 'bg-white/10 text-white/60';
};

const activityVerb = (action) => {
  const map = {
    'appointment_booked': 'booked a new appointment',
    'appointment_completed': 'completed an appointment',
    'appointment_cancelled': 'cancelled an appointment',
    'payment_received': 'processed a payment',
    'service_added': 'added a new service',
    'barber_added': 'added a new barber'
  };
  return map[action] || action.replace('_', ' ');
};

const timeAgo = (dateStr) => {
  if (!dateStr) return '';
  const seconds = Math.floor((new Date() - new Date(dateStr)) / 1000);
  let interval = seconds / 31536000;
  if (interval > 1) return Math.floor(interval) + ' years ago';
  interval = seconds / 2592000;
  if (interval > 1) return Math.floor(interval) + ' months ago';
  interval = seconds / 86400;
  if (interval > 1) return Math.floor(interval) + ' days ago';
  interval = seconds / 3600;
  if (interval > 1) return Math.floor(interval) + ' hours ago';
  interval = seconds / 60;
  if (interval > 1) return Math.floor(interval) + ' minutes ago';
  return Math.floor(seconds) + ' seconds ago';
};
</script>
