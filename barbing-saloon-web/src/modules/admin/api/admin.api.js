import client from '../../../core/api/axios';

export const adminApi = {
  dashboard: () => client.get('/admin/dashboard'),
  appointments: () => client.get('/admin/appointments'),
  approveAppointment: (id) => client.patch(`/admin/appointments/${id}/approve`),
  cancelAppointment: (id) => client.patch(`/admin/appointments/${id}/cancel`),
  services: () => client.get('/admin/services'),
  serviceCategories: () => client.get('/admin/service-categories'),
  gallery: () => client.get('/admin/gallery'),
  testimonials: () => client.get('/admin/testimonials'),
  approveTestimonial: (id) => client.patch(`/admin/testimonials/${id}/approve`),
  featureTestimonial: (id) => client.patch(`/admin/testimonials/${id}/feature`),
  deleteTestimonial: (id) => client.delete(`/admin/testimonials/${id}`),
  blogPosts: () => client.get('/admin/blog'),
  workingHours: () => client.get('/admin/working-hours'),
  reports: () => client.get('/admin/reports'),
  settings: () => client.get('/admin/settings'),
  updateSettings: (data) => client.post('/admin/settings', data, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
  barbers: () => client.get('/admin/barbers'),
  createBarber: (data) => client.post('/admin/barbers', data),
  updateBarber: (id, data) => client.put(`/admin/barbers/${id}`, data),
  updateBarberStatus: (id, status) => client.patch(`/admin/barbers/${id}/status`, { status }),
  deleteBarber: (id) => client.delete(`/admin/barbers/${id}`),
};