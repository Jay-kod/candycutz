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
            How to Set Up Resend Transactional Email
          </h2>
          <p class="text-xs sm:text-sm text-slate-600 dark:text-white/60 mt-1 max-w-2xl leading-relaxed">
            Follow this 5-step walkthrough to connect Resend with CandyCutz. Resend provides developer-first, high-deliverability transactional email delivery with built-in rate-limiting safeguards.
          </p>
        </div>
        <a
          href="https://resend.com/"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--color-accent)] to-amber-500 px-5 py-3 text-xs font-bold text-obsidian shadow-lg shadow-[var(--color-accent)]/20 hover:brightness-110 active:scale-95 transition-all shrink-0 cursor-pointer"
        >
          <span>Open Resend Website</span>
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
                Create or Log In to Your Resend Account
              </h3>
              <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded">
                Developer Friendly
              </span>
            </div>
            <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed">
              Visit <a href="https://resend.com" target="_blank" rel="noopener noreferrer" class="text-[var(--color-accent)] hover:underline font-semibold">resend.com</a> and create your account. Complete registration and verify your account email.
            </p>
            <div class="rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.06] p-3 text-xs text-slate-500 dark:text-white/60">
              💡 <strong>Pro Tip:</strong> Resend's free tier includes <strong>3,000 emails/month</strong> (100 emails/day), which cleanly covers booking confirmations, receipts, and password resets.
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
                Add &amp; Verify Your Sending Domain
              </h3>
              <a
                href="https://resend.com/domains"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1 text-xs text-[var(--color-accent)] hover:underline font-semibold"
              >
                <span>Resend Domains Console</span>
                <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5" />
              </a>
            </div>
            <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed">
              Navigate to <strong>Domains</strong> in Resend. Click <strong>Add Domain</strong> (e.g. <code>mail.candycutz.com</code> or <code>candycutz.com</code>).
            </p>
            <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed">
              Resend will provide DNS records (DKIM, SPF, and DMARC TXT records). Add these in your DNS manager (Cloudflare, cPanel Zone Editor, Route53, Namecheap). Once verified, your deliverability reaches near 100%.
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
                Generate an API Key
              </h3>
              <a
                href="https://resend.com/api-keys"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1 text-xs text-[var(--color-accent)] hover:underline font-semibold"
              >
                <span>Resend API Keys</span>
                <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5" />
              </a>
            </div>
            <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed">
              Go to <strong>API Keys</strong> in Resend and click <strong>Create API Key</strong>.
            </p>
            <ol class="list-decimal list-inside space-y-1.5 text-xs text-slate-600 dark:text-white/70">
              <li>Give your key a name such as <code>CandyCutz Production</code>.</li>
              <li>Set permissions to <strong>Sending access</strong> restricted to your verified domain.</li>
              <li>Click <strong>Add</strong> and copy your new API key (starts with <code>re_</code>).</li>
            </ol>
            <div class="rounded-xl bg-amber-500/10 border border-amber-500/20 p-3 text-xs text-amber-300">
              ⚠️ <strong>Important:</strong> Copy the key immediately. Resend displays the full token only once upon creation.
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
              Save Credentials in CandyCutz
            </h3>
            <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed">
              Switch to the <strong>Credentials</strong> tab in this dashboard:
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
              <div class="rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.06] p-3">
                <span class="font-bold text-slate-900 dark:text-white">Resend API Key:</span>
                <code class="block mt-1 font-mono text-[var(--color-accent)]">re_••••••••1234</code>
              </div>
              <div class="rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.06] p-3">
                <span class="font-bold text-slate-900 dark:text-white">From Sender Email:</span>
                <code class="block mt-1 font-mono text-[var(--color-accent)]">no-reply@mail.candycutz.com</code>
              </div>
            </div>
            <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed">
              Click <strong>Save Configuration</strong>. The backend automatically masks the key and secures it in the database.
            </p>
          </div>
        </div>
      </div>

      <!-- Step 5 -->
      <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-7 shadow-xl">
        <div class="flex items-start gap-4">
          <div class="h-10 w-10 rounded-2xl bg-[var(--color-accent)]/15 border border-[var(--color-accent)]/30 flex items-center justify-center font-display font-bold text-base text-[var(--color-accent)] shrink-0">
            5
          </div>
          <div class="space-y-3 flex-1 min-w-0">
            <h3 class="font-display text-base font-bold text-slate-900 dark:text-white">
              Dispatch a Live Test Email
            </h3>
            <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed">
              Enter your email address in the <strong>Live Connection Test</strong> box on the Credentials tab and click <strong>Dispatch Test Email</strong>. Confirm receipt in your inbox and inspect the entry in the <strong>Delivery Logs</strong> tab.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/outline';
</script>
