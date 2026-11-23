<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.24em] text-admin">Authentication</p>
          <h1 class="mt-1 text-2xl font-bold text-theme-text sm:text-3xl">Google Authentication</h1>
          <p class="mt-2 max-w-2xl text-sm text-theme-muted">
            Connect Google Sign-In and monitor whether customers use Google or the normal registration form.
          </p>
        </div>
        <button
          type="button"
          :disabled="loading"
          class="rounded-xl border border-theme-border bg-theme-surface px-4 py-2 text-xs font-bold text-theme-text"
          @click="loadPage"
        >
          {{ loading ? 'Refreshing...' : 'Refresh data' }}
        </button>
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-theme-border bg-theme-surface p-5">
          <p class="text-xs font-bold uppercase tracking-wider text-theme-muted">Google users</p>
          <p class="mt-2 text-3xl font-bold text-blue-400">{{ googleUsers.length }}</p>
        </div>
        <div class="rounded-2xl border border-theme-border bg-theme-surface p-5">
          <p class="text-xs font-bold uppercase tracking-wider text-theme-muted">Normal registration</p>
          <p class="mt-2 text-3xl font-bold text-theme-text">{{ localUsers.length }}</p>
        </div>
        <div class="rounded-2xl border border-theme-border bg-theme-surface p-5">
          <p class="text-xs font-bold uppercase tracking-wider text-theme-muted">Connection status</p>
          <p class="mt-2 flex items-center gap-2 text-sm font-bold" :class="connectionStatusClass">
            <span class="h-2 w-2 rounded-full" :class="connectionDotClass"></span>
            {{ connectionStatusLabel }}
          </p>
        </div>
      </div>

      <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
        <section class="rounded-2xl border border-theme-border bg-theme-surface p-5 sm:p-6">
          <div class="flex items-center justify-between gap-4 border-b border-theme-border pb-5">
            <div>
              <h2 class="text-lg font-bold text-theme-text">Connection settings</h2>
              <p class="mt-1 text-xs text-theme-muted">Enter the OAuth 2.0 Web Client ID from Google Cloud Console.</p>
            </div>
            <span
              class="rounded-full border px-3 py-1 text-[10px] font-bold uppercase tracking-wider"
              :class="connectionBadgeClass"
            >
              {{ connectionBadgeLabel }}
            </span>
          </div>

          <div class="mt-6">
            <label for="google-client-id" class="mb-2 block text-xs font-bold uppercase tracking-wider text-theme-muted">
              Google Web Client ID
            </label>
            <input
              id="google-client-id"
              v-model="googleClientId"
              type="text"
              class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 font-mono text-sm text-theme-text outline-none focus:border-admin"
              placeholder="1234567890-abcdef.apps.googleusercontent.com"
            />
            <p class="mt-2 text-xs text-theme-muted">
              The client ID is public. Do not enter the Google client secret in this page.
            </p>
          </div>

          <div class="mt-5 flex flex-wrap gap-3">
            <button
              type="button"
              :disabled="saving"
              class="rounded-xl bg-admin px-5 py-3 text-sm font-bold text-obsidian disabled:opacity-60"
              @click="saveConnection"
            >
              {{ saving ? 'Saving...' : 'Save Google connection' }}
            </button>
            <button
              type="button"
              :disabled="testing || !googleConfigured"
              class="rounded-xl border border-blue-500/30 bg-blue-500/10 px-5 py-3 text-sm font-bold text-blue-300 disabled:cursor-not-allowed disabled:opacity-50"
              @click="testConnection"
            >
              {{ testing ? 'Testing connection...' : 'Test connection' }}
            </button>
          </div>

          <div
            v-if="testResult"
            class="mt-5 rounded-xl border p-4"
            :class="testResult.connected
              ? 'border-green-500/30 bg-green-500/[0.08]'
              : 'border-red-500/30 bg-red-500/[0.08]'"
          >
            <div class="flex items-start gap-3">
              <span
                class="mt-0.5 h-2.5 w-2.5 shrink-0 rounded-full"
                :class="testResult.connected ? 'bg-green-400' : 'bg-red-400'"
              ></span>
              <div>
                <p class="text-sm font-bold" :class="testResult.connected ? 'text-green-400' : 'text-red-400'">
                  {{ testResult.connected ? 'Connection active and running' : 'Connection test failed' }}
                </p>
                <p class="mt-1 text-xs text-theme-muted">{{ testResult.message }}</p>
                <p v-if="testResult.tested_at" class="mt-2 text-[10px] uppercase tracking-wider text-theme-muted">
                  Tested {{ testResult.tested_at }}
                </p>
              </div>
            </div>
          </div>
        </section>

        <aside class="rounded-2xl border border-blue-500/20 bg-blue-500/[0.05] p-5 sm:p-6">
          <h2 class="text-lg font-bold text-theme-text">Google Cloud setup</h2>
          <ol class="mt-5 space-y-4 text-sm text-theme-muted">
            <li class="flex gap-3">
              <span class="step-number">1</span>
              <span>Open <strong class="text-theme-text">console.cloud.google.com</strong> and create or select a project.</span>
            </li>
            <li class="flex gap-3">
              <span class="step-number">2</span>
              <span>Go to <strong class="text-theme-text">APIs &amp; Services → OAuth consent screen</strong> and choose External.</span>
            </li>
            <li class="flex gap-3">
              <span class="step-number">3</span>
              <span>Add the app name, support email, and developer contact. Add your domain under Authorized domains.</span>
            </li>
            <li class="flex gap-3">
              <span class="step-number">4</span>
              <span>Open <strong class="text-theme-text">Credentials → Create credentials → OAuth client ID</strong>, then select Web application.</span>
            </li>
            <li class="flex gap-3">
              <span class="step-number">5</span>
              <span>
                Add every exact origin to <strong class="text-theme-text">Authorized JavaScript origins</strong>.
                Include <code>http://127.0.0.1:5173</code> for local testing and
                <code>{{ windowLocationOrigin }}</code> for the current ngrok or production domain.
              </span>
            </li>
            <li class="flex gap-3">
              <span class="step-number">6</span>
              <span>Copy the generated Web Client ID into the field on the left, then save it.</span>
            </li>
          </ol>
        </aside>
      </div>

      <section class="overflow-hidden rounded-2xl border border-theme-border bg-theme-surface">
        <div class="flex flex-col gap-3 border-b border-theme-border p-5 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 class="text-lg font-bold text-theme-text">Authentication usage</h2>
            <p class="mt-1 text-xs text-theme-muted">Customers classified by their registration method.</p>
          </div>
          <div class="flex rounded-xl border border-theme-border p-1">
            <button
              v-for="filter in usageFilters"
              :key="filter.value"
              type="button"
              class="rounded-lg px-3 py-2 text-xs font-bold"
              :class="activeFilter === filter.value ? 'bg-admin text-obsidian' : 'text-theme-muted'"
              @click="activeFilter = filter.value"
            >
              {{ filter.label }}
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="border-b border-theme-border text-[10px] uppercase tracking-wider text-theme-muted">
                <th class="px-5 py-3">Customer</th>
                <th class="px-5 py-3">Email</th>
                <th class="px-5 py-3">Authentication</th>
                <th class="px-5 py-3">Joined</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="customer in filteredUsers" :key="customer.id" class="border-b border-theme-border/60 last:border-0">
                <td class="px-5 py-4 text-sm font-semibold text-theme-text">{{ customer.name || 'Unnamed customer' }}</td>
                <td class="px-5 py-4 text-sm text-theme-muted">{{ customer.email || '—' }}</td>
                <td class="px-5 py-4">
                  <span
                    class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase"
                    :class="providerLabel(customer).className"
                  >
                    {{ providerLabel(customer).label }}
                  </span>
                </td>
                <td class="px-5 py-4 text-xs text-theme-muted">{{ formatDate(customer.created_at) }}</td>
              </tr>
              <tr v-if="!filteredUsers.length">
                <td colspan="4" class="px-5 py-10 text-center text-sm text-theme-muted">No customers match this filter.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

const toast = useToast();
const loading = ref(false);
const saving = ref(false);
const testing = ref(false);
const googleClientId = ref('');
const customers = ref([]);
const activeFilter = ref('all');
const testResult = ref(null);
const windowLocationOrigin = typeof window !== 'undefined' ? window.location.origin : '';

const usageFilters = [
  { label: 'All', value: 'all' },
  { label: 'Google', value: 'google' },
  { label: 'Normal', value: 'local' },
];

const googleConfigured = computed(() => Boolean(googleClientId.value.trim()));

const connectionStatusLabel = computed(() => {
  if (!googleConfigured.value) return 'Needs setup';
  if (testResult.value?.connected) return 'Active and running';
  if (testResult.value) return 'Test failed';
  return 'Configured, not tested';
});

const connectionStatusClass = computed(() => {
  if (!googleConfigured.value) return 'text-amber-400';
  if (testResult.value?.connected) return 'text-green-400';
  if (testResult.value) return 'text-red-400';
  return 'text-blue-300';
});

const connectionDotClass = computed(() => {
  if (!googleConfigured.value) return 'bg-amber-400';
  if (testResult.value?.connected) return 'bg-green-400';
  if (testResult.value) return 'bg-red-400';
  return 'bg-blue-400';
});

const connectionBadgeLabel = computed(() => {
  if (!googleConfigured.value) return 'Not connected';
  if (testResult.value?.connected) return 'Active';
  if (testResult.value) return 'Failed';
  return 'Configured';
});

const connectionBadgeClass = computed(() => {
  if (!googleConfigured.value) return 'border-amber-500/30 bg-amber-500/10 text-amber-400';
  if (testResult.value?.connected) return 'border-green-500/30 bg-green-500/10 text-green-400';
  if (testResult.value) return 'border-red-500/30 bg-red-500/10 text-red-400';
  return 'border-blue-500/30 bg-blue-500/10 text-blue-300';
});

const googleUsers = computed(() =>
  customers.value.filter(customer => String(customer.auth_provider || '').toLowerCase() === 'google')
);

const localUsers = computed(() =>
  customers.value.filter(customer => {
    const provider = String(customer.auth_provider || 'local').toLowerCase();
    return provider === 'local' || provider === '';
  })
);

const filteredUsers = computed(() => {
  if (activeFilter.value === 'google') return googleUsers.value;
  if (activeFilter.value === 'local') return localUsers.value;
  return customers.value;
});

function providerLabel(customer) {
  const provider = String(customer.auth_provider || 'local').toLowerCase();
  if (provider === 'google') {
    return { label: 'Google', className: 'bg-blue-500/10 text-blue-400 border border-blue-500/20' };
  }
  if (provider === 'apple') {
    return { label: 'Apple', className: 'bg-theme-text/10 text-theme-text border border-theme-border' };
  }
  return { label: 'Registration form', className: 'bg-theme-surface-subtle text-theme-muted border border-theme-border' };
}

function formatDate(value) {
  if (!value) return '—';
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? '—' : date.toLocaleDateString();
}

async function loadPage() {
  loading.value = true;
  try {
    const [settingsResponse, customersResponse] = await Promise.all([
      client.get('/admin/settings'),
      client.get('/admin/customers'),
    ]);
    const settings = settingsResponse.data?.data || settingsResponse.data || {};
    googleClientId.value = settings.google_client_id || '';
    customers.value = customersResponse.data?.data || [];
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load Google authentication data');
  } finally {
    loading.value = false;
  }
}

function loadGoogleIdentityScript() {
  return new Promise((resolve) => {
    if (window.google?.accounts?.id) {
      resolve(true);
      return;
    }

    const existing = document.querySelector('script[data-candycutz-google-identity]');
    if (existing) {
      existing.addEventListener('load', () => resolve(Boolean(window.google?.accounts?.id)), { once: true });
      existing.addEventListener('error', () => resolve(false), { once: true });
      return;
    }

    const script = document.createElement('script');
    script.src = 'https://accounts.google.com/gsi/client';
    script.async = true;
    script.defer = true;
    script.dataset.candycutzGoogleIdentity = 'true';
    script.onload = () => resolve(Boolean(window.google?.accounts?.id));
    script.onerror = () => resolve(false);
    document.head.appendChild(script);
  });
}

async function testConnection() {
  if (!googleConfigured.value) {
    toast.error('Save a Google Client ID before testing');
    return;
  }

  testing.value = true;
  testResult.value = null;

  try {
    const [browserReady, response] = await Promise.all([
      loadGoogleIdentityScript(),
      client.post('/admin/google-auth/test', { client_id: googleClientId.value.trim() }),
    ]);

    if (!browserReady) {
      testResult.value = {
        connected: false,
        message: 'The Google Identity Services script could not load in this browser.',
        tested_at: new Date().toLocaleString(),
      };
      toast.error('Google connection test failed');
      return;
    }

    const data = response.data?.data || response.data || {};
    testResult.value = {
      connected: data.connected === true,
      message: data.message || 'Google connection verified.',
      browser_ready: browserReady,
      tested_at: new Date().toLocaleString(),
    };
    toast.success('Google connection is active and running');
  } catch (error) {
    const data = error?.response?.data?.data;
    testResult.value = {
      connected: false,
      message: error?.response?.data?.error
        || error?.response?.data?.message
        || data?.message
        || 'Google connection test failed.',
      tested_at: new Date().toLocaleString(),
    };
    toast.error(testResult.value.message);
  } finally {
    testing.value = false;
  }
}

async function saveConnection() {
  const clientId = googleClientId.value.trim();
  if (!clientId) {
    toast.error('Enter a Google Web Client ID');
    return;
  }
  if (!clientId.endsWith('.apps.googleusercontent.com')) {
    toast.error('Enter a valid Google OAuth Web Client ID');
    return;
  }

  saving.value = true;
  try {
    await client.post('/admin/settings', {
      settings: {
        google_client_id: clientId,
        google_client_ids: clientId,
      },
    });
    toast.success('Google Authentication connection saved');
    await testConnection();
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to save Google Authentication');
  } finally {
    saving.value = false;
  }
}

onMounted(loadPage);
</script>

<style scoped>
.step-number {
  display: inline-flex;
  height: 1.75rem;
  width: 1.75rem;
  flex: 0 0 1.75rem;
  align-items: center;
  justify-content: center;
  border-radius: 9999px;
  border: 1px solid rgb(59 130 246 / 0.25);
  background: rgb(59 130 246 / 0.10);
  color: rgb(96 165 250);
  font-size: 0.75rem;
  font-weight: 800;
}
</style>
