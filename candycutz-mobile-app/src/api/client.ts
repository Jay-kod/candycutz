import axios, { AxiosError } from 'axios';
import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';
import { CONFIG } from '../constants/config';
import {
  ApiResponse,
  Appointment,
  AppointmentStatus,
  Barber,
  BarberProfile,
  BlockedPeriod,
  ChairStatus,
  Service,
  ServiceZone,
  TimeSlot,
  User,
  WeeklyScheduleDay,
} from '../types';

const TOKEN_KEY = 'candycutz_auth_token';

// In-memory fallback for web environment where SecureStore is unavailable
let memoryToken: string | null = null;

export const tokenStorage = {
  get: async (): Promise<string | null> => {
    try {
      if (Platform.OS === 'web') {
        return typeof localStorage !== 'undefined' ? localStorage.getItem(TOKEN_KEY) : memoryToken;
      }
      return await SecureStore.getItemAsync(TOKEN_KEY);
    } catch (e) {
      return memoryToken;
    }
  },
  set: async (token: string): Promise<void> => {
    memoryToken = token;
    try {
      if (Platform.OS === 'web') {
        if (typeof localStorage !== 'undefined') {
          localStorage.setItem(TOKEN_KEY, token);
        }
        return;
      }
      await SecureStore.setItemAsync(TOKEN_KEY, token);
    } catch (e) {
      // Ignored fallback
    }
  },
  remove: async (): Promise<void> => {
    memoryToken = null;
    try {
      if (Platform.OS === 'web') {
        if (typeof localStorage !== 'undefined') {
          localStorage.removeItem(TOKEN_KEY);
        }
        return;
      }
      await SecureStore.deleteItemAsync(TOKEN_KEY);
    } catch (e) {
      // Ignored fallback
    }
  },
};

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
      const token = await tokenStorage.get();
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    } catch (e) {
      // Gracefully continue
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
        await tokenStorage.remove();
      } catch (e) {}
    }
    return Promise.reject(error);
  }
);

// ==========================================
// Authentication Endpoints
// ==========================================
export const authApi = {
  login: async (identity: string, password: string): Promise<{ token: string; user: User }> => {
    const res = await apiClient.post<ApiResponse<{ token: string; user: User }>>('/auth/login', {
      identity,
      password,
    });
    const data = res.data.data || (res.data as any);
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
    const data = res.data.data || (res.data as any);
    if (data.token) {
      await tokenStorage.set(data.token);
    }
    return data;
  },

  me: async (): Promise<User> => {
    const res = await apiClient.get<ApiResponse<User>>('/auth/me');
    return res.data.data || (res.data as any);
  },

  logout: async (): Promise<void> => {
    try {
      await apiClient.post('/auth/logout');
    } finally {
      await tokenStorage.remove();
    }
  },

  socialLogin: async (payload: {
    provider: 'google' | 'apple';
    id_token: string;
    role?: 'customer' | 'barber';
  }): Promise<{ token: string; user: User }> => {
    const res = await apiClient.post<ApiResponse<{ token: string; user: User }>>('/auth/social-login', payload);
    const data = res.data.data || (res.data as any);
    if (data.token) {
      await tokenStorage.set(data.token);
    }
    return data;
  },

  forgotPassword: async (email: string): Promise<{ message: string }> => {
    const res = await apiClient.post<ApiResponse<{ message: string }>>('/auth/forgot-password', { email });
    return res.data.data || (res.data as any);
  },
};

// ==========================================
// Services Endpoints
// ==========================================
export const servicesApi = {
  getAll: async (): Promise<Service[]> => {
    const res = await apiClient.get<ApiResponse<Service[]>>('/services');
    return res.data.data || (res.data as any) || [];
  },
  getById: async (id: number): Promise<Service> => {
    const res = await apiClient.get<ApiResponse<Service>>(`/services/${id}`);
    return res.data.data || (res.data as any);
  },
};

// ==========================================
// Barbers & Staff Operations Endpoints
// ==========================================
export const barbersApi = {
  getAll: async (): Promise<Barber[]> => {
    const res = await apiClient.get<ApiResponse<Barber[]>>('/barbers');
    return res.data.data || (res.data as any) || [];
  },

  getById: async (id: number): Promise<Barber> => {
    const res = await apiClient.get<ApiResponse<Barber>>(`/barbers/${id}`);
    return res.data.data || (res.data as any);
  },

  updateChairStatus: async (status: ChairStatus): Promise<Barber> => {
    const res = await apiClient.patch<ApiResponse<Barber>>('/barbers/chair-status', { status });
    return res.data.data || (res.data as any);
  },

  getSchedule: async (): Promise<WeeklyScheduleDay[]> => {
    const res = await apiClient.get<ApiResponse<WeeklyScheduleDay[]>>('/barbers/schedule');
    return res.data.data || (res.data as any) || [];
  },

  updateSchedule: async (schedule: WeeklyScheduleDay[]): Promise<WeeklyScheduleDay[]> => {
    const res = await apiClient.put<ApiResponse<WeeklyScheduleDay[]>>('/barbers/schedule', { schedule });
    return res.data.data || (res.data as any);
  },

  getBlockedPeriods: async (): Promise<BlockedPeriod[]> => {
    const res = await apiClient.get<ApiResponse<BlockedPeriod[]>>('/barbers/blocked-periods');
    return res.data.data || (res.data as any) || [];
  },

  addBlockedPeriod: async (payload: {
    start_datetime: string;
    end_datetime: string;
    reason?: string;
  }): Promise<BlockedPeriod> => {
    const res = await apiClient.post<ApiResponse<BlockedPeriod>>('/barbers/blocked-periods', payload);
    return res.data.data || (res.data as any);
  },

  deleteBlockedPeriod: async (id: number): Promise<void> => {
    await apiClient.delete(`/barbers/blocked-periods/${id}`);
  },
};

// ==========================================
// Availability Endpoints
// ==========================================
export const availabilityApi = {
  getSlots: async (params: {
    date: string; // YYYY-MM-DD
    service_id: number;
    barber_id?: number;
    type?: 'in_shop' | 'home_service';
  }): Promise<TimeSlot[]> => {
    const res = await apiClient.get<ApiResponse<TimeSlot[]>>('/availability', { params });
    return res.data.data || (res.data as any) || [];
  },
};

// ==========================================
// Appointments & Queue Endpoints
// ==========================================
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
    return res.data.data || (res.data as any);
  },

  getAll: async (params?: { date?: string; status?: string }): Promise<Appointment[]> => {
    const res = await apiClient.get<ApiResponse<Appointment[]>>('/appointments', { params });
    return res.data.data || (res.data as any) || [];
  },

  getById: async (id: number): Promise<Appointment> => {
    const res = await apiClient.get<ApiResponse<Appointment>>(`/appointments/${id}`);
    return res.data.data || (res.data as any);
  },

  cancel: async (id: number, reason?: string): Promise<Appointment> => {
    const res = await apiClient.post<ApiResponse<Appointment>>(`/appointments/${id}/cancel`, { reason });
    return res.data.data || (res.data as any);
  },

  getTodayQueue: async (): Promise<Appointment[]> => {
    const today = new Date().toISOString().split('T')[0];
    const res = await apiClient.get<ApiResponse<Appointment[]>>('/barbers/my-appointments', {
      params: { date: today },
    });
    return res.data.data || (res.data as any) || [];
  },

  transitionStatus: async (
    appointmentId: number,
    status: AppointmentStatus,
    notes?: string
  ): Promise<Appointment> => {
    const res = await apiClient.patch<ApiResponse<Appointment>>(`/appointments/${appointmentId}/status`, {
      status,
      notes,
    });
    return res.data.data || (res.data as any);
  },

  createWalkIn: async (payload: {
    customer_name: string;
    customer_phone?: string;
    service_id: number;
    payment_method: 'cash' | 'pos';
    notes?: string;
  }): Promise<Appointment> => {
    const res = await apiClient.post<ApiResponse<Appointment>>('/barbers/walk-in', payload);
    return res.data.data || (res.data as any);
  },
};

// ==========================================
// Service Zones Endpoints
// ==========================================
export const zonesApi = {
  getServiceZones: async (): Promise<ServiceZone[]> => {
    const res = await apiClient.get<ApiResponse<ServiceZone[]>>('/service-zones');
    return res.data.data || (res.data as any) || [];
  },
};

// Staff/Barber Operation Aliases for Unified Mobile Component Compatibility
export const staffQueueApi = {
  getTodayQueue: bookingsApi.getTodayQueue,
  transitionStatus: bookingsApi.transitionStatus,
  getAllAppointments: bookingsApi.getAll,
};

export const staffScheduleApi = barbersApi;

export const staffWalkInApi = {
  getServices: servicesApi.getAll,
  createWalkIn: bookingsApi.createWalkIn,
};

export const staffAuthApi = authApi;
export const barberTokenStorage = tokenStorage;

