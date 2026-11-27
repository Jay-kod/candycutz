<template>
  <component :is="layoutComponent">
    <div class="max-w-7xl mx-auto space-y-8 animate-fade-in pb-16 pt-2 px-4 sm:px-6 lg:px-8">
      <!-- Top Hero Banner with Navigation Tabs -->
      <ResendHero
        v-model:currentTab="currentTab"
        :is-configured="isConfigured"
        :loading="loading"
        :saving="saving"
        @refresh="refreshAll"
        @save="saveSettings"
      />

      <!-- High-Density KPI Status Cards -->
      <ResendStats
        :is-configured="isConfigured"
        :host-name="form.mail_host"
        :host-port="form.mail_port"
        :sender-email="form.mail_from"
        :sender-name="form.mail_from_name"
        :total-deliveries="emailStats.total"
        :failed-deliveries="emailStats.failed"
      />

      <ResendFlowDiagnostics
        :api-key-status="apiKeyStatus"
        :sender-email="form.mail_from"
        :settings-loaded="settingsLoaded"
        :settings-error="settingsError"
        :save-message="saveMessage"
        :save-error="saveError"
        :config-dirty="configDirty"
        :connection-result="connectionResult"
        :test-result="testResult"
        :delivery-error="deliveryError"
        :stats-error="statsError"
      />

      <!-- Tab 1: Credentials & Configuration -->
      <div v-if="currentTab === 'config'">
        <ResendConfigForm
          :form="form"
          :is-configured="isConfigured"
          :config-saved="!configDirty"
          :connection-test-ready="!apiKeyDirty"
          :saving="saving"
          :testing-connection="testingConnection"
          :connection-result="connectionResult"
          :testing="testing"
          :test-result="testResult"
          @save="saveSettings"
          @test-connection="testResendConnection"
          @test-dispatch="sendTestEmail"
        />
      </div>

      <!-- Tab 2: Step-by-Step Setup Guide -->
      <div v-else-if="currentTab === 'guide'">
        <ResendGuide />
      </div>

      <!-- Tab 3: Interactive Email Templates Studio -->
      <div v-else-if="currentTab === 'templates'">
        <EmailStudioCard
          :form="form"
          :is-configured="isConfigured"
          :saving="saving"
          :testing="testing"
          @save="saveSettings"
          @test-dispatch="sendTestEmail"
        />
      </div>

      <!-- Tab 4: Recent Email Deliveries & Audit Log -->
      <div v-else-if="currentTab === 'deliveries'">
        <ResendDeliveryLogs
          :deliveries="deliveries"
          :loading="loadingDeliveries"
          :error="deliveryError"
          @refresh="loadDeliveries"
        />
      </div>
    </div>
  </component>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import SuperAdminLayout from '@/portals/superadmin/layouts/SuperAdminLayout.vue';
import { client } from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

import ResendHero from '../components/resend/ResendHero.vue';
import ResendStats from '../components/resend/ResendStats.vue';
import ResendConfigForm from '../components/resend/ResendConfigForm.vue';
import ResendGuide from '../components/resend/ResendGuide.vue';
import ResendDeliveryLogs from '../components/resend/ResendDeliveryLogs.vue';
import ResendFlowDiagnostics from '../components/resend/ResendFlowDiagnostics.vue';
import EmailStudioCard from '../components/resend/EmailStudioCard.vue';

const route = useRoute();
const toast = useToast();

const layoutComponent = computed(() => {
  return route.path.startsWith('/superadmin') ? SuperAdminLayout : AdminLayout;
});

const currentTab = ref('config');
const loading = ref(false);
const saving = ref(false);
const testing = ref(false);
const testingConnection = ref(false);
const loadingDeliveries = ref(false);
const settingsLoaded = ref(false);
const settingsError = ref('');
const saveMessage = ref('');
const saveError = ref('');
const deliveryError = ref('');
const statsError = ref('');

const form = reactive({
  resend_api_key: '',
  mail_host: 'smtp.resend.com',
  mail_port: '587',
  mail_username: 'resend',
  mail_password: '',
  brevo_api_key: '',
  mail_from: '',
  mail_from_name: 'CandyCutz',
  mail_confirmed_subject: '',
  mail_reminder_subject: '',
  mail_receipt_subject: '',
  mail_cancellation_subject: '',
  mail_custom_note: '',
  mail_show_maps: true,
  mail_show_calendar: true,
  mail_show_whatsapp: true,
  mail_show_qr: true
});

const testResult = ref(null);
const connectionResult = ref(null);
const deliveries = ref([]);
const savedMailConfig = ref(null);
const emailStats = reactive({
  total: 0,
  failed: 0
});

const apiKeyStatus = computed(() => {
  const key = form.resend_api_key?.trim() || '';
  if (key.startsWith('••••••••')) return 'saved';
  if (key.startsWith('re_') && key.length > 3) return 'provided';
  return key ? 'invalid' : 'missing';
});

const isConfigured = computed(() => (
  ['saved', 'provided'].includes(apiKeyStatus.value) && Boolean(form.mail_from?.trim())
));

const configDirty = computed(() => {
  if (!savedMailConfig.value) return true;
  return savedMailConfig.value.apiKey !== form.resend_api_key ||
    savedMailConfig.value.sender !== form.mail_from;
});

const apiKeyDirty = computed(() => (
  !savedMailConfig.value || savedMailConfig.value.apiKey !== form.resend_api_key
));

watch(() => form.resend_api_key, () => {
  connectionResult.value = null;
});

function apiErrorMessage(error, fallback) {
  const response = error?.response?.data;
  const message = response?.error?.message || response?.message || error?.message;
  return typeof message === 'string' && message.trim() ? message : fallback;
}

async function loadSettings() {
  loading.value = true;
  settingsError.value = '';
  try {
    const res = await client.get('/admin/settings');
    const data = res.data?.data || {};

    form.resend_api_key = data.resend_api_key || '';
    form.mail_host = data.mail_host || 'smtp.resend.com';
    form.mail_port = data.mail_port || '587';
    form.mail_username = data.mail_username || 'resend';
    form.mail_password = data.mail_password || '';
    form.brevo_api_key = data.brevo_api_key || '';
    form.mail_from = data.mail_from || '';
    form.mail_from_name = data.mail_from_name || 'CandyCutz';
    form.mail_confirmed_subject = data.mail_confirmed_subject || '';
    form.mail_reminder_subject = data.mail_reminder_subject || '';
    form.mail_receipt_subject = data.mail_receipt_subject || '';
    form.mail_cancellation_subject = data.mail_cancellation_subject || '';
    form.mail_custom_note = data.mail_custom_note || '';
    if (data.mail_show_maps !== undefined) form.mail_show_maps = data.mail_show_maps === '1' || data.mail_show_maps === true;
    if (data.mail_show_calendar !== undefined) form.mail_show_calendar = data.mail_show_calendar === '1' || data.mail_show_calendar === true;
    if (data.mail_show_whatsapp !== undefined) form.mail_show_whatsapp = data.mail_show_whatsapp === '1' || data.mail_show_whatsapp === true;
    if (data.mail_show_qr !== undefined) form.mail_show_qr = data.mail_show_qr === '1' || data.mail_show_qr === true;
    savedMailConfig.value = {
      apiKey: form.resend_api_key,
      sender: form.mail_from
    };
    settingsLoaded.value = true;
  } catch (error) {
    console.error('[ResendSetup] Failed to load settings:', error);
    settingsError.value = apiErrorMessage(error, 'Could not load email settings from the API.');
    toast.error(settingsError.value);
  } finally {
    loading.value = false;
  }
}

async function loadDeliveries() {
  loadingDeliveries.value = true;
  deliveryError.value = '';
  statsError.value = '';
  try {
    const res = await client.get('/admin/notifications/deliveries', {
      params: { channel: 'email', per_page: 50 }
    });
    const payload = res.data?.data;
    const rows = Array.isArray(payload) ? payload : payload?.data;
    if (!Array.isArray(rows)) throw new Error('The delivery-log response did not contain a records list.');
    deliveries.value = rows;
  } catch (error) {
    console.error('[ResendSetup] Failed to load email delivery logs:', error);
    deliveryError.value = apiErrorMessage(error, 'Could not load email delivery records from the API.');
  }

  try {
    const statsRes = await client.get('/admin/notifications/analytics');
    const rows = statsRes.data?.data?.deliveries_by_channel;
    if (!Array.isArray(rows)) throw new Error('The analytics response did not contain delivery statistics.');
    const emailRows = rows.filter(row => row.channel === 'email');
    emailStats.total = emailRows.reduce((total, row) => total + (Number(row.total) || 0), 0);
    emailStats.failed = emailRows
      .filter(row => ['failed', 'exhausted'].includes(String(row.status).toLowerCase()))
      .reduce((total, row) => total + (Number(row.total) || 0), 0);
  } catch (error) {
    console.error('[ResendSetup] Failed to load email delivery statistics:', error);
    statsError.value = apiErrorMessage(error, 'Could not load email delivery statistics from the API.');
  } finally {
    loadingDeliveries.value = false;
  }
}

async function saveSettings() {
  saving.value = true;
  saveMessage.value = '';
  saveError.value = '';
  try {
    await client.post('/admin/settings', {
      settings: {
        resend_api_key: form.resend_api_key,
        mail_host: form.mail_host,
        mail_port: form.mail_port,
        mail_username: form.mail_username,
        mail_password: form.mail_password || form.resend_api_key,
        brevo_api_key: form.brevo_api_key,
        mail_from: form.mail_from,
        mail_from_name: form.mail_from_name,
        mail_confirmed_subject: form.mail_confirmed_subject,
        mail_reminder_subject: form.mail_reminder_subject,
        mail_receipt_subject: form.mail_receipt_subject,
        mail_cancellation_subject: form.mail_cancellation_subject,
        mail_custom_note: form.mail_custom_note,
        mail_show_maps: form.mail_show_maps ? '1' : '0',
        mail_show_calendar: form.mail_show_calendar ? '1' : '0',
        mail_show_whatsapp: form.mail_show_whatsapp ? '1' : '0',
        mail_show_qr: form.mail_show_qr ? '1' : '0'
      }
    });

    saveMessage.value = 'Settings saved. This confirms persistence only, not Resend delivery or sender-domain verification.';
    toast.success('Resend email settings saved. Run a test dispatch to verify the provider response.');
    await loadSettings();
  } catch (error) {
    console.error('[ResendSetup] Failed to save settings:', error);
    saveError.value = apiErrorMessage(error, 'Failed to save email settings.');
    toast.error(saveError.value);
  } finally {
    saving.value = false;
  }
}

async function sendTestEmail(recipientEmail) {
  if (!recipientEmail) {
    toast.error('Please specify a valid test recipient email address.');
    return;
  }

  if (configDirty.value) {
    const message = 'Save the current Resend API key and sender address before testing. The backend test uses saved settings.';
    testResult.value = { success: false, message };
    toast.error(message);
    return;
  }

  testing.value = true;
  testResult.value = null;

  try {
    const res = await client.post('/admin/test-email', {
      to: recipientEmail
    });

    testResult.value = {
      success: true,
      message: res.data?.message || 'The mail transport accepted the test dispatch.',
      data: res.data?.data
    };
    toast.success(`Test dispatch accepted for ${recipientEmail}. Check the recipient inbox and Resend events.`);
    await loadDeliveries();
  } catch (error) {
    const errorMsg = apiErrorMessage(error, 'Test dispatch failed. Check the API key, sender-domain verification, and backend logs.');

    testResult.value = {
      success: false,
      message: errorMsg,
      code: error.response?.data?.error?.code,
      httpStatus: error.response?.status,
      details: error.response?.data?.error?.details
    };
    toast.error(errorMsg);
  } finally {
    testing.value = false;
  }
}

async function testResendConnection() {
  if (apiKeyDirty.value) {
    const message = 'Save the current Resend API key before checking the connection. The check uses the saved key.';
    connectionResult.value = { success: false, message };
    toast.error(message);
    return;
  }

  testingConnection.value = true;
  connectionResult.value = null;

  try {
    const res = await client.post('/admin/resend/test-connection');
    const data = res.data?.data || {};
    const verifiedDomainCount = Number(data.verified_domain_count) || 0;
    const domainCount = Number(data.domain_count) || 0;

    connectionResult.value = {
      success: true,
      message: res.data?.message || 'Resend API connection successful. No email was sent.',
      verifiedDomainCount,
      domainCount,
      domains: Array.isArray(data.domains) ? data.domains : []
    };

    toast.success(verifiedDomainCount > 0
      ? 'Resend API connection succeeded. No email was sent.'
      : 'Resend API connection succeeded, but no verified sending domain was found.');
  } catch (error) {
    const errorMsg = apiErrorMessage(error, 'Connection test failed. Check the saved API key and Resend API permissions.');
    connectionResult.value = {
      success: false,
      message: errorMsg,
      code: error.response?.data?.error?.code,
      httpStatus: error.response?.status
    };
    toast.error(errorMsg);
  } finally {
    testingConnection.value = false;
  }
}

function refreshAll() {
  loadSettings();
  loadDeliveries();
}

onMounted(() => {
  if (route.query?.tab && ['config', 'templates', 'guide', 'deliveries'].includes(route.query.tab)) {
    currentTab.value = route.query.tab;
  }
  loadSettings();
  loadDeliveries();
});
</script>
