import axios from 'axios';
import { useToast } from 'vue-toastification';

const toast = useToast();

export function setupAxiosInterceptors() {
  const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
    timeout: 10000,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
  });

  // Request interceptor
  api.interceptors.request.use(
    (config) => {
      const token = localStorage.getItem('auth_token');
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
        localStorage.removeItem('auth_token');
        window.location.href = '/customer/login';
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

export const api = setupAxiosInterceptors();
