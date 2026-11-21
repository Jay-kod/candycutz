<template>
  <div class="rounded-[2rem] border border-white/[0.08] bg-[#12161C] overflow-hidden shadow-2xl transition-all">
    <!-- Barber Card Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-6 py-5 bg-white/[0.02] border-b border-white/[0.06]">
      <!-- Barber Identity -->
      <div class="flex items-center gap-3.5">
        <div class="h-11 w-11 rounded-2xl bg-gradient-to-br from-admin/20 to-amber-500/10 border border-admin/30 flex items-center justify-center text-admin font-display font-bold text-lg shadow-[0_0_20px_rgba(255,103,0,0.15)]">
          {{ barberInitial }}
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="font-display text-lg font-bold text-white tracking-tight">{{ barber.barber_name }}</h2>
            <span 
              v-if="isWorkingToday" 
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-950/80 text-emerald-400 border border-emerald-500/30"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
              On Duty Today
            </span>
          </div>
          <p class="text-xs text-white/50 mt-0.5">
            {{ openDaysCount }} of 7 days scheduled • {{ weeklyOpenHours }} estimated active hours
          </p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-wrap items-center gap-2">
        <template v-if="isEditing">
          <!-- Template Preset CTA -->
          <button
            type="button"
            @click="$emit('applyDefaults', barber)"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold text-white/80 bg-white/5 hover:bg-white/10 border border-white/10 transition-colors"
            title="Mon–Sat 9:00 AM – 6:00 PM, Sun Closed"
          >
            Apply Template
          </button>

          <!-- Cancel CTA -->
          <button 
            type="button"
            @click="$emit('cancelEdit', barber)" 
            class="px-3.5 py-2 rounded-xl text-xs font-semibold text-white/60 hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 transition-colors"
          >
            Cancel
          </button>

          <!-- Save CTA -->
          <button 
            type="button"
            @click="$emit('save', barber)" 
            :disabled="isSaving"
            class="px-5 py-2 rounded-xl text-xs font-bold text-obsidian bg-gradient-to-r from-admin to-admin-light shadow-[0_4px_20px_rgba(255,103,0,0.25)] hover:shadow-[0_6px_28px_rgba(255,103,0,0.4)] disabled:opacity-50 transition-all flex items-center gap-2"
          >
            <span v-if="isSaving" class="h-3.5 w-3.5 border-2 border-obsidian border-t-transparent rounded-full animate-spin"></span>
            <span>{{ isSaving ? 'Saving Schedule...' : 'Save Schedule' }}</span>
          </button>
        </template>

        <button 
          v-else
          type="button"
          @click="$emit('startEdit', barber)" 
          class="px-4 py-2 rounded-xl text-xs font-bold text-admin bg-admin/10 hover:bg-admin/20 border border-admin/30 transition-all flex items-center gap-1.5"
        >
          <PencilSquareIcon class="h-4 w-4" />
          <span>Edit Schedule</span>
        </button>
      </div>
    </div>

    <!-- 7-Day Shifts Grid -->
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 p-5">
      <article 
        v-for="hour in barber.hours" 
        :key="hour.day_of_week" 
        class="relative overflow-hidden rounded-2xl border p-4 transition-all duration-300"
        :class="[
          hour.is_closed 
            ? 'border-white/[0.04] bg-white/[0.01] opacity-75' 
            : 'border-white/[0.08] bg-[#171C23] hover:border-admin/40',
          Number(hour.day_of_week) === todayIndex ? 'ring-1 ring-admin/40' : ''
        ]"
      >
        <div class="relative z-10 space-y-3">
          <!-- Day Name & Toggle/Badge -->
          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2.5">
              <div 
                class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0 border"
                :class="hour.is_closed 
                  ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' 
                  : 'bg-admin/15 text-admin border-admin/30'"
              >
                <CalendarDaysIcon v-if="!hour.is_closed" class="h-4 w-4" />
                <XCircleIcon v-else class="h-4 w-4" />
              </div>

              <div>
                <div class="flex items-center gap-1.5">
                  <h3 class="font-display text-sm font-semibold text-white">
                    {{ getDayName(hour.day_of_week) }}
                  </h3>
                  <span 
                    v-if="Number(hour.day_of_week) === todayIndex"
                    class="text-[9px] font-bold uppercase tracking-wider text-admin bg-admin/10 px-1.5 py-0.2 rounded"
                  >
                    Today
                  </span>
                </div>
              </div>
            </div>

            <!-- Toggle Switch (Edit Mode) -->
            <button 
              v-if="isEditing"
              type="button"
              @click="hour.is_closed = !hour.is_closed"
              class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border transition-colors duration-200 focus:outline-none"
              :class="hour.is_closed ? 'bg-white/[0.06] border-white/[0.08]' : 'bg-emerald-500/30 border-emerald-500/50'"
              :title="hour.is_closed ? 'Set open' : 'Set closed'"
            >
              <span 
                class="inline-block h-3.5 w-3.5 transform rounded-full shadow transition-transform duration-200 mt-0.5 ml-0.5"
                :class="hour.is_closed ? 'translate-x-0 bg-white/40' : 'translate-x-4 bg-emerald-400'"
              ></span>
            </button>

            <!-- Status Pill (View Mode) -->
            <span 
              v-else 
              class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border"
              :class="hour.is_closed 
                ? 'bg-rose-950/60 text-rose-400 border-rose-500/20' 
                : 'bg-emerald-950/60 text-emerald-400 border-emerald-500/20'"
            >
              {{ hour.is_closed ? 'Closed' : 'Open' }}
            </span>
          </div>
          
          <!-- Shift Time Details -->
          <div v-if="!hour.is_closed">
            <!-- Edit Inputs -->
            <div v-if="isEditing" class="space-y-2 pt-1">
              <div class="flex items-center gap-2">
                <label class="text-[10px] text-white/40 uppercase tracking-wider w-11 shrink-0">Open</label>
                <input 
                  v-model="hour.open_time" 
                  type="time" 
                  class="flex-1 rounded-lg border border-white/[0.08] bg-black/40 px-2.5 py-1 text-xs text-white outline-none focus:border-admin/50 transition-colors [color-scheme:dark]"
                />
              </div>
              <div class="flex items-center gap-2">
                <label class="text-[10px] text-white/40 uppercase tracking-wider w-11 shrink-0">Close</label>
                <input 
                  v-model="hour.close_time" 
                  type="time" 
                  class="flex-1 rounded-lg border border-white/[0.08] bg-black/40 px-2.5 py-1 text-xs text-white outline-none focus:border-admin/50 transition-colors [color-scheme:dark]"
                />
              </div>
            </div>

            <!-- View Badge -->
            <div v-else class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/[0.03] border border-white/[0.04]">
              <ClockIcon class="h-3.5 w-3.5 text-admin shrink-0" />
              <div class="flex items-center gap-1.5 text-xs font-mono font-medium text-white/90">
                <span>{{ formatTime(hour.open_time) }}</span>
                <span class="text-white/30">—</span>
                <span>{{ formatTime(hour.close_time) }}</span>
              </div>
            </div>
          </div>

          <!-- Closed Day Off Banner -->
          <div v-else class="px-3 py-2.5 rounded-xl bg-rose-500/[0.03] border border-rose-500/10 text-center">
            <p class="text-xs text-rose-300/60 font-medium">Off Duty</p>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
  CalendarDaysIcon, 
  ClockIcon, 
  XCircleIcon, 
  PencilSquareIcon 
} from '@heroicons/vue/24/outline';
import { DAY_NAMES, formatScheduleTime } from '../../composables/useAdminWorkingHours';

const props = defineProps({
  barber: {
    type: Object,
    required: true
  },
  isEditing: {
    type: Boolean,
    default: false
  },
  isSaving: {
    type: Boolean,
    default: false
  },
  todayIndex: {
    type: Number,
    default: 0
  }
});

defineEmits(['startEdit', 'cancelEdit', 'save', 'applyDefaults']);

const barberInitial = computed(() => {
  return props.barber.barber_name?.charAt(0).toUpperCase() || 'B';
});

const openDaysCount = computed(() => {
  return props.barber.hours?.filter(h => !h.is_closed).length || 0;
});

const isWorkingToday = computed(() => {
  const todayHour = props.barber.hours?.find(h => Number(h.day_of_week) === props.todayIndex);
  return todayHour && !todayHour.is_closed;
});

const weeklyOpenHours = computed(() => {
  if (!Array.isArray(props.barber.hours)) return 0;
  let totalMinutes = 0;
  props.barber.hours.forEach(h => {
    if (!h.is_closed && h.open_time && h.close_time) {
      const [oh, om] = h.open_time.split(':').map(Number);
      const [ch, cm] = h.close_time.split(':').map(Number);
      const diff = (ch * 60 + cm) - (oh * 60 + om);
      if (diff > 0) totalMinutes += diff;
    }
  });
  return Math.round(totalMinutes / 60);
});

const getDayName = (dayIndex) => {
  return DAY_NAMES[Number(dayIndex)] || `Day ${dayIndex}`;
};

const formatTime = (time) => {
  return formatScheduleTime(time);
};
</script>

