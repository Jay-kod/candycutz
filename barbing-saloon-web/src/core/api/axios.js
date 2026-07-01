import axios from 'axios';
import { useAuthStore } from '../../modules/auth/store/auth.store';
import { useToast } from '../composables/useToast';

const toast = useToast();

const client = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  withCredentials: false,
  timeout: 10000,
});

// Request interceptor: add auth token
client.interceptors.request.use((config) => {
  const auth = useAuthStore();
  const token = auth.token || localStorage.getItem('candycutz_auth_token');

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
});

// Response interceptor: handle errors consistently
client.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status;
    const message = error?.response?.data?.message || error?.response?.data?.error || error?.message || 'An error occurred. Please try again.';

    switch (status) {
      case 401:
        // Unauthorized - clear auth and redirect (but not during login/register)
        if (!error.config?.url?.includes('/auth/login') && !error.config?.url?.includes('/auth/register')) {
          const auth = useAuthStore();
          auth.clearAuth();
          toast.error(error?.response?.data?.message || 'Session expired. Please log in again.');
          window.location.href = '/customer/login';
        } else {
          toast.error(message);
        }
        break;
      case 403:
        // Forbidden
        toast.error(error?.response?.data?.message || 'You do not have permission to perform this action.');
        break;
      case 422:
        // Validation error
        if (error?.response?.data?.errors) {
            const firstError = Object.values(error.response.data.errors)[0];
            const errorMsg = Array.isArray(firstError) ? firstError[0] : firstError;
            if (errorMsg) toast.error(errorMsg);
            else toast.error(message);
        } else {
            toast.error(message);
        }
        break;
      case 404:
        toast.error(error?.response?.data?.message || `Resource not found: ${error.config?.url}`);
        break;
      case 500:
        toast.error(error?.response?.data?.message || error?.response?.data?.error || 'Server error. Please contact support.');
        break;
      default:
        if (!error.response) {
          if (error.code === 'ECONNABORTED') {
            toast.error('Request timeout. Please try again.');
          } else {
            toast.error(`Network error: ${error.message}`);
          }
        } else {
          toast.error(message);
        }
    }

    return Promise.reject(error);
  },
);

export default client;