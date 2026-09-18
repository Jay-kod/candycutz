<template>
  <div class="flex items-center gap-2 overflow-x-auto hide-scrollbar pb-2">
    <button 
      v-for="tab in tabs" 
      :key="tab.value"
      @click="$emit('update:currentFilter', tab.value)"
      class="relative px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all whitespace-nowrap overflow-hidden"
      :class="currentFilter === tab.value ? 'text-obsidian bg-gold shadow-[0_0_15px_rgba(212,175,55,0.3)]' : 'text-ivory/50 hover:text-ivory bg-theme-surface/60 border border-theme-border hover:bg-theme-surface'"
    >
      <span class="relative z-10 flex items-center gap-2">
        {{ tab.label }}
        <span class="flex items-center justify-center h-5 px-1.5 rounded-md text-[10px] tabular-nums" :class="currentFilter === tab.value ? 'bg-black/20 text-obsidian' : 'bg-black/40 text-ivory/50'">
          {{ tab.count }}
        </span>
      </span>
    </button>
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
  }
});

defineEmits(['update:currentFilter']);
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
