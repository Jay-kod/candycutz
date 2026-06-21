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
  updateTestimonial: (id, data) => client.put(`/admin/testimonials/${id}`, data),
  deleteTestimonial: (id) => client.delete(`/admin/testimonials/${id}`),
  blogPosts: () => client.get('/admin/blog'),
  workingHours: () => client.get('/admin/working-hours'),
  reports: () => client.get('/admin/reports'),
  settings: () => client.get('/admin/settings'),
  updateSettings: (data) => client.post('/admin/settings', data, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
};