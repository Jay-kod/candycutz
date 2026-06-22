import client from '../../../core/api/axios';

export const customerApi = {
  dashboard: () => client.get('/customer/dashboard'),
  bookings: () => client.get('/customer/bookings'),
  createBooking: (data) => client.post('/customer/bookings', data),
  booking: (id) => client.get(`/customer/bookings/${id}`),
  cancelBooking: (id) => client.patch(`/customer/bookings/${id}/cancel`),
  profile: () => client.get('/customer/profile'),
  updateProfile: (data) => client.post('/customer/profile', data, { headers: { 'Content-Type': 'multipart/form-data' } }),
  reviews: () => client.get('/customer/reviews'),
  createReview: (data) => client.post('/customer/testimonials', data),
  getWishlist: () => client.get('/customer/wishlist'),
  addToWishlist: (data) => client.post('/customer/wishlist', data),
  removeFromWishlist: (id) => client.delete(`/customer/wishlist/${id}`),
  removeFromWishlistByType: (type, id) => client.delete(`/customer/wishlist/item?type=${type}&id=${id}`),
  checkout: (data) => client.post('/customer/checkout', data),
  reactToBlogPost: (id, type) => client.post(`/customer/blog/${id}/react`, { type }),
  removeReactionFromBlogPost: (id) => client.delete(`/customer/blog/${id}/react`),
  notifications: () => client.get('/customer/notifications'),
};