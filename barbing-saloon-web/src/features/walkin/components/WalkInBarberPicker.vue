<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#0B0F13]/90 p-6 sm:p-7 relative overflow-hidden group hover:border-admin/40 transition-all">
    <!-- Technical Corner Ticks -->
    <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-admin/30 group-hover:border-admin transition-colors"></span>

    <!-- Section Title -->
    <div class="flex items-center justify-between mb-6 pb-4 border-b border-white/[0.04]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-admin/10 border border-admin/20 flex items-center justify-center text-admin">
          <UserGroupIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="text-base font-bold text-white tracking-wide">Assign Stylist Chair</h2>
          <p class="text-xs text-ivory/50">Allocate barber for execution</p>
        </div>
      </div>

      <span class="text-[10px] font-mono text-ivory/40 uppercase tracking-widest">
        {{ barbers.length }} Stylists On Roster
      </span>
    </div>

    <!-- Barber Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
      <!-- Any Barber Option -->
      <div
        @click="selectBarber('')"
        class="group/barber cursor-pointer p-4 rounded-xl border transition-all relative flex items-center gap-3.5"
        :class="!modelValue 
          ? 'border-admin bg-admin/10 ring-1 ring-admin/40 shadow-lg shadow-admin/10' 
          : 'border-white/[0.06] bg-white/[0.02] hover:border-white/20 hover:bg-white/[0.04]'"
      >
        <div class="h-11 w-11 rounded-full bg-white/[0.06] border border-white/10 flex items-center justify-center text-sm font-bold text-white shrink-0">
          ✨
        </div>
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-1.5">
            <h3 class="text-xs font-bold text-white group-hover/barber:text-admin-light transition-colors truncate">
              First Available Chair
            </h3>
          </div>
          <p class="text-[10px] text-ivory/40 font-mono mt-0.5">Auto-assign first free stylist</p>
        </div>
      </div>

      <!-- Specific Barbers -->
      <div
        v-for="b in barbers"
        :key="b.id"
        @click="selectBarber(b.id)"
        class="group/barber cursor-pointer p-4 rounded-xl border transition-all relative flex items-center gap-3.5"
        :class="modelValue === b.id 
          ? 'border-admin bg-admin/10 ring-1 ring-admin/40 shadow-lg shadow-admin/10' 
          : 'border-white/[0.06] bg-white/[0.02] hover:border-white/20 hover:bg-white/[0.04]'"
      >
        <!-- Selected Check Badge -->
        <span 
          v-if="modelValue === b.id"
          class="absolute top-2 right-2 h-4 w-4 rounded-full bg-admin text-white flex items-center justify-center shadow"
        >
          <CheckIcon class="h-2.5 w-2.5 stroke-[3]" />
        </span>

        <!-- Avatar Initials -->
        <div class="h-11 w-11 rounded-full bg-gradient-to-br from-admin/40 to-admin/10 border border-admin/30 flex items-center justify-center text-xs font-bold text-white shrink-0">
          {{ getInitials(b.name) }}
        </div>

        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-1.5">
            <h3 class="text-xs font-bold text-white group-hover/barber:text-admin-light transition-colors truncate">
              {{ b.name }}
            </h3>
            <span class="text-[10px] text-amber-400 font-mono shrink-0">★ {{ b.rating || '5.0' }}</span>
          </div>
          <div class="flex items-center gap-1.5 mt-0.5">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
            <span class="text-[10px] text-emerald-400 font-mono">Available</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { UserGroupIcon, CheckIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  modelValue: { type: [Number, String], default: '' },
  barbers: { type: Array, default: () => [] }
});

const emit = defineEmits(['update:modelValue']);

const getInitials = (name) => {
  if (!name) return 'CC';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const selectBarber = (id) => {
  emit('update:modelValue', id);
};
</script>

