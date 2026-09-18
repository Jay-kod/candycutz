<template>
  <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <article
      v-for="appointment in appointments"
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
            @click="$emit('viewReceipt', appointment)"
            class="flex-1 inline-flex justify-center items-center gap-1.5 rounded-xl bg-blue-500/10 border border-blue-500/20 py-2.5 text-xs font-bold uppercase tracking-widest text-blue-400 hover:bg-blue-500 hover:text-white transition-all shadow-sm"
          >
            Receipt
          </button>
          <button
            v-else
            @click="$emit('approve', appointment.id)"
            class="flex-1 inline-flex justify-center items-center gap-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 py-2.5 text-xs font-bold uppercase tracking-widest text-emerald-400 hover:bg-emerald-500 hover:text-white transition-all shadow-sm"
          >
            <CheckIcon class="h-4 w-4" />
            Approve
          </button>
          <button
            @click="$emit('cancel', appointment.id)"
            class="inline-flex justify-center items-center px-4 rounded-xl border border-red-500/20 py-2.5 text-xs font-bold uppercase tracking-widest text-red-400 hover:bg-red-500/10 hover:border-red-500/50 transition-all"
            title="Cancel Appointment"
          >
            <XMarkIcon class="h-4 w-4" />
          </button>
        </template>
        
        <template v-else-if="appointment.status === 'confirmed'">
          <button
            @click="$emit('promptComplete', appointment.id)"
            class="flex-1 inline-flex justify-center items-center gap-1.5 rounded-xl bg-gold py-2.5 text-xs font-bold uppercase tracking-widest text-obsidian hover:bg-gold-light hover:shadow-[0_0_15px_rgba(212,175,55,0.4)] transition-all shadow-sm"
          >
            <CheckIcon class="h-4 w-4" />
            Complete
          </button>
          <button
            @click="$emit('markNoShow', appointment.id)"
            class="inline-flex justify-center items-center px-4 rounded-xl border border-amber-500/20 py-2.5 text-xs font-bold uppercase tracking-widest text-amber-500 hover:bg-amber-500/10 hover:border-amber-500/50 transition-all"
            title="Mark as No Show"
          >
            No Show
          </button>
          <button
            @click="$emit('cancel', appointment.id)"
            class="inline-flex justify-center items-center px-4 rounded-xl border border-red-500/20 py-2.5 text-xs font-bold uppercase tracking-widest text-red-400 hover:bg-red-500/10 hover:border-red-500/50 transition-all"
            title="Cancel Appointment"
          >
            <XMarkIcon class="h-4 w-4" />
          </button>
        </template>
      </div>
    </article>

    <!-- Empty State -->
    <div v-if="appointments.length === 0" class="col-span-full py-24 text-center rounded-3xl border border-dashed border-theme-border bg-theme-surface/30">
      <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-white/[0.02] border border-white/[0.05] shadow-inner mb-6">
        <ClipboardDocumentCheckIcon class="h-10 w-10 text-gold/30" />
      </div>
      <h3 class="font-display text-2xl text-theme-text mb-2">No Appointments</h3>
      <p class="text-sm text-ivory/40">You don't have any appointments matching this criteria.</p>
    </div>
  </div>
</template>

<script setup>
import {
  CalendarIcon,
  ClockIcon,
  PhoneIcon,
  EnvelopeIcon,
  UserPlusIcon,
  GlobeAltIcon,
  BanknotesIcon,
  CheckIcon,
  XMarkIcon,
  ClipboardDocumentCheckIcon
} from '@heroicons/vue/24/outline';

defineProps({
  appointments: {
    type: Array,
    required: true
  }
});

defineEmits(['viewReceipt', 'approve', 'cancel', 'promptComplete', 'markNoShow']);

function initials(name) {
  return (name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

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
</script>
