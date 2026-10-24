<template>
  <div class="hidden md:block rounded-2xl border border-white/[0.08] bg-[#12161C] shadow-2xl overflow-hidden min-h-[380px]">
    <!-- Data Table -->
    <div v-if="customers.length > 0" class="overflow-x-auto">
      <table class="w-full text-left border-collapse min-w-[840px]">
        <thead>
          <tr class="divide-x divide-white/[0.04] border-b border-white/[0.08] bg-white/[0.02] text-[10px] uppercase tracking-[0.2em] text-white/40 font-bold">
            <th class="px-5 py-3.5">Customer</th>
            <th class="px-5 py-3.5">Contact Info</th>
            <th class="px-5 py-3.5">Engagement</th>
            <th class="px-5 py-3.5">Lifetime Value</th>
            <th class="px-5 py-3.5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/[0.04]">
          <tr
            v-for="customer in customers"
            :key="customer.id"
            class="group transition-colors hover:bg-white/[0.03] divide-x divide-white/[0.02]"
          >
            <!-- Customer -->
            <td class="px-5 py-4 align-middle">
              <div class="flex items-center gap-3.5">
                <div class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-admin/25 to-admin/10 border border-admin/30 text-admin font-bold text-xs shadow-[inset_0_1px_0_rgba(255,255,255,0.1)] group-hover:scale-105 transition-transform duration-300 overflow-hidden">
                  <img v-if="customer.avatar" :src="customer.avatar" :alt="customer.name" class="h-full w-full object-cover" />
                  <span v-else>{{ initials(customer.name) }}</span>
                  <div v-if="isNewClient(customer)" class="absolute -top-1 -right-1 h-3 w-3 rounded-full bg-admin border-2 border-[#12161C]" title="New Client"></div>
                </div>
                <div class="min-w-0">
                  <p class="text-sm font-bold text-white whitespace-nowrap">{{ customer.name }}</p>
                  <p class="text-[11px] font-mono text-white/40 flex items-center gap-1.5 mt-0.5">
                    <CalendarDaysIcon class="h-3 w-3 text-white/30" />
                    <span>Joined {{ formatDate(customer.created_at) }}</span>
                  </p>
                  <div class="mt-1">
                    <span 
                      class="inline-flex rounded-md border px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider"
                      :class="channelBadgeClass(customer)"
                    >
                      {{ customer.registered_via || 'Registration form' }}
                    </span>
                  </div>
                </div>
              </div>
            </td>

            <!-- Contact Info -->
            <td class="px-5 py-4 align-middle">
              <div class="space-y-1">
                <a 
                  v-if="customer.email" 
                  :href="`mailto:${customer.email}`"
                  class="text-xs text-white/80 font-medium hover:text-white flex items-center gap-1.5 truncate max-w-[220px]"
                >
                  <EnvelopeIcon class="h-3.5 w-3.5 text-white/30 shrink-0" />
                  <span class="truncate">{{ customer.email }}</span>
                </a>
                <span v-else class="text-xs text-white/30 flex items-center gap-1.5">
                  <EnvelopeIcon class="h-3.5 w-3.5 text-white/20 shrink-0" />
                  <span>No email</span>
                </span>

                <div class="flex items-center gap-2">
                  <a 
                    v-if="customer.phone && customer.phone !== 'N/A'" 
                    :href="`tel:${customer.phone}`"
                    class="text-[11px] font-mono text-admin/90 hover:underline flex items-center gap-1"
                  >
                    <PhoneIcon class="h-3 w-3 text-admin/70" />
                    <span>{{ customer.phone }}</span>
                  </a>
                  <span v-else class="text-[11px] font-mono text-white/30">
                    No phone
                  </span>
                </div>
              </div>
            </td>

            <!-- Engagement -->
            <td class="px-5 py-4 align-middle">
              <div class="flex flex-col gap-1 whitespace-nowrap">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/[0.04] border border-white/[0.06] px-2.5 py-1 text-xs font-semibold text-white/90 w-fit">
                  <ChartBarIcon class="h-3.5 w-3.5 text-blue-400" />
                  <span>{{ customer.total_bookings || 0 }} {{ (customer.total_bookings === 1) ? 'Booking' : 'Bookings' }}</span>
                </span>
                <span class="text-[11px] text-white/40 mt-0.5">
                  Last visit: <span class="text-white/60 font-medium">{{ timeAgo(customer.last_booking_date || customer.last_visit) }}</span>
                </span>
              </div>
            </td>

            <!-- Lifetime Value -->
            <td class="px-5 py-4 align-middle">
              <div class="flex flex-col gap-1 whitespace-nowrap">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/25 px-2.5 py-1 text-xs font-mono font-bold text-emerald-400 w-fit">
                  ₦{{ (customer.total_spent || 0).toLocaleString() }}
                </span>
                <span v-if="(customer.total_bookings || 0) > 0" class="text-[10px] text-white/40">
                  Avg: ₦{{ Math.round((customer.total_spent || 0) / customer.total_bookings).toLocaleString() }} / visit
                </span>
              </div>
            </td>

            <!-- Actions -->
            <td class="px-5 py-4 text-right align-middle">
              <button
                @click="$emit('viewProfile', customer)"
                class="rounded-xl bg-white/[0.05] border border-white/10 px-3.5 py-2 text-xs font-semibold text-white hover:bg-white/[0.12] hover:border-white/20 transition-all inline-flex items-center gap-1.5 shadow-sm"
              >
                <DocumentTextIcon class="h-3.5 w-3.5 text-white/60" />
                <span>View Profile</span>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-else class="flex flex-col items-center justify-center py-20 px-6 text-center">
      <div class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-white/[0.03] border border-white/[0.08] mb-4">
        <UserGroupIcon class="h-8 w-8 text-white/30" />
      </div>
      <h3 class="text-lg font-bold text-white mb-1.5">No Customers Found</h3>
      <p class="text-xs text-white/40 max-w-sm mx-auto">
        {{ search ? `No results match your search "${search}".` : `There are no customers matching the selected criteria.` }}
      </p>
      <button 
        v-if="search || currentFilter !== 'all'" 
        @click="$emit('resetFilters')" 
        class="mt-5 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-admin/15 text-admin border border-admin/30 text-xs font-bold hover:bg-admin/25 transition-all"
      >
        Reset Filter Criteria
      </button>
    </div>
  </div>
</template>

<script setup>
import { 
  UserGroupIcon, 
  CalendarDaysIcon, 
  ChartBarIcon, 
  DocumentTextIcon,
  EnvelopeIcon,
  PhoneIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  customers: {
    type: Array,
    required: true
  },
  search: String,
  currentFilter: String
});

defineEmits(['viewProfile', 'resetFilters']);

function initials(name) {
  return (name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function formatDate(dateStr) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function timeAgo(dateStr) {
  if (!dateStr) return 'Never';
  const now = new Date();
  const then = new Date(dateStr);
  const diff = Math.floor((now - then) / 1000);
  if (diff < 86400) return 'Today';
  if (diff < 172800) return 'Yesterday';
  if (diff < 604800) return `${Math.floor(diff / 86400)} days ago`;
  if (diff < 2592000) return `${Math.floor(diff / 604800)} weeks ago`;
  return formatDate(dateStr);
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