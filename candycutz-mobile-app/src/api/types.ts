/**
 * AUTO-GENERATED FROM OPENAPI 3.0 SPEC (docs/openapi.json)
 * DO NOT EDIT DIRECTLY. Run `npm run generate:types` to regenerate.
 */

export type UserRole = 'customer' | 'barber' | 'admin' | 'superadmin' | 'super_admin';
export type ChairStatus = 'free' | 'busy' | 'break' | 'offline';
export type AppointmentStatus = 'pending' | 'confirmed' | 'in_progress' | 'completed' | 'cancelled' | 'no_show';
export type PaymentStatus = 'pending' | 'paid' | 'partially_paid' | 'refunded' | 'failed' | 'awaiting_transfer' | 'receipt_uploaded' | 'under_review' | 'verified' | 'rejected' | 'successful';
export type PlatformType = 'ios' | 'android' | 'web';

export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data: T;
  meta?: {
    current_page?: number;
    last_page?: number;
    per_page?: number;
    total?: number;
  };
}

export interface ApiErrorResponse {
  success: boolean;
  message: string;
  error: {
    code?: string;
    details?: Record<string, any>;
  };
}

export interface User {
  id: number;
  name: string;
  real_name?: string | null;
  username: string;
  email: string;
  phone?: string | null;
  role: 'customer' | 'barber' | 'admin' | 'super_admin';
  avatar?: string | null;
  wallet_balance?: number;
  notification_preferences?: Record<string, any> | null;
  barber?: Barber;
  created_at?: string;
}

export interface Customer {
  id: number;
  name: string;
  username?: string | null;
  phone?: string | null;
  avatar?: string | null;
  email?: string | null;
}

export interface Barber {
  id: number;
  user_id: number;
  name: string;
  real_name?: string | null;
  username?: string;
  email?: string | null;
  phone?: string | null;
  chair_status: 'free' | 'busy' | 'break' | 'offline';
  is_available?: boolean;
  is_active: boolean;
  rating: number;
  total_reviews?: number;
  experience_years?: number;
  bio?: string | null;
  specialties?: string[];
  avatar?: string | null;
}

export interface Address {
  id?: number | null;
  label?: string | null;
  street_address: string;
  area_landmark: string;
  city: string;
  state?: string | null;
  service_zone_id?: number | null;
  latitude?: number | null;
  longitude?: number | null;
}

export interface Service {
  id: number;
  name: string;
  slug?: string;
  description?: string;
  price: number;
  home_service_price?: number | null;
  duration_minutes: number;
  category_id?: number | null;
  category?: string | null;
  image_url?: string | null;
  is_active: boolean;
}

export interface ServiceZone {
  id: number;
  name: string;
  code: string;
  surcharge: number;
  min_order_amount?: number;
  estimated_travel_minutes?: number;
  is_active: boolean;
}

export interface Appointment {
  id: number;
  booking_reference: string;
  customer_id: number;
  barber_id?: number | null;
  service_id?: number;
  appointment_date: string;
  start_time: string;
  end_time: string;
  appointment_type?: 'in_shop' | 'home_service';
  status: 'pending' | 'confirmed' | 'in_progress' | 'completed' | 'cancelled' | 'no_show';
  payment_status: 'pending' | 'paid' | 'partially_paid' | 'refunded' | 'failed';
  subtotal?: number;
  home_service_surcharge?: number;
  discount_amount?: number;
  grand_total: number;
  notes?: string | null;
  service?: Service;
  barber?: Barber;
  customer?: Customer;
  destination_address?: Address;
  created_at?: string;
}

export interface Payment {
  id: number;
  appointment_id: number;
  amount: number;
  currency: string;
  gateway: 'paystack' | 'manual_transfer' | 'stripe';
  payment_method?: string;
  status: 'awaiting_transfer' | 'receipt_uploaded' | 'under_review' | 'verified' | 'rejected' | 'successful' | 'failed' | 'refunded';
  transaction_reference?: string;
  receipt_path?: string | null;
  receipt_uploaded_at?: string | null;
  verified_at?: string | null;
  sla_expires_at?: string | null;
}

export interface DeviceToken {
  id: number;
  user_id: number;
  token: string;
  platform: 'ios' | 'android' | 'web';
  last_seen_at?: string | null;
}

export interface Notification {
  id: number;
  sender_id?: number | null;
  recipient_type?: string;
  recipient_id?: number | null;
  type?: string;
  title: string;
  message: string;
  related_entity_id?: number | null;
  is_read: boolean;
  created_at?: string;
}

export interface LoginRequest {
  identity: string;
  password: string;
}

export interface RegisterRequest {
  name: string;
  username: string;
  email: string;
  phone: string;
  password: string;
  password_confirmation: string;
}

export interface SocialLoginRequest {
  provider: 'google' | 'apple';
  id_token: string;
  role?: 'customer' | 'barber';
}

export interface ForgotPasswordRequest {
  email: string;
}

export interface ResetPasswordRequest {
  email: string;
  token: string;
  password: string;
  password_confirmation: string;
}

export interface CreateBookingRequest {
  service_id: number;
  barber_id?: number | null;
  appointment_date: string;
  start_time: string;
  appointment_type: 'in_shop' | 'home_service';
  destination_address?: {
    street_address?: string;
    area_landmark?: string;
    city?: string;
    state?: string;
    service_zone_id?: number;
  } | null;
  notes?: string | null;
}

export interface WalkInRequest {
  customer_name: string;
  customer_phone?: string | null;
  service_id: number;
  payment_method?: 'cash' | 'pos';
  notes?: string | null;
}

export interface CancelAppointmentRequest {
  reason?: string;
}

export interface UpdateStatusRequest {
  status: 'pending' | 'confirmed' | 'in_progress' | 'completed' | 'cancelled' | 'no_show';
  notes?: string | null;
}

export interface InitiateCheckoutRequest {
  appointment_id: number;
  gateway: 'paystack' | 'manual_transfer' | 'stripe';
  callback_url?: string | null;
}

export interface UploadReceiptRequest {
  receipt: string;
  notes?: string | null;
}

export interface RegisterDeviceTokenRequest {
  token: string;
  platform?: 'ios' | 'android' | 'web';
}

export interface UpdateNotificationPreferencesRequest {
  notify_appointments?: boolean;
  notify_promotions?: boolean;
  notify_wishlist?: boolean;
  notify_blog?: boolean;
  notify_general?: boolean;
}

export interface CreateServiceRequest {
  name: string;
  description?: string;
  price: number;
  home_service_price?: number | null;
  duration_minutes: number;
  category_id?: number | null;
  is_active?: boolean;
}

export interface UpdateScheduleRequest {
  schedule: {
    day_of_week: number;
    day_name?: string;
    start_time: string;
    end_time: string;
    is_closed?: boolean;
  }[];
}

export interface WeeklyScheduleDay {
  id?: number | null;
  day_of_week: number;
  day_name: string;
  is_working?: boolean;
  is_closed?: boolean;
  is_off?: boolean;
  open_time?: string;
  close_time?: string;
  start_time: string;
  end_time: string;
}

export interface BlockedPeriod {
  id: number;
  barber_id: number;
  start_datetime: string;
  end_datetime: string;
  reason?: string;
}

export interface TimeSlot {
  time: string;
  available: boolean;
  barber_id?: number;
}

export interface BarberProfile extends Barber {
  today_cuts_count?: number;
  today_earnings?: number;
}
