<template>
  <component :is="layoutComponent">
    <div class="space-y-6 p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Refunds &amp; Cancellation Policy</h1>
          <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Manage refund tiers, compassionate review requests, and manual payouts.</p>
        </div>
        <button
          type="button"
          class="rounded-lg border border-zinc-300 px-3.5 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800"
          @click="loadAll"
        >
          Refresh
        </button>
      </div>

      <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <p class="text-[11px] font-medium uppercase text-zinc-500">Pending Review</p>
          <p class="mt-1 text-2xl font-bold text-amber-600">{{ stats.pending }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <p class="text-[11px] font-medium uppercase text-zinc-500">Manual Queue</p>
          <p class="mt-1 text-2xl font-bold text-sky-600">{{ stats.processing }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <p class="text-[11px] font-medium uppercase text-zinc-500">Completed</p>
          <p class="mt-1 text-2xl font-bold text-emerald-600">{{ stats.completed }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <p class="text-[11px] font-medium uppercase text-zinc-500">Declined</p>
          <p class="mt-1 text-2xl font-bold text-rose-600">{{ stats.rejected }}</p>
        </div>
        <div class="col-span-2 rounded-xl border border-zinc-200 bg-white p-4 lg:col-span-1 dark:border-zinc-800 dark:bg-zinc-900">
          <p class="text-[11px] font-medium uppercase text-zinc-500">Total Refunded</p>
          <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">₦{{ Math.round((stats.total_refunded_minor || 0) / 100).toLocaleString() }}</p>
        </div>
      </div>

      <RefundPolicyForm :policy="policy" :saving="savingPolicy" @save="savePolicy" />

      <RefundQueueTable
        :refunds="refunds"
        :loading="loading"
        :acting-id="actingId"
        :status-filter="statusFilter"
        :type-filter="typeFilter"
        @update:status-filter="onStatusChange"
        @update:type-filter="onTypeChange"
        @review="openModal($event, 'review')"
        @complete-manual="openModal($event, 'manual')"
        @view-evidence="viewEvidence"
      />

      <RefundDecisionModal
        :refund="modalRefund"
        :mode="modalMode"
        :submitting="actingId !== null"
        @close="modalRefund = null"
        @submit-decision="handleDecision"
        @submit-manual="handleManualComplete"
      />
    </div>
  </component>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue'
import SuperAdminLayout from '@/portals/superadmin/layouts/SuperAdminLayout.vue'
import { refundApi } from '../api/refundApi'
import { useAdminRefunds } from '../composables/useAdminRefunds'
import RefundPolicyForm from '../components/RefundPolicyForm.vue'
import RefundQueueTable from '../components/RefundQueueTable.vue'
import RefundDecisionModal from '../components/RefundDecisionModal.vue'
import { useToast } from '@/core/composables/useToast'

const route = useRoute()
const toast = useToast()
const layoutComponent = computed(() => (route.path.startsWith('/superadmin') ? SuperAdminLayout : AdminLayout))

const {
  refunds,
  stats,
  policy,
  loading,
  savingPolicy,
  actingId,
  statusFilter,
  typeFilter,
  fetchRefunds,
  loadAll,
  savePolicy,
  decideRefund,
  completeManual,
} = useAdminRefunds()

const modalRefund = ref(null)
const modalMode = ref('review')

onMounted(loadAll)

function onStatusChange(value) {
  statusFilter.value = value
  fetchRefunds(1)
}

function onTypeChange(value) {
  typeFilter.value = value
  fetchRefunds(1)
}

function openModal(item, mode) {
  modalRefund.value = item
  modalMode.value = mode
}

async function handleDecision(payload) {
  if (!modalRefund.value) return
  const ok = await decideRefund(modalRefund.value.id, payload)
  if (ok) modalRefund.value = null
}

async function handleManualComplete(payload) {
  if (!modalRefund.value) return
  const ok = await completeManual(modalRefund.value.id, payload)
  if (ok) modalRefund.value = null
}

async function viewEvidence(item) {
  try {
    const blob = await refundApi.fetchEvidenceBlob(item.id)
    const url = URL.createObjectURL(blob)
    window.open(url, '_blank', 'noopener,noreferrer')
  } catch (err) {
    toast.error(err?.response?.data?.message || 'Unable to load supporting evidence.')
  }
}
</script>
