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
      <div class="absolute inset-0 bg-black/60 backdrop-blur-md" @click="$emit('close')"></div>
      
      <!-- Slide-Out Drawer Panel -->
      <div class="relative w-full max-w-2xl bg-theme-surface border-l border-theme-border shadow-2xl overflow-y-auto z-10 flex flex-col">
        <!-- Sticky Header -->
        <div class="sticky top-0 z-20 bg-theme-surface/95 backdrop-blur-lg border-b border-theme-border px-6 py-4 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-admin"></span>
            <h2 class="text-xs font-bold text-theme-text uppercase tracking-widest">Customer Dossier</h2>
          </div>
          <button 
            @click="$emit('close')" 
            class="flex items-center justify-center h-8 w-8 rounded-xl bg-theme-surface-subtle border border-theme-border text-theme-text-muted hover:text-theme-text hover:bg-theme-border/30 transition-all"
            title="Close Drawer"
          >
            <XMarkIcon class="h-4 w-4" />
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="profileLoading" class="flex-1 flex flex-col items-center justify-center p-12 text-center">
          <div class="h-9 w-9 animate-spin rounded-full border-2 border-admin/20 border-t-admin mb-3"></div>
          <p class="text-xs text-theme-text-muted">Loading profile & booking history...</p>
        </div>
        
        <!-- Profile Content -->
        <div v-else-if="profileData" class="p-6 space-y-6 flex-1">
          <!-- Client Header Card -->
          <div class="rounded-2xl border border-theme-border bg-theme-surface-subtle p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div class="flex items-center gap-4">
                <div class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-admin/25 to-admin/10 border border-admin/30 text-admin font-bold text-lg shadow-[inset_0_1px_0_rgba(255,255,255,0.1)] overflow-hidden">
                  <img v-if="profileData.avatar" :src="profileData.avatar" :alt="profileData.name" class="w-full h-full object-cover" />
                  <span v-else>{{ initials(profileData.name) }}</span>
                </div>
                <div>
                  <h3 class="text-lg font-bold text-theme-text">{{ profileData.name }}</h3>
                  <p class="text-xs text-theme-text-muted mt-0.5">Joined {{ formatDate(profileData.created_at) }}</p>
                  <span 
                    class="inline-flex mt-1 rounded-md border px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider"
                    :class="profileData.auth_provider && profileData.auth_provider !== 'local' ? 'text-red-500 dark:text-red-300 bg-red-500/10 border-red-500/20' : 'text-theme-text-muted bg-theme-surface-subtle border-theme-border'"
                  >
                    {{ profileData.registered_via || 'Registration Form' }}
                  </span>
                </div>
              </div>

              <!-- Quick Contact Links -->
              <div class="flex items-center gap-2">
                <a 
                  v-if="profileData.phone && profileData.phone !== 'N/A'"
                  :href="`tel:${profileData.phone}`"
                  class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-theme-surface-subtle border border-theme-border text-xs font-semibold text-theme-text hover:bg-theme-border/30 transition-colors"
                  title="Call Customer"
                >
                  <PhoneIcon class="h-3.5 w-3.5 text-admin" />
                  <span>Call</span>
                </a>
                <a 
                  v-if="profileData.email"
                  :href="`mailto:${profileData.email}`"
                  class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-theme-surface-subtle border border-theme-border text-xs font-semibold text-theme-text hover:bg-theme-border/30 transition-colors"
                  title="Email Customer"
                >
                  <EnvelopeIcon class="h-3.5 w-3.5 text-sky-400" />
                  <span>Email</span>
                </a>
              </div>
            </div>
          </div>

          <!-- 4-Metric Grid -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="rounded-xl bg-blue-500/5 border border-blue-500/20 p-3.5 text-center">
              <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ profileData.total_bookings || 0 }}</p>
              <p class="text-[9px] uppercase tracking-wider text-blue-600/70 dark:text-blue-400/60 font-bold mt-1">Bookings</p>
            </div>
            <div class="rounded-xl bg-emerald-500/5 border border-emerald-500/20 p-3.5 text-center">
              <p class="text-xl sm:text-2xl font-bold text-emerald-600 dark:text-emerald-400 font-mono">₦{{ Number(profileData.total_spent || 0).toLocaleString() }}</p>
              <p class="text-[9px] uppercase tracking-wider text-emerald-600/70 dark:text-emerald-400/60 font-bold mt-1">Lifetime Value</p>
            </div>
            <div class="rounded-xl bg-purple-500/5 border border-purple-500/20 p-3.5 text-center">
              <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ getCompletedCount(profileData) }}</p>
              <p class="text-[9px] uppercase tracking-wider text-purple-600/70 dark:text-purple-400/60 font-bold mt-1">Completed</p>
            </div>
            <div class="rounded-xl bg-red-500/5 border border-red-500/20 p-3.5 text-center">
              <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ profileData.no_shows || 0 }}</p>
              <p class="text-[9px] uppercase tracking-wider text-red-600/70 dark:text-red-400/60 font-bold mt-1">No-Shows</p>
            </div>
          </div>

          <!-- Booking History Timeline -->
          <div class="space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-theme-border">
              <h4 class="text-xs font-bold text-theme-text uppercase tracking-wider flex items-center gap-2">
                <ClockIcon class="h-4 w-4 text-admin" />
                <span>Appointment History</span>
              </h4>
              <span class="text-[11px] font-mono text-theme-text-muted">
                {{ profileData.appointments?.length || 0 }} {{ profileData.appointments?.length === 1 ? 'record' : 'records' }}
              </span>
            </div>

            <div v-if="profileData.appointments && profileData.appointments.length > 0" class="space-y-2.5">
              <div 
                v-for="appt in profileData.appointments" 
                :key="appt.id" 
                class="rounded-xl bg-theme-surface-subtle border border-theme-border p-3.5 hover:bg-theme-border/20 transition-colors"
              >
                <div class="flex items-start justify-between gap-3 mb-1.5">
                  <div>
                    <p class="text-sm font-semibold text-theme-text">{{ appt.service_name || 'General Booking' }}</p>
                    <p class="text-[11px] text-admin/80 mt-0.5">💈 {{ appt.barber_name || 'Any Barber' }}</p>
                  </div>
                  <span 
                    class="shrink-0 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider border"
                    :class="statusClass(appt.status)"
                  >
                    {{ appt.status }}
                  </span>
                </div>
                <div class="flex items-center justify-between text-[11px] text-theme-text-muted pt-1 border-t border-theme-border/50">
                  <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1">
                      <CalendarDaysIcon class="h-3 w-3" />
                      {{ formatDate(appt.date || appt.appointment_date) }}
                    </span>
                    <span class="flex items-center gap-1">
                      <ClockIcon class="h-3 w-3" />
                      {{ appt.time || appt.appointment_time }}
                    </span>
                  </div>
                  <span v-if="appt.amount || appt.payment_amount" class="text-emerald-600 dark:text-emerald-400 font-mono font-bold">
                    ₦{{ Number(appt.amount || appt.payment_amount).toLocaleString() }}
                  </span>
                </div>
              </div>
            </div>

            <div v-else class="py-10 text-center text-xs text-theme-text-muted border border-dashed border-theme-border rounded-2xl">
              No previous appointments found for this client.
            </div>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { 
  XMarkIcon, 
  ClockIcon, 
  CalendarDaysIcon, 
  EnvelopeIcon, 
  PhoneIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  profilePanel: Object,
  profileLoading: Boolean,
  profileData: Object
});

defineEmits(['close']);

function initials(name) {
  return (name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function formatDate(dateStr) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function getCompletedCount(data) {
  if (data.completed !== undefined) return data.completed;
  if (!Array.isArray(data.appointments)) return 0;
  return data.appointments.filter(a => a.status === 'completed').length;
}

function statusClass(status) {
  const map = {
    completed: 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/30',
    confirmed: 'bg-blue-500/15 text-blue-600 dark:text-blue-400 border-blue-500/30',
    pending: 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/30',
    cancelled: 'bg-red-500/15 text-red-600 dark:text-red-400 border-red-500/30'
  };
  return map[status] || 'bg-theme-surface-subtle text-theme-text-muted border-theme-border';
}
</script>