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
      'X-Client-Type': 'web',
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
        localStorage.removeItem('candycutz_auth_token');

        const requestUrl = error.config?.url || '';
        const isAuthRequest = requestUrl.includes('/auth/login') ||
                              requestUrl.includes('/auth/register') ||
                              requestUrl.includes('/auth/social-login');

        const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';
        const isAlreadyOnAuthPage = currentPath.includes('/login') ||
                                    currentPath.includes('/register') ||
                                    currentPath.includes('/forgot-password');

        if (!isAuthRequest && !isAlreadyOnAuthPage) {
          const redirectPath = currentPath.startsWith('/superadmin')
            ? '/superadmin/login'
            : currentPath.startsWith('/admin')
              ? '/admin/login'
              : currentPath.startsWith('/barber')
                ? '/barber/login'
                : '/customer/login';

          toast.error('Session expired. Please log in again.');
          window.location.href = redirectPath;
        }

        return Promise.reject(error);
      } else if (error.response?.status === 403) {
        // Forbidden
        toast.error('You do not have permission to perform this action.');
      } else if (error.response?.status === 422) {
        // Validation error - don't show generic toast, let component handle it
        return Promise.reject(error);
      } else if (error.response?.status === 404) {
        // Log 404 warning in console without triggering disruptive UI toasts
        console.warn(`[API 404] ${error.config?.method?.toUpperCase()} ${error.config?.url}: ${message}`);
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
