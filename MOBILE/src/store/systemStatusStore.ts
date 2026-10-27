import { create } from 'zustand';

export interface UpgradeData {
  min_version?: string;
  latest_version?: string;
  store_url?: string;
  release_notes?: string;
}

interface SystemStatusState {
  isMaintenance: boolean;
  maintenanceMessage: string | null;
  isUpgradeRequired: boolean;
  upgradeData: UpgradeData | null;

  setMaintenance: (isMaintenance: boolean, message?: string | null) => void;
  setUpgradeRequired: (required: boolean, data?: UpgradeData | null) => void;
  clearStatus: () => void;
}

export const useSystemStatusStore = create<SystemStatusState>((set) => ({
  isMaintenance: false,
  maintenanceMessage: null,
  isUpgradeRequired: false,
  upgradeData: null,

  setMaintenance: (isMaintenance: boolean, message?: string | null) => {
    set({
      isMaintenance,
      maintenanceMessage: message || 'The mobile app is undergoing scheduled maintenance. Please check back shortly.',
    });
  },

  setUpgradeRequired: (required: boolean, data?: UpgradeData | null) => {
    set({
      isUpgradeRequired: required,
      upgradeData: data || null,
    });
  },

  clearStatus: () => {
    set({
      isMaintenance: false,
      maintenanceMessage: null,
      isUpgradeRequired: false,
      upgradeData: null,
    });
  },
}));
