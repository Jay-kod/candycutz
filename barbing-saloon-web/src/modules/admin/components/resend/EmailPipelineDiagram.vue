<template>
  <div class="space-y-6">
    <!-- Header Banner -->
    <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161F] p-6 shadow-2xl">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded">
              Pictorial System Architecture
            </span>
            <span class="text-[10px] font-mono text-slate-400 dark:text-white/40">TLS 1.3 &bull; DKIM Signed</span>
          </div>
          <h2 class="font-display text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mt-2">
            Email &amp; OTP Dispatch Pipelines
          </h2>
          <p class="text-xs sm:text-sm text-slate-600 dark:text-white/60 mt-1 max-w-2xl leading-relaxed">
            High-fidelity pictorial breakdown of how automated transactional emails and high-security OTP authentication codes flow through the CandyCutz architecture.
          </p>
        </div>

        <!-- Pipeline Selector Tabs -->
        <div class="flex items-center gap-1.5 p-1 rounded-2xl bg-slate-100 dark:bg-[#0B0E14] border border-slate-200 dark:border-white/[0.08] shrink-0">
          <button
            type="button"
            @click="activeFlow = 'transactional'"
            :class="[
              'px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2',
              activeFlow === 'transactional'
                ? 'bg-white dark:bg-white/10 text-slate-900 dark:text-white shadow-sm'
                : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
            ]"
          >
            <EnvelopeIcon class="w-4 h-4 text-[var(--color-accent)]" />
            <span>Transactional Flow</span>
          </button>
          <button
            type="button"
            @click="activeFlow = 'otp'"
            :class="[
              'px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2',
              activeFlow === 'otp'
                ? 'bg-white dark:bg-white/10 text-slate-900 dark:text-white shadow-sm'
                : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
            ]"
          >
            <KeyIcon class="w-4 h-4 text-amber-400" />
            <span>OTP Verification Flow</span>
          </button>
        </div>
      </div>
    </div>

    <!-- PIPELINE 1: Transactional Emails (Booking, Reminder, Receipt, Cancellation) -->
    <div v-if="activeFlow === 'transactional'" class="space-y-6">
      <!-- Architecture Meta Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161F] p-4 shadow-lg">
          <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-white/40 block">Trigger Points</span>
          <span class="text-sm font-bold text-slate-900 dark:text-white mt-0.5 block">Booking &amp; Checkout</span>
          <span class="text-[11px] text-emerald-500 font-mono">POST /api/v1/appointments</span>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161F] p-4 shadow-lg">
          <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-white/40 block">Queue Driver</span>
          <span class="text-sm font-bold text-slate-900 dark:text-white mt-0.5 block">Redis / DB Queue</span>
          <span class="text-[11px] text-amber-500 font-mono">SendNotificationEmail Job</span>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161F] p-4 shadow-lg">
          <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-white/40 block">Delivery Service</span>
          <span class="text-sm font-bold text-slate-900 dark:text-white mt-0.5 block">Resend API (HTTP)</span>
          <span class="text-[11px] text-emerald-500 font-mono">mail.candycutz.com</span>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161F] p-4 shadow-lg">
          <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-white/40 block">Average Latency</span>
          <span class="text-sm font-bold text-slate-900 dark:text-white mt-0.5 block">&lt; 1,200 ms</span>
          <span class="text-[11px] text-emerald-500 font-mono">99.8% Inbox Placement</span>
        </div>
      </div>

      <!-- Step-by-Step Flowchart Pipeline -->
      <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161F] p-6 sm:p-8 shadow-2xl space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-white/[0.08]">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-[var(--color-accent)]/15 border border-[var(--color-accent)]/30 flex items-center justify-center text-[var(--color-accent)] font-bold text-xs">
              01
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900 dark:text-white">Transactional Email Delivery Pipeline</h3>
              <p class="text-[11px] text-slate-500 dark:text-white/50">Dispatches confirmation tickets, calendar invites, and payment receipts</p>
            </div>
          </div>
          <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-bold">
            Asynchronous Non-Blocking
          </span>
        </div>

        <!-- Visual Step Nodes -->
        <div class="space-y-4">
          <div
            v-for="(step, idx) in transactionalSteps"
            :key="idx"
            class="group relative flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 rounded-2xl border transition-all duration-200"
            :class="[
              step.highlight
                ? 'bg-[var(--color-accent)]/5 border-[var(--color-accent)]/30 shadow-md'
                : 'bg-slate-50 dark:bg-white/[0.02] border-slate-200 dark:border-white/[0.06] hover:border-slate-300 dark:hover:border-white/15'
            ]"
          >
            <!-- Step Badge & Content -->
            <div class="flex items-start gap-4">
              <div
                class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-xs shrink-0 border"
                :class="step.iconBg"
              >
                <component :is="step.icon" class="w-5 h-5" />
              </div>
              <div class="space-y-1">
                <div class="flex items-center gap-2">
                  <span class="text-xs font-mono font-bold text-slate-400 dark:text-white/40">STAGE {{ idx + 1 }}</span>
                  <span class="text-sm font-bold text-slate-900 dark:text-white">{{ step.title }}</span>
                  <span v-if="step.pill" class="text-[10px] font-mono font-bold px-2 py-0.5 rounded" :class="step.pillClass">
                    {{ step.pill }}
                  </span>
                </div>
                <p class="text-xs text-slate-600 dark:text-white/60 leading-relaxed max-w-xl">
                  {{ step.desc }}
                </p>
              </div>
            </div>

            <!-- Technical Detail Box -->
            <div class="md:text-right shrink-0 font-mono text-[11px] text-slate-500 dark:text-white/50 space-y-0.5 bg-white/50 dark:bg-black/30 p-2.5 rounded-xl border border-slate-200 dark:border-white/[0.06]">
              <div class="font-bold text-slate-700 dark:text-slate-300">{{ step.techSpec }}</div>
              <div class="text-[10px] text-slate-400 dark:text-white/40">{{ step.techSub }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- PIPELINE 2: OTP & Authentication Flow (Verification Code & Magic Link) -->
    <div v-else class="space-y-6">
      <!-- Architecture Meta Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161F] p-4 shadow-lg">
          <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-white/40 block">Security Mode</span>
          <span class="text-sm font-bold text-slate-900 dark:text-white mt-0.5 block">6-Digit OTP + Link</span>
          <span class="text-[11px] text-amber-400 font-mono">High-Entropy CSPRNG</span>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161F] p-4 shadow-lg">
          <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-white/40 block">Expiration Window</span>
          <span class="text-sm font-bold text-slate-900 dark:text-white mt-0.5 block">15 Minutes (900s)</span>
          <span class="text-[11px] text-emerald-400 font-mono">Atomic Cache TTL</span>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161F] p-4 shadow-lg">
          <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-white/40 block">Rate Limiting</span>
          <span class="text-sm font-bold text-slate-900 dark:text-white mt-0.5 block">60s Cooldown</span>
          <span class="text-[11px] text-rose-400 font-mono">Max 3 attempts / hr</span>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161F] p-4 shadow-lg">
          <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-white/40 block">Delivery Priority</span>
          <span class="text-sm font-bold text-slate-900 dark:text-white mt-0.5 block">&lt; 800 ms</span>
          <span class="text-[11px] text-emerald-400 font-mono">Zero-Delay Transactional</span>
        </div>
      </div>

      <!-- Step-by-Step Flowchart Pipeline -->
      <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161F] p-6 sm:p-8 shadow-2xl space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-white/[0.08]">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400 font-bold text-xs">
              02
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900 dark:text-white">High-Security OTP Verification Pipeline</h3>
              <p class="text-[11px] text-slate-500 dark:text-white/50">Secures customer signup, email binding, and account authorization</p>
            </div>
          </div>
          <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20 font-bold">
            Zero-Delay Priority Route
          </span>
        </div>

        <!-- Visual Step Nodes -->
        <div class="space-y-4">
          <div
            v-for="(step, idx) in otpSteps"
            :key="idx"
            class="group relative flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 rounded-2xl border transition-all duration-200"
            :class="[
              step.highlight
                ? 'bg-amber-500/5 border-amber-500/30 shadow-md'
                : 'bg-slate-50 dark:bg-white/[0.02] border-slate-200 dark:border-white/[0.06] hover:border-slate-300 dark:hover:border-white/15'
            ]"
          >
            <!-- Step Badge & Content -->
            <div class="flex items-start gap-4">
              <div
                class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-xs shrink-0 border"
                :class="step.iconBg"
              >
                <component :is="step.icon" class="w-5 h-5" />
              </div>
              <div class="space-y-1">
                <div class="flex items-center gap-2">
                  <span class="text-xs font-mono font-bold text-slate-400 dark:text-white/40">STEP {{ idx + 1 }}</span>
                  <span class="text-sm font-bold text-slate-900 dark:text-white">{{ step.title }}</span>
                  <span v-if="step.pill" class="text-[10px] font-mono font-bold px-2 py-0.5 rounded" :class="step.pillClass">
                    {{ step.pill }}
                  </span>
                </div>
                <p class="text-xs text-slate-600 dark:text-white/60 leading-relaxed max-w-xl">
                  {{ step.desc }}
                </p>
              </div>
            </div>

            <!-- Technical Detail Box -->
            <div class="md:text-right shrink-0 font-mono text-[11px] text-slate-500 dark:text-white/50 space-y-0.5 bg-white/50 dark:bg-black/30 p-2.5 rounded-xl border border-slate-200 dark:border-white/[0.06]">
              <div class="font-bold text-slate-700 dark:text-slate-300">{{ step.techSpec }}</div>
              <div class="text-[10px] text-slate-400 dark:text-white/40">{{ step.techSub }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import {
  EnvelopeIcon,
  KeyIcon,
  SparklesIcon,
  QueueListIcon,
  PaintBrushIcon,
  PaperAirplaneIcon,
  CheckBadgeIcon,
  ShieldCheckIcon,
  ClockIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline';

const activeFlow = ref('transactional');

const transactionalSteps = [
  {
    icon: SparklesIcon,
    iconBg: 'bg-[var(--color-accent)]/15 border-[var(--color-accent)]/30 text-[var(--color-accent)]',
    title: 'Client Event Trigger',
    pill: 'HTTP 201',
    pillClass: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
    desc: 'Customer completes appointment booking or cashier verifies a manual transfer receipt via /api/v1/appointments or /api/v1/payments/verify.',
    techSpec: 'POST /api/v1/appointments',
    techSub: 'State: confirmed | verified'
  },
  {
    icon: ShieldCheckIcon,
    iconBg: 'bg-indigo-500/15 border-indigo-500/30 text-indigo-400',
    title: 'Transactional Integrity Lock',
    pill: 'DB::transaction',
    pillClass: 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20',
    desc: 'AvailabilityEngine locks chair slot using SELECT ... FOR UPDATE to eliminate double-booking hazards before payment is finalized.',
    techSpec: 'InnoDB lockForUpdate()',
    techSub: 'Idempotency key checked'
  },
  {
    icon: QueueListIcon,
    iconBg: 'bg-amber-500/15 border-amber-500/30 text-amber-400',
    title: 'Background Job Queuing',
    pill: 'Async Job',
    pillClass: 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
    desc: 'SendNotificationEmail::dispatch($appointment) is pushed onto the Redis queue. The HTTP endpoint responds immediately to client in <80ms.',
    techSpec: 'Redis / Database Queue',
    techSub: 'Retry backoff: 3x attempts'
  },
  {
    icon: PaintBrushIcon,
    iconBg: 'bg-purple-500/15 border-purple-500/30 text-purple-400',
    title: 'Luxury HTML Template Compilation',
    pill: 'Blade Engine',
    pillClass: 'bg-purple-500/10 text-purple-400 border border-purple-500/20',
    desc: 'Compiles booking-confirmation.blade.php with dark gold typography, QR check-in reference, saloon address, and .ics calendar attachments.',
    techSpec: 'CandyCutz Luxury CSS',
    techSub: 'Responsive 600px / 380px'
  },
  {
    icon: PaperAirplaneIcon,
    iconBg: 'bg-emerald-500/15 border-emerald-500/30 text-emerald-400',
    title: 'Resend API Dispatch',
    pill: 'TLS 1.3',
    pillClass: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
    highlight: true,
    desc: 'Payload is transmitted to https://api.resend.com/emails with cryptographic DKIM, SPF, and DMARC headers from mail.candycutz.com.',
    techSpec: 'REST HTTPS API',
    techSub: 'Header: x-resend-id'
  },
  {
    icon: CheckCircleIcon,
    iconBg: 'bg-emerald-500/15 border-emerald-500/30 text-emerald-400',
    title: 'Delivered to Customer VIP Inbox',
    pill: 'Delivered',
    pillClass: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
    desc: 'Customer receives official ticket on mobile / desktop mail client with 1-click directions to Angwan Kare saloon and instant portal management.',
    techSpec: 'Primary Inbox Placement',
    techSub: 'Zero spam score'
  }
];

const otpSteps = [
  {
    icon: KeyIcon,
    iconBg: 'bg-amber-500/15 border-amber-500/30 text-amber-400',
    title: 'Verification Request',
    pill: 'Auth Action',
    pillClass: 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
    desc: 'New customer registers, requests email verification, or requests password reset from the CandyCutz mobile app or web portal.',
    techSpec: 'POST /api/v1/auth/email/...',
    techSub: 'Context: email_verification'
  },
  {
    icon: ClockIcon,
    iconBg: 'bg-rose-500/15 border-rose-500/30 text-rose-400',
    title: 'Rate-Limiter Protection Gate',
    pill: 'Anti-Abuse',
    pillClass: 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
    desc: 'RateLimiter enforces a strict 60-second cooldown per account/IP to prevent brute force and email bombing. Throws 429 if abused.',
    techSpec: 'RateLimiter::tooManyAttempts',
    techSub: 'Limit: 1/min, max 3/hr'
  },
  {
    icon: ShieldCheckIcon,
    iconBg: 'bg-amber-500/15 border-amber-500/30 text-amber-400',
    title: 'Cryptographic 6-Digit OTP Generation',
    pill: 'CSPRNG',
    pillClass: 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
    desc: 'System generates a cryptographically secure 6-digit integer: sprintf("%06d", random_int(100000, 999999)). Non-guessable.',
    techSpec: 'random_int(100000, 999999)',
    techSub: 'Format: 6 numeric digits'
  },
  {
    icon: QueueListIcon,
    iconBg: 'bg-indigo-500/15 border-indigo-500/30 text-indigo-400',
    title: 'Atomic Redis Cache Storage',
    pill: '15m TTL',
    pillClass: 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20',
    desc: 'OTP is stored in Redis cache bound specifically to user ID and email: Cache::put("email_verification_otp:{id}", code, 900s).',
    techSpec: 'Cache::put(..., 15 mins)',
    techSub: 'Atomic binding to user'
  },
  {
    icon: PaperAirplaneIcon,
    iconBg: 'bg-emerald-500/15 border-emerald-500/30 text-emerald-400',
    title: 'Immediate Priority Resend Dispatch',
    pill: '&lt; 800ms',
    pillClass: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
    highlight: true,
    desc: 'Bypasses standard bulk queues for instantaneous transactional delivery. Features high-contrast glowing OTP digits + 1-click magic URL.',
    techSpec: 'Direct API Dispatch',
    techSub: 'Subject: Verify Your Email'
  },
  {
    icon: CheckBadgeIcon,
    iconBg: 'bg-emerald-500/15 border-emerald-500/30 text-emerald-400',
    title: 'Dual Verification Gate',
    pill: 'Verified',
    pillClass: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
    desc: 'User either submits the 6-digit code via POST /api/v1/auth/verify-otp OR taps the 1-click signed URL. Cache is purged and email_verified_at is stamped.',
    techSpec: 'email_verified_at = now()',
    techSub: 'Account permanently unlocked'
  }
];
</script>
