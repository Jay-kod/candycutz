<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in">
      <!-- Header Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/5 blur-3xl"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>
        
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-admin/70 font-bold">Schedule</p>
          <h1 class="mt-2 font-display text-4xl text-theme-text drop-shadow-lg">
            Working <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Hours</span>
          </h1>
          <p class="mt-2 text-sm text-ivory/60">View and manage each barber's working schedule.</p>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-20">
        <div class="h-8 w-8 border-2 border-admin border-t-transparent rounded-full animate-spin"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="barbers.length === 0" class="flex flex-col items-center justify-center py-20 text-center border border-dashed border-white/10 rounded-3xl bg-theme-surface/30">
        <ClockIcon class="h-12 w-12 text-ivory/20 mb-4" />
        <p class="text-ivory/60 font-medium">No working hours configured by any barber yet.</p>
        <p class="text-sm text-ivory/40 mt-1">Barbers need to set their schedules from their own dashboard first.</p>
      </div>

      <!-- Barber Schedules -->
      <div v-else class="space-y-8">
        <div v-for="barber in barbers" :key="barber.barber_id" class="rounded-2xl border border-white/10 bg-black/20 overflow-hidden">
          <!-- Barber Header -->
          <div class="flex items-center justify-between gap-4 px-6 py-4 bg-white/[0.02] border-b border-white/5">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-xl bg-admin/10 border border-admin/30 flex items-center justify-center">
                <UserIcon class="h-5 w-5 text-admin" />
              </div>
              <div>
                <h2 class="font-display text-lg text-theme-text">{{ barber.barber_name }}</h2>
                <p class="text-xs text-ivory/40">{{ barber.hours.filter(h => !h.is_closed).length }} of 7 days open</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button 
                v-if="editingBarber === barber.barber_id"
                @click="cancelEdit(barber)" 
                class="px-4 py-2 rounded-xl text-xs font-bold text-ivory/60 bg-white/5 hover:bg-white/10 border border-white/10 transition-all"
              >
                Cancel
              </button>
              <button 
                v-if="editingBarber === barber.barber_id"
                @click="saveBarberHours(barber)" 
                :disabled="savingBarber === barber.barber_id"
                class="px-5 py-2 rounded-xl text-xs font-bold text-obsidian bg-gradient-to-r from-admin to-admin-light hover:shadow-[0_0_20px_rgba(255,103,0,0.3)] transition-all disabled:opacity-50 flex items-center gap-2"
              >
                <span v-if="savingBarber === barber.barber_id" class="h-3 w-3 border-2 border-obsidian border-t-transparent rounded-full animate-spin"></span>
                {{ savingBarber === barber.barber_id ? 'Saving...' : 'Save Changes' }}
              </button>
              <button 
                v-else
                @click="startEdit(barber)" 
                class="px-5 py-2 rounded-xl text-xs font-bold text-admin bg-admin/10 hover:bg-admin/20 border border-admin/30 transition-all flex items-center gap-2"
              >
                <PencilSquareIcon class="h-4 w-4" />
                Edit
              </button>
            </div>
          </div>

          <!-- Days Grid -->
          <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 p-5">
            <article 
              v-for="hour in barber.hours" 
              :key="hour.day_of_week" 
              class="group relative overflow-hidden rounded-2xl border bg-black/20 p-5 backdrop-blur-sm transition-all duration-300"
              :class="hour.is_closed 
                ? 'border-white/5 opacity-60' 
                : 'border-admin/20 hover:border-admin/40 hover:shadow-[0_10px_30px_rgba(255,103,0,0.08)]'"
            >
              <div class="relative z-10 space-y-3">
                <!-- Day Name + Toggle -->
                <div class="flex items-center justify-between gap-2">
                  <div class="flex items-center gap-2.5">
                    <div class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0" :class="hour.is_closed ? 'bg-red-500/10 border border-red-500/20' : 'bg-admin/10 border border-admin/30'">
                      <CalendarDaysIcon v-if="!hour.is_closed" class="h-4 w-4 text-admin" />
                      <XCircleIcon v-else class="h-4 w-4 text-red-400" />
                    </div>
                    <h3 class="font-display text-base text-theme-text">{{ getDayName(hour.day_of_week) }}</h3>
                  </div>

                  <!-- Toggle switch (only in edit mode) -->
                  <button 
                    v-if="editingBarber === barber.barber_id"
                    @click="hour.is_closed = !hour.is_closed"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none shrink-0"
                    :class="hour.is_closed ? 'bg-white/10' : 'bg-emerald-500'"
                  >
                    <span 
                      class="inline-block h-4 w-4 transform rounded-full bg-white shadow-md transition-transform duration-200"
                      :class="hour.is_closed ? 'translate-x-1' : 'translate-x-6'"
                    ></span>
                  </button>
                  <!-- Status dot (view mode) -->
                  <div v-else class="flex items-center gap-1.5">
                    <div class="h-2 w-2 rounded-full" :class="hour.is_closed ? 'bg-red-500' : 'bg-emerald-500'"></div>
                    <span class="text-[10px] font-bold uppercase tracking-widest" :class="hour.is_closed ? 'text-red-400' : 'text-emerald-400'">
                      {{ hour.is_closed ? 'Closed' : 'Open' }}
                    </span>
                  </div>
                </div>
                
                <!-- Time Display / Edit -->
                <div v-if="!hour.is_closed">
                  <!-- Edit Mode -->
                  <div v-if="editingBarber === barber.barber_id" class="space-y-2">
                    <div class="flex items-center gap-2">
                      <label class="text-[10px] text-ivory/40 uppercase tracking-widest w-12 shrink-0">Open</label>
                      <input 
                        v-model="hour.open_time" 
                        type="time" 
                        class="flex-1 rounded-lg border border-white/10 bg-black/40 px-3 py-1.5 text-sm text-theme-text outline-none focus:border-admin/50 transition-colors [color-scheme:dark]"
                      />
                    </div>
                    <div class="flex items-center gap-2">
                      <label class="text-[10px] text-ivory/40 uppercase tracking-widest w-12 shrink-0">Close</label>
                      <input 
                        v-model="hour.close_time" 
                        type="time" 
                        class="flex-1 rounded-lg border border-white/10 bg-black/40 px-3 py-1.5 text-sm text-theme-text outline-none focus:border-admin/50 transition-colors [color-scheme:dark]"
                      />
                    </div>
                  </div>
                  <!-- View Mode -->
                  <div v-else class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/[0.03] border border-white/5">
                    <ClockIcon class="h-4 w-4 text-admin/60 shrink-0" />
                    <div class="flex items-center gap-1.5">
                      <span class="text-sm font-display text-admin-light">{{ formatTime(hour.open_time) }}</span>
                      <span class="text-ivory/30 text-xs">—</span>
                      <span class="text-sm font-display text-admin-light">{{ formatTime(hour.close_time) }}</span>
                    </div>
                  </div>
                </div>
                <div v-else class="px-3 py-2 rounded-lg bg-red-500/5 border border-red-500/10 text-center">
                  <p class="text-xs text-red-400/70">Not available</p>
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';
import { useToast } from '../../../core/composables/useToast';
import { CalendarDaysIcon, ClockIcon, XCircleIcon, UserIcon, PencilSquareIcon } from '@heroicons/vue/24/outline';

const barbers = ref([]);
const loading = ref(true);
const editingBarber = ref(null);
const savingBarber = ref(null);
const originalHours = ref(null); // snapshot for cancel
const toast = useToast();

const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

const getDayName = (dayIndex) => dayNames[dayIndex] || `Day ${dayIndex}`;

const formatTime = (time) => {
  if (!time) return '';
  const [hours, minutes] = time.split(':');
  const h = parseInt(hours);
  const ampm = h >= 12 ? 'PM' : 'AM';
  const h12 = h % 12 || 12;
  return `${h12}:${minutes} ${ampm}`;
};

// Ensure all 7 days exist (fill missing days with defaults)
function ensureAllDays(hours) {
  const map = {};
  hours.forEach(h => { map[h.day_of_week] = h; });
  const full = [];
  for (let d = 0; d < 7; d++) {
    full.push(map[d] || {
      id: null,
      day_of_week: d,
      open_time: '09:00',
      close_time: '17:00',
      is_closed: true,
    });
  }
  return full;
}

function startEdit(barber) {
  // Ensure all 7 days exist before editing
  barber.hours = ensureAllDays(barber.hours);
  // Take a deep copy snapshot for cancel
  originalHours.value = JSON.parse(JSON.stringify(barber.hours));
  editingBarber.value = barber.barber_id;
}

function cancelEdit(barber) {
  // Restore from snapshot
  if (originalHours.value) {
    barber.hours = JSON.parse(JSON.stringify(originalHours.value));
  }
  editingBarber.value = null;
  originalHours.value = null;
}

async function saveBarberHours(barber) {
  savingBarber.value = barber.barber_id;
  try {
    await adminApi.updateWorkingHours(barber.barber_id, barber.hours);
    toast.success(`${barber.barber_name}'s schedule updated`);
    editingBarber.value = null;
    originalHours.value = null;
  } catch (err) {
    const msg = err.response?.data?.error || 'Failed to save working hours';
    toast.error(msg);
  } finally {
    savingBarber.value = null;
  }
}

onMounted(async () => {
  try {
    const response = await adminApi.workingHours();
    barbers.value = response.data.data;
  } catch (err) {
    console.error('Failed to fetch working hours:', err);
  } finally {
    loading.value = false;
  }
});
</script>