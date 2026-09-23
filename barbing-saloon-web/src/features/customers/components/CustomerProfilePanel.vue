<template>
  <transition
    enter-active-class="transition-all duration-300 ease-out"
    leave-active-class="transition-all duration-200 ease-in"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div v-if="profilePanel" class="fixed inset-0 z-50 flex justify-end" @click.self="$emit('close')">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="$emit('close')"></div>
      
      <!-- Panel -->
      <div class="relative w-full max-w-2xl bg-[#141414] border-l border-white/[0.06] shadow-2xl overflow-y-auto">
        <!-- Loading state -->
        <div v-if="profileLoading" class="flex items-center justify-center h-full">
          <div class="h-8 w-8 animate-spin rounded-full border-2 border-admin/30 border-t-admin"></div>
        </div>
        
        <template v-else-if="profileData">
          <!-- Header -->
          <div class="sticky top-0 z-10 bg-[#141414]/95 backdrop-blur-lg border-b border-white/[0.04] px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Customer Profile</h2>
            <button @click="$emit('close')" class="flex items-center justify-center h-8 w-8 rounded-xl bg-white/5 border border-white/10 text-white/50 hover:text-white hover:bg-white/10 transition-all">
              <XMarkIcon class="h-4 w-4" />
            </button>
          </div>

          <!-- Profile Card -->
          <div class="p-6">
            <div class="flex items-center justify-between gap-4 mb-6">
              <div class="flex items-center gap-4">
                <div class="relative flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-admin/20 to-admin/5 border border-admin/20 text-admin font-bold text-xl shadow-[inset_0_1px_0_rgba(255,255,255,0.1)] overflow-hidden">
                  <img v-if="profileData.avatar" :src="getAvatarUrl(profileData.avatar)" :alt="profileData.name" class="w-full h-full object-cover" />
                  <span v-else>{{ initials(profileData.name) }}</span>
                </div>
                <div>
                  <h3 class="text-lg font-bold text-white">{{ profileData.name }}</h3>
                  <p class="text-xs text-white/40 mt-0.5">Customer since {{ formatDate(profileData.created_at) }}</p>
                  <span :class="profileData.auth_provider && profileData.auth_provider !== 'local' ? 'text-blue-300 bg-blue-500/10 border-blue-500/20' : 'text-white/40 bg-white/5 border-white/10'" class="inline-flex mt-1 rounded-md border px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider">
                    Registered via {{ profileData.registered_via || 'Registration form' }}
                  </span>
                </div>
              </div>
              <div class="flex flex-col items-end gap-1.5 shrink-0">
                <div class="flex items-center gap-2 text-sm text-white/70">
                  <span class="truncate max-w-[200px]">{{ profileData.email || 'No email' }}</span>
                  <EnvelopeIcon class="h-4 w-4 text-white/40 shrink-0" />
                </div>
                <div class="flex items-center gap-2 text-sm text-white/70">
                  <span>{{ profileData.phone || 'No phone' }}</span>
                  <PhoneIcon class="h-4 w-4 text-white/40 shrink-0" />
                </div>
              </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-4 gap-3 mb-8">
              <div class="rounded-xl bg-blue-500/5 border border-blue-500/20 p-4 text-center shadow-[inset_0_0_20px_rgba(59,130,246,0.05)]">
                <p class="text-2xl font-bold text-blue-400">{{ profileData.total_bookings || 0 }}</p>
                <p class="text-[10px] uppercase tracking-wider text-blue-400/60 font-bold mt-1">Bookings</p>
              </div>
              <div class="rounded-xl bg-emerald-500/5 border border-emerald-500/20 p-4 text-center shadow-[inset_0_0_20px_rgba(16,185,129,0.05)]">
                <p class="text-2xl font-bold text-emerald-400">₦{{ Number(profileData.total_spent || 0).toLocaleString() }}</p>
                <p class="text-[10px] uppercase tracking-wider text-emerald-400/60 font-bold mt-1">Total Generated</p>
              </div>
              <div class="rounded-xl bg-purple-500/5 border border-purple-500/20 p-4 text-center shadow-[inset_0_0_20px_rgba(168,85,247,0.05)]">
                <p class="text-2xl font-bold text-purple-400">{{ profileData.completed || 0 }}</p>
                <p class="text-[10px] uppercase tracking-wider text-purple-400/60 font-bold mt-1">Completed</p>
              </div>
              <div class="rounded-xl bg-red-500/5 border border-red-500/20 p-4 text-center shadow-[inset_0_0_20px_rgba(239,68,68,0.05)]">
                <p class="text-2xl font-bold text-red-400">{{ profileData.no_shows || 0 }}</p>
                <p class="text-[10px] uppercase tracking-wider text-red-400/60 font-bold mt-1">No-Shows</p>
              </div>
            </div>

            <!-- Booking History -->
            <div>
              <h4 class="text-xs font-bold text-white/50 uppercase tracking-wider mb-4 flex items-center gap-2">
                <ClockIcon class="h-4 w-4" />
                Booking History
              </h4>
              <div v-if="profileData.appointments && profileData.appointments.length > 0" class="space-y-3">
                <div v-for="appt in profileData.appointments" :key="appt.id" class="rounded-xl bg-white/[0.02] border border-white/[0.05] p-4 hover:bg-white/[0.04] transition-colors">
                  <div class="flex items-start justify-between gap-3 mb-2">
                    <div>
                      <p class="text-sm font-bold text-white">{{ appt.service_name || 'General Booking' }}</p>
                      <p class="text-[11px] text-white/40 mt-0.5">{{ appt.barber_name || 'Any Barber' }}</p>
                    </div>
                    <span class="shrink-0 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-widest border" :class="profileStatusClass(appt.status)">
                      <span class="h-1.5 w-1.5 rounded-full" :class="profileStatusDot(appt.status)"></span>
                      {{ appt.status }}
                    </span>
                  </div>
                  <div class="flex items-center gap-4 text-[11px] text-white/40">
                    <span class="flex items-center gap-1"><CalendarDaysIcon class="h-3 w-3" /> {{ formatDate(appt.appointment_date) }}</span>
                    <span class="flex items-center gap-1"><ClockIcon class="h-3 w-3" /> {{ appt.appointment_time }}</span>
                    <span v-if="appt.payment_amount" class="text-emerald-400 font-bold">₦{{ Number(appt.payment_amount).toLocaleString() }}</span>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8">
                <p class="text-sm text-white/30">No booking history yet.</p>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { 
  XMarkIcon, EnvelopeIcon, PhoneIcon, ClockIcon, CalendarDaysIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  profilePanel: { type: Object, default: null },
  profileLoading: { type: Boolean, required: true },
  profileData: { type: Object, default: null }
});

defineEmits(['close']);

function getAvatarUrl(path) {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  return `http://localhost:8000${path}`;
}

function initials(name) {
  return (name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function profileStatusClass(status) {
  const map = {
    pending: 'border-amber-500/30 bg-amber-500/10 text-amber-400',
    confirmed: 'border-blue-500/30 bg-blue-500/10 text-blue-400',
    completed: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400',
    cancelled: 'border-red-500/30 bg-red-500/10 text-red-400',
    no_show: 'border-red-500/30 bg-red-500/10 text-red-400',
  };
  return map[status] || 'border-white/10 bg-white/5 text-white/60';
}

function profileStatusDot(status) {
  const map = {
    pending: 'bg-amber-400',
    confirmed: 'bg-blue-400',
    completed: 'bg-emerald-400',
    cancelled: 'bg-red-400',
    no_show: 'bg-red-400',
  };
  return map[status] || 'bg-white/40';
}
</script>
