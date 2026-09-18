import { ref } from 'vue';
import { adminApi } from '@/shared/api/old_adminApi';
import { useToast } from '@/core/composables/useToast';

export function usePaymentSettings() {
  const toast = useToast();
  const showSettingsModal = ref(false);
  const savingSettings = ref(false);
  
  const settingsForm = ref({
    bank_name: '',
    account_name: '',
    account_number: ''
  });

  const loadSettings = async () => {
    try {
      const response = await adminApi.settings();
      const data = response.data.data || {};
      settingsForm.value.bank_name = data.bank_name || '';
      settingsForm.value.account_name = data.account_name || '';
      settingsForm.value.account_number = data.account_number || '';
    } catch (err) {
      console.error(err);
    }
  };

  const saveSettings = async () => {
    if (!settingsForm.value.bank_name || !settingsForm.value.account_name || !settingsForm.value.account_number) {
      return toast.error('Please fill out all fields');
    }
    
    savingSettings.value = true;
    try {
      await adminApi.updateSettings(settingsForm.value);
      toast.success('Payment settings updated');
      showSettingsModal.value = false;
    } catch (err) {
      toast.error('Failed to save settings');
    } finally {
      savingSettings.value = false;
    }
  };

  return {
    showSettingsModal,
    savingSettings,
    settingsForm,
    loadSettings,
    saveSettings
  };
}
