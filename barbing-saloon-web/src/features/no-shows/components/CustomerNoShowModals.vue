<template>
  <div>
  <!-- Extend Grace Period Modal -->
  <BaseModal :open="Boolean(graceBooking)" title="Running Late? Extend Grace Period" @close="$emit('close-grace')">
    <form v-if="graceBooking" @submit.prevent="submitGrace" class="space-y-4">
      <div class="p-3.5 rounded-xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-xs space-y-1">
        <p class="font-bold text-[var(--color-text-primary)]">
          {{ graceBooking.service?.name || 'Appointment' }} · {{ graceBooking.booking_reference }}
        </p>
        <p class="text-[var(--color-text-secondary)]">
          You can extend your arrival window once with a valid reason so your booking is not marked as a no-show.
        </p>
      </div>

      <label class="block space-y-1.5">
        <span class="text-xs font-semibold text-[var(--color-text-primary)]">Valid Reason for Delay</span>
        <textarea
          v-model="graceReason"
          rows="3"
          required
          minlength="5"
          placeholder="e.g. Heavy traffic along Keffi-Abuja road, arriving in 10 minutes"
          class="w-full p-3 rounded-xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-xs text-[var(--color-text-primary)] focus:outline-none focus:border-[var(--color-accent)]"
        ></textarea>
      </label>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" @click="$emit('close-grace')" class="px-4 py-2 rounded-xl border border-[var(--color-border)] text-xs font-semibold text-[var(--color-text-secondary)] cursor-pointer">
          Cancel
        </button>
        <button type="submit" :disabled="submitting" class="px-4 py-2 rounded-xl bg-[var(--color-accent)] text-[var(--color-accent-foreground)] text-xs font-bold cursor-pointer disabled:opacity-50">
          {{ submitting ? 'Extending...' : 'Confirm Extension' }}
        </button>
      </div>
    </form>
  </BaseModal>

  <!-- Appeal No-Show Modal -->
  <BaseModal :open="Boolean(appealBooking)" title="Appeal No-Show Record" @close="$emit('close-appeal')">
    <form v-if="appealBooking" @submit.prevent="submitAppeal" class="space-y-4">
      <div class="p-3.5 rounded-xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-xs space-y-1">
        <p class="font-bold text-[var(--color-text-primary)]">
          Booking {{ appealBooking.booking_reference }}
        </p>
        <p class="text-[var(--color-text-secondary)]">
          If you were unwell or experienced an emergency, explain below for admin review. Approved appeals clear your no-show record and unlock refund review.
        </p>
      </div>

      <label class="block space-y-1.5">
        <span class="text-xs font-semibold text-[var(--color-text-primary)]">Explanation / Reason</span>
        <textarea
          v-model="appealReason"
          rows="3"
          required
          minlength="5"
          placeholder="Explain why you missed this appointment..."
          class="w-full p-3 rounded-xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-xs text-[var(--color-text-primary)] focus:outline-none focus:border-[var(--color-accent)]"
        ></textarea>
      </label>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" @click="$emit('close-appeal')" class="px-4 py-2 rounded-xl border border-[var(--color-border)] text-xs font-semibold text-[var(--color-text-secondary)] cursor-pointer">
          Cancel
        </button>
        <button type="submit" :disabled="submitting" class="px-4 py-2 rounded-xl bg-[var(--color-accent)] text-[var(--color-accent-foreground)] text-xs font-bold cursor-pointer disabled:opacity-50">
          {{ submitting ? 'Submitting...' : 'Submit Appeal' }}
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
  graceBooking: { type: Object, default: null },
  appealBooking: { type: Object, default: null },
  submitting: { type: Boolean, default: false },
});

const emit = defineEmits(['close-grace', 'close-appeal', 'submit-grace', 'submit-appeal']);

const graceReason = ref('');
const appealReason = ref('');

watch(() => props.graceBooking, (val) => {
  if (val) graceReason.value = '';
});

watch(() => props.appealBooking, (val) => {
  if (val) appealReason.value = '';
});

function submitGrace() {
  if (!props.graceBooking) return;
  emit('submit-grace', props.graceBooking.id, graceReason.value);
}

function submitAppeal() {
  if (!props.appealBooking?.no_show_record?.id) return;
  emit('submit-appeal', props.appealBooking.no_show_record.id, appealReason.value);
}
</script>
