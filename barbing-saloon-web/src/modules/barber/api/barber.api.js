import client from '../../../core/api/axios';

export const barberApi = {
  dashboard: () => client.get('/barber/dashboard'),
  schedule: () => client.get('/barber/schedule'),
  updateSchedule: (data) => client.put('/barber/schedule', data),
  appointments: () => client.get('/barber/appointments'),
  complete: (id, data) => client.patch(`/barber/appointments/${id}/complete`, data),
  noShow: (id) => client.patch(`/barber/appointments/${id}/no-show`),
  profile: () => client.get('/barber/profile'),
  updateProfile: (data) => client.post('/barber/profile', data),
  updateMyStatus: (data) => client.patch('/barber/my-status', data),
  getAccountDetails: () => client.get('/barber/account'),
  updateAccountDetails: (data) => client.patch('/barber/account', data),
  
  // Bookings / Payments
  getBookings: () => client.get('/barber/bookings'),
  verifyPayment: (id, action) => client.post(`/barber/bookings/${id}/verify-payment`, { action }),
  approveBooking: (id) => client.patch(`/barber/appointments/${id}/approve`),
  cancelBooking: (id) => client.patch(`/barber/appointments/${id}/cancel`),
  createWalkIn: (data) => client.post('/barber/appointments/walk-in', data),
  
  // Notifications
  getNotifications: () => client.get('/barber/notifications'),
  sendNotification: (data) => client.post('/barber/notifications', data),
  
  // Services
  getServices: () => client.get('/barber/services'),
  createService: (data) => client.post('/barber/services', data),
  updateService: (id, data) => client.put(`/barber/services/${id}`, data),
  deleteService: (id) => client.delete(`/barber/services/${id}`),
  
  // Gallery
  getGallery: () => client.get('/barber/gallery'),
  addGalleryImage: (data) => client.post('/barber/gallery', data),
  updateGalleryImage: (id, data) => client.post(`/barber/gallery/${id}`, data),
  deleteGalleryImage: (id) => client.delete(`/barber/gallery/${id}`),
  
  // Blog
  getBlogPosts: () => client.get('/barber/blog'),
  createBlogPost: (data) => client.post('/barber/blog', data),
  updateBlogPost: (id, data) => client.post(`/barber/blog/${id}`, data),
  deleteBlogPost: (id) => client.delete(`/barber/blog/${id}`),
  analytics: (range) => client.get('/barber/analytics', { params: { range } }),
};