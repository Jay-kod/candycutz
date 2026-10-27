<template>
  <div class="rounded-2xl border border-theme-border bg-theme-surface overflow-hidden shadow-2xl relative">
    <!-- Top Brand Header Bar -->
    <div class="h-1.5 bg-gradient-to-r from-admin via-amber-400 to-amber-200"></div>

    <div class="p-6 sm:p-7 relative z-10">
      <!-- Ticket Header -->
      <div class="text-center pb-5 border-b border-theme-border">
        <div class="w-12 h-12 mx-auto rounded-xl bg-admin/10 border border-admin/20 flex items-center justify-center text-admin mb-3 shadow-inner">
          <TicketIcon class="h-6 w-6" />
        </div>
        <p class="text-[9px] font-mono uppercase tracking-[0.25em] text-admin font-bold">CandyCutz Luxury Grooming</p>
        <h3 class="text-lg font-display font-bold text-theme-text tracking-wide mt-0.5">Walk-In Intake Slip</h3>
        <p class="text-[10px] text-theme-text-muted font-mono mt-0.5">{{ formattedDate }}</p>
      </div>

      <!-- Ticket Data Fields -->
      <div class="py-5 space-y-4">
        <!-- Client -->
        <div class="flex items-center justify-between text-xs">
          <span class="text-theme-text-muted uppercase tracking-wider text-[10px] font-mono">Client</span>
          <span class="font-bold text-theme-text truncate max-w-[170px]">
            {{ form.customer_name || 'Guest Client' }}
          </span>
        </div>

        <!-- Phone -->
        <div class="flex items-center justify-between text-xs">
          <span class="text-theme-text-muted uppercase tracking-wider text-[10px] font-mono">Phone</span>
          <span class="font-mono text-theme-text">
            {{ form.customer_phone || '---' }}
          </span>
        </div>

        <!-- Service -->
        <div class="flex items-center justify-between text-xs">
          <span class="text-theme-text-muted uppercase tracking-wider text-[10px] font-mono">Service</span>
          <span class="font-bold text-admin-light truncate max-w-[170px]">
            {{ service?.name || 'Select a service' }}
          </span>
        </div>

        <!-- Stylist -->
        <div class="flex items-center justify-between text-xs">
          <span class="text-theme-text-muted uppercase tracking-wider text-[10px] font-mono">Stylist</span>
          <span class="font-bold text-theme-text truncate max-w-[170px]">
            {{ barber?.name || 'First Free Chair' }}
          </span>
        </div>

        <!-- Schedule Window -->
        <div class="flex items-center justify-between text-xs">
          <span class="text-theme-text-muted uppercase tracking-wider text-[10px] font-mono">Window</span>
          <span class="font-mono text-emerald-400">
            {{ form.appointment_time || 'Now' }} <span v-if="finishTime">→ {{ finishTime }}</span>
          </span>
        </div>

        <!-- Payment Method -->
        <div class="flex items-center justify-between text-xs">
          <span class="text-theme-text-muted uppercase tracking-wider text-[10px] font-mono">Payment</span>
          <span class="px-2 py-0.5 rounded bg-theme-bg border border-theme-border text-theme-text text-[10px] font-semibold uppercase tracking-wider">
            {{ paymentMethodLabel }}
          </span>
        </div>

        <!-- Price Banner -->
        <div class="mt-4 p-4 rounded-xl bg-gradient-to-r from-admin/15 to-transparent border border-admin/30 flex items-center justify-between">
          <div>
            <p class="text-[9px] uppercase tracking-widest text-theme-text-muted font-mono">Total Due</p>
            <p class="text-xs text-admin-light font-semibold">Standard salon fee</p>
          </div>
          <div class="text-2xl font-bold font-mono text-theme-text">
            ₦{{ Number(service?.price || 0).toLocaleString() }}
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="space-y-2.5 pt-2">
        <button
          type="button"
          @click="$emit('submit')"
          :disabled="submitting || !isValid"
          class="w-full flex items-center justify-center gap-2 py-3.5 px-4 rounded-xl bg-admin hover:bg-admin-light text-white text-xs font-bold uppercase tracking-widest transition-all shadow-lg shadow-admin/25 hover:shadow-admin/40 disabled:opacity-40 disabled:cursor-not-allowed hover:-translate-y-0.5"
        >
          <span v-if="submitting" class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
          <CheckBadgeIcon v-else class="h-4 w-4" />
          <span>{{ submitting ? 'Checking In...' : 'Check-In Client' }}</span>
        </button>

        <button
          type="button"
          @click="$emit('reset')"
          class="w-full py-2.5 text-center text-[11px] font-semibold text-theme-text-muted hover:text-theme-text transition-colors uppercase tracking-wider"
        >
          Clear Form
        </button>
      </div>

      <!-- Decorative Tear Line -->
      <div class="mt-6 pt-4 border-t border-dashed border-theme-border flex items-center justify-between text-[9px] font-mono text-theme-text-muted">
        <span>CANDYCUTZ-DESK-SLIP</span>
        <span>AUTH: VALID</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { TicketIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  form: { type: Object, required: true },
  service: { type: Object, default: null },
  barber: { type: Object, default: null },
  submitting: { type: Boolean, default: false },
  isValid: { type: Boolean, default: false }
});

defineEmits(['submit', 'reset']);

const formattedDate = computed(() => {
  return new Date().toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
});

const finishTime = computed(() => {
  if (!props.form.appointment_time || !props.service?.duration_minutes) return '';
  try {
    const [h, m] = props.form.appointment_time.split(':').map(Number);
    const date = new Date();
    date.setHours(h, m + Number(props.service.duration_minutes), 0);
    return date.toTimeString().slice(0, 5);
  } catch {
    return '';
  }
});

const paymentMethodLabel = computed(() => {
  const map = {
    cash: 'Cash',
    pos: 'POS Terminal',
    transfer: 'Bank Transfer',
    pending: 'Pay at Exit'
  };
  return map[props.form.payment_method] || 'Cash';
});
</script>
