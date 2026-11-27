<template>
  <CustomerLayout>
    <div class="mx-auto max-w-5xl space-y-6 p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">My Refunds</h1>
          <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Track automatic cancellation refunds and compassionate review requests.</p>
        </div>
        <RouterLink
          to="/customer/dashboard/bookings"
          class="rounded-lg border border-zinc-300 px-3.5 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800"
        >
          Back to Bookings
        </RouterLink>
      </div>

      <div v-if="loading" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
        Loading your refunds...
      </div>

      <div v-else-if="!refunds.length" class="rounded-xl border border-zinc-200 bg-white p-8 text-center dark:border-zinc-800 dark:bg-zinc-900">
        <p class="text-sm font-semibold text-zinc-900 dark:text-white">No refund requests yet</p>
        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Refunds triggered when you cancel an eligible paid appointment or submit a compassionate claim will appear here.</p>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="item in refunds"
          :key="item.id"
          class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900"
        >
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <p class="text-sm font-bold text-zinc-900 dark:text-white">
                {{ item.appointment?.booking_reference || `#${item.appointment_id}` }} • {{ item.appointment?.service_name || 'Grooming Service' }}
              </p>
              <p class="mt-0.5 text-xs text-zinc-500">
                Type: <span class="font-medium capitalize">{{ item.type }}</span> • Reason: <span class="capitalize">{{ item.reason_category }}</span>
              </p>
            </div>
            <span :class="badgeClass(item.status)" class="rounded-full px-2.5 py-1 text-[11px] font-semibold capitalize">
              {{ item.status }}
            </span>
          </div>

          <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-zinc-100 pt-3 text-xs dark:border-zinc-800">
            <div>
              <span class="font-semibold text-zinc-900 dark:text-white">
                ₦{{ Number(item.refund_amount || 0).toLocaleString() }} ({{ item.refund_percentage }}%)
              </span>
              <span class="text-zinc-500"> of ₦{{ Number(item.original_amount || 0).toLocaleString() }}</span>
            </div>
            <div v-if="item.gateway_refund_ref" class="font-mono text-[11px] text-zinc-500">
              Ref: {{ item.gateway_refund_ref }}
            </div>
          </div>

          <p v-if="item.decision_note" class="mt-2 rounded-lg bg-zinc-50 p-2.5 text-xs text-zinc-600 dark:bg-zinc-800/50 dark:text-zinc-300">
            Note: {{ item.decision_note }}
          </p>
        </div>
      </div>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue'
import { refundApi } from '../api/refundApi'
import { useToast } from '@/core/composables/useToast'

const toast = useToast()
const refunds = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const data = await refundApi.listMyRefunds()
    refunds.value = data?.items ?? []
  } catch (err) {
    toast.error(err?.response?.data?.message || 'Unable to load refunds.')
  } finally {
    loading.value = false
  }
})

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
