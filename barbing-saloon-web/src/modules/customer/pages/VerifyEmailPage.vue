<template>
  <CustomerLayout>
    <section class="max-w-4xl mx-auto pb-16 pt-4 px-4 sm:px-6 space-y-6 animate-fade-in">
      <!-- Luxury Hero Card -->
      <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-white dark:from-[#12161C] dark:via-[#0E1217] dark:to-[#12161C] p-8 sm:p-10 shadow-2xl">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
          <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 text-[var(--color-accent)] text-xs font-bold uppercase tracking-wider">
              <ShieldCheckIcon class="w-4 h-4" />
              <span>Identity &amp; Account Protection</span>
            </div>
            <h1 class="font-display text-3xl sm:text-4xl text-slate-900 dark:text-white font-bold">
              Email <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-amber-200">Verification</span>
            </h1>
            <p class="max-w-xl text-xs sm:text-sm text-slate-600 dark:text-white/70 leading-relaxed">
              Verify your email address to permanently secure your account. Verified emails cannot be registered or claimed by any other user and receive all official booking confirmations, receipts, and schedule updates.
            </p>
          </div>

          <router-link
            to="/customer/dashboard/profile"
            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.03] text-xs font-semibold text-slate-700 dark:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06] transition-colors self-start sm:self-auto shrink-0"
          >
            <ArrowLeftIcon class="w-4 h-4" />
            <span>Back to Profile</span>
          </router-link>
        </div>
      </div>

      <!-- Verification Interactive Card -->
      <EmailVerificationCard />

      <!-- Security & Policy Explainer -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
        <div class="rounded-2xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-[#12161C] p-5 space-y-2">
          <div class="h-8 w-8 rounded-lg bg-[var(--color-accent)]/10 text-[var(--color-accent)] flex items-center justify-center font-bold">
            1
          </div>
          <h4 class="font-bold text-slate-900 dark:text-white">Duplicate Protection</h4>
          <p class="text-slate-500 dark:text-white/50 leading-relaxed text-[11px]">
            Once confirmed, this email cannot be claimed or re-used in any new account registration.
          </p>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-[#12161C] p-5 space-y-2">
          <div class="h-8 w-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold">
            2
          </div>
          <h4 class="font-bold text-slate-900 dark:text-white">Booking Receipts</h4>
          <p class="text-slate-500 dark:text-white/50 leading-relaxed text-[11px]">
            Automated confirmation emails and verified payment receipts are reliably sent to your inbox.
          </p>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-[#12161C] p-5 space-y-2">
          <div class="h-8 w-8 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center font-bold">
            3
          </div>
          <h4 class="font-bold text-slate-900 dark:text-white">Schedule Alerts</h4>
          <p class="text-slate-500 dark:text-white/50 leading-relaxed text-[11px]">
            Timely reminder notifications prevent missed appointments and keep you synced with your barber.
          </p>
        </div>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import EmailVerificationCard from '@/shared/components/EmailVerificationCard.vue';
import { ShieldCheckIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
</script>
