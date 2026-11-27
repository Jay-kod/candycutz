<template>
  <div class="min-h-screen flex items-center justify-center p-4 sm:p-6 bg-slate-50 dark:bg-[#0B0F13] text-slate-900 dark:text-white">
    <div class="max-w-md w-full rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-8 shadow-2xl text-center space-y-6 animate-fade-in">
      <!-- Logo Branding -->
      <div class="flex items-center justify-center gap-2">
        <span class="font-display text-2xl font-black text-[var(--color-accent)] tracking-tight">CandyCutz</span>
        <span class="text-[10px] uppercase font-bold tracking-widest text-slate-400">Security</span>
      </div>

      <!-- State 1: Verification in Progress -->
      <div v-if="verifying" class="py-8 space-y-4">
        <ArrowPathIcon class="w-12 h-12 text-[var(--color-accent)] animate-spin mx-auto" />
        <h2 class="text-lg font-bold">Verifying Your Email...</h2>
        <p class="text-xs text-slate-500 dark:text-white/50">
          Validating cryptographic verification token with CandyCutz authentication gateway.
        </p>
      </div>

      <!-- State 2: Success -->
      <div v-else-if="verified" class="py-4 space-y-4">
        <div class="h-16 w-16 rounded-full bg-emerald-500/10 border-2 border-emerald-500/30 text-emerald-500 flex items-center justify-center mx-auto">
          <CheckCircleIcon class="w-10 h-10" />
        </div>
        <h2 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Email Verified!</h2>
        <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed max-w-sm mx-auto">
          Your email address has been verified and permanently bound to your account. You will now receive all official booking confirmations, receipts, and appointment updates.
        </p>

        <div class="pt-4">
          <button
            type="button"
            @click="goToDashboard"
            class="w-full py-3.5 rounded-xl bg-[var(--color-accent)] hover:brightness-110 text-white font-bold text-xs shadow-lg shadow-[var(--color-accent)]/20 transition-all cursor-pointer"
          >
            Continue to Dashboard
          </button>
        </div>
      </div>

      <!-- State 3: Error / Expired -->
      <div v-else-if="error" class="py-4 space-y-4">
        <div class="h-16 w-16 rounded-full bg-rose-500/10 border-2 border-rose-500/30 text-rose-500 flex items-center justify-center mx-auto">
          <XCircleIcon class="w-10 h-10" />
        </div>
        <h2 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Verification Failed</h2>
        <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed max-w-sm mx-auto">
          {{ errorMessage }}
        </p>

        <div class="pt-4 flex flex-col gap-2">
          <button
            type="button"
            @click="goToDashboard"
            class="w-full py-3.5 rounded-xl bg-[var(--color-accent)] hover:brightness-110 text-white font-bold text-xs shadow-lg shadow-[var(--color-accent)]/20 transition-all cursor-pointer"
          >
            Go to Profile to Request New Code
          </button>
        </div>
      </div>

      <!-- State 4: Default / Embedded Card -->
      <div v-else class="text-left">
        <EmailVerificationCard />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { client } from '@/shared/api/client';
import { useAuthStore } from '@/modules/auth/store/auth.store';
import EmailVerificationCard from '@/shared/components/EmailVerificationCard.vue';
import { CheckCircleIcon, XCircleIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const verifying = ref(false);
const verified = ref(false);
const error = ref(false);
const errorMessage = ref('This verification link is invalid or has expired.');

async function checkRouteParams() {
  const statusParam = route.query.status;
  if (statusParam === 'success') {
    verified.value = true;
    if (authStore.user) {
      authStore.setUser({ ...authStore.user, is_email_verified: true, email_verified_at: new Date().toISOString() });
    }
    return;
  }

  if (statusParam === 'invalid') {
    error.value = true;
    errorMessage.value = 'This email verification link is invalid or has expired. Please log in and request a new one.';
    return;
  }

  const id = route.query.id;
  const hash = route.query.hash;

  if (id && hash) {
    verifying.value = true;
    try {
      const res = await client.get(`/auth/email/verify/${id}/${hash}`, { params: route.query });
      if (res.data?.success !== false) {
        verified.value = true;
        if (authStore.user) {
          authStore.setUser({ ...authStore.user, is_email_verified: true, email_verified_at: new Date().toISOString() });
        }
      } else {
        error.value = true;
        errorMessage.value = res.data?.message || 'Verification failed.';
      }
    } catch (e) {
      error.value = true;
      errorMessage.value = e?.response?.data?.message || 'This verification link is invalid or has expired.';
    } finally {
      verifying.value = false;
    }
  }
}

function goToDashboard() {
  const role = authStore.user?.role;
  if (role === 'barber') {
    router.push('/barber/profile');
  } else if (role === 'admin' || role === 'super_admin') {
    router.push('/admin/dashboard');
  } else {
    router.push('/customer/dashboard/profile');
  }
}

onMounted(() => {
  checkRouteParams();
});
</script>
