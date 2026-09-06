import client from '../../../core/api/axios';

export const authApi = {
  login: (credentials) => client.post('/auth/login', credentials),
  register: (data) => client.post('/auth/register', data),
  logout: () => client.post('/auth/logout'),
  me: () => client.get('/auth/me'),
  socialLogin: (data) => client.post('/auth/social-login', data),
  forgotPassword: (email) => client.post('/auth/forgot-password', { email }),
  resetPassword: (data) => client.post('/auth/reset-password', data),
};