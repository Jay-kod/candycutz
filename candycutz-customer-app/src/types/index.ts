export type UserRole = 'customer' | 'barber' | 'admin';

export interface User {
  id: number;
  name: string;
  real_name?: string;
  username: string;
  email: string;
  phone?: string;
  role: UserRole;
  avatar?: string;
  wallet_balance?: number;
  created_at?: string;
}

export interface Service {
  id: number;
  name: string;
  slug?: string;
  description: string;
  price: number;
  home_service_price?: number;
  duration_minutes: number;
  category?: string;
  image_url?: string;
  is_active: boolean;
}

export interface Barber {
  id: number;
  user_id: number;
  name: string;
  real_name?: string;
  username?: string;
  specialties?: string[];
  rating: number;
  total_reviews?: number;
  experience_years?: number;
  avatar?: string;
  is_active: boolean;
  chair_status?: 'free' | 'busy' | 'break' | 'offline';
}

export interface ServiceZone {
  id: number;
  name: string;
  code: string;
  surcharge: number;
  min_order_amount: number;
  is_active: boolean;
  estimated_travel_minutes: number;
}

export interface Address {
  id?: number;
  label: string; // e.g. 'Home', 'Office'
  street_address: string;
  area_landmark: string;
  city: string;
  state: string;
  service_zone_id?: number;
  latitude?: number;
  longitude?: number;
}

export type AppointmentStatus = 
  | 'pending'
  | 'confirmed'
  | 'in_progress'
  | 'completed'
  | 'cancelled'
  | 'no_show';

export type PaymentStatus = 'pending' | 'paid' | 'partially_paid' | 'refunded' | 'failed';

export interface Appointment {
  id: number;
  booking_reference: string;
  customer_id: number;
  barber_id?: number;
  service_id: number;
  branch_id?: number;
  appointment_date: string; // YYYY-MM-DD
  start_time: string; // HH:mm:ss
  end_time: string; // HH:mm:ss
  appointment_type: 'in_shop' | 'home_service';
  status: AppointmentStatus;
  payment_status: PaymentStatus;
  subtotal: number;
  home_service_surcharge: number;
  discount_amount: number;
  grand_total: number;
  notes?: string;
  service?: Service;
  barber?: Barber;
  destination_address?: Address;
  created_at: string;
}

export interface TimeSlot {
  time: string; // HH:mm
  available: boolean;
  barber_id?: number;
}

export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data: T;
  meta?: {
    current_page?: number;
    last_page?: number;
    total?: number;
  };
}
