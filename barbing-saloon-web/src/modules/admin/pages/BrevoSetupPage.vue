<template>
  <component :is="layoutComponent">
    <div class="max-w-7xl mx-auto space-y-8 animate-fade-in pb-16 pt-2 px-4 sm:px-6 lg:px-8">
      <!-- Top Hero Banner with Navigation Tabs -->
      <BrevoHero
        v-model:currentTab="currentTab"
        :is-configured="isConfigured"
        :loading="loading"
        :saving="saving"
        @refresh="refreshAll"
        @save="saveSettings"
      />

      <!-- High-Density KPI Status Cards -->
      <BrevoStats
        :is-configured="isConfigured"
        :host-name="form.mail_host"
        :host-port="form.mail_port"
        :sender-email="form.mail_from"
        :sender-name="form.mail_from_name"
        :total-deliveries="emailStats.total"
        :failed-deliveries="emailStats.failed"
      />

      <!-- Tab 1: Credentials & SMTP Configuration -->
      <div v-if="currentTab === 'config'">
        <BrevoConfigForm
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
        <BrevoGuide />
      </div>

      <!-- Tab 3: Recent Email Deliveries & Audit Log -->
      <div v-else-if="currentTab === 'deliveries'">
        <BrevoDeliveryLogs
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

import BrevoHero from '../components/brevo/BrevoHero.vue';
import BrevoStats from '../components/brevo/BrevoStats.vue';
import BrevoConfigForm from '../components/brevo/BrevoConfigForm.vue';
import BrevoGuide from '../components/brevo/BrevoGuide.vue';
import BrevoDeliveryLogs from '../components/brevo/BrevoDeliveryLogs.vue';

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
    const data = res.data?.data || res.data || {};
    form.resend_api_key = data.resend_api_key || '';
    form.mail_host = data.mail_host || 'smtp.resend.com';
    form.mail_port = String(data.mail_port || '587');
    form.mail_username = data.mail_username || 'resend';
    form.mail_password = data.mail_password || '';
    form.brevo_api_key = data.brevo_api_key || '';
    form.mail_from = data.mail_from || data.mail_from_address || '';
    form.mail_from_name = data.mail_from_name || 'CandyCutz';
  } catch (error) {
    console.error('[ResendSetup] Failed to load settings:', error);
    toast.error('Failed to load email settings from server');
  } finally {
    loading.value = false;
  }
}

async function loadDeliveries() {
  loadingDeliveries.value = true;
  try {
    const res = await client.get('/admin/notifications/deliveries?channel=email&per_page=25');
    const payload = res.data?.data || res.data;
    deliveries.value = Array.isArray(payload) ? payload : (payload?.data || []);
    calculateEmailStats();
  } catch (error) {
    console.error('[BrevoSetup] Failed to load email delivery logs:', error);
  } finally {
    loadingDeliveries.value = false;
  }
}

async function loadAnalytics() {
  try {
    const res = await client.get('/admin/notifications/analytics');
    const analytics = res.data?.data || res.data;
    if (analytics?.deliveries_by_channel) {
      const emailRows = analytics.deliveries_by_channel.filter(c => c.channel === 'email');
      let total = 0;
      let failed = 0;
      emailRows.forEach(r => {
        const count = parseInt(r.total, 10) || 0;
        total += count;
        if (['failed', 'exhausted', 'suppressed'].includes(String(r.status).toLowerCase())) {
          failed += count;
        }
      });
      emailStats.total = total;
      emailStats.failed = failed;
    }
  } catch {
    calculateEmailStats();
  }
}

function calculateEmailStats() {
  if (deliveries.value.length && emailStats.total === 0) {
    emailStats.total = deliveries.value.length;
    emailStats.failed = deliveries.value.filter(d => ['failed', 'exhausted', 'suppressed'].includes(String(d.status).toLowerCase())).length;
  }
}

async function saveSettings() {
  saving.value = true;
  try {
    await client.post('/admin/settings', {
      settings: {
        resend_api_key: form.resend_api_key,
        mail_host: form.mail_host,
        mail_port: form.mail_port,
        mail_username: form.mail_username,
        mail_password: form.mail_password,
        brevo_api_key: form.brevo_api_key,
        mail_from: form.mail_from,
        mail_from_name: form.mail_from_name
      }
    });
    toast.success('Resend configuration saved successfully');
  } catch (error) {
    console.error('[ResendSetup] Failed to save settings:', error);
    const msg = error?.response?.data?.error || error?.response?.data?.message || 'Failed to save settings';
    toast.error(msg);
  } finally {
    saving.value = false;
  }
}

async function sendTestEmail(recipient) {
  if (!recipient || !recipient.includes('@')) {
    toast.error('Please enter a valid recipient email address');
    return;
  }

  testing.value = true;
  testResult.value = null;

  try {
    const res = await client.post('/admin/test-email', {
      to: recipient
    });

    if (res.data?.success !== false) {
      const now = new Date().toLocaleTimeString();
      testResult.value = {
        success: true,
        message: `Verification message successfully dispatched to ${recipient}. Please check your inbox (and spam folder).`,
        timestamp: now
      };
      toast.success(`Test email dispatched to ${recipient}!`);
      // Reload delivery logs to show the new dispatch record
      loadDeliveries();
    } else {
      testResult.value = {
        success: false,
        message: res.data?.message || 'Delivery rejected by mail server.',
        timestamp: new Date().toLocaleTimeString()
      };
      toast.error(res.data?.message || 'Test email failed');
    }
  } catch (error) {
    console.error('[BrevoSetup] Test email error:', error);
    const errorMsg = error?.response?.data?.message || error?.response?.data?.error || 'Test email failed to send';
    testResult.value = {
      success: false,
      message: errorMsg,
      timestamp: new Date().toLocaleTimeString()
    };
    toast.error(errorMsg);
  } finally {
    testing.value = false;
  }
}

async function refreshAll() {
  await Promise.all([
    loadSettings(),
    loadDeliveries(),
    loadAnalytics()
  ]);
  toast.success('Resend status refreshed');
}

onMounted(() => {
  loadSettings();
  loadDeliveries();
  loadAnalytics();
});
</script>
