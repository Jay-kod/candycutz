<template>
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <!-- Status Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto custom-scrollbar pb-2 md:pb-0 hide-scrollbar">
      <button 
        v-for="tab in tabs" 
        :key="tab.value"
        @click="$emit('update:currentFilter', tab.value)"
        class="relative px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all whitespace-nowrap overflow-hidden"
        :class="currentFilter === tab.value ? 'text-white' : 'text-white/40 hover:text-white hover:bg-white/5'"
      >
        <div v-if="currentFilter === tab.value" class="absolute inset-0 bg-white/10 border border-white/20 rounded-xl"></div>
        <span class="relative z-10 flex items-center gap-2">
          {{ tab.label }}
          <span v-if="tab.count !== undefined" class="flex items-center justify-center h-5 px-1.5 rounded-md bg-black/40 text-[10px] tabular-nums" :class="currentFilter === tab.value ? 'text-white' : 'text-white/40'">
            {{ tab.count }}
          </span>
        </span>
      </button>
    </div>
    
    <div class="flex items-center gap-3 shrink-0">
      <!-- Source Filter Dropdown -->
      <div class="relative">
        <select 
          :value="sourceFilter"
          @change="$emit('update:sourceFilter', $event.target.value)"
          class="appearance-none bg-white/[0.03] border border-white/[0.08] text-xs font-bold text-white rounded-xl py-2.5 pl-4 pr-10 outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-2 focus:ring-admin/10 cursor-pointer"
        >
          <option value="all" class="bg-[#1a1a1a] text-white">All Sources</option>
          <option value="app" class="bg-[#1a1a1a] text-white">📱 Mobile App</option>
          <option value="web" class="bg-[#1a1a1a] text-white">🌐 Website</option>
          <option value="walk_in" class="bg-[#1a1a1a] text-white">🚶 Walk-In</option>
        </select>
        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
          <svg class="h-4 w-4 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>
      </div>

      <!-- Sorting Dropdown -->
      <div class="relative">
        <select 
          :value="sortOrder"
          @change="$emit('update:sortOrder', $event.target.value)"
          class="appearance-none bg-white/[0.03] border border-white/[0.08] text-xs font-bold text-white rounded-xl py-2.5 pl-4 pr-10 outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-2 focus:ring-admin/10 cursor-pointer"
        >
          <option value="newest_created" class="bg-[#1a1a1a] text-white">Newest First</option>
          <option value="oldest_created" class="bg-[#1a1a1a] text-white">Oldest First</option>
          <option value="upcoming_date" class="bg-[#1a1a1a] text-white">Date (Ascending)</option>
          <option value="past_date" class="bg-[#1a1a1a] text-white">Date (Descending)</option>
        </select>
        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
          <svg class="h-4 w-4 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  tabs: {
    type: Array,
    required: true
  },
  currentFilter: {
    type: String,
    default: 'all'
  },
  sourceFilter: {
    type: String,
    default: 'all'
  },
  sortOrder: {
    type: String,
    default: 'newest_created'
  }
});

defineEmits(['update:currentFilter', 'update:sourceFilter', 'update:sortOrder']);
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
