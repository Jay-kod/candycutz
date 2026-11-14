<template>
  <component :is="layout">
    <section class="space-y-8 pb-16 animate-fade-in">
      <header :class="['relative overflow-hidden rounded-3xl border p-8 lg:p-12 shadow-2xl', bannerBgClass]">
        <div :class="['absolute -right-16 -top-16 h-64 w-64 rounded-full blur-3xl', bannerOrbClass]"></div>
        <div :class="['absolute -left-16 -bottom-16 h-48 w-48 rounded-full blur-3xl', bannerOrbClass]"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
          <div>
            <p :class="['text-xs uppercase tracking-[0.3em] font-bold', bannerAccentText]">General &amp; Business</p>
            <h1 class="mt-2 font-display text-4xl lg:text-5xl text-theme-text drop-shadow-lg">
              <span :class="['text-transparent bg-clip-text bg-gradient-to-r', bannerGradientText]">System Configuration</span>
            </h1>
            <p class="mt-3 text-sm lg:text-base text-theme-text-muted max-w-2xl">
              Manage general website and business settings across the platform.
            </p>
          </div>

          <button
            @click="saveAll"
            :disabled="saving || loading"
            :class="['flex items-center justify-center gap-2 rounded-xl px-7 py-3 text-sm font-bold transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed', saveBtnClass]"
          >
            <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
            <CheckIcon v-else class="h-5 w-5" />
            {{ saving ? 'Saving Changes...' : 'Save Changes' }}
          </button>
        </div>
      </header>

      <div v-if="loading" class="flex justify-center py-32">
        <div :class="['h-10 w-10 animate-spin rounded-full border-4', spinnerClass]"></div>
      </div>

      <div v-else class="space-y-8 animate-slide-up">
        <div class="rounded-3xl border border-theme-border bg-theme-surface p-8 shadow-xl">
          <div class="flex items-center justify-between pb-6 border-b border-theme-border mb-6">
            <div>
              <p class="text-xs uppercase tracking-[0.3em] text-theme-muted font-bold">General &amp; Business</p>
              <h2 :class="['font-display text-2xl mt-1', configHeadingClass]">System Configuration</h2>
            </div>
          </div>

          <div class="grid gap-6 lg:grid-cols-2">
            <article
              v-for="(groupSettings, groupName) in systemSettingsGroups"
              :key="groupName"
              class="rounded-2xl border border-theme-border bg-theme-bg/50 p-6 space-y-4"
            >
              <h3 :class="['font-display text-xl capitalize', configHeadingClass]">{{ groupName }} Settings</h3>
              <div class="space-y-3">
                <div v-for="(val, key) in groupSettings" :key="key" class="space-y-1">
                  <label class="text-xs font-semibold text-theme-muted uppercase tracking-wider">{{ key }}</label>
                  <input
                    type="text"
                    v-model="groupSettings[key]"
                    :class="['w-full rounded-xl border border-theme-border bg-theme-surface px-4 py-2.5 text-sm text-theme-text focus:outline-none transition-colors', inputFocusClass]"
                  />
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>
  </component>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useTheme } from '@/core/composables/useTheme';
import { CheckIcon } from '@heroicons/vue/24/outline';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import SuperAdminLayout from '@/portals/superadmin/layouts/SuperAdminLayout.vue';
import { adminApi } from '@/shared/api/old_adminApi';
import { superadminApi } from '@/shared/api/old_superadminApi';
import { useToast } from '@/core/composables/useToast';

const route = useRoute();
const toast = useToast();
const { isDark } = useTheme();

const isSuperAdmin = computed(() => !route.path.startsWith('/admin'));
const layout = computed(() => route.path.startsWith('/admin') ? AdminLayout : SuperAdminLayout);
const currentApi = computed(() => route.path.startsWith('/admin') ? adminApi : superadminApi);

const bannerBgClass = computed(() => {
  if (isSuperAdmin.value) {
    return isDark.value
      ? 'bg-gradient-to-br from-red-950/60 via-black to-black border-red-500/20'
      : 'bg-gradient-to-br from-emerald-50 via-white to-white border-emerald-500/20';
  }
  return 'border-admin/20 bg-gradient-to-br from-theme-surface via-theme-card to-theme-surface';
});

const bannerOrbClass = computed(() => isSuperAdmin.value
  ? (isDark.value ? 'bg-red-500/10' : 'bg-emerald-500/10')
  : 'bg-admin/10');
const bannerAccentText = computed(() => isSuperAdmin.value
  ? (isDark.value ? 'text-red-400' : 'text-emerald-700')
  : 'text-admin');
const bannerGradientText = computed(() => isSuperAdmin.value
  ? (isDark.value ? 'from-red-400 to-red-600' : 'from-emerald-600 to-green-700')
  : 'from-admin to-admin-light');
const saveBtnClass = computed(() => {
  if (isSuperAdmin.value) {
    return isDark.value
      ? 'bg-gradient-to-r from-red-600 to-red-500 text-white hover:shadow-[0_4px_25px_rgba(239,68,68,0.4)]'
      : 'bg-gradient-to-r from-emerald-600 to-green-600 text-white hover:shadow-[0_4px_25px_rgba(16,185,129,0.3)]';
  }
  return 'bg-gradient-to-r from-admin to-admin-light text-obsidian hover:shadow-[0_4px_25px_rgba(255,103,0,0.4)]';
});
const configHeadingClass = computed(() => isSuperAdmin.value
  ? (isDark.value ? 'text-red-400' : 'text-emerald-700')
  : 'text-gold');
const inputFocusClass = computed(() => isSuperAdmin.value
  ? (isDark.value ? 'focus:border-red-500' : 'focus:border-emerald-600')
  : 'focus:border-gold');
const spinnerClass = computed(() => isSuperAdmin.value
  ? (isDark.value ? 'border-red-500/30 border-t-red-500' : 'border-emerald-500/30 border-t-emerald-600')
  : 'border-admin/30 border-t-admin');

const loading = ref(true);
const saving = ref(false);
const rawSettings = ref({});

const systemSettingsGroups = computed(() => {
  const groups = {};
  const entries = Object.entries(rawSettings.value || {});
  
  // Check if rawSettings contains nested group objects
  const hasGroupObjects = entries.some(
    ([, v]) => typeof v === 'object' && v !== null && !Array.isArray(v)
  );

  if (hasGroupObjects) {
    for (const [group, values] of entries) {
      if (group === 'mobile') continue;
      if (typeof values === 'object' && values !== null && !Array.isArray(values)) {
        groups[group] = { ...values };
      }
    }
  } else {
    // Flat map: infer group for each key
    for (const [key, value] of entries) {
      if (key === 'mobile' || typeof value === 'object') continue;
      let group = 'general';
      if (key.startsWith('hero_') || key.startsWith('cta_') || key.startsWith('page_hero_')) {
        group = 'hero';
      } else if (key.startsWith('about_')) {
        group = 'about';
      } else if (key.startsWith('contact_') || ['phone', 'email', 'address', 'city', 'state', 'latitude', 'longitude', 'map_link'].includes(key)) {
        group = 'contact';
      } else if (key.startsWith('bank_') || key.startsWith('manual_transfer_') || ['bank_name', 'account_name', 'account_number'].includes(key)) {
        group = 'payment';
      } else if (key.startsWith('booking_') || ['deposit_amount', 'min_notice_hours', 'max_days_ahead', 'slot_interval_minutes'].includes(key)) {
        group = 'booking';
      } else if (key.startsWith('social_') || ['instagram', 'facebook', 'whatsapp', 'twitter', 'tiktok', 'youtube'].includes(key)) {
        group = 'social';
      }
      if (!groups[group]) groups[group] = {};
      groups[group][key] = value;
    }
  }
  return groups;
});

async function fetchSettings() {
  loading.value = true;
  try {
    const res = await currentApi.value.settings();
    rawSettings.value = res.data?.data || {};
  } catch (err) {
    console.error('Failed to load settings:', err);
    toast.error('Failed to load system settings');
  } finally {
    loading.value = false;
  }
}

async function saveAll() {
  saving.value = true;
  try {
    const formData = new FormData();
    for (const [, groupValues] of Object.entries(systemSettingsGroups.value)) {
      for (const [key, value] of Object.entries(groupValues)) {
        formData.append(`settings[${key}]`, value ?? '');
      }
    }

    if (systemSettingsGroups.value.payment?.bank_name) {
      formData.append('settings[manual_transfer_bank]', systemSettingsGroups.value.payment.bank_name);
    }
    if (systemSettingsGroups.value.payment?.account_name) {
      formData.append('settings[manual_transfer_account_name]', systemSettingsGroups.value.payment.account_name);
    }
    if (systemSettingsGroups.value.payment?.account_number) {
      formData.append('settings[manual_transfer_account_number]', systemSettingsGroups.value.payment.account_number);
    }

    await currentApi.value.updateSettings(formData);
    toast.success('System settings saved successfully!');
    await fetchSettings();
  } catch (err) {
    console.error('Failed to save settings:', err);
    toast.error('Failed to save settings. Please try again.');
  } finally {
    saving.value = false;
  }
}

onMounted(fetchSettings);
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.4s ease-out forwards;
}
.animate-slide-up {
  animation: slideUp 0.5s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(16px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
