import axios, { AxiosError } from 'axios';
import * as SecureStore from 'expo-secure-store';
import { CONFIG } from '../constants/config';
import {
  ApiResponse,
  Appointment,
  AppointmentStatus,
  BarberProfile,
  BlockedPeriod,
  ChairStatus,
  Service,
  WeeklyScheduleDay,
} from '../types';

const BARBER_TOKEN_KEY = 'candycutz_barber_token';

export const staffApiClient = axios.create({
  baseURL: CONFIG.API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  timeout: 15000,
});

staffApiClient.interceptors.request.use(
  async (config) => {
    try {
      const token = await SecureStore.getItemAsync(BARBER_TOKEN_KEY);
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    } catch (e) {}
    return config;
  },
  (error) => Promise.reject(error)
);

staffApiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    if (error.response?.status === 401) {
      try {
        await SecureStore.deleteItemAsync(BARBER_TOKEN_KEY);
      } catch (e) {}
    }
    return Promise.reject(error);
  }
);

export const barberTokenStorage = {
  get: () => SecureStore.getItemAsync(BARBER_TOKEN_KEY),
  set: (token: string) => SecureStore.setItemAsync(BARBER_TOKEN_KEY, token),
  remove: () => SecureStore.deleteItemAsync(BARBER_TOKEN_KEY),
};

export const staffAuthApi = {
  login: async (identity: string, password: string): Promise<{ token: string; barber: BarberProfile }> => {
    const res = await staffApiClient.post<ApiResponse<{ token: string; barber: BarberProfile }>>('/auth/login', {
      identity,
      password,
    });
    const data = res.data.data || res.data;
    if (data.token) {
      await barberTokenStorage.set(data.token);
    }
    return data;
  },

  me: async (): Promise<BarberProfile> => {
    const res = await staffApiClient.get<ApiResponse<BarberProfile>>('/auth/me');
    return res.data.data || res.data;
  },

  updateChairStatus: async (status: ChairStatus): Promise<BarberProfile> => {
    const res = await staffApiClient.patch<ApiResponse<BarberProfile>>('/barbers/chair-status', { status });
    return res.data.data || res.data;
  },

  logout: async (): Promise<void> => {
    try {
      await staffApiClient.post('/auth/logout');
    } finally {
      await barberTokenStorage.remove();
    }
  },
};

export const staffQueueApi = {
  getTodayQueue: async (): Promise<Appointment[]> => {
    const today = new Date().toISOString().split('T')[0];
    const res = await staffApiClient.get<ApiResponse<Appointment[]>>('/barbers/my-appointments', {
      params: { date: today },
    });
    return res.data.data || res.data || [];
  },

  transitionStatus: async (appointmentId: number, status: AppointmentStatus, notes?: string): Promise<Appointment> => {
    const res = await staffApiClient.patch<ApiResponse<Appointment>>(`/appointments/${appointmentId}/status`, {
      status,
      notes,
    });
    return res.data.data || res.data;
  },

  getAllAppointments: async (params?: { date?: string; status?: string }): Promise<Appointment[]> => {
    const res = await staffApiClient.get<ApiResponse<Appointment[]>>('/barbers/my-appointments', {
      params,
    });
    return res.data.data || res.data || [];
  },
};

export const staffScheduleApi = {
  getSchedule: async (): Promise<WeeklyScheduleDay[]> => {
    const res = await staffApiClient.get<ApiResponse<WeeklyScheduleDay[]>>('/barbers/schedule');
    return res.data.data || res.data || [];
  },

  updateSchedule: async (schedule: WeeklyScheduleDay[]): Promise<WeeklyScheduleDay[]> => {
    const res = await staffApiClient.put<ApiResponse<WeeklyScheduleDay[]>>('/barbers/schedule', { schedule });
    return res.data.data || res.data;
  },

  getBlockedPeriods: async (): Promise<BlockedPeriod[]> => {
    const res = await staffApiClient.get<ApiResponse<BlockedPeriod[]>>('/barbers/blocked-periods');
    return res.data.data || res.data || [];
  },

  addBlockedPeriod: async (payload: {
    start_datetime: string;
    end_datetime: string;
    reason?: string;
  }): Promise<BlockedPeriod> => {
    const res = await staffApiClient.post<ApiResponse<BlockedPeriod>>('/barbers/blocked-periods', payload);
    return res.data.data || res.data;
  },

  deleteBlockedPeriod: async (id: number): Promise<void> => {
    await staffApiClient.delete(`/barbers/blocked-periods/${id}`);
  },
};

export const staffWalkInApi = {
  getServices: async (): Promise<Service[]> => {
    const res = await staffApiClient.get<ApiResponse<Service[]>>('/services');
    return res.data.data || res.data || [];
  },

  createWalkIn: async (payload: {
    customer_name: string;
    customer_phone?: string;
    service_id: number;
    payment_method: 'cash' | 'pos';
    notes?: string;
  }): Promise<Appointment> => {
    const res = await staffApiClient.post<ApiResponse<Appointment>>('/barbers/walk-in', payload);
    return res.data.data || res.data;
  },
};
