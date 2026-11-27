<template>
  <form class="space-y-4 rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900" @submit.prevent="handleSubmit">
    <div class="flex flex-wrap items-center justify-between gap-2">
      <div>
        <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Refund Policy Settings</h2>
        <p class="text-xs text-zinc-500 dark:text-zinc-400">Configure automatic cancellation tiers and compassionate review rules.</p>
      </div>
      <button
        type="submit"
        :disabled="saving"
        class="rounded-lg bg-amber-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-amber-700 disabled:opacity-60"
      >
        {{ saving ? 'Saving...' : 'Save Policy' }}
      </button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div>
        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Full Refund Cutoff (Hours)</label>
        <input
          v-model.number="form.refund_full_hours"
          type="number"
          min="1"
          max="336"
          class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
        />
        <p class="mt-1 text-[11px] text-zinc-500">100% refund at or above this notice.</p>
      </div>

      <div>
        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Partial Refund Cutoff (Hours)</label>
        <input
          v-model.number="form.refund_partial_hours"
          type="number"
          min="0"
          max="168"
          class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
        />
        <p class="mt-1 text-[11px] text-zinc-500">Must be ≤ full cutoff.</p>
      </div>

      <div>
        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Partial Refund Rate (%)</label>
        <input
          v-model.number="form.refund_partial_percent"
          type="number"
          min="0"
          max="100"
          class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
        />
        <p class="mt-1 text-[11px] text-zinc-500">Applied between partial and full cutoff.</p>
      </div>

      <div>
        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Review SLA (Hours)</label>
        <input
          v-model.number="form.refund_sla_hours"
          type="number"
          min="1"
          max="720"
          class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
        />
        <p class="mt-1 text-[11px] text-zinc-500">Target processing turnaround.</p>
      </div>
    </div>

    <div class="flex flex-wrap gap-6 pt-1">
      <label class="inline-flex cursor-pointer items-center gap-2 text-xs text-zinc-700 dark:text-zinc-300">
        <input v-model="form.refund_compassionate_enabled" type="checkbox" class="rounded border-zinc-300 text-amber-600 focus:ring-amber-500" />
        Allow compassionate refunds (illness / emergency)
      </label>
      <label class="inline-flex cursor-pointer items-center gap-2 text-xs text-zinc-700 dark:text-zinc-300">
        <input v-model="form.refund_require_evidence_for_compassionate" type="checkbox" class="rounded border-zinc-300 text-amber-600 focus:ring-amber-500" />
        Require uploaded evidence for compassionate claims
      </label>
    </div>
  </form>
</template>

<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  policy: { type: Object, required: true },
  saving: { type: Boolean, default: false },
})

const emit = defineEmits(['save'])

const form = reactive({
  refund_full_hours: 24,
  refund_partial_hours: 6,
  refund_partial_percent: 50,
  refund_sla_hours: 48,
  refund_compassionate_enabled: true,
  refund_require_evidence_for_compassionate: false,
})

watch(
  () => props.policy,
  (val) => {
    if (!val) return
    form.refund_full_hours = Number(val.refund_full_hours ?? 24)
    form.refund_partial_hours = Number(val.refund_partial_hours ?? 6)
    form.refund_partial_percent = Number(val.refund_partial_percent ?? 50)
    form.refund_sla_hours = Number(val.refund_sla_hours ?? 48)
    form.refund_compassionate_enabled = Boolean(val.refund_compassionate_enabled ?? true)
    form.refund_require_evidence_for_compassionate = Boolean(val.refund_require_evidence_for_compassionate ?? false)
  },
  { immediate: true, deep: true }
)

function handleSubmit() {
  emit('save', { ...form })
}
</script>
