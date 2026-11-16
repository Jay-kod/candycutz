<template>
  <div class="rounded-lg border border-white/[0.06] bg-[#12161F]/80 px-2.5 sm:px-3 py-1.5 backdrop-blur-md">
    <div class="flex items-center justify-between gap-2 flex-wrap sm:flex-nowrap">
      <!-- Left: Assigned Barber Info & Service Context -->
      <div class="flex items-center gap-2 min-w-0">
        <!-- Avatar / Initials with Green Status -->
        <div class="relative h-7 w-7 shrink-0 rounded-full overflow-hidden border border-amber-500/40 bg-[#0E1217] shadow-sm">
          <img
            v-if="selectedBarber?.avatar"
            :src="selectedBarber.avatar"
            :alt="selectedBarber.name"
            class="h-full w-full object-cover"
          />
          <div v-else class="flex h-full w-full items-center justify-center bg-amber-500/15 text-amber-400 font-bold text-[10px]">
            {{ getInitials(selectedBarber?.name || 'Master Barber') }}
          </div>
          <span class="absolute bottom-0 right-0 h-2 w-2 rounded-full bg-emerald-400 border-2 border-[#12161F]"></span>
        </div>

        <!-- Stylist & Chair Meta -->
        <div class="min-w-0">
          <span class="text-[8px] uppercase tracking-wider text-amber-400/90 font-bold block leading-none">
            Selected Master Stylist
          </span>
          <p class="text-xs font-semibold text-white leading-tight truncate">
            {{ selectedBarber?.name || 'Assigned Master Barber' }}
            <span class="text-white/40 font-normal text-[10px] ml-0.5">
              (#{{ selectedBarber?.chair_number || 1 }})
            </span>
          </p>
        </div>

        <!-- Service / Appointment Pill -->
        <div
          v-if="appointment?.service?.name"
          class="hidden md:flex items-center gap-1 px-2 py-0.5 rounded bg-white/[0.04] border border-white/[0.06] text-[10px] shrink-0"
        >
          <span class="text-[9px] uppercase font-bold text-white/40 tracking-wider">Service:</span>
          <span class="font-semibold text-amber-300 truncate max-w-[120px]">{{ appointment.service.name }}</span>
          <span v-if="appointment.appointment_time" class="text-white/30 text-[9px] ml-0.5">
            • {{ appointment.appointment_date }}
          </span>
        </div>
      </div>

      <!-- Right: Switch Barber Dropdown & Encryption Badge -->
      <div class="flex items-center gap-2 shrink-0 ml-auto sm:ml-0">
        <div v-if="barbers.length > 1" class="flex items-center gap-1">
          <label for="barber-select" class="text-[10px] text-white/50 sr-only">Change Stylist</label>
          <select
            id="barber-select"
            :value="selectedBarberId"
            @change="$emit('update:selectedBarberId', Number($event.target.value))"
            class="rounded-md border border-white/10 bg-[#0B0F13] px-2 py-0.5 text-[10px] sm:text-[11px] text-white/90 outline-none focus:border-amber-500 transition-colors cursor-pointer max-w-[130px] sm:max-w-none truncate"
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

        <div class="hidden sm:flex items-center gap-1 text-[10px] text-white/40 font-medium font-mono">
          <svg class="h-3 w-3 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd" />
          </svg>
          <span>SSL Secured</span>
        </div>
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
  },
  appointment: {
    type: Object,
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
