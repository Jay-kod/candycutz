<template>
  <div class="hidden md:block rounded-2xl border border-white/[0.08] bg-[#12161C] shadow-2xl overflow-hidden min-h-[380px]">
    <!-- Data Table -->
    <div v-if="appointments.length > 0" class="overflow-x-auto">
      <table class="w-full text-left border-collapse min-w-[880px]">
        <thead>
          <tr class="divide-x divide-white/[0.04] border-b border-white/[0.08] bg-white/[0.02] text-[10px] uppercase tracking-[0.2em] text-white/40 font-bold">
            <th class="px-5 py-3.5">Client Details</th>
            <th class="px-5 py-3.5">Service & Chair</th>
            <th class="px-5 py-3.5">Schedule</th>
            <th class="px-5 py-3.5">Channel</th>
            <th class="px-5 py-3.5">Status & Payment</th>
            <th class="px-5 py-3.5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/[0.04]">
          <tr
            v-for="appointment in appointments"
            :key="appointment.id"
            class="group transition-colors hover:bg-white/[0.03] divide-x divide-white/[0.02]"
          >
            <!-- Client Details -->
            <td class="px-5 py-4 align-middle">
              <div class="flex items-center gap-3.5">
                <div class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-admin/25 to-admin/10 border border-admin/30 text-admin font-bold text-xs shadow-[inset_0_1px_0_rgba(255,255,255,0.1)] group-hover:scale-105 transition-transform duration-300 overflow-hidden">
                  <img 
                    v-if="appointment.customer_avatar" 
                    :src="getAvatarUrl(appointment.customer_avatar)" 
                    :alt="appointment.customer?.name || appointment.client_name" 
                    class="w-full h-full object-cover" 
                  />
                  <span v-else>{{ initials(appointment.customer?.name || appointment.client_name) }}</span>
                </div>
                <div class="min-w-0">
                  <p class="text-sm font-bold text-white whitespace-nowrap">{{ appointment.customer?.name || appointment.client_name }}</p>
                  <p class="text-[11px] font-mono text-white/40 flex items-center gap-1.5 mt-0.5">
                    <span>{{ appointment.booking_reference || `CC-${String(appointment.id).padStart(4, '0')}` }}</span>
                    <span v-if="appointment.client_phone" class="text-white/20">•</span>
                    <span v-if="appointment.client_phone">{{ appointment.client_phone }}</span>
                  </p>
                </div>
              </div>
            </td>

            <!-- Service & Barber -->
            <td class="px-5 py-4 align-middle">
              <div class="min-w-0">
                <div class="flex items-center gap-2">
                  <p class="text-sm text-white/95 font-semibold truncate max-w-[200px]" :title="appointment.service?.name">
                    {{ appointment.service?.name || 'General Booking' }}
                  </p>
                  <span v-if="appointment.service?.duration_minutes" class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-white/[0.06] text-white/60">
                    {{ appointment.service.duration_minutes }}m
                  </span>
                </div>
                <p class="text-[11px] text-admin/90 mt-1 flex items-center gap-1 whitespace-nowrap">
                  <span class="opacity-70">💈</span>
                  <span>{{ appointment.barber?.name || 'Any Available Stylist' }}</span>
                  <span v-if="appointment.barber?.rating" class="text-amber-400 font-mono text-[10px]">★ {{ Number(appointment.barber.rating).toFixed(1) }}</span>
                </p>
              </div>
            </td>

            <!-- Schedule -->
            <td class="px-5 py-4 align-middle">
              <div class="flex flex-col gap-0.5 whitespace-nowrap">
                <div class="flex items-center gap-1.5">
                  <p class="text-xs text-white/95 font-semibold">{{ formatDate(appointment.appointment_date) }}</p>
                  <span 
                    v-if="isToday(appointment.appointment_date)" 
                    class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase bg-emerald-500/20 text-emerald-400 border border-emerald-500/30"
                  >
                    Today
                  </span>
                </div>
                <p class="text-xs text-admin font-mono font-medium flex items-center gap-1 mt-0.5">
                  <ClockIcon class="h-3.5 w-3.5 opacity-70" />
                  <span>{{ appointment.appointment_time }}</span>
                </p>
              </div>
            </td>

            <!-- Channel / Source -->
            <td class="px-5 py-4 align-middle">
              <span v-if="appointment.source === 'app'" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-500/15 text-purple-300 border border-purple-500/30 shadow-sm">
                <DevicePhoneMobileIcon class="h-3.5 w-3.5" /> App
              </span>
              <span v-else-if="appointment.source === 'walk_in' || appointment.booking_type === 'walk_in'" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-500/15 text-amber-300 border border-amber-500/30 shadow-sm">
                <UserPlusIcon class="h-3.5 w-3.5" /> Walk-In
              </span>
              <span v-else class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-sky-500/15 text-sky-300 border border-sky-500/30 shadow-sm">
                <GlobeAltIcon class="h-3.5 w-3.5" /> Web
              </span>
            </td>

            <!-- Status & Payment -->
            <td class="px-5 py-4 align-middle">
              <div class="flex flex-col gap-1.5 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider border shadow-sm" :class="statusClass(appointment.status)">
                    <span class="h-1.5 w-1.5 rounded-full" :class="statusDot(appointment.status)"></span>
                    {{ appointment.status }}
                  </span>
                  <span class="text-xs font-mono font-bold text-white/90">
                    ₦{{ (appointment.total_price || appointment.grand_total || appointment.service?.price || 0).toLocaleString() }}
                  </span>
                </div>
                <div class="flex items-center gap-1.5 text-[10px] text-white/50">
                  <span v-if="appointment.receipt_image" class="text-blue-400 flex items-center gap-0.5">
                    <PaperClipIcon class="h-3 w-3" /> Receipt
                  </span>
                  <span v-if="appointment.receipt_image && appointment.payment_status" class="text-white/20">•</span>
                  <span class="capitalize">{{ (appointment.payment_status || 'unpaid').replace('_', ' ') }}</span>
                </div>
              </div>
            </td>

            <!-- Actions -->
            <td class="px-5 py-4 text-right align-middle">
              <div class="flex items-center justify-end gap-1.5 opacity-85 group-hover:opacity-100 transition-opacity">
                <button 
                  @click="$emit('details', appointment)" 
                  class="rounded-xl bg-white/[0.05] border border-white/10 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/[0.12] hover:border-white/20 transition-all"
                >
                  Details
                </button>
                
                <template v-if="appointment.status === 'pending'">
                  <button 
                    v-if="!appointment.receipt_image || appointment.payment_status !== 'awaiting_verification'" 
                    @click="$emit('approve', appointment.id)" 
                    class="flex items-center justify-center h-8 w-8 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500 hover:text-white transition-all shadow-sm" 
                    title="Approve Booking"
                  >
                    <CheckIcon class="h-4 w-4" />
                  </button>
                  <button 
                    @click="$emit('force-approve', appointment.id)" 
                    class="flex items-center justify-center h-8 w-8 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-400 hover:bg-amber-500 hover:text-white transition-all shadow-sm" 
                    title="Force Approve (Bypass Payment)"
                  >
                    <ShieldCheckIcon class="h-4 w-4" />
                  </button>
                  <button 
                    v-if="appointment.receipt_image && appointment.payment_status === 'awaiting_verification'" 
                    @click="$emit('view-receipt', appointment)" 
                    class="flex items-center justify-center h-8 w-8 rounded-xl bg-blue-500/15 border border-blue-500/30 text-blue-400 hover:bg-blue-500 hover:text-white transition-all shadow-sm" 
                    title="Verify Uploaded Receipt"
                  >
                    <DocumentTextIcon class="h-4 w-4" />
                  </button>
                  <button 
                    @click="$emit('cancel', appointment.id)" 
                    class="flex items-center justify-center h-8 w-8 rounded-xl bg-red-500/15 border border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white transition-all shadow-sm" 
                    title="Cancel Booking"
                  >
                    <XMarkIcon class="h-4 w-4" />
                  </button>
                </template>

                <template v-else-if="appointment.status === 'confirmed' || appointment.status === 'completed'">
                  <button 
                    v-if="appointment.receipt_image" 
                    @click="$emit('view-receipt', appointment)" 
                    class="flex items-center justify-center h-8 w-8 rounded-xl bg-white/[0.05] border border-white/10 text-white/60 hover:bg-white/10 hover:text-white transition-all" 
                    title="View Receipt"
                  >
                    <DocumentTextIcon class="h-4 w-4" />
                  </button>
                  <button 
                    v-if="appointment.status === 'confirmed'" 
                    @click="$emit('cancel', appointment.id)" 
                    class="flex items-center justify-center h-8 w-8 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white transition-all" 
                    title="Cancel Booking"
                  >
                    <XMarkIcon class="h-4 w-4" />
                  </button>
                </template>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-else class="flex flex-col items-center justify-center py-20 px-6 text-center">
      <div class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-white/[0.03] border border-white/[0.08] mb-4">
        <ClipboardDocumentCheckIcon class="h-8 w-8 text-white/30" />
      </div>
      <h3 class="text-lg font-bold text-white mb-1.5">No Appointments Found</h3>
      <p class="text-xs text-white/40 max-w-sm mx-auto">
        {{ search ? `No results match your search "${search}".` : `There are no ${currentFilter !== 'all' ? currentFilter : ''} appointments matching the selected filters.` }}
      </p>
      <button 
        v-if="search || currentFilter !== 'all'" 
        @click="$emit('reset-filters')" 
        class="mt-5 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-admin/15 text-admin border border-admin/30 text-xs font-bold hover:bg-admin/25 transition-all"
      >
        Reset Filter Criteria
      </button>
    </div>
  </div>
</template>

<script setup>
import { 
  ClockIcon, CheckIcon, XMarkIcon, ShieldCheckIcon, 
  DocumentTextIcon, ClipboardDocumentCheckIcon, PaperClipIcon,
  DevicePhoneMobileIcon, UserPlusIcon, GlobeAltIcon
} from '@heroicons/vue/24/outline';
import { getStorageUrl } from '@/core/utils/url';

defineProps({
  appointments: {
    type: Array,
    required: true
  },
  search: String,
  currentFilter: String
});

defineEmits(['details', 'approve', 'force-approve', 'cancel', 'view-receipt', 'reset-filters']);

function initials(name) {
  return (name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function getAvatarUrl(path) {
  if (!path) return '';
  return getStorageUrl(path);
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function isToday(dateStr) {
  if (!dateStr) return false;
  return dateStr === new Date().toISOString().split('T')[0];
}

function statusClass(status) {
  const map = {
    completed: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
    confirmed: 'bg-blue-500/15 text-blue-400 border-blue-500/30',
    pending: 'bg-amber-500/15 text-amber-400 border-amber-500/30',
    cancelled: 'bg-red-500/15 text-red-400 border-red-500/30',
    no_show: 'bg-purple-500/15 text-purple-400 border-purple-500/30'
  };
  return map[status] || 'bg-white/5 text-white/50 border-white/10';
}

function statusDot(status) {
  const map = {
    completed: 'bg-emerald-400',
    confirmed: 'bg-blue-400',
    pending: 'bg-amber-400',
    cancelled: 'bg-red-400',
    no_show: 'bg-purple-400'
  };
  return map[status] || 'bg-white/30';
}
</script>