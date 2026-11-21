<template>
  <div class="space-y-4">
    <!-- Source Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto hide-scrollbar pb-1 border-b border-slate-200 dark:border-white/[0.06] pt-1">
      <button
        @click="$emit('update:sourceFilter', 'all')"
        class="shrink-0 rounded-2xl px-4 py-2 text-xs font-bold uppercase tracking-wider border transition-all whitespace-nowrap flex items-center gap-2"
        :class="
          sourceFilter === 'all'
            ? 'bg-gradient-to-r from-gold via-amber-400 to-amber-500 text-obsidian border-transparent shadow-[0_0_15px_rgba(255,153,0,0.3)]'
            : 'bg-white dark:bg-[#12161C] text-slate-600 dark:text-slate-400 border-slate-200 dark:border-white/[0.08] hover:bg-slate-50 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white'
        "
      >
        <span>All Services</span>
        <span
          class="px-2 py-0.5 rounded-full text-[10px]"
          :class="sourceFilter === 'all' ? 'bg-[#0B0F13]/30 text-[#0B0F13]' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-400'"
        >
          {{ totalCount }}
        </span>
      </button>

      <button
        @click="$emit('update:sourceFilter', 'admin')"
        class="shrink-0 rounded-2xl px-4 py-2 text-xs font-bold uppercase tracking-wider border transition-all whitespace-nowrap flex items-center gap-2"
        :class="
          sourceFilter === 'admin'
            ? 'bg-gradient-to-r from-gold via-amber-400 to-amber-500 text-obsidian border-transparent shadow-[0_0_15px_rgba(255,153,0,0.3)]'
            : 'bg-white dark:bg-[#12161C] text-slate-600 dark:text-slate-400 border-slate-200 dark:border-white/[0.08] hover:bg-slate-50 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white'
        "
      >
        <span>Salon Services (Admin Added)</span>
        <span
          class="px-2 py-0.5 rounded-full text-[10px]"
          :class="sourceFilter === 'admin' ? 'bg-[#0B0F13]/30 text-[#0B0F13]' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-400'"
        >
          {{ adminCount }}
        </span>
      </button>

      <button
        @click="$emit('update:sourceFilter', 'mine')"
        class="shrink-0 rounded-2xl px-4 py-2 text-xs font-bold uppercase tracking-wider border transition-all whitespace-nowrap flex items-center gap-2"
        :class="
          sourceFilter === 'mine'
            ? 'bg-gradient-to-r from-amber-500 to-orange-400 text-[#0B0F13] border-transparent shadow-[0_0_15px_rgba(245,158,11,0.3)]'
            : 'bg-white dark:bg-[#12161C] text-slate-600 dark:text-slate-400 border-slate-200 dark:border-white/[0.08] hover:bg-slate-50 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white'
        "
      >
        <span>My Custom Services</span>
        <span
          class="px-2 py-0.5 rounded-full text-[10px]"
          :class="sourceFilter === 'mine' ? 'bg-[#0B0F13]/30 text-[#0B0F13]' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-400'"
        >
          {{ mineCount }}
        </span>
      </button>

      <button
        v-if="pendingCount > 0"
        @click="$emit('update:sourceFilter', 'pending')"
        class="shrink-0 rounded-2xl px-4 py-2 text-xs font-bold uppercase tracking-wider border transition-all whitespace-nowrap flex items-center gap-2"
        :class="
          sourceFilter === 'pending'
            ? 'bg-gradient-to-r from-amber-500 to-amber-400 text-[#0B0F13] border-transparent shadow-[0_0_15px_rgba(245,158,11,0.3)]'
            : 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-500/30 hover:bg-amber-500/20'
        "
      >
        <span class="flex items-center gap-1.5">
          <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
          Pending Approval
        </span>
        <span
          class="px-2 py-0.5 rounded-full text-[10px]"
          :class="sourceFilter === 'pending' ? 'bg-[#0B0F13]/30 text-[#0B0F13]' : 'bg-amber-500/20 text-amber-800 dark:text-amber-300 font-bold'"
        >
          {{ pendingCount }}
        </span>
      </button>
    </div>

    <!-- Category Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto hide-scrollbar pb-1">
      <button
        @click="$emit('update:activeCategory', 'all')"
        class="shrink-0 rounded-xl px-4 py-2 text-xs font-bold uppercase tracking-wider border transition-all whitespace-nowrap"
        :class="
          activeCategory === 'all'
            ? 'bg-gold/15 text-amber-800 dark:text-gold border-gold/40 shadow-[0_0_15px_rgba(255,153,0,0.15)] font-extrabold'
            : 'bg-white dark:bg-[#12161C] text-slate-600 dark:text-slate-400 border-slate-200 dark:border-white/[0.08] hover:bg-slate-50 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white'
        "
        data-test="tab-all"
      >
        All Treatments
        <span class="ml-1.5 text-[10px] opacity-75">{{ totalCount }}</span>
      </button>

      <button
        v-for="cat in categories"
        :key="cat"
        @click="$emit('update:activeCategory', cat)"
        class="shrink-0 rounded-xl px-4 py-2 text-xs font-bold uppercase tracking-wider border transition-all whitespace-nowrap"
        :class="
          activeCategory === cat
            ? 'bg-gold/15 text-amber-800 dark:text-gold border-gold/40 shadow-[0_0_15px_rgba(255,153,0,0.15)] font-extrabold'
            : 'bg-white dark:bg-[#12161C] text-slate-600 dark:text-slate-400 border-slate-200 dark:border-white/[0.08] hover:bg-slate-50 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white'
        "
        data-test="category-tab"
      >
        {{ cat }}
        <span class="ml-1.5 text-[10px] opacity-75">
          {{ countCategory(cat) }}
        </span>
      </button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  categories: {
    type: Array,
    required: true,
  },
  activeCategory: {
    type: String,
    default: 'all',
  },
  sourceFilter: {
    type: String,
    default: 'all',
  },
  totalCount: {
    type: Number,
    default: 0,
  },
  adminCount: {
    type: Number,
    default: 0,
  },
  mineCount: {
    type: Number,
    default: 0,
  },
  pendingCount: {
    type: Number,
    default: 0,
  },
  services: {
    type: Array,
    default: () => [],
  },
});

defineEmits(['update:activeCategory', 'update:sourceFilter']);

function countCategory(cat) {
  return (props.services || []).filter(
    (s) => (s.category_name || s.category?.name || 'General') === cat
  ).length;
}
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
