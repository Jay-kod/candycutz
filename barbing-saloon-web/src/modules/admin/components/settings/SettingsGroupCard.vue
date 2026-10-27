<script setup>
import { computed } from 'vue'
import {
  BuildingOfficeIcon,
  CalendarDaysIcon,
  MapPinIcon,
  ShareIcon,
  GlobeAltIcon,
  SparklesIcon,
  AdjustmentsVerticalIcon,
  InformationCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  categorizedSettings: {
    type: Object,
    default: () => ({}),
  },
  categories: {
    type: Array,
    default: () => [],
  },
  activeTab: {
    type: String,
    default: 'general',
  },
  searchQuery: {
    type: String,
    default: '',
  },
  rawSettings: {
    type: Object,
    required: true,
  },
})

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

const activeCategoryMeta = computed(() => {
  return props.categories.find(c => c.id === props.activeTab) || { id: props.activeTab, label: 'Configuration' }
})

const displayedCategories = computed(() => {
  if (props.searchQuery.trim()) {
    // Show all categories that have matching fields
    return props.categories.filter(c => (props.categorizedSettings[c.id] || []).length > 0)
  }
  // Show only active tab
  return props.categories.filter(c => c.id === props.activeTab)
})
</script>

<template>
  <div class="space-y-6">
    <!-- Notice if in search mode -->
    <div
      v-if="searchQuery"
      class="p-3.5 rounded-xl bg-white/[0.02] border border-white/[0.08] flex items-center justify-between text-xs text-zinc-400"
    >
      <span>Displaying search results matching <strong class="text-white">"{{ searchQuery }}"</strong></span>
      <span class="font-mono text-[11px] text-zinc-500">
        {{ displayedCategories.reduce((sum, c) => sum + (categorizedSettings[c.id]?.length || 0), 0) }} Settings Found
      </span>
    </div>

    <!-- Category Cards -->
    <div
      v-for="cat in displayedCategories"
      :key="cat.id"
      class="rounded-2xl bg-[#12161C] border border-white/[0.08] overflow-hidden shadow-xl"
    >
      <!-- Card Section Header -->
      <div class="px-6 py-4 border-b border-white/[0.08] flex items-center justify-between bg-white/[0.02]">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-[#FF6700]/10 border border-[#FF6700]/25 flex items-center justify-center">
            <component :is="getCategoryIcon(cat.id)" class="w-4 h-4 text-[#FF6700]" />
          </div>
          <div>
            <h2 class="text-sm font-bold text-white tracking-tight">{{ cat.label }}</h2>
            <p class="text-xs text-zinc-400">Manage parameters and values for this category</p>
          </div>
        </div>
        <span class="text-xs font-mono text-zinc-500">
          {{ (categorizedSettings[cat.id] || []).length }} Keys
        </span>
      </div>

      <!-- Form Grid -->
      <div class="p-6">
        <div
          v-if="!categorizedSettings[cat.id]?.length"
          class="py-8 text-center text-xs text-zinc-500"
        >
          No configuration parameters registered for this group.
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div
            v-for="field in categorizedSettings[cat.id]"
            :key="field.key"
            :class="[
              'space-y-1.5',
              field.type === 'textarea' ? 'md:col-span-2' : ''
            ]"
          >
            <div class="flex items-center justify-between">
              <label class="text-xs font-semibold text-zinc-300">
                {{ field.label }}
              </label>
              <span class="text-[10px] font-mono text-zinc-500">
                {{ field.key }}
              </span>
            </div>

            <!-- Textarea Control -->
            <textarea
              v-if="field.type === 'textarea'"
              v-model="rawSettings[field.key]"
              rows="3"
              :placeholder="field.placeholder"
              class="w-full p-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-[#FF6700]/50 focus:ring-1 focus:ring-[#FF6700]/30 transition-all font-sans leading-relaxed"
            ></textarea>

            <!-- Number Control -->
            <input
              v-else-if="field.type === 'number'"
              v-model="rawSettings[field.key]"
              type="number"
              :placeholder="field.placeholder"
              class="w-full px-4 py-2.5 rounded-xl bg-white/[0.03] border border-white/[0.08] text-xs font-mono text-white placeholder-zinc-500 focus:outline-none focus:border-[#FF6700]/50 focus:ring-1 focus:ring-[#FF6700]/30 transition-all"
            />

            <!-- Standard Text Control -->
            <input
              v-else
              v-model="rawSettings[field.key]"
              type="text"
              :placeholder="field.placeholder"
              class="w-full px-4 py-2.5 rounded-xl bg-white/[0.03] border border-white/[0.08] text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-[#FF6700]/50 focus:ring-1 focus:ring-[#FF6700]/30 transition-all"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
