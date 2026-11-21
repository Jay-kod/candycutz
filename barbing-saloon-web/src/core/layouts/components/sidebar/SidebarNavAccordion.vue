<template>
  <div class="space-y-3 py-2 px-2.5 custom-scrollbar">
    <div v-for="(group, gIdx) in groups" :key="gIdx" class="space-y-1">
      <!-- Section Label with Accordion Toggle -->
      <div
        v-if="group.label"
        class="flex items-center justify-between px-2.5 pt-2 pb-1 text-[10px] uppercase tracking-wider font-bold text-[var(--color-accent)] cursor-pointer select-none group/hdr"
        @click="toggleGroup(group.label)"
      >
        <span
          :class="[
            'transition-opacity duration-200',
            isCollapsed ? 'lg:opacity-0 lg:hidden' : 'opacity-80 group-hover/hdr:opacity-100'
          ]"
        >
          {{ group.label }}
        </span>
        <div v-if="!isCollapsed" class="flex items-center gap-1.5 ml-auto">
          <span
            v-if="group.items?.length"
            class="min-w-5 text-center text-[9px] px-1.5 py-0.2 rounded-full border border-amber-500/25 bg-amber-500/10 text-[var(--color-accent)] font-mono"
          >
            {{ group.items.length }}
          </span>
          <ChevronDownIcon
            v-if="isCollapsible(group.label)"
            :class="[
              'h-3 w-3 transition-transform duration-200 text-[var(--color-accent)]',
              isGroupOpen(group.label) ? 'rotate-180' : ''
            ]"
          />
        </div>
      </div>

      <!-- Group Nav Items (Collapsible transition) -->
      <nav v-show="isGroupOpen(group.label) || isCollapsed" class="space-y-0.5">
        <RouterLink
          v-for="item in group.items"
          :key="item.name"
          :to="item.to"
          :class="[
            'group relative flex items-center rounded-xl transition-all duration-200',
            isCollapsed ? 'justify-center p-2' : 'gap-3 px-3 py-2',
            isActiveRoute(item.to)
              ? [
                  portalConfig.activeGradient,
                  'border',
                  portalConfig.activeBorder,
                  'shadow-sm shadow-amber-500/5'
                ]
              : 'text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] hover:bg-[var(--color-bg-tertiary)] dark:hover:bg-white/[0.04] border border-transparent'
          ]"
          :title="isCollapsed ? item.name : ''"
        >
          <!-- Active Indicator Accent Pip -->
          <div
            v-if="isActiveRoute(item.to)"
            :class="['absolute left-0 top-1/2 -translate-y-1/2 h-4 w-1 rounded-r-full', portalConfig.activeIndicator]"
          ></div>

          <!-- Icon Container -->
          <div
            :class="[
              'relative flex h-7 w-7 shrink-0 items-center justify-center rounded-lg transition-all duration-200',
              isActiveRoute(item.to)
                ? [portalConfig.iconActive, 'border']
                : 'text-[var(--color-text-muted)] group-hover:text-[var(--color-text-primary)]'
            ]"
          >
            <component
              :is="item.icon"
              class="h-4 w-4 transition-transform duration-200 group-hover:scale-110"
            />
          </div>

          <!-- Item Label -->
          <span
            :class="[
              'text-[13px] leading-tight tracking-normal whitespace-nowrap transition-all duration-200',
              isCollapsed ? 'lg:opacity-0 lg:w-0 lg:hidden' : 'opacity-100',
              isActiveRoute(item.to) ? 'font-semibold text-[var(--color-text-primary)]' : 'font-medium'
            ]"
          >
            {{ item.name }}
          </span>

          <!-- Item Badge (e.g. 'CMS', 'New') -->
          <span
            v-if="item.badge && !isCollapsed"
            class="ml-auto text-[9px] font-bold px-1.5 py-0.5 rounded-full border border-amber-500/20 bg-amber-500/10 text-amber-500 dark:text-amber-400"
          >
            {{ item.badge }}
          </span>

          <!-- Active Arrow Indicator -->
          <ChevronRightIcon
            v-if="isActiveRoute(item.to) && !isCollapsed && !item.badge"
            class="ml-auto h-3.5 w-3.5 opacity-60 text-current"
          />
        </RouterLink>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ChevronDownIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  groups: { type: Array, required: true },
  portalConfig: { type: Object, required: true },
  isCollapsed: { type: Boolean, default: false },
  isActiveRoute: { type: Function, required: true },
});

// Groups that can be collapsed to prevent vertical overcrowding
const collapsibleGroups = new Set([
  'The Website',
  'Content Management',
  'System Governance',
  'Management & Security',
  'Discover',
  'Activity & Insights',
  'Activity',
  'Configuration',
]);

const isCollapsible = (label) => collapsibleGroups.has(label);

// State for open groups: default Operations and Overview to open, collapsible groups open if an active item is inside
const openGroups = ref(new Set(['Overview & Operations', 'Operations', 'Overview', 'Financials & Revenue', 'Menu']));

const isGroupOpen = (label) => {
  if (!isCollapsible(label)) return true;
  if (openGroups.value.has(label)) return true;
  // If group contains the currently active route, keep it open automatically
  const targetGroup = props.groups.find((g) => g.label === label);
  if (targetGroup?.items?.some((item) => props.isActiveRoute(item.to))) {
    openGroups.value.add(label);
    return true;
  }
  return false;
};

const toggleGroup = (label) => {
  if (!isCollapsible(label)) return;
  if (openGroups.value.has(label)) {
    openGroups.value.delete(label);
  } else {
    openGroups.value.add(label);
  }
};
</script>

