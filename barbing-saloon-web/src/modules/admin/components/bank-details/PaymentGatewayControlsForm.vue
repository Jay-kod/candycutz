<template>
  <form @submit.prevent="$emit('save')" class="space-y-6">
    <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-8 shadow-xl space-y-6">
      <!-- Section Title -->
      <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-white/[0.08]">
        <div>
          <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <AdjustmentsHorizontalIcon class="h-5 w-5 text-[var(--color-accent)]" />
            Checkout Page & Gateway Controls
          </h2>
          <p class="text-xs text-slate-500 dark:text-[var(--color-text-secondary)] mt-0.5">
            Admin controls for the customer payment modal matching the Paystack/FlyX layout.
          </p>
        </div>
        <span class="text-[11px] font-mono text-[var(--color-accent)] bg-[var(--color-accent)]/10 px-2 py-0.5 rounded-full border border-[var(--color-accent)]/20 font-bold">
          Admin Power Mode
        </span>
      </div>

      <!-- Grid of Config Fields -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Gateway Header / Brand Title -->
        <div class="space-y-2">
          <label class="block text-xs uppercase tracking-wider font-bold text-slate-700 dark:text-slate-300">
            Checkout Header / Brand Title
          </label>
          <input
            :value="form.payment_gateway_title"
            @input="form.payment_gateway_title = $event.target.value"
            type="text"
            required
            placeholder="e.g. MTN FlyX Registration • CandyCutz"
            class="w-full rounded-2xl border border-slate-200 dark:border-white/[0.1] bg-slate-50 dark:bg-black/40 px-4 py-3 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none transition-all focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40"
          />
          <p class="text-[11px] text-slate-500 dark:text-zinc-400">
            Displayed prominently at the top of the payment screen next to the logo badge.
          </p>
        </div>

        <!-- Expiry Timer in Minutes -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="block text-xs uppercase tracking-wider font-bold text-slate-700 dark:text-slate-300">
              Expiry Countdown (Minutes)
            </label>
            <span class="text-[11px] font-mono text-amber-500 font-bold">
              {{ form.payment_expiry_minutes || 120 }} mins ({{ formatCountdownDisplay(form.payment_expiry_minutes) }})
            </span>
          </div>
          <input
            :value="form.payment_expiry_minutes"
            @input="form.payment_expiry_minutes = Number($event.target.value)"
            type="number"
            min="5"
            max="1440"
            required
            class="w-full rounded-2xl border border-slate-200 dark:border-white/[0.1] bg-slate-50 dark:bg-black/40 px-4 py-3 text-sm font-mono text-[var(--color-accent)] outline-none transition-all focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40"
          />
          <!-- Quick presets -->
          <div class="flex items-center gap-1.5 pt-0.5">
            <span class="text-[10px] text-slate-500 dark:text-zinc-400 mr-1 uppercase font-semibold">Presets:</span>
            <button
              v-for="mins in [30, 60, 120, 180]"
              :key="mins"
              type="button"
              @click="form.payment_expiry_minutes = mins"
              :class="[
                'px-2 py-0.5 rounded text-[11px] font-mono font-medium transition-all cursor-pointer',
                form.payment_expiry_minutes === mins
                  ? 'bg-[var(--color-accent)] text-obsidian font-bold'
                  : 'bg-slate-100 dark:bg-white/[0.04] text-slate-700 dark:text-zinc-400 hover:text-white'
              ]"
            >
              {{ mins }}m
            </button>
          </div>
        </div>
      </div>

      <!-- Notice Banner Text -->
      <div class="space-y-2">
        <label class="block text-xs uppercase tracking-wider font-bold text-slate-700 dark:text-slate-300">
          Blue Alert Notice Text
        </label>
        <textarea
          :value="form.payment_notice_text"
          @input="form.payment_notice_text = $event.target.value"
          rows="2"
          placeholder="e.g. Before transferring, make sure the account name shown by your bank matches the one provided below."
          class="w-full rounded-2xl border border-slate-200 dark:border-white/[0.1] bg-slate-50 dark:bg-black/40 px-4 py-3 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none transition-all focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40 resize-none"
        ></textarea>
      </div>

      <!-- Enabled Channels Selector -->
      <div class="space-y-3">
        <label class="block text-xs uppercase tracking-wider font-bold text-slate-700 dark:text-slate-300">
          Active Payment Channels Available to Customers
        </label>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
          <label
            v-for="channel in allChannels"
            :key="channel.id"
            :class="[
              'flex items-center gap-2 p-3 rounded-2xl border transition-all cursor-pointer select-none',
              form.payment_channels.includes(channel.id)
                ? 'border-[var(--color-accent)]/50 bg-[var(--color-accent)]/10 text-slate-900 dark:text-white ring-1 ring-[var(--color-accent)]/30'
                : 'border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-white/[0.02] text-slate-500 dark:text-zinc-500 hover:border-slate-300 dark:hover:border-white/20'
            ]"
          >
            <input
              type="checkbox"
              :value="channel.id"
              :checked="form.payment_channels.includes(channel.id)"
              @change="toggleChannel(channel.id)"
              class="h-4 w-4 rounded accent-[var(--color-accent)] cursor-pointer"
            />
            <span class="text-xs font-semibold">{{ channel.label }}</span>
          </label>
        </div>
      </div>

      <!-- USSD Code Template & Confirmation Mode -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
        <div class="space-y-2">
          <label class="block text-xs uppercase tracking-wider font-bold text-slate-700 dark:text-slate-300">
            USSD Code Template
          </label>
          <input
            :value="form.ussd_code_template"
            @input="form.ussd_code_template = $event.target.value"
            type="text"
            placeholder="e.g. *737*2*{amount}*{account_number}#"
            class="w-full rounded-2xl border border-slate-200 dark:border-white/[0.1] bg-slate-50 dark:bg-black/40 px-4 py-3 text-xs font-mono text-slate-900 dark:text-white outline-none transition-all focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40"
          />
          <p class="text-[11px] text-slate-500 dark:text-zinc-400">
            Dynamic tags: <code class="text-[var(--color-accent)]">{amount}</code>, <code class="text-[var(--color-accent)]">{account_number}</code>
          </p>
        </div>

        <!-- Auto-Confirm Mode -->
        <div class="space-y-2">
          <label class="block text-xs uppercase tracking-wider font-bold text-slate-700 dark:text-slate-300">
            Payment Confirmation Mode
          </label>
          <div class="flex items-center gap-3 pt-1">
            <button
              type="button"
              @click="form.auto_confirm = true"
              :class="[
                'flex-1 p-3 rounded-2xl border text-xs font-bold transition-all cursor-pointer text-left',
                form.auto_confirm
                  ? 'border-emerald-500 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                  : 'border-slate-200 dark:border-white/[0.08] text-slate-500 dark:text-zinc-500'
              ]"
            >
              <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span>Auto-Confirm</span>
              </div>
              <p class="text-[10px] font-normal mt-1 opacity-80">Instant confirmation & barber booking upon customer transfer.</p>
            </button>

            <button
              type="button"
              @click="form.auto_confirm = false"
              :class="[
                'flex-1 p-3 rounded-2xl border text-xs font-bold transition-all cursor-pointer text-left',
                !form.auto_confirm
                  ? 'border-amber-500 bg-amber-500/10 text-amber-600 dark:text-amber-400'
                  : 'border-slate-200 dark:border-white/[0.08] text-slate-500 dark:text-zinc-500'
              ]"
            >
              <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                <span>Manual Verification</span>
              </div>
              <p class="text-[10px] font-normal mt-1 opacity-80">Requires Admin or Barber approval in registry before booking.</p>
            </button>
          </div>
        </div>
      </div>

      <!-- Action Footer -->
      <div class="pt-4 border-t border-slate-100 dark:border-white/[0.08] flex items-center justify-end">
        <button
          type="submit"
          :disabled="saving"
          class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--color-accent)] to-amber-500 px-6 py-2.5 text-xs font-bold text-obsidian shadow-lg shadow-[var(--color-accent)]/20 hover:brightness-110 active:scale-95 transition-all disabled:opacity-50 cursor-pointer"
        >
          <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></span>
          <CheckCircleIcon v-else class="h-4 w-4" />
          <span>{{ saving ? 'Saving Changes...' : 'Save Checkout Controls' }}</span>
        </button>
      </div>
    </div>

    <!-- Paystack Gateway Integration & Connection Testing Card -->
    <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-8 shadow-xl space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-white/[0.08]">
        <div>
          <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <BoltIcon class="h-5 w-5 text-emerald-500" />
            Paystack Integration & Connection
          </h2>
          <p class="text-xs text-slate-500 dark:text-[var(--color-text-secondary)] mt-0.5">
            Link your Paystack API keys to connect the payment gateway and verify your account credentials.
          </p>
        </div>
        <div class="flex items-center gap-2">
          <span
            :class="[
              'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border',
              paystackStatus.connected
                ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30'
                : 'bg-amber-500/10 text-amber-400 border-amber-500/30'
            ]"
          >
            <span class="h-2 w-2 rounded-full" :class="paystackStatus.connected ? 'bg-emerald-500' : 'bg-amber-500'"></span>
            {{ paystackStatus.connected ? (paystackStatus.mode?.toUpperCase() + ' MODE CONNECTED') : (paystackStatus.is_secret_set ? 'KEYS LINKED • UNTESTED' : 'NOT LINKED') }}
          </span>
        </div>
      </div>

      <!-- Paystack Keys Inputs -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Public Key -->
        <div class="space-y-2">
          <label class="block text-xs uppercase tracking-wider font-bold text-slate-700 dark:text-slate-300">
            Paystack Public Key
          </label>
          <input
            v-model="paystackForm.public_key"
            type="text"
            placeholder="pk_test_... or pk_live_..."
            class="w-full rounded-2xl border border-slate-200 dark:border-white/[0.1] bg-slate-50 dark:bg-black/40 px-4 py-3 text-xs font-mono text-slate-900 dark:text-white outline-none transition-all focus:border-emerald-500"
          />
          <p class="text-[11px] text-slate-500 dark:text-zinc-400">
            Client-facing key for Paystack checkout and mobile integration.
          </p>
        </div>

        <!-- Secret Key -->
        <div class="space-y-2">
          <label class="block text-xs uppercase tracking-wider font-bold text-slate-700 dark:text-slate-300">
            Paystack Secret Key
          </label>
          <div class="relative">
            <input
              v-model="paystackForm.secret_key"
              :type="showSecretKey ? 'text' : 'password'"
              :placeholder="paystackStatus.masked_secret_key || 'sk_test_... or sk_live_...'"
              class="w-full rounded-2xl border border-slate-200 dark:border-white/[0.1] bg-slate-50 dark:bg-black/40 px-4 py-3 pr-14 text-xs font-mono text-slate-900 dark:text-white outline-none transition-all focus:border-emerald-500"
            />
            <button
              type="button"
              @click="showSecretKey = !showSecretKey"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white text-xs cursor-pointer font-medium"
            >
              {{ showSecretKey ? 'Hide' : 'Show' }}
            </button>
          </div>
          <p class="text-[11px] text-slate-500 dark:text-zinc-400">
            Secret token used server-side for signature validation, transactions, and refunds.
          </p>
        </div>
      </div>

      <!-- Webhook URL Display & Paystack Dashboard Link -->
      <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/[0.06] space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <div>
            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Paystack Webhook URL:</span>
            <p class="text-[11px] text-slate-500 dark:text-zinc-400">Paste this URL into your Paystack Dashboard &rarr; Settings &rarr; Preferences / API Keys & Webhooks</p>
          </div>
          <a
            href="https://dashboard.paystack.com/#/settings/developer"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-1 text-xs text-[var(--color-accent)] hover:underline font-semibold"
          >
            Open Paystack Dashboard &rarr;
          </a>
        </div>
        <div class="flex items-center gap-2">
          <input
            :value="paystackStatus.webhook_url || defaultWebhookUrl"
            readonly
            class="flex-1 rounded-xl bg-white dark:bg-black/60 border border-slate-200 dark:border-white/[0.08] px-3 py-2 text-xs font-mono text-slate-600 dark:text-zinc-300"
          />
          <button
            type="button"
            @click="copyWebhook"
            class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/[0.1] bg-white dark:bg-white/[0.04] text-xs font-bold text-slate-700 dark:text-zinc-200 hover:bg-slate-100 dark:hover:bg-white/[0.08] transition-all cursor-pointer"
          >
            {{ webhookCopied ? 'Copied!' : 'Copy URL' }}
          </button>
        </div>
      </div>

      <!-- Test Connection Result Notification -->
      <div
        v-if="testResult"
        :class="[
          'p-3.5 rounded-2xl border text-xs flex items-center justify-between gap-3 transition-all',
          testResult.success
            ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400'
            : 'bg-rose-500/10 border-rose-500/30 text-rose-400'
        ]"
      >
        <div class="flex items-center gap-2">
          <CheckCircleIcon v-if="testResult.success" class="h-4 w-4 shrink-0 text-emerald-400" />
          <XCircleIcon v-else class="h-4 w-4 shrink-0 text-rose-400" />
          <span class="font-medium">{{ testResult.message }}</span>
        </div>
        <span
          v-if="testResult.mode"
          class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-black/40 border border-current shrink-0"
        >
          {{ testResult.mode }} MODE
        </span>
      </div>

      <!-- Footer Buttons for Paystack -->
      <div class="pt-4 border-t border-slate-100 dark:border-white/[0.08] flex flex-wrap items-center justify-between gap-3">
        <!-- Test Connection Button -->
        <button
          type="button"
          :disabled="testingConnection"
          @click="testConnection"
          class="flex items-center gap-2 rounded-xl bg-slate-800 dark:bg-white/[0.06] hover:bg-slate-700 dark:hover:bg-white/[0.1] border border-slate-600 dark:border-white/10 px-4 py-2.5 text-xs font-bold text-white transition-all disabled:opacity-50 cursor-pointer"
        >
          <span v-if="testingConnection" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
          <BoltIcon v-else class="h-4 w-4 text-emerald-400" />
          <span>{{ testingConnection ? 'Testing Connection...' : 'Test Paystack Connection' }}</span>
        </button>

        <!-- Save Keys Button -->
        <button
          type="button"
          :disabled="savingPaystack"
          @click="savePaystackKeys"
          class="flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 px-5 py-2.5 text-xs font-bold text-white shadow-lg shadow-emerald-600/20 active:scale-95 transition-all disabled:opacity-50 cursor-pointer"
        >
          <span v-if="savingPaystack" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
          <CheckCircleIcon v-else class="h-4 w-4" />
          <span>{{ savingPaystack ? 'Saving Keys...' : 'Save Paystack Keys' }}</span>
        </button>
      </div>
    </div>
  </form>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { AdjustmentsHorizontalIcon, CheckCircleIcon, BoltIcon, XCircleIcon } from '@heroicons/vue/24/outline';
import { client } from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

const props = defineProps({
  form: { type: Object, required: true },
  saving: { type: Boolean, default: false }
});

defineEmits(['save']);

const toast = useToast();

const allChannels = [
  { id: 'bank_transfer', label: 'Bank Transfer' },
  { id: 'ussd', label: 'USSD' },
  { id: 'bank_account', label: 'Bank Account' },
  { id: 'nqr', label: 'NQR' },
  { id: 'card', label: 'Card' }
];

function toggleChannel(channelId) {
  const current = props.form.payment_channels || [];
  if (current.includes(channelId)) {
    if (current.length === 1) return; // Prevent disabling all
    props.form.payment_channels = current.filter(c => c !== channelId);
  } else {
    props.form.payment_channels = [...current, channelId];
  }
}

function formatCountdownDisplay(mins) {
  const m = Number(mins) || 120;
  return `${m - 1}:21`;
}

// Paystack Integration State
const showSecretKey = ref(false);
const testingConnection = ref(false);
const savingPaystack = ref(false);
const webhookCopied = ref(false);
const defaultWebhookUrl = ref(
  typeof window !== 'undefined'
    ? `${window.location.origin}/api/v1/payments/webhook`
    : 'https://api.candycutz.com/api/v1/payments/webhook'
);

const paystackForm = reactive({
  public_key: '',
  secret_key: ''
});

const paystackStatus = reactive({
  connected: false,
  mode: '',
  is_secret_set: false,
  masked_secret_key: '',
  webhook_url: ''
});

const testResult = ref(null);

async function loadPaystackSettings() {
  try {
    const res = await client.get('/admin/paystack/settings');
    const data = res.data?.data || res.data || {};
    if (data.public_key) paystackForm.public_key = data.public_key;
    paystackStatus.is_secret_set = Boolean(data.is_secret_set);
    paystackStatus.masked_secret_key = data.masked_secret_key || '';
    if (data.webhook_url) paystackStatus.webhook_url = data.webhook_url;
  } catch {
    // Graceful fallback for test environments or offline
  }
}

async function copyWebhook() {
  const url = paystackStatus.webhook_url || defaultWebhookUrl.value;
  try {
    await navigator.clipboard.writeText(url);
    webhookCopied.value = true;
    toast.success('Webhook URL copied to clipboard!');
    setTimeout(() => { webhookCopied.value = false; }, 2500);
  } catch {
    toast.error('Failed to copy webhook URL');
  }
}

async function savePaystackKeys() {
  savingPaystack.value = true;
  try {
    await client.post('/admin/paystack/settings', {
      public_key: paystackForm.public_key.trim(),
      secret_key: paystackForm.secret_key.trim()
    });
    toast.success('Paystack API keys saved successfully!');
    paystackForm.secret_key = '';
    await loadPaystackSettings();
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to save Paystack settings';
    toast.error(msg);
  } finally {
    savingPaystack.value = false;
  }
}

async function testConnection() {
  testingConnection.value = true;
  testResult.value = null;

  try {
    const res = await client.post('/admin/paystack/test-connection', {
      secret_key: paystackForm.secret_key.trim() || undefined
    });
    const data = res.data?.data || res.data || {};
    testResult.value = {
      success: true,
      message: data.message || 'Connection verified successfully!',
      mode: data.mode || (paystackForm.public_key.startsWith('pk_live_') ? 'live' : 'test')
    };
    paystackStatus.connected = true;
    paystackStatus.mode = testResult.value.mode;
    toast.success(testResult.value.message);
  } catch (err) {
    const msg = err.response?.data?.message || err.message || 'Paystack connection failed';
    testResult.value = {
      success: false,
      message: msg,
      mode: null
    };
    paystackStatus.connected = false;
    toast.error(msg);
  } finally {
    testingConnection.value = false;
  }
}

onMounted(() => {
  loadPaystackSettings();
});
</script>
