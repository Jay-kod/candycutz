import client from '../../../core/api/axios';

export const publicApi = {
  settings: () => client.get('/public/settings?v=2'),
  services: () => client.get('/public/services?v=2'),
  service: (slug) => client.get(`/public/services/${slug}`),
  serviceCategories: () => client.get('/public/service-categories'),
  barbers: () => client.get('/public/barbers'),
  barber: (id) => client.get(`/public/barbers/${id}`),
  gallery: (params = {}) => client.get('/public/gallery', { params }),
  testimonials: (params = {}) => client.get('/public/testimonials', { params }),
  blog: () => client.get('/public/blog'),
  blogPost: (slug) => client.get(`/public/blog/${slug}`),
  workingHours: () => client.get('/public/working-hours'),
  availableSlots: (params) => client.get('/public/available-slots', { params }),
  contact: (data) => client.post('/public/contact', data),
};