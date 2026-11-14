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
  </form>
</template>

<script setup>
import { AdjustmentsHorizontalIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  form: { type: Object, required: true },
  saving: { type: Boolean, default: false }
});

defineEmits(['save']);

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
</script>
