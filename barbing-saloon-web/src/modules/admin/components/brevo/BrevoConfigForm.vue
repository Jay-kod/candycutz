<template>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    <!-- Left Column: Primary Credentials & SMTP Configuration Form (7 cols) -->
    <div class="lg:col-span-7 space-y-6">
      <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] overflow-hidden shadow-2xl">
        <!-- Form Header -->
        <div class="px-6 py-5 bg-slate-50 dark:bg-white/[0.02] border-b border-slate-200 dark:border-white/[0.06] flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-[var(--color-accent)]/15 border border-[var(--color-accent)]/30 flex items-center justify-center text-[var(--color-accent)]">
              <KeyIcon class="h-5 w-5" />
            </div>
            <div>
              <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">Brevo SMTP Credentials</h2>
              <p class="text-xs text-slate-500 dark:text-white/50">Enter the relay credentials from your Brevo account dashboard.</p>
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
            <!-- SMTP Host -->
            <div class="space-y-1.5 sm:col-span-2">
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-white/60">
                SMTP Relay Host <span class="text-rose-500">*</span>
              </label>
              <div class="relative">
                <input
                  v-model="form.mail_host"
                  type="text"
                  required
                  class="w-full bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-white/25 focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-white/[0.06] focus:ring-2 focus:ring-[var(--color-accent)]/15 outline-none transition-all font-mono"
                  placeholder="smtp-relay.brevo.com"
                />
              </div>
              <p class="text-[11px] text-slate-500 dark:text-white/40">
                Default Brevo relay: <code>smtp-relay.brevo.com</code>
              </p>
            </div>

            <!-- SMTP Port -->
            <div class="space-y-1.5">
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-white/60">
                Port <span class="text-rose-500">*</span>
              </label>
              <div class="flex items-center gap-2">
                <input
                  v-model="form.mail_port"
                  type="text"
                  required
                  class="w-full bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-white/25 focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-white/[0.06] focus:ring-2 focus:ring-[var(--color-accent)]/15 outline-none transition-all font-mono"
                  placeholder="587"
                />
                <button
                  type="button"
                  @click="form.mail_port = '587'"
                  class="px-2.5 py-3 rounded-xl border text-[10px] font-mono font-bold transition-all shrink-0 cursor-pointer"
                  :class="form.mail_port === '587'
                    ? 'bg-[var(--color-accent)]/15 text-[var(--color-accent)] border-[var(--color-accent)]/40'
                    : 'bg-slate-100 dark:bg-white/[0.04] text-slate-600 dark:text-white/60 border-slate-200 dark:border-white/10'"
                >
                  587 (TLS)
                </button>
                <button
                  type="button"
                  @click="form.mail_port = '465'"
                  class="px-2.5 py-3 rounded-xl border text-[10px] font-mono font-bold transition-all shrink-0 cursor-pointer"
                  :class="form.mail_port === '465'
                    ? 'bg-[var(--color-accent)]/15 text-[var(--color-accent)] border-[var(--color-accent)]/40'
                    : 'bg-slate-100 dark:bg-white/[0.04] text-slate-600 dark:text-white/60 border-slate-200 dark:border-white/10'"
                >
                  465 (SSL)
                </button>
              </div>
            </div>

            <!-- SMTP Login / Username -->
            <div class="space-y-1.5">
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-white/60">
                SMTP Login / Username <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.mail_username"
                type="text"
                autocomplete="username"
                required
                class="w-full bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-white/25 focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-white/[0.06] focus:ring-2 focus:ring-[var(--color-accent)]/15 outline-none transition-all font-mono"
                placeholder="e.g. 7f9b8c001@smtp-brevo.com"
              />
              <p class="text-[11px] text-slate-500 dark:text-white/40">
                Found on Brevo under SMTP &amp; API → SMTP.
              </p>
            </div>

            <!-- SMTP Master Key / Password -->
            <div class="space-y-1.5 sm:col-span-2">
              <div class="flex items-center justify-between">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-white/60">
                  SMTP Key / Password <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    @click="showSmtpKey = !showSmtpKey"
                    class="text-[11px] text-[var(--color-accent)] hover:underline cursor-pointer flex items-center gap-1"
                  >
                    <EyeIcon v-if="!showSmtpKey" class="h-3.5 w-3.5" />
                    <EyeSlashIcon v-else class="h-3.5 w-3.5" />
                    <span>{{ showSmtpKey ? 'Hide Key' : 'Reveal Key' }}</span>
                  </button>
                </div>
              </div>
              <input
                v-model="form.mail_password"
                :type="showSmtpKey ? 'text' : 'password'"
                autocomplete="new-password"
                required
                class="w-full bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-white/25 focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-white/[0.06] focus:ring-2 focus:ring-[var(--color-accent)]/15 outline-none transition-all font-mono"
                placeholder="xsmtpsib-..."
              />
              <p class="text-[11px] text-slate-500 dark:text-white/40">
                Generated via Brevo SMTP &amp; API → SMTP → Generate a new SMTP key. Never use your main account password.
              </p>
            </div>

            <!-- Optional Brevo REST API Key -->
            <div class="space-y-1.5 sm:col-span-2">
              <div class="flex items-center justify-between">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-white/60">
                  Brevo v3 REST API Key <span class="text-slate-400 dark:text-white/40 font-normal">(Optional)</span>
                </label>
                <button
                  type="button"
                  @click="showApiKey = !showApiKey"
                  class="text-[11px] text-[var(--color-accent)] hover:underline cursor-pointer flex items-center gap-1"
                >
                  <EyeIcon v-if="!showApiKey" class="h-3.5 w-3.5" />
                  <EyeSlashIcon v-else class="h-3.5 w-3.5" />
                  <span>{{ showApiKey ? 'Hide' : 'Reveal' }}</span>
                </button>
              </div>
              <input
                v-model="form.brevo_api_key"
                :type="showApiKey ? 'text' : 'password'"
                autocomplete="off"
                class="w-full bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-white/25 focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-white/[0.06] focus:ring-2 focus:ring-[var(--color-accent)]/15 outline-none transition-all font-mono"
                placeholder="xkeysib-..."
              />
              <p class="text-[11px] text-slate-500 dark:text-white/40">
                Enables high-throughput HTTP REST API fallback if SMTP port 587 is firewalled on your host.
              </p>
            </div>

            <!-- From Sender Email -->
            <div class="space-y-1.5">
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-white/60">
                From Sender Email <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.mail_from"
                type="email"
                required
                class="w-full bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-white/25 focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-white/[0.06] focus:ring-2 focus:ring-[var(--color-accent)]/15 outline-none transition-all"
                placeholder="no-reply@candycutz.com"
              />
              <p class="text-[11px] text-slate-500 dark:text-white/40">
                Must be an authenticated sender address inside your Brevo Senders console.
              </p>
            </div>

            <!-- From Sender Name -->
            <div class="space-y-1.5">
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-white/60">
                From Sender Name
              </label>
              <input
                v-model="form.mail_from_name"
                type="text"
                class="w-full bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-white/25 focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-white/[0.06] focus:ring-2 focus:ring-[var(--color-accent)]/15 outline-none transition-all"
                placeholder="CandyCutz"
              />
              <p class="text-[11px] text-slate-500 dark:text-white/40">
                The friendly display name shown in client inboxes.
              </p>
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

    <!-- Right Column: Live Connection Test & Dispatch Tool + Quick Checklist (5 cols) -->
    <div class="lg:col-span-5 space-y-6">
      <!-- Live Test Email Dispatcher Card -->
      <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-7 shadow-2xl space-y-5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="h-9 w-9 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
              <PaperAirplaneIcon class="h-4 w-4" />
            </div>
            <div>
              <h3 class="font-display text-sm font-bold text-slate-900 dark:text-white">Live Connection Test</h3>
              <p class="text-[11px] text-slate-500 dark:text-white/50">Verify end-to-end SMTP dispatch right now.</p>
            </div>
          </div>
          <span
            class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border"
            :class="isConfigured
              ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
              : 'bg-amber-500/10 text-amber-400 border-amber-500/20'"
          >
            {{ isConfigured ? 'Ready to Test' : 'Setup Required' }}
          </span>
        </div>

        <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed">
          Send an immediate verification email to any inbox to confirm that Brevo accepts your credentials and routes messages cleanly.
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
          :disabled="testing || !recipientEmail || !recipientEmail.includes('@')"
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
            <CheckCircleIcon v-if="testResult.success" class="h-4 w-4 shrink-0 text-emerald-400" />
            <XCircleIcon v-else class="h-4 w-4 shrink-0 text-rose-400" />
            <span>{{ testResult.success ? 'Dispatch Succeeded!' : 'Connection Error' }}</span>
          </div>
          <p class="text-[11px] leading-relaxed opacity-90 pl-6">
            {{ testResult.message }}
          </p>
          <div v-if="testResult.timestamp" class="text-[10px] text-white/40 pl-6 pt-1 font-mono">
            Tested at: {{ testResult.timestamp }}
          </div>
        </div>
      </div>

      <!-- Quick Setup Checklist Card -->
      <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-7 shadow-2xl space-y-4">
        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--color-accent)]">
          <SparklesIcon class="h-4 w-4" />
          <span>Brevo Setup Checklist</span>
        </div>
        <ul class="space-y-3 text-xs text-slate-600 dark:text-white/70">
          <li class="flex items-start gap-2.5">
            <span
              class="h-4 w-4 rounded-full flex items-center justify-center shrink-0 mt-0.5 text-[10px] font-bold"
              :class="form.mail_host ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-200 dark:bg-white/10 text-slate-500'"
            >
              ✓
            </span>
            <span>Relay Host set to <code>smtp-relay.brevo.com</code></span>
          </li>
          <li class="flex items-start gap-2.5">
            <span
              class="h-4 w-4 rounded-full flex items-center justify-center shrink-0 mt-0.5 text-[10px] font-bold"
              :class="form.mail_username ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-200 dark:bg-white/10 text-slate-500'"
            >
              ✓
            </span>
            <span>SMTP Login entered from Brevo Keys page</span>
          </li>
          <li class="flex items-start gap-2.5">
            <span
              class="h-4 w-4 rounded-full flex items-center justify-center shrink-0 mt-0.5 text-[10px] font-bold"
              :class="form.mail_password ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-200 dark:bg-white/10 text-slate-500'"
            >
              ✓
            </span>
            <span>Generated SMTP Master Key (starts with <code>xsmtpsib-</code>)</span>
          </li>
          <li class="flex items-start gap-2.5">
            <span
              class="h-4 w-4 rounded-full flex items-center justify-center shrink-0 mt-0.5 text-[10px] font-bold"
              :class="form.mail_from ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-200 dark:bg-white/10 text-slate-500'"
            >
              ✓
            </span>
            <span>Sender email verified in Brevo Senders console</span>
          </li>
        </ul>

        <div class="pt-3 border-t border-slate-200 dark:border-white/[0.06]">
          <a
            href="https://app.brevo.com/settings/keys/smtp"
            target="_blank"
            rel="noopener noreferrer"
            class="text-xs text-[var(--color-accent)] hover:underline inline-flex items-center gap-1 font-semibold"
          >
            <span>Open Brevo SMTP &amp; API Keys Console</span>
            <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5" />
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
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
  saving: {
    type: Boolean,
    default: false
  },
  testing: {
    type: Boolean,
    default: false
  },
  testResult: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['save', 'test-dispatch']);

const showSmtpKey = ref(false);
const showApiKey = ref(false);
const recipientEmail = ref('');

function fillDefaults() {
  props.form.mail_host = 'smtp-relay.brevo.com';
  props.form.mail_port = '587';
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
</script>
