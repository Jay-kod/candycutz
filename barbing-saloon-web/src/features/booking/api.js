import { api } from '@/shared/api/client';

export const bookingApi = {
  getAppointments: (params) => api.get('/v1/appointments', { params }),
  getAppointment: (id) => api.get(`/v1/appointments/${id}`),
  createAppointment: (data) => api.post('/v1/appointments', data),
  createWalkIn: (data) => api.post('/v1/appointments/walk-in', data),
  cancelAppointment: (id) => api.post(`/v1/appointments/${id}/cancel`),
  updateStatus: (id, data) => api.patch(`/v1/appointments/${id}/status`, data),
  getAvailability: (params) => api.get('/v1/availability', { params }),
};
