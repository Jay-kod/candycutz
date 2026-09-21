import { create } from 'zustand';

export type ToastVariant = 'success' | 'error' | 'warning' | 'info';

export interface ToastConfig {
  variant: ToastVariant;
  title: string;
  message?: string;
  /** Duration in ms before auto-dismiss. Defaults: success/info/warning=3000, error=5000 */
  duration?: number;
  /** Optional callback when toast is dismissed (by timeout or swipe) */
  onDismiss?: () => void;
}

interface ToastState {
  current: ToastConfig | null;
  show: (config: ToastConfig) => void;
  dismiss: () => void;
}

export const useToastStore = create<ToastState>((set, get) => ({
  current: null,

  show: (config: ToastConfig) => {
    set({ current: config });
  },

  dismiss: () => {
    const prev = get().current;
    set({ current: null });
    if (prev?.onDismiss) {
      prev.onDismiss();
    }
  },
}));
