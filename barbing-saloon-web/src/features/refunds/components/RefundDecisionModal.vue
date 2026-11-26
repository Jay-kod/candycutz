<template>
  <div v-if="refund" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
    <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900">
      <h3 class="text-lg font-bold text-zinc-900 dark:text-white">
        {{ mode === 'manual' ? 'Complete Manual Bank Refund' : 'Review Refund Request' }}
      </h3>
      <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
        {{ refund.appointment?.booking_reference }} • {{ refund.customer?.name }} • Paid ₦{{ Number(refund.original_amount || 0).toLocaleString() }}
      </p>

      <div v-if="refund.reason_text" class="mt-4 rounded-lg bg-zinc-50 p-3 text-xs text-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-300">
        <span class="font-semibold uppercase text-zinc-500">{{ refund.reason_category }}:</span>
        {{ refund.reason_text }}
      </div>

      <form class="mt-4 space-y-4" @submit.prevent="handleSubmit">
        <div v-if="mode === 'review'">
          <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Decision</label>
          <div class="mt-1 grid grid-cols-2 gap-2">
            <button
              type="button"
              :class="decision === 'approve' ? 'border-emerald-600 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'border-zinc-300 dark:border-zinc-700'"
              class="rounded-lg border px-3 py-2 text-xs font-semibold"
              @click="decision = 'approve'"
            >
              Approve Refund
            </button>
            <button
              type="button"
              :class="decision === 'reject' ? 'border-rose-600 bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300' : 'border-zinc-300 dark:border-zinc-700'"
              class="rounded-lg border px-3 py-2 text-xs font-semibold"
              @click="decision = 'reject'"
            >
              Decline Request
            </button>
          </div>
        </div>

        <div v-if="mode === 'review' && decision === 'approve'">
          <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Refund Amount (₦)</label>
          <input
            v-model.number="amountNaira"
            type="number"
            min="1"
            :max="refund.original_amount"
            step="0.01"
            class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
          />
          <p class="mt-1 text-[11px] text-zinc-500">Default full/tier amount: ₦{{ Number(refund.refund_amount || 0).toLocaleString() }}</p>
        </div>

        <div v-if="mode === 'manual'">
          <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Bank Transfer Reference</label>
          <input
            v-model="transferReference"
            type="text"
            placeholder="e.g. NIP-20261126-9910"
            class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
          />
        </div>

        <div>
          <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Decision / Audit Note</label>
          <textarea
            v-model="note"
            rows="3"
            placeholder="Add reason or transfer confirmation note..."
            class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
          />
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button
            type="button"
            class="rounded-lg border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"
            @click="$emit('close')"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="rounded-lg bg-amber-600 px-4 py-2 text-xs font-semibold text-white hover:bg-amber-700 disabled:opacity-60"
          >
            {{ submitting ? 'Processing...' : mode === 'manual' ? 'Confirm Transfer Completed' : 'Submit Decision' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  refund: { type: Object, default: null },
  mode: { type: String, default: 'review' },
  submitting: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'submit-decision', 'submit-manual'])

const decision = ref('approve')
const amountNaira = ref(0)
const transferReference = ref('')
const note = ref('')

watch(
  () => props.refund,
  (val) => {
    if (!val) return
    decision.value = 'approve'
    amountNaira.value = Number(val.refund_amount ?? 0)
    transferReference.value = ''
    note.value = ''
  },
  { immediate: true }
)

function handleSubmit() {
  if (props.mode === 'manual') {
    emit('submit-manual', {
      transfer_reference: transferReference.value || undefined,
      note: note.value || undefined,
    })
    return
  }

  emit('submit-decision', {
    decision: decision.value,
    note: note.value || undefined,
    refund_amount_minor: decision.value === 'approve' ? Math.round(Number(amountNaira.value) * 100) : undefined,
  })
}
</script>
