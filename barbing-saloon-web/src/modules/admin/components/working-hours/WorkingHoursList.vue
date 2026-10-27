<template>
  <div>
    <!-- Barber Schedules Stack -->
    <div 
      v-if="barbers.length > 0"
      class="space-y-6"
    >
      <BarberScheduleCard
        v-for="barber in barbers"
        :key="barber.barber_id"
        :barber="barber"
        :is-editing="editingBarberId === barber.barber_id"
        :is-saving="savingBarberId === barber.barber_id"
        :today-index="todayIndex"
        @start-edit="$emit('startEdit', $event)"
        @cancel-edit="$emit('cancelEdit', $event)"
        @save="$emit('save', $event)"
        @apply-defaults="$emit('applyDefaults', $event)"
      />
    </div>

    <!-- Empty State: Search Query -->
    <div 
      v-else-if="searchQuery"
      class="flex flex-col items-center justify-center py-20 px-4 text-center rounded-3xl border border-dashed border-white/10 bg-[#12161C]/50 backdrop-blur-sm"
    >
      <div class="h-16 w-16 rounded-2xl bg-white/[0.04] border border-white/[0.08] flex items-center justify-center text-white/40 mb-4">
        <MagnifyingGlassIcon class="h-8 w-8" />
      </div>
      <h3 class="font-display text-xl text-white font-semibold mb-2">No Matching Barbers</h3>
      <p class="text-sm text-white/50 max-w-md mb-6">
        No staff members found matching "<span class="text-white font-medium">{{ searchQuery }}</span>". Try searching by another name.
      </p>
      <button
        @click="$emit('reset')"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-white/10 bg-white/5 text-xs font-semibold text-white hover:bg-white/10 transition-colors"
      >
        <ArrowPathIcon class="h-4 w-4" />
        <span>Clear Search</span>
      </button>
    </div>

    <!-- Empty State: Filter Conditions -->
    <div 
      v-else-if="dayFilter !== 'all' || statusFilter !== 'all'"
      class="flex flex-col items-center justify-center py-20 px-4 text-center rounded-3xl border border-dashed border-white/10 bg-[#12161C]/50 backdrop-blur-sm"
    >
      <div class="h-16 w-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 mb-4">
        <ClockIcon class="h-8 w-8" />
      </div>
      <h3 class="font-display text-xl text-white font-semibold mb-2">No Barbers Match Shift Criteria</h3>
      <p class="text-sm text-white/50 max-w-md mb-6">
        No barbers have active shifts matching the selected day or availability filter.
      </p>
      <button
        @click="$emit('reset')"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-white/10 bg-white/5 text-xs font-semibold text-white hover:bg-white/10 transition-colors"
      >
        <span>Reset Shift Filters</span>
      </button>
    </div>

    <!-- Empty State: Zero Staff Overall -->
    <div 
      v-else
      class="flex flex-col items-center justify-center py-24 px-4 text-center rounded-3xl border border-dashed border-white/10 bg-[#12161C]/50 backdrop-blur-sm"
    >
      <div class="h-20 w-20 rounded-3xl bg-admin/10 border border-admin/20 flex items-center justify-center text-admin mb-5 shadow-[0_0_30px_rgba(255,103,0,0.15)]">
        <ClockIcon class="h-10 w-10" />
      </div>
      <h3 class="font-display text-2xl text-white font-semibold mb-2">No Working Hours Configured</h3>
      <p class="text-sm text-white/50 max-w-md mb-6 leading-relaxed">
        Barber profiles need to be added to the saloon roster first. Once added, you can customize individual shifts and operating days here.
      </p>
      <router-link
        to="/admin/barbers/new"
        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-admin to-admin-light text-obsidian text-xs font-bold shadow-[0_4px_25px_rgba(255,103,0,0.3)] hover:shadow-[0_6px_35px_rgba(255,103,0,0.45)] hover:scale-105 transition-all"
      >
        <span>Add First Barber Profile</span>
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { 
  MagnifyingGlassIcon, 
  ArrowPathIcon, 
  ClockIcon 
} from '@heroicons/vue/24/outline';
import BarberScheduleCard from './BarberScheduleCard.vue';

defineProps({
  barbers: {
    type: Array,
    required: true
  },
  searchQuery: {
    type: String,
    default: ''
  },
  dayFilter: {
    type: String,
    default: 'all'
  },
  statusFilter: {
    type: String,
    default: 'all'
  },
  editingBarberId: {
    type: [Number, String],
    default: null
  },
  savingBarberId: {
    type: [Number, String],
    default: null
  },
  todayIndex: {
    type: Number,
    default: 0
  }
});

defineEmits(['startEdit', 'cancelEdit', 'save', 'applyDefaults', 'reset']);
</script>
