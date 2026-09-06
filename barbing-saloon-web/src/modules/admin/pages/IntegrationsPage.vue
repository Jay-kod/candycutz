<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in">
      <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/5 blur-3xl"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-admin/70 font-bold">Configuration</p>
          <h1 class="mt-2 font-display text-4xl text-theme-text drop-shadow-lg">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Integrations</span>
          </h1>
          <p class="mt-2 text-sm text-ivory/60">Connect your email service and social sign-in providers. Just paste your API keys.</p>
        </div>
      </div>

      <div v-if="flash" :class="['rounded-2xl border p-4 text-sm font-medium flex items-center gap-2', flashType === 'error' ? 'bg-red-500/10 border-red-500/30 text-red-400' : 'bg-green-500/10 border-green-500/30 text-green-400']">
        <span>{{ flash }}</span>
      </div>

      <!-- Email / SMTP -->
      <div class="rounded-2xl border border-white/10 bg-black/20 overflow-hidden">
        <div class="flex items-center justify-between gap-4 px-6 py-4 bg-white/[0.02] border-b border-white/5">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-admin/10 border border-admin/30 flex items-center justify-center">
              <EnvelopeIcon class="h-5 w-5 text-admin" />
            </div>
            <div>
              <h2 class="font-display text-lg text-theme-text">Email Notifications</h2>
              <p class="text-xs text-ivory/40">Brevo SMTP. Drives signup, login, and password-reset emails.</p>
            </div>
          </div>
          <span :class="['px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider', mailConfigured ? 'bg-green-500/15 text-green-400 border border-green-500/30' : 'bg-white/5 text-ivory/50 border border-white/10']">
            {{ mailConfigured ? 'Configured' : 'Not set up' }}
          </span>
        </div>
        <div class="p-6 space-y-4">
          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-semibold uppercase tracking-wider text-ivory/50 mb-1.5">SMTP Host</label>
              <input v-model="form.mail_host" class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-sm text-theme-text placeholder-ivory/30 focus:border-admin focus:ring-1 focus:ring-admin outline-none" placeholder="smtp-relay.brevo.com" />
            </div>
            <div>
              <label class="block text-[10px] font-semibold uppercase tracking-wider text-ivory/50 mb-1.5">SMTP Port</label>
              <input v-model="form.mail_port" class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-sm text-theme-text placeholder-ivory/30 focus:border-admin focus:ring-1 focus:ring-admin outline-none" placeholder="587" />
            </div>
            <div>
              <label class="block text-[10px] font-semibold uppercase tracking-wider text-ivory/50 mb-1.5">SMTP Username</label>
              <input v-model="form.mail_username" class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-sm text-theme-text placeholder-ivory/30 focus:border-admin focus:ring-1 focus:ring-admin outline-none" placeholder="your smtp username" />
            </div>
            <div>
              <label class="block text-[10px] font-semibold uppercase tracking-wider text-ivory/50 mb-1.5">SMTP Password</label>
              <input v-model="form.mail_password" type="password" class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-sm text-theme-text placeholder-ivory/30 focus:border-admin focus:ring-1 focus:ring-admin outline-none" placeholder="your smtp password" />
            </div>
            <div>
              <label class="block text-[10px] font-semibold uppercase tracking-wider text-ivory/50 mb-1.5">From Email</label>
              <input v-model="form.mail_from" class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-sm text-theme-text placeholder-ivory/30 focus:border-admin focus:ring-1 focus:ring-admin outline-none" placeholder="no-reply@yoursite.com" />
            </div>
            <div>
              <label class="block text-[10px] font-semibold uppercase tracking-wider text-ivory/50 mb-1.5">From Name</label>
              <input v-model="form.mail_from_name" class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-sm text-theme-text placeholder-ivory/30 focus:border-admin focus:ring-1 focus:ring-admin outline-none" placeholder="CandyCutz" />
            </div>
          </div>
          <div class="flex flex-wrap items-end gap-3 pt-2">
            <button @click="saveIntegrations" :disabled="saving" class="px-5 py-3 rounded-xl text-sm font-bold text-obsidian bg-gradient-to-r from-admin to-admin-light hover:shadow-[0_0_20px_rgba(255,103,0,0.3)] transition-all disabled:opacity-50 flex items-center gap-2">
              <svg v-if="saving" class="animate-spin h-4 w-4 text-obsidian" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
              {{ saving ? 'Saving...' : 'Save Email Settings' }}
            </button>
            <div class="flex items-center gap-3 ml-auto">
              <div class="flex items-center gap-2">
                <label class="text-xs text-ivory/50">Test to:</label>
                <input v-model="testEmail" type="email" placeholder="you@example.com" class="w-52 bg-black/20 border border-white/10 rounded-xl px-3 py-2.5 text-sm text-theme-text placeholder-ivory/30 focus:border-admin outline-none" />
              </div>
              <button @click="sendTestEmail" :disabled="testing || !mailConfigured" class="px-4 py-2.5 rounded-xl text-sm font-bold text-admin bg-admin/10 hover:bg-admin/20 border border-admin/30 transition-all disabled:opacity-40 flex items-center gap-2">
                <PaperAirplaneIcon v-if="!testing" class="h-4 w-4" />
                <svg v-else class="animate-spin h-4 w-4 text-admin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                {{ testing ? 'Sending...' : 'Send Test' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Social Sign-In -->
      <div class="rounded-2xl border border-white/10 bg-black/20 overflow-hidden">
        <div class="flex items-center justify-between gap-4 px-6 py-4 bg-white/[0.02] border-b border-white/5">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-admin/10 border border-admin/30 flex items-center justify-center">
              <GlobeAltIcon class="h-5 w-5 text-admin" />
            </div>
            <div>
              <h2 class="font-display text-lg text-theme-text">Social Sign-In</h2>
              <p class="text-xs text-ivory/40">Google and Apple buttons on the customer login/register pages.</p>
            </div>
          </div>
        </div>
        <div class="p-6 space-y-6">
          <div>
            <h3 class="text-sm font-bold text-ivory/70 mb-3">Google</h3>
            <div class="grid md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-ivory/50 mb-1.5">Google Client ID</label>
                <input v-model="form.google_client_id" class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-sm text-theme-text placeholder-ivory/30 focus:border-admin outline-none" placeholder="1234567890-abcdef.apps.googleusercontent.com" />
                <p class="text-[11px] text-ivory/40 mt-1.5">Create in Google Cloud Console: APIs &amp; Services → Credentials → OAuth 2.0 Client ID (Web).</p>
              </div>
            </div>
          </div>
          <div class="border-t border-white/5 pt-6">
            <h3 class="text-sm font-bold text-ivory/70 mb-3">Apple</h3>
            <div class="grid md:grid-cols-3 gap-4">
              <div class="md:col-span-3">
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-ivory/50 mb-1.5">Apple Services ID (Client ID)</label>
                <input v-model="form.apple_client_id" class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-sm text-theme-text placeholder-ivory/30 focus:border-admin outline-none" placeholder="com.candycutz.app" />
                <p class="text-[11px] text-ivory/40 mt-1.5">From Apple Developer → Certificates, IDs &amp; Profiles → Identifiers → Services IDs.</p>
              </div>
              <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-ivory/50 mb-1.5">Team ID</label>
                <input v-model="form.apple_team_id" class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-sm text-theme-text placeholder-ivory/30 focus:border-admin outline-none" placeholder="Your 10-char Team ID" />
              </div>
              <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-ivory/50 mb-1.5">Key ID</label>
                <input v-model="form.apple_key_id" class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-sm text-theme-text placeholder-ivory/30 focus:border-admin outline-none" placeholder="Your 10-char Key ID" />
              </div>
              <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-ivory/50 mb-1.5">App URL</label>
                <input v-model="form.app_url" class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-sm text-theme-text placeholder-ivory/30 focus:border-admin outline-none" placeholder="https://candycutz.app" />
              </div>
            </div>
          </div>
          <div class="flex pt-2">
            <button @click="saveIntegrations" :disabled="saving" class="px-5 py-3 rounded-xl text-sm font-bold text-obsidian bg-gradient-to-r from-admin to-admin-light hover:shadow-[0_0_20px_rgba(255,103,0,0.3)] transition-all disabled:opacity-50 flex items-center gap-2">
              <svg v-if="saving" class="animate-spin h-4 w-4 text-obsidian" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
              {{ saving ? 'Saving...' : 'Save Social Settings' }}
            </button>
          </div>
        </div>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';
import { EnvelopeIcon, GlobeAltIcon, PaperAirplaneIcon } from '@heroicons/vue/24/outline';

const form = reactive({
  mail_host: 'smtp-relay.brevo.com',
  mail_port: '587',
  mail_username: '',
  mail_password: '',
  mail_from: '',
  mail_from_name: 'CandyCutz',
  google_client_id: '',
  apple_client_id: '',
  apple_team_id: '',
  apple_key_id: '',
  app_url: '',
});

const saving = ref(false);
const testing = ref(false);
const testEmail = ref('');
const flash = ref('');
const flashType = ref('info');

const mailConfigured = computed(() => Boolean(form.mail_username) && Boolean(form.mail_from));

function setFlash(message, type = 'info') {
  flash.value = message;
  flashType.value = type;
  setTimeout(() => { flash.value = ''; }, 5000);
}

function applySettings(all) {
  const s = all || {};
  form.mail_host = s.mail_host || 'smtp-relay.brevo.com';
  form.mail_port = s.mail_port || '587';
  form.mail_username = s.mail_username || '';
  form.mail_password = s.mail_password || '';
  form.mail_from = s.mail_from || '';
  form.mail_from_name = s.mail_from_name || 'CandyCutz';
  form.google_client_id = s.google_client_id || '';
  form.apple_client_id = s.apple_client_id || '';
  form.apple_team_id = s.apple_team_id || '';
  form.apple_key_id = s.apple_key_id || '';
  form.app_url = s.app_url || '';
}

async function loadSettings() {
  try {
    const res = await adminApi.settings();
    applySettings(res.data.data || res.data);
  } catch (error) {
    setFlash('Could not load settings.', 'error');
  }
}

async function saveIntegrations() {
  saving.value = true;
  setFlash('');
  try {
    await adminApi.updateSettings({ settings: { ...form } });
    setFlash('Integration settings saved. Updates take effect immediately.');
  } catch (error) {
    setFlash(error?.response?.data?.error || 'Failed to save settings.', 'error');
  } finally {
    saving.value = false;
  }
}

async function sendTestEmail() {
  if (!testEmail.value) {
    setFlash('Enter a recipient email first.', 'error');
    return;
  }
  testing.value = true;
  setFlash('');
  try {
    const res = await adminApi.testEmail(testEmail.value);
    if (res.data.success) {
      setFlash(`Test email sent to ${testEmail.value}. Check your inbox.`);
    } else {
      setFlash(res.data.message || 'Test email failed.', 'error');
    }
  } catch (error) {
    setFlash(error?.response?.data?.message || error?.response?.data?.error || 'Test email failed.', 'error');
  } finally {
    testing.value = false;
  }
}

onMounted(loadSettings);
</script>