import api from '@/shared/api/client'

const unwrap = (response) => response?.data?.data ?? response?.data

export const refundApi = {
  // Admin endpoints
  async listAdminRefunds(params = {}) {
    const res = await api.get('/admin/refunds', { params })
    return unwrap(res)
  },

  async getAdminPolicy() {
    const res = await api.get('/admin/refunds/policy')
    return unwrap(res)
  },

  async updateAdminPolicy(payload) {
    const res = await api.post('/admin/refunds/policy', payload)
    return unwrap(res)
  },

  async decideRefund(id, payload) {
    const res = await api.post(`/admin/refunds/${id}/decide`, payload)
    return unwrap(res)
  },

  async completeManualRefund(id, payload = {}) {
    const res = await api.post(`/admin/refunds/${id}/complete-manual`, payload)
    return unwrap(res)
  },

  // Customer endpoints
  async getRefundEligibility(appointmentId, params = {}) {
    const res = await api.get(`/appointments/${appointmentId}/refund-eligibility`, { params })
    return unwrap(res)
  },

  async listMyRefunds(params = {}) {
    const res = await api.get('/customer/refunds', { params })
    return unwrap(res)
  },

  async submitRefundRequest(formData) {
    const isMultipart = typeof FormData !== 'undefined' && formData instanceof FormData
    const res = await api.post(
      '/refunds',
      formData,
      isMultipart ? { headers: { 'Content-Type': 'multipart/form-data' } } : undefined
    )
    return unwrap(res)
  },

  async cancelAppointmentWithRefund(appointmentId, payload) {
    const isMultipart = typeof FormData !== 'undefined' && payload instanceof FormData
    const res = await api.patch(
      `/appointments/${appointmentId}/cancel`,
      payload,
      isMultipart ? { headers: { 'Content-Type': 'multipart/form-data' } } : undefined
    )
    return unwrap(res)
  },

  async fetchEvidenceBlob(id) {
    const res = await api.get(`/refunds/${id}/evidence`, { responseType: 'blob' })
    return res.data
  },
}
