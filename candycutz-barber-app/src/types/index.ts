export type ChairStatus = 'free' | 'busy' | 'break' | 'offline';

export interface BarberProfile {
  id: number;
  user_id: number;
  name: string;
  real_name?: string;
  username: string;
  email: string;
  phone?: string;
  avatar?: string;
  chair_status: ChairStatus;
  is_active: boolean;
  rating: number;
  today_cuts_count?: number;
  today_earnings?: number;
}

export type AppointmentStatus = 
  | 'pending'
  | 'confirmed'
  | 'in_progress'
  | 'completed'
  | 'cancelled'
  | 'no_show';

export interface Customer {
  id: number;
  name: string;
  username?: string;
  phone?: string;
  avatar?: string;
}

export interface Service {
  id: number;
  name: string;
  price: number;
  duration_minutes: number;
  description?: string;
}

export interface Appointment {
  id: number;
  booking_reference: string;
  customer_id: number;
  barber_id: number;
  service_id: number;
  appointment_date: string;
  start_time: string;
  end_time: string;
  appointment_type: 'in_shop' | 'home_service';
  status: AppointmentStatus;
  payment_status: 'pending' | 'paid' | 'failed';
  grand_total: number;
  notes?: string;
  customer?: Customer;
  service?: Service;
  destination_address?: {
    street_address: string;
    area_landmark: string;
    city: string;
  };
}

export interface WeeklyScheduleDay {
  day_of_week: number; // 0 = Sunday, 1 = Monday, etc.
  day_name: string;
  is_working: boolean;
  start_time: string; // HH:mm
  end_time: string; // HH:mm
}

export interface BlockedPeriod {
  id: number;
  barber_id: number;
  start_datetime: string;
  end_datetime: string;
  reason?: string;
}

export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data: T;
}
