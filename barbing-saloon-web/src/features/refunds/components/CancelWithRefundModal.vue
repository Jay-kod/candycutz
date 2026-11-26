<template>
  <div v-if="booking" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
    <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900">
      <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Cancel Appointment &amp; Refund Preview</h3>
      <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
        {{ booking.booking_reference || `#${booking.id}` }} • {{ booking.service?.name || 'Service' }} on {{ booking.appointment_date }} at {{ booking.appointment_time }}
      </p>

      <div class="mt-4 rounded-xl border p-3.5 text-xs" :class="eligibilityBannerClass">
        <div v-if="loadingPreview" class="text-zinc-500">Checking refund eligibility...</div>
        <div v-else-if="eligibility" class="space-y-1">
          <p class="font-semibold">
            <span v-if="!eligibility.is_paid">No Paid Deposit Attached</span>
            <span v-else-if="eligibility.eligible">
              Eligible Refund: ₦{{ Number(eligibility.refund_amount || 0).toLocaleString() }} ({{ eligibility.percentage }}%)
            </span>
            <span v-else>No Automatic Refund Eligible (0%)</span>
          </p>
          <p class="opacity-90">{{ eligibility.reason }}</p>
          <p v-if="eligibility.requires_approval" class="text-[11px] font-medium">
            Compassionate requests are reviewed by our salon team before payout.
          </p>
        </div>
      </div>

      <form class="mt-4 space-y-4" @submit.prevent="submitCancel">
        <div>
          <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Cancellation Reason</label>
          <select
            v-model="reasonCategory"
            class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
          >
            <option value="changed_mind">Schedule Change / Personal Plan</option>
            <option value="illness">Medical / Unwell (Compassionate Review)</option>
            <option value="emergency">Family / Urgent Emergency (Compassionate Review)</option>
            <option value="other">Other Reason</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Details (Optional)</label>
          <textarea
            v-model="reasonText"
            rows="3"
            placeholder="Tell us briefly why you need to cancel..."
            class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
          />
        </div>

        <div v-if="isCompassionate">
          <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Supporting Evidence (Optional JPG, PNG, PDF up to 5MB)</label>
          <input
            type="file"
            accept=".jpg,.jpeg,.png,.pdf,.webp"
            class="mt-1 block w-full text-xs text-zinc-600 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-amber-700 hover:file:bg-amber-100 dark:text-zinc-400"
            @change="onFileChange"
          />
          <p v-if="fileError" class="mt-1 text-xs font-medium text-rose-600">{{ fileError }}</p>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button
            type="button"
            class="rounded-lg border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"
            @click="$emit('close')"
          >
            Keep Booking
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-60"
          >
            {{ submitting ? 'Cancelling...' : 'Confirm Cancellation' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { refundApi } from '../api/refundApi'

const props = defineProps({
  booking: { type: Object, default: null },
  submitting: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'confirm'])

const reasonCategory = ref('changed_mind')
const reasonText = ref('')
const evidenceFile = ref(null)
const fileError = ref('')
const eligibility = ref(null)
const loadingPreview = ref(false)
let previewSeq = 0

const isCompassionate = computed(() => ['illness', 'emergency'].includes(reasonCategory.value))

const eligibilityBannerClass = computed(() => {
  if (!eligibility.value || !eligibility.value.is_paid) {
    return 'border-zinc-200 bg-zinc-50 text-zinc-700 dark:border-zinc-800 dark:bg-zinc-800/50 dark:text-zinc-300'
  }
  if (eligibility.value.eligible) {
    return eligibility.value.requires_approval
      ? 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-800/60 dark:bg-amber-950/30 dark:text-amber-200'
      : 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800/60 dark:bg-emerald-950/30 dark:text-emerald-200'
  }
  return 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-800/60 dark:bg-rose-950/30 dark:text-rose-200'
})

async function loadEligibility() {
  if (!props.booking?.id) return
  const seq = ++previewSeq
  loadingPreview.value = true
  try {
    const res = await refundApi.getRefundEligibility(props.booking.id, {
      reason_category: reasonCategory.value,
      compassionate: isCompassionate.value ? 1 : 0,
    })
    if (seq === previewSeq) {
      eligibility.value = res
    }
  } catch {
    if (seq === previewSeq) {
      eligibility.value = null
    }
  } finally {
    if (seq === previewSeq) {
      loadingPreview.value = false
    }
  }
}

watch(
  () => props.booking?.id,
  (id) => {
    if (!id) return
    reasonCategory.value = 'changed_mind'
    reasonText.value = ''
    evidenceFile.value = null
    fileError.value = ''
    loadEligibility()
  },
  { immediate: true }
)

watch(reasonCategory, () => {
  if (!isCompassionate.value) {
    evidenceFile.value = null
    fileError.value = ''
  }
  loadEligibility()
})

function onFileChange(event) {
  fileError.value = ''
  const selected = event.target?.files?.[0] ?? null
  if (selected && selected.size > 5 * 1024 * 1024) {
    fileError.value = 'Selected file exceeds the 5MB limit.'
    evidenceFile.value = null
    return
  }
  evidenceFile.value = selected
}

function submitCancel() {
  if (!props.booking || fileError.value) return
  const payload = new FormData()
  payload.append('reason_category', reasonCategory.value)
  if (reasonText.value.trim()) {
    payload.append('reason', reasonText.value.trim())
  }
  if (isCompassionate.value) {
    payload.append('request_compassionate', '1')
  }
  if (evidenceFile.value) {
    payload.append('evidence', evidenceFile.value)
  }
  emit('confirm', { appointmentId: props.booking.id, payload })
}
</script>
