<template>
  <span
    :class="[
      'inline-flex items-center gap-1.5 font-medium tracking-wide rounded-full border transition-colors',
      sizeClasses,
      statusConfig.classes
    ]"
  >
    <span
      v-if="dot"
      :class="['rounded-full shrink-0', dotSizeClasses, statusConfig.dotClass]"
    ></span>
    <span>{{ label || statusConfig.label }}</span>
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    default: '',
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },
  dot: {
    type: Boolean,
    default: true,
  },
});

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'px-2 py-0.5 text-[10px] leading-tight';
    case 'lg':
      return 'px-3.5 py-1.5 text-xs';
    case 'md':
    default:
      return 'px-2.5 py-1 text-xs';
  }
});

const dotSizeClasses = computed(() => {
  return props.size === 'sm' ? 'w-1.5 h-1.5' : 'w-2 h-2';
});

const statusConfig = computed(() => {
  const s = (props.status || '').toLowerCase().trim();

  switch (s) {
    // Green: Success / Active / Verified / Completed
    case 'confirmed':
    case 'completed':
    case 'active':
    case 'verified':
    case 'approved':
    case 'published':
    case 'online':
      return {
        label: s.charAt(0).toUpperCase() + s.slice(1),
        classes: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
        dotClass: 'bg-emerald-500 animate-pulse',
      };

    // Amber: Warning / Pending / In Review
    case 'pending':
    case 'pending_verification':
    case 'awaiting_payment':
    case 'in_progress':
    case 'draft':
      return {
        label: s === 'pending_verification' ? 'Pending Review' : s.charAt(0).toUpperCase() + s.slice(1).replace('_', ' '),
        classes: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
        dotClass: 'bg-amber-500 animate-pulse',
      };

    // Red: Danger / Cancelled / Suspended / Rejected / Failed
    case 'cancelled':
    case 'suspended':
    case 'rejected':
    case 'failed':
    case 'no_show':
    case 'inactive':
      return {
        label: s === 'no_show' ? 'No Show' : s.charAt(0).toUpperCase() + s.slice(1),
        classes: 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
        dotClass: 'bg-rose-500',
      };

    // Blue: Info / Primary / SuperAdmin / System
    case 'super_admin':
    case 'admin':
    case 'barber':
    case 'customer':
    case 'system':
    case 'refunded':
      return {
        label: s.replace('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase()),
        classes: 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20',
        dotClass: 'bg-sky-500',
      };

    // Neutral default
    default:
      return {
        label: s ? s.charAt(0).toUpperCase() + s.slice(1) : 'Unknown',
        classes: 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20',
        dotClass: 'bg-slate-400',
      };
  }
});
</script>

