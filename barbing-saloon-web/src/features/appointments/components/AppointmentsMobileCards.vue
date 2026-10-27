<template>
  <div class="md:hidden">
    <div v-if="appointments && appointments.length > 0" class="space-y-3.5">
      <article 
        v-for="appointment in appointments" 
        :key="appointment.id"
        class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-4 flex flex-col gap-3 shadow-lg transition-all duration-200 hover:border-admin/40"
      >
        <!-- Top: Avatar, Name, Ref & Status -->
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-center gap-3 min-w-0">
            <div class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-admin/25 to-admin/10 border border-admin/30 text-admin font-bold text-xs shadow-inner overflow-hidden">
              <img 
                v-if="appointment.customer_avatar" 
                :src="getAvatarUrl(appointment.customer_avatar)" 
                :alt="appointment.customer?.name || appointment.client_name" 
                class="w-full h-full object-cover" 
              />
              <span v-else>{{ initials(appointment.customer?.name || appointment.client_name) }}</span>
            </div>
            <div class="min-w-0">
              <p class="text-sm font-bold text-theme-text truncate">{{ appointment.customer?.name || appointment.client_name }}</p>
              <p class="text-[10px] font-mono text-theme-text-muted flex items-center gap-1 mt-0.5">
                <span>{{ appointment.booking_reference || `CC-${String(appointment.id).padStart(4, '0')}` }}</span>
                <span v-if="appointment.client_phone" class="text-theme-text-muted opacity-40">•</span>
                <span v-if="appointment.client_phone">{{ appointment.client_phone }}</span>
              </p>
            </div>
          </div>

          <span 
            class="inline-flex items-center gap-1.5 shrink-0 rounded-full px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider border shadow-sm" 
            :class="statusClass(appointment.status)"
          >
            <span class="h-1.5 w-1.5 rounded-full" :class="statusDot(appointment.status)"></span>
            {{ appointment.status }}
          </span>
        </div>

        <!-- Service, Chair, Time, Price -->
        <div class="grid grid-cols-2 gap-3 py-2.5 border-y border-theme-border text-xs">
          <div>
            <span class="text-[9px] uppercase tracking-wider text-theme-text-muted block mb-0.5">Service & Barber</span>
            <p class="text-theme-text font-semibold truncate">{{ appointment.service?.name || 'General Booking' }}</p>
            <p class="text-[11px] text-admin/80 truncate mt-0.5 flex items-center gap-1">
              <span>💈 {{ appointment.barber?.name || 'Any Barber' }}</span>
            </p>
          </div>
          <div>
            <span class="text-[9px] uppercase tracking-wider text-theme-text-muted block mb-0.5">Schedule</span>
            <p class="text-theme-text font-semibold">{{ formatDate(appointment.appointment_date) }}</p>
            <p class="text-amber-400 font-mono font-medium text-[11px] mt-0.5">{{ appointment.appointment_time }}</p>
          </div>
        </div>

        <!-- Footer: Channel, Price & Action Buttons -->
        <div class="flex items-center justify-between gap-2 pt-0.5">
          <div class="flex items-center gap-2">
            <!-- Channel Pill -->
            <span v-if="appointment.source === 'app'" class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[9px] font-bold uppercase bg-purple-500/15 text-purple-300 border border-purple-500/30">
              App
            </span>
            <span v-else-if="appointment.source === 'walk_in' || appointment.booking_type === 'walk_in'" class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[9px] font-bold uppercase bg-amber-500/15 text-amber-300 border border-amber-500/30">
              Walk-In
            </span>
            <span v-else class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[9px] font-bold uppercase bg-sky-500/15 text-sky-300 border border-sky-500/30">
              Web
            </span>

            <!-- Price -->
            <span class="text-xs font-mono font-bold text-theme-text">
              ₦{{ (appointment.total_price || appointment.grand_total || appointment.service?.price || 0).toLocaleString() }}
            </span>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center gap-1.5">
            <button 
              @click="$emit('details', appointment)" 
              class="min-h-[38px] px-3 rounded-lg bg-theme-bg border border-theme-border text-xs font-semibold text-theme-text hover:bg-theme-surface-hover transition-colors"
            >
              Details
            </button>

            <template v-if="appointment.status === 'pending'">
              <button 
                v-if="!appointment.receipt_image || appointment.payment_status !== 'awaiting_verification'" 
                @click="$emit('approve', appointment.id)" 
                class="min-h-[38px] min-w-[38px] flex items-center justify-center rounded-lg bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500 hover:text-white transition-all" 
                title="Approve"
              >
                <CheckIcon class="h-4 w-4" />
              </button>
              <button 
                v-if="appointment.receipt_image && appointment.payment_status === 'awaiting_verification'" 
                @click="$emit('view-receipt', appointment)" 
                class="min-h-[38px] min-w-[38px] flex items-center justify-center rounded-lg bg-blue-500/15 border border-blue-500/30 text-blue-400 hover:bg-blue-500 hover:text-white transition-all" 
                title="Verify Receipt"
              >
                <DocumentTextIcon class="h-4 w-4" />
              </button>
              <button 
                @click="$emit('cancel', appointment.id)" 
                class="min-h-[38px] min-w-[38px] flex items-center justify-center rounded-lg bg-red-500/15 border border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white transition-all" 
                title="Cancel Booking"
              >
                <XMarkIcon class="h-4 w-4" />
              </button>
            </template>

            <template v-else-if="appointment.status === 'confirmed'">
              <button 
                v-if="appointment.receipt_image" 
                @click="$emit('view-receipt', appointment)" 
                class="min-h-[38px] min-w-[38px] flex items-center justify-center rounded-lg bg-theme-bg border border-theme-border text-theme-text-muted hover:text-theme-text transition-colors" 
                title="Receipt"
              >
                <DocumentTextIcon class="h-4 w-4" />
              </button>
              <button 
                @click="$emit('cancel', appointment.id)" 
                class="min-h-[38px] min-w-[38px] flex items-center justify-center rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white transition-colors" 
                title="Cancel"
              >
                <XMarkIcon class="h-4 w-4" />
              </button>
            </template>
          </div>
        </div>
      </article>
    </div>

    <!-- Mobile Empty State -->
    <div v-else class="flex flex-col items-center justify-center py-14 px-4 text-center rounded-2xl border border-theme-border bg-theme-surface shadow-lg">
      <div class="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-theme-bg border border-theme-border mb-3.5">
        <ClipboardDocumentCheckIcon class="h-7 w-7 text-theme-text-muted" />
      </div>
      <h3 class="text-base font-bold text-theme-text mb-1">No Appointments Found</h3>
      <p class="text-xs text-theme-text-muted max-w-xs mx-auto">
        {{ search ? `No results match your search "${search}".` : `There are no ${currentFilter !== 'all' ? currentFilter : ''} appointments matching the selected filters.` }}
      </p>
      <button 
        v-if="search || currentFilter !== 'all'" 
        @click="$emit('reset-filters')" 
        class="mt-4 min-h-[44px] inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-admin/15 text-admin border border-admin/30 text-xs font-bold hover:bg-admin/25 transition-all"
      >
        Reset Filter Criteria
      </button>
    </div>
  </div>
</template>

<script setup>
import { CheckIcon, XMarkIcon, DocumentTextIcon, ClipboardDocumentCheckIcon } from '@heroicons/vue/24/outline';
import { getStorageUrl } from '@/core/utils/url';

defineProps({
  appointments: {
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
  }
});

defineEmits(['details', 'approve', 'cancel', 'view-receipt', 'reset-filters']);

function initials(name) {
  return (name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function getAvatarUrl(path) {
  if (!path) return '';
  return getStorageUrl(path);
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function statusClass(status) {
  const map = {
    completed: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
    confirmed: 'bg-blue-500/15 text-blue-400 border-blue-500/30',
    pending: 'bg-amber-500/15 text-amber-400 border-amber-500/30',
    cancelled: 'bg-red-500/15 text-red-400 border-red-500/30',
    no_show: 'bg-purple-500/15 text-purple-400 border-purple-500/30'
  };
  return map[status] || 'bg-theme-bg text-theme-text-muted border-theme-border';
}

function statusDot(status) {
  const map = {
    completed: 'bg-emerald-400',
    confirmed: 'bg-blue-400',
    pending: 'bg-amber-400',
    cancelled: 'bg-red-400',
    no_show: 'bg-purple-400'
  };
  return map[status] || 'bg-theme-text-muted';
}
</script>
