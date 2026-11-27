<template>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    <!-- Left Column: Primary Credentials & Configuration Form (7 cols) -->
    <div class="lg:col-span-7 space-y-6">
      <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] overflow-hidden shadow-2xl">
        <!-- Form Header -->
        <div class="px-6 py-5 bg-slate-50 dark:bg-white/[0.02] border-b border-slate-200 dark:border-white/[0.06] flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-[var(--color-accent)]/15 border border-[var(--color-accent)]/30 flex items-center justify-center text-[var(--color-accent)]">
              <KeyIcon class="h-5 w-5" />
            </div>
            <div>
              <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Resend API Credentials</h2>
              <p class="text-xs text-slate-500 dark:text-white/50">Enter your Resend API Key and verified sender details.</p>
            </div>
          </div>
          <button
            type="button"
            @click="fillDefaults"
            class="text-[11px] font-semibold text-[var(--color-accent)] hover:underline cursor-pointer"
          >
            Reset Defaults
          </button>
        </div>

        <!-- Form Body -->
        <div class="p-6 sm:p-8 space-y-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <!-- Resend API Key -->
            <div class="space-y-1.5 sm:col-span-2">
              <div class="flex items-center justify-between">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-white/60">
                  Resend API Key <span class="text-rose-500">*</span>
                </label>
                <button
                  type="button"
                  @click="showApiKey = !showApiKey"
                  class="text-[11px] text-[var(--color-accent)] hover:underline cursor-pointer flex items-center gap-1"
                >
                  <EyeIcon v-if="!showApiKey" class="h-3.5 w-3.5" />
                  <EyeSlashIcon v-else class="h-3.5 w-3.5" />
                  <span>{{ showApiKey ? 'Hide Key' : 'Reveal Key' }}</span>
                </button>
              </div>
              <input
                v-model="form.resend_api_key"
                :type="showApiKey ? 'text' : 'password'"
                autocomplete="off"
                required
                class="w-full bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-white/25 focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-white/[0.06] focus:ring-2 focus:ring-[var(--color-accent)]/15 outline-none transition-all font-mono"
                placeholder="re_123456789_abcdefghijklmnopqrstuvwxyz"
              />
              <details class="text-[11px] text-slate-500 dark:text-white/40">
                <summary class="cursor-pointer">Key help</summary>
                <p class="mt-1">Create it in Resend Dashboard → API Keys. Resend keys start with <code>re_</code>.</p>
              </details>
            </div>

            <!-- From Sender Email -->
            <div class="space-y-1.5 sm:col-span-2">
              <div class="flex items-center justify-between">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-white/60">
                  From Sender Email <span class="text-rose-500">*</span>
                </label>
                <button
                  type="button"
                  @click="form.mail_from = 'onboarding@resend.dev'"
                  class="text-[10px] font-semibold text-[var(--color-accent)] hover:underline flex items-center gap-1 cursor-pointer"
                >
                  Use Test Sender (onboarding@resend.dev)
                </button>
              </div>
              <input
                v-model="form.mail_from"
                type="email"
                required
                class="w-full bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-white/25 focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-white/[0.06] focus:ring-2 focus:ring-[var(--color-accent)]/15 outline-none transition-all"
                placeholder="no-reply@mail.candycutz.com"
              />
              <details v-if="isPublicWebmailDomain(form.mail_from)" class="rounded-lg border border-amber-500/20 bg-amber-500/10 p-2.5 text-[11px] leading-relaxed text-amber-500 dark:text-amber-400">
                <summary class="cursor-pointer">Sender may be rejected — view guidance</summary>
                <p class="mt-2">
                  Resend rejects public email addresses (@gmail.com, @yahoo.com, etc.). To test without a custom domain, use <code>onboarding@resend.dev</code> (delivers to your Resend account email). For customer deliveries, verify your custom domain in Resend.
                </p>
              </details>
              <details v-else class="text-[11px] text-slate-500 dark:text-white/40">
                <summary class="cursor-pointer">Sender domain requirements</summary>
                <p class="mt-1">
                  The sender must match an authenticated domain configured in Resend, or use <code>onboarding@resend.dev</code> for testing.
                </p>
              </details>
            </div>

            <!-- From Sender Name -->
            <div class="space-y-1.5 sm:col-span-2">
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-white/60">
                From Sender Name
              </label>
              <input
                v-model="form.mail_from_name"
                type="text"
                class="w-full bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-white/25 focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-white/[0.06] focus:ring-2 focus:ring-[var(--color-accent)]/15 outline-none transition-all"
                placeholder="CandyCutz"
              />
              <details class="text-[11px] text-slate-500 dark:text-white/40">
                <summary class="cursor-pointer">Name display info</summary>
                <p class="mt-1">This is the friendly name shown in customer inboxes.</p>
              </details>
            </div>
          </div>

          <!-- Optional SMTP Settings Toggle -->
          <div class="pt-4 border-t border-slate-200 dark:border-white/[0.06]">
            <button
              type="button"
              @click="showSmtpAdvanced = !showSmtpAdvanced"
              class="text-xs font-semibold text-[var(--color-accent)] hover:underline flex items-center gap-1 cursor-pointer"
            >
              <span>{{ showSmtpAdvanced ? 'Hide SMTP Relay Fields' : 'Show SMTP Relay Fields (not used by Resend API)' }}</span>
            </button>

            <div v-if="showSmtpAdvanced" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="space-y-1 sm:col-span-2">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-white/60">SMTP Host</label>
                <input
                  v-model="form.mail_host"
                  type="text"
                  class="w-full bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] rounded-xl px-4 py-2.5 text-xs font-mono"
                  placeholder="smtp.resend.com"
                />
              </div>
              <div class="space-y-1">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-white/60">Port</label>
                <input
                  v-model="form.mail_port"
                  type="text"
                  class="w-full bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] rounded-xl px-4 py-2.5 text-xs font-mono"
                  placeholder="587"
                />
              </div>
              <div class="space-y-1">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-white/60">Login</label>
                <input
                  v-model="form.mail_username"
                  type="text"
                  class="w-full bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] rounded-xl px-4 py-2.5 text-xs font-mono"
                  placeholder="resend"
                />
              </div>
            </div>
          </div>

          <!-- Bottom Save Toolbar -->
          <div class="pt-6 border-t border-slate-200 dark:border-white/[0.06] flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-white/50">
              <ShieldCheckIcon class="h-4 w-4 text-emerald-400" />
              <span>Credentials encrypted &amp; stored securely in database settings.</span>
            </div>
            <button
              type="button"
              @click="$emit('save')"
              :disabled="saving"
              class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--color-accent)] to-amber-500 px-6 py-3 text-xs font-bold text-obsidian shadow-lg shadow-[var(--color-accent)]/20 hover:brightness-110 active:scale-95 transition-all disabled:opacity-50 cursor-pointer"
            >
              <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></span>
              <CheckCircleIcon v-else class="h-4 w-4" />
              <span>{{ saving ? 'Saving Changes...' : 'Save Configuration' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Connection Checks + Dispatch Tool + Quick Checklist (5 cols) -->
    <div class="lg:col-span-5 space-y-6">
      <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-7 shadow-2xl space-y-4">
        <div class="flex items-center gap-2.5">
          <div class="h-9 w-9 rounded-xl bg-sky-500/15 border border-sky-500/30 flex items-center justify-center text-sky-400">
            <ShieldCheckIcon class="h-4 w-4" />
          </div>
          <div>
            <h3 class="font-display text-sm font-bold text-slate-900 dark:text-white">Resend API Connection</h3>
            <p class="text-[11px] text-slate-500 dark:text-white/50">Validate the saved API key without sending an email.</p>
          </div>
        </div>

        <button
          type="button"
          @click="$emit('test-connection')"
          :disabled="testingConnection || !connectionTestReady || !hasResendApiKey"
          class="w-full flex items-center justify-center gap-2 rounded-xl border border-sky-500/30 bg-sky-500/10 px-5 py-3 text-xs font-bold text-sky-700 dark:text-sky-300 hover:bg-sky-500/20 transition-all disabled:opacity-40 cursor-pointer"
        >
          <ArrowPathIcon v-if="testingConnection" class="h-4 w-4 animate-spin" />
          <ShieldCheckIcon v-else class="h-4 w-4" />
          <span>{{ testingConnection ? 'Checking Resend API...' : 'Test Resend Connection' }}</span>
        </button>

        <p v-if="!connectionTestReady" class="text-[11px] text-amber-600 dark:text-amber-400">
          Save the current API key before testing. This check uses the saved key; unsaved sender edits do not affect it.
        </p>

        <div
          v-if="connectionResult"
          class="rounded-2xl p-4 border text-xs space-y-1"
          :class="connectionResult.success
            ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400'
            : 'bg-rose-500/10 border-rose-500/30 text-rose-400'"
        >
          <div class="flex items-center gap-2 font-bold">
            <CheckCircleIcon v-if="connectionResult.success" class="h-4 w-4 shrink-0" />
            <XCircleIcon v-else class="h-4 w-4 shrink-0" />
            <span>{{ connectionResult.success ? 'Resend API Connected' : 'Resend API Connection Failed' }}</span>
          </div>
          <p class="text-[11px] text-slate-600 dark:text-white/70 pl-6">{{ connectionResult.message }}</p>
          <p v-if="connectionResult.success" class="text-[11px] text-slate-600 dark:text-white/70 pl-6">
            {{ connectionResult.verifiedDomainCount }} verified sending domain(s) · {{ connectionResult.domainCount }} total domain(s)
          </p>
          <p v-if="connectionResult.httpStatus || connectionResult.code" class="text-[10px] font-mono pl-6">
            <span v-if="connectionResult.httpStatus">HTTP {{ connectionResult.httpStatus }}</span>
            <span v-if="connectionResult.httpStatus && connectionResult.code"> · </span>
            <span v-if="connectionResult.code">{{ connectionResult.code }}</span>
          </p>
          <details v-if="connectionResult.success && connectionResult.domains?.length" class="pl-6 pt-1 text-[11px] text-slate-600 dark:text-white/60">
            <summary class="cursor-pointer">Domain status</summary>
            <ul class="mt-1 space-y-1">
              <li v-for="domain in connectionResult.domains" :key="domain.name">
                {{ domain.name }} — {{ domain.status }}
              </li>
            </ul>
          </details>
          <p v-if="connectionResult.success && connectionResult.verifiedDomainCount === 0" class="text-[11px] text-amber-600 dark:text-amber-400 pl-6">
            The API key works, but Resend may restrict delivery until a sending domain is verified.
          </p>
        </div>
      </div>

      <!-- Live Test Email Dispatcher Card -->
      <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-7 shadow-2xl space-y-5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="h-9 w-9 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
              <PaperAirplaneIcon class="h-4 w-4" />
            </div>
            <div>
              <h3 class="font-display text-sm font-bold text-slate-900 dark:text-white">Live Email Dispatch Test</h3>
              <p class="text-[11px] text-slate-500 dark:text-white/50">Test the saved mail configuration with a real dispatch.</p>
            </div>
          </div>
          <span
            class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border"
            :class="isConfigured && configSaved
              ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
              : 'bg-amber-500/10 text-amber-400 border-amber-500/20'"
          >
            {{ !configSaved ? 'Save Before Test' : isConfigured ? 'Ready to Test' : 'Check Runtime Config' }}
          </span>
        </div>

        <details class="text-xs text-slate-600 dark:text-white/70">
          <summary class="cursor-pointer font-semibold">What this test confirms</summary>
          <p class="mt-2 leading-relaxed">
            It sends a real test email using saved backend settings. A successful response means the transport accepted the dispatch; it does not confirm inbox placement.
          </p>
        </details>
        <p v-if="!configSaved" class="text-[11px] text-amber-600 dark:text-amber-400">
          Save the current API key and sender address before testing. Unsaved form values are not used by the backend.
        </p>

        <div class="space-y-2">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-white/60">
            Recipient Test Email
          </label>
          <div class="relative">
            <input
              v-model="recipientEmail"
              type="email"
              placeholder="admin@candycutz.com"
              class="w-full bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-white/30 focus:border-[var(--color-accent)] outline-none"
            />
          </div>
        </div>

        <!-- Dispatch CTA -->
        <button
          type="button"
          @click="handleTestDispatch"
          :disabled="testing || !configSaved || !recipientEmail || !recipientEmail.includes('@')"
          class="w-full flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-5 py-3 text-xs font-bold text-white shadow-lg shadow-emerald-500/20 hover:brightness-110 active:scale-[0.99] transition-all disabled:opacity-40 cursor-pointer"
        >
          <ArrowPathIcon v-if="testing" class="h-4 w-4 animate-spin" />
          <PaperAirplaneIcon v-else class="h-4 w-4" />
          <span>{{ testing ? 'Connecting & Dispatching...' : 'Dispatch Test Email' }}</span>
        </button>

        <!-- Test Result Banner -->
        <div
          v-if="testResult"
          class="rounded-2xl p-4 border text-xs transition-all space-y-1"
          :class="testResult.success
            ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400'
            : 'bg-rose-500/10 border-rose-500/30 text-rose-400'"
        >
          <div class="flex items-center gap-2 font-bold">
            <CheckCircleIcon v-if="testResult.success" class="h-4 w-4 text-emerald-400 shrink-0" />
            <XCircleIcon v-else class="h-4 w-4 text-rose-400 shrink-0" />
            <span>{{ testResult.success ? 'Dispatch Accepted by Mail Transport' : 'Dispatch Failed' }}</span>
          </div>
          <p class="text-[11px] text-slate-600 dark:text-white/70 pl-6">
            {{ testResult.message }}
          </p>
          <p v-if="testResult.data?.sent_to" class="text-[10px] text-slate-500 dark:text-white/50 pl-6">
            Recipient: {{ testResult.data.sent_to }}
          </p>
        </div>
      </div>

      <!-- Quick Checklist Card -->
      <details class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] shadow-xl">
        <summary class="flex cursor-pointer list-none items-center gap-2 p-6 text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-white sm:p-7">
          <SparklesIcon class="h-4 w-4 text-[var(--color-accent)]" />
          <span>Resend Pre-Flight Checklist</span>
          <span class="ml-auto text-[10px] font-semibold text-[var(--color-accent)]">View / hide</span>
        </summary>

        <ul class="space-y-3 border-t border-slate-200 p-6 text-xs text-slate-600 dark:border-white/[0.06] dark:text-white/70 sm:p-7">
          <li class="flex items-start gap-2.5">
            <span
              class="h-4 w-4 rounded-full flex items-center justify-center shrink-0 mt-0.5 text-[10px] font-bold"
              :class="hasResendApiKey ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-200 dark:bg-white/10 text-slate-500'"
            >
              ✓
            </span>
            <span>Resend API key present (starts with <code>re_</code>); validity still requires a live test</span>
          </li>
          <li class="flex items-start gap-2.5">
            <span
              class="h-4 w-4 rounded-full flex items-center justify-center shrink-0 mt-0.5 text-[10px] font-bold"
              :class="form.mail_from ? 'bg-blue-500/20 text-blue-400' : 'bg-slate-200 dark:bg-white/10 text-slate-500'"
            >
              •
            </span>
            <span>Sender address entered; domain verification must be checked in Resend</span>
          </li>
          <li class="flex items-start gap-2.5">
            <span
              class="h-4 w-4 rounded-full flex items-center justify-center shrink-0 mt-0.5 text-[10px] font-bold"
              :class="form.mail_from_name ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-200 dark:bg-white/10 text-slate-500'"
            >
              ✓
            </span>
            <span>Display name configured for customer receipts</span>
          </li>
        </ul>

        <div class="mx-6 mb-6 border-t border-slate-200 pt-3 dark:border-white/[0.06] sm:mx-7 sm:mb-7">
          <a
            href="https://resend.com/api-keys"
            target="_blank"
            rel="noopener noreferrer"
            class="text-xs text-[var(--color-accent)] hover:underline inline-flex items-center gap-1 font-semibold"
          >
            <span>Open Resend API Keys Console</span>
            <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5" />
          </a>
        </div>
        </details>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import {
  KeyIcon,
  EyeIcon,
  EyeSlashIcon,
  ShieldCheckIcon,
  CheckCircleIcon,
  XCircleIcon,
  PaperAirplaneIcon,
  ArrowPathIcon,
  SparklesIcon,
  ArrowTopRightOnSquareIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  form: {
    type: Object,
    required: true
  },
  isConfigured: {
    type: Boolean,
    default: false
  },
  configSaved: {
    type: Boolean,
    default: false
  },
  connectionTestReady: {
    type: Boolean,
    default: false
  },
  saving: {
    type: Boolean,
    default: false
  },
  testing: {
    type: Boolean,
    default: false
  },
  testingConnection: {
    type: Boolean,
    default: false
  },
  connectionResult: {
    type: Object,
    default: null
  },
  testResult: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['save', 'test-connection', 'test-dispatch']);

const showApiKey = ref(false);
const showSmtpAdvanced = ref(false);
const recipientEmail = ref('');

const hasResendApiKey = computed(() => {
  const key = props.form.resend_api_key?.trim() || '';
  return (key.startsWith('re_') && key.length > 3) || key.startsWith('••••••••');
});

function fillDefaults() {
  if (!props.form.mail_from) {
    props.form.mail_from = 'no-reply@mail.candycutz.com';
  }
  if (!props.form.mail_from_name) {
    props.form.mail_from_name = 'CandyCutz';
  }
}

function handleTestDispatch() {
  if (!recipientEmail.value || !recipientEmail.value.includes('@')) {
    return;
  }
  emit('test-dispatch', recipientEmail.value.trim());
}

function isPublicWebmailDomain(email) {
  if (!email || typeof email !== 'string') return false;
  const lower = email.toLowerCase().trim();
  return (
    lower.endsWith('@gmail.com') ||
    lower.endsWith('@yahoo.com') ||
    lower.endsWith('@outlook.com') ||
    lower.endsWith('@hotmail.com') ||
    lower.endsWith('@icloud.com') ||
    lower.endsWith('@aol.com')
  );
}
</script>
