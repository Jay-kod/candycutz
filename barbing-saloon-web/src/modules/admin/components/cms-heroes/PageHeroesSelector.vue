<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-4 sm:p-5 backdrop-blur-xl shadow-xl space-y-3.5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-white/[0.06] pb-3">
      <div class="flex items-center gap-2">
        <DocumentTextIcon class="h-4 w-4 text-[#FF6700]" />
        <span class="text-xs font-bold uppercase tracking-wider text-white">Select Page Target</span>
      </div>
      <span class="text-[11px] text-white/40">
        Editing: <strong class="text-white">{{ activePageLabel }}</strong>
      </span>
    </div>

    <!-- Desktop / Tablet Interactive Pills -->
    <div class="hidden sm:flex flex-wrap gap-2">
      <button
        v-for="page in pages"
        :key="page.key"
        type="button"
        @click="$emit('select-page', page.key)"
        class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-semibold transition-all"
        :class="[
          selectedPage === page.key
            ? 'bg-gradient-to-r from-[#FF6700]/20 to-amber-500/20 text-[#FF6700] border border-[#FF6700]/40 shadow-sm shadow-[#FF6700]/10 font-bold'
            : 'border border-white/[0.06] bg-white/[0.02] text-white/60 hover:bg-white/[0.05] hover:text-white'
        ]"
      >
        <span
          class="h-1.5 w-1.5 rounded-full"
          :class="selectedPage === page.key ? 'bg-[#FF6700]' : 'bg-white/30'"
        ></span>
        <span>{{ page.label }}</span>
      </button>
    </div>

    <!-- Mobile Dropdown Selector -->
    <div class="sm:hidden">
      <select
        :value="selectedPage"
        @change="$emit('select-page', $event.target.value)"
        class="w-full rounded-xl border border-white/[0.08] bg-[#0B0F13] px-3.5 py-2.5 text-xs text-white outline-none focus:border-[#FF6700]/50"
      >
        <option v-for="page in pages" :key="page.key" :value="page.key">
          {{ page.label }}
        </option>
      </select>
    </div>

    <p class="text-[11px] text-white/40 leading-relaxed pt-1">
      Choose a target page to customise its headline, subtitle narrative, uploaded photography cover, and dark overlay contrast.
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { DocumentTextIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  pages: {
    type: Array,
    required: true
  },
  selectedPage: {
    type: String,
    required: true
  }
});

defineEmits(['select-page']);

const activePageLabel = computed(() => {
  const p = props.pages.find(item => item.key === props.selectedPage);
  return p ? p.label : 'Home';
});
</script>

