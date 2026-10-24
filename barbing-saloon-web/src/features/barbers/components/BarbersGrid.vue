<template>
  <div>
    <!-- Barbers Grid -->
    <div v-if="barbers.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <BarberCard
        v-for="barber in barbers"
        :key="barber.id"
        :barber="barber"
        @delete="$emit('delete', $event)"
        @status-change="(b, s) => $emit('status-change', b, s)"
      />
    </div>

    <!-- Empty State -->
    <div v-else class="rounded-3xl border border-white/[0.08] bg-[#12161C] p-16 flex flex-col items-center justify-center text-center">
      <div class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-white/[0.03] border border-white/[0.08] mb-4">
        <ScissorsIcon class="h-8 w-8 text-white/30" />
      </div>
      <h3 class="text-lg font-bold text-white mb-1.5">No Barbers Found</h3>
      <p class="text-xs text-white/40 max-w-sm mx-auto mb-5">
        {{ search ? `No stylists match your search "${search}".` : `No staff members found matching the selected filters.` }}
      </p>
      <div class="flex items-center gap-3">
        <button 
          v-if="search || currentFilter !== 'all' || specialtyFilter !== 'all'" 
          @click="$emit('reset-filters')" 
          class="px-4 py-2 rounded-xl bg-white/[0.05] border border-white/10 text-xs font-semibold text-white hover:bg-white/10 transition-all"
        >
          Reset Filters
        </button>
        <router-link 
          to="/admin/barbers/new" 
          class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-admin to-admin-light text-xs font-bold text-obsidian shadow-md hover:shadow-lg transition-all"
        >
          <UserPlusIcon class="h-4 w-4" />
          <span>Add New Stylist</span>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import BarberCard from './BarberCard.vue';
import { ScissorsIcon, UserPlusIcon } from '@heroicons/vue/24/outline';

defineProps({
  barbers: {
    type: Array,
    required: true
  },
  search: {
    type: String,
    default: ''
  },
  currentFilter: {
    type: String,
    default: 'all'
  },
  specialtyFilter: {
    type: String,
    default: 'all'
  }
});

defineEmits(['delete', 'status-change', 'reset-filters']);
</script>