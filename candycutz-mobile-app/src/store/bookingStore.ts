import { create } from 'zustand';
import { Address, Barber, Service, ServiceZone } from '../types';

interface BookingState {
  service: Service | null;
  barber: Barber | null;
  appointmentType: 'in_shop' | 'home_service';
  appointmentDate: string; // YYYY-MM-DD
  startTime: string; // HH:mm
  address: Address | null;
  selectedZone: ServiceZone | null;
  paymentMethod: 'pay_at_venue' | 'stripe' | 'wallet';
  notes: string;

  // Actions
  setService: (service: Service | null) => void;
  setBarber: (barber: Barber | null) => void;
  setAppointmentType: (type: 'in_shop' | 'home_service') => void;
  setAppointmentDate: (date: string) => void;
  setStartTime: (time: string) => void;
  setAddress: (address: Address | null) => void;
  setSelectedZone: (zone: ServiceZone | null) => void;
  setPaymentMethod: (method: 'pay_at_venue' | 'stripe' | 'wallet') => void;
  setNotes: (notes: string) => void;
  resetBooking: () => void;

  // Computed helper
  calculateTotal: () => {
    subtotal: number;
    surcharge: number;
    total: number;
  };
}

const initialState = {
  service: null,
  barber: null,
  appointmentType: 'in_shop' as const,
  appointmentDate: new Date().toISOString().split('T')[0],
  startTime: '',
  address: null,
  selectedZone: null,
  paymentMethod: 'pay_at_venue' as const,
  notes: '',
};

export const useBookingStore = create<BookingState>((set, get) => ({
  ...initialState,

  setService: (service) => set({ service }),
  setBarber: (barber) => set({ barber }),
  setAppointmentType: (appointmentType) => set({ appointmentType }),
  setAppointmentDate: (appointmentDate) => set({ appointmentDate, startTime: '' }),
  setStartTime: (startTime) => set({ startTime }),
  setAddress: (address) => set({ address }),
  setSelectedZone: (selectedZone) => set({ selectedZone }),
  setPaymentMethod: (paymentMethod) => set({ paymentMethod }),
  setNotes: (notes) => set({ notes }),

  resetBooking: () => set(initialState),

  calculateTotal: () => {
    const { service, appointmentType, selectedZone } = get();
    const basePrice = service?.price || 0;
    let surcharge = 0;

    if (appointmentType === 'home_service') {
      surcharge = selectedZone?.surcharge || 1500; // default 1500 NGN if zone not selected
    }

    return {
      subtotal: basePrice,
      surcharge,
      total: basePrice + surcharge,
    };
  },
}));
