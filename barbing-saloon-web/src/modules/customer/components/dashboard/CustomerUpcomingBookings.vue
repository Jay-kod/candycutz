<template>
  <article class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-6 shadow-sm flex flex-col justify-between">
    <div>
      <div class="flex items-center justify-between pb-4 border-b border-[var(--color-border-subtle)]">
        <h2 class="font-display text-lg sm:text-xl text-[var(--color-text-primary)] flex items-center gap-2">
          <CalendarIcon class="w-5 h-5 text-[var(--color-accent)]" />
          <span>Upcoming <strong class="text-[var(--color-accent)] font-semibold">Appointments</strong></span>
        </h2>
        <RouterLink to="/customer/dashboard/bookings" class="text-xs font-bold text-[var(--color-accent)] hover:underline">
          View all &rarr;
        </RouterLink>
      </div>

      <div class="mt-5 space-y-4">
        <div 
          v-for="booking in appointments" 
          :key="booking.id" 
          class="group rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/50 p-4 sm:p-5 transition-all duration-300 hover:border-[var(--color-accent)]/40 hover:bg-[var(--color-bg-tertiary)]"
        >
          <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div class="flex gap-3.5">
              <div class="h-11 w-11 shrink-0 flex items-center justify-center rounded-xl bg-[var(--color-accent)]/10 text-[var(--color-accent)] border border-[var(--color-accent)]/20 shadow-inner">
                <CalendarIcon class="h-5 w-5" />
              </div>
              <div class="min-w-0">
                <p class="font-display text-sm sm:text-base text-[var(--color-text-primary)] group-hover:text-[var(--color-accent)] transition-colors truncate">
                  {{ booking.service?.name || 'Grooming Session' }}
                </p>
                <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[var(--color-text-secondary)]">
                  <span class="flex items-center gap-1.5 font-mono">
                    <ClockIcon class="h-3.5 w-3.5 text-[var(--color-accent)]" />
                    {{ booking.appointment_date }} at {{ booking.appointment_time }}
                  </span>
                  <span class="text-[var(--color-border)]">•</span>
                  <span class="flex items-center gap-1.5">
                    <UserIcon class="h-3.5 w-3.5 text-[var(--color-accent)]" />
                    {{ booking.barber?.name || 'Assigned Barber' }}
                  </span>
                </div>

                <!-- Verification Check-in Code -->
                <div v-if="booking.status === 'confirmed' && booking.verification_code" class="mt-3 flex items-center gap-2">
                  <span class="text-[10px] uppercase tracking-widest text-emerald-600 dark:text-emerald-400 font-bold">Check-in Code:</span>
                  <div class="inline-flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/30 rounded-lg px-2.5 py-1 text-emerald-600 dark:text-emerald-400 font-mono text-xs tracking-wider font-bold">
                    <span>{{ booking.verification_code }}</span>
                    <button 
                      type="button"
                      @click.prevent="$emit('copy', booking.verification_code)"
                      class="p-0.5 hover:bg-emerald-500/20 rounded transition-colors cursor-pointer"
                      title="Copy Code"
                    >
                      <DocumentDuplicateIcon class="h-3.5 w-3.5" />
                    </button>
                  </div>
                </div>

                <!-- Pending Bank Transfer Alert -->
                <div v-if="booking.status === 'pending' && !booking.deposit_paid" class="mt-3 rounded-lg border border-[var(--color-accent)]/30 bg-[var(--color-accent)]/5 p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                  <div class="flex items-center gap-2 min-w-0">
                    <BuildingLibraryIcon class="h-4 w-4 text-[var(--color-accent)] shrink-0" />
                    <span class="text-xs text-[var(--color-text-secondary)] truncate">
                      Transfer to <strong class="text-[var(--color-text-primary)]">{{ getBank(booking).bank_name }}</strong>:
                      <span class="font-mono text-[var(--color-text-primary)] font-bold tracking-wider ml-1">{{ getBank(booking).account_number }}</span>
                    </span>
                    <button 
                      type="button"
                      @click.prevent="$emit('copy', getBank(booking).account_number)"
                      class="p-1 text-[var(--color-accent)] hover:bg-[var(--color-accent)]/15 rounded transition-colors cursor-pointer shrink-0"
                      title="Copy Account Number"
                    >
                      <DocumentDuplicateIcon class="h-3.5 w-3.5" />
                    </button>
                  </div>
                  <RouterLink 
                    to="/customer/dashboard/bookings" 
                    class="text-[11px] font-bold text-[var(--color-accent)] hover:underline flex items-center gap-1 shrink-0"
                  >
                    <span>Upload Receipt</span>
                    <span>&rarr;</span>
                  </RouterLink>
                </div>
              </div>
            </div>

            <StatusBadge :status="booking.status || 'pending'" size="sm" class="self-start" />
          </div>
        </div>

        <div v-if="appointments.length === 0" class="rounded-xl border border-dashed border-[var(--color-border)] p-8 text-center bg-[var(--color-bg-tertiary)]/30">
          <p class="text-xs sm:text-sm text-[var(--color-text-secondary)]">No upcoming appointments scheduled.</p>
          <RouterLink to="/customer/dashboard/services" class="mt-2.5 inline-flex items-center gap-1.5 text-xs font-bold text-[var(--color-accent)] hover:underline">
            Explore available grooming services &rarr;
          </RouterLink>
        </div>
      </div>
    </div>
  </article>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { CalendarIcon, ClockIcon, UserIcon, DocumentDuplicateIcon, BuildingLibraryIcon } from '@heroicons/vue/24/outline';
import StatusBadge from '@/shared/ui/StatusBadge.vue';

const props = defineProps({
  appointments: {
    type: Array,
    default: () => [],
  },
  fallbackBank: {
    type: Object,
    default: () => ({
      bank_name: 'Zenith Bank',
      account_name: 'Candy Cutz Luxury Grooming',
      account_number: '1012345678',
    }),
  },
});

defineEmits(['copy']);

const getBank = (booking) => {
  if (booking?.bank_details?.bank_name && booking?.bank_details?.account_number) {
    return booking.bank_details;
  }
  return props.fallbackBank;
};
</script>

