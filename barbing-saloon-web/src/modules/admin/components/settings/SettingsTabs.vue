<script setup>
import {
  MagnifyingGlassIcon,
  XMarkIcon,
  BuildingOfficeIcon,
  CalendarDaysIcon,
  MapPinIcon,
  ShareIcon,
  GlobeAltIcon,
  SparklesIcon,
  AdjustmentsVerticalIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
  activeTab: {
    type: String,
    default: 'general',
  },
  categoryCounts: {
    type: Object,
    default: () => ({}),
  },
  searchQuery: {
    type: String,
    default: '',
  },
})

defineEmits(['update:activeTab', 'update:searchQuery'])

const getCategoryIcon = (id) => {
  switch (id) {
    case 'general': return BuildingOfficeIcon
    case 'booking': return CalendarDaysIcon
    case 'contact': return MapPinIcon
    case 'social': return ShareIcon
    case 'seo': return GlobeAltIcon
    case 'brand': return SparklesIcon
    default: return AdjustmentsVerticalIcon
  }
}
</script>

<template>
  <div class="space-y-4">
    <!-- Search Bar & Tab Bar Layout -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
      <!-- Horizontal Scrollable Tab Pills -->
      <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 md:pb-0">
        <button
          v-for="cat in categories"
          :key="cat.id"
          type="button"
          @click="$emit('update:activeTab', cat.id)"
          :class="[
            'inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all border shrink-0',
            activeTab === cat.id && !searchQuery
              ? 'bg-[#FF6700]/15 border-[#FF6700]/40 text-[#FF6700] shadow-sm shadow-[#FF6700]/10'
              : 'bg-[#12161C] border-white/[0.08] text-zinc-400 hover:text-white hover:bg-white/[0.04]'
          ]"
        >
          <component :is="getCategoryIcon(cat.id)" class="w-3.5 h-3.5" />
          <span>{{ cat.label }}</span>
          <span
            v-if="categoryCounts[cat.id] !== undefined"
            :class="[
              'px-1.5 py-0.2 rounded-full text-[10px] font-mono',
              activeTab === cat.id && !searchQuery
                ? 'bg-[#FF6700]/20 text-[#FF6700]'
                : 'bg-white/[0.04] text-zinc-500'
            ]"
          >
            {{ categoryCounts[cat.id] }}
          </span>
        </button>
      </div>

      <!-- Real-time Filter Search Box -->
      <div class="relative w-full md:w-64 shrink-0">
        <MagnifyingGlassIcon class="w-3.5 h-3.5 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
        <input
          :value="searchQuery"
          @input="$emit('update:searchQuery', $event.target.value)"
          type="text"
          placeholder="Filter configuration keys…"
          class="w-full pl-8 pr-7 py-2 rounded-xl bg-[#12161C] border border-white/[0.08] text-white text-xs placeholder-zinc-500 focus:outline-none focus:border-[#FF6700]/40 transition-colors"
        />
        <button
          v-if="searchQuery"
          type="button"
          @click="$emit('update:searchQuery', '')"
          class="absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-white transition-colors"
        >
          <XMarkIcon class="w-3.5 h-3.5" />
        </button>
      </div>
    </div>
  </div>
</template>

