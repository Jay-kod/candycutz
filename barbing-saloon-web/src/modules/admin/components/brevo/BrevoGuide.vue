<template>
  <div class="space-y-8 max-w-5xl">
    <!-- Overview Banner -->
    <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-8 shadow-2xl">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--color-accent)] px-2.5 py-1 rounded bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20">
            Official Integration Guide
          </span>
          <h2 class="font-display text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mt-2">
            How to Set Up Brevo Transactional Email
          </h2>
          <p class="text-xs sm:text-sm text-slate-600 dark:text-white/60 mt-1 max-w-2xl leading-relaxed">
            Follow this 5-step walkthrough to connect Brevo (formerly Sendinblue) with CandyCutz. The Brevo free tier includes 300 free emails per day with zero credit card required.
          </p>
        </div>
        <a
          href="https://www.brevo.com/"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--color-accent)] to-amber-500 px-5 py-3 text-xs font-bold text-obsidian shadow-lg shadow-[var(--color-accent)]/20 hover:brightness-110 active:scale-95 transition-all shrink-0 cursor-pointer"
        >
          <span>Open Brevo Website</span>
          <ArrowTopRightOnSquareIcon class="h-4 w-4" />
        </a>
      </div>
    </div>

    <!-- 5 Walkthrough Steps -->
    <div class="space-y-4">
      <!-- Step 1 -->
      <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-7 shadow-xl">
        <div class="flex items-start gap-4">
          <div class="h-10 w-10 rounded-2xl bg-[var(--color-accent)]/15 border border-[var(--color-accent)]/30 flex items-center justify-center font-display font-bold text-base text-[var(--color-accent)] shrink-0">
            1
          </div>
          <div class="space-y-3 flex-1 min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-2">
              <h3 class="font-display text-base font-bold text-slate-900 dark:text-white">
                Create or Log In to Your Brevo Account
              </h3>
              <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded">
                Free Tier Available
              </span>
            </div>
            <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed">
              Visit <a href="https://www.brevo.com" target="_blank" rel="noopener noreferrer" class="text-[var(--color-accent)] hover:underline font-semibold">brevo.com</a> and sign up for a free account. Complete the initial registration and verify your business email address.
            </p>
            <div class="rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.06] p-3 text-xs text-slate-500 dark:text-white/60">
              💡 <strong>Pro Tip:</strong> The Brevo Free Plan includes <strong>300 emails/day</strong>, which easily handles customer receipts, password resets, and appointment notices.
            </div>
          </div>
        </div>
      </div>

      <!-- Step 2 -->
      <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-7 shadow-xl">
        <div class="flex items-start gap-4">
          <div class="h-10 w-10 rounded-2xl bg-[var(--color-accent)]/15 border border-[var(--color-accent)]/30 flex items-center justify-center font-display font-bold text-base text-[var(--color-accent)] shrink-0">
            2
          </div>
          <div class="space-y-3 flex-1 min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-2">
              <h3 class="font-display text-base font-bold text-slate-900 dark:text-white">
                Verify Your Sender Email &amp; Domain
              </h3>
              <a
                href="https://app.brevo.com/senders"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1 text-xs text-[var(--color-accent)] hover:underline font-semibold"
              >
                <span>Brevo Senders Console</span>
                <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5" />
              </a>
            </div>
            <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed">
              Navigate to <strong>Senders, Domains &amp; Dedicated IPs</strong> in Brevo. Click <strong>Add a Sender</strong> and enter the email address you want emails sent from (e.g. <code>no-reply@candycutz.com</code> or <code>bookings@yourdomain.com</code>).
            </p>
            <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed">
              Brevo will send a confirmation code to that inbox to verify ownership. For 100% deliverability and to avoid spam folders, authenticate your domain by adding Brevo's SPF and DKIM TXT records in your DNS manager (GoDaddy, Namecheap, Cloudflare, etc.).
            </p>
          </div>
        </div>
      </div>

      <!-- Step 3 -->
      <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-7 shadow-xl">
        <div class="flex items-start gap-4">
          <div class="h-10 w-10 rounded-2xl bg-[var(--color-accent)]/15 border border-[var(--color-accent)]/30 flex items-center justify-center font-display font-bold text-base text-[var(--color-accent)] shrink-0">
            3
          </div>
          <div class="space-y-3 flex-1 min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-2">
              <h3 class="font-display text-base font-bold text-slate-900 dark:text-white">
                Generate an SMTP Key
              </h3>
              <a
                href="https://app.brevo.com/settings/keys/smtp"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1 text-xs text-[var(--color-accent)] hover:underline font-semibold"
              >
                <span>SMTP &amp; API Keys</span>
                <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5" />
              </a>
            </div>
            <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed">
              In your Brevo dashboard, click your profile menu at top-right and select <strong>SMTP &amp; API</strong>, or navigate to <strong>Transactional &rarr; Settings &rarr; Configuration</strong>.
            </p>
            <ol class="list-decimal list-inside space-y-1.5 text-xs text-slate-600 dark:text-white/70">
              <li>Click the <strong>SMTP</strong> tab.</li>
              <li>Note your <strong>SMTP Server</strong> (<code>smtp-relay.brevo.com</code>), <strong>Port</strong> (<code>587</code>), and <strong>Login</strong> (e.g. <code>7f9b8c001@smtp-brevo.com</code>).</li>
              <li>Click <strong>Generate a new SMTP key</strong>.</li>
              <li>Give your key a descriptive name like <code>CandyCutz Live</code> and click <strong>Generate</strong>.</li>
              <li>Copy the generated key (starts with <code>xsmtpsib-...</code>). Keep this window open.</li>
            </ol>
            <div class="rounded-xl bg-amber-500/10 border border-amber-500/20 p-3 text-xs text-amber-300">
              ⚠️ <strong>Important:</strong> Brevo only shows your generated SMTP Key once. Do not use your regular account login password here — only the generated SMTP key works.
            </div>
          </div>
        </div>
      </div>

      <!-- Step 4 -->
      <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-7 shadow-xl">
        <div class="flex items-start gap-4">
          <div class="h-10 w-10 rounded-2xl bg-[var(--color-accent)]/15 border border-[var(--color-accent)]/30 flex items-center justify-center font-display font-bold text-base text-[var(--color-accent)] shrink-0">
            4
          </div>
          <div class="space-y-3 flex-1 min-w-0">
            <h3 class="font-display text-base font-bold text-slate-900 dark:text-white">
              Save Your Credentials in CandyCutz
            </h3>
            <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed">
              Switch to the <strong>Credentials &amp; SMTP</strong> tab in this dashboard and enter the parameters:
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
              <div class="rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.06] p-3">
                <span class="font-bold text-slate-900 dark:text-white">SMTP Relay Host:</span>
                <code class="block mt-1 font-mono text-[var(--color-accent)]">smtp-relay.brevo.com</code>
              </div>
              <div class="rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.06] p-3">
                <span class="font-bold text-slate-900 dark:text-white">Port:</span>
                <code class="block mt-1 font-mono text-[var(--color-accent)]">587 (TLS encryption)</code>
              </div>
              <div class="rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.06] p-3">
                <span class="font-bold text-slate-900 dark:text-white">SMTP Login:</span>
                <span class="block mt-1 font-mono text-slate-500 dark:text-white/60">Your Brevo SMTP Username</span>
              </div>
              <div class="rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.06] p-3">
                <span class="font-bold text-slate-900 dark:text-white">SMTP Password:</span>
                <span class="block mt-1 font-mono text-slate-500 dark:text-white/60">Paste the generated xsmtpsib- key</span>
              </div>
            </div>
            <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed">
              Set your <strong>From Sender Email</strong> to match the verified sender from Step 2, and click <strong>Save Configuration</strong>.
            </p>
          </div>
        </div>
      </div>

      <!-- Step 5 -->
      <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-7 shadow-xl">
        <div class="flex items-start gap-4">
          <div class="h-10 w-10 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center font-display font-bold text-base text-emerald-400 shrink-0">
            5
          </div>
          <div class="space-y-3 flex-1 min-w-0">
            <h3 class="font-display text-base font-bold text-slate-900 dark:text-white">
              Verify End-to-End Delivery
            </h3>
            <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed">
              Use the <strong>Live Connection Test</strong> tool on the right-hand side of the configuration tab. Enter your personal email address and click <strong>Dispatch Test Email</strong>.
            </p>
            <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed">
              You should receive a test message with the subject <em>"CandyCutz Brevo connection test"</em> within seconds. Once verified, your entire notification engine is active!
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Troubleshooting / FAQ Section -->
    <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-8 shadow-2xl space-y-4">
      <h3 class="font-display text-lg font-bold text-slate-900 dark:text-white">
        Frequently Asked Questions &amp; Troubleshooting
      </h3>
      <div class="space-y-3 text-xs text-slate-600 dark:text-white/70">
        <div class="rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.06] p-4 space-y-1">
          <p class="font-bold text-slate-900 dark:text-white">❓ Error 535: Authentication Failed</p>
          <p class="text-slate-500 dark:text-white/60 leading-relaxed">
            This error occurs when you use your Brevo web login password instead of the dedicated SMTP Master Key, or if there is a typo in your SMTP Login. Re-generate an SMTP key under Brevo &rarr; SMTP &amp; API &rarr; SMTP, paste it into CandyCutz, and save.
          </p>
        </div>

        <div class="rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.06] p-4 space-y-1">
          <p class="font-bold text-slate-900 dark:text-white">❓ Sender Not Authorized / Rejected</p>
          <p class="text-slate-500 dark:text-white/60 leading-relaxed">
            Brevo requires the "From" address to be pre-authorized. Ensure the value in <strong>From Sender Email</strong> exactly matches an email address approved in <a href="https://app.brevo.com/senders" target="_blank" rel="noopener noreferrer" class="text-[var(--color-accent)] hover:underline">Brevo Senders</a>.
          </p>
        </div>

        <div class="rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.06] p-4 space-y-1">
          <p class="font-bold text-slate-900 dark:text-white">❓ Emails Going to Spam or Junk</p>
          <p class="text-slate-500 dark:text-white/60 leading-relaxed">
            Ensure your sending domain has Brevo's SPF record (<code>include:spf.brevo.com</code>) and DKIM TXT record added in your DNS settings. This gives mail providers cryptographic proof that Brevo is authorized to send emails on your behalf.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/outline';
</script>
