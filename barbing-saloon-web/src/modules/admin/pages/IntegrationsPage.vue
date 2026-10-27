<template>
  <AdminLayout>
    <div class="space-y-6 pb-12">
      <!-- 1. Header with Live Digital Clock, Badges, Status Counter & Sync -->
      <IntegrationsHeader
        :configured-count="stats.configuredCount"
        :total-services="stats.totalServices"
        :is-refreshing="isRefreshing"
        @refresh="loadSettings(true)"
      />

      <!-- 2. Loading Shimmer Skeleton or Main Gateway Configuration Cards -->
      <IntegrationsSkeleton v-if="loading" />

      <template v-else>
        <!-- 3. High-Density KPI Status Cards -->
        <IntegrationsStats :stats="stats" />

        <!-- 4. Email / Brevo SMTP Card -->
        <EmailIntegrationCard
          :form="form"
          :mail-configured="mailConfigured"
          :saving="saving"
          :testing="testing"
          v-model:test-email="testEmail"
          :show-password="showSmtpPassword"
          @toggle-password="showSmtpPassword = !showSmtpPassword"
          @save="saveIntegrations('Email')"
          @test="sendTestEmail"
        />

        <!-- 5. Google OAuth Social Sign-In Card -->
        <GoogleIntegrationCard
          :form="form"
          :google-configured="googleConfigured"
          :saving="saving"
          @save="saveIntegrations('Google Sign-In')"
          @generate-android="generateGoogleAndroidDetails"
          @copy="copyValue"
        />

        <!-- 6. Apple Sign-In Identity Provider Card -->
        <AppleIntegrationCard
          :form="form"
          :apple-configured="appleConfigured"
          :saving="saving"
          @save="saveIntegrations('Apple Sign-In')"
        />
      </template>
    </div>
  </AdminLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import { useAdminIntegrations } from '../composables/useAdminIntegrations';
import IntegrationsHeader from '../components/integrations/IntegrationsHeader.vue';
import IntegrationsStats from '../components/integrations/IntegrationsStats.vue';
import EmailIntegrationCard from '../components/integrations/EmailIntegrationCard.vue';
import GoogleIntegrationCard from '../components/integrations/GoogleIntegrationCard.vue';
import AppleIntegrationCard from '../components/integrations/AppleIntegrationCard.vue';
import IntegrationsSkeleton from '../components/integrations/IntegrationsSkeleton.vue';

const {
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
} = useAdminIntegrations();

onMounted(() => {
  loadSettings();
});
</script>
