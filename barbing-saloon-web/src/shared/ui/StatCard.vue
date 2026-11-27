<template>
  <div
    :class="[
      'group relative overflow-hidden rounded-2xl border transition-all duration-300',
      variant === 'customer' ? 'p-3 sm:p-5' : 'p-5',
      'bg-[var(--color-bg-secondary)] dark:bg-[#121824]',
      'border-[var(--color-border-subtle)] dark:border-white/[0.08]',
      'hover:border-amber-400/40 dark:hover:border-white/20 hover:shadow-xl hover:-translate-y-0.5',
      clickable ? 'cursor-pointer' : ''
    ]"
    @click="clickable && $emit('click')"
  >
    <!-- Background Ambient Glow on Hover -->
    <div
      class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-gradient-to-br from-amber-500/10 to-transparent blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"
    ></div>

    <!-- Skeleton Loading State -->
    <div v-if="loading" class="space-y-3 animate-pulse">
      <div class="flex items-center justify-between">
        <div class="h-4 w-24 bg-slate-200 dark:bg-white/10 rounded"></div>
        <div class="h-10 w-10 bg-slate-200 dark:bg-white/10 rounded-xl"></div>
      </div>
      <div class="h-8 w-32 bg-slate-200 dark:bg-white/10 rounded"></div>
      <div class="h-3 w-20 bg-slate-200 dark:bg-white/10 rounded"></div>
    </div>

    <!-- Live Card Content -->
    <template v-else>
      <div class="flex items-start justify-between gap-2 sm:gap-3">
        <div class="min-w-0 flex-1">
          <p
            :class="[
              'font-semibold uppercase tracking-wider text-[var(--color-text-secondary)] truncate',
              variant === 'customer' ? 'text-[10px] sm:text-xs' : 'text-xs'
            ]"
          >
            <span class="sm:hidden">{{ mobileTitle || title }}</span>
            <span class="hidden sm:inline">{{ title }}</span>
          </p>
          <div class="mt-2 flex items-baseline gap-2">
            <span
              :class="[
                'font-display font-bold tracking-tight text-[var(--color-text-primary)] tabular-nums',
                variant === 'customer' ? 'text-xl sm:text-3xl' : 'text-2xl sm:text-3xl'
              ]"
            >
              {{ value }}
            </span>
            <slot name="value-suffix" />
          </div>
        </div>

        <!-- Metric Icon Wrap -->
        <div
          v-if="icon"
          :class="[
            'flex shrink-0 items-center justify-center rounded-xl border transition-transform duration-300 group-hover:scale-105',
            variant === 'customer' ? 'h-8 w-8 sm:h-11 sm:w-11' : 'h-11 w-11',
            iconWrapClasses
          ]"
        >
          <component
            :is="icon"
            :class="[
              iconColorClasses,
              variant === 'customer' ? 'h-4 w-4 sm:h-5 sm:w-5' : 'h-5 w-5'
            ]"
          />
        </div>
      </div>

      <!-- Trend & Subtitle Footer -->
      <div v-if="trend || subtitle || $slots.footer" class="mt-4 flex items-center justify-between text-xs pt-3 border-t border-[var(--color-border-subtle)] dark:border-white/[0.04]">
        <slot name="footer">
          <div v-if="trend" class="flex items-center gap-1.5 font-medium">
            <span :class="trendClasses">
              {{ trendDirection === 'up' ? '↑' : trendDirection === 'down' ? '↓' : '•' }}
              {{ trend }}
            </span>
            <span v-if="trendPeriod" class="text-[var(--color-text-muted)] text-[11px]">
              {{ trendPeriod }}
            </span>
          </div>
          <span v-else-if="subtitle" class="text-[var(--color-text-muted)] text-[11px] truncate">
            {{ subtitle }}
          </span>
        </slot>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  mobileTitle: {
    type: String,
    default: '',
  },
  value: {
    type: [String, Number],
    required: true,
  },
  subtitle: {
    type: String,
    default: '',
  },
  trend: {
    type: String,
    default: '',
  },
  trendDirection: {
    type: String,
    default: 'up',
    validator: (v) => ['up', 'down', 'neutral'].includes(v),
  },
  trendPeriod: {
    type: String,
    default: '',
  },
  icon: {
    type: [Object, Function],
    default: null,
  },
  variant: {
    type: String,
    default: 'gold',
    validator: (v) => ['customer', 'gold', 'emerald', 'sky', 'rose', 'slate'].includes(v),
  },
  loading: {
    type: Boolean,
    default: false,
  },
  clickable: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['click']);

const iconWrapClasses = computed(() => {
  switch (props.variant) {
    case 'emerald':
      return 'bg-emerald-500/10 border-emerald-500/20 text-emerald-500';
    case 'sky':
      return 'bg-sky-500/10 border-sky-500/20 text-sky-500';
    case 'rose':
      return 'bg-rose-500/10 border-rose-500/20 text-rose-500';
    case 'slate':
      return 'bg-slate-500/10 border-slate-500/20 text-slate-400';
    case 'gold':
    default:
      return 'bg-amber-500/10 border-amber-500/20 text-amber-500';
  }
});

const iconColorClasses = computed(() => {
  switch (props.variant) {
    case 'emerald':
      return 'text-emerald-500 dark:text-emerald-400';
    case 'sky':
      return 'text-sky-500 dark:text-sky-400';
    case 'rose':
      return 'text-rose-500 dark:text-rose-400';
    case 'slate':
      return 'text-slate-500 dark:text-slate-400';
    case 'gold':
    default:
      return 'text-amber-500 dark:text-amber-400';
  }
});

const trendClasses = computed(() => {
  if (props.trendDirection === 'up') {
    return 'text-emerald-600 dark:text-emerald-400 font-semibold';
  }
  if (props.trendDirection === 'down') {
    return 'text-rose-600 dark:text-rose-400 font-semibold';
  }
  return 'text-[var(--color-text-muted)]';
});
</script>

