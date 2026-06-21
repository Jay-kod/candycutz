import client from '../../../core/api/axios';

export const authApi = {
  login: (credentials) => client.post('/auth/login', credentials),
  register: (data) => client.post('/auth/register', data),
  logout: () => client.post('/auth/logout'),
  me: () => client.get('/auth/me'),
};