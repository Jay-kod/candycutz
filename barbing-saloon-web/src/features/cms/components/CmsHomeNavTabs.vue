<template>
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <!-- Section Navigation Pills -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-2 sm:pb-0 hide-scrollbar">
      <button 
        v-for="tab in tabs" 
        :key="tab.id"
        @click="$emit('update:activeTab', tab.id)"
        class="relative px-3.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all whitespace-nowrap overflow-hidden flex items-center gap-2"
        :class="activeTab === tab.id 
          ? 'bg-admin text-obsidian shadow-[0_2px_10px_rgba(255,103,0,0.3)]' 
          : 'bg-white/[0.03] border border-white/[0.06] text-white/50 hover:text-white hover:bg-white/[0.06]'"
      >
        <component :is="tab.icon" class="h-3.5 w-3.5" :class="activeTab === tab.id ? 'text-obsidian' : 'text-white/60'" />
        <span>{{ tab.label }}</span>
        <span 
          v-if="tab.badge" 
          class="flex items-center justify-center h-4 px-1.5 rounded-full text-[10px] tabular-nums font-mono font-bold" 
          :class="activeTab === tab.id ? 'bg-black/25 text-obsidian' : 'bg-white/10 text-white/60'"
        >
          {{ tab.badge }}
        </span>
      </button>
    </div>

    <!-- Right: Quick Live Preview Switcher Button -->
    <div class="flex items-center gap-2.5 shrink-0 self-end sm:self-auto">
      <button
        @click="$emit('update:activeTab', activeTab === 'preview' ? 'all' : 'preview')"
        class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold transition-all border"
        :class="activeTab === 'preview' 
          ? 'bg-amber-400 text-obsidian border-amber-400 shadow-[0_2px_10px_rgba(251,191,36,0.3)]' 
          : 'bg-white/[0.03] border-white/[0.08] text-white/70 hover:text-white hover:bg-white/[0.06]'"
      >
        <EyeIcon class="h-4 w-4" />
        <span>{{ activeTab === 'preview' ? 'Exit Preview' : 'Interactive Preview' }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { 
  Squares2X2Icon, 
  PhotoIcon, 
  SparklesIcon, 
  BookmarkSquareIcon, 
  UserGroupIcon,
  EyeIcon
} from '@heroicons/vue/24/outline';

defineProps({
  activeTab: {
    type: String,
    default: 'all'
  }
});

defineEmits(['update:activeTab']);

const tabs = [
  { id: 'all', label: 'All Sections', icon: Squares2X2Icon },
  { id: 'hero', label: 'Hero Showcase', icon: PhotoIcon, badge: 'Main' },
  { id: 'stats', label: 'Social Proof', icon: SparklesIcon, badge: '4' },
  { id: 'about', label: 'Philosophy', icon: BookmarkSquareIcon },
  { id: 'portal', label: 'Portal Gateway', icon: UserGroupIcon },
  { id: 'preview', label: 'Live Preview', icon: EyeIcon }
];
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>

