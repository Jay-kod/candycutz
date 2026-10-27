<template>
  <div class="mt-6 pt-5 border-t border-[var(--color-border)]">
    <div class="flex items-center justify-between mb-4">
      <label class="text-xs font-semibold uppercase tracking-widest text-[var(--color-accent)] block">Select Barber</label>
      <span class="text-xs text-[var(--color-text-muted)]">{{ barbers.length }} Master Barbers Available</span>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div 
        v-for="b in barbers" 
        :key="b.id"
        @click="$emit('select', b.id)"
        class="flex items-center gap-4 rounded-xl border p-4 cursor-pointer transition-all duration-200"
        :class="selectedBarberId === b.id 
          ? 'bg-[var(--color-accent)]/10 border-[var(--color-accent)] shadow-[0_0_20px_rgba(255,153,0,0.15)] ring-1 ring-[var(--color-accent)]/30' 
          : 'bg-[var(--color-bg-tertiary)]/50 border-[var(--color-border)] hover:border-[var(--color-accent)]/40 hover:bg-[var(--color-bg-tertiary)]'"
      >
        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-full border-2" :class="selectedBarberId === b.id ? 'border-[var(--color-accent)]' : 'border-[var(--color-border)]'">
          <img v-if="b.avatar" :src="getFullImageUrl(b.avatar)" :alt="b.name" class="h-full w-full object-cover" />
          <div v-else class="h-full w-full bg-[var(--color-accent)]/10 flex items-center justify-center text-[var(--color-accent)] font-bold text-lg">
            {{ b.name?.charAt(0) || 'B' }}
          </div>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between">
            <p class="text-sm font-display font-bold text-[var(--color-text-primary)] truncate">{{ b.name }}</p>
            <span v-if="selectedBarberId === b.id" class="text-[10px] font-bold uppercase tracking-wider text-[var(--color-accent)] bg-[var(--color-accent)]/20 px-2 py-0.5 rounded-full">Selected</span>
          </div>
          <div class="flex items-center gap-3 text-xs text-[var(--color-text-secondary)] mt-0.5">
            <span class="text-[var(--color-accent)] flex items-center gap-1">★ {{ b.rating || '5.0' }}</span>
            <span>•</span>
            <span>{{ b.years_experience || 5 }}+ yrs exp</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  barbers: { type: Array, default: () => [] },
  selectedBarberId: { type: [String, Number], default: '' },
  getFullImageUrl: { type: Function, required: true }
})

defineEmits(['select'])
</script>
