<template>
  <div>
  <!-- Initiate Refund Modal -->
  <BaseModal :open="Boolean(refundRecord)" title="Initiate No-Show Refund" @close="$emit('close-refund')">
    <form v-if="refundRecord" @submit.prevent="handleRefundSubmit" class="space-y-4">
      <div class="p-3.5 rounded-xl bg-theme-surface border border-theme-border text-xs space-y-1">
        <p class="font-bold text-theme-text">
          Booking {{ refundRecord.appointment?.booking_reference }} · {{ refundRecord.customer?.name || refundRecord.appointment?.client_name }}
        </p>
        <p class="text-theme-text-muted">
          Paid Amount: ₦{{ Number(refundRecord.paid_amount_naira || 0).toLocaleString() }} ({{ refundRecord.appointment?.payment?.provider || 'gateway' }})
        </p>
      </div>

      <label class="block space-y-1.5">
        <span class="text-xs font-semibold text-theme-text">Refund Amount (₦)</span>
        <input
          v-model.number="refundNaira"
          type="number"
          min="1"
          :max="refundRecord.paid_amount_naira || 1000000"
          step="1"
          required
          class="w-full px-3.5 py-2.5 rounded-xl bg-theme-surface border border-theme-border text-xs font-mono text-theme-text focus:outline-none focus:border-admin"
        />
      </label>

      <label class="block space-y-1.5">
        <span class="text-xs font-semibold text-theme-text">Reason / Refund Note</span>
        <textarea
          v-model="refundReason"
          rows="3"
          required
          placeholder="e.g. Customer unwell — compassionate refund approved by admin"
          class="w-full p-3 rounded-xl bg-theme-surface border border-theme-border text-xs text-theme-text focus:outline-none focus:border-admin"
        ></textarea>
      </label>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" @click="$emit('close-refund')" class="px-4 py-2 rounded-xl border border-theme-border text-xs font-semibold text-theme-text-muted cursor-pointer">
          Cancel
        </button>
        <button type="submit" :disabled="submitting" class="px-4 py-2 rounded-xl bg-emerald-500 text-white text-xs font-bold cursor-pointer disabled:opacity-50">
          {{ submitting ? 'Processing...' : 'Start Refund Process' }}
        </button>
      </div>
    </form>
  </BaseModal>

  <!-- Review Appeal Modal -->
  <BaseModal :open="Boolean(appealRecord)" title="Review No-Show Appeal" @close="$emit('close-appeal')">
    <form v-if="appealRecord" @submit.prevent="handleAppealSubmit" class="space-y-4">
      <div class="p-3.5 rounded-xl bg-theme-surface border border-theme-border text-xs space-y-1.5">
        <p class="font-bold text-theme-text">
          {{ appealRecord.customer?.name }} · {{ appealRecord.appointment?.booking_reference }}
        </p>
        <p class="text-theme-text-muted italic">"{{ appealRecord.appeal_reason }}"</p>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <button
          type="button"
          @click="appealOutcome = 'approved'"
          class="py-2.5 rounded-xl text-xs font-bold border transition-all cursor-pointer"
          :class="appealOutcome === 'approved' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-400' : 'bg-theme-surface border-theme-border text-theme-text-muted'"
        >
          Approve Appeal
        </button>
        <button
          type="button"
          @click="appealOutcome = 'rejected'"
          class="py-2.5 rounded-xl text-xs font-bold border transition-all cursor-pointer"
          :class="appealOutcome === 'rejected' ? 'bg-red-500/20 border-red-500 text-red-400' : 'bg-theme-surface border-theme-border text-theme-text-muted'"
        >
          Decline Appeal
        </button>
      </div>

      <label
        v-if="appealOutcome === 'approved' && appealRecord.can_initiate_refund"
        class="flex items-center gap-2.5 p-3 rounded-xl bg-theme-surface border border-theme-border cursor-pointer text-xs text-theme-text"
      >
        <input v-model="initiateRefundOnApprove" type="checkbox" class="h-4 w-4 accent-emerald-500" />
        <span>Also start refund process (₦{{ Number(appealRecord.paid_amount_naira || 0).toLocaleString() }})</span>
      </label>

      <label class="block space-y-1.5">
        <span class="text-xs font-semibold text-theme-text">Decision Note</span>
        <textarea
          v-model="resolutionNote"
          rows="3"
          placeholder="Optional note sent to the customer..."
          class="w-full p-3 rounded-xl bg-theme-surface border border-theme-border text-xs text-theme-text focus:outline-none focus:border-admin"
        ></textarea>
      </label>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" @click="$emit('close-appeal')" class="px-4 py-2 rounded-xl border border-theme-border text-xs font-semibold text-theme-text-muted cursor-pointer">
          Cancel
        </button>
        <button type="submit" :disabled="submitting" class="px-4 py-2 rounded-xl bg-admin text-obsidian text-xs font-bold cursor-pointer disabled:opacity-50">
          {{ submitting ? 'Submitting...' : 'Confirm Decision' }}
        </button>
      </div>
    </form>
  </BaseModal>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import BaseModal from '@/core/components/BaseModal.vue';

const props = defineProps({
  refundRecord: { type: Object, default: null },
  appealRecord: { type: Object, default: null },
  submitting: { type: Boolean, default: false },
});

const emit = defineEmits(['close-refund', 'close-appeal', 'submit-refund', 'submit-appeal']);

const refundNaira = ref(0);
const refundReason = ref('');
const appealOutcome = ref('approved');
const resolutionNote = ref('');
const initiateRefundOnApprove = ref(true);

watch(
  () => props.refundRecord,
  (rec) => {
    if (rec) {
      refundNaira.value = Number(rec.paid_amount_naira || 0);
      refundReason.value = rec.appeal_reason ? `Approved: ${rec.appeal_reason}` : 'Admin initiated refund';
    }
  },
  { immediate: true }
);

watch(
  () => props.appealRecord,
  (rec) => {
    if (rec) {
      appealOutcome.value = 'approved';
      resolutionNote.value = '';
      initiateRefundOnApprove.value = Boolean(rec.can_initiate_refund);
    }
  },
  { immediate: true }
);

function handleRefundSubmit() {
  if (!props.refundRecord) return;
  emit('submit-refund', props.refundRecord.id, {
    amount_minor: Math.round(Number(refundNaira.value) * 100),
    reason: refundReason.value,
  });
}

function handleAppealSubmit() {
  if (!props.appealRecord) return;
  emit('submit-appeal', props.appealRecord.id, {
    outcome: appealOutcome.value,
    resolution_note: resolutionNote.value || null,
    initiate_refund: appealOutcome.value === 'approved' && initiateRefundOnApprove.value,
  });
}
</script>
