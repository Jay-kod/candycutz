import { api } from '@/shared/api/client';

export const paymentApi = {
  getReceipt: (appointmentId) => api.get(`/v1/payments/appointments/${appointmentId}/receipt`),
  // other payment methods based on new backend
};
