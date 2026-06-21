import client from '../../../core/api/axios';

export const barberApi = {
  dashboard: () => client.get('/barber/dashboard'),
  schedule: () => client.get('/barber/schedule'),
  appointments: () => client.get('/barber/appointments'),
  complete: (id) => client.patch(`/barber/appointments/${id}/complete`),
  noShow: (id) => client.patch(`/barber/appointments/${id}/no-show`),
  profile: () => client.get('/barber/profile'),
  updateProfile: (data) => client.post('/barber/profile', data),
  
  // Services
  getServices: () => client.get('/barber/services'),
  createService: (data) => client.post('/barber/services', data),
  updateService: (id, data) => client.put(`/barber/services/${id}`, data),
  deleteService: (id) => client.delete(`/barber/services/${id}`),
  
  // Gallery
  getGallery: () => client.get('/barber/gallery'),
  addGalleryImage: (data) => client.post('/barber/gallery', data),
  deleteGalleryImage: (id) => client.delete(`/barber/gallery/${id}`),
  
  // Blog
  getBlogPosts: () => client.get('/barber/blog'),
  createBlogPost: (data) => client.post('/barber/blog', data),
  updateBlogPost: (id, data) => client.put(`/barber/blog/${id}`, data),
  deleteBlogPost: (id) => client.delete(`/barber/blog/${id}`),
};