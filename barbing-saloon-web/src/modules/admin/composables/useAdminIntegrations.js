import { ref, reactive, computed } from 'vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

export function useAdminIntegrations() {
  const toast = useToast();

  const loading = ref(true);
  const isRefreshing = ref(false);
  const saving = ref(false);
  const testing = ref(false);
  const testEmail = ref('');
  const showSmtpPassword = ref(false);

  const form = reactive({
    mail_host: 'smtp-relay.brevo.com',
    mail_port: '587',
    mail_username: '',
    mail_password: '',
    mail_from: '',
    mail_from_name: 'CandyCutz',
    google_client_id: '',
    google_android_client_id: '',
    google_android_name: 'CandyCutz Android Debug',
    google_android_package: 'com.candycutz.app',
    google_android_sha1: '5E:8F:16:06:2E:A3:CD:2C:4A:0D:54:78:76:BA:A6:F3:8C:AB:F6:25',
    apple_client_id: '',
    apple_team_id: '',
    apple_key_id: '',
    app_url: '',
  });

  const mailConfigured = computed(() => {
    return Boolean(
      form.mail_host?.trim() &&
      form.mail_port?.toString().trim() &&
      form.mail_username?.trim() &&
      form.mail_password?.trim() &&
      form.mail_from?.trim()
    );
  });

  const googleConfigured = computed(() => {
    return Boolean(form.google_client_id?.trim());
  });

  const appleConfigured = computed(() => {
    return Boolean(form.apple_client_id?.trim());
  });

  const stats = computed(() => {
    let configuredCount = 0;
    if (mailConfigured.value) configuredCount++;
    if (googleConfigured.value) configuredCount++;
    if (appleConfigured.value) configuredCount++;

    const totalServices = 3;
    const percentageConfigured = Math.round((configuredCount / totalServices) * 100);

    return {
      configuredCount,
      totalServices,
      percentageConfigured,
      mailConfigured: mailConfigured.value,
      googleConfigured: googleConfigured.value,
      appleConfigured: appleConfigured.value,
      googleAndroidConfigured: Boolean(form.google_android_client_id?.trim()),
    };
  });

  const applySettings = (all) => {
    const s = all || {};
    form.mail_host = s.mail_host || 'smtp-relay.brevo.com';
    form.mail_port = s.mail_port || '587';
    form.mail_username = s.mail_username || '';
    form.mail_password = s.mail_password || '';
    form.mail_from = s.mail_from || '';
    form.mail_from_name = s.mail_from_name || 'CandyCutz';
    form.google_client_id = s.google_client_id || '';
    form.google_android_client_id = s.google_android_client_id || '';
    form.google_android_name = s.google_android_name || 'CandyCutz Android Debug';
    form.google_android_package = s.google_android_package || 'com.candycutz.app';
    form.google_android_sha1 = s.google_android_sha1 || '5E:8F:16:06:2E:A3:CD:2C:4A:0D:54:78:76:BA:A6:F3:8C:AB:F6:25';
    form.apple_client_id = s.apple_client_id || '';
    form.apple_team_id = s.apple_team_id || '';
    form.apple_key_id = s.apple_key_id || '';
    form.app_url = s.app_url || '';
  };

  const loadSettings = async (silent = false) => {
    if (silent) {
      isRefreshing.value = true;
    } else {
      loading.value = true;
    }

    try {
      const res = await client.get('/admin/settings');
      applySettings(res.data?.data || res.data);
    } catch (error) {
      console.error('[AdminIntegrations] Failed to load settings:', error);
      toast.error('Failed to load integration settings');
    } finally {
      loading.value = false;
      isRefreshing.value = false;
    }
  };

  const saveIntegrations = async (serviceName = 'Integration') => {
    saving.value = true;
    try {
      await client.post('/admin/settings', {
        settings: { ...form },
      });
      toast.success(`${serviceName} settings saved successfully`);
    } catch (error) {
      console.error('[AdminIntegrations] Failed to save settings:', error);
      const msg = error?.response?.data?.error || error?.response?.data?.message || 'Failed to save settings';
      toast.error(msg);
    } finally {
      saving.value = false;
    }
  };

  const sendTestEmail = async () => {
    if (!testEmail.value || !testEmail.value.includes('@')) {
      toast.error('Enter a valid recipient email address first');
      return;
    }

    testing.value = true;
    try {
      const res = await client.post('/admin/test-email', {
        to: testEmail.value.trim(),
      });
      if (res.data?.success !== false) {
        toast.success(`Test email dispatched to ${testEmail.value}. Check your inbox`);
      } else {
        toast.error(res.data?.message || 'Test email delivery failed');
      }
    } catch (error) {
      console.error('[AdminIntegrations] Test email error:', error);
      const msg = error?.response?.data?.message || error?.response?.data?.error || 'Test email failed';
      toast.error(msg);
    } finally {
      testing.value = false;
    }
  };

  const generateGoogleAndroidDetails = () => {
    form.google_android_name = 'CandyCutz Android Debug';
    form.google_android_package = 'com.candycutz.app';
    form.google_android_sha1 = '5E:8F:16:06:2E:A3:CD:2C:4A:0D:54:78:76:BA:A6:F3:8C:AB:F6:25';
    toast.info('Generated Android OAuth credentials template. Click Save to persist.');
  };

  const copyValue = async (value, label) => {
    try {
      await navigator.clipboard.writeText(value || '');
      toast.success(`${label} copied to clipboard`);
    } catch {
      toast.error(`Could not copy ${label}`);
    }
  };

  return {
    form,
    loading,
    isRefreshing,
    saving,
    testing,
    testEmail,
    showSmtpPassword,
    mailConfigured,
    googleConfigured,
    appleConfigured,
    stats,
    loadSettings,
    saveIntegrations,
    sendTestEmail,
    generateGoogleAndroidDetails,
    copyValue,
  };
}
