<template>
  <form @submit.prevent="$emit('save')" class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-8 shadow-xl space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-white/[0.08]">
      <div>
        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <BuildingLibraryIcon class="h-5 w-5 text-[var(--color-accent)]" />
          Primary Salon Bank Configuration
        </h2>
        <p class="text-xs text-slate-500 dark:text-[var(--color-text-secondary)] mt-0.5">
          These details are directly presented on customer bank transfer checkout screens.
        </p>
      </div>
      <span class="text-[11px] font-mono text-slate-500 dark:text-zinc-400">Settings: <strong class="text-emerald-500 dark:text-emerald-400">Database Live</strong></span>
    </div>

    <!-- Bank Selection / Custom Input -->
    <div class="space-y-2">
      <label class="block text-xs uppercase tracking-wider font-bold text-slate-700 dark:text-slate-300">
        Bank Name <span class="text-[var(--color-accent)]">*</span>
      </label>
      <input
        :value="form.bank_name"
        @input="form.bank_name = $event.target.value"
        type="text"
        required
        placeholder="e.g. Sterling Bank, Zenith Bank, GTBank"
        class="w-full rounded-2xl border border-slate-200 dark:border-white/[0.1] bg-slate-50 dark:bg-black/40 px-4 py-3.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none transition-all focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40 focus:ring-4 focus:ring-[var(--color-accent)]/10"
      />
      <!-- Popular Bank Quick Pills -->
      <div class="flex flex-wrap items-center gap-1.5 pt-1">
        <span class="text-[10px] text-slate-500 dark:text-zinc-400 mr-1 uppercase tracking-wider font-semibold">Quick Pick:</span>
        <button
          v-for="b in popularBanks"
          :key="b"
          type="button"
          @click="form.bank_name = b"
          :class="[
            'px-2.5 py-1 rounded-lg text-[11px] font-medium transition-all cursor-pointer',
            form.bank_name === b
              ? 'bg-[var(--color-accent)] text-obsidian font-bold shadow-sm'
              : 'bg-slate-100 dark:bg-white/[0.04] text-slate-700 dark:text-zinc-300 hover:bg-slate-200 dark:hover:bg-white/[0.08] hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-white/[0.06]'
          ]"
        >
          {{ b }}
        </button>
      </div>
    </div>

    <!-- Account Holder Name -->
    <div class="space-y-2">
      <label class="block text-xs uppercase tracking-wider font-bold text-slate-700 dark:text-slate-300">
        Account Holder Name <span class="text-[var(--color-accent)]">*</span>
      </label>
      <input
        :value="form.account_name"
        @input="form.account_name = $event.target.value"
        type="text"
        required
        placeholder="e.g. NextGen PG, Candy Cutz Luxury Grooming"
        class="w-full rounded-2xl border border-slate-200 dark:border-white/[0.1] bg-slate-50 dark:bg-black/40 px-4 py-3.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none transition-all focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40 focus:ring-4 focus:ring-[var(--color-accent)]/10"
      />
    </div>

    <!-- Account Number -->
    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <label class="block text-xs uppercase tracking-wider font-bold text-slate-700 dark:text-slate-300">
          Account Number (NUBAN) <span class="text-[var(--color-accent)]">*</span>
        </label>
        <span
          class="text-[11px] font-mono"
          :class="form.account_number.length === 10 ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-400 dark:text-zinc-500'"
        >
          {{ form.account_number.length }}/10 Digits
        </span>
      </div>
      <input
        :value="form.account_number"
        @input="form.account_number = $event.target.value"
        type="text"
        maxlength="10"
        required
        placeholder="e.g. 5261935770"
        class="w-full rounded-2xl border border-slate-200 dark:border-white/[0.1] bg-slate-50 dark:bg-black/40 px-4 py-3.5 font-mono text-base text-[var(--color-accent)] tracking-wider placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none transition-all focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40 focus:ring-4 focus:ring-[var(--color-accent)]/10"
      />
    </div>

    <!-- Payment Narration Instructions -->
    <div class="space-y-2">
      <label class="block text-xs uppercase tracking-wider font-bold text-slate-700 dark:text-slate-300">
        Transfer Remarks / Narration Instructions
      </label>
      <textarea
        :value="form.instructions"
        @input="form.instructions = $event.target.value"
        rows="3"
        placeholder="e.g. Please put your Booking Reference in the transfer description to speed up confirmation."
        class="w-full rounded-2xl border border-slate-200 dark:border-white/[0.1] bg-slate-50 dark:bg-black/40 px-4 py-3 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none transition-all focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40 focus:ring-4 focus:ring-[var(--color-accent)]/10 leading-relaxed resize-none"
      ></textarea>
    </div>

    <!-- Bottom Actions -->
    <div class="pt-4 border-t border-slate-100 dark:border-white/[0.08] flex items-center justify-between gap-4">
      <button
        type="button"
        @click="$emit('reset-defaults')"
        class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 text-xs font-semibold text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06] transition-all cursor-pointer"
      >
        Reset Defaults
      </button>

      <button
        type="submit"
        :disabled="saving"
        class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--color-accent)] to-amber-500 px-6 py-2.5 text-xs font-bold text-obsidian shadow-lg shadow-[var(--color-accent)]/20 hover:brightness-110 active:scale-95 transition-all disabled:opacity-50 cursor-pointer"
      >
        <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></span>
        <CheckCircleIcon v-else class="h-4 w-4" />
        <span>{{ saving ? 'Saving...' : 'Save Bank Details' }}</span>
      </button>
    </div>
  </form>
</template>

<script setup>
import { BuildingLibraryIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

defineProps({
  form: { type: Object, required: true },
  saving: { type: Boolean, default: false }
});

defineEmits(['save', 'reset-defaults']);

const popularBanks = [
  'Sterling Bank',
  'Zenith Bank',
  'GTBank',
  'Access Bank',
  'First Bank',
  'UBA',
  'Kuda Bank',
  'Moniepoint',
  'OPay'
];
</script>
