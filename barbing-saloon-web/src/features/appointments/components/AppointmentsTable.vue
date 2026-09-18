<template>
  <div class="rounded-[2rem] border border-white/[0.05] bg-[#1a1a1a]/80 backdrop-blur-2xl shadow-2xl overflow-hidden min-h-[400px]">
    
    <!-- Loading Skeleton -->
    <div v-if="loading" class="divide-y divide-white/[0.02]">
      <div v-for="i in 5" :key="i" class="p-6 grid grid-cols-1 md:grid-cols-[1.5fr_1.5fr_1fr_1fr_auto] gap-6 items-center animate-pulse">
        <div class="flex items-center gap-4">
          <div class="h-12 w-12 rounded-full bg-white/[0.03]"></div>
          <div class="space-y-2"><div class="h-4 w-24 bg-white/[0.03] rounded"></div><div class="h-3 w-16 bg-white/[0.02] rounded"></div></div>
        </div>
        <div class="space-y-2 hidden md:block"><div class="h-4 w-32 bg-white/[0.03] rounded"></div><div class="h-3 w-20 bg-white/[0.02] rounded"></div></div>
        <div class="space-y-2 hidden md:block"><div class="h-4 w-24 bg-white/[0.03] rounded"></div><div class="h-3 w-16 bg-white/[0.02] rounded"></div></div>
        <div class="h-6 w-20 bg-white/[0.03] rounded-full hidden md:block"></div>
        <div class="h-10 w-24 bg-white/[0.03] rounded-xl justify-self-end"></div>
      </div>
    </div>

    <!-- Appointments Table -->
    <template v-else>
      <div v-if="appointments.length > 0" class="overflow-x-auto pb-4">
        <table class="w-full text-left border-collapse min-w-[800px]">
          <thead>
            <tr class="divide-x divide-white/[0.04] border-y border-white/[0.04] bg-white/[0.02] text-[10px] uppercase tracking-[0.25em] text-white/40 font-bold">
              <th class="px-6 py-4 font-bold">Client Details</th>
              <th class="px-6 py-4 font-bold">Service & Barber</th>
              <th class="px-6 py-4 font-bold">Schedule</th>
              <th class="px-6 py-4 font-bold">Status</th>
              <th class="px-6 py-4 text-right font-bold">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/[0.04]">
            <tr
              v-for="appointment in appointments"
              :key="appointment.id"
              class="group transition-colors hover:bg-white/[0.02] divide-x divide-white/[0.02]"
            >
              <!-- Client details -->
              <td class="px-6 py-5 align-middle">
                <div class="flex items-center gap-4">
                  <div class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-admin/20 to-admin/5 border border-admin/20 text-admin font-bold text-sm shadow-[inset_0_1px_0_rgba(255,255,255,0.1)] group-hover:scale-105 transition-transform duration-300 overflow-hidden">
                    <img v-if="appointment.customer_avatar" :src="getAvatarUrl(appointment.customer_avatar)" :alt="appointment.customer?.name || appointment.client_name" class="w-full h-full object-cover" />
                    <span v-else>{{ initials(appointment.customer?.name || appointment.client_name) }}</span>
                    <div v-if="isNewClient(appointment)" class="absolute -top-1 -right-1 h-3 w-3 rounded-full bg-emerald-500 border-2 border-[#1a1a1a] z-10" title="New Client"></div>
                  </div>
                  <div>
                    <p class="text-sm font-bold text-white whitespace-nowrap">{{ appointment.customer?.name || appointment.client_name }}</p>
                    <p class="text-[11px] font-mono text-white/40 flex items-center gap-1 mt-0.5"><HashtagIcon class="h-3 w-3"/> {{ String(appointment.id).padStart(5, '0') }}</p>
                  </div>
                </div>
              </td>
              
              <!-- Service & Barber -->
              <td class="px-6 py-5 align-middle">
                <p class="text-sm text-white/90 font-medium truncate max-w-[200px]" :title="appointment.service?.name">{{ appointment.service?.name || 'General Booking' }}</p>
                <p class="text-[11px] text-white/40 mt-1 flex items-center gap-1 whitespace-nowrap"><UserIcon class="h-3 w-3 opacity-70" /> {{ appointment.barber?.name || 'Any Barber' }}</p>
              </td>

              <!-- Schedule -->
              <td class="px-6 py-5 align-middle">
                <div class="flex flex-col gap-1 whitespace-nowrap">
                  <p class="text-sm text-white/90 font-medium flex items-center gap-1.5"><CalendarDaysIcon class="h-4 w-4 text-white/30" /> {{ formatDate(appointment.appointment_date) }}</p>
                  <p class="text-xs text-admin font-bold flex items-center gap-1.5"><ClockIcon class="h-4 w-4 opacity-60" /> {{ appointment.appointment_time }}</p>
                </div>
              </td>

              <!-- Status -->
              <td class="px-6 py-5 align-middle">
                <div class="flex flex-col gap-1.5 whitespace-nowrap">
                  <!-- Main Appointment Status Pill -->
                  <span class="inline-flex items-center gap-1.5 w-fit rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest border shadow-sm" :class="statusClass(appointment.status)">
                    <span class="h-1.5 w-1.5 rounded-full" :class="statusDot(appointment.status)"></span>
                    {{ appointment.status }}
                  </span>
                  
                  <!-- Subtle Metadata Row (Source & Payment) -->
                  <div class="flex items-center gap-3 text-[10px] font-medium text-white/50 mt-1 uppercase tracking-wide">
                    <!-- Booking Source -->
                    <div class="flex items-center gap-1">
                      <UserPlusIcon v-if="appointment.booking_type === 'walk_in'" class="h-3 w-3 opacity-70" />
                      <GlobeAltIcon v-else class="h-3 w-3 opacity-70" />
                      <span>{{ appointment.booking_type === 'walk_in' ? 'Walk-In' : 'Online' }}</span>
                    </div>
                    
                    <!-- Divider -->
                    <span v-if="appointment.payment_status" class="text-white/20">&bull;</span>
                    
                    <!-- Payment Info -->
                    <div v-if="appointment.payment_status" class="flex items-center gap-1" :class="appointment.payment_status === 'verified' ? 'text-emerald-400' : appointment.payment_status === 'awaiting_verification' ? 'text-amber-400' : ''">
                      <BanknotesIcon class="h-3 w-3 opacity-80" />
                      <span>{{ appointment.payment_method === 'pos' ? 'POS' : appointment.payment_method ? appointment.payment_method : 'PAY' }}: {{ appointment.payment_status.replace('_', ' ') }}</span>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Actions -->
              <td class="px-6 py-5 text-right align-middle">
                <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                  <button @click="$emit('details', appointment)" class="rounded-xl bg-white/[0.05] border border-white/10 px-4 py-2 text-xs font-bold text-white hover:bg-white/[0.15] hover:shadow-[0_0_15px_rgba(255,255,255,0.1)] transition-all">
                    Details
                  </button>
                  
                  <template v-if="appointment.status === 'pending'">
                    <button v-if="!appointment.receipt_image || appointment.payment_status !== 'awaiting_verification'" @click="$emit('approve', appointment.id)" class="flex items-center justify-center h-8 w-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 hover:bg-emerald-500 hover:text-white hover:shadow-[0_0_15px_rgba(16,185,129,0.3)] transition-all" title="Approve">
                      <CheckIcon class="h-4 w-4" />
                    </button>
                    <button @click="$emit('force-approve', appointment.id)" class="flex items-center justify-center h-8 w-8 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 hover:bg-amber-500 hover:text-white hover:shadow-[0_0_15px_rgba(245,158,11,0.3)] transition-all" title="Force Approve (Bypass Clearance)">
                      <ShieldCheckIcon class="h-4 w-4" />
                    </button>
                    <button v-if="appointment.receipt_image && appointment.payment_status === 'awaiting_verification'" @click="$emit('view-receipt', appointment)" class="flex items-center justify-center h-8 w-8 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-400 hover:bg-blue-500 hover:text-white hover:shadow-[0_0_15px_rgba(59,130,246,0.3)] transition-all" title="Verify Receipt">
                      <DocumentTextIcon class="h-4 w-4" />
                    </button>
                    <button @click="$emit('cancel', appointment.id)" class="flex items-center justify-center h-8 w-8 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white hover:shadow-[0_0_15px_rgba(239,68,68,0.3)] transition-all" title="Cancel Booking">
                      <XMarkIcon class="h-4 w-4" />
                    </button>
                  </template>
                  <template v-else-if="appointment.status === 'confirmed' || appointment.status === 'completed'">
                    <button v-if="appointment.receipt_image" @click="$emit('view-receipt', appointment)" class="flex items-center justify-center h-8 w-8 rounded-xl bg-white/[0.05] border border-white/10 text-white/60 hover:bg-white/10 hover:text-white transition-all" title="View Receipt">
                      <DocumentTextIcon class="h-4 w-4" />
                    </button>
                    <button v-if="appointment.status === 'confirmed'" @click="$emit('cancel', appointment.id)" class="flex items-center justify-center h-8 w-8 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white hover:shadow-[0_0_15px_rgba(239,68,68,0.3)] transition-all" title="Cancel Booking">
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
      <div v-else class="flex flex-col items-center justify-center py-24 px-6 text-center">
        <div class="relative flex h-20 w-20 items-center justify-center rounded-full bg-white/[0.02] border border-white/[0.05] mb-6">
          <div class="absolute inset-0 rounded-full border border-dashed border-white/10 animate-[spin_10s_linear_infinite]"></div>
          <ClipboardDocumentCheckIcon class="h-8 w-8 text-white/20" />
        </div>
        <h3 class="text-xl font-bold text-white mb-2">No Appointments Found</h3>
        <p class="text-sm text-white/40 max-w-sm mx-auto">
          {{ search ? `No results match your search "${search}".` : `There are no ${currentFilter !== 'all' ? currentFilter : ''} appointments to display right now.` }}
        </p>
        <button v-if="search || currentFilter !== 'all'" @click="$emit('reset-filters')" class="mt-6 text-admin hover:text-admin-light text-sm font-semibold transition-colors">
          Clear filters
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { 
  BanknotesIcon, UserIcon, DocumentTextIcon, CheckIcon, XMarkIcon, 
  ClipboardDocumentCheckIcon, HashtagIcon, ClockIcon, CalendarDaysIcon, 
  UserPlusIcon, GlobeAltIcon, ShieldCheckIcon
} from '@heroicons/vue/24/outline';

defineProps({
  appointments: {
    type: Array,
    required: true
  },
  loading: Boolean,
  search: String,
  currentFilter: String
});

defineEmits(['details', 'approve', 'force-approve', 'cancel', 'view-receipt', 'reset-filters']);

function initials(name) {
  return (name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

const getAvatarUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  return `http://localhost:8000${path}`;
};

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function isNewClient(appt) {
  if (!appt.customer?.created_at) return false;
  const created = new Date(appt.customer.created_at);
  const now = new Date();
  return (now - created) / (1000 * 60 * 60 * 24) < 7;
}

const statusClass = (status) => {
  const map = {
    completed: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    confirmed: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    pending: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    cancelled: 'bg-red-500/10 text-red-400 border-red-500/20',
    no_show: 'bg-purple-500/10 text-purple-400 border-purple-500/20'
  };
  return map[status] || 'bg-white/5 text-white/50 border-white/10';
};

const statusDot = (status) => {
  const map = {
    completed: 'bg-emerald-400',
    confirmed: 'bg-blue-400',
    pending: 'bg-amber-400',
    cancelled: 'bg-red-400',
    no_show: 'bg-purple-400'
  };
  return map[status] || 'bg-white/30';
};
</script>
