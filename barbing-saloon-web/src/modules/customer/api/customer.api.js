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
  checkout: (data) => client.post('/customer/checkout', data),
};