<template>
  <form
    @submit.prevent="$emit('save')"
    class="rounded-2xl border border-theme-border bg-theme-surface p-5 sm:p-6 shadow-xl space-y-5"
  >
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-theme-border">
      <div>
        <h2 class="text-base sm:text-lg font-bold text-theme-text">
          No-Show Timing, Grace Periods & Consequence Policy
        </h2>
        <p class="text-xs text-theme-text-muted mt-0.5">
          Configure arrival deadlines, warning notifications (Email + Web & App Push), grace extensions, and consequence thresholds.
        </p>
      </div>
      <button
        type="submit"
        :disabled="saving"
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-admin text-obsidian text-xs font-bold uppercase tracking-wider hover:brightness-110 disabled:opacity-50 transition-all cursor-pointer shrink-0"
      >
        <span v-if="saving" class="h-3.5 w-3.5 border-2 border-obsidian border-t-transparent rounded-full animate-spin"></span>
        <span>{{ saving ? 'Saving...' : 'Save Policy' }}</span>
      </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <label class="space-y-1.5 block">
        <span class="text-xs font-semibold text-theme-text">No-Show Delay (Minutes)</span>
        <input
          v-model.number="policy.no_show_delay_minutes"
          type="number"
          min="5"
          max="180"
          class="w-full px-3.5 py-2.5 rounded-xl bg-theme-bg border border-theme-border text-xs font-mono text-theme-text focus:outline-none focus:border-admin"
        />
        <span class="text-[11px] text-theme-text-muted block">
          Auto-mark no-show this many minutes after scheduled start.
        </span>
      </label>

      <label class="space-y-1.5 block">
        <span class="text-xs font-semibold text-theme-text">Warning Lead Time (Minutes)</span>
        <input
          v-model.number="policy.no_show_warning_lead_minutes"
          type="number"
          min="1"
          max="60"
          class="w-full px-3.5 py-2.5 rounded-xl bg-theme-bg border border-theme-border text-xs font-mono text-theme-text focus:outline-none focus:border-admin"
        />
        <span class="text-[11px] text-theme-text-muted block">
          Send Email + Web/App Push warning {{ policy.no_show_warning_lead_minutes }}m before deadline.
        </span>
      </label>

      <label class="space-y-1.5 block">
        <span class="text-xs font-semibold text-theme-text">Allowed Grace Periods (Per Booking)</span>
        <input
          v-model.number="policy.no_show_grace_limit"
          type="number"
          min="0"
          max="5"
          class="w-full px-3.5 py-2.5 rounded-xl bg-theme-bg border border-theme-border text-xs font-mono text-theme-text focus:outline-none focus:border-admin"
        />
        <span class="text-[11px] text-theme-text-muted block">
          Max extensions a customer can request with a valid reason.
        </span>
      </label>

      <label class="space-y-1.5 block">
        <span class="text-xs font-semibold text-theme-text">Grace Extension Length (Minutes)</span>
        <input
          v-model.number="policy.no_show_grace_extension_minutes"
          type="number"
          min="5"
          max="120"
          class="w-full px-3.5 py-2.5 rounded-xl bg-theme-bg border border-theme-border text-xs font-mono text-theme-text focus:outline-none focus:border-admin"
        />
        <span class="text-[11px] text-theme-text-muted block">
          Extra minutes added when a customer extends once.
        </span>
      </label>

      <label class="space-y-1.5 block">
        <span class="text-xs font-semibold text-theme-text">No-Shows Before Consequences</span>
        <input
          v-model.number="policy.no_show_threshold"
          type="number"
          min="1"
          max="20"
          class="w-full px-3.5 py-2.5 rounded-xl bg-theme-bg border border-theme-border text-xs font-mono text-theme-text focus:outline-none focus:border-admin"
        />
        <span class="text-[11px] text-theme-text-muted block">
          Require mandatory upfront deposit once reached.
        </span>
      </label>

      <label class="space-y-1.5 block">
        <span class="text-xs font-semibold text-theme-text">Consequence Window (Days)</span>
        <input
          v-model.number="policy.no_show_threshold_window_days"
          type="number"
          min="7"
          max="365"
          class="w-full px-3.5 py-2.5 rounded-xl bg-theme-bg border border-theme-border text-xs font-mono text-theme-text focus:outline-none focus:border-admin"
        />
        <span class="text-[11px] text-theme-text-muted block">
          Rolling lookback window for counting missed bookings.
        </span>
      </label>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-theme-border">
      <label class="flex items-start gap-3 p-3.5 rounded-xl bg-theme-bg border border-theme-border cursor-pointer">
        <input
          v-model="policy.no_show_auto_mark_enabled"
          type="checkbox"
          class="mt-0.5 h-4 w-4 rounded accent-amber-500"
        />
        <div>
          <span class="text-xs font-bold text-theme-text block">Enable Automatic No-Show Sweep</span>
          <span class="text-[11px] text-theme-text-muted">
            Automatically warns late clients and marks confirmed bookings as no-show after the deadline.
          </span>
        </div>
      </label>

      <label class="flex items-start gap-3 p-3.5 rounded-xl bg-theme-bg border border-theme-border cursor-pointer">
        <input
          v-model="policy.no_show_auto_forfeit_deposit"
          type="checkbox"
          class="mt-0.5 h-4 w-4 rounded accent-amber-500"
        />
        <div>
          <span class="text-xs font-bold text-theme-text block">Auto-Forfeit Deposit on Sweep</span>
          <span class="text-[11px] text-theme-text-muted">
            Leave unchecked to forfeit deposits manually via the "Forfeit Deposit" button.
          </span>
        </div>
      </label>
    </div>
  </form>
</template>

<script setup>
defineProps({
  policy: { type: Object, required: true },
  saving: { type: Boolean, default: false },
});

defineEmits(['save']);
</script>
