import client from '@/shared/api/client';

export const noShowApi = {
  // Admin endpoints
  getDashboard: (params = {}) => client.get('/admin/no-shows', { params }),
  getPolicy: () => client.get('/admin/no-shows/policy'),
  updatePolicy: (data) => client.post('/admin/no-shows/policy', data),
  forfeitRecordDeposit: (recordId, reason = '') =>
    client.post(`/admin/no-shows/${recordId}/forfeit-deposit`, { reason }),
  resolveAppeal: (recordId, payload) =>
    client.patch(`/admin/no-shows/${recordId}/appeal`, payload),
  initiateRefund: (recordId, payload = {}) =>
    client.post(`/admin/no-shows/${recordId}/initiate-refund`, payload),
  resetCustomerFlag: (customerId) =>
    client.post(`/admin/customers/${customerId}/reset-no-show-flag`),

  // Barber & Admin appointment actions
  markAppointmentNoShow: (appointmentId, payload = {}) =>
    client.patch(`/appointments/${appointmentId}/no-show`, payload),
  forfeitAppointmentDeposit: (appointmentId, reason = '') =>
    client.post(`/appointments/${appointmentId}/forfeit-deposit`, { reason }),

  // Customer actions
  extendGrace: (appointmentId, reason) =>
    client.post(`/appointments/${appointmentId}/grace-extension`, { reason }),
  appealNoShow: (recordId, appealReason) =>
    client.post(`/no-shows/${recordId}/appeal`, { appeal_reason: appealReason }),
};
