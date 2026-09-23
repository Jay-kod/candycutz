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
  BlogPost,
  ChairStatus,
  GalleryItem,
  Notification,
  Service,
  ServiceZone,
  Testimonial,
  TimeSlot,
  User,
  WeeklyScheduleDay,
} from '../types';

import { useSystemStatusStore } from '../store/systemStatusStore';

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
  set: async (token: string, persist = true): Promise<void> => {
    memoryToken = token;
    try {
      if (Platform.OS === 'web') {
        if (typeof localStorage !== 'undefined') {
          if (persist) {
            localStorage.setItem(TOKEN_KEY, token);
          } else {
            localStorage.removeItem(TOKEN_KEY);
          }
        }
        return;
      }
      if (persist) {
        await SecureStore.setItemAsync(TOKEN_KEY, token);
      } else {
        await SecureStore.deleteItemAsync(TOKEN_KEY);
      }
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
    'X-Client-Type': 'mobile',
    'X-App-Version': CONFIG.APP_VERSION,
    'X-App-Platform': Platform.OS,
  },
  timeout: 15000,
});

// Request interceptor to attach Bearer token
apiClient.interceptors.request.use(
  async (config) => {
    // Prevent double /api/v1 or /v1 prefixes if endpoint includes them
    if (config.url) {
      if (config.url.startsWith('/api/v1/')) {
        config.url = config.url.replace('/api/v1/', '/');
      } else if (config.url.startsWith('api/v1/')) {
        config.url = `/${config.url.replace('api/v1/', '')}`;
      } else if (config.url.startsWith('/v1/')) {
        config.url = config.url.replace('/v1/', '/');
      } else if (config.url.startsWith('v1/')) {
        config.url = `/${config.url.replace('v1/', '')}`;
      }
    }

    try {
      config.headers['X-App-Version'] = CONFIG.APP_VERSION;
      config.headers['X-App-Platform'] = Platform.OS;
      config.headers['X-Client-Type'] = 'mobile';
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

// Response interceptor for auth expiration and system status (426 / 503)
apiClient.interceptors.response.use(
  (response) => {
    // If successful request from an app that previously had upgrade / maintenance set, clear if needed
    return response;
  },
  async (error: AxiosError) => {
    if (error.response?.status === 401) {
      try {
        await tokenStorage.remove();
      } catch (e) {}
    } else if (error.response?.status === 426) {
      // HTTP 426 Upgrade Required
      const resData = (error.response?.data as any);
      useSystemStatusStore.getState().setUpgradeRequired(true, resData?.data);
    } else if (error.response?.status === 503) {
      // HTTP 503 Service Unavailable / Maintenance Mode
      const resData = (error.response?.data as any);
      const isMaintenance = resData?.maintenance || resData?.code === 'MAINTENANCE_MODE';
      if (isMaintenance) {
        useSystemStatusStore.getState().setMaintenance(true, resData?.message);
      }
    }
    return Promise.reject(error);
  }
);

// ==========================================
// Authentication Endpoints
// ==========================================
export const authApi = {
  login: async (identity: string, password: string, rememberMe = false): Promise<{ token: string; user: User }> => {
    const res = await apiClient.post<ApiResponse<{ token: string; user: User }>>('/auth/login', {
      identity,
      password,
    });
    const data = res.data.data || (res.data as any);
    if (data.token) {
      await tokenStorage.set(data.token, rememberMe);
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
  create: async (payload: {
    name: string;
    description?: string;
    price: number;
    duration_minutes: number;
    category_id?: number;
    image1?: string;
  }): Promise<Service> => {
    const formData = new FormData();
    formData.append('name', payload.name);
    if (payload.description) formData.append('description', payload.description);
    formData.append('price', String(payload.price));
    formData.append('duration_minutes', String(payload.duration_minutes));
    if (payload.category_id) formData.append('category_id', String(payload.category_id));
    if (payload.image1) {
      formData.append('image1', { uri: payload.image1, name: 'service.jpg', type: 'image/jpeg' } as any);
    }
    const res = await apiClient.post<ApiResponse<Service>>('/services', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return res.data.data || (res.data as any);
  },
  getCategories: async (): Promise<{ id: number; name: string }[]> => {
    const res = await apiClient.get<ApiResponse<{ id: number; name: string }[]>>('/service-categories');
    return res.data.data || (res.data as any) || [];
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

  getAccount: async (): Promise<{ user: User; barber: Barber | null }> => {
    const res = await apiClient.get<ApiResponse<{ user: User; barber: Barber | null }>>('/barbers/account');
    return res.data.data || (res.data as any);
  },

  updateAccount: async (payload: {
    name?: string;
    email?: string;
    phone?: string;
    bio?: string | null;
    specialties?: string[];
    experience_years?: number;
    instagram_url?: string | null;
  }): Promise<{ user: User; barber: Barber | null }> => {
    const res = await apiClient.put<ApiResponse<{ user: User; barber: Barber | null }>>('/barbers/account', payload);
    return res.data.data || (res.data as any);
  },

  updateAccountWithImages: async (payload: {
    name?: string;
    email?: string;
    phone?: string;
    bio?: string | null;
    specialties?: string[];
    experience_years?: number;
    instagram_url?: string | null;
    avatarUri?: string;
    coverImageUri?: string;
  }): Promise<{ user: User; barber: Barber | null }> => {
    const formData = new FormData();
    formData.append('name', payload.name || '');
    if (payload.email) formData.append('email', payload.email);
    formData.append('phone', payload.phone || '');
    formData.append('bio', payload.bio || '');
    formData.append('experience_years', String(payload.experience_years ?? 0));
    (payload.specialties || []).forEach((specialty) => formData.append('specialties[]', specialty));
    formData.append('instagram_url', payload.instagram_url || '');

    if (payload.avatarUri) {
      formData.append('avatar', { uri: payload.avatarUri, name: 'avatar.jpg', type: 'image/jpeg' } as any);
    }
    if (payload.coverImageUri) {
      formData.append('cover_image', { uri: payload.coverImageUri, name: 'cover.jpg', type: 'image/jpeg' } as any);
    }

    const res = await apiClient.post<ApiResponse<{ user: User; barber: Barber | null }>>('/barbers/account', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return res.data.data || (res.data as any);
  },

  updateUsername: async (username: string): Promise<User> => {
    const res = await apiClient.patch<ApiResponse<User>>('/barbers/account/username', { username });
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
// Customer Account & Profile Endpoints
// ==========================================
export const accountApi = {
  getProfile: async (): Promise<{ profile: User; history?: any }> => {
    const res = await apiClient.get<ApiResponse<{ profile: User; history?: any }>>('/customer/profile');
    return res.data.data || (res.data as any);
  },

  updateProfile: async (payload: {
    name?: string;
    username?: string;
    email?: string;
    phone?: string;
    bio?: string;
    avatarUri?: string;
  }): Promise<User> => {
    if (payload.avatarUri) {
      const formData = new FormData();
      if (payload.name) formData.append('name', payload.name);
      if (payload.username) formData.append('username', payload.username);
      if (payload.email) formData.append('email', payload.email);
      if (payload.phone) formData.append('phone', payload.phone);
      if (payload.bio) formData.append('bio', payload.bio);
      formData.append('avatar', {
        uri: payload.avatarUri,
        name: 'avatar.jpg',
        type: 'image/jpeg',
      } as any);

      const res = await apiClient.post<ApiResponse<User>>('/customer/profile', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      return res.data.data || (res.data as any);
    }

    const res = await apiClient.post<ApiResponse<User>>('/customer/profile', payload);
    return res.data.data || (res.data as any);
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
    const data = res.data.data as any;
    return Array.isArray(data) ? data : Array.isArray(data?.items) ? data.items : [];
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

// ==========================================
// Notifications & Push Tokens Endpoints
// ==========================================
export const notificationsApi = {
  getAll: async (type?: string): Promise<Notification[]> => {
    const res = await apiClient.get<ApiResponse<Notification[]>>('/notifications', {
      params: type ? { type } : undefined,
    });
    return res.data.data || (res.data as any) || [];
  },

  getUnreadCount: async (): Promise<number> => {
    const res = await apiClient.get<ApiResponse<{ count: number }>>('/notifications/unread-count');
    return res.data?.data?.count ?? 0;
  },

  markAsRead: async (id: number): Promise<void> => {
    await apiClient.patch(`/notifications/${id}/read`);
  },

  markAllRead: async (): Promise<void> => {
    await apiClient.patch('/notifications/read-all');
  },

  registerDeviceToken: async (
    token: string,
    platform: 'ios' | 'android' | 'web' = Platform.OS === 'ios' ? 'ios' : 'android'
  ): Promise<void> => {
    await apiClient.post('/notifications/device-token', {
      token,
      platform,
    });
  },

  deleteDeviceToken: async (token: string): Promise<void> => {
    await apiClient.delete('/notifications/device-token', {
      data: { token },
    });
  },

  getSettings: async (): Promise<Record<string, boolean>> => {
    const res = await apiClient.get<ApiResponse<Record<string, boolean>>>('/notification-settings');
    return res.data?.data || {};
  },

  updateSettings: async (settings: Record<string, boolean>): Promise<Record<string, boolean>> => {
    const res = await apiClient.post<ApiResponse<Record<string, boolean>>>('/notification-settings', settings);
    return res.data?.data || {};
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

// ==========================================
// Gallery API
// ==========================================
export const galleryApi = {
  getAll: async (): Promise<GalleryItem[]> => {
    const res = await apiClient.get<ApiResponse<GalleryItem[]>>('/gallery');
    return res.data.data || (res.data as any) || [];
  },
  upload: async (payload: {
    title: string;
    description?: string;
    category?: string;
    imageUri: string;
  }): Promise<GalleryItem> => {
    const formData = new FormData();
    formData.append('title', payload.title);
    if (payload.description) formData.append('description', payload.description);
    formData.append('category', payload.category || 'haircut');
    formData.append('image', { uri: payload.imageUri, name: 'gallery.jpg', type: 'image/jpeg' } as any);
    const res = await apiClient.post<ApiResponse<GalleryItem>>('/gallery', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return res.data.data || (res.data as any);
  },
  delete: async (id: number): Promise<void> => {
    await apiClient.delete(`/gallery/${id}`);
  },
};

// ==========================================
// Testimonials / Reviews API
// ==========================================
export const testimonialsApi = {
  getAll: async (params?: { service_id?: number; barber_id?: number; featured?: boolean }): Promise<Testimonial[]> => {
    const res = await apiClient.get<ApiResponse<Testimonial[]>>('/testimonials', { params });
    return res.data.data || (res.data as any) || [];
  },
  getMyReviews: async (): Promise<Testimonial[]> => {
    const res = await apiClient.get<ApiResponse<Testimonial[]>>('/reviews/me');
    return res.data.data || (res.data as any) || [];
  },
  submit: async (payload: {
    rating: number;
    review: string;
    service_id?: number;
    barber_id?: number;
  }): Promise<Testimonial> => {
    const res = await apiClient.post<ApiResponse<Testimonial>>('/testimonials', payload);
    return res.data.data || (res.data as any);
  },
};

// ==========================================
// Blog API
// ==========================================
export const blogApi = {
  getAll: async (): Promise<BlogPost[]> => {
    const res = await apiClient.get<ApiResponse<BlogPost[]>>('/blog');
    return res.data.data || (res.data as any) || [];
  },
  getBySlug: async (slug: string): Promise<BlogPost> => {
    const res = await apiClient.get<ApiResponse<BlogPost>>(`/blog/${slug}`);
    return res.data.data || (res.data as any);
  },
  create: async (payload: {
    title: string;
    excerpt?: string;
    content: string;
    imageUri?: string;
    status?: string;
  }): Promise<BlogPost> => {
    const formData = new FormData();
    formData.append('title', payload.title);
    if (payload.excerpt) formData.append('excerpt', payload.excerpt);
    formData.append('content', payload.content);
    formData.append('status', payload.status || 'published');
    if (payload.imageUri) {
      formData.append('featured_image', { uri: payload.imageUri, name: 'blog.jpg', type: 'image/jpeg' } as any);
    }
    const res = await apiClient.post<ApiResponse<BlogPost>>('/blog', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return res.data.data || (res.data as any);
  },
  react: async (id: number, type: string = 'love'): Promise<void> => {
    await apiClient.post(`/blog/${id}/react`, { reaction_type: type });
  },
  removeReaction: async (id: number): Promise<void> => {
    await apiClient.delete(`/blog/${id}/react`);
  },
};

// ==========================================
// CMS / Policy Settings API
// ==========================================
export const cmsApi = {
  getSettings: async (): Promise<Record<string, any>> => {
    const res = await apiClient.get<ApiResponse<Record<string, any>>>('/settings');
    return res.data.data || (res.data as any) || {};
  },
};

// ==========================================
// Feature Flags API
// ==========================================
export const featureFlagsApi = {
  getFlags: async (): Promise<Record<string, boolean>> => {
    const res = await apiClient.get<ApiResponse<Record<string, boolean>>>('/feature-flags');
    return res.data?.data || {};
  },
};

// ==========================================
// App Telemetry API
// ==========================================
export const appTelemetryApi = {
  reportCrash: async (payload: {
    error_message: string;
    stack_trace?: string | null;
    component_stack?: string | null;
    app_version?: string;
    platform?: string;
    device_info?: Record<string, any>;
  }): Promise<{ crash_id: number }> => {
    const res = await apiClient.post<ApiResponse<{ crash_id: number }>>('/app/crashes', payload);
    return res.data?.data || (res.data as any);
  },
};


