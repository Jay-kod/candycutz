import axios from 'axios';
import { useToast } from '@/core/composables/useToast';
import { API_BASE_URL } from '@/core/utils/url';

const toast = useToast();

export function setupAxiosInterceptors() {
  const api = axios.create({
    baseURL: API_BASE_URL,
    timeout: 10000,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
  });

  // Request interceptor
  api.interceptors.request.use(
    (config) => {
      // Normalize requested URL to guarantee clean /v1/ routing without double prefixes
      if (config.url) {
        let url = config.url;
        if (url.startsWith('/api/v1/')) {
          config.url = url.replace('/api/v1/', '/v1/');
        } else if (url.startsWith('api/v1/')) {
          config.url = `/${url.replace('api/v1/', 'v1/')}`;
        } else if (!url.startsWith('/v1/') && !url.startsWith('http://') && !url.startsWith('https://')) {
          config.url = `/v1/${url.replace(/^\/+/, '')}`;
        }
      }

      const token = localStorage.getItem('candycutz_auth_token');
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
      return config;
    },
    (error) => {
      return Promise.reject(error);
    }
  );

  // Response interceptor
  api.interceptors.response.use(
    (response) => {
      return response;
    },
    (error) => {
      const message = error.response?.data?.message || 'An error occurred. Please try again.';

      // Handle specific status codes
      if (error.response?.status === 401) {
        // Unauthorized - redirect to login
        localStorage.removeItem('candycutz_auth_token');
        const path = window.location.pathname;
        window.location.href = path.startsWith('/admin')
          ? '/admin/login'
          : path.startsWith('/barber')
            ? '/barber/login'
            : '/customer/login';
        toast.error('Session expired. Please log in again.');
      } else if (error.response?.status === 403) {
        // Forbidden
        toast.error('You do not have permission to perform this action.');
      } else if (error.response?.status === 422) {
        // Validation error - don't show generic toast, let component handle it
        return Promise.reject(error);
      } else if (error.response?.status === 500) {
        toast.error('Server error. Please contact support.');
      } else if (error.code === 'ECONNABORTED') {
        toast.error('Request timeout. Please try again.');
      } else if (!error.response) {
        toast.error('Network error. Please check your connection.');
      } else {
        toast.error(message);
      }

      return Promise.reject(error);
    }
  );

  return api;
}

const client = setupAxiosInterceptors();
export const api = client;
export { client };
export default client;
