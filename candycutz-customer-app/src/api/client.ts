import axios, { AxiosError } from 'axios';
import * as SecureStore from 'expo-secure-store';
import { CONFIG } from '../constants/config';
import { ApiResponse, Appointment, Barber, Service, ServiceZone, TimeSlot, User } from '../types';

const TOKEN_KEY = 'candycutz_auth_token';

export const apiClient = axios.create({
  baseURL: CONFIG.API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  timeout: 15000,
});

// Request interceptor to attach Bearer token
apiClient.interceptors.request.use(
  async (config) => {
    try {
      const token = await SecureStore.getItemAsync(TOKEN_KEY);
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    } catch (e) {
      // SecureStore might fail on web fallback; handle gracefully
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor for auth expiration
apiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    if (error.response?.status === 401) {
      try {
        await SecureStore.deleteItemAsync(TOKEN_KEY);
      } catch (e) {}
    }
    return Promise.reject(error);
  }
);

// Secure token helpers
export const tokenStorage = {
  get: () => SecureStore.getItemAsync(TOKEN_KEY),
  set: (token: string) => SecureStore.setItemAsync(TOKEN_KEY, token),
  remove: () => SecureStore.deleteItemAsync(TOKEN_KEY),
};

// API Services
export const authApi = {
  login: async (identity: string, password: string): Promise<{ token: string; user: User }> => {
    // Identity can be username, email, or phone
    const res = await apiClient.post<ApiResponse<{ token: string; user: User }>>('/auth/login', {
      identity,
      password,
    });
    const data = res.data.data || res.data;
    if (data.token) {
      await tokenStorage.set(data.token);
    }
    return data;
  },

  register: async (payload: {
    name: string;
    username: string;
    email: string;
    phone: string;
    password: string;
    password_confirmation: string;
  }): Promise<{ token: string; user: User }> => {
    const res = await apiClient.post<ApiResponse<{ token: string; user: User }>>('/auth/register', payload);
    const data = res.data.data || res.data;
    if (data.token) {
      await tokenStorage.set(data.token);
    }
    return data;
  },

  me: async (): Promise<User> => {
    const res = await apiClient.get<ApiResponse<User>>('/auth/me');
    return res.data.data || res.data;
  },

  logout: async (): Promise<void> => {
    try {
      await apiClient.post('/auth/logout');
    } finally {
      await tokenStorage.remove();
    }
  },
};

export const servicesApi = {
  getAll: async (): Promise<Service[]> => {
    const res = await apiClient.get<ApiResponse<Service[]>>('/services');
    return res.data.data || res.data || [];
  },
  getById: async (id: number): Promise<Service> => {
    const res = await apiClient.get<ApiResponse<Service>>(`/services/${id}`);
    return res.data.data || res.data;
  },
};

export const barbersApi = {
  getAll: async (): Promise<Barber[]> => {
    const res = await apiClient.get<ApiResponse<Barber[]>>('/barbers');
    return res.data.data || res.data || [];
  },
  getById: async (id: number): Promise<Barber> => {
    const res = await apiClient.get<ApiResponse<Barber>>(`/barbers/${id}`);
    return res.data.data || res.data;
  },
};

export const availabilityApi = {
  getSlots: async (params: {
    date: string; // YYYY-MM-DD
    service_id: number;
    barber_id?: number;
    type?: 'in_shop' | 'home_service';
  }): Promise<TimeSlot[]> => {
    const res = await apiClient.get<ApiResponse<TimeSlot[]>>('/availability', { params });
    return res.data.data || res.data || [];
  },
};

export const bookingsApi = {
  create: async (payload: {
    service_id: number;
    barber_id?: number;
    appointment_date: string;
    start_time: string;
    appointment_type: 'in_shop' | 'home_service';
    destination_address?: {
      street_address: string;
      area_landmark: string;
      city: string;
      state: string;
      service_zone_id?: number;
    };
    payment_method: 'pay_at_venue' | 'stripe' | 'wallet';
    notes?: string;
  }): Promise<Appointment> => {
    const res = await apiClient.post<ApiResponse<Appointment>>('/appointments', payload);
    return res.data.data || res.data;
  },

  getAll: async (): Promise<Appointment[]> => {
    const res = await apiClient.get<ApiResponse<Appointment[]>>('/appointments');
    return res.data.data || res.data || [];
  },

  getById: async (id: number): Promise<Appointment> => {
    const res = await apiClient.get<ApiResponse<Appointment>>(`/appointments/${id}`);
    return res.data.data || res.data;
  },

  cancel: async (id: number, reason?: string): Promise<Appointment> => {
    const res = await apiClient.post<ApiResponse<Appointment>>(`/appointments/${id}/cancel`, { reason });
    return res.data.data || res.data;
  },
};

export const zonesApi = {
  getServiceZones: async (): Promise<ServiceZone[]> => {
    const res = await apiClient.get<ApiResponse<ServiceZone[]>>('/service-zones');
    return res.data.data || res.data || [];
  },
};
