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

      <!-- Tab 1: Credentials & Configuration -->
      <div v-if="currentTab === 'config'">
        <ResendConfigForm
          :form="form"
          :is-configured="isConfigured"
          :saving="saving"
          :testing="testing"
          :test-result="testResult"
          @save="saveSettings"
          @test-dispatch="sendTestEmail"
        />
      </div>

      <!-- Tab 2: Step-by-Step Setup Guide -->
      <div v-else-if="currentTab === 'guide'">
        <ResendGuide />
      </div>

      <!-- Tab 3: Recent Email Deliveries & Audit Log -->
      <div v-else-if="currentTab === 'deliveries'">
        <ResendDeliveryLogs
          :deliveries="deliveries"
          :loading="loadingDeliveries"
          @refresh="loadDeliveries"
        />
      </div>
    </div>
  </component>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
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

const route = useRoute();
const toast = useToast();

const layoutComponent = computed(() => {
  return route.path.startsWith('/superadmin') ? SuperAdminLayout : AdminLayout;
});

const currentTab = ref('config');
const loading = ref(false);
const saving = ref(false);
const testing = ref(false);
const loadingDeliveries = ref(false);

const form = reactive({
  resend_api_key: '',
  mail_host: 'smtp.resend.com',
  mail_port: '587',
  mail_username: 'resend',
  mail_password: '',
  brevo_api_key: '',
  mail_from: '',
  mail_from_name: 'CandyCutz'
});

const testResult = ref(null);
const deliveries = ref([]);
const emailStats = reactive({
  total: 0,
  failed: 0
});

const isConfigured = computed(() => {
  return Boolean(
    (form.resend_api_key?.trim() || form.mail_password?.trim() || form.mail_host?.trim()) &&
    form.mail_from?.trim()
  );
});

async function loadSettings() {
  loading.value = true;
  try {
    const res = await client.get('/admin/settings');
    const data = res.data?.data || {};

    form.resend_api_key = data.resend_api_key || data.brevo_api_key || '';
    form.mail_host = data.mail_host || 'smtp.resend.com';
    form.mail_port = data.mail_port || '587';
    form.mail_username = data.mail_username || 'resend';
    form.mail_password = data.mail_password || '';
    form.brevo_api_key = data.brevo_api_key || '';
    form.mail_from = data.mail_from || 'no-reply@mail.candycutz.com';
    form.mail_from_name = data.mail_from_name || 'CandyCutz';
  } catch (error) {
    console.error('[ResendSetup] Failed to load settings:', error);
    toast.error('Failed to load email gateway settings from database.');
  } finally {
    loading.value = false;
  }
}

async function loadDeliveries() {
  loadingDeliveries.value = true;
  try {
    const res = await client.get('/admin/notification-deliveries', {
      params: { channel: 'email', limit: 50 }
    });
    deliveries.value = res.data?.data || [];

    const statsRes = await client.get('/admin/notification-deliveries/stats');
    if (statsRes.data?.data) {
      emailStats.total = statsRes.data.data.email_total || 0;
      emailStats.failed = statsRes.data.data.email_failed || 0;
    }
  } catch (error) {
    console.error('[ResendSetup] Failed to load email delivery logs:', error);
  } finally {
    loadingDeliveries.value = false;
  }
}

async function saveSettings() {
  saving.value = true;
  try {
    await client.put('/admin/settings', {
      settings: {
        resend_api_key: form.resend_api_key,
        mail_host: form.mail_host,
        mail_port: form.mail_port,
        mail_username: form.mail_username,
        mail_password: form.mail_password || form.resend_api_key,
        brevo_api_key: form.brevo_api_key,
        mail_from: form.mail_from,
        mail_from_name: form.mail_from_name
      }
    });

    toast.success('Resend transactional email configuration saved successfully.');
    await loadSettings();
  } catch (error) {
    console.error('[ResendSetup] Failed to save settings:', error);
    toast.error(error.response?.data?.message || 'Failed to save email settings.');
  } finally {
    saving.value = false;
  }
}

async function sendTestEmail(recipientEmail) {
  if (!recipientEmail) {
    toast.error('Please specify a valid test recipient email address.');
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
      message: res.data?.message || 'Test email dispatched successfully via Resend!'
    };
    toast.success(`Test email sent to ${recipientEmail}`);
    await loadDeliveries();
  } catch (error) {
    const errorMsg =
      error.response?.data?.error?.message ||
      error.response?.data?.message ||
      'Failed to send test email. Please check your Resend API credentials, sender address, and domain verification.';

    testResult.value = {
      success: false,
      message: errorMsg
    };
    toast.error(errorMsg);
  } finally {
    testing.value = false;
  }
}

function refreshAll() {
  loadSettings();
  loadDeliveries();
}

onMounted(() => {
  loadSettings();
  loadDeliveries();
});
</script>
