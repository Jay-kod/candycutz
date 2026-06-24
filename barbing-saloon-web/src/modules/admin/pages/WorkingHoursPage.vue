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
          <p class="mt-2 text-sm text-ivory/60">Set when the saloon is open and closed for business.</p>
        </div>
      </div>

      <!-- Days Grid -->
      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article 
          v-for="hour in workingHours" 
          :key="hour.id" 
          class="group relative overflow-hidden rounded-2xl border bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:bg-black/40 hover:-translate-y-1"
          :class="hour.is_closed ? 'border-white/5 opacity-60 hover:opacity-100' : 'border-admin/20 hover:border-admin/40 hover:shadow-[0_10px_30px_rgba(255,103,0,0.08)]'"
        >
          <div class="absolute inset-0 bg-gradient-to-br from-admin/0 via-admin/[0.02] to-admin/5 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
          
          <div class="relative z-10">
            <div class="flex items-center justify-between gap-3 mb-4">
              <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl flex items-center justify-center shrink-0" :class="hour.is_closed ? 'bg-red-500/10 border border-red-500/20' : 'bg-admin/10 border border-admin/30'">
                  <CalendarDaysIcon v-if="!hour.is_closed" class="h-5 w-5 text-admin" />
                  <XCircleIcon v-else class="h-5 w-5 text-red-400" />
                </div>
                <h2 class="font-display text-xl text-theme-text">{{ getDayName(hour.day_of_week) }}</h2>
              </div>
              <div class="flex items-center gap-1.5">
                <div class="h-2 w-2 rounded-full" :class="hour.is_closed ? 'bg-red-500' : 'bg-emerald-500'"></div>
                <span class="text-[10px] font-bold uppercase tracking-widest" :class="hour.is_closed ? 'text-red-400' : 'text-emerald-400'">
                  {{ hour.is_closed ? 'Closed' : 'Open' }}
                </span>
              </div>
            </div>
            
            <div v-if="!hour.is_closed" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/[0.03] border border-white/5">
              <ClockIcon class="h-5 w-5 text-admin/60 shrink-0" />
              <div class="flex items-center gap-2">
                <span class="text-lg font-display text-admin-light">{{ formatTime(hour.open_time) }}</span>
                <span class="text-ivory/30 text-sm">—</span>
                <span class="text-lg font-display text-admin-light">{{ formatTime(hour.close_time) }}</span>
              </div>
            </div>
            <div v-else class="px-4 py-3 rounded-xl bg-red-500/5 border border-red-500/10 text-center">
              <p class="text-sm text-red-400/70">Not available for bookings</p>
            </div>
          </div>
        </article>

        <div v-if="workingHours.length === 0" class="col-span-full flex flex-col items-center justify-center py-20 text-center border border-dashed border-white/10 rounded-3xl bg-theme-surface/30">
          <ClockIcon class="h-12 w-12 text-ivory/20 mb-4" />
          <p class="text-ivory/60 font-medium">No working hours configured.</p>
          <p class="text-sm text-ivory/40 mt-1">Set up your saloon's operating hours.</p>
        </div>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';
import { CalendarDaysIcon, ClockIcon, XCircleIcon } from '@heroicons/vue/24/outline';

const workingHours = ref([]);

const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

const getDayName = (dayIndex) => {
  return dayNames[dayIndex] || `Day ${dayIndex}`;
};

const formatTime = (time) => {
  if (!time) return '';
  const [hours, minutes] = time.split(':');
  const h = parseInt(hours);
  const ampm = h >= 12 ? 'PM' : 'AM';
  const h12 = h % 12 || 12;
  return `${h12}:${minutes} ${ampm}`;
};

onMounted(async () => {
  try {
    const response = await adminApi.workingHours();
    workingHours.value = response.data.data;
  } catch (err) {
    console.error('Failed to fetch working hours:', err);
  }
});
</script>