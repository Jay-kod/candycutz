import { create } from 'zustand';
import { Appointment, ChairStatus } from '../types';

interface ChairState {
  activeClient: Appointment | null;
  serviceStartTime: number | null; // Timestamp
  elapsedSeconds: number;

  setActiveClient: (appointment: Appointment | null) => void;
  tickTimer: () => void;
  resetTimer: () => void;
}

export const useChairStore = create<ChairState>((set, get) => ({
  activeClient: null,
  serviceStartTime: null,
  elapsedSeconds: 0,

  setActiveClient: (appointment) => {
    if (appointment) {
      set({
        activeClient: appointment,
        serviceStartTime: Date.now(),
        elapsedSeconds: 0,
      });
    } else {
      set({
        activeClient: null,
        serviceStartTime: null,
        elapsedSeconds: 0,
      });
    }
  },

  tickTimer: () => {
    const { serviceStartTime } = get();
    if (serviceStartTime) {
      const seconds = Math.floor((Date.now() - serviceStartTime) / 1000);
      set({ elapsedSeconds: seconds });
    }
  },

  resetTimer: () => set({ elapsedSeconds: 0, serviceStartTime: null }),
}));
