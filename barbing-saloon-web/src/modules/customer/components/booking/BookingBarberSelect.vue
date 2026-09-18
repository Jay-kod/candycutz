<template>
  <div class="mt-6 pt-5 border-t border-white/10">
    <div class="flex items-center justify-between mb-4">
      <label class="text-xs font-semibold uppercase tracking-widest text-gold/80 block">Select Barber</label>
      <span class="text-xs text-ivory/40">{{ barbers.length }} Master Barbers Available</span>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div 
        v-for="b in barbers" 
        :key="b.id"
        @click="$emit('select', b.id)"
        class="flex items-center gap-4 rounded-xl border p-4 cursor-pointer transition-all duration-200"
        :class="selectedBarberId === b.id 
          ? 'bg-gold/10 border-gold shadow-[0_0_20px_rgba(212,175,55,0.15)] ring-1 ring-gold/30' 
          : 'bg-white/[0.03] border-white/5 hover:border-gold/30 hover:bg-white/[0.06]'"
      >
        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-full border-2" :class="selectedBarberId === b.id ? 'border-gold' : 'border-white/10'">
          <img v-if="b.avatar" :src="getFullImageUrl(b.avatar)" :alt="b.name" class="h-full w-full object-cover" />
          <div v-else class="h-full w-full bg-gold/10 flex items-center justify-center text-gold font-bold text-lg">
            {{ b.name?.charAt(0) || 'B' }}
          </div>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between">
            <p class="text-sm font-display font-bold text-theme-text truncate">{{ b.name }}</p>
            <span v-if="selectedBarberId === b.id" class="text-[10px] font-bold uppercase tracking-wider text-gold bg-gold/20 px-2 py-0.5 rounded-full">Selected</span>
          </div>
          <div class="flex items-center gap-3 text-xs text-ivory/50 mt-0.5">
            <span class="text-gold flex items-center gap-1">★ {{ b.rating || '5.0' }}</span>
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
