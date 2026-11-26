<template>
  <div v-if="booking" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
    <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900">
      <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Request Compassionate Refund</h3>
      <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
        For illness or emergencies affecting booking {{ booking.booking_reference || `#${booking.id}` }}.
      </p>

      <form class="mt-4 space-y-4" @submit.prevent="submitForm">
        <div>
          <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Circumstance</label>
          <select
            v-model="reasonCategory"
            class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
          >
            <option value="illness">Medical / Unwell</option>
            <option value="emergency">Urgent Emergency</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Explanation</label>
          <textarea
            v-model="reasonText"
            required
            rows="3"
            placeholder="Describe what happened so our team can review..."
            class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
          />
        </div>

        <div>
          <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Supporting Evidence (Optional JPG, PNG, PDF up to 5MB)</label>
          <input
            type="file"
            accept=".jpg,.jpeg,.png,.pdf,.webp"
            class="mt-1 block w-full text-xs text-zinc-600 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-amber-700 hover:file:bg-amber-100 dark:text-zinc-400"
            @change="evidenceFile = $event.target?.files?.[0] ?? null"
          />
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button
            type="button"
            class="rounded-lg border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"
            @click="$emit('close')"
          >
            Close
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="rounded-lg bg-amber-600 px-4 py-2 text-xs font-semibold text-white hover:bg-amber-700 disabled:opacity-60"
          >
            {{ submitting ? 'Submitting...' : 'Submit Refund Request' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  booking: { type: Object, default: null },
  submitting: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'submit'])

const reasonCategory = ref('illness')
const reasonText = ref('')
const evidenceFile = ref(null)

watch(
  () => props.booking?.id,
  () => {
    reasonCategory.value = 'illness'
    reasonText.value = ''
    evidenceFile.value = null
  }
)

function submitForm() {
  if (!props.booking) return
  const payload = new FormData()
  payload.append('appointment_id', String(props.booking.id))
  payload.append('reason_category', reasonCategory.value)
  payload.append('reason_text', reasonText.value.trim())
  payload.append('is_compassionate', '1')
  if (evidenceFile.value) {
    payload.append('evidence', evidenceFile.value)
  }
  emit('submit', payload)
}
</script>
