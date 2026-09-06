<template>
  <div class="h-screen flex bg-theme-bg overflow-hidden relative text-theme-text">
    <div class="hidden lg:flex lg:w-1/2 relative flex-col items-center justify-between p-16 overflow-hidden text-center">
      <div class="absolute inset-0 bg-theme-bg z-0"></div>
      <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_top_left,_var(--tw-gradient-stops))] from-gold/10 via-transparent to-transparent z-0"></div>
      <div class="absolute bottom-0 right-0 w-full h-full bg-[radial-gradient(ellipse_at_bottom_right,_var(--tw-gradient-stops))] from-gold/5 via-transparent to-transparent z-0"></div>
      <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1px] h-full bg-gradient-to-b from-transparent via-theme-border/20 to-transparent z-0"></div>

      <div class="relative z-10">
        <a href="/" class="text-2xl font-display font-bold text-theme-text tracking-widest uppercase flex flex-col items-center gap-2">
          <img src="/images/logo-icon.png" alt="CandyCutz Logo" class="h-36 w-36 object-contain drop-shadow-[0_0_16px_rgba(212,175,55,0.5)] rounded-full" />
          <span><span class="text-gold">Candy</span>Cutz</span>
        </a>
      </div>

      <div class="relative z-10 max-w-lg">
        <div class="w-12 h-1 bg-gold mb-8 mx-auto"></div>
        <h2 class="font-display text-5xl text-theme-text leading-tight mb-6">
          Fresh cut,<br />fresh password.
        </h2>
        <p class="text-lg text-theme-muted font-light leading-relaxed">
          Choose a new password to secure your CandyCutz account.
        </p>
      </div>

      <div class="relative z-10">
        <p class="text-xs uppercase tracking-[0.3em] text-theme-muted font-semibold">
          &copy; {{ new Date().getFullYear() }} CandyCutz. All rights reserved.
        </p>
      </div>
    </div>

    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-24 relative z-10 bg-theme-surface lg:bg-transparent shadow-[-20px_0_50px_rgba(0,0,0,0.5)] lg:shadow-none border-l border-theme-border overflow-y-auto">
      <div class="w-full max-w-sm">
        <div class="mb-8">
          <div class="text-center">
            <h1 class="font-display font-bold text-3xl sm:text-4xl text-gold mb-4">Reset Password</h1>
          </div>
          <p class="text-theme-muted text-xs sm:text-sm text-center">Set a new password for your account.</p>
        </div>

        <form class="space-y-4" @submit.prevent="submitForm">
          <div v-if="generalError" class="bg-red-500/10 border border-red-500/30 rounded-lg p-3 text-xs text-red-400 font-medium flex items-center gap-2">
            <span>{{ generalError }}</span>
          </div>

          <div v-if="message" class="bg-green-500/10 border border-green-500/30 rounded-lg p-3 text-xs text-green-400 font-medium flex items-center gap-2">
            <span>{{ message }}</span>
          </div>

          <div class="relative group">
            <label for="rp-password" class="block text-[10px] font-semibold uppercase tracking-wider text-gold mb-1.5">New Password</label>
            <input
              v-model="password"
              id="rp-password"
              class="w-full bg-theme-surface border border-graphite rounded-lg px-4 py-3 text-theme-text text-sm font-medium placeholder-theme-muted focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 outline-none"
              type="password"
              placeholder="Minimum 8 characters"
            />
            <p v-if="errors.password" class="mt-1.5 text-xs text-red-400 font-medium">{{ errors.password }}</p>
          </div>

          <div class="relative group">
            <label for="rp-password-confirm" class="block text-[10px] font-semibold uppercase tracking-wider text-gold mb-1.5">Confirm New Password</label>
            <input
              v-model="passwordConfirmation"
              id="rp-password-confirm"
              class="w-full bg-theme-surface border border-graphite rounded-lg px-4 py-3 text-theme-text text-sm font-medium placeholder-theme-muted focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 outline-none"
              type="password"
              placeholder="Re-enter your new password"
            />
            <p v-if="errors.password_confirmation" class="mt-1.5 text-xs text-red-400 font-medium">{{ errors.password_confirmation }}</p>
          </div>

          <button
            :disabled="loading"
            class="w-full rounded-lg bg-gold px-4 py-3 text-sm font-bold text-obsidian tracking-wide transition-all duration-300 hover:bg-gold-dark hover:shadow-[0_0_20px_rgba(212,175,55,0.3)] disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <svg v-if="loading" class="animate-spin h-4 w-4 text-obsidian" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            {{ loading ? 'Resetting...' : 'Reset Password' }}
          </button>
        </form>

        <p class="mt-6 text-center text-xs text-theme-muted">
          Remembered your password?
          <RouterLink to="/customer/login" class="font-semibold text-gold hover:text-gold-dark transition-colors">Sign in</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { object, string } from 'yup';
import { authApi } from '../api/auth.api';
import { useDark } from '@vueuse/core';

const isDark = useDark({
  selector: 'html',
  attribute: 'data-theme',
  valueDark: 'dark',
  valueLight: 'light',
});

isDark.value = true;

const route = useRoute();

const password = ref('');
const passwordConfirmation = ref('');
const loading = ref(false);
const message = ref('');
const generalError = ref('');
const errors = reactive({});

const schema = object({
  password: string().required('Password is required').min(8, 'Password must be at least 8 characters'),
});

async function submitForm() {
  loading.value = true;
  message.value = '';
  generalError.value = '';
  errors.password = '';
  errors.password_confirmation = '';

  if (password.value !== passwordConfirmation.value) {
    errors.password_confirmation = 'Passwords do not match';
    loading.value = false;
    return;
  }

  try {
    await schema.validate({ password: password.value }, { abortEarly: false });
    const response = await authApi.resetPassword({
      email: route.query.email || '',
      token: route.query.token || '',
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    });
    message.value = response.data.message || 'Your password has been reset. You can now sign in.';
  } catch (error) {
    if (error.inner) {
      error.inner.forEach((item) => {
        errors[item.path] = item.message;
      });
    } else {
      generalError.value = error?.response?.data?.error || 'Something went wrong. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}
</script>