<template>
  <BarberLayout>
    <section class="space-y-6 animate-fade-in">
      <!-- Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Appointments</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-theme-text">
              Manage Your <span class="text-gold">Chair List</span>
            </h1>
          </div>
          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <button 
              @click="$router.push('/barber/walk-in')"
              class="inline-flex items-center gap-2 rounded-xl bg-gold px-5 py-2.5 text-sm font-bold text-obsidian shadow-lg shadow-gold/20 transition-all hover:bg-gold-light hover:scale-105 hover:shadow-[0_0_20px_rgba(212,175,55,0.4)]"
            >
              <UserPlusIcon class="h-4 w-4" />
              New Walk-In
            </button>
            <button 
              @click="loadAppointments"
              class="p-2.5 rounded-xl bg-white/[0.04] border border-theme-border text-ivory/50 hover:text-gold hover:border-gold/30 hover:bg-gold/10 transition-all shadow-sm group"
              title="Refresh Data"
            >
              <ArrowPathIcon class="w-5 h-5 group-active:rotate-180 transition-transform duration-500" />
            </button>
            <div class="flex items-center gap-2 rounded-xl bg-white/[0.04] border border-theme-border px-4 py-2.5">
              <MagnifyingGlassIcon class="h-4 w-4 text-ivory/30" />
              <input
                v-model="search"
                type="text"
                placeholder="Search client..."
                class="bg-transparent text-sm text-theme-text placeholder:text-ivory/25 outline-none w-32 sm:w-40"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex items-center gap-2 overflow-x-auto hide-scrollbar pb-2">
        <button 
          v-for="tab in tabs" 
          :key="tab.value"
          @click="currentFilter = tab.value"
          class="relative px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all whitespace-nowrap overflow-hidden"
          :class="currentFilter === tab.value ? 'text-obsidian bg-gold shadow-[0_0_15px_rgba(212,175,55,0.3)]' : 'text-ivory/50 hover:text-ivory bg-theme-surface/60 border border-theme-border hover:bg-theme-surface'"
        >
          <span class="relative z-10 flex items-center gap-2">
            {{ tab.label }}
            <span class="flex items-center justify-center h-5 px-1.5 rounded-md text-[10px] tabular-nums" :class="currentFilter === tab.value ? 'bg-black/20 text-obsidian' : 'bg-black/40 text-ivory/50'">
              {{ tab.count }}
            </span>
          </span>
        </button>
      </div>

      <!-- Appointments Grid -->
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <article
          v-for="appointment in filteredAppointments"
          :key="appointment.id"
          class="group relative rounded-[2rem] backdrop-blur-md p-6 transition-all duration-500 hover:-translate-y-1.5 flex flex-col overflow-hidden border"
          :class="cardClasses(appointment.status)"
        >
          <!-- Status Glow Orb -->
          <div 
            class="absolute -top-20 -right-20 w-44 h-44 rounded-full blur-3xl transition-all duration-500 pointer-events-none"
            :class="glowClasses(appointment.status)"
          ></div>
          
          <!-- Status Accent Line -->
          <div 
            class="absolute top-0 left-8 right-8 h-[2px] rounded-full"
            :class="accentLineClasses(appointment.status)"
          ></div>

          <!-- Top Row: Client Info & Status -->
          <div class="flex justify-between items-start gap-4 mb-5 relative z-10">
            <!-- Client Info -->
            <div class="flex items-center gap-4 min-w-0">
              <div 
                class="flex h-14 w-14 sm:h-16 sm:w-16 shrink-0 items-center justify-center rounded-2xl font-display text-xl sm:text-2xl font-bold border shadow-inner overflow-hidden"
                :class="avatarClasses(appointment.status)"
              >
                <img v-if="appointment.customer_avatar" :src="`http://localhost:8000${appointment.customer_avatar}`" alt="Customer" class="w-full h-full object-cover" />
                <span v-else>{{ initials(appointment.customer_name) }}</span>
              </div>
              <div class="min-w-0 flex-1">
                <h3 class="font-display text-lg sm:text-xl text-theme-text truncate" :title="appointment.customer_name">{{ appointment.customer_name || 'Unknown Client' }}</h3>
                <p class="text-xs sm:text-sm font-medium truncate mb-1.5" :class="serviceTextClasses(appointment.status)" :title="appointment.service_name">{{ appointment.service_name || 'General Service' }}</p>
                <div class="flex items-center gap-1.5 text-[10px] sm:text-xs text-ivory/50">
                  <span v-if="appointment.customer_phone" class="flex items-center gap-1 truncate"><PhoneIcon class="h-3 w-3 shrink-0" /> {{ appointment.customer_phone }}</span>
                  <span v-else-if="appointment.customer_email" class="flex items-center gap-1 truncate"><EnvelopeIcon class="h-3 w-3 shrink-0" /> {{ appointment.customer_email }}</span>
                  <span v-else>No contact info</span>
                </div>
              </div>
            </div>

            <!-- Primary Status Badge -->
            <div class="shrink-0 pt-1">
              <span
                class="rounded-full px-3 py-1.5 text-[9px] sm:text-[10px] font-bold uppercase tracking-widest border shadow-sm"
                :class="badgeClasses(appointment.status)"
              >
                {{ appointment.status }}
              </span>
            </div>
          </div>

          <!-- Booking Type & Payment Info -->
          <div class="mb-4 relative z-10 flex flex-wrap items-center gap-1.5">
            <!-- Walk-In / Online Badge -->
            <span v-if="appointment.booking_type === 'walk_in'" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-[9px] sm:text-[10px] font-bold uppercase tracking-wider border border-purple-500/20 bg-purple-500/10 text-purple-400">
              <UserPlusIcon class="h-3 w-3" />
              Walk-In
            </span>
            <span v-else class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-[9px] sm:text-[10px] font-bold uppercase tracking-wider border border-cyan-500/20 bg-cyan-500/10 text-cyan-400">
              <GlobeAltIcon class="h-3 w-3" />
              Online
            </span>

            <!-- Payment Method -->
            <span v-if="appointment.payment_method" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-[9px] sm:text-[10px] font-bold uppercase tracking-wider border border-white/10 bg-white/[0.04] text-ivory/60">
              <BanknotesIcon class="h-3 w-3" />
              {{ appointment.payment_method === 'pos' ? 'POS' : appointment.payment_method }}
            </span>

            <!-- Payment Status -->
            <span v-if="appointment.payment_status" class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-[9px] sm:text-[10px] font-bold uppercase tracking-wider border border-white/5 bg-black/40 text-ivory/60">
              <span class="w-1.5 h-1.5 rounded-full" :class="appointment.payment_status === 'successful' ? 'bg-emerald-400' : appointment.payment_status === 'awaiting_verification' ? 'bg-amber-400' : 'bg-red-400'"></span>
              {{ appointment.payment_status.replace('_', ' ') }}
            </span>
            <span v-else class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-[9px] sm:text-[10px] font-bold uppercase tracking-wider border border-white/5 bg-black/20 text-ivory/30">
              <span class="w-1.5 h-1.5 rounded-full bg-ivory/20"></span>
              Unpaid
            </span>
          </div>

          <!-- Details Card -->
          <div 
            class="flex-1 rounded-2xl p-3 sm:p-4 border mb-6 relative z-10"
            :class="detailsCardClasses(appointment.status)"
          >
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div class="flex items-center gap-3 text-sm text-ivory/70">
                <div class="p-2 rounded-xl bg-theme-surface border border-theme-border shadow-sm" :class="iconClasses(appointment.status)">
                  <CalendarIcon class="h-4 w-4" />
                </div>
                <span class="font-medium tracking-wide text-xs sm:text-sm">{{ new Date(appointment.appointment_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</span>
              </div>
              <div class="flex items-center gap-3 text-sm text-ivory/70">
                <div class="p-2 rounded-xl bg-theme-surface border border-theme-border shadow-sm" :class="iconClasses(appointment.status)">
                  <ClockIcon class="h-4 w-4" />
                </div>
                <span class="font-bold tracking-widest text-xs sm:text-sm" :class="timeTextClasses(appointment.status)">{{ appointment.appointment_time }}</span>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-auto pt-5 border-t border-white/5 flex flex-wrap gap-2">
            <template v-if="appointment.status === 'pending'">
              <button
                v-if="appointment.receipt_image && appointment.payment_status === 'awaiting_verification'"
                @click="viewReceipt(appointment)"
                class="flex-1 inline-flex justify-center items-center gap-1.5 rounded-xl bg-blue-500/10 border border-blue-500/20 py-2.5 text-xs font-bold uppercase tracking-widest text-blue-400 hover:bg-blue-500 hover:text-white transition-all shadow-sm"
              >
                Receipt
              </button>
              <button
                v-else
                @click="approve(appointment.id)"
                class="flex-1 inline-flex justify-center items-center gap-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 py-2.5 text-xs font-bold uppercase tracking-widest text-emerald-400 hover:bg-emerald-500 hover:text-white transition-all shadow-sm"
              >
                <CheckIcon class="h-4 w-4" />
                Approve
              </button>
              <button
                @click="cancel(appointment.id)"
                class="inline-flex justify-center items-center px-4 rounded-xl border border-red-500/20 py-2.5 text-xs font-bold uppercase tracking-widest text-red-400 hover:bg-red-500/10 hover:border-red-500/50 transition-all"
                title="Cancel Appointment"
              >
                <XMarkIcon class="h-4 w-4" />
              </button>
            </template>
            
            <template v-else-if="appointment.status === 'confirmed'">
              <button
                @click="promptComplete(appointment.id)"
                class="flex-1 inline-flex justify-center items-center gap-1.5 rounded-xl bg-gold py-2.5 text-xs font-bold uppercase tracking-widest text-obsidian hover:bg-gold-light hover:shadow-[0_0_15px_rgba(212,175,55,0.4)] transition-all shadow-sm"
              >
                <CheckIcon class="h-4 w-4" />
                Complete
              </button>
              <button
                @click="markNoShow(appointment.id)"
                class="inline-flex justify-center items-center px-4 rounded-xl border border-amber-500/20 py-2.5 text-xs font-bold uppercase tracking-widest text-amber-500 hover:bg-amber-500/10 hover:border-amber-500/50 transition-all"
                title="Mark as No Show"
              >
                No Show
              </button>
              <button
                @click="cancel(appointment.id)"
                class="inline-flex justify-center items-center px-4 rounded-xl border border-red-500/20 py-2.5 text-xs font-bold uppercase tracking-widest text-red-400 hover:bg-red-500/10 hover:border-red-500/50 transition-all"
                title="Cancel Appointment"
              >
                <XMarkIcon class="h-4 w-4" />
              </button>
            </template>
          </div>
        </article>

        <!-- Empty State -->
        <div v-if="filteredAppointments.length === 0" class="col-span-full py-24 text-center rounded-3xl border border-dashed border-theme-border bg-theme-surface/30">
          <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-white/[0.02] border border-white/[0.05] shadow-inner mb-6">
            <ClipboardDocumentCheckIcon class="h-10 w-10 text-gold/30" />
          </div>
          <h3 class="font-display text-2xl text-theme-text mb-2">No Appointments</h3>
          <p class="text-sm text-ivory/40">You don't have any appointments matching this criteria.</p>
        </div>
      </div>
    </section>

    <!-- Receipt Viewer Modal -->
    <ReceiptViewer 
      v-if="selectedBooking" 
      :booking="selectedBooking" 
      @close="selectedBooking = null" 
      @approve="handleReceiptApproved" 
      @reject="handleReceiptRejected"
      portal="barber"
    />

    <!-- Verification Code Modal -->
    <div v-if="completingBookingId" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-obsidian/80 backdrop-blur-sm" @click="closeCompleteModal"></div>
      <div class="relative w-full max-w-sm rounded-3xl border border-theme-border bg-theme-surface/90 p-8 shadow-2xl backdrop-blur-xl animate-fade-in text-center">
        <button @click="closeCompleteModal" class="absolute top-4 right-4 text-ivory/40 hover:text-white transition-colors">
          <XMarkIcon class="h-6 w-6" />
        </button>
        
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gold/10 border border-gold/30 mb-6">
          <CheckIcon class="h-8 w-8 text-gold" />
        </div>
        
        <h3 class="font-display text-2xl text-theme-text mb-2">Service Verification</h3>
        <p class="text-sm text-ivory/60 mb-6">
          Please ask the customer for their verification code to complete this booking.
        </p>
        
        <div class="relative w-full mb-6 group">
          <input 
            v-model="verificationCodeInput" 
            type="text" 
            placeholder="MW2406261234"
            maxlength="20"
            class="w-full text-center text-2xl font-mono tracking-widest font-bold rounded-xl border border-white/10 bg-obsidian/80 py-4 pr-14 text-emerald-400 placeholder-white/20 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 uppercase transition-all shadow-inner"
          />
          <button 
            type="button"
            @click.prevent="pasteCode" 
            class="absolute right-3 top-1/2 -translate-y-1/2 p-2 text-gold/70 hover:text-gold hover:bg-gold/10 rounded-lg transition-all z-10"
            title="Paste Code"
          >
            <ClipboardDocumentIcon class="h-6 w-6" />
          </button>
        </div>
        
        <button 
          @click="submitComplete"
          :disabled="isVerifying || verificationCodeInput.length < 12"
          class="w-full rounded-xl bg-gold py-3.5 text-sm font-bold text-obsidian transition-all hover:bg-gold-light hover:shadow-[0_0_20px_rgba(212,175,55,0.3)] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
        >
          <ArrowPathIcon v-if="isVerifying" class="h-5 w-5 animate-spin" />
          {{ isVerifying ? 'Verifying...' : 'Complete Appointment' }}
        </button>
      </div>
    </div>

  </BarberLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import BarberLayout from '../layouts/BarberLayout.vue';
import { barberApi } from '../api/barber.api';
import ReceiptViewer from '../../../core/components/ReceiptViewer.vue';
import { useToast } from '../../../core/composables/useToast';
import { useConfirm } from '../../../core/composables/useConfirm';
import {
  MagnifyingGlassIcon,
  CheckIcon,
  ClipboardDocumentCheckIcon,
  XMarkIcon,
  ArrowPathIcon,
  CalendarIcon,
  ClockIcon,
  PhoneIcon,
  EnvelopeIcon,
  ClipboardDocumentIcon,
  UserPlusIcon,
  GlobeAltIcon,
  BanknotesIcon
} from '@heroicons/vue/24/outline';

const toast = useToast();
const { confirm } = useConfirm();

const appointments = ref([]);
const search = ref('');
const currentFilter = ref('all');
const selectedBooking = ref(null);

const completingBookingId = ref(null);
const verificationCodeInput = ref('');
const isVerifying = ref(false);

// Walk-in state
const showWalkInModal = ref(false);
const walkInSubmitting = ref(false);
const servicesList = ref([]);
const walkInForm = ref({
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  service_id: '',
  appointment_date: new Date().toISOString().split('T')[0],
  appointment_time: new Date().toTimeString().slice(0, 5),
  payment_method: 'cash'
});

const tabs = computed(() => {
  const list = appointments.value.data || appointments.value || [];
  if (!Array.isArray(list)) return [];
  return [
    { label: 'All', value: 'all', count: list.length },
    { label: 'Pending', value: 'pending', count: list.filter(a => a.status === 'pending').length },
    { label: 'Confirmed', value: 'confirmed', count: list.filter(a => a.status === 'confirmed').length },
    { label: 'Completed', value: 'completed', count: list.filter(a => a.status === 'completed').length },
    { label: 'Cancelled', value: 'cancelled', count: list.filter(a => a.status === 'cancelled').length },
    { label: '🟣 Walk-In', value: 'walk_in', count: list.filter(a => a.booking_type === 'walk_in').length },
    { label: '🔵 Online', value: 'online', count: list.filter(a => a.booking_type !== 'walk_in').length }
  ];
});

const filteredAppointments = computed(() => {
  let list = appointments.value.data || appointments.value;
  if (!Array.isArray(list)) return [];
  
  if (currentFilter.value !== 'all') {
    if (currentFilter.value === 'walk_in') {
      list = list.filter(a => a.booking_type === 'walk_in');
    } else if (currentFilter.value === 'online') {
      list = list.filter(a => a.booking_type !== 'walk_in');
    } else {
      list = list.filter(a => a.status === currentFilter.value);
    }
  }
  
  if (!search.value) return list;
  const q = search.value.toLowerCase();
  return list.filter(a => (a.customer_name || '').toLowerCase().includes(q));
});

function initials(name) {
  return (name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

// ── Status-based color helpers ──
const statusColors = {
  pending: {
    card: 'border-amber-500/20 bg-gradient-to-br from-amber-500/[0.06] via-theme-surface/60 to-theme-surface/60 hover:border-amber-400/35 hover:shadow-[0_20px_50px_rgba(245,158,11,0.12)]',
    glow: 'bg-amber-500/8 group-hover:bg-amber-500/15',
    accentLine: 'bg-gradient-to-r from-transparent via-amber-400/60 to-transparent',
    avatar: 'bg-gradient-to-br from-amber-500/25 to-amber-600/10 text-amber-400 border-amber-500/25',
    serviceText: 'text-amber-400/80',
    badge: 'bg-amber-400/15 text-amber-400 border-amber-400/25 shadow-[0_0_10px_rgba(245,158,11,0.1)]',
    detailsCard: 'bg-amber-950/20 border-amber-500/10',
    icon: 'text-amber-400/70',
    timeText: 'text-amber-400',
  },
  confirmed: {
    card: 'border-blue-500/20 bg-gradient-to-br from-blue-500/[0.06] via-theme-surface/60 to-theme-surface/60 hover:border-blue-400/35 hover:shadow-[0_20px_50px_rgba(59,130,246,0.12)]',
    glow: 'bg-blue-500/8 group-hover:bg-blue-500/15',
    accentLine: 'bg-gradient-to-r from-transparent via-blue-400/60 to-transparent',
    avatar: 'bg-gradient-to-br from-blue-500/25 to-blue-600/10 text-blue-400 border-blue-500/25',
    serviceText: 'text-blue-400/80',
    badge: 'bg-blue-400/15 text-blue-400 border-blue-400/25 shadow-[0_0_10px_rgba(59,130,246,0.1)]',
    detailsCard: 'bg-blue-950/20 border-blue-500/10',
    icon: 'text-blue-400/70',
    timeText: 'text-blue-400',
  },
  completed: {
    card: 'border-emerald-500/20 bg-gradient-to-br from-emerald-500/[0.06] via-theme-surface/60 to-theme-surface/60 hover:border-emerald-400/35 hover:shadow-[0_20px_50px_rgba(16,185,129,0.12)]',
    glow: 'bg-emerald-500/8 group-hover:bg-emerald-500/15',
    accentLine: 'bg-gradient-to-r from-transparent via-emerald-400/60 to-transparent',
    avatar: 'bg-gradient-to-br from-emerald-500/25 to-emerald-600/10 text-emerald-400 border-emerald-500/25',
    serviceText: 'text-emerald-400/80',
    badge: 'bg-emerald-400/15 text-emerald-400 border-emerald-400/25 shadow-[0_0_10px_rgba(16,185,129,0.1)]',
    detailsCard: 'bg-emerald-950/20 border-emerald-500/10',
    icon: 'text-emerald-400/70',
    timeText: 'text-emerald-400',
  },
  cancelled: {
    card: 'border-red-500/20 bg-gradient-to-br from-red-500/[0.06] via-theme-surface/60 to-theme-surface/60 hover:border-red-400/35 hover:shadow-[0_20px_50px_rgba(239,68,68,0.12)]',
    glow: 'bg-red-500/8 group-hover:bg-red-500/15',
    accentLine: 'bg-gradient-to-r from-transparent via-red-400/60 to-transparent',
    avatar: 'bg-gradient-to-br from-red-500/25 to-red-600/10 text-red-400 border-red-500/25',
    serviceText: 'text-red-400/80',
    badge: 'bg-red-400/15 text-red-400 border-red-400/25 shadow-[0_0_10px_rgba(239,68,68,0.1)]',
    detailsCard: 'bg-red-950/20 border-red-500/10',
    icon: 'text-red-400/70',
    timeText: 'text-red-400',
  },
  no_show: {
    card: 'border-gray-500/20 bg-gradient-to-br from-gray-500/[0.04] via-theme-surface/60 to-theme-surface/60 hover:border-gray-400/30 hover:shadow-[0_20px_50px_rgba(107,114,128,0.08)]',
    glow: 'bg-gray-500/5 group-hover:bg-gray-500/10',
    accentLine: 'bg-gradient-to-r from-transparent via-gray-500/40 to-transparent',
    avatar: 'bg-gradient-to-br from-gray-500/20 to-gray-600/10 text-gray-400 border-gray-500/20',
    serviceText: 'text-gray-400/70',
    badge: 'bg-gray-400/15 text-gray-400 border-gray-400/25',
    detailsCard: 'bg-gray-950/20 border-gray-500/10',
    icon: 'text-gray-400/70',
    timeText: 'text-gray-400',
  },
};

const fallback = statusColors.pending;

function cardClasses(status) { return (statusColors[status] || fallback).card; }
function glowClasses(status) { return (statusColors[status] || fallback).glow; }
function accentLineClasses(status) { return (statusColors[status] || fallback).accentLine; }
function avatarClasses(status) { return (statusColors[status] || fallback).avatar; }
function serviceTextClasses(status) { return (statusColors[status] || fallback).serviceText; }
function badgeClasses(status) { return (statusColors[status] || fallback).badge; }
function detailsCardClasses(status) { return (statusColors[status] || fallback).detailsCard; }
function iconClasses(status) { return (statusColors[status] || fallback).icon; }
function timeTextClasses(status) { return (statusColors[status] || fallback).timeText; }

function statusClass(status) {
  const s = (status || '').toLowerCase();
  if (s === 'confirmed' || s === 'completed') return 'bg-emerald-400/10 text-emerald-400';
  if (s === 'pending') return 'bg-gold/10 text-gold';
  if (s === 'cancelled' || s === 'no_show') return 'bg-red-400/10 text-red-400';
  return 'bg-white/[0.06] text-ivory/50';
}

async function loadAppointments() {
  try {
    const response = await barberApi.getBookings();
    appointments.value = response.data.data;
  } catch (e) {
    toast.error('Failed to load appointments');
  }
}

const viewReceipt = (booking) => {
  const mapped = {
    ...booking,
    customer: {
      name: booking.customer_name,
      email: booking.customer_email,
      phone: booking.customer_phone
    },
    service: {
      name: booking.service_name,
      price: booking.price,
      duration_minutes: booking.duration_minutes
    },
    barber: {
      name: 'You'
    }
  };
  selectedBooking.value = mapped;
};

const handleReceiptApproved = async () => {
  selectedBooking.value = null;
  await loadAppointments();
  toast.success('Payment approved and booking confirmed!');
};

const handleReceiptRejected = async () => {
  selectedBooking.value = null;
  await loadAppointments();
  toast.error('Payment rejected and booking cancelled');
};

async function approve(id) {
  if (await confirm('Approve Booking', 'Are you sure you want to approve this booking manually?')) {
    try {
      await barberApi.approveBooking(id);
      await loadAppointments();
      toast.success('Booking approved and confirmed!');
    } catch (err) {
      toast.error('Failed to approve booking');
    }
  }
}

async function cancel(id) {
  if (await confirm('Cancel Booking', 'Are you sure you want to cancel this booking? This action cannot be undone.')) {
    try {
      await barberApi.cancelBooking(id);
      await loadAppointments();
      toast.success('Booking cancelled');
    } catch (err) {
      toast.error('Failed to cancel booking');
    }
  }
}

function promptComplete(id) {
  const list = appointments.value.data || appointments.value || [];
  const appointment = list.find(a => a.id === id);
  // Walk-in bookings skip verification code entirely
  if (appointment && appointment.booking_type === 'walk_in') {
    completingBookingId.value = id;
    submitComplete(true);
    return;
  }
  completingBookingId.value = id;
  verificationCodeInput.value = '';
}

function closeCompleteModal() {
  completingBookingId.value = null;
  verificationCodeInput.value = '';
}

async function pasteCode() {
  try {
    if (!navigator.clipboard || !navigator.clipboard.readText) {
      toast.error('Browser does not support clipboard pasting directly. Please paste manually.');
      return;
    }
    const text = await navigator.clipboard.readText();
    if (text) {
      verificationCodeInput.value = text.trim().toUpperCase();
      toast.success('Code pasted');
    }
  } catch (err) {
    console.error('Clipboard error:', err);
    toast.error('Could not read from clipboard. Please paste manually.');
  }
}

async function submitComplete(isWalkin = false) {
  if (isWalkin !== true && (!verificationCodeInput.value || verificationCodeInput.value.length !== 12)) {
    toast.error('Please enter a valid 12-character code');
    return;
  }
  
  isVerifying.value = true;
  try {
    await barberApi.complete(completingBookingId.value, { verification_code: verificationCodeInput.value || '' });
    await loadAppointments();
    toast.success('Booking verified and marked as completed!');
    closeCompleteModal();
  } catch (err) {
    toast.error(err.response?.data?.error || 'Failed to verify booking');
  } finally {
    isVerifying.value = false;
  }
}

async function markNoShow(id) {
  try {
    await barberApi.noShow(id);
    await loadAppointments();
    toast.success('Booking marked as no-show');
  } catch (err) {
    toast.error('Failed to mark as no-show');
  }
}

function closeWalkInModal() {
  showWalkInModal.value = false;
  walkInForm.value = {
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    service_id: '',
    appointment_date: new Date().toISOString().split('T')[0],
    appointment_time: new Date().toTimeString().slice(0, 5),
    payment_method: 'cash'
  };
}

async function loadServices() {
  try {
    const res = await barberApi.getServices();
    servicesList.value = res.data.data || [];
  } catch (e) {
    // Silently fail, services dropdown will just be empty
  }
}

async function submitWalkIn() {
  if (!walkInForm.value.customer_name || !walkInForm.value.customer_phone || !walkInForm.value.service_id) {
    toast.error('Please fill in all required fields');
    return;
  }
  walkInSubmitting.value = true;
  try {
    await barberApi.createWalkIn(walkInForm.value);
    toast.success('Walk-in appointment created successfully!');
    closeWalkInModal();
    await loadAppointments();
  } catch (err) {
    toast.error(err.response?.data?.error || 'Failed to create walk-in appointment');
  } finally {
    walkInSubmitting.value = false;
  }
}

onMounted(() => {
  loadAppointments();
  loadServices();
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Custom hide scrollbar */
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>