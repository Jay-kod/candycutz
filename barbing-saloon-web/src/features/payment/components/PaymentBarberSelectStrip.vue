<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#161B22]/70 p-3.5 backdrop-blur-md">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <!-- Left: Assigned Barber Info -->
      <div class="flex items-center gap-3">
        <div class="relative h-10 w-10 shrink-0 rounded-full overflow-hidden border border-amber-500/30 bg-[#0E1217]">
          <img
            v-if="selectedBarber?.avatar"
            :src="selectedBarber.avatar"
            :alt="selectedBarber.name"
            class="h-full w-full object-cover"
          />
          <div v-else class="flex h-full w-full items-center justify-center bg-amber-500/10 text-amber-500 font-bold text-xs">
            {{ getInitials(selectedBarber?.name || 'Master Barber') }}
          </div>
          <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-emerald-400 border border-black"></span>
        </div>

        <div>
          <span class="text-[10px] uppercase tracking-wider text-amber-400/90 font-bold block">
            Selected Master Stylist
          </span>
          <p class="text-xs sm:text-sm font-bold text-white leading-tight">
            {{ selectedBarber?.name || 'Assigned Master Barber' }}
            <span class="text-white/40 font-normal text-xs ml-1">
              (Chair #{{ selectedBarber?.chair_number || 1 }})
            </span>
          </p>
        </div>
      </div>

      <!-- Right: Switch / Select Barber Dropdown -->
      <div v-if="barbers.length > 1" class="flex items-center gap-2">
        <label for="barber-select" class="text-[11px] text-white/50 sr-only">Change Stylist</label>
        <select
          id="barber-select"
          :value="selectedBarberId"
          @change="$emit('update:selectedBarberId', Number($event.target.value))"
          class="rounded-xl border border-white/10 bg-[#0B0F13] px-3 py-1.5 text-xs text-white/90 outline-none focus:border-amber-500 transition-colors cursor-pointer"
        >
          <option
            v-for="b in barbers"
            :key="b.id"
            :value="b.id"
          >
            {{ b.name }} (Chair #{{ b.chair_number || 1 }})
          </option>
        </select>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  barbers: {
    type: Array,
    default: () => []
  },
  selectedBarber: {
    type: Object,
    default: null
  },
  selectedBarberId: {
    type: [Number, String],
    default: null
  }
});

defineEmits(['update:selectedBarberId']);

function getInitials(name) {
  if (!name) return 'MB';
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .substring(0, 2)
    .toUpperCase();
}
</script>
