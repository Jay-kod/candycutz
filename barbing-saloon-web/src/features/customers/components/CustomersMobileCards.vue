<template>
  <div class="md:hidden space-y-3.5">
    <article
      v-for="customer in customers"
      :key="customer.id"
      class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-4 flex flex-col gap-3 shadow-lg transition-all duration-200 hover:border-admin/40"
    >
      <!-- Header: Avatar, Name, Joined, Channel -->
      <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <div class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-admin/25 to-admin/10 border border-admin/30 text-admin font-bold text-xs shadow-inner overflow-hidden">
            <img v-if="customer.avatar" :src="customer.avatar" :alt="customer.name" class="h-full w-full object-cover" />
            <span v-else>{{ initials(customer.name) }}</span>
            <div v-if="isNewClient(customer)" class="absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full bg-admin border-2 border-[#12161C]" title="New Client"></div>
          </div>
          <div class="min-w-0">
            <p class="text-sm font-bold text-white truncate">{{ customer.name }}</p>
            <p class="text-[10px] font-mono text-white/40 flex items-center gap-1 mt-0.5">
              <span>Joined {{ formatDate(customer.created_at) }}</span>
            </p>
          </div>
        </div>

        <span 
          class="inline-flex shrink-0 rounded-md border px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider"
          :class="channelBadgeClass(customer)"
        >
          {{ customer.registered_via || 'Registration' }}
        </span>
      </div>

      <!-- Contact Info -->
      <div class="py-2 border-y border-white/[0.05] space-y-1 text-xs">
        <div v-if="customer.email" class="flex items-center gap-1.5 text-white/70 truncate">
          <EnvelopeIcon class="h-3.5 w-3.5 text-white/30 shrink-0" />
          <a :href="`mailto:${customer.email}`" class="truncate hover:text-white">{{ customer.email }}</a>
        </div>
        <div v-if="customer.phone && customer.phone !== 'N/A'" class="flex items-center gap-1.5 text-admin font-mono">
          <PhoneIcon class="h-3.5 w-3.5 text-admin/70 shrink-0" />
          <a :href="`tel:${customer.phone}`" class="hover:underline">{{ customer.phone }}</a>
        </div>
      </div>

      <!-- Stats Grid: Bookings & Spent -->
      <div class="grid grid-cols-2 gap-2 text-xs">
        <div class="rounded-xl bg-white/[0.03] border border-white/[0.05] p-2.5">
          <span class="text-[9px] uppercase tracking-wider text-white/40 block mb-0.5">Total Bookings</span>
          <p class="text-sm font-bold text-white flex items-center gap-1.5">
            <ChartBarIcon class="h-3.5 w-3.5 text-blue-400" />
            <span>{{ customer.total_bookings || 0 }}</span>
          </p>
        </div>
        <div class="rounded-xl bg-white/[0.03] border border-white/[0.05] p-2.5">
          <span class="text-[9px] uppercase tracking-wider text-white/40 block mb-0.5">Lifetime Value</span>
          <p class="text-sm font-bold text-emerald-400 font-mono">
            ₦{{ (customer.total_spent || 0).toLocaleString() }}
          </p>
        </div>
      </div>

      <!-- Action Button -->
      <div class="pt-0.5 flex justify-end">
        <button
          @click="$emit('viewProfile', customer)"
          class="w-full sm:w-auto rounded-xl bg-white/[0.06] border border-white/10 px-4 py-2 text-xs font-semibold text-white hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5"
        >
          <DocumentTextIcon class="h-3.5 w-3.5 text-white/60" />
          <span>View Profile & History</span>
        </button>
      </div>
    </article>
  </div>
</template>

<script setup>
import { 
  EnvelopeIcon, 
  PhoneIcon, 
  ChartBarIcon, 
  DocumentTextIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  customers: {
    type: Array,
    required: true
  }
});

defineEmits(['viewProfile']);

function initials(name) {
  return (name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function formatDate(dateStr) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function isNewClient(customer) {
  if (!customer.created_at) return false;
  const created = new Date(customer.created_at);
  const now = new Date();
  return (now - created) / (1000 * 60 * 60 * 24) < 14;
}

function channelBadgeClass(customer) {
  const prov = (customer.auth_provider || 'local').toLowerCase();
  if (prov === 'google') return 'text-red-300 bg-red-500/10 border-red-500/20';
  if (prov === 'apple') return 'text-white bg-white/10 border-white/20';
  if (customer.registered_via && customer.registered_via.toLowerCase().includes('app')) {
    return 'text-purple-300 bg-purple-500/10 border-purple-500/20';
  }
  return 'text-white/50 bg-white/5 border-white/10';
}
</script>
