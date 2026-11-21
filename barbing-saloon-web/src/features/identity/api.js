import { api } from '@/shared/api/client';

export const authApi = {
  login: (data) => api.post('/v1/auth/login', data),
  register: (data) => api.post('/v1/auth/register', data),
  socialLogin: (data) => api.post('/v1/auth/social-login', data),
  logout: () => api.post('/v1/auth/logout'),
  me: () => api.get('/v1/auth/me'),
};
