import { describe, expect, it, vi, beforeEach } from 'vitest'

const { mockClient } = vi.hoisted(() => ({
  mockClient: {
    get: vi.fn(),
    post: vi.fn(),
    patch: vi.fn(),
  },
}))

vi.mock('@/shared/api/client', () => ({
  default: mockClient,
}))

import { refundApi } from '../api/refundApi'

describe('refundApi', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('calls admin and customer refund endpoints with expected paths and payloads', async () => {
    await refundApi.listAdminRefunds({ status: 'pending' })
    expect(mockClient.get).toHaveBeenCalledWith('/admin/refunds', { params: { status: 'pending' } })

    await refundApi.getAdminPolicy()
    expect(mockClient.get).toHaveBeenCalledWith('/admin/refunds/policy')

    await refundApi.updateAdminPolicy({ refund_full_hours: 48, refund_partial_hours: 12, refund_partial_percent: 50, refund_compassionate_enabled: true, refund_sla_hours: 24 })
    expect(mockClient.post).toHaveBeenCalledWith('/admin/refunds/policy', expect.objectContaining({ refund_full_hours: 48 }))

    await refundApi.decideRefund(11, { decision: 'approve', note: 'Approved compassionate claim' })
    expect(mockClient.post).toHaveBeenCalledWith('/admin/refunds/11/decide', {
      decision: 'approve',
      note: 'Approved compassionate claim',
    })

    await refundApi.completeManualRefund(12, { transfer_reference: 'NIP-123' })
    expect(mockClient.post).toHaveBeenCalledWith('/admin/refunds/12/complete-manual', {
      transfer_reference: 'NIP-123',
    })

    await refundApi.getRefundEligibility(77, { reason_category: 'illness', compassionate: 1 })
    expect(mockClient.get).toHaveBeenCalledWith('/appointments/77/refund-eligibility', {
      params: { reason_category: 'illness', compassionate: 1 },
    })

    await refundApi.listMyRefunds()
    expect(mockClient.get).toHaveBeenCalledWith('/customer/refunds', { params: {} })

    await refundApi.cancelAppointmentWithRefund(77, { reason_category: 'changed_mind' })
    expect(mockClient.patch).toHaveBeenCalledWith('/appointments/77/cancel', { reason_category: 'changed_mind' }, undefined)
  })
})
