<template>
  <section class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] shadow-xl" aria-labelledby="email-flow-diagnostics">
    <details>
      <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-5 sm:p-6">
        <div>
          <h2 id="email-flow-diagnostics" class="font-display text-base font-bold text-slate-900 dark:text-white">
            Email Flow Diagnostics
          </h2>
          <p class="mt-1 text-xs text-slate-600 dark:text-white/50">{{ summaryStatus }}</p>
        </div>
        <span class="shrink-0 text-xs font-semibold text-[var(--color-accent)]">View / hide details</span>
      </summary>

      <div class="space-y-5 border-t border-slate-200 p-5 dark:border-white/[0.06] sm:p-6">
        <p class="text-xs text-slate-600 dark:text-white/50">
          A saved configuration is not proof of provider connectivity or inbox delivery.
        </p>

        <div class="grid gap-4 md:grid-cols-2">
          <div class="rounded-2xl border border-slate-200 dark:border-white/[0.06] p-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-white/80">1. Configuration</h3>
            <p class="mt-2 text-xs text-slate-600 dark:text-white/60">
              {{ settingsLoaded ? 'Settings API responded.' : 'Settings have not loaded yet.' }}
              {{ apiKeyDescription }}
            </p>
            <p class="mt-1 text-xs text-slate-600 dark:text-white/60">
              {{ senderEmail ? `Sender address set: ${senderEmail}.` : 'No sender address is set.' }}
              A sender address here does not confirm Resend domain verification.
            </p>
            <p v-if="settingsLoaded && configDirty" class="mt-2 text-xs font-semibold text-amber-600 dark:text-amber-400">
              Unsaved key or sender edits: save before running a test. Tests use saved backend settings.
            </p>
            <p v-else-if="!settingsLoaded && !settingsError" class="mt-2 text-xs text-amber-600 dark:text-amber-400">
              Waiting for saved settings before a test can run.
            </p>
            <p v-else-if="settingsError" class="mt-2 text-xs text-amber-600 dark:text-amber-400">
              Saved values could not be verified; reload settings before testing.
            </p>
            <p v-if="settingsError || saveError" class="mt-2 text-xs text-rose-600 dark:text-rose-400">
              {{ settingsError || saveError }}
            </p>
            <p v-else-if="saveMessage" class="mt-2 text-xs text-emerald-600 dark:text-emerald-400">
              {{ saveMessage }}
            </p>
          </div>

          <div class="rounded-2xl border border-slate-200 dark:border-white/[0.06] p-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-white/80">2. Resend API Connection</h3>
            <p v-if="!connectionResult" class="mt-2 text-xs text-slate-600 dark:text-white/60">
              Not checked yet. This check authenticates with Resend and lists domain status; it does not send an email.
            </p>
            <template v-else>
              <p class="mt-2 text-xs font-semibold" :class="connectionResult.success ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                {{ connectionResult.success ? 'Resend API connection succeeded.' : 'Resend API connection failed.' }}
              </p>
              <p class="mt-1 break-words text-xs text-slate-600 dark:text-white/60">{{ connectionResult.message }}</p>
              <p v-if="connectionResult.success" class="mt-1 text-xs text-slate-600 dark:text-white/60">
                {{ connectionResult.verifiedDomainCount }} verified domain(s) out of {{ connectionResult.domainCount }}.
                API connectivity does not guarantee email delivery.
              </p>
              <p v-if="connectionResult.httpStatus || connectionResult.code" class="mt-1 text-[11px] font-mono text-slate-500 dark:text-white/40">
                <span v-if="connectionResult.httpStatus">HTTP {{ connectionResult.httpStatus }}</span>
                <span v-if="connectionResult.httpStatus && connectionResult.code"> · </span>
                <span v-if="connectionResult.code">{{ connectionResult.code }}</span>
              </p>
            </template>
          </div>

          <div class="rounded-2xl border border-slate-200 dark:border-white/[0.06] p-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-white/80">3. Live Dispatch Test</h3>
            <p v-if="!testResult" class="mt-2 text-xs text-slate-600 dark:text-white/60">
              Not run yet. Save settings, send a test to an inbox you control, then check the inbox and Resend events.
            </p>
            <template v-else>
              <p class="mt-2 text-xs font-semibold" :class="testResult.success ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                {{ testResult.success ? 'The backend accepted the dispatch.' : 'The backend rejected or failed the dispatch.' }}
              </p>
              <p class="mt-1 break-words text-xs text-slate-600 dark:text-white/60">{{ testResult.message }}</p>
              <p v-if="testResult.httpStatus || testResult.code" class="mt-1 text-[11px] font-mono text-slate-500 dark:text-white/40">
                <span v-if="testResult.httpStatus">HTTP {{ testResult.httpStatus }}</span>
                <span v-if="testResult.httpStatus && testResult.code"> · </span>
                <span v-if="testResult.code">{{ testResult.code }}</span>
              </p>
              <p v-if="testResult.success" class="mt-2 text-[11px] text-slate-500 dark:text-white/40">
                Provider acceptance does not guarantee inbox placement.
              </p>
            </template>
            <p v-if="deliveryError" class="mt-2 text-xs text-rose-600 dark:text-rose-400">
              Delivery-log API: {{ deliveryError }}
            </p>
            <p v-if="statsError" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
              Delivery-statistics API: {{ statsError }}
            </p>
          </div>
        </div>

        <details class="rounded-2xl border border-amber-500/20 bg-amber-500/[0.06]">
          <summary class="cursor-pointer list-none p-4 text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">
            Password-reset OTP details
          </summary>
          <div class="space-y-2 border-t border-amber-500/20 p-4">
            <p class="text-xs leading-relaxed text-slate-700 dark:text-white/70">
              OTP requests use <code>/api/v1/auth/forgot-password</code>. For account-enumeration protection, that endpoint returns a generic success even if email sending fails. A success response therefore does not prove the OTP was sent.
            </p>
            <p class="text-xs leading-relaxed text-slate-700 dark:text-white/70">
              OTP mail errors are written to backend logs and are not recorded in the notification-delivery list, so an empty log does not rule out an OTP failure.
            </p>
          </div>
        </details>

        <details class="rounded-2xl border border-slate-200 dark:border-white/[0.06]">
          <summary class="cursor-pointer list-none p-4 text-xs font-bold text-slate-700 dark:text-white/70">
            Mail transport and SMTP fields
          </summary>
          <p class="border-t border-slate-200 p-4 text-xs leading-relaxed text-slate-600 dark:border-white/[0.06] dark:text-white/50">
            The current Resend mailer uses the Resend API key and sender address. The SMTP host, port, and login fields on this page are not used by that transport.
          </p>
        </details>
      </div>
    </details>
  </section>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  apiKeyStatus: { type: String, default: 'missing' },
  senderEmail: { type: String, default: '' },
  settingsLoaded: { type: Boolean, default: false },
  settingsError: { type: String, default: '' },
  saveMessage: { type: String, default: '' },
  saveError: { type: String, default: '' },
  configDirty: { type: Boolean, default: true },
  connectionResult: { type: Object, default: null },
  testResult: { type: Object, default: null },
  deliveryError: { type: String, default: '' },
  statsError: { type: String, default: '' }
});

const summaryStatus = computed(() => {
  const configStatus = props.settingsError
    ? 'Settings unavailable'
    : !props.settingsLoaded
      ? 'Loading settings'
      : props.configDirty
        ? 'Unsaved edits'
        : 'Settings loaded';
  const testStatus = !props.testResult
    ? 'Test not run'
    : props.testResult.success
      ? 'Dispatch accepted'
      : 'Test failed';
  const connectionStatus = !props.connectionResult
    ? 'API connection not checked'
    : props.connectionResult.success
      ? 'API connected'
      : 'API connection failed';
  return `${configStatus} · ${connectionStatus} · ${testStatus} · OTP errors may be hidden`;
});

const apiKeyDescription = computed(() => {
  switch (props.apiKeyStatus) {
    case 'saved':
      return 'A saved key is present but masked; its validity has not been tested.';
    case 'provided':
      return 'A key with the Resend prefix is present; its validity has not been tested.';
    case 'invalid':
      return 'A key value exists, but it does not start with the expected re_ prefix.';
    default:
      return 'No saved Resend key is visible; environment-level keys are not checked on this page.';
  }
});
</script>
