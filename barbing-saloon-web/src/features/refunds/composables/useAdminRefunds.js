import { ref } from 'vue'
import { refundApi } from '../api/refundApi'
import { useToast } from '@/core/composables/useToast'

export function useAdminRefunds() {
  const toast = useToast()

  const refunds = ref([])
  const stats = ref({
    pending: 0,
    processing: 0,
    completed: 0,
    rejected: 0,
    total_refunded_minor: 0,
  })
  const meta = ref({ current_page: 1, last_page: 1, total: 0 })
  const policy = ref({
    refund_full_hours: 24,
    refund_partial_hours: 6,
    refund_partial_percent: 50,
    refund_compassionate_enabled: true,
    refund_sla_hours: 48,
    refund_require_evidence_for_compassionate: false,
  })

  const loading = ref(false)
  const savingPolicy = ref(false)
  const actingId = ref(null)
  const statusFilter = ref('all')
  const typeFilter = ref('all')

  async function fetchRefunds(page = 1) {
    loading.value = true
    try {
      const data = await refundApi.listAdminRefunds({
        page,
        status: statusFilter.value,
        type: typeFilter.value,
      })
      refunds.value = data?.items ?? []
      meta.value = data?.meta ?? { current_page: 1, last_page: 1, total: 0 }
      stats.value = data?.stats ?? stats.value
    } catch (err) {
      toast.error(err?.response?.data?.message || 'Failed to load refund queue')
    } finally {
      loading.value = false
    }
  }

  async function fetchPolicy() {
    try {
      const data = await refundApi.getAdminPolicy()
      if (data) {
        policy.value = {
          refund_full_hours: Number(data.refund_full_hours ?? 24),
          refund_partial_hours: Number(data.refund_partial_hours ?? 6),
          refund_partial_percent: Number(data.refund_partial_percent ?? 50),
          refund_compassionate_enabled: Boolean(data.refund_compassionate_enabled ?? true),
          refund_sla_hours: Number(data.refund_sla_hours ?? 48),
          refund_require_evidence_for_compassionate: Boolean(data.refund_require_evidence_for_compassionate ?? false),
        }
      }
    } catch (err) {
      toast.error(err?.response?.data?.message || 'Failed to load refund policy')
    }
  }

  async function loadAll() {
    await Promise.all([fetchRefunds(1), fetchPolicy()])
  }

  async function savePolicy(formValues) {
    savingPolicy.value = true
    try {
      const updated = await refundApi.updateAdminPolicy(formValues)
      if (updated) {
        policy.value = updated
      }
      toast.success('Refund policy settings updated.')
    } catch (err) {
      toast.error(err?.response?.data?.message || 'Could not save refund policy.')
    } finally {
      savingPolicy.value = false
    }
  }

  async function decideRefund(id, payload) {
    actingId.value = id
    try {
      await refundApi.decideRefund(id, payload)
      toast.success(payload.decision === 'approve' ? 'Refund approved and queued/processed.' : 'Refund request declined.')
      await fetchRefunds(meta.value.current_page)
      return true
    } catch (err) {
      toast.error(err?.response?.data?.message || 'Failed to update refund request.')
      return false
    } finally {
      actingId.value = null
    }
  }

  async function completeManual(id, payload) {
    actingId.value = id
    try {
      await refundApi.completeManualRefund(id, payload)
      toast.success('Manual refund transfer marked as completed.')
      await fetchRefunds(meta.value.current_page)
      return true
    } catch (err) {
      toast.error(err?.response?.data?.message || 'Failed to complete manual refund.')
      return false
    } finally {
      actingId.value = null
    }
  }

  return {
    refunds,
    stats,
    meta,
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
  }
}
