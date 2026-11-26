<template>
  <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
      <div>
        <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Refund Requests Queue</h2>
        <p class="text-xs text-zinc-500 dark:text-zinc-400">Review compassionate claims, monitor automatic reversals, and complete manual bank transfers.</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <select
          :value="statusFilter"
          class="rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs text-zinc-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
          @change="$emit('update:statusFilter', $event.target.value)"
        >
          <option value="all">All Statuses</option>
          <option value="pending">Pending Review</option>
          <option value="processing">Awaiting Manual Transfer</option>
          <option value="completed">Completed</option>
          <option value="rejected">Rejected</option>
          <option value="failed">Failed</option>
        </select>
        <select
          :value="typeFilter"
          class="rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs text-zinc-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
          @change="$emit('update:typeFilter', $event.target.value)"
        >
          <option value="all">All Types</option>
          <option value="standard">Time-Based Standard</option>
          <option value="compassionate">Compassionate (Illness / Emergency)</option>
          <option value="no_show_dispute">No-Show Appeal Refund</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="p-8 text-center text-sm text-zinc-500">Loading refund requests...</div>
    <div v-else-if="!refunds.length" class="p-8 text-center text-sm text-zinc-500">No refund requests match the selected filters.</div>

    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-zinc-200 text-left text-xs dark:divide-zinc-800">
        <thead class="bg-zinc-50 text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:bg-zinc-950 dark:text-zinc-400">
          <tr>
            <th class="px-4 py-3">Booking</th>
            <th class="px-4 py-3">Customer</th>
            <th class="px-4 py-3">Type &amp; Reason</th>
            <th class="px-4 py-3">Refund Amount</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
          <tr v-for="item in refunds" :key="item.id" class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40">
            <td class="px-4 py-3">
              <p class="font-semibold text-zinc-900 dark:text-white">{{ item.appointment?.booking_reference || `#${item.appointment_id}` }}</p>
              <p class="text-[11px] text-zinc-500">{{ item.appointment?.service_name || 'Service' }}</p>
            </td>
            <td class="px-4 py-3">
              <p class="font-medium text-zinc-900 dark:text-white">{{ item.customer?.name || 'Client' }}</p>
              <p class="text-[11px] text-zinc-500">{{ item.customer?.email || '' }}</p>
            </td>
            <td class="px-4 py-3">
              <span class="inline-block rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                {{ item.type }} • {{ item.reason_category }}
              </span>
              <p v-if="item.reason_text" class="mt-1 max-w-xs truncate text-[11px] text-zinc-600 dark:text-zinc-400" :title="item.reason_text">
                {{ item.reason_text }}
              </p>
              <button
                v-if="item.has_evidence"
                type="button"
                class="mt-1 text-[11px] font-medium text-amber-600 underline hover:text-amber-700"
                @click="$emit('view-evidence', item)"
              >
                View supporting evidence
              </button>
            </td>
            <td class="px-4 py-3">
              <p class="font-semibold text-zinc-900 dark:text-white">₦{{ Number(item.refund_amount || 0).toLocaleString() }} ({{ item.refund_percentage }}%)</p>
              <p class="text-[11px] text-zinc-500">Paid: ₦{{ Number(item.original_amount || 0).toLocaleString() }}</p>
              <p class="text-[10px] uppercase text-zinc-400">{{ item.payment?.provider || 'manual_transfer' }}</p>
            </td>
            <td class="px-4 py-3">
              <span :class="badgeClass(item.status)" class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold capitalize">
                {{ item.status }}
              </span>
              <p v-if="item.gateway_refund_ref" class="mt-1 font-mono text-[10px] text-zinc-400">{{ item.gateway_refund_ref }}</p>
            </td>
            <td class="px-4 py-3 text-right">
              <div class="inline-flex flex-wrap justify-end gap-1.5">
                <button
                  v-if="item.can_decide"
                  type="button"
                  :disabled="actingId === item.id"
                  class="rounded-md bg-amber-600 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-amber-700 disabled:opacity-50"
                  @click="$emit('review', item)"
                >
                  Review
                </button>
                <button
                  v-if="item.can_complete_manual"
                  type="button"
                  :disabled="actingId === item.id"
                  class="rounded-md bg-emerald-600 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                  @click="$emit('complete-manual', item)"
                >
                  Mark Transferred
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
defineProps({
  refunds: { type: Array, required: true },
  loading: { type: Boolean, default: false },
  actingId: { type: Number, default: null },
  statusFilter: { type: String, default: 'all' },
  typeFilter: { type: String, default: 'all' },
})

defineEmits(['update:statusFilter', 'update:typeFilter', 'review', 'complete-manual', 'view-evidence'])

function badgeClass(status) {
  switch (status) {
    case 'completed':
      return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300'
    case 'processing':
      return 'bg-sky-100 text-sky-800 dark:bg-sky-950/50 dark:text-sky-300'
    case 'pending':
      return 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300'
    case 'rejected':
    case 'failed':
      return 'bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300'
    default:
      return 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300'
  }
}
</script>
