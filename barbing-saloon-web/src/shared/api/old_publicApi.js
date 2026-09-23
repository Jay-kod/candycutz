import client from '@/shared/api/client';

export const publicApi = {
  settings: () => client.get('/settings?v=2'),
  services: () => client.get('/services?v=2'),
  service: (slug) => client.get(`/services/${slug}`),
  serviceCategories: () => client.get('/service-categories'),
  barbers: () => client.get('/barbers'),
  barber: (id) => client.get(`/barbers/${id}`),
  gallery: (params = {}) => client.get('/gallery', { params }),
  testimonials: (params = {}) => client.get('/testimonials', { params }),
  blog: () => client.get('/blog'),
  blogPost: (slug) => client.get(`/blog/${slug}`),
  workingHours: () => client.get('/working-hours'),
  availableSlots: (params) => client.get('/available-slots', { params }),
  contact: (data) => client.post('/contact', data),
};
